<?php
/**
 * Aggiornamenti del plugin da GitHub (repository privato).
 *
 * Come funziona:
 * - l'intestazione "Update URI: https://github.com/..." del plugin dice a WordPress di non cercarlo su wordpress.org
 *   e di chiedere gli aggiornamenti al filtro "update_plugins_github.com", gestito qui;
 * - il plugin legge l'ultima release del repository (API GitHub, con cache di 6 ore) e, se la versione è più nuova,
 *   WordPress mostra "Aggiornamento disponibile" come per ogni altro plugin;
 * - il pacchetto è il file "lu3g-login-brand.zip" allegato alla release (cartella lu3g-login-brand/ all'interno).
 *
 * Repository privato: ogni sito ha bisogno di un token GitHub di sola lettura in wp-config.php:
 *   define( 'LU3G_GITHUB_TOKEN', 'github_pat_...' );
 * (token "fine-grained" limitato a questo repository, permesso "Contents: Read-only"). Senza token il plugin funziona,
 * ma non vede gli aggiornamenti.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LU3G_Login_Brand_Updater {

	const REPO      = 'sviluppolu3g/lu3g-login-brand';   // proprietario/nome del repository su GitHub
	const ASSET     = 'lu3g-login-brand.zip';
	const SLUG      = 'lu3g-login-brand';
	const TRANSIENT = 'lu3g_login_brand_release';

	private static $file;

	public static function init( $plugin_file ) {
		self::$file = $plugin_file;
		add_filter( 'update_plugins_github.com', array( __CLASS__, 'check' ), 10, 3 );
		add_filter( 'plugins_api', array( __CLASS__, 'details' ), 20, 3 );
		add_filter( 'upgrader_pre_download', array( __CLASS__, 'download' ), 10, 3 );
		// dopo un aggiornamento o un controllo manuale si rilegge GitHub
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush' ) );
		add_action( 'load-update-core.php', array( __CLASS__, 'flush' ) );
	}

	private static function token() {
		return defined( 'LU3G_GITHUB_TOKEN' ) ? (string) LU3G_GITHUB_TOKEN : '';
	}

	private static function headers( $accept = 'application/vnd.github+json' ) {
		$h = array(
			'Accept'               => $accept,
			'User-Agent'           => 'lu3g-login-brand-updater',
			'X-GitHub-Api-Version' => '2022-11-28',
		);
		if ( self::token() ) {
			$h['Authorization'] = 'Bearer ' . self::token();
		}
		return $h;
	}

	public static function flush() {
		delete_site_transient( self::TRANSIENT );
	}

	/** Ultima release pubblicata: versione, pacchetto (URL API dell'allegato), note, data. */
	public static function release() {
		$cached = get_site_transient( self::TRANSIENT );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$res  = wp_remote_get( 'https://api.github.com/repos/' . self::REPO . '/releases/latest', array( 'headers' => self::headers(), 'timeout' => 10 ) );
		$data = array();
		if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
			$json = json_decode( wp_remote_retrieve_body( $res ), true );
			foreach ( (array) ( $json['assets'] ?? array() ) as $a ) {
				if ( self::ASSET === ( $a['name'] ?? '' ) ) {
					$data = array(
						'version'   => ltrim( (string) $json['tag_name'], 'vV' ),
						'package'   => $a['url'],
						'notes'     => (string) ( $json['body'] ?? '' ),
						'date'      => (string) ( $json['published_at'] ?? '' ),
						'html_url'  => (string) ( $json['html_url'] ?? '' ),
					);
				}
			}
		}
		// anche un errore va in cache (1 ora), per non chiamare GitHub a ogni pagina di wp-admin
		set_site_transient( self::TRANSIENT, $data, $data ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
		return $data;
	}

	/** Filtro "update_plugins_github.com": risponde con l'ultima release; WordPress confronta le versioni. */
	public static function check( $update, $plugin_data, $plugin_file ) {
		if ( plugin_basename( self::$file ) !== $plugin_file ) {
			return $update;
		}
		$r = self::release();
		if ( empty( $r['version'] ) ) {
			return $update;
		}
		return array(
			'id'           => 'github.com/' . self::REPO,
			'slug'         => self::SLUG,
			'plugin'       => $plugin_file,
			'version'      => $r['version'],
			'new_version'  => $r['version'],
			'url'          => $r['html_url'],
			'package'      => $r['package'],
			'requires_php' => '7.4',
		);
	}

	/** Finestra "Visualizza dettagli": note della release come registro delle modifiche. */
	public static function details( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || self::SLUG !== ( $args->slug ?? '' ) ) {
			return $result;
		}
		$r    = self::release();
		$data = get_plugin_data( self::$file, false, false );
		return (object) array(
			'name'          => $data['Name'],
			'slug'          => self::SLUG,
			'version'       => $r['version'] ?? $data['Version'],
			'author'        => $data['Author'],
			'homepage'      => $data['PluginURI'] ? $data['PluginURI'] : 'https://lu3g.it/',
			'requires_php'  => '7.4',
			'last_updated'  => $r['date'] ?? '',
			'sections'      => array(
				'description' => wpautop( esc_html( $data['Description'] ) ),
				'changelog'   => wpautop( esc_html( $r['notes'] ?? '' ) ),
			),
		);
	}

	/**
	 * Scarica il pacchetto dal repository privato. L'allegato si chiede all'API con il token; GitHub risponde con un
	 * rinvio a un indirizzo temporaneo già firmato, che si scarica SENZA token (lì il token verrebbe rifiutato).
	 */
	public static function download( $reply, $package, $upgrader ) {
		if ( 0 !== strpos( (string) $package, 'https://api.github.com/repos/' . self::REPO . '/releases/assets/' ) ) {
			return $reply;
		}
		$res = wp_remote_get( $package, array( 'headers' => self::headers( 'application/octet-stream' ), 'redirection' => 0, 'timeout' => 15 ) );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$code = wp_remote_retrieve_response_code( $res );
		$to   = wp_remote_retrieve_header( $res, 'location' );
		if ( $code < 300 || $code > 399 || ! $to ) {
			return new WP_Error( 'lu3g_download', 'GitHub non ha fornito il pacchetto (codice ' . $code . '): controlla LU3G_GITHUB_TOKEN in wp-config.php.' );
		}
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		return download_url( $to, 300 );
	}
}
