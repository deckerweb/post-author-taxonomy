<?php
if ( 'yes' !== getenv( 'PAT_TEST_DISPOSABLE' ) ) { fwrite( STDERR, 'Use an isolated disposable test site.\n' ); exit(1); }
require rtrim( getenv( 'PAT_TEST_ROOT' ), '/' ) . '/wp-load.php';
wp_set_current_user( 1 );
update_option( 'pat_settings', array( 'post_types' => array( 'post' ), 'link' => 'archive' ) );
set_exception_handler( function( $error ) { fwrite( STDERR, $error->getMessage() . '\n' ); exit(1); } );
set_error_handler( function( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
$checks = 0;
function check( $condition, $name ) { global $checks; if ( ! $condition ) { throw new RuntimeException( $name ); } echo "PASS $name\n"; $checks++; }
$term = wp_insert_term( 'Alice & Bob', 'pat-author', array( 'slug' => 'alice-bob', 'description' => 'A short biography.' ) );
if ( is_wp_error( $term ) ) { $term = array( 'term_id' => get_term_by( 'slug', 'alice-bob', 'pat-author' )->term_id ); }
$id = $term['term_id'];
$other = wp_insert_term( 'Jane Doe', 'pat-author', array( 'slug' => 'jane-doe' ) );
if ( is_wp_error( $other ) ) { $other = array( 'term_id' => get_term_by( 'slug', 'jane-doe', 'pat-author' )->term_id ); }
$post_id = wp_insert_post( array( 'post_title' => 'Author test', 'post_status' => 'publish', 'post_type' => 'post' ) );
wp_set_object_terms( $post_id, array( $id, $other['term_id'] ), 'pat-author' );
$GLOBALS['post'] = get_post( $post_id );
check( '' === do_shortcode( '[pat-author-box]' ), 'No implicit author on ordinary post' );
check( '' === do_shortcode( '[pat-author-box id="99999999"]' ), 'Unknown ID yields empty output' );
check( '' === do_shortcode( '[pat-author-box slug="missing"]' ), 'Unknown slug yields empty output' );
check( '' === do_shortcode( '[pat-author-box id="bad" slug="alice-bob"]' ), 'Invalid ID does not fall through' );
$box = do_shortcode( '[pat-author-box name="Alice & Bob"]' );
check( str_contains( $box, 'Alice &amp; Bob' ), 'Raw name lookup and escaped output' );
$GLOBALS['post'] = get_post( wp_insert_post( array( 'post_title' => 'A page', 'post_type' => 'page', 'post_status' => 'publish' ) ) );
check( str_contains( do_shortcode( '[pat-author-box slug="alice-bob"]' ), 'Alice' ), 'Explicit author works on unregistered page' );
$GLOBALS['post'] = get_post( $post_id );
$list = do_shortcode( '[pat-authors after="END" class="foo bar"]' );
check( 1 === substr_count( $list, 'END' ), 'Suffix rendered exactly once' );
check( str_contains( $list, 'pat-authors foo bar' ), 'Multiple classes preserved' );
check( ! str_contains( do_shortcode( '[pat-authors link="none"]' ), '<a ' ), 'Plain author list' );
update_term_meta( $id, 'pat_website', 'https://example.test/alice' );
check( str_contains( do_shortcode( '[pat-authors link="website"]' ), 'https://example.test/alice' ), 'Website links' );
check( 2 === substr_count( do_shortcode( '[pat-author-boxes]' ), 'class="pat-author-box"' ), 'All current-post authors' );
check( ! str_contains( do_shortcode( '[pat-authors wrapper="script"]' ), '<script' ), 'Disallowed wrapper falls back' );
check( ! str_contains( do_shortcode( '[pat-author-box slug="alice-bob" photo="no" headline="<img src=x onerror=alert(1)>"]' ), '<img' ), 'Headline escaped' );
check( ! str_contains( do_shortcode( '[pat-authors before="<img src=x onerror=alert(1)>"]' ), '<img' ), 'Unsafe label removed' );
add_filter( 'pat/shortcode/author-box', fn( $html ) => $html . 'FILTER' );
check( str_ends_with( do_shortcode( '[pat-author-box slug="alice-bob"]' ), 'FILTER' ), 'Existing output filter retained' );
remove_all_filters( 'pat/shortcode/author-box' );
$GLOBALS['wp_query']->is_tax = true;
$GLOBALS['wp_query']->queried_object = get_term( $id, 'pat-author' );
$GLOBALS['wp_query']->queried_object_id = $id;
check( str_contains( do_shortcode( '[pat-author-box]' ), 'Alice' ), 'Current taxonomy archive author' );
$GLOBALS['wp_query']->is_tax = false;
$admin = new DDW_PAT_Admin();
$safe = $admin->sanitize_settings( array( 'post_types' => array( 'post', 'page', 'bad' ), 'link' => 'bad' ) );
check( array( 'post', 'page' ) === $safe['post_types'] && 'archive' === $safe['link'], 'Settings allowlist' );
$_POST = array( 'pat_website' => 'https://changed.test' );
$admin->save_fields( $id );
check( 'https://example.test/alice' === get_term_meta( $id, 'pat_website', true ), 'Profile save requires nonce' );
$_POST['pat_profile_nonce'] = wp_create_nonce( 'pat_save_profile' );
$_POST['pat_website'] = 'javascript:alert(1)';
$admin->save_fields( $id );
check( '' === get_term_meta( $id, 'pat_website', true ), 'Profile rejects active URL protocol' );
check( ! empty( $GLOBALS['deckerweb_library_runtime_v1'] ), 'Embedded library elected' );
check( class_exists( '\Deckerweb\GitHubReleaseUpdater\V2\Updater' ), 'Shared updater initialized' );
wp_set_current_user( 0 );
check( ! ( new DDW_Post_Author_Taxonomy() )->can_edit_meta(), 'Anonymous REST metadata write denied' );
echo "Completed $checks checks\n";
