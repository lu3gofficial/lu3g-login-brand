<?php
/**
 * Schermata di caricamento dopo l'accesso (opzione "Schermata di caricamento", attiva di serie).
 *
 * - Compare UNA volta, sulla prima pagina dopo il login: al login si salva un segnale sull'utente (user meta con l'ora),
 *   la prima pagina lo legge e lo cancella subito. Cambiando pagina non ricompare.
 * - Non rallenta l'accesso: la pagina (di solito la Bacheca) si carica normalmente sotto; la schermata è uno strato
 *   disegnato con CSS (html::before / html::after) presente dal primo disegno, dura ~1,3 s e poi sfuma scorrendo verso l'alto.
 *   Nessun JavaScript, nessun file da scaricare (il logo è incorporato). pointer-events:none: i clic passano sempre.
 * - Aspetto "Report LU3G": fondo #09090b e logo LU3G; aspetto "Colori del sito": fondo e logo del sito.
 * - Con "riduci animazioni" nel sistema operativo: niente movimento, solo una dissolvenza breve.
 * - Anteprima per gli amministratori: aggiungere ?lu3g_splash=1 all'indirizzo di una pagina di wp-admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Splash {

	const META = 'lu3g_login_splash';
	const TTL  = 120;   // secondi: oltre questo tempo il segnale non vale più (es. login senza aprire pagine)

	public static function init() {
		add_action( 'wp_login', array( __CLASS__, 'mark' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'maybe_print' ), 1 );
		// se dopo il login si torna al sito invece che in Bacheca, la schermata compare lì
		add_action( 'wp_head', array( __CLASS__, 'maybe_print' ), 1 );
	}

	/** Al login: segnale sull'utente (solo se l'opzione è attiva). */
	public static function mark( $user_login, $user ) {
		$o = LU3G_Login_Brand::get();
		if ( empty( $o['splash'] ) || ! ( $user instanceof WP_User ) ) {
			return;
		}
		update_user_meta( $user->ID, self::META, time() );
	}

	/** Prima pagina dopo il login: stampa la schermata e cancella il segnale. */
	public static function maybe_print() {
		if ( ! is_user_logged_in() || wp_doing_ajax() || ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) ) {
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
		echo '<style id="lu3g-splash">' . self::css( LU3G_Login_Brand::get() ) . '</style>' . "\n";
	}

	public static function css( array $o ) {
		$report = 'report' === $o['layout'];
		$bg     = $report ? LU3G_Login_Report::PAPER : $o['bg'];
		$line   = $report ? LU3G_Login_Report::INK : $o['accent'];
		$logo   = $report ? LU3G_Login_Brand::lu3g_logo( 'light' ) : $o['logo'];
		$logo   = 0 === strpos( (string) $logo, 'data:' ) ? $logo : esc_url( $logo );
		$size   = $report ? '64px 64px' : 'auto 80px';   // misure fisse: lo strato è a tutto schermo
		$ease   = 'cubic-bezier(.22,1,.36,1)';

		// strato di fondo (html::before) con il logo al centro; riga di avanzamento sotto il logo (html::after)
		$css  = "html::before{content:\"\";position:fixed;inset:0;z-index:2147483646;pointer-events:none;background:{$bg} url(\"{$logo}\") center/{$size} no-repeat;"
			. "animation:lu3g-splash-out .55s {$ease} 1.25s forwards}";
		$css .= "html::after{content:\"\";position:fixed;left:50%;top:50%;z-index:2147483647;pointer-events:none;width:120px;height:2px;margin:"
			. ( $report ? '56px' : '72px' ) . " 0 0 -60px;border-radius:2px;background:{$line};transform-origin:left center;"
			. "animation:lu3g-splash-bar 1.1s {$ease} .1s both,lu3g-splash-fade .3s ease 1.25s forwards}";
		$css .= '@keyframes lu3g-splash-out{to{opacity:0;transform:translateY(-24px);visibility:hidden}}';
		$css .= '@keyframes lu3g-splash-bar{from{transform:scaleX(0)}to{transform:scaleX(1)}}';
		$css .= '@keyframes lu3g-splash-fade{to{opacity:0;visibility:hidden}}';
		// il logo entra con una leggera crescita: lo ottengo animando la dimensione dello sfondo
		$css .= '@media (prefers-reduced-motion:no-preference){@keyframes lu3g-splash-in{from{background-size:' . ( $report ? '52px 52px' : 'auto 66px' ) . '}}'
			. "html::before{animation:lu3g-splash-in .7s {$ease} both,lu3g-splash-out .55s {$ease} 1.25s forwards}}";
		// riduci animazioni: nessun movimento, dissolvenza breve
		$css .= '@media (prefers-reduced-motion:reduce){html::before{animation:lu3g-splash-fade .3s ease .6s forwards}html::after{display:none}}';
		return $css;
	}
}
