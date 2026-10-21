<?php
/**
 * Integrate the shared deckerweb updater with plugin-scoped safeguards.
 *
 * @package PostAuthorTaxonomy
 */

namespace Deckerweb\PostAuthorTaxonomy;

defined( 'ABSPATH' ) || exit;

/** Configure shared update artwork and isolate plugin-specific safeguards. */
final class GitHubUpdates {
	/** Public release repository; never taken from user input. */
	private const REPOSITORY = 'https://github.com/deckerweb/post-author-taxonomy';

	/** Register after WordPress initializes translations. @return void */
	public function register(): void {
		add_action( 'init', array( $this, 'boot' ), 30 );
	}

	/** Load the shared versioned class once, including beside other deckerweb plugins. @return void */
	public function boot(): void {
		if ( ! is_admin() && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) { return; }
		if ( ! class_exists( '\Deckerweb\GitHubReleaseUpdater\V2\Updater' ) ) {
			require_once PAT_PLUGIN_DIR . 'includes/deckerweb-github-release-updater-v2.php';
		}
		if ( ! defined( '\\Deckerweb\\GitHubReleaseUpdater\\V2\\Updater::SUPPORTS_HOST_TRANSLATIONS' ) ) {
			add_action( 'admin_notices', array( $this, 'compatibility_notice' ) );
			add_action( 'network_admin_notices', array( $this, 'compatibility_notice' ) );
			return;
		}
		$options = array( 'translate' => array( $this, 'translate' ) );
		if ( defined( '\\Deckerweb\\GitHubReleaseUpdater\\V2\\Updater::SUPPORTS_CATALOG_PROVIDER' ) && function_exists( 'deckerweb_library_updater_options_v1' ) ) {
			$options = array_merge( $options, deckerweb_library_updater_options_v1( PAT_PLUGIN_FILE, self::REPOSITORY ) );
		}
		try {
			$updater = new \Deckerweb\GitHubReleaseUpdater\V2\Updater(
				PAT_PLUGIN_FILE,
				self::REPOSITORY,
				'Post Author Taxonomy',
				__( 'Authors as a public taxonomy: multiple authors, local photos, profiles and flexible shortcodes for your builder or theme.', 'post-author-taxonomy' ),
				$this->artwork(),
				$options
			);
		} catch ( \InvalidArgumentException $error ) {
			// An unsupported directory must not break the plugin interface.
			return;
		}
		$updater->register();
		add_filter( 'http_request_args', array( $this, 'request_limits' ), 20, 2 );
		add_filter( 'upgrader_source_selection', array( $this, 'validate_source' ), 30, 4 );
	}

	/**
	 * Provide bundled artwork matching the current administrator's language.
	 *
	 * @return array<string,array<string,string>> WordPress icon and banner maps.
	 */
	public function artwork(): array {
		$language = 1 === preg_match( '/^de(?:_|$)/i', determine_locale() ) ? 'de-' : '';
		return array(
			'icons'   => array(
				'svg' => plugins_url( 'assets/icon.svg', PAT_PLUGIN_FILE ),
				'1x'  => plugins_url( 'assets/icon-128x128.png', PAT_PLUGIN_FILE ),
				'2x'  => plugins_url( 'assets/icon-256x256.png', PAT_PLUGIN_FILE ),
			),
			'banners' => array(
				'low'  => plugins_url( 'assets/banner-' . $language . '772x250.png', PAT_PLUGIN_FILE ),
				'high' => plugins_url( 'assets/banner-' . $language . '1544x500.png', PAT_PLUGIN_FILE ),
			),
		);
	}

	/**
	 * Bound only this repository's metadata requests; do not change package downloads.
	 *
	 * @param array  $args WordPress HTTP arguments.
	 * @param string $url Requested URL.
	 * @return array
	 */
	public function request_limits( array $args, string $url ): array {
		if ( 'https://api.github.com/repos/deckerweb/post-author-taxonomy/releases/latest' === $url ) {
			$args['limit_response_size'] = 512 * 1024;
			$args['timeout']             = 6;
			$args['redirection']         = 0;
			$args['sslverify']           = true;
			$args['reject_unsafe_urls']  = true;
		}
		return $args;
	}

