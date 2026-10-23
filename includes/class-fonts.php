<?php
/**
 * Google Fonts: elenco completo e importazione in locale.
 *
 * - assets/data/google-fonts.json: tutte le famiglie con caratteri latini (nome, categoria, pesi, intervallo variabile),
 *   ricavate da https://fonts.google.com/metadata/fonts e ordinate per popolarità. Serve alla select con ricerca.
 * - Quando nelle impostazioni si sceglie un font e si salva, il plugin lo SCARICA sul sito
 *   (wp-content/uploads/lu3g-login-brand/fonts/<famiglia>/): CSS + file woff2 dei set latino e latino esteso.
 *   La pagina di accesso poi lo carica dal sito stesso: nessuna chiamata a Google per chi visita (GDPR).
 * - Font variabili: un file per set con l'intervallo di pesi 300–800; font statici: pesi 400, 500, 600, 700 se esistono.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Fonts {

	const SUBDIR = 'lu3g-login-brand/fonts';
	const UA     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

	private static $catalog;
	private static $plugin_file;

	public static function init( $plugin_file ) {
		self::$plugin_file = $plugin_file;
	}

	/** Elenco dei font: array di [famiglia, categoria (s|r|d|h|m), pesi, intervallo variabile o null]. */
	public static function catalog() {
		if ( null === self::$catalog ) {
			$file          = plugin_dir_path( self::$plugin_file ) . 'assets/data/google-fonts.json';
			$data          = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array();
			self::$catalog = $data['fonts'] ?? array();
		}
		return self::$catalog;
	}

	public static function catalog_url() {
		return plugins_url( 'assets/data/google-fonts.json', self::$plugin_file );
	}

	public static function find( $family ) {
		foreach ( self::catalog() as $f ) {
			if ( 0 === strcasecmp( $f[0], (string) $family ) ) {
				return $f;
			}
		}
		return null;
	}

	/** Pila CSS con il ripiego adatto alla categoria. */
	public static function stack( $family ) {
		$f       = self::find( $family );
		$generic = array( 's' => 'sans-serif', 'r' => 'serif', 'd' => 'sans-serif', 'h' => 'cursive', 'm' => 'monospace' );
		return '"' . str_replace( '"', '', $family ) . '",' . ( $f ? $generic[ $f[1] ] : 'sans-serif' );
	}

	private static function dir( $family ) {
		$up   = wp_upload_dir();
		$slug = sanitize_title( $family );
		return array( trailingslashit( $up['basedir'] ) . self::SUBDIR . '/' . $slug, trailingslashit( $up['baseurl'] ) . self::SUBDIR . '/' . $slug );
	}

	/** URL del CSS locale del font, se è già stato importato. */
	public static function local_css( $family ) {
		list( $path, $url ) = self::dir( $family );
		return is_readable( $path . '/font.css' ) ? $url . '/font.css?v=' . filemtime( $path . '/font.css' ) : '';
	}

	/** Scarica il font da Google sul sito. Restituisce l'URL del CSS locale o un WP_Error. */
	public static function import( $family ) {
		$f = self::find( $family );
		if ( ! $f ) {
			return new WP_Error( 'lu3g_font', 'Font non trovato nell\'elenco di Google Fonts: ' . $family );
		}
		$done = self::local_css( $f[0] );
		if ( $done ) {
			return $done;
		}
		if ( $f[3] ) {
			$axis = 'wght@' . max( $f[3][0], 300 ) . '..' . min( $f[3][1], 800 );
		} else {
			$w    = array_values( array_intersect( array( 400, 500, 600, 700 ), $f[2] ) );
			$axis = $w ? 'wght@' . implode( ';', $w ) : '';
		}
		$api = 'https://fonts.googleapis.com/css2?family=' . str_replace( '%20', '+', rawurlencode( $f[0] ) ) . ( $axis ? ':' . $axis : '' ) . '&display=swap';
		$res = wp_remote_get( $api, array( 'timeout' => 20, 'user-agent' => self::UA ) );
		if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return new WP_Error( 'lu3g_font', 'Google Fonts non ha risposto per ' . $f[0] . '.' );
		}
		list( $path, $url ) = self::dir( $f[0] );
		if ( ! wp_mkdir_p( $path ) ) {
			return new WP_Error( 'lu3g_font', 'Impossibile creare la cartella dei font in wp-content/uploads.' );
		}
		// blocchi @font-face preceduti dal commento con il set di caratteri: tengo latin e latin-ext
		preg_match_all( '#/\*\s*([a-z0-9-]+)\s*\*/\s*(@font-face\s*\{[^}]*\})#i', wp_remote_retrieve_body( $res ), $m, PREG_SET_ORDER );
		$css  = '/* ' . $f[0] . ' – importato da Google Fonts il ' . gmdate( 'Y-m-d' ) . " (licenza nel sito di Google Fonts) */\n";
		$seen = array();
		foreach ( $m as $block ) {
			if ( ! in_array( strtolower( $block[1] ), array( 'latin', 'latin-ext' ), true ) ) {
				continue;
			}
			$face = $block[2];
			if ( preg_match_all( '#url\((https://fonts\.gstatic\.com/[^)]+\.woff2)\)#', $face, $u ) ) {
				foreach ( $u[1] as $remote ) {
					$name = substr( md5( $remote ), 0, 12 ) . '.woff2';
					if ( ! isset( $seen[ $name ] ) ) {
						$file = wp_remote_get( $remote, array( 'timeout' => 30, 'user-agent' => self::UA ) );
						if ( is_wp_error( $file ) || 200 !== wp_remote_retrieve_response_code( $file ) ) {
							return new WP_Error( 'lu3g_font', 'Download non riuscito per un file di ' . $f[0] . '.' );
						}
						file_put_contents( $path . '/' . $name, wp_remote_retrieve_body( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
						$seen[ $name ] = true;
					}
					$face = str_replace( $remote, $name, $face );
				}
			}
			$css .= '/* ' . $block[1] . " */\n" . $face . "\n";
		}
		if ( ! $seen ) {
			return new WP_Error( 'lu3g_font', 'Nessun file latino trovato per ' . $f[0] . '.' );
		}
		file_put_contents( $path . '/font.css', $css ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return self::local_css( $f[0] );
	}
}
