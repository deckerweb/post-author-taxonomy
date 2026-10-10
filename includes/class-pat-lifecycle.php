<?php
/**
 * Site-scoped rewrite maintenance and non-destructive plugin lifecycle.
 *
 * @package PostAuthorTaxonomy
 */
defined( 'ABSPATH' ) || exit;

/** Preserve author content while coordinating rewrite caches across network sites. */
final class DDW_PAT_Lifecycle {
	/** Register site initialization and lazy rewrite maintenance. @return void */
	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'refresh_rewrites' ), 110 );
		add_action( 'wp_initialize_site', array( __CLASS__, 'initialize_site' ), 200 );
	}

	/** Load the optional public builder adapter only when the active theme provides Bricks. @return void */
	public static function load_builder(): void {
		if ( defined( 'BRICKS_VERSION' ) ) {
			require_once PAT_PLUGIN_DIR . 'includes/class-pat-bricks.php';
			( new DDW_PAT_Bricks() )->register();
		}
	}

	/**
	 * Register this site's taxonomy without installing duplicate request hooks.
	 *
	 * @return void
	 */
	private static function register_site(): void {
		$core = new DDW_Post_Author_Taxonomy( false );
		$core->load_translations();
		$core->register_taxonomy();
		$core->register_meta();
	}

	/**
	 * Activate on one site or mark a whole network for lazy rewrite regeneration.
	 * Network markers avoid scanning all sites during an activation request.
	 *
	 * @param bool $network_wide Whether WordPress activates the plugin for the network.
	 * @return void
	 */
	public static function activate( $network_wide = false ): void {
		self::register_site();
		if ( is_multisite() && $network_wide ) {
			update_network_option( get_current_network_id(), 'pat_rewrite_generation', wp_generate_uuid4() );
		}
		update_option( 'pat_flush_rewrite', 1, true );
		self::refresh_rewrites();
	}

	/**
	 * Regenerate this site's rules after registration when its settings or network marker changed.
	 * Settings and content remain site-local; only the invalidation marker is network-wide.
	 *
	 * @return void
	 */
	public static function refresh_rewrites(): void {
		// Keep the tiny pending marker in Core's existing autoload cache, including upgrades without activation.
		$alloptions = wp_load_alloptions();
		if ( ! array_key_exists( 'pat_flush_rewrite', $alloptions ) ) {
			$pending = get_option( 'pat_flush_rewrite', false );
			if ( false === $pending ) {
				add_option( 'pat_flush_rewrite', 0, '', true );
			} elseif ( ! $pending ) {
				wp_set_option_autoload_values( array( 'pat_flush_rewrite' => true ) );
			}
		}
		$generation = '';
		if ( is_multisite() ) {
			$active = get_network_option( get_current_network_id(), 'active_sitewide_plugins', array() );
			if ( isset( $active[ plugin_basename( PAT_PLUGIN_FILE ) ] ) ) {
				$generation = get_network_option( get_current_network_id(), 'pat_rewrite_generation', '' );
			}
		}
		if ( ! get_option( 'pat_flush_rewrite' ) && ( '' === $generation || get_option( 'pat_rewrite_generation', '' ) === $generation ) ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'pat_flush_rewrite', 0, true );
		if ( '' !== $generation ) {
			update_option( 'pat_rewrite_generation', $generation, true );
		}
	}

	/**
	 * Mark a newly initialized network site for a site-language-aware rewrite refresh.
	 *
	 * @param WP_Site $site Newly initialized WordPress site.
	 * @return void
	 */
	public static function initialize_site( $site ): void {
		$active = get_network_option( $site->network_id, 'active_sitewide_plugins', array() );
		if ( isset( $active[ plugin_basename( PAT_PLUGIN_FILE ) ] ) ) {
			update_blog_option( $site->blog_id, 'pat_flush_rewrite', 1 );
		}
	}

	/**
	 * Remove stale archive rules after site or network deactivation, preserving all author data.
	 * Network sites are read in bounded batches; switched site context is always restored.
	 *
	 * @param bool $network_wide Whether WordPress deactivates the plugin for the network.
	 * @return void
	 */
	public static function deactivate( $network_wide = false ): void {
		if ( is_multisite() && $network_wide ) {
			$offset = 0;
			do {
				$ids = get_sites( array( 'network_id' => get_current_network_id(), 'fields' => 'ids', 'number' => 100, 'offset' => $offset, 'orderby' => 'id', 'order' => 'ASC' ) );
				foreach ( $ids as $id ) {
					switch_to_blog( (int) $id );
					try {
						self::clear_site_rewrites();
					} finally {
						restore_current_blog();
					}
				}
				$offset += count( $ids );
			} while ( 100 === count( $ids ) );
			delete_network_option( get_current_network_id(), 'pat_rewrite_generation' );
			global $wp_rewrite;
			$wp_rewrite->init();
		} else {
			self::clear_site_rewrites();
		}
	}

	/** Remove this site's taxonomy rules and invalidation flags without deleting terms or settings. @return void */
	private static function clear_site_rewrites(): void {
		global $wp_rewrite;
		$wp_rewrite->init();
		unregister_taxonomy( DDW_Post_Author_Taxonomy::TAXONOMY );
		flush_rewrite_rules( false );
		delete_option( 'pat_flush_rewrite' );
		delete_option( 'pat_rewrite_generation' );
	}
}
