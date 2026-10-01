<?php
/**
 * Plugin Name: Post Author Taxonomy
 * Plugin URI: https://github.com/deckerweb/post-author-taxonomy
 * Description: Authors as a public taxonomy: multiple authors, local photos, profiles and flexible shortcodes for your builder or theme.
 * Version: 1.3.0
 * Author: David Decker – DECKERWEB
 * Author URI: https://deckerweb.de/
 * License: GPL-2.0-or-later
 * Text Domain: post-author-taxonomy
 * Domain Path: /languages
 * Requires at least: 6.7
 * Requires PHP: 8.0
 * Update URI: https://github.com/deckerweb/post-author-taxonomy
 * GitHub Plugin URI: https://github.com/deckerweb/post-author-taxonomy
 * GitHub Branch: master
 * Copyright: 2017–2026 David Decker – DECKERWEB
 */
defined( 'ABSPATH' ) || exit;

define( 'PAT_PLUGIN_FILE', __FILE__ );
define( 'PAT_PLUGIN_DIR', __DIR__ . '/' );
require_once __DIR__ . '/includes/class-post-author-taxonomy.php';
require_once __DIR__ . '/includes/class-pat-changelog.php';
require_once __DIR__ . '/includes/class-pat-admin.php';
require_once __DIR__ . '/includes/class-pat-github-updates.php';
new DDW_PAT_Admin();
( new \Deckerweb\PostAuthorTaxonomy\GitHubUpdates() )->register();
require_once __DIR__ . '/includes/deckerweb-plugin-library/bootstrap.php';
deckerweb_library_register( __FILE__, array(), __DIR__ . '/includes/deckerweb-plugin-library' );
register_activation_hook( __FILE__, function() {
	$core = new DDW_Post_Author_Taxonomy();
	$core->load_translations();
	$core->register_taxonomy();
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, function() {
	unregister_taxonomy( DDW_Post_Author_Taxonomy::TAXONOMY );
	flush_rewrite_rules();
} );
