# Registro delle modifiche

## 1.7.1 – 2026-10-27
- Aggiornamenti indipendenti dal proprietario del repository GitHub: senza token il pacchetto è il link pubblico di
  download (WordPress lo scarica da solo), con il token l'indirizzo API dell'allegato è riconosciuto qualunque sia il
  proprietario. Così uno spostamento del repository non blocca più "Aggiorna adesso" (con la 1.6.0 succedeva).

## 1.7.0 – 2026-10-27
- L'aspetto "Report LU3G" si chiama ora **Tema LU3G** (il valore salvato resta lo stesso: nessuna impostazione da rifare).
- Nuova scheda **Bacheca** (spenta di serie): i colori dell'aspetto scelto in tutta la bacheca di WordPress – menu laterale,
  barra in alto (anche sul sito), pulsanti, link, campi e componenti dell'editor a blocchi. Con "Tema LU3G" nero e bianco
  come report.lu3g.com, con "Colori del sito" fondo e accento del sito; testi sempre calcolati per restare leggibili.
  "Animazioni nella bacheca": pagine che entrano, sottomenu e menu della barra a comparsa, pulsanti con pressione al clic,
  riquadri della bacheca in sequenza (mai nell'editor a blocchi, mai con "riduci animazioni").
- Schermata di accesso: quattro entrate a scelta (Sale, Zoom, Sfocatura, Cascata) con miniature animate; messaggi che
  scendono dall'alto ed errore con un piccolo scatto. Tema LU3G: freccia del pulsante che scatta all'hover, logo che ruota
  appena, etichette che si accendono, occhio della password animato.
- Schermata di caricamento: tre animazioni nuove (Apertura, Zoom, Scansione), nove in tutto.
- Pagina delle impostazioni: barra delle sezioni che segue lo scorrimento, schede che entrano in sequenza, "Rivedi l'entrata"
  nell'anteprima, miniatura della bacheca con i colori che avrà, rotellina durante il salvataggio.

## 1.6.1 – 2026-10-27
- Il repository è passato all'organizzazione GitHub lu3gofficial: aggiornati Plugin URI, Update URI e sorgente
  degli aggiornamenti (lu3gofficial/lu3g-login-brand). I siti con versioni precedenti si aggiornano comunque,
  perché GitHub reindirizza il vecchio indirizzo.

## 1.6.0 – 2026-10-23
- Schermata di caricamento dopo l'accesso: sei animazioni a scelta (Linea, Dissolvenza, Pulsazione, Anello, Sipario,
  Cerchio), ognuna con una miniatura animata nelle impostazioni e il pulsante "Prova a schermo intero".
- Logo ed effetti della schermata in un piccolo elemento stampato all'apertura della pagina (in_admin_header /
  wp_body_open); lo sfondo resta html::before, presente dal primo disegno. Sempre solo CSS e una volta sola dopo il login.
- Anteprima per gli amministratori: `?lu3g_splash=1&lu3g_splash_style=<nome>`.

## 1.5.0 – 2026-10-23
- Nuova pagina delle impostazioni: intestazione LU3G, schede (Aspetto, Testi, Font, Colori e logo del sito, Effetti,
  Firma LU3G), anteprima della schermata di accesso che si aggiorna mentre si modificano i campi, barra "Salva" fissa
  con avviso di modifiche non salvate.
- Tutti i Google Fonts (1.829 famiglie con caratteri latini) selezionabili per titoli e testi, con ricerca, filtro per
  categoria e anteprima del font. Al salvataggio il font viene scaricato sul sito (wp-content/uploads/lu3g-login-brand/fonts):
  la pagina di accesso non contatta Google.
- "Analizza il sito" aggiorna subito l'anteprima.

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
