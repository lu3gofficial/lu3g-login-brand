=== LU3G Login Brand ===
Versione: 1.6.1
Richiede: WordPress 6.0+, PHP 7.4+

Personalizza la sola schermata di accesso a wp-admin con lo stile del sito su cui è installato
(logo, colore scuro, colore dei pulsanti, font) e affianca il logo LU3G al logo del sito.
Il resto di wp-admin non viene toccato.

== Installazione su un nuovo sito ==
1. Plugin → Aggiungi nuovo → Carica plugin → scegli lu3g-login-brand.zip → Installa → Attiva.
2. Impostazioni → Accesso LU3G → "Analizza il sito".
3. Controlla i valori trovati, ritoccali se serve, premi Salva.
4. Apri la schermata di accesso in una finestra privata per vedere il risultato.

== Cosa legge "Analizza il sito" ==
Apre la homepage in un riquadro nascosto e la legge così come viene mostrata, con qualsiasi tema o page builder:
- Logo: logo personalizzato del tema, logo di Elementor, oppure immagine/SVG nell'header.
  Misura anche se il logo è chiaro o scuro: un logo scuro va su un riquadro bianco (e il logo LU3G passa alla versione scura).
- Colore di fondo: il colore scuro che occupa più superficie (header, footer, sezioni scure).
  Se il sito non ha sezioni scure, usa una versione scurita del colore di accento.
- Colore di accento: il colore dei pulsanti; in mancanza, il colore saturo più usato.
- Font: quello dei titoli, con i fogli di stile che lo caricano (se il font non è caricato, si usa quello di sistema).

Prima dell'analisi il plugin parte da: logo personalizzato → logo Elementor → icona del sito,
e dai colori di theme.json / kit Elementor.

== Aggiornamenti ==
Il plugin si aggiorna da solo dalle release di https://github.com/lu3gofficial/lu3g-login-brand (repository pubblico):
le nuove versioni compaiono in Bacheca → Aggiornamenti, senza configurare nulla.

== Font ==
DM Sans e Manrope (assets/fonts) sono distribuiti con licenza SIL Open Font License 1.1.
Gli altri Google Fonts si scelgono in Impostazioni → Accesso LU3G → Font: al salvataggio il font scelto viene scaricato in
wp-content/uploads/lu3g-login-brand/fonts/ e la pagina di accesso lo carica dal sito (ogni font mantiene la sua licenza, OFL o Apache).

== Note ==
- Nessun file esterno: i due loghi LU3G (per fondo scuro e per fondo chiaro) sono nella cartella assets.
- I selettori partono da "body.login" perché da WordPress 7.1 lo stile della pagina di accesso arriva dopo quello del plugin.
- Se sul sito c'è già una personalizzazione dell'accesso (per esempio uno snippet in WPCode), disattivala per non sovrapporre gli stili.
- Opzioni salvate in wp_options, chiave "lu3g_login_brand".