	/**
	 * Validate the actual candidate before WordPress removes the installed plugin.
	 * Requirements in installed headers cannot describe a future release reliably.
	 *
	 * @param mixed $source Normalized directory or WP_Error from the shared updater.
	 * @param mixed $remote_source Unused extraction root.
	 * @param mixed $upgrader Unused WordPress upgrader instance.
	 * @param array $hook_extra Upgrade context.
	 * @return mixed Original source or localized WP_Error.
	 */
	public function validate_source( $source, $remote_source, $upgrader, array $hook_extra ) {
		if ( ( $hook_extra['plugin'] ?? '' ) !== plugin_basename( PAT_PLUGIN_FILE ) || ( isset( $hook_extra['type'] ) && 'plugin' !== $hook_extra['type'] ) || ( isset( $hook_extra['action'] ) && 'update' !== $hook_extra['action'] ) || ( ! isset( $hook_extra['type'], $hook_extra['action'] ) && ! ( $upgrader instanceof \Plugin_Upgrader && true === $upgrader->bulk ) ) ) {
			return $source;
		}
		if ( is_wp_error( $source ) ) {
			$messages = array(
				'ddw_ghru_filesystem' => __( 'The update filesystem is unavailable. Please try again.', 'post-author-taxonomy' ),
				'ddw_ghru_archive'    => __( 'The GitHub package does not contain the Post Author Taxonomy plugin file.', 'post-author-taxonomy' ),
				'ddw_ghru_rename'     => __( 'The GitHub package could not be prepared. The installed version has been kept.', 'post-author-taxonomy' ),
			);
			$code     = $source->get_error_code();
			return isset( $messages[ $code ] ) ? new \WP_Error( $code, $messages[ $code ], $source->get_error_data( $code ) ) : $source;
		}
		global $wp_filesystem;
		if ( ! is_string( $source ) || ! $wp_filesystem ) {
			return new \WP_Error( 'pat_update_source', __( 'The update package could not be checked.', 'post-author-taxonomy' ) );
		}
		$main = trailingslashit( $source ) . basename( PAT_PLUGIN_FILE );
		if ( ! $wp_filesystem->is_file( $main ) || $wp_filesystem->size( $main ) > 1024 * 1024 ) {
			return new \WP_Error( 'pat_update_source', __( 'The update package could not be checked.', 'post-author-taxonomy' ) );
		}
		$text = $wp_filesystem->get_contents( $main );
		if ( ! is_string( $text ) ) {
			return new \WP_Error( 'pat_update_source', __( 'The update package could not be checked.', 'post-author-taxonomy' ) );
		}
		$headers = array();
		foreach ( array( 'Plugin Name', 'Version', 'Update URI', 'Requires PHP', 'Requires at least' ) as $header ) {
			$headers[ $header ] = preg_match( '/^[ \t\/*#@]*' . preg_quote( $header, '/' ) . ':(.*)$/mi', str_replace( "\r", "\n", substr( $text, 0, 8192 ) ), $match ) ? trim( $match[1] ) : '';
		}
		if ( 'Post Author Taxonomy' !== $headers['Plugin Name'] || self::REPOSITORY !== $headers['Update URI'] || ! preg_match( '/^\d+\.\d+\.\d+$/D', $headers['Version'] ) || version_compare( $headers['Version'], \DDW_Post_Author_Taxonomy::VERSION, '<=' ) ) {
			return new \WP_Error( 'pat_update_identity', __( 'The package identity or version does not match a newer Post Author Taxonomy release.', 'post-author-taxonomy' ) );
		}
		$current  = get_site_transient( 'update_plugins' );
		$expected = is_object( $current ) ? ( $current->response[ plugin_basename( PAT_PLUGIN_FILE ) ]->new_version ?? '' ) : '';
		if ( ! is_string( $expected ) || '' === $expected || $headers['Version'] !== $expected ) {
			return new \WP_Error( 'pat_update_version', __( 'The package version differs from the offered update. Please check for updates again.', 'post-author-taxonomy' ) );
		}
		foreach ( array( 'Requires PHP', 'Requires at least' ) as $header ) {
			if ( ! preg_match( '/^\d+\.\d+(?:\.\d+)?$/D', $headers[ $header ] ) ) {
				return new \WP_Error( 'pat_update_requirements', __( 'The update package has missing or invalid WordPress/PHP requirements.', 'post-author-taxonomy' ) );
			}
		}
		if ( ! is_php_version_compatible( $headers['Requires PHP'] ) || ! is_wp_version_compatible( $headers['Requires at least'] ) ) {
			return new \WP_Error( 'pat_update_compatibility', __( 'This release requires a newer WordPress or PHP version. The installed version has been kept.', 'post-author-taxonomy' ) );
		}
		if ( preg_match_all( '/require(?:_once)?\s+__DIR__\s*\.\s*[\"\'](\/includes\/[^\"\']+\.php)[\"\']/', $text, $includes ) ) {
			foreach ( $includes[1] as $include ) {
				if ( str_contains( $include, '..' ) || ! $wp_filesystem->is_file( untrailingslashit( $source ) . $include ) ) {
					return new \WP_Error( 'pat_update_incomplete', __( 'The update package is incomplete. The installed plugin has been kept.', 'post-author-taxonomy' ) );
				}
			}
		}
		return $source;
	}

