<?php
/**
 * Remove only disposable plugin caches and delegate shared component ownership checks.
 * Author terms, assignments, profile metadata and settings are deliberately retained.
 *
 * @package PostAuthorTaxonomy
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Keep temporary cleanup variables out of the global plugin namespace.
(static function (): void {
	/**
	 * Clean the current site's rewrite invalidation markers, preserving all persistent plugin data.
	 *
	 * @return void
	 */
	$pat_clear_site = static function (): void {
		delete_option( 'pat_flush_rewrite' );
		delete_option( 'pat_rewrite_generation' );
	};

	if ( is_multisite() ) {
		$pat_offset = 0;
		do {
			$pat_ids = get_sites( array( 'fields' => 'ids', 'number' => 100, 'offset' => $pat_offset, 'orderby' => 'id', 'order' => 'ASC' ) );
			foreach ( $pat_ids as $pat_id ) {
				switch_to_blog( (int) $pat_id );
				try {
					$pat_clear_site();
				} finally {
					restore_current_blog();
				}
			}
			$pat_offset += count( $pat_ids );
		} while ( 100 === count( $pat_ids ) );
		foreach ( get_networks( array( 'fields' => 'ids', 'number' => 0 ) ) as $pat_network_id ) {
			delete_network_option( (int) $pat_network_id, 'pat_rewrite_generation' );
		}
	} else {
		$pat_clear_site();
	}
	// WordPress stores updater metadata as site transients: network-scoped on Multisite.
	$pat_updater_class = require __DIR__ . '/includes/deckerweb-github-release-updater-v2.php';
	$pat_repository = 'https://github.com/deckerweb/post-author-taxonomy';
	$pat_caches = array(
		'ddw_ghru_' . substr( md5( $pat_repository ), 0, 24 ),
		'ddw_ghru_' . substr( md5( $pat_repository . '|' . $pat_updater_class::IMPLEMENTATION_VERSION ), 0, 24 ),
	);
	foreach ( $pat_caches as $pat_cache_base ) {
		foreach ( array( $pat_cache_base, $pat_cache_base . '_private' ) as $pat_cache ) {
			if ( is_multisite() ) {
				foreach ( get_networks( array( 'fields' => 'ids', 'number' => 0 ) ) as $pat_network_id ) {
					delete_network_option( (int) $pat_network_id, '_site_transient_' . $pat_cache );
					delete_network_option( (int) $pat_network_id, '_site_transient_timeout_' . $pat_cache );
				}
			} else {
				delete_site_transient( $pat_cache );
			}
		}
	}
	require_once __DIR__ . '/includes/deckerweb-plugin-library/lifecycle.php';
	deckerweb_library_uninstall_v3( __DIR__ . '/post-author-taxonomy.php' );
})();
