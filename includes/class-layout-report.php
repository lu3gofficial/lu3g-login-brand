<?php
/**
 * Aspetto "Report LU3G": la schermata di accesso riprodotta da https://report.lu3g.com/login.
 *
 * Fondo quasi nero #09090b, colonna centrale di 390px, logo LU3G 46px allineato a sinistra, titolo in Manrope 30px,
 * etichette 13px, campi scuri (#171719, bordo #2b2b30, raggio 6px, alti 46px), occhio della password con l'icona originale
 * (Lucide "eye"), pulsante bianco a tutta larghezza con freccia in alto a destra, link piccoli centrati sotto il modulo.
 * Font DM Sans e Manrope (variabili, licenza SIL OFL) inclusi nel plugin in assets/fonts: nessuna chiamata esterna.
 *
 * Sul markup di WordPress: il logo è il link nel titolo h1 della pagina; il titolo del modulo arriva dal filtro
 * login_message; etichetta "Email", segnaposto, testo del pulsante e riga dei link si impostano con un piccolo script
 * in fondo alla pagina (senza JavaScript la pagina resta comunque completa e funzionante).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Report {

	/* colori di report.lu3g.com (variabili CSS dell'app) */
	const PAPER   = '#09090b';   // fondo
	const INK     = '#fafafa';   // testo
	const MUTED   = '#a1a1aa';   // testo secondario, segnaposto, bordo attivo
	const LABEL   = '#d4d4d8';   // etichette, hover del pulsante
	const LINE    = '#2b2b30';   // bordi
	const SURFACE = '#171719';   // campi
	const RAISED  = '#202023';   // hover dell'occhio

	/** Titolo sopra il modulo, secondo la pagina (accesso, password dimenticata, nuova password, registrazione). */
	public static function title( array $o ) {
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$titles = array(
			'lostpassword' => 'Recupera la password',
			'retrievepassword' => 'Recupera la password',
			'rp'           => 'Nuova password',
			'resetpass'    => 'Nuova password',
			'register'     => 'Crea un account',
			'confirmaction' => 'Conferma',
		);
		return $titles[ $action ] ?? ( $o['title'] ? $o['title'] : 'Accedi al sito' );
	}

	private static function svg_url( $svg ) {
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/** Icone Lucide usate da report.lu3g.com (occhio, occhio barrato, freccia in alto a destra). */
	private static function icon( $name, $color ) {
		$paths = array(
			'eye'     => '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>',
			'eye-off' => '<path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>',
			'arrow'   => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
			'check'   => '<path d="M20 6 9 17l-5-5"/>',
		);
		return self::svg_url( '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="' . $color
			. '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</svg>' );
	}

	public static function css( array $o, $plugin_file, $lu3g_logo ) {
		$fonts = plugins_url( 'assets/fonts/', $plugin_file );
		$p     = self::PAPER;
		$ink   = self::INK;
		$mut   = self::MUTED;
		$lab   = self::LABEL;
		$line  = self::LINE;
		$sur   = self::SURFACE;
		$rai   = self::RAISED;
		$dm    = '"DM Sans Variable",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif';

		// font (stessi file dell'app: latin, variabili)
		$css  = "@font-face{font-family:\"DM Sans Variable\";font-style:normal;font-display:swap;font-weight:100 1000;src:url(\"{$fonts}dm-sans-latin-wght.woff2\") format(\"woff2-variations\")}";
		$css .= "@font-face{font-family:\"Manrope Variable\";font-style:normal;font-display:swap;font-weight:200 800;src:url(\"{$fonts}manrope-latin-wght.woff2\") format(\"woff2-variations\")}";

		// pagina e colonna centrale
		$css .= "html{background:{$p}}";
		$css .= "body.login{background:{$p};color:{$ink};font-family:{$dm};font-size:14px;-webkit-font-smoothing:antialiased;"
			. 'display:flex;flex-direction:column;justify-content:center;min-height:100vh;min-height:100dvh;margin:0}';
		$css .= 'body.login #login{width:auto;max-width:390px;margin:0 auto;padding:48px 24px;box-sizing:content-box}';
		$css .= 'body.login #login *{box-sizing:border-box}';

		// logo LU3G 46px, a sinistra
		$css .= 'body.login #login h1.wp-login-logo,body.login #login h1{margin:0 0 40px;padding:0;text-align:left}';
		$css .= "body.login #login h1 a{display:block;width:46px;height:46px;margin:0;padding:0;background:url(\"{$lu3g_logo}\") center/contain no-repeat;"
			. 'text-indent:-9999px;overflow:hidden;outline-offset:3px}';

		// titolo
		$css .= "body.login .lu3g-titolo{margin:0 0 28px;font-family:\"Manrope Variable\",sans-serif;font-size:30px;font-weight:700;line-height:1.3;letter-spacing:-1px;color:{$ink}}";

		// modulo
		$css .= 'body.login form{margin:0;padding:0;background:none;border:0;box-shadow:none;overflow:visible;display:grid;gap:20px}';
		$css .= 'body.login form p,body.login form .user-pass-wrap{margin:0}';
		$css .= "body.login form label{display:block;margin:0 0 8px;color:{$lab};font-size:13px;font-weight:500;line-height:normal}";
		$css .= "body.login form .input,body.login input[type=\"text\"],body.login input[type=\"email\"],body.login input[type=\"password\"]{"
			. "width:100%;min-height:46px;margin:0;padding:11px 12px;background:{$sur};border:1px solid {$line};border-radius:6px;box-shadow:none;"
			. "color:{$ink};font-family:{$dm};font-size:14px;font-weight:500;line-height:normal;outline:none;transition:border-color .15s,box-shadow .15s}";
		$css .= "body.login form .input::placeholder,body.login input::placeholder{color:{$mut};opacity:1}";
		$css .= "body.login form .input:focus,body.login input[type=\"text\"]:focus,body.login input[type=\"email\"]:focus,body.login input[type=\"password\"]:focus{"
			. "border-color:{$mut};box-shadow:0 0 0 3px rgba(255,255,255,.07);outline:none}";
		// riempimento automatico del browser: resta scuro
		$css .= "body.login input:-webkit-autofill{-webkit-text-fill-color:{$ink};box-shadow:0 0 0 100px {$sur} inset;caret-color:{$ink}}";

		// password: occhio a destra, come nell'app
		$css .= 'body.login .wp-pwd{position:relative}';
		$css .= 'body.login .wp-pwd input.password-input{padding-right:46px}';
		$css .= "body.login .button.wp-hide-pw{position:absolute;top:23px;right:4px;transform:translateY(-50%);width:40px;height:40px;min-height:0;margin:0;padding:0;"
			. "display:inline-flex;align-items:center;justify-content:center;background:transparent;border:0;border-radius:6px;box-shadow:none;color:{$lab}}";
		$css .= "body.login .button.wp-hide-pw:hover{background:{$rai};color:{$ink}}";
		$css .= "body.login .button.wp-hide-pw:focus{background:transparent;border:0;box-shadow:none;outline:3px solid {$mut};outline-offset:3px}";
		$css .= 'body.login .button.wp-hide-pw .dashicons{width:18px;height:18px;font-size:0;top:0;background:currentColor;'
			. '-webkit-mask:url("' . self::icon( 'eye', '#000' ) . '") center/contain no-repeat;mask:url("' . self::icon( 'eye', '#000' ) . '") center/contain no-repeat}';
		$css .= 'body.login .button.wp-hide-pw .dashicons-hidden{-webkit-mask-image:url("' . self::icon( 'eye-off', '#000' ) . '");mask-image:url("' . self::icon( 'eye-off', '#000' ) . '")}';
		$css .= "body.login .caps-warning{background:{$rai};color:{$ink};border:1px solid {$line};border-radius:6px}";
		$css .= "body.login .caps-warning svg{fill:{$ink};stroke:{$ink}}";

		// "Ricordami" (non c'è nell'app: tenuto, nello stesso stile sobrio)
		$css .= "body.login .forgetmenot{display:flex;align-items:center;gap:8px;float:none;color:{$lab}}";
		$css .= 'body.login .forgetmenot label{display:inline;margin:0;font-size:13px}';
		$css .= "body.login input[type=\"checkbox\"]{width:16px;height:16px;min-width:16px;margin:0;background:{$sur};border:1px solid {$line};border-radius:4px;box-shadow:none}";
		$css .= "body.login input[type=\"checkbox\"]:focus{border-color:{$mut};box-shadow:0 0 0 3px rgba(255,255,255,.07)}";
		$css .= 'body.login input[type="checkbox"]:checked::before{content:"";display:block;width:12px;height:12px;margin:1px;'
			. 'background:url("' . self::icon( 'check', $ink ) . '") center/contain no-repeat}';
		$css .= "body.login .wp-tooltip__toggle{color:{$mut};background:transparent;border:0;box-shadow:none}";
		$css .= "body.login .wp-tooltip__bubble{background:{$sur};color:{$ink};border:1px solid {$line};border-radius:6px}";

		// pulsante: bianco, a tutta larghezza, testo a sinistra e freccia a destra
		$css .= 'body.login p.submit{margin:7px 0 0;padding:0;clear:both}';
		$css .= "body.wp-core-ui.login .button-primary,body.wp-core-ui.login #wp-submit{float:none;display:block;width:100%;min-height:46px;height:auto;margin:0;"
			. "padding:13px 46px 13px 17px;text-align:left;background:{$ink} url(\"" . self::icon( 'arrow', $p ) . "\") right 17px center/18px 18px no-repeat;"
			. "border:1px solid {$ink};border-radius:7px;box-shadow:0 1px 2px rgba(0,0,0,.125);text-shadow:none;color:{$p};"
			. "font-family:{$dm};font-size:12px;font-weight:600;line-height:18px;letter-spacing:normal;text-transform:none;cursor:pointer;transition:background-color .15s,transform .15s}";
		$css .= "body.wp-core-ui.login .button-primary:hover,body.wp-core-ui.login .button-primary:focus{background-color:{$lab};border-color:{$lab};color:{$p};box-shadow:0 1px 2px rgba(0,0,0,.125)}";
		$css .= "body.wp-core-ui.login .button-primary:focus-visible{outline:3px solid {$mut};outline-offset:3px}";

		// link sotto il modulo, in una riga centrata
		$css .= "body.login .lu3g-link{display:flex;flex-wrap:wrap;justify-content:center;gap:18px;margin:18px 0 0;color:{$mut};font-size:11px}";
		$css .= "body.login .lu3g-link a{color:{$ink};text-decoration:none;font-size:11px}";
		$css .= 'body.login .lu3g-link a:hover{opacity:.75}';
		$css .= "body.login .lu3g-link a:focus-visible{outline:3px solid {$mut};outline-offset:3px;box-shadow:none}";
		$css .= "body.login #nav,body.login #backtoblog,body.login .privacy-policy-page-link{margin:18px 0 0;padding:0;text-align:center;font-size:11px}";
		$css .= "body.login #nav a,body.login #backtoblog a,body.login .privacy-policy-page-link a{color:{$ink};text-decoration:none}";
		$css .= 'body.lu3g-js #nav,body.lu3g-js #backtoblog,body.lu3g-js .privacy-policy-page-link{display:none}';
		$css .= 'body.login .language-switcher,body.login .lu3g-firma{display:none}';

		// messaggi ed errori
		$css .= "body.login .message,body.login .notice,body.login .success,body.login #login_error{margin:0 0 20px;padding:12px 14px;background:{$sur};"
			. "border:1px solid {$line};border-left:3px solid {$ink};border-radius:6px;box-shadow:none;color:{$ink};font-size:13px}";
		$css .= "body.login #login_error,body.login .notice-error{border-left-color:#f87171}";
		$css .= "body.login .message a,body.login #login_error a,body.login .notice a{color:{$ink}}";
		$css .= "body.login .description,body.login .indicator-hint{color:{$mut}}";
		$css .= "body.login #pass-strength-result{background:{$sur};border-color:{$line};color:{$ink};border-radius:6px}";

		$css .= '@media (max-width:600px){body.login #login{padding:37px 25px 50px}}';

		// animazione d'entrata leggera (opzione "Animazioni"; mai con "riduci animazioni")
		if ( ! empty( $o['motion'] ) ) {
			$css .= '@media (prefers-reduced-motion:no-preference){@keyframes lu3g-r-in{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}'
				. 'body.login #login h1,body.login .lu3g-titolo,body.login form,body.login .lu3g-link{animation:lu3g-r-in .6s cubic-bezier(.22,1,.36,1) backwards}'
				. 'body.login .lu3g-titolo{animation-delay:.06s}body.login form{animation-delay:.12s}body.login .lu3g-link{animation-delay:.2s}'
				. 'body.wp-core-ui.login .button-primary:active{transform:scale(.99)}}';
		}
		return $css;
	}

	/** Script in fondo alla pagina: testi e segnaposto dell'app, riga dei link. */
	public static function footer( array $o ) {
		$data = array(
			'button' => $o['button'] ? $o['button'] : 'Accedi al pannello',
			'home'   => home_url( '/' ),
		);
		?>
<script id="lu3g-login-report">
(function () {
	var d = <?php echo wp_json_encode( $data ); ?>;
	document.body.classList.add('lu3g-js');
	var user = document.getElementById('user_login'), pass = document.getElementById('user_pass');
	var form = document.getElementById('loginform');
	if (form && user) {
		var l = form.querySelector('label[for="user_login"]');
		if (l) l.textContent = 'Email';
		user.placeholder = 'nome@azienda.it';
		user.setAttribute('autocomplete', 'username');
	}
	if (form && pass) pass.placeholder = 'La tua password';
	var btn = document.getElementById('wp-submit');
	if (form && btn) btn.value = d.button;
	var lost = document.getElementById('user_login') && document.getElementById('lostpasswordform');
	if (lost) document.getElementById('user_login').placeholder = 'nome@azienda.it';
	// i link di WordPress (password dimenticata, torna al sito, privacy) in una riga sola, come nell'app
	var nav = document.createElement('nav');
	nav.className = 'lu3g-link';
	nav.setAttribute('aria-label', 'Collegamenti');
	document.querySelectorAll('#nav a, .privacy-policy-page-link a, #backtoblog a').forEach(function (a) {
		var c = a.cloneNode(true);
		if (a.closest('#backtoblog')) c.textContent = 'Torna al sito';
		nav.appendChild(c);
	});
	var box = document.getElementById('login');
	if (box && nav.children.length) box.appendChild(nav);
})();
</script>
		<?php
	}
}
