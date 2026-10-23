<?php
/**
 * Plugin Name:       LU3G Login Brand
 * Description:       Personalizza la schermata di accesso a wp-admin con logo, colori e font del sito, affiancando il logo LU3G. Il pulsante "Analizza il sito" legge lo stile della homepage; i valori si possono poi ritoccare.
 * Version:           1.5.0
 * Author:            LU3G
 * Author URI:        https://lu3g.it/
 * Plugin URI:        https://github.com/sviluppolu3g/lu3g-login-brand
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       lu3g-login-brand
 * Update URI:        https://github.com/sviluppolu3g/lu3g-login-brand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Brand {

	const OPT     = 'lu3g_login_brand';
	const VERSION = '1.5.0';

	public static function init() {
		// schermata di accesso (il resto di wp-admin resta com'è)
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'login_styles' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'header_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'header_text' ) );
		add_action( 'login_footer', array( __CLASS__, 'login_footer' ) );
		add_filter( 'login_message', array( __CLASS__, 'login_message' ) );
		add_filter( 'login_display_language_dropdown', array( __CLASS__, 'language_dropdown' ) );

		// impostazioni: Impostazioni → Accesso LU3G
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_links' ) );
	}

	/* ---------------------------------------------------------------- impostazioni */

	/** Valori di partenza letti dal server (logo personalizzato, kit Elementor, theme.json), usati finché non si analizza il sito. */
	public static function defaults() {
		$logo = '';
		$id   = (int) get_theme_mod( 'custom_logo' );
		if ( $id ) {
			$logo = (string) wp_get_attachment_image_url( $id, 'full' );
		}
		$kit = self::elementor_kit();
		if ( ! $logo && ! empty( $kit['site_logo']['url'] ) ) {
			$logo = $kit['site_logo']['url'];
		}
		if ( ! $logo ) {
			$logo = (string) get_site_icon_url( 256 );
		}

		$palette = array();
		if ( function_exists( 'wp_get_global_settings' ) ) {
			$theme = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
			foreach ( (array) $theme as $c ) {
				if ( ! empty( $c['color'] ) ) {
					$palette[] = $c['color'];
				}
			}
		}
		if ( ! empty( $kit['system_colors'] ) ) {
			foreach ( $kit['system_colors'] as $c ) {
				if ( ! empty( $c['color'] ) ) {
					$palette[] = $c['color'];
				}
			}
		}
		list( $bg, $accent ) = self::pick_colors( $palette );

		$font = '';
		foreach ( (array) ( $kit['system_typography'] ?? array() ) as $t ) {
			if ( 'primary' === ( $t['_id'] ?? '' ) && ! empty( $t['typography_font_family'] ) ) {
				$font = $t['typography_font_family'];
			}
		}

		return array(
			'logo'      => $logo,
			'logo_tone' => 'light',      // light = logo chiaro, sta sul fondo scuro; dark = logo scuro, va su un riquadro bianco
			'bg'        => $bg,
			'accent'    => $accent,
			'font'      => $font,
			'font_css'  => array(),
			'lu3g'      => 1,
			'signature' => 1,
			'motion'    => 1,          // interazioni animate (entrata, alone, pulsante, campi)
			'splash'    => 1,          // schermata di caricamento dopo l'accesso
			'font_head' => '',         // Google Font dei titoli ('' = predefinito: Manrope o font del sito)
			'font_body' => '',         // Google Font di testi, campi e pulsante ('' = DM Sans o font del sito)
			'layout'    => 'report',   // aspetto: report = come report.lu3g.com/login; sito = colori e logo del sito
			'title'     => 'Accedi al sito',
			'button'    => 'Accedi al pannello',
			'analyzed'  => '',
		);
	}

	public static function get() {
		return wp_parse_args( (array) get_option( self::OPT, array() ), self::defaults() );
	}

	private static function elementor_kit() {
		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) {
			return array();
		}
		$s = get_post_meta( $kit_id, '_elementor_page_settings', true );
		return is_array( $s ) ? $s : array();
	}

	/** Da una lista di colori: il fondo (il più scuro) e l'accento (il più saturo, diverso dal fondo). */
	private static function pick_colors( array $colors ) {
		$bg = '';
		$accent = '';
		$best_l = 1;
		$best_s = 0;
		foreach ( $colors as $c ) {
			$c = sanitize_hex_color( $c );
			if ( ! $c ) {
				continue;
			}
			$l = self::luminance( $c );
			$s = self::saturation( $c );
			if ( $l < 0.2 && $l < $best_l ) {
				$best_l = $l;
				$bg = $c;
			}
			if ( $s > 0.35 && $l > 0.12 && $s > $best_s ) {
				$best_s = $s;
				$accent = $c;
			}
		}
		return array( $bg ? $bg : '#1d2327', $accent ? $accent : '#2271b1' );
	}

	public static function register_setting() {
		register_setting( 'lu3g_login_brand', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $in ) {
		$in  = (array) $in;
		$out = array();

		$logo = trim( (string) ( $in['logo'] ?? '' ) );
		// un logo SVG scritto nella pagina arriva come data URI: lo accetto solo in base64
		if ( preg_match( '#^data:image/svg\+xml;base64,[A-Za-z0-9+/=]+$#', $logo ) && strlen( $logo ) < 200000 ) {
			$out['logo'] = $logo;
		} else {
			$out['logo'] = esc_url_raw( $logo );
		}
		$out['logo_tone'] = ( 'dark' === ( $in['logo_tone'] ?? '' ) ) ? 'dark' : 'light';
		$out['bg']        = sanitize_hex_color( $in['bg'] ?? '' ) ?: '#1d2327';
		$out['accent']    = sanitize_hex_color( $in['accent'] ?? '' ) ?: '#2271b1';
		$out['font']      = self::clean_font( $in['font'] ?? '' );

		$css = is_array( $in['font_css'] ?? null ) ? $in['font_css'] : preg_split( '/\s+/', (string) ( $in['font_css'] ?? '' ) );
		$out['font_css'] = array_slice( array_values( array_filter( array_map( 'esc_url_raw', $css ) ) ), 0, 4 );

		$out['lu3g']      = empty( $in['lu3g'] ) ? 0 : 1;
		$out['signature'] = empty( $in['signature'] ) ? 0 : 1;
		$out['motion']    = empty( $in['motion'] ) ? 0 : 1;
		$out['splash']    = empty( $in['splash'] ) ? 0 : 1;
		$out['layout']    = ( 'sito' === ( $in['layout'] ?? '' ) ) ? 'sito' : 'report';
		$out['title']     = substr( sanitize_text_field( $in['title'] ?? '' ), 0, 80 );
		$out['button']    = substr( sanitize_text_field( $in['button'] ?? '' ), 0, 60 );
		$out['analyzed']  = sanitize_text_field( $in['analyzed'] ?? '' );

		// Google Fonts scelti: si scaricano sul sito al salvataggio (se Google non risponde resta il predefinito)
		foreach ( array( 'font_head', 'font_body' ) as $key ) {
			$family      = trim( sanitize_text_field( $in[ $key ] ?? '' ) );
			$out[ $key ] = '';
			if ( '' === $family ) {
				continue;
			}
			$f = LU3G_Login_Fonts::find( $family );
			if ( ! $f ) {
				add_settings_error( self::OPT, 'lu3g-font-' . $key, '"' . $family . '" non è nell\'elenco dei Google Fonts: resta il font predefinito.' );
				continue;
			}
			$res = LU3G_Login_Fonts::import( $f[0] );
			if ( is_wp_error( $res ) ) {
				add_settings_error( self::OPT, 'lu3g-font-' . $key, $res->get_error_message() . ' Resta il font predefinito.' );
				continue;
			}
			$out[ $key ] = $f[0];
		}
		return $out;
	}

	private static function clean_font( $f ) {
		$f = preg_replace( '/[^A-Za-z0-9 ,\'"_-]/', '', (string) $f );
		return trim( substr( $f, 0, 200 ) );
	}

	/* ---------------------------------------------------------------- colori */

	private static function rgb( $hex ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	}

	private static function hex( array $c ) {
		return sprintf( '#%02x%02x%02x', max( 0, min( 255, round( $c[0] ) ) ), max( 0, min( 255, round( $c[1] ) ) ), max( 0, min( 255, round( $c[2] ) ) ) );
	}

	/** Luminanza relativa (WCAG), 0 = nero, 1 = bianco. */
	public static function luminance( $hex ) {
		$l = 0;
		foreach ( self::rgb( $hex ) as $i => $v ) {
			$v  = $v / 255;
			$v  = $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
			$l += $v * array( 0.2126, 0.7152, 0.0722 )[ $i ];
		}
		return $l;
	}

	private static function saturation( $hex ) {
		$c   = self::rgb( $hex );
		$max = max( $c ) / 255;
		$min = min( $c ) / 255;
		$l   = ( $max + $min ) / 2;
		if ( $max === $min ) {
			return 0;
		}
		return ( $max - $min ) / ( 1 - abs( 2 * $l - 1 ) );
	}

	/** Mescola due colori: $t = quanto del secondo. */
	private static function mix( $a, $b, $t ) {
		$a = self::rgb( $a );
		$b = self::rgb( $b );
		return self::hex( array( $a[0] + ( $b[0] - $a[0] ) * $t, $a[1] + ( $b[1] - $a[1] ) * $t, $a[2] + ( $b[2] - $a[2] ) * $t ) );
	}

	private static function rgba( $hex, $alpha ) {
		$c = self::rgb( $hex );
		return 'rgba(' . $c[0] . ',' . $c[1] . ',' . $c[2] . ',' . $alpha . ')';
	}

	/* ---------------------------------------------------------------- schermata di accesso */

	public static function header_url() {
		return home_url( '/' );
	}

	public static function header_text() {
		$o = self::get();
		if ( 'report' === $o['layout'] ) {
			return 'LU3G';
		}
		return get_bloginfo( 'name' ) . ( $o['lu3g'] ? ' · sito realizzato da LU3G' : '' );
	}

	/** Aspetto "Report LU3G": titolo sopra il modulo (il filtro login_message lo stampa tra il logo e il modulo). */
	public static function login_message( $message ) {
		$o = self::get();
		if ( 'report' !== $o['layout'] ) {
			return $message;
		}
		return '<h2 class="lu3g-titolo">' . esc_html( LU3G_Login_Report::title( $o ) ) . '</h2>' . $message;
	}

	/** Aspetto "Report LU3G": niente scelta della lingua sotto il modulo (come nell'app). */
	public static function language_dropdown( $show ) {
		return 'report' === self::get()['layout'] ? false : $show;
	}

	public static function login_footer() {
		$o = self::get();
		if ( 'report' === $o['layout'] ) {
			LU3G_Login_Report::footer( $o );
			return;
		}
		if ( $o['lu3g'] && $o['signature'] ) {
			echo '<p class="lu3g-firma">Sito realizzato da <a href="https://lu3g.it/" target="_blank" rel="noopener">LU3G</a></p>';
		}
	}

	public static function lu3g_logo( $tone ) {
		$file = plugin_dir_path( __FILE__ ) . 'assets/lu3g-' . ( 'dark' === $tone ? 'scuro' : 'chiaro' ) . '.svg';
		return is_readable( $file ) ? 'data:image/svg+xml;base64,' . base64_encode( (string) file_get_contents( $file ) ) : '';
	}

	public static function login_styles() {
		$o = self::get();
		// Google Fonts scelti nelle impostazioni, dalla copia scaricata sul sito
		foreach ( array_unique( array_filter( array( $o['font_head'], $o['font_body'] ) ) ) as $i => $family ) {
			$css = LU3G_Login_Fonts::local_css( $family );
			if ( $css ) {
				wp_enqueue_style( 'lu3g-login-gfont-' . $i, $css, array(), null );
			}
		}
		if ( 'report' === $o['layout'] ) {
			echo '<style id="lu3g-login-brand">' . LU3G_Login_Report::css( $o, __FILE__, self::lu3g_logo( 'light' ) ) . '</style>' . "\n";
			return;
		}
		foreach ( $o['font_css'] as $i => $url ) {
			wp_enqueue_style( 'lu3g-login-font-' . $i, $url, array(), null );
		}
		// login.css di WordPress arriva dopo questo stile: i selettori partono da body.login per avere la precedenza
		echo '<style id="lu3g-login-brand">' . self::css( $o ) . '</style>' . "\n";
	}

	/** CSS della schermata di accesso, ricavato da fondo, accento, logo e font. */
	public static function css( array $o ) {
		$bg     = $o['bg'];
		$bg2    = self::mix( $bg, '#000000', 0.38 );                       // fondo più profondo per la sfumatura
		$accent = $o['accent'];
		$on_acc = self::luminance( $accent ) > 0.45 ? $bg2 : '#ffffff';    // testo sul pulsante, sempre leggibile
		$ink    = self::luminance( $bg ) < 0.5 ? $bg2 : '#1d2327';         // testo scuro dentro il modulo
		$font   = $o['font_body'] ? LU3G_Login_Fonts::stack( $o['font_body'] ) . ', ' : ( $o['font'] ? $o['font'] . ', ' : '' );
		$panel  = 'dark' === $o['logo_tone'];                             // logo scuro: loghi su un riquadro bianco
		$line   = $panel ? 'rgba(0,0,0,.14)' : 'rgba(255,255,255,.28)';
		$logo   = $o['logo'];
		$lu3g   = $o['lu3g'] ? self::lu3g_logo( $panel ? 'dark' : 'light' ) : '';
		$u      = static function ( $url ) {
			return 0 === strpos( $url, 'data:' ) ? $url : esc_url( $url );
		};

		$css  = "body.login{background:{$bg2} radial-gradient(120% 80% at 50% 0%,{$bg} 0%,{$bg2} 70%) fixed;color:#fff;font-family:{$font}-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,sans-serif}";
		$css .= "body.login::before{content:\"\";position:fixed;left:0;right:0;top:0;height:4px;background:{$accent}}";
		$css .= 'body.login #login{width:340px;padding:7vh 0 24px}';

		// loghi: il logo del sito a sinistra, una riga sottile, il logo LU3G a destra (proporzioni libere, "contain")
		$css .= 'body.login #login h1 a{display:flex;align-items:center;justify-content:center;width:auto;height:auto;margin:0 auto 28px;'
			. 'padding:' . ( $panel ? '18px 24px' : '0' ) . ';background:' . ( $panel ? '#fff' : 'none' ) . ';border-radius:16px;'
			. 'text-indent:0;font-size:0;line-height:0;overflow:visible;color:#fff}';
		if ( $logo ) {
			$css .= 'body.login #login h1 a::before{content:"";flex:0 1 ' . ( $lu3g ? '150px' : '240px' ) . ';height:72px;'
				. 'background:url("' . $u( $logo ) . '") ' . ( $lu3g ? 'right' : 'center' ) . ' center/contain no-repeat}';
		} else {
			// nessun logo trovato: il nome del sito al suo posto
			$css .= 'body.login #login h1 a{font-size:24px;line-height:1.2;font-weight:600;letter-spacing:.02em}';
		}
		if ( $lu3g ) {
			$css .= "body.login #login h1 a::after{content:\"\";flex:0 0 auto;width:72px;height:72px;margin-left:26px;padding-left:26px;"
				. "border-left:1px solid {$line};background:url(\"{$lu3g}\") center/contain no-repeat;background-origin:content-box}";
		}

		$css .= 'body.login form{background:#fff;border:0;border-radius:16px;padding:28px 26px 30px;margin-top:0;box-shadow:0 24px 60px rgba(0,0,0,.35)}';
		$css .= "body.login label{color:{$ink};font-size:13px;letter-spacing:.02em}";
		$css .= 'body.login form .input,body.login input[type="text"],body.login input[type="password"]{border:1px solid #cfd6de;border-radius:10px;font-size:18px;padding:6px 12px;background:#f6f8fa}';
		$css .= 'body.login form .input:focus,body.login input[type="text"]:focus,body.login input[type="password"]:focus'
			. "{border-color:{$accent};box-shadow:0 0 0 3px " . self::rgba( $accent, '.35' ) . ';background:#fff;outline:0}';
		$css .= "body.login .button.wp-hide-pw,body.login .button.wp-hide-pw .dashicons{color:{$ink}}";
		$css .= "body.login .button.wp-hide-pw:focus,body.login input[type=\"checkbox\"]:focus{border-color:{$accent};box-shadow:0 0 0 1px {$accent}}";
		$css .= "body.wp-core-ui.login .button-primary{background:{$accent};border:0;border-radius:999px;color:{$on_acc};text-shadow:none;box-shadow:none;"
			. 'padding:0 22px;min-height:40px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;transition:background .3s ease,color .3s ease}';
		$css .= "body.wp-core-ui.login .button-primary:hover,body.wp-core-ui.login .button-primary:focus{background:{$bg};color:#fff;box-shadow:0 0 0 3px "
			. self::rgba( $accent, '.45' ) . '}';
		$css .= 'body.login #nav,body.login #backtoblog{text-align:center;padding:0}';
		$css .= 'body.login #nav a,body.login #backtoblog a,body.login .privacy-policy-page-link a{color:rgba(255,255,255,.75)}';
		$css .= "body.login #nav a:hover,body.login #backtoblog a:hover,body.login .privacy-policy-page-link a:hover,body.login #nav a:focus,body.login #backtoblog a:focus{color:{$accent};box-shadow:none}";
		$css .= "body.login .message,body.login .notice,body.login .success{border-left-color:{$accent};border-radius:10px;color:{$ink}}";
		$css .= "body.login #login_error{border-radius:10px;color:{$ink}}";
		$css .= 'body.login .language-switcher label .dashicons{color:rgba(255,255,255,.75)}';
		$css .= 'body.login .language-switcher .button{border-color:rgba(255,255,255,.5);color:#fff;background:transparent}';
		$css .= 'body.login .lu3g-firma{text-align:center;margin:26px 0 0;font-size:12px;color:rgba(255,255,255,.55)}';
		$css .= 'body.login .lu3g-firma a{color:rgba(255,255,255,.8);text-decoration:none}';
		$css .= "body.login .lu3g-firma a:hover{color:{$accent}}";
		$css .= '@media (max-width:400px){body.login #login{width:320px;padding-top:5vh}body.login #login h1 a::before{flex-basis:120px}}';
		if ( ! empty( $o['motion'] ) ) {
			$css .= self::motion_css( $bg, $accent, $panel );
		}
		return $css;
	}

	/**
	 * Interazioni animate della schermata di accesso (opzione "Animazioni", attiva di serie).
	 * Tutto è dentro prefers-reduced-motion: no-preference, quindi chi ha "riduci animazioni" nel sistema operativo
	 * vede la pagina ferma. Solo transform e opacity (GPU), niente JavaScript.
	 * - entrata in sequenza: riga d'accento in alto, logo del sito, logo LU3G con la riga divisoria, modulo, link sotto
	 *   ("backwards": finita l'entrata valgono di nuovo gli stili normali, così funziona il sollevamento all'hover);
	 * - alone del colore di accento che si muove piano dietro il modulo;
	 * - pulsante "Accedi": il colore scorre da sinistra all'hover (come i pulsanti dei siti LU3G), piccola pressione al clic;
	 * - campi: bordo d'accento che si accende e leggero sollevamento quando ci si scrive;
	 * - loghi: si sollevano appena al passaggio del mouse.
	 */
	private static function motion_css( $bg, $accent, $panel ) {
		$glow = self::rgba( $accent, '.20' );
		$ease = 'cubic-bezier(.22,1,.36,1)';
		$css  = '@media (prefers-reduced-motion:no-preference){';
		$css .= '@keyframes lu3g-in{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}';
		$css .= '@keyframes lu3g-fade{from{opacity:0}to{opacity:1}}';
		$css .= '@keyframes lu3g-bar{from{transform:scaleX(0)}to{transform:scaleX(1)}}';
		$css .= '@keyframes lu3g-glow{0%{transform:translate(-30%,-20%) scale(1)}50%{transform:translate(20%,10%) scale(1.15)}100%{transform:translate(-10%,25%) scale(.95)}}';
		// riga d'accento che si disegna da sinistra
		$css .= "body.login::before{transform-origin:left center;animation:lu3g-bar .9s {$ease} both}";
		// alone che si muove lentamente dietro al modulo
		$css .= "body.login::after{content:\"\";position:fixed;left:50%;top:40%;width:70vmax;height:70vmax;margin:-35vmax 0 0 -35vmax;border-radius:50%;"
			. "background:radial-gradient(circle,{$glow} 0%,rgba(0,0,0,0) 62%);pointer-events:none;z-index:0;animation:lu3g-glow 22s ease-in-out infinite alternate}";
		$css .= 'body.login #login,body.login .language-switcher,body.login .lu3g-firma{position:relative;z-index:1}';
		// entrata in sequenza
		$css .= "body.login #login h1 a::before{animation:lu3g-in .8s {$ease} .1s backwards;transition:transform .4s {$ease}}";
		$css .= "body.login #login h1 a::after{animation:lu3g-in .8s {$ease} .3s backwards;transition:transform .4s {$ease}}";
		$css .= "body.login #login h1 a:hover::before,body.login #login h1 a:hover::after{transform:translateY(-3px)}";
		if ( $panel ) {
			$css .= "body.login #login h1 a{animation:lu3g-fade .6s ease both}";
		}
		$css .= "body.login form{animation:lu3g-in .9s {$ease} .35s backwards}";
		$css .= 'body.login #nav,body.login #backtoblog,body.login .privacy-policy-page-link,body.login .language-switcher,body.login .lu3g-firma{animation:lu3g-fade .8s ease .8s both}';
		// campi: bordo d'accento e leggero sollevamento
		$css .= "body.login form .input,body.login input[type=\"text\"],body.login input[type=\"password\"]{transition:border-color .3s ease,box-shadow .3s ease,background-color .3s ease,transform .3s {$ease}}";
		$css .= 'body.login form .input:focus,body.login input[type="text"]:focus,body.login input[type="password"]:focus{transform:translateY(-1px)}';
		$css .= "body.login form p:focus-within label,body.login .user-pass-wrap:focus-within label{color:{$bg};transition:color .3s ease}";
		// pulsante: colore che scorre da sinistra (sfondo a due colori spostato) + pressione al clic
		$css .= "body.wp-core-ui.login .button-primary{background:linear-gradient(90deg,{$bg} 50%,{$accent} 50%) right center/202% 100% no-repeat;"
			. "transition:background-position .6s {$ease},color .4s ease,box-shadow .3s ease,transform .15s ease}";
		$css .= 'body.wp-core-ui.login .button-primary:hover,body.wp-core-ui.login .button-primary:focus{background-position:left center}';
		$css .= 'body.wp-core-ui.login .button-primary:active{transform:scale(.97)}';
		$css .= '}';
		return $css;
	}

	/* ---------------------------------------------------------------- pagina delle impostazioni */

	public static function admin_menu() {
		add_options_page( 'Accesso LU3G', 'Accesso LU3G', 'manage_options', 'lu3g-login-brand', array( __CLASS__, 'page' ) );
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=lu3g-login-brand' ) ) . '">Impostazioni</a>' );
		return $links;
	}

	public static function notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! current_user_can( 'manage_options' ) || ( $screen && 'settings_page_lu3g-login-brand' === $screen->id ) ) {
			return;
		}
		if ( empty( get_option( self::OPT )['analyzed'] ) && $screen && 'plugins' === $screen->id ) {
			echo '<div class="notice notice-info"><p><strong>LU3G Login Brand:</strong> apri <a href="'
				. esc_url( admin_url( 'options-general.php?page=lu3g-login-brand' ) )
				. '">Impostazioni → Accesso LU3G</a> e premi "Analizza il sito" per prendere logo, colori e font del sito.</p></div>';
		}
	}

	public static function admin_assets( $hook ) {
		if ( 'settings_page_lu3g-login-brand' === $hook ) {
			LU3G_Login_Admin::assets( __FILE__, self::VERSION, self::OPT );   // vedi includes/class-admin.php
		}
	}

	public static function page() {
		if ( current_user_can( 'manage_options' ) ) {
			LU3G_Login_Admin::page( self::get(), self::OPT, self::VERSION );   // vedi includes/class-admin.php
		}
	}
}

LU3G_Login_Brand::init();

// aggiornamenti dalle release GitHub (vedi includes/class-updater.php)
require_once __DIR__ . '/includes/class-fonts.php';
LU3G_Login_Fonts::init( __FILE__ );
require_once __DIR__ . '/includes/class-admin.php';
require_once __DIR__ . '/includes/class-layout-report.php';
require_once __DIR__ . '/includes/class-splash.php';
LU3G_Login_Splash::init();
require_once __DIR__ . '/includes/class-updater.php';
LU3G_Login_Brand_Updater::init( __FILE__ );
