<?php
/** Regression checks for host maintenance in explicitly disposable WordPress sites. */
if ( 'yes' !== getenv( 'PAT_TEST_DISPOSABLE' ) ) { fwrite( STDERR, "Disposable test opt-in required.\n" ); exit( 1 ); }
define( 'WP_ADMIN', true );
require rtrim( getenv( 'PAT_TEST_ROOT' ), '/' ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
set_exception_handler( static function ( $error ) { fwrite( STDERR, $error->getMessage() . "\n" ); exit( 1 ); } );
$checks = 0;
$photo_id = (int) getenv( 'PAT_TEST_PHOTO_ID' );
if ( ! wp_attachment_is_image( $photo_id ) ) { throw new RuntimeException( 'Explicit local test image is required.' ); }
/** Assert one maintenance behavior. @param bool $ok Condition. @param string $label Behavior name. @return void */
function pat_maintenance_check( $ok, $label ) { global $checks; if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; ++$checks; }
wp_set_current_user( 1 );
$runtime = $GLOBALS['deckerweb_library_runtime_v1'] ?? null;
pat_maintenance_check( is_object( $runtime ) && str_contains( get_class( $runtime ), 'V0_9_0' ), 'Library 0.9.0 elected in the host' );
pat_maintenance_check( '2.1.0' === \Deckerweb\GitHubReleaseUpdater\V2\Updater::IMPLEMENTATION_VERSION, 'Canonical updater 2.1.0 loaded' );
$updates = new \Deckerweb\PostAuthorTaxonomy\GitHubUpdates();
foreach ( array( 'de_DE', 'de_DE_formal' ) as $test_locale ) {
	$switched = switch_to_locale( $test_locale );
	( new DDW_Post_Author_Taxonomy( false ) )->load_translations();
	pat_maintenance_check( 'Schließen' === __( 'Close', 'post-author-taxonomy' ), $test_locale . ' host translation' );
	pat_maintenance_check( 'See the release on GitHub.' !== $updates->translate( 'See the release on GitHub.' ), $test_locale . ' updater translation' );
	pat_maintenance_check( ( 'de_DE_formal' === $test_locale ? 'Ihre Autoren. Ihr Layout. Ihr WordPress.' : 'Deine Autoren. Dein Layout. Dein WordPress.' ) === __( 'Your authors. Your layout. Your WordPress.', 'post-author-taxonomy' ), $test_locale . ' distinct address form' );
	$html = \Deckerweb\PostAuthorTaxonomy\Changelog::content();
	pat_maintenance_check( str_contains( $html, 'pat-changelog-badge' ) && str_contains( $html, 'Neu:' ) && ! str_contains( $html, '<pre' ), $test_locale . ' structured localized changelog' );
	if ( $switched ) { restore_previous_locale(); }
}
wp_set_current_user( 1 );
$html = \Deckerweb\PostAuthorTaxonomy\Changelog::content();
pat_maintenance_check( 4 === substr_count( $html, '<h3>' ), 'All four available historical releases retained' );
$old = get_option( 'pat_settings', array() );
update_option( 'pat_settings', array( 'post_types' => array( 'post', 'page' ), 'link' => 'none' ) );
$core = new DDW_Post_Author_Taxonomy( false );
$core->register_taxonomy();
pat_maintenance_check( in_array( 'page', get_taxonomy( 'pat-author' )->object_type, true ), 'Configured post types preserved' );
$term = wp_insert_term( 'Maintenance author ' . wp_generate_uuid4(), 'pat-author' );
update_term_meta( $term['term_id'], 'pat_website', 'https://maintenance.example.test' );
DDW_PAT_Lifecycle::activate( false );
pat_maintenance_check( ! get_option( 'pat_flush_rewrite' ), 'Site activation consumes rewrite flag' );
DDW_PAT_Lifecycle::deactivate( false );
pat_maintenance_check( 'https://maintenance.example.test' === get_term_meta( $term['term_id'], 'pat_website', true ), 'Deactivation preserves author metadata' );
pat_maintenance_check( array( 'post_types' => array( 'post', 'page' ), 'link' => 'none' ) === get_option( 'pat_settings' ), 'Deactivation preserves settings' );
$core->register_taxonomy();
update_option( 'pat_settings', $old );
$core->register_taxonomy();
$valid_settings = get_option( 'pat_settings' );
update_option( 'pat_settings', array( 'post_types' => 'invalid' ) ); $core->register_taxonomy();
pat_maintenance_check( array( 'post' ) === get_taxonomy( 'pat-author' )->object_type, 'Malformed stored post types safely fall back to Posts' );
update_option( 'pat_settings', $valid_settings ); $core->register_taxonomy();
$admin = new DDW_PAT_Admin();
wp_set_current_user( 0 );
pat_maintenance_check( array( 'deactivate' => 'unchanged' ) === $admin->links( array( 'deactivate' => 'unchanged' ) ), 'Unauthorized plugin rows do not advertise settings links' );
wp_set_current_user( 1 );
$links = $admin->links( array( 'deactivate' => 'unchanged' ) );
pat_maintenance_check( str_contains( reset( $links ), 'options-general.php?page=post-author-taxonomy' ), 'Settings action link leads before deactivation' );
WP_Filesystem();
$fixture = ABSPATH . 'pat-maintenance-update';
$wp_filesystem->delete( $fixture, true );
wp_mkdir_p( $fixture );
$main = "<?php\n/*\nPlugin Name: Post Author Taxonomy\nVersion: 1.4.1\nUpdate URI: https://github.com/deckerweb/post-author-taxonomy\nRequires PHP: 8.0\nRequires at least: 6.7\n*/\nrequire_once __DIR__ . '/includes/required.php';\n";
file_put_contents( $fixture . '/post-author-taxonomy.php', $main );
set_site_transient( 'update_plugins', (object) array( 'response' => array( plugin_basename( PAT_PLUGIN_FILE ) => (object) array( 'new_version' => '1.4.1' ) ) ) );
$bulk = new Plugin_Upgrader(); $bulk->bulk = true;
$context = array( 'plugin' => plugin_basename( PAT_PLUGIN_FILE ) );
pat_maintenance_check( is_wp_error( $updates->validate_source( $fixture, null, $bulk, $context ) ), 'Bulk update rejects missing required bootstrap includes' );
wp_mkdir_p( $fixture . '/includes' ); file_put_contents( $fixture . '/includes/required.php', '<?php' );
pat_maintenance_check( $fixture === $updates->validate_source( $fixture, null, $bulk, $context ), 'Native bulk context reaches candidate validation' );
$context['action'] = 'install';
pat_maintenance_check( $fixture === $updates->validate_source( $fixture, null, $bulk, $context ), 'Explicit non-update context left untouched' );
$context = array( 'plugin' => plugin_basename( PAT_PLUGIN_FILE ), 'type' => 'plugin', 'action' => 'update' );
file_put_contents( $fixture . '/post-author-taxonomy.php', str_replace( '1.4.1', '1.4.2', $main ) );
pat_maintenance_check( is_wp_error( $updates->validate_source( $fixture, null, $bulk, $context ) ), 'Unexpected candidate version rejected before replacement' );
$post_id = wp_insert_post( array( 'post_title' => 'Non-image fixture', 'post_status' => 'publish' ) );
update_term_meta( $term['term_id'], 'pat_photo_id', $photo_id );
$request = new WP_REST_Request( 'POST', '/wp/v2/pat-author/' . $term['term_id'] );
$request->set_param( 'meta', array( 'pat_photo_id' => $post_id ) );
$response = rest_do_request( $request );
if ( 403 !== $response->get_status() ) { fwrite( STDERR, json_encode( array( 'status' => $response->get_status(), 'data' => $response->get_data(), 'hook' => has_filter( 'rest_request_before_callbacks' ) ) ) . "\n" ); }
pat_maintenance_check( 403 === $response->get_status(), 'REST profile rejects non-image photo references' );
pat_maintenance_check( $photo_id === (int) get_term_meta( $term['term_id'], 'pat_photo_id', true ), 'Rejected REST photo request preserves stored image' );
require_once ABSPATH . 'wp-admin/includes/user.php';
$limited = wp_create_user( 'pat-limited-' . wp_generate_password( 8, false ), wp_generate_password(), 'pat-' . wp_generate_uuid4() . '@example.test' );
$limited_user = new WP_User( $limited ); $limited_user->set_role( 'subscriber' ); $limited_user->add_cap( 'manage_categories' );
wp_set_current_user( $limited );
$request->set_param( 'meta', array( 'pat_photo_id' => $photo_id ) );
$response = rest_do_request( $request );
pat_maintenance_check( 403 === $response->get_status(), 'Term editor without image editing rights cannot assign another user photo' );
pat_maintenance_check( $photo_id === (int) get_term_meta( $term['term_id'], 'pat_photo_id', true ), 'Denied image permission preserves the existing photo' );
wp_set_current_user( 1 ); wp_delete_user( $limited );
$response = rest_do_request( $request );
pat_maintenance_check( 200 === $response->get_status(), 'Authorized image editors can save a valid local photo through REST' );
$_POST = array( 'pat_profile_nonce' => wp_create_nonce( 'pat_save_profile' ), 'pat_website' => 'https://partial.example.test' );
$admin->save_fields( $term['term_id'] );
pat_maintenance_check( $photo_id === (int) get_term_meta( $term['term_id'], 'pat_photo_id', true ), 'Partial admin profile save preserves an omitted photo' );
$_POST['pat_website'] = array( 'invalid' );
$admin->save_fields( $term['term_id'] );
pat_maintenance_check( 'https://partial.example.test' === get_term_meta( $term['term_id'], 'pat_website', true ), 'Invalid form shape does not erase the stored website' );
$request->set_param( 'meta', array( 'pat_photo_id' => 0 ) );
$response = rest_do_request( $request );
pat_maintenance_check( 200 === $response->get_status() && 0 === (int) get_term_meta( $term['term_id'], 'pat_photo_id', true ), 'Authorized REST clients can explicitly clear the photo' );
wp_delete_post( $post_id, true );
wp_delete_term( $term['term_id'], 'pat-author' );
$wp_filesystem->delete( $fixture, true );
echo "Completed $checks maintenance checks\n";
