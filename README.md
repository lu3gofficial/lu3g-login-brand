# LU3G Login Brand

Due aspetti (Impostazioni → Accesso LU3G → Aspetto):
- **Tema LU3G** (predefinito, prima "Report LU3G"): la schermata di accesso di https://report.lu3g.com riprodotta in WordPress.
- **Colori del sito**: logo, colori e font del sito letti con "Analizza il sito", logo LU3G affiancato.

Plugin WordPress di LU3G: personalizza **solo la schermata di accesso a wp-admin** con lo stile del sito su cui è
installato (logo, colore scuro, colore dei pulsanti, font) e affianca il logo LU3G a quello del sito.
Piccole interazioni animate (quattro entrate a scelta, alone di colore, pulsante, campi e messaggi), disattivabili e spente
con "riduci animazioni". Schermata di caricamento dopo l'accesso con nove animazioni.
Facoltativo (spento di serie): **colori del tema in tutta la bacheca** di WordPress (menu, barra in alto, pulsanti, link),
presi dall'aspetto scelto, con piccole animazioni nella bacheca.

## Installazione su un sito
1. Scarica `lu3g-login-brand.zip` dall'ultima [release](../../releases/latest).
2. WordPress → Plugin → Aggiungi nuovo → Carica plugin → Attiva.
3. Impostazioni → Accesso LU3G: scegli l'aspetto, i testi e i font (tutti i Google Fonts); con "Colori del sito" premi **Analizza il sito**; Salva.
4. Gli aggiornamenti arrivano da soli dalle release di questo repository (Bacheca → Aggiornamenti): non serve configurare nulla.
   Facoltativo: `define( 'LU3G_GITHUB_TOKEN', 'github_pat_...' );` in `wp-config.php` alza il limite di richieste all'API GitHub.

Se sul sito c'è lo snippet WPCode "Schermata di accesso a wp-admin con logo LU3G", il plugin ha la precedenza e lo snippet
non fa nulla: si può disattivare.

## Pubblicare una nuova versione
1. Modifica il codice.
2. Aumenta la versione in `lu3g-login-brand.php` (`Version:` e `const VERSION`) e in `readme.txt`.
3. Aggiungi la sezione della versione in `CHANGELOG.md` (diventa la nota della release).
4. Commit, poi tag e invio:
   ```sh
   git tag v1.2.0 && git push && git push --tags
   ```
5. L'automazione `.github/workflows/release.yml` controlla la versione, crea lo zip e pubblica la release.
   I siti vedono l'aggiornamento in Bacheca → Aggiornamenti (entro 6 ore, o subito con "Controlla di nuovo").

Pacchetto in locale: `sh bin/build.sh` → `dist/lu3g-login-brand.zip`.

## Struttura
- `lu3g-login-brand.php` – schermata di accesso, impostazioni, CSS generato dai valori salvati.
- `assets/admin.js` – "Analizza il sito": legge la homepage in un riquadro nascosto (logo, colori, font).
- `assets/lu3g-chiaro.svg`, `assets/lu3g-scuro.svg` – logo LU3G per fondi scuri e per fondi chiari.
- `includes/class-layout-report.php` – aspetto "Tema LU3G" (valore salvato `report`).
- `includes/class-dashboard.php` – colori del tema e animazioni in tutta la bacheca (opzione "Bacheca").
- `includes/class-splash.php` – schermata di caricamento dopo l'accesso.
- `includes/class-fonts.php` – Google Fonts: elenco (`assets/data/google-fonts.json`) e importazione sul sito al salvataggio.
- `includes/class-admin.php`, `assets/admin.css`, `assets/admin-ui.js` – pagina delle impostazioni con anteprima.
- `assets/fonts/` – DM Sans e Manrope (variabili, latin), licenza SIL Open Font License 1.1.
- `includes/class-updater.php` – aggiornamenti dalle release GitHub.
