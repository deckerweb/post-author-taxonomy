<?php
/**
 * Remove only disposable plugin caches and delegate shared component ownership checks.
 * Author terms, assignments, profile metadata and settings are deliberately retained.
 *
 * @package PostAuthorTaxonomy
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Clean the current site's rewrite invalidation markers, preserving all persistent plugin data.
 *
 * @return void
 */
function ddw_pat_clear_temporary_site_data(): void {
	delete_option( 'pat_flush_rewrite' );
	delete_option( 'pat_rewrite_generation' );
}

if ( is_multisite() ) {
	$offset = 0;
	do {
		$ids = get_sites( array( 'fields' => 'ids', 'number' => 100, 'offset' => $offset, 'orderby' => 'id', 'order' => 'ASC' ) );
		foreach ( $ids as $id ) {
			switch_to_blog( (int) $id );
			try {
				ddw_pat_clear_temporary_site_data();
			} finally {
				restore_current_blog();
			}
		}
		$offset += count( $ids );
	} while ( 100 === count( $ids ) );
	foreach ( get_networks( array( 'fields' => 'ids', 'number' => 0 ) ) as $network_id ) {
		delete_network_option( (int) $network_id, 'pat_rewrite_generation' );
	}
} else {
	ddw_pat_clear_temporary_site_data();
}
// WordPress stores updater metadata as site transients: network-scoped on Multisite.
$cache = 'ddw_ghru_' . substr( md5( 'https://github.com/deckerweb/post-author-taxonomy' ), 0, 24 );
if ( is_multisite() ) {
	foreach ( get_networks( array( 'fields' => 'ids', 'number' => 0 ) ) as $network_id ) {
		delete_network_option( (int) $network_id, '_site_transient_' . $cache );
		delete_network_option( (int) $network_id, '_site_transient_timeout_' . $cache );
	}
} else {
	delete_site_transient( $cache );
}
require_once __DIR__ . '/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v3( __DIR__ . '/post-author-taxonomy.php' );
