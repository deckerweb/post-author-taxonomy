<?php
/** Validate network activation, new sites and data-preserving removal in a disposable network. */
if ( 'yes' !== getenv( 'PAT_TEST_DISPOSABLE' ) ) { fwrite( STDERR, "Disposable network opt-in required.\n" ); exit( 1 ); }
define( 'WP_ADMIN', true ); define( 'WP_NETWORK_ADMIN', true );
require rtrim( getenv( 'PAT_TEST_ROOT' ), '/' ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/schema.php';
set_exception_handler( static function ( $error ) { fwrite( STDERR, $error->getMessage() . "\n" ); exit( 1 ); } );
if ( ! is_multisite() ) { throw new RuntimeException( 'Actual Multisite installation required.' ); }
$checks = 0;
/** Assert a network behavior. @param bool $ok Condition. @param string $label Behavior. @return void */
function pat_network_check( $ok, $label ) { global $checks; if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; ++$checks; }
wp_set_current_user( 1 );
update_site_option( 'site_admins', array( get_userdata( 1 )->user_login ) );
remove_all_filters( 'pre_site_option_active_sitewide_plugins' );
update_site_option( 'active_sitewide_plugins', array() );
$file = plugin_basename( PAT_PLUGIN_FILE );
$result = activate_plugin( $file, '', true );
pat_network_check( ! is_wp_error( $result ) && is_plugin_active_for_network( $file ), 'Native network activation succeeds' );
pat_network_check( '' !== get_network_option( get_current_network_id(), 'pat_rewrite_generation', '' ), 'Network activation creates a lazy rewrite generation' );
$site = wp_insert_site( array( 'domain' => '127.0.0.1:8877', 'path' => '/pat-maintenance-' . wp_rand( 10000, 99999 ) . '/', 'network_id' => get_current_network_id(), 'public' => 0 ) );
if ( is_wp_error( $site ) ) { throw new RuntimeException( $site->get_error_message() ); }
pat_network_check( 1 === (int) get_blog_option( $site, 'pat_flush_rewrite' ), 'New network site scheduled for rewrite refresh' );
$original_blog = get_current_blog_id();
switch_to_blog( $site );
update_option( 'WPLANG', 'de_DE' );
update_option( 'pat_settings', array( 'post_types' => array( 'post', 'page' ), 'link' => 'none' ) );
$core = new DDW_Post_Author_Taxonomy( false ); $core->register_taxonomy();
$term = wp_insert_term( 'Network author', 'pat-author' );
update_term_meta( $term['term_id'], 'pat_website', 'https://network.example.test' );
$attribution_post = wp_insert_post( array( 'post_title' => 'Network attribution fixture', 'post_status' => 'publish' ) );
wp_set_post_terms( $attribution_post, array( $term['term_id'] ), 'pat-author' );
$attribution_value = array( array( 'id' => $term['term_id'], 'role' => 'reviewer' ) );
update_post_meta( $attribution_post, DDW_PAT_Attribution::META, $attribution_value );
DDW_PAT_Lifecycle::refresh_rewrites();
pat_network_check( ! get_option( 'pat_flush_rewrite' ) && '' !== get_option( 'pat_rewrite_generation', '' ), 'New site refresh consumes pending rewrite state' );
pat_network_check( in_array( 'page', get_taxonomy( 'pat-author' )->object_type, true ), 'Network site retains its own post-type configuration' );
pat_network_check( 'beitragsautor' === get_taxonomy( 'pat-author' )->rewrite['slug'], 'Network author archive follows its site language' );
restore_current_blog(); $core->register_taxonomy();
pat_network_check( get_option( 'pat_settings', array() ) !== get_blog_option( $site, 'pat_settings' ), 'Site settings do not leak into the main site' );
$links = ( new DDW_PAT_Admin() )->links( array( 'deactivate' => 'existing' ) );
pat_network_check( array( 'deactivate' => 'existing' ) === $links, 'Network plugin list omits nonexistent network settings links' );
deactivate_plugins( $file, false, true );
pat_network_check( ! is_plugin_active_for_network( $file ), 'Native network deactivation succeeds' );
pat_network_check( get_current_blog_id() === $original_blog, 'Network deactivation restores the original site context' );
switch_to_blog( $site );
pat_network_check( 'https://network.example.test' === get_term_meta( $term['term_id'], 'pat_website', true ), 'Network deactivation preserves author profile metadata' );
pat_network_check( $attribution_value === get_post_meta( $attribution_post, DDW_PAT_Attribution::META, true ), 'Network deactivation preserves post-local attribution' );
pat_network_check( array( 'post_types' => array( 'post', 'page' ), 'link' => 'none' ) === get_option( 'pat_settings' ), 'Network deactivation preserves site settings' );
pat_network_check( ! get_option( 'pat_rewrite_generation' ) && ! get_option( 'pat_flush_rewrite' ), 'Network deactivation removes stale site markers' );
update_option( 'pat_flush_rewrite', 1 );
restore_current_blog();
$other = WP_PLUGIN_DIR . '/pat-inactive-host';
wp_mkdir_p( $other . '/includes/deckerweb-plugin-library' );
file_put_contents( $other . '/pat-inactive-host.php', "<?php\n/* Plugin Name: Inactive Library host fixture */\n" );
copy( PAT_PLUGIN_DIR . 'includes/deckerweb-plugin-library/bootstrap.php', $other . '/includes/deckerweb-plugin-library/bootstrap.php' );
wp_clean_plugins_cache( true );
update_site_option( 'deckerweb_library_settings_v1', array( 'show_tab' => false, 'delete_settings' => false ) );
set_site_transient( 'ddw_ghru_' . substr( md5( 'https://github.com/deckerweb/post-author-taxonomy' ), 0, 24 ), array( 'version' => '1.3.0' ), 3600 );
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { define( 'WP_UNINSTALL_PLUGIN', $file ); }
require PAT_PLUGIN_DIR . 'uninstall.php';
pat_network_check( array( 'show_tab' => false, 'delete_settings' => false ) === get_site_option( 'deckerweb_library_settings_v1' ), 'Inactive installed Library host protects shared settings' );
pat_network_check( false === get_site_transient( 'ddw_ghru_' . substr( md5( 'https://github.com/deckerweb/post-author-taxonomy' ), 0, 24 ) ), 'Uninstall removes the host updater cache' );
switch_to_blog( $site );
pat_network_check( ! get_option( 'pat_flush_rewrite' ), 'Uninstall removes transient markers on all sites' );
pat_network_check( 'https://network.example.test' === get_term_meta( $term['term_id'], 'pat_website', true ), 'Uninstall retains user-authored profiles' );
pat_network_check( 'none' === get_option( 'pat_settings' )['link'], 'Uninstall retains host settings' );
pat_network_check( $attribution_value === get_post_meta( $attribution_post, DDW_PAT_Attribution::META, true ), 'Network uninstall preserves author order and editorial roles' );
restore_current_blog();
require_once ABSPATH . 'wp-admin/includes/file.php'; WP_Filesystem();
$wp_filesystem->delete( $other, true ); wp_clean_plugins_cache( true );
pat_network_check( deckerweb_library_uninstall_v3( PAT_PLUGIN_FILE ), 'Final-host shared cleanup executes after the inactive host is removed' );
pat_network_check( array( 'show_tab' => false, 'delete_settings' => false ) === get_site_option( 'deckerweb_library_settings_v1' ), 'Final-host cleanup preserves Library settings by default' );
echo "Completed $checks Multisite checks\n";
