<?php
/**
 * Pagina delle impostazioni (Impostazioni → Accesso LU3G).
 *
 * Struttura: intestazione scura con il logo LU3G e la barra delle sezioni (porta alla scheda, si accende quella che si
 * sta guardando); a sinistra le schede delle impostazioni (Aspetto, Testi, Font, Colori e logo del sito, Effetti,
 * Bacheca, Firma LU3G); a destra l'anteprima della schermata di accesso, che si aggiorna mentre si cambia qualcosa e
 * può rivedere l'animazione d'entrata (assets/admin-ui.js); in basso la barra con "Salva".
 * Gli ID dei campi di "Analizza il sito" (lu3g-logo, lu3g-bg, lu3g-accent, lu3g-font, lu3g-font-css, lu3g-analyzed...)
 * sono gli stessi di prima: li usa assets/admin.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Admin {

	public static function assets( $plugin_file, $version, $opt ) {
		$o = LU3G_Login_Brand::get();
		wp_enqueue_media();
		wp_enqueue_style( 'lu3g-login-admin', plugins_url( 'assets/admin.css', $plugin_file ), array(), $version );
		wp_enqueue_script( 'lu3g-login-brand', plugins_url( 'assets/admin.js', $plugin_file ), array(), $version, true );
		wp_enqueue_script( 'lu3g-login-admin-ui', plugins_url( 'assets/admin-ui.js', $plugin_file ), array(), $version, true );
		wp_localize_script( 'lu3g-login-brand', 'LU3GLogin', array(
			'home'     => home_url( '/' ),
			'opt'      => $opt,
			'fonts'    => LU3G_Login_Fonts::catalog_url(),
			'fontsDir' => plugins_url( 'assets/fonts/', $plugin_file ),
			'lu3g'     => LU3G_Login_Brand::lu3g_logo( 'light' ),
			'lu3gDark' => LU3G_Login_Brand::lu3g_logo( 'dark' ),
			'site'     => get_bloginfo( 'name' ),
			'saved'    => array( 'head' => $o['font_head'], 'body' => $o['font_body'] ),
		) );
	}

	private static function toggle( $n, $key, $o, $label, $desc ) {
		?>
		<label class="lu3g-switch">
			<input type="checkbox" name="<?php echo esc_attr( $n ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $o[ $key ], 1 ); ?>>
			<span class="lu3g-switch__track" aria-hidden="true"></span>
			<span class="lu3g-switch__text"><strong><?php echo esc_html( $label ); ?></strong><small><?php echo wp_kses( $desc, array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?></small></span>
		</label>
		<?php
	}

	private static function font_field( $n, $key, $value, $label, $default ) {
		?>
		<div class="lu3g-font" data-key="<?php echo esc_attr( $key ); ?>">
			<label class="lu3g-label" for="lu3g-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="lu3g-combo">
				<input type="text" id="lu3g-<?php echo esc_attr( $key ); ?>" class="lu3g-input lu3g-combo__input" name="<?php echo esc_attr( $n ); ?>[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( $value ); ?>" placeholder="Predefinito: <?php echo esc_attr( $default ); ?>" autocomplete="off"
					role="combobox" aria-expanded="false" aria-autocomplete="list" aria-controls="lu3g-<?php echo esc_attr( $key ); ?>-list">
				<button type="button" class="lu3g-combo__clear" aria-label="Torna al font predefinito">×</button>
				<ul class="lu3g-combo__list" id="lu3g-<?php echo esc_attr( $key ); ?>-list" role="listbox" hidden></ul>
			</div>
			<p class="lu3g-font__sample" aria-hidden="true">Accedi al sito · Aa Bb Cc 123</p>
		</div>
		<?php
	}

	public static function page( array $o, $n, $version ) {
		$is_site = 'sito' === $o['layout'];
		?>
		<div class="wrap lu3g-admin" data-layout="<?php echo esc_attr( $o['layout'] ); ?>">
			<h1 class="screen-reader-text">Accesso LU3G</h1>
			<header class="lu3g-head">
				<img src="<?php echo esc_attr( LU3G_Login_Brand::lu3g_logo( 'light' ) ); ?>" alt="" width="34" height="34">
				<div>
					<p class="lu3g-head__title">Accesso LU3G</p>
					<p class="lu3g-head__sub">Schermata di accesso e bacheca di WordPress · versione <?php echo esc_html( $version ); ?> ·
						<a href="https://github.com/lu3gofficial/lu3g-login-brand/releases" target="_blank" rel="noopener">Novità ↗</a></p>
				</div>
				<a class="lu3g-btn lu3g-btn--ghost" href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener">Apri la schermata di accesso ↗</a>
			</header>
			<nav class="lu3g-nav" aria-label="Sezioni">
				<a href="#lu3g-s-aspetto">Aspetto</a>
				<a href="#lu3g-s-testi" class="lu3g-only-report">Testi</a>
				<a href="#lu3g-s-font">Font</a>
				<a href="#lu3g-s-sito" class="lu3g-only-sito">Colori e logo</a>
				<a href="#lu3g-s-effetti">Effetti</a>
				<a href="#lu3g-s-bacheca">Bacheca</a>
				<a href="#lu3g-s-firma" class="lu3g-only-sito">Firma LU3G</a>
			</nav>

			<form method="post" action="options.php" class="lu3g-grid" id="lu3g-form">
				<?php settings_fields( 'lu3g_login_brand' ); ?>
				<div class="lu3g-col">

					<section class="lu3g-card" id="lu3g-s-aspetto">
						<h2>Aspetto</h2>
						<div class="lu3g-tiles" role="radiogroup" aria-label="Aspetto">
							<label class="lu3g-tile">
								<input type="radio" name="<?php echo esc_attr( $n ); ?>[layout]" value="report" <?php checked( $o['layout'], 'report' ); ?>>
								<span class="lu3g-tile__thumb lu3g-tile__thumb--report" aria-hidden="true"><i></i><b></b><b></b><em></em></span>
								<strong>Tema LU3G</strong><small>Scuro e minimale, lo stile di report.lu3g.com</small>
							</label>
							<label class="lu3g-tile">
								<input type="radio" name="<?php echo esc_attr( $n ); ?>[layout]" value="sito" <?php checked( $o['layout'], 'sito' ); ?>>
								<span class="lu3g-tile__thumb lu3g-tile__thumb--sito" aria-hidden="true"><i></i><b></b><b></b><em></em></span>
								<strong>Colori del sito</strong><small>Logo, colori e font del sito con il logo LU3G</small>
							</label>
						</div>
					</section>

					<section class="lu3g-card lu3g-only-report" id="lu3g-s-testi">
						<h2>Testi</h2>
						<div class="lu3g-row">
							<div><label class="lu3g-label" for="lu3g-title">Titolo</label>
								<input type="text" class="lu3g-input" id="lu3g-title" name="<?php echo esc_attr( $n ); ?>[title]" value="<?php echo esc_attr( $o['title'] ); ?>" placeholder="Accedi al sito"></div>
							<div><label class="lu3g-label" for="lu3g-button">Pulsante</label>
								<input type="text" class="lu3g-input" id="lu3g-button" name="<?php echo esc_attr( $n ); ?>[button]" value="<?php echo esc_attr( $o['button'] ); ?>" placeholder="Accedi al pannello"></div>
						</div>
					</section>

					<section class="lu3g-card" id="lu3g-s-font">
						<h2>Font</h2>
						<p class="lu3g-hint">Tutti i Google Fonts. Al salvataggio il font scelto viene scaricato sul sito: la pagina di accesso non contatta Google.</p>
						<div class="lu3g-chips" role="group" aria-label="Filtra per categoria">
							<button type="button" class="lu3g-chip is-on" data-cat="">Tutti</button>
							<button type="button" class="lu3g-chip" data-cat="s">Sans serif</button>
							<button type="button" class="lu3g-chip" data-cat="r">Serif</button>
							<button type="button" class="lu3g-chip" data-cat="d">Display</button>
							<button type="button" class="lu3g-chip" data-cat="h">Calligrafici</button>
							<button type="button" class="lu3g-chip" data-cat="m">Monospace</button>
						</div>
						<div class="lu3g-row">
							<?php self::font_field( $n, 'font_head', $o['font_head'], 'Titoli', $is_site && $o['font'] ? trim( $o['font'], '"' ) : 'Manrope' ); ?>
							<?php self::font_field( $n, 'font_body', $o['font_body'], 'Testi, campi e pulsante', $is_site && $o['font'] ? trim( $o['font'], '"' ) : 'DM Sans' ); ?>
						</div>
					</section>

					<section class="lu3g-card lu3g-only-sito" id="lu3g-s-sito">
						<h2>Colori e logo del sito</h2>
						<div class="lu3g-analyze">
							<button type="button" class="lu3g-btn" id="lu3g-analyze">Analizza il sito</button>
							<span id="lu3g-status" class="lu3g-hint"><?php echo $o['analyzed'] ? 'Ultima analisi: ' . esc_html( $o['analyzed'] ) : 'Legge logo, colori e font della homepage così come vengono mostrati.'; ?></span>
						</div>
						<label class="lu3g-label" for="lu3g-logo">Logo del sito</label>
						<div class="lu3g-inline">
							<input type="text" class="lu3g-input lu3g-mono" id="lu3g-logo" name="<?php echo esc_attr( $n ); ?>[logo]" value="<?php echo esc_attr( $o['logo'] ); ?>">
							<button type="button" class="lu3g-btn lu3g-btn--ghost" id="lu3g-media">Libreria</button>
						</div>
						<div class="lu3g-seg" role="radiogroup" aria-label="Tono del logo">
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[logo_tone]" value="light" <?php checked( $o['logo_tone'], 'light' ); ?>><span>Logo chiaro, su fondo scuro</span></label>
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[logo_tone]" value="dark" <?php checked( $o['logo_tone'], 'dark' ); ?>><span>Logo scuro, su riquadro bianco</span></label>
						</div>
						<div class="lu3g-row">
							<div><label class="lu3g-label" for="lu3g-bg">Fondo</label>
								<div class="lu3g-color"><input type="color" id="lu3g-bg" name="<?php echo esc_attr( $n ); ?>[bg]" value="<?php echo esc_attr( $o['bg'] ); ?>"><code><?php echo esc_html( $o['bg'] ); ?></code></div></div>
							<div><label class="lu3g-label" for="lu3g-accent">Accento (pulsanti)</label>
								<div class="lu3g-color"><input type="color" id="lu3g-accent" name="<?php echo esc_attr( $n ); ?>[accent]" value="<?php echo esc_attr( $o['accent'] ); ?>"><code><?php echo esc_html( $o['accent'] ); ?></code></div></div>
						</div>
						<p class="lu3g-hint" id="lu3g-site-font">Font del sito trovato dall'analisi: <strong><?php echo $o['font'] ? esc_html( trim( $o['font'], '"' ) ) : 'nessuno'; ?></strong> (usato se nella scheda Font lasci "Predefinito").</p>
						<input type="hidden" id="lu3g-font" name="<?php echo esc_attr( $n ); ?>[font]" value="<?php echo esc_attr( $o['font'] ); ?>">
						<textarea hidden id="lu3g-font-css" name="<?php echo esc_attr( $n ); ?>[font_css]"><?php echo esc_textarea( implode( "\n", $o['font_css'] ) ); ?></textarea>
					</section>

					<section class="lu3g-card" id="lu3g-s-effetti">
						<h2>Effetti</h2>
						<?php self::toggle( $n, 'motion', $o, 'Animazioni', 'Entrata in sequenza di logo, titolo e modulo; pulsante, campi e messaggi animati. Spente per chi ha "riduci animazioni".' ); ?>
						<div class="lu3g-anims lu3g-entries" id="lu3g-entries">
							<p class="lu3g-label">Entrata della schermata di accesso</p>
							<div class="lu3g-anims__grid lu3g-entries__grid" role="radiogroup" aria-label="Entrata della schermata di accesso">
								<?php foreach ( LU3G_Login_Brand::entry_styles() as $key => $info ) : ?>
									<label class="lu3g-anim lu3g-entry lu3g-entry--<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $info[1] ); ?>">
										<input type="radio" name="<?php echo esc_attr( $n ); ?>[motion_style]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $o['motion_style'], $key ); ?>>
										<span class="lu3g-anim__stage lu3g-entry__stage" aria-hidden="true"><i></i><b></b><b></b><em></em></span>
										<strong><?php echo esc_html( $info[0] ); ?></strong>
										<small><?php echo esc_html( $info[1] ); ?></small>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<?php self::toggle( $n, 'splash', $o, 'Schermata di caricamento dopo l\'accesso', 'Una volta, sulla prima pagina dopo il login, per circa 1,5 secondi; la pagina si carica intanto sotto.' ); ?>
						<div class="lu3g-anims" id="lu3g-anims">
							<p class="lu3g-label">Animazione</p>
							<div class="lu3g-anims__grid" role="radiogroup" aria-label="Animazione della schermata di caricamento">
								<?php foreach ( LU3G_Login_Splash::styles() as $key => $info ) : ?>
									<label class="lu3g-anim lu3g-anim--<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $info[1] ); ?>">
										<input type="radio" name="<?php echo esc_attr( $n ); ?>[splash_style]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $o['splash_style'], $key ); ?>>
										<span class="lu3g-anim__stage" aria-hidden="true"><span class="lu3g-anim__curtain"></span><span class="lu3g-anim__fx"></span><span class="lu3g-anim__logo"></span></span>
										<strong><?php echo esc_html( $info[0] ); ?></strong>
										<small><?php echo esc_html( $info[1] ); ?></small>
									</label>
								<?php endforeach; ?>
							</div>
							<a class="lu3g-btn lu3g-btn--ghost lu3g-anims__try" id="lu3g-splash-try" href="<?php echo esc_url( admin_url( '?lu3g_splash=1&lu3g_splash_style=' . $o['splash_style'] ) ); ?>" target="_blank" rel="noopener">Prova a schermo intero ↗</a>
						</div>
					</section>

					<section class="lu3g-card" id="lu3g-s-bacheca">
						<h2>Bacheca</h2>
						<?php self::toggle( $n, 'dashboard', $o, 'Colori in tutta la bacheca', 'Menu laterale, barra in alto (anche sul sito), pulsanti, link e campi con i colori dell\'aspetto scelto: Tema LU3G o colori del sito. Le pagine restano chiare.' ); ?>
						<div class="lu3g-dashbox" id="lu3g-dashbox">
							<div class="lu3g-dash" id="lu3g-dash" aria-hidden="true">
								<span class="lu3g-dash__bar"><i></i><i></i><i></i></span>
								<span class="lu3g-dash__menu"><i></i><i class="is-on"></i><i></i><i></i><i></i></span>
								<span class="lu3g-dash__page"><b></b><b></b><b></b><em></em></span>
							</div>
							<?php self::toggle( $n, 'dashboard_motion', $o, 'Animazioni nella bacheca', 'Pagine che entrano, sottomenu e menu della barra che compaiono, pulsanti con pressione al clic. Mai nell\'editor a blocchi.' ); ?>
						</div>
					</section>

					<section class="lu3g-card lu3g-only-sito" id="lu3g-s-firma">
						<h2>Firma LU3G</h2>
						<?php self::toggle( $n, 'lu3g', $o, 'Logo LU3G', 'Accanto al logo del sito, separato da una riga sottile.' ); ?>
						<?php self::toggle( $n, 'signature', $o, '"Sito realizzato da LU3G"', 'In fondo alla pagina di accesso.' ); ?>
					</section>
					<input type="hidden" id="lu3g-analyzed" name="<?php echo esc_attr( $n ); ?>[analyzed]" value="<?php echo esc_attr( $o['analyzed'] ); ?>">
				</div>

				<aside class="lu3g-preview" aria-label="Anteprima">
					<div class="lu3g-preview__bar"><span></span><span></span><span></span><em>Anteprima</em>
						<button type="button" class="lu3g-replay" id="lu3g-replay">↻ Rivedi l'entrata</button></div>
					<div class="lu3g-pv" id="lu3g-pv">
						<div class="lu3g-pv__inner">
							<div class="lu3g-pv__logos"><span class="lu3g-pv__site"></span><span class="lu3g-pv__lu3g"></span></div>
							<p class="lu3g-pv__title"></p>
							<div class="lu3g-pv__form">
								<span class="lu3g-pv__label">Email</span><span class="lu3g-pv__field">nome@azienda.it</span>
								<span class="lu3g-pv__label">Password</span><span class="lu3g-pv__field">La tua password<i></i></span>
								<span class="lu3g-pv__btn"></span>
							</div>
							<p class="lu3g-pv__links"><span>Password dimenticata?</span><span>Torna al sito</span></p>
						</div>
					</div>
				</aside>

				<div class="lu3g-savebar">
					<span class="lu3g-hint" id="lu3g-dirty" hidden>Modifiche non salvate</span>
					<?php submit_button( 'Salva', 'primary lu3g-btn lu3g-btn--primary', 'submit', false ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}
