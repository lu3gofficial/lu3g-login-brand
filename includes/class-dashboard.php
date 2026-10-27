<?php
/**
 * Colori del tema in tutta la bacheca di WordPress (opzione "Colori in tutta la bacheca", spenta di serie).
 *
 * Prende i colori dall'aspetto scelto per la schermata di accesso:
 * - "Tema LU3G": menu e barra quasi neri (#09090b), voce attiva bianca, pulsanti neri, come report.lu3g.com;
 * - "Colori del sito": menu e barra nel colore di fondo del sito, voce attiva e pulsanti nel colore di accento.
 * Colora la "cornice" della bacheca (menu laterale, barra in alto, anche sul sito), pulsanti principali, link, campi
 * attivi e il colore dei componenti dell'editor a blocchi (--wp-admin-theme-color). Le pagine restano chiare: così i
 * plugin che disegnano le proprie schermate non si rompono. I testi sono sempre calcolati per restare leggibili.
 *
 * Lo stile esce in admin_head con priorità 99, dopo lo schema colori scelto nel profilo utente, quindi lo sostituisce.
 * Con "Animazioni nella bacheca" (attiva di serie quando i colori sono accesi) aggiunge piccole transizioni: entrata della
 * pagina, sottomenu e menu della barra che compaiono, pulsanti con pressione al clic, avvisi e riquadri della bacheca che
 * salgono. Tutto dentro prefers-reduced-motion: no-preference, mai nell'editor a blocchi.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Dashboard {

	public static function init() {
		add_action( 'admin_head', array( __CLASS__, 'admin_styles' ), 99 );
		add_action( 'wp_head', array( __CLASS__, 'bar_styles' ), 99 );   // barra in alto quando si guarda il sito da collegati
	}

	/** Colori della bacheca ricavati dall'aspetto scelto. */
	public static function palette( array $o ) {
		$b = 'LU3G_Login_Brand';
		if ( 'report' === $o['layout'] ) {
			return array(
				'menu'     => LU3G_Login_Report::PAPER,
				'sub'      => LU3G_Login_Report::SURFACE,
				'hover'    => LU3G_Login_Report::RAISED,
				'text'     => LU3G_Login_Report::LABEL,
				'subtext'  => LU3G_Login_Report::MUTED,
				'bright'   => LU3G_Login_Report::INK,
				'acc'      => LU3G_Login_Report::INK,     // voce attiva: bianca con testo nero
				'on_acc'   => LU3G_Login_Report::PAPER,
				'btn'      => LU3G_Login_Report::PAPER,   // pulsanti: neri con testo bianco
				'btn_h'    => '#27272a',
				'on_btn'   => LU3G_Login_Report::INK,
				'link'     => '#18181b',
				'link_h'   => '#52525b',
			);
		}
		$bg     = $o['bg'];
		$dark   = $b::luminance( $bg ) < 0.4;
		$acc    = $o['accent'];
		$deep   = $b::mix( $bg, '#000000', 0.38 );
		$on_acc = $b::contrast( $acc, '#ffffff' ) >= $b::contrast( $acc, $deep ) ? '#ffffff' : $deep;   // testo sulla voce attiva e sui pulsanti
		$link   = $b::readable( $acc, '#ffffff', 4.5 );
		return array(
			'menu'    => $bg,
			'sub'     => $b::mix( $bg, $dark ? '#000000' : '#ffffff', 0.28 ),
			'hover'   => $b::mix( $bg, $dark ? '#ffffff' : '#000000', 0.10 ),
			'text'    => $dark ? '#f0f0f1' : '#1d2327',
			'subtext' => $dark ? 'rgba(240,240,241,.72)' : 'rgba(29,35,39,.72)',
			'bright'  => $b::readable( $acc, $b::mix( $bg, $dark ? '#000000' : '#ffffff', 0.28 ), 4.5 ),
			'acc'     => $acc,
			'on_acc'  => $on_acc,
			'btn'     => $acc,
			'btn_h'   => $b::mix( $acc, $dark ? $bg : '#000000', 0.2 ),
			'on_btn'  => $on_acc,
			'link'    => $link,
			'link_h'  => $b::mix( $link, '#000000', 0.25 ),
		);
	}

	public static function admin_styles() {
		$o = LU3G_Login_Brand::get();
		if ( empty( $o['dashboard'] ) ) {
			return;
		}
		$c   = self::palette( $o );
		$css = self::menu_css( $c ) . self::bar_css( $c ) . self::content_css( $c );
		if ( ! empty( $o['dashboard_motion'] ) ) {
			$css .= self::motion_css();
		}
		echo '<style id="lu3g-dashboard">' . $css . '</style>' . "\n";
	}

	public static function bar_styles() {
		$o = LU3G_Login_Brand::get();
		if ( empty( $o['dashboard'] ) || ! is_admin_bar_showing() ) {
			return;
		}
		echo '<style id="lu3g-dashboard-bar">' . self::bar_css( self::palette( $o ) ) . '</style>' . "\n";
	}

	/** Menu laterale (stessi selettori degli schemi colori di WordPress). */
	private static function menu_css( array $c ) {
		extract( $c ); // phpcs:ignore WordPress.PHP.DontExtract -- variabili locali dalla tavolozza qui sopra
		$css  = "#adminmenuback,#adminmenuwrap,#adminmenu{background:{$menu}}";
		$css .= "#adminmenu a{color:{$text}}";
		$css .= "#adminmenu div.wp-menu-image:before{color:{$subtext}}";
		$css .= "#adminmenu a:hover,#adminmenu li.menu-top:hover,#adminmenu li.opensub>a.menu-top,#adminmenu li>a.menu-top:focus{color:{$bright};background-color:{$hover}}";
		$css .= "#adminmenu li.menu-top:hover div.wp-menu-image:before,#adminmenu li.opensub>a.menu-top div.wp-menu-image:before,#adminmenu li>a.menu-top:focus div.wp-menu-image:before{color:{$bright}}";
		$css .= "#adminmenu .wp-submenu,#adminmenu .wp-has-current-submenu .wp-submenu,#adminmenu .wp-has-current-submenu.opensub .wp-submenu,"
			. "#adminmenu a.wp-has-current-submenu:focus+.wp-submenu,.folded #adminmenu .wp-has-current-submenu .wp-submenu{background:{$sub}}";
		$css .= "#adminmenu li.wp-has-submenu.wp-not-current-submenu.opensub:hover:after,#adminmenu li.wp-has-submenu.wp-not-current-submenu:focus-within:after{border-right-color:{$sub}}";
		$css .= "#adminmenu .wp-submenu .wp-submenu-head{color:{$text}}";
		$css .= "#adminmenu .wp-submenu a,#adminmenu .wp-has-current-submenu .wp-submenu a,.folded #adminmenu .wp-has-current-submenu .wp-submenu a,"
			. "#adminmenu a.wp-has-current-submenu:focus+.wp-submenu a,#adminmenu .wp-has-current-submenu.opensub .wp-submenu a{color:{$subtext}}";
		$css .= "#adminmenu .wp-submenu a:focus,#adminmenu .wp-submenu a:hover,#adminmenu .wp-has-current-submenu .wp-submenu a:focus,#adminmenu .wp-has-current-submenu .wp-submenu a:hover,"
			. ".folded #adminmenu .wp-has-current-submenu .wp-submenu a:focus,.folded #adminmenu .wp-has-current-submenu .wp-submenu a:hover,"
			. "#adminmenu a.wp-has-current-submenu:focus+.wp-submenu a:focus,#adminmenu a.wp-has-current-submenu:focus+.wp-submenu a:hover,"
			. "#adminmenu .wp-has-current-submenu.opensub .wp-submenu a:focus,#adminmenu .wp-has-current-submenu.opensub .wp-submenu a:hover{color:{$bright}}";
		$css .= "#adminmenu .wp-submenu li.current a,#adminmenu a.wp-has-current-submenu:focus+.wp-submenu li.current a,#adminmenu .wp-has-current-submenu.opensub .wp-submenu li.current a,"
			. "#adminmenu .wp-submenu li.current a:hover,#adminmenu .wp-submenu li.current a:focus{color:{$bright};font-weight:600}";
		// voce attiva
		$css .= "#adminmenu li.current a.menu-top,#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.wp-has-current-submenu .wp-submenu .wp-submenu-head,"
			. ".folded #adminmenu li.current.menu-top{color:{$on_acc};background:{$acc}}";
		$css .= "#adminmenu li.wp-has-current-submenu div.wp-menu-image:before,#adminmenu a.current:hover div.wp-menu-image:before,#adminmenu li.current div.wp-menu-image:before,"
			. "#adminmenu li.wp-has-current-submenu a:focus div.wp-menu-image:before,#adminmenu li.wp-has-current-submenu.opensub div.wp-menu-image:before,"
			. "#adminmenu li.wp-has-current-submenu:hover div.wp-menu-image:before{color:{$on_acc}}";
		$css .= "#adminmenu .awaiting-mod,#adminmenu .update-plugins{background:{$acc};color:{$on_acc}}";
		$css .= "#adminmenu li.current a .awaiting-mod,#adminmenu li a.wp-has-current-submenu .update-plugins,#adminmenu li:hover a .awaiting-mod,#adminmenu li.menu-top:hover>a .update-plugins{background:{$menu};color:{$text}}";
		$css .= "#collapse-button{color:{$subtext}}#collapse-button:hover,#collapse-button:focus{color:{$bright}}";
		return $css;
	}

	/** Barra in alto (in wp-admin e sul sito). */
	private static function bar_css( array $c ) {
		extract( $c ); // phpcs:ignore WordPress.PHP.DontExtract
		$css  = "#wpadminbar{background:{$menu};color:{$text}}";
		$css .= "#wpadminbar .ab-item,#wpadminbar a.ab-item,#wpadminbar>#wp-toolbar span.ab-label,#wpadminbar>#wp-toolbar span.noticon{color:{$text}}";
		$css .= "#wpadminbar .ab-icon,#wpadminbar .ab-icon:before,#wpadminbar .ab-item:before,#wpadminbar .ab-item:after{color:{$subtext}}";
		$css .= "#wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item,#wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus,#wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus,"
			. "#wpadminbar .ab-top-menu>li.hover>.ab-item,#wpadminbar.nojs .ab-top-menu>li.menupop:hover>.ab-item,#wpadminbar .ab-top-menu>li.menupop.hover>.ab-item{background:{$sub};color:{$bright}}";
		$css .= "#wpadminbar:not(.mobile)>#wp-toolbar li:hover span.ab-label,#wpadminbar>#wp-toolbar li.hover span.ab-label,#wpadminbar:not(.mobile)>#wp-toolbar a:focus span.ab-label{color:{$bright}}";
		$css .= "#wpadminbar:not(.mobile) li:hover .ab-icon:before,#wpadminbar:not(.mobile) li:hover .ab-item:before,#wpadminbar:not(.mobile) li:hover .ab-item:after,"
			. "#wpadminbar:not(.mobile) li:hover #adminbarsearch:before,#wpadminbar li.hover .ab-icon:before,#wpadminbar li.hover .ab-item:before,#wpadminbar li.hover .ab-item:after{color:{$bright}}";
		$css .= "#wpadminbar .menupop .ab-sub-wrapper,#wpadminbar .shortlink-input{background:{$sub}}";
		$css .= "#wpadminbar .quicklinks .menupop ul.ab-sub-secondary,#wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu{background:{$hover}}";
		$css .= "#wpadminbar .ab-submenu .ab-item,#wpadminbar .quicklinks .menupop ul li a,#wpadminbar .quicklinks .menupop.hover ul li a,#wpadminbar.nojs .quicklinks .menupop:hover ul li a{color:{$subtext}}";
		$css .= "#wpadminbar .quicklinks .menupop ul li a:hover,#wpadminbar .quicklinks .menupop ul li a:focus,#wpadminbar .quicklinks .menupop ul li a:hover strong,"
			. "#wpadminbar .quicklinks .menupop.hover ul li a:hover,#wpadminbar .quicklinks .menupop.hover ul li a:focus,#wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover,"
			. "#wpadminbar li:hover .ab-item:before,#wpadminbar .menupop .menupop>.ab-item:hover:before{color:{$bright}}";
		$css .= "#wpadminbar #adminbarsearch:before{color:{$subtext}}";
		$css .= "#wpadminbar #wp-admin-bar-user-info .display-name{color:{$bright}}#wpadminbar #wp-admin-bar-user-info .username{color:{$subtext}}";
		return $css;
	}

	/** Contenuto: pulsanti principali, link, campi attivi, colore dei componenti dell'editor. */
	private static function content_css( array $c ) {
		extract( $c ); // phpcs:ignore WordPress.PHP.DontExtract
		$rgb  = implode( ',', LU3G_Login_Brand::rgb( $btn ) );
		$css  = "body.wp-admin.wp-core-ui{--wp-admin-theme-color:{$btn};--wp-admin-theme-color--rgb:{$rgb};"
			. "--wp-admin-theme-color-darker-10:{$btn_h};--wp-admin-theme-color-darker-20:" . LU3G_Login_Brand::mix( $btn_h, '#000000', 0.15 ) . '}';
		// link con la stessa forza di WordPress (a{}), così i plugin con colori propri restano come sono
		$css .= "a{color:{$link}}a:hover,a:active,a:focus{color:{$link_h}}";
		$css .= ".wp-core-ui .button-primary{background:{$btn};border-color:{$btn};color:{$on_btn}}";
		$css .= ".wp-core-ui .button-primary:hover,.wp-core-ui .button-primary:focus,.wp-core-ui .button-primary.hover,.wp-core-ui .button-primary.focus{background:{$btn_h};border-color:{$btn_h};color:{$on_btn}}";
		$css .= ".wp-core-ui .button-primary:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px {$btn}}";
		$css .= ".wp-core-ui .button-primary:active,.wp-core-ui .button-primary.active{background:{$btn_h};border-color:{$btn_h};color:{$on_btn}}";
		$css .= ".wp-core-ui .button,.wp-core-ui .button-secondary,.wrap .page-title-action{color:{$link};border-color:{$link}}";
		$css .= ".wp-core-ui .button:hover,.wp-core-ui .button-secondary:hover,.wrap .page-title-action:hover{color:{$link_h};border-color:{$link_h}}";
		$css .= ".wp-core-ui .button:focus,.wp-core-ui .button-secondary:focus,.wrap .page-title-action:focus{border-color:{$link};box-shadow:0 0 0 1px {$link};color:{$link_h}}";
		$css .= "input[type=text]:focus,input[type=password]:focus,input[type=email]:focus,input[type=search]:focus,input[type=url]:focus,input[type=number]:focus,"
			. "input[type=tel]:focus,input[type=date]:focus,select:focus,textarea:focus{border-color:{$link};box-shadow:0 0 0 1px {$link}}";
		$css .= "input[type=checkbox],input[type=radio]{accent-color:{$link}}";
		$css .= "input[type=radio]:checked::before{background-color:{$link}}";
		$css .= ".notice-info,.notice.is-dismissible.notice-info{border-left-color:{$link}}";
		return $css;
	}

	private static function motion_css() {
		$e    = 'cubic-bezier(.22,1,.36,1)';
		$css  = '@media (prefers-reduced-motion:no-preference){';
		$css .= '@keyframes lu3g-d-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}';
		$css .= '@keyframes lu3g-d-pop{from{opacity:0;transform:translateX(-6px)}to{opacity:1;transform:none}}';
		$css .= '@keyframes lu3g-d-drop{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}';
		// pagina che entra (mai nell'editor a blocchi, che ha elementi fissi propri)
		$css .= "body:not(.block-editor-page) #wpbody-content>.wrap{animation:lu3g-d-in .4s {$e} backwards}";
		// menu laterale: colori che sfumano, icona che si sposta appena, sottomenu a comparsa
		$css .= '#adminmenu a,#adminmenu div.wp-menu-image:before{transition:color .2s ease,background-color .2s ease}';
		$css .= "#adminmenu div.wp-menu-image{transition:transform .3s {$e}}#adminmenu li.menu-top:hover div.wp-menu-image{transform:translateX(2px)}";
		$css .= "#adminmenu .wp-not-current-submenu.opensub .wp-submenu,#adminmenu .wp-not-current-submenu:hover .wp-submenu{animation:lu3g-d-pop .2s {$e}}";
		// barra in alto: menu a tendina che scendono
		$css .= "#wpadminbar .menupop.hover>.ab-sub-wrapper,#wpadminbar.nojs .menupop:hover>.ab-sub-wrapper{animation:lu3g-d-drop .2s {$e}}";
		// pulsanti: colore che sfuma e pressione al clic
		$css .= '.wp-core-ui .button,.wp-core-ui .button-primary,.wrap .page-title-action{transition:background-color .2s ease,border-color .2s ease,color .2s ease,box-shadow .2s ease,transform .12s ease}';
		$css .= '.wp-core-ui .button:active,.wp-core-ui .button-primary:active,.wrap .page-title-action:active{transform:scale(.97)}';
		// avvisi e riquadri della bacheca che salgono uno dopo l'altro
		$css .= "#wpbody-content .notice,#wpbody-content div.updated,#wpbody-content div.error{animation:lu3g-d-in .45s {$e} .1s backwards}";
		$css .= "#dashboard-widgets .postbox{animation:lu3g-d-in .5s {$e} backwards}";
		for ( $i = 1; $i <= 6; $i++ ) {
			$css .= '#dashboard-widgets .postbox:nth-child(' . $i . '){animation-delay:' . ( 0.05 * $i ) . 's}';
		}
		$css .= '}';
		return $css;
	}
}
