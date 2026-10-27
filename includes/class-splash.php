<?php
/**
 * Schermata di caricamento dopo l'accesso (opzione "Schermata di caricamento", attiva di serie), con nove animazioni:
 *   linea      logo che cresce e riga di avanzamento, poi dissolvenza con scorrimento verso l'alto (predefinita)
 *   dissolvenza logo che compare piano, poi tutto sfuma
 *   pulsazione logo che pulsa due volte con un alone leggero, poi dissolvenza
 *   anello     anello che gira intorno al logo, poi dissolvenza
 *   sipario    logo che compare, poi la schermata sale come un sipario e scopre la pagina
 *   cerchio    logo che compare, poi la schermata si chiude in un cerchio verso il centro
 *   apertura   logo che compare, poi la schermata si apre dal centro verso i lati come due ante
 *   zoom       logo che compare, poi si ingrandisce verso chi guarda mentre la schermata sfuma
 *   scansione  una luce passa sul logo due volte, poi la schermata sale e sfuma
 *
 * Come funziona:
 * - Compare UNA volta, sulla prima pagina dopo il login: al login si salva un segnale sull'utente (user meta con l'ora),
 *   la prima pagina lo legge e lo cancella. Cambiando pagina non ricompare.
 * - Non rallenta: la pagina si carica normalmente sotto. Lo sfondo è html::before (presente dal primo disegno, nessun
 *   lampo); logo ed effetti sono un piccolo elemento stampato subito dopo l'apertura del body (in_admin_header in wp-admin,
 *   wp_body_open sul sito). Solo CSS, nessun JavaScript, pointer-events:none: i clic passano sempre. Durata ~1,3–1,8 s.
 * - Con "riduci animazioni" nel sistema operativo: nessun movimento, solo una dissolvenza breve.
 * - Anteprima per gli amministratori: ?lu3g_splash=1 (e &lu3g_splash_style=<nome> per provare un'altra animazione).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Splash {

	const META = 'lu3g_login_splash';
	const TTL  = 120;   // secondi: oltre questo tempo il segnale non vale più

	/** Animazioni disponibili: chiave => [nome, descrizione]. */
	public static function styles() {
		return array(
			'linea'       => array( 'Linea', 'Il logo cresce, una riga si riempie, poi la schermata sale e sfuma.' ),
			'dissolvenza' => array( 'Dissolvenza', 'Il logo compare piano, poi tutto sfuma. La più sobria.' ),
			'pulsazione'  => array( 'Pulsazione', 'Il logo pulsa due volte con un alone leggero.' ),
			'anello'      => array( 'Anello', 'Un anello gira intorno al logo, come un caricamento.' ),
			'sipario'     => array( 'Sipario', 'La schermata sale come un sipario e scopre la pagina.' ),
			'cerchio'     => array( 'Cerchio', 'La schermata si chiude in un cerchio verso il logo.' ),
			'apertura'    => array( 'Apertura', 'La schermata si apre dal centro verso i lati.' ),
			'zoom'        => array( 'Zoom', 'Il logo si ingrandisce verso di te mentre tutto sfuma.' ),
			'scansione'   => array( 'Scansione', 'Una luce passa sul logo, poi la schermata sale.' ),
		);
	}

	private static $style = '';   // animazione da stampare in questa pagina ('' = nessuna)

	public static function init() {
		add_action( 'wp_login', array( __CLASS__, 'mark' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'maybe_print' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'maybe_print' ), 1 );   // se dopo il login si torna al sito
		add_action( 'in_admin_header', array( __CLASS__, 'markup' ), 1 );
		add_action( 'wp_body_open', array( __CLASS__, 'markup' ), 1 );
	}

	/** Al login: segnale sull'utente (solo se l'opzione è attiva). */
	public static function mark( $user_login, $user ) {
		$o = LU3G_Login_Brand::get();
		if ( ! empty( $o['splash'] ) && $user instanceof WP_User ) {
			update_user_meta( $user->ID, self::META, time() );
		}
	}

	/** Prima pagina dopo il login: stampa lo stile e cancella il segnale. */
	public static function maybe_print() {
		if ( self::$style || ! is_user_logged_in() || wp_doing_ajax() || ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) ) {
			return;
		}
		$uid     = get_current_user_id();
		$preview = isset( $_GET['lu3g_splash'] ) && is_admin() && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo anteprima visiva
		$when    = (int) get_user_meta( $uid, self::META, true );
		if ( ! $preview && ! $when ) {
			return;
		}
		if ( $when ) {
			delete_user_meta( $uid, self::META );   // una volta sola
		}
		if ( ! $preview && time() - $when > self::TTL ) {
			return;
		}
		$o     = LU3G_Login_Brand::get();
		$style = $o['splash_style'] ?? 'linea';
		if ( $preview && isset( $_GET['lu3g_splash_style'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$style = sanitize_key( wp_unslash( $_GET['lu3g_splash_style'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		self::$style = isset( self::styles()[ $style ] ) ? $style : 'linea';
		echo '<style id="lu3g-splash-css">' . self::css( $o, self::$style ) . '</style>' . "\n";
	}

	/** Logo ed effetti, subito dopo l'apertura della pagina. */
	public static function markup() {
		if ( self::$style ) {
			echo '<div class="lu3g-splash lu3g-splash--' . esc_attr( self::$style ) . '" aria-hidden="true"><span class="lu3g-splash__fx"></span><span class="lu3g-splash__logo"></span></div>';
		}
	}

	public static function css( array $o, $style ) {
		$report = 'report' === $o['layout'];
		$bg     = $report ? LU3G_Login_Report::PAPER : $o['bg'];
		$line   = $report ? LU3G_Login_Report::INK : $o['accent'];
		$logo   = $report ? LU3G_Login_Brand::lu3g_logo( 'light' ) : $o['logo'];
		$logo   = 0 === strpos( (string) $logo, 'data:' ) ? $logo : esc_url( $logo );
		$w      = $report ? 64 : 200;   // area del logo: quadrata per LU3G, larga per i loghi dei siti
		$h      = $report ? 64 : 80;
		$e      = 'cubic-bezier(.22,1,.36,1)';
		$glow   = self::rgba( $line, '.35' );

		// fondo dal primo disegno (html::before) e contenitore di logo ed effetti (.lu3g-splash), centrati
		$css  = "html::before{content:\"\";position:fixed;inset:0;z-index:2147483646;pointer-events:none;background:{$bg}}";
		$css .= '.lu3g-splash{position:fixed;inset:0;z-index:2147483647;pointer-events:none;display:grid;place-items:center}';
		$css .= ".lu3g-splash__logo{grid-area:1/1;width:{$w}px;height:{$h}px;background:url(\"{$logo}\") center/contain no-repeat}";
		$css .= '.lu3g-splash__fx{grid-area:1/1}';

		// animazioni comuni
		$css .= '@keyframes lu3g-s-out{to{opacity:0;visibility:hidden}}';
		$css .= '@keyframes lu3g-s-out-up{to{opacity:0;transform:translateY(-24px);visibility:hidden}}';
		$css .= '@keyframes lu3g-s-in{from{opacity:0;transform:scale(.8)}to{opacity:1;transform:none}}';
		$css .= '@keyframes lu3g-s-fade-in{from{opacity:0}to{opacity:1}}';

		$out = "lu3g-s-out .5s ease 1.25s forwards";   // uscita predefinita (dissolvenza)
		switch ( $style ) {
			case 'dissolvenza':
				$css .= '.lu3g-splash__logo{animation:lu3g-s-fade-in .6s ease .1s both}';
				$out  = 'lu3g-s-out .6s ease 1.1s forwards';
				break;

			case 'pulsazione':
				$css .= "@keyframes lu3g-s-pulse{0%,100%{transform:scale(1);filter:drop-shadow(0 0 0 transparent)}50%{transform:scale(1.08);filter:drop-shadow(0 0 18px {$glow})}}";
				$css .= ".lu3g-splash__logo{animation:lu3g-s-in .5s {$e} both,lu3g-s-pulse .55s ease-in-out .5s 2}";
				$out  = 'lu3g-s-out .5s ease 1.6s forwards';
				break;

			case 'anello':
				$ring = $report ? 104 : 140;
				$css .= '@keyframes lu3g-s-spin{to{transform:rotate(360deg)}}';
				$css .= ".lu3g-splash__fx{width:{$ring}px;height:{$ring}px;border-radius:50%;border:2px solid " . self::rgba( $line, '.15' ) . ";border-top-color:{$line};"
					. 'animation:lu3g-s-fade-in .3s ease both,lu3g-s-spin .8s linear infinite}';
				$css .= ".lu3g-splash__logo{width:" . ( $report ? 48 : 90 ) . "px;height:" . ( $report ? 48 : 60 ) . "px;animation:lu3g-s-in .5s {$e} both}";
				$out  = 'lu3g-s-out .5s ease 1.4s forwards';
				break;

			case 'sipario':
				$css .= '@keyframes lu3g-s-curtain{to{transform:translateY(-100%)}}';
				$css .= ".lu3g-splash__logo{animation:lu3g-s-in .5s {$e} both}";
				$out  = 'lu3g-s-curtain .75s cubic-bezier(.76,0,.24,1) 1.1s forwards';
				break;

			case 'cerchio':
				$css .= '@keyframes lu3g-s-iris{from{clip-path:circle(75% at 50% 50%)}to{clip-path:circle(0% at 50% 50%);visibility:hidden}}';
				$css .= ".lu3g-splash__logo{animation:lu3g-s-in .5s {$e} both}";
				$out  = 'lu3g-s-iris .7s cubic-bezier(.76,0,.24,1) 1.1s forwards';
				break;

			case 'apertura':
				$css .= '@keyframes lu3g-s-doors{from{clip-path:inset(0 0 0 0)}to{clip-path:inset(0 50% 0 50%);visibility:hidden}}';
				$css .= ".lu3g-splash__logo{animation:lu3g-s-in .5s {$e} both}";
				$out  = 'lu3g-s-doors .75s cubic-bezier(.76,0,.24,1) 1.1s forwards';
				break;

			case 'zoom':
				// il logo cresce per conto suo; fondo e contenitore sfumano insieme
				$css .= '@keyframes lu3g-s-zoom{to{transform:scale(6);opacity:0}}';
				$css .= ".lu3g-splash__logo{animation:lu3g-s-in .5s {$e} both,lu3g-s-zoom .7s cubic-bezier(.7,0,.84,0) 1.05s forwards}";
				$out  = 'lu3g-s-out .45s ease 1.3s forwards';
				break;

			case 'scansione':
				// una fascia che scorre sopra il logo, visibile solo dentro la sua forma (maschera): scura sui loghi chiari,
				// chiara sui loghi scuri
				$light = $report || 'dark' !== ( $o['logo_tone'] ?? 'light' );
				$band  = $light ? self::rgba( $bg, '.7' ) : 'rgba(255,255,255,.85)';
				$css  .= '@keyframes lu3g-s-scan{from{background-position:150% 0}to{background-position:-50% 0}}';
				$css  .= ".lu3g-splash__fx{position:relative;z-index:1;width:{$w}px;height:{$h}px;background:linear-gradient(105deg,transparent 35%,{$band} 50%,transparent 65%) 150% 0/250% 100% no-repeat;"
					. "-webkit-mask:url(\"{$logo}\") center/contain no-repeat;mask:url(\"{$logo}\") center/contain no-repeat;"
					. 'animation:lu3g-s-scan .65s ease-in-out .35s 2 both}';
				$css  .= ".lu3g-splash__logo{animation:lu3g-s-in .5s {$e} both}";
				$out   = "lu3g-s-out-up .55s {$e} 1.4s forwards";
				break;

			default: // linea
				$bar  = $report ? 120 : 160;
				$css .= '@keyframes lu3g-s-bar{from{transform:scaleX(0)}to{transform:scaleX(1)}}';
				$css .= ".lu3g-splash__fx{width:{$bar}px;height:2px;margin-top:" . ( $h + 48 ) . "px;border-radius:2px;background:{$line};transform-origin:left center;"
					. "animation:lu3g-s-bar 1.1s {$e} .1s both}";
				$css .= ".lu3g-splash__logo{animation:lu3g-s-in .7s {$e} both}";
				$out  = "lu3g-s-out-up .55s {$e} 1.25s forwards";
		}
		// stessa uscita per il fondo e per logo/effetti, così si muovono insieme
		$css .= "html::before,.lu3g-splash{animation:{$out}}";

		// riduci animazioni: niente movimento, solo una dissolvenza breve
		$css .= '@media (prefers-reduced-motion:reduce){.lu3g-splash__logo,.lu3g-splash__fx{animation:none!important}.lu3g-splash__fx{display:none}'
			. 'html::before,.lu3g-splash{animation:lu3g-s-out .3s ease .6s forwards!important;transform:none!important;clip-path:none!important}}';
		return $css;
	}

	private static function rgba( $hex, $alpha ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return 'rgba(' . hexdec( substr( $hex, 0, 2 ) ) . ',' . hexdec( substr( $hex, 2, 2 ) ) . ',' . hexdec( substr( $hex, 4, 2 ) ) . ',' . $alpha . ')';
	}
}
