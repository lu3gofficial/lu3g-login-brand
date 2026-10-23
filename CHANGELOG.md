# Registro delle modifiche

## 1.4.0 – 2026-10-23
- Schermata di caricamento dopo l'accesso (opzione "Dopo l'accesso", attiva di serie): logo e riga di avanzamento per circa
  1,3 secondi, poi dissolvenza con leggero scorrimento verso la Bacheca. Una sola volta, sulla prima pagina dopo il login;
  la pagina si carica intanto sotto; solo CSS; con "riduci animazioni" solo una dissolvenza breve.
  Anteprima per gli amministratori: `?lu3g_splash=1` su una pagina di wp-admin.
- Aspetto "Report LU3G": testo dei campi sempre leggibile anche in hover, clic, focus e con il riempimento automatico del
  browser (prima, con l'autocompletamento di Chrome attivo, il fondo tornava chiaro sotto al testo bianco); pagina dichiarata
  scura al browser (color-scheme: dark), come nell'app.

## 1.3.2 – 2026-10-23
- Aspetto "Report LU3G" da telefono: margini laterali di 24px come nell'app.

## 1.3.1 – 2026-10-23
- Aspetto "Report LU3G": la colonna è larga 390px come nell'app (prima si stringeva al contenuto).

## 1.3.0 – 2026-10-23
- Nuovo aspetto "Report LU3G" (predefinito): la schermata di https://report.lu3g.com/login riprodotta in WordPress.
  Fondo #09090b, colonna di 390px, logo LU3G 46px, titolo in Manrope, campi scuri, occhio della password con l'icona
  dell'app, pulsante bianco con freccia, link in una riga sotto il modulo. Font DM Sans e Manrope inclusi nel plugin (SIL OFL).
- Titolo e testo del pulsante modificabili; per le altre pagine (password dimenticata, nuova password) titolo automatico.
- L'aspetto precedente resta disponibile: Impostazioni → Accesso LU3G → Aspetto → "Colori del sito".

## 1.2.1 – 2026-10-23
- "Controlla di nuovo" in Bacheca → Aggiornamenti rilegge subito GitHub (prima poteva usare la release in cache fino a 6 ore).

## 1.2.0 – 2026-10-23
- Interazioni animate nella schermata di accesso (opzione "Animazioni" in Impostazioni → Accesso LU3G, attiva di serie):
  entrata in sequenza di riga d'accento, loghi, modulo e link; alone del colore di accento che si muove piano sullo sfondo;
  pulsante "Accedi" con il colore che scorre all'hover e piccola pressione al clic; campi che si illuminano e si sollevano
  appena quando ci si scrive; loghi che si sollevano al passaggio del mouse.
- Tutte le animazioni si spengono per chi ha "riduci animazioni" nel sistema operativo; solo CSS (transform e opacity).

## 1.1.1 – 2026-10-23
- Repository pubblico: gli aggiornamenti arrivano senza token (LU3G_GITHUB_TOKEN resta facoltativo).
- Collegamento al repository nella pagina dei plugin ("Plugin URI").

## 1.1.0 – 2026-10-23
- Aggiornamenti automatici dalle release GitHub.
- Logo LU3G per fondi chiari: lettere bianche con una classe esplicita (prima restavano nere in alcuni visualizzatori).

## 1.0.0 – 2026-10-23
- Prima versione: schermata di accesso a wp-admin con logo del sito e logo LU3G affiancati, colori e font del sito.
- "Analizza il sito" (Impostazioni → Accesso LU3G) legge logo, colore scuro, colore dei pulsanti e font dalla homepage.