	/**
	 * Translate shared-engine messages through this host's existing textdomain at output time.
	 *
	 * @param string $message Original English component message.
	 * @return string Localized message, or unchanged input for an unknown component message.
	 */
	public function translate( string $message ): string {
    switch ( $message ) {
        case 'Private mode must be boolean.':
            return __( 'Private mode must be boolean.', 'post-author-taxonomy' );
        case 'Invalid authentication provider.':
            return __( 'Invalid authentication provider.', 'post-author-taxonomy' );
        case 'The plugin must be installed in a stable slug directory.':
            return __( 'The plugin must be installed in a stable slug directory.', 'post-author-taxonomy' );
        case 'Invalid GitHub repository URL.':
            return __( 'Invalid GitHub repository URL.', 'post-author-taxonomy' );
        case 'The private update could not be authorized. Check the repository credentials and refresh updates.':
            return __( 'The private update could not be authorized. Check the repository credentials and refresh updates.', 'post-author-taxonomy' );
        case 'Could not create the update download file.':
            return __( 'Could not create the update download file.', 'post-author-taxonomy' );
        case 'The private update download failed. Check credentials and try again.':
            return __( 'The private update download failed. Check credentials and try again.', 'post-author-taxonomy' );
        case 'Could not access the update filesystem.':
            return __( 'Could not access the update filesystem.', 'post-author-taxonomy' );
        case 'GitHub release does not contain the plugin main file.':
            return __( 'GitHub release does not contain the plugin main file.', 'post-author-taxonomy' );
        case 'Could not prepare the GitHub release package.':
            return __( 'Could not prepare the GitHub release package.', 'post-author-taxonomy' );
        case 'See the release on GitHub.':
            return __( 'See the release on GitHub.', 'post-author-taxonomy' );
        case 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.':
            return __( 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.', 'post-author-taxonomy' );
        case 'Manage installed plugins':
            return __( 'Manage installed plugins', 'post-author-taxonomy' );
        case 'Dismiss this notice':
            return __( 'Dismiss this notice', 'post-author-taxonomy' );
        case 'Permission denied.':
            return __( 'Permission denied.', 'post-author-taxonomy' );
        case 'Public catalog callbacks require both providers and cannot be used in private mode.':
            return __( 'Public catalog callbacks require both providers and cannot be used in private mode.', 'post-author-taxonomy' );
        case 'The approved release could not be verified.':
            return __( 'The approved release could not be verified.', 'post-author-taxonomy' );
        case 'The approved release changed. Refresh updates first.':
            return __( 'The approved release changed. Refresh updates first.', 'post-author-taxonomy' );
        default:
            return $message;
    }
	}

	/** Report a shared-class compatibility conflict only on relevant administrative screens. @return void */
	public function compatibility_notice(): void {
		$screen = get_current_screen();
		if ( ! current_user_can( 'update_plugins' ) || ! $screen || ! in_array( $screen->id, array( 'plugins', 'plugins-network', 'update-core', 'update-core-network', 'settings_page_post-author-taxonomy' ), true ) ) { return; }
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Post Author Taxonomy updates are paused because another plugin loaded an older deckerweb updater. Update the other deckerweb plugin to restore compatible updates.', 'post-author-taxonomy' ) . '</p></div>';
	}
}
