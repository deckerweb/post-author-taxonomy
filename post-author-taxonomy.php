<?php
/**
 * Plugin Name: Post Author Taxonomy
 * Plugin URI: https://github.com/deckerweb/post-author-taxonomy
 * Description: Authors as a public taxonomy: multiple authors, local photos, profiles and flexible shortcodes for your builder or theme.
 * Version: 1.4.1
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Author: David Decker – DECKERWEB
 * Author URI: https://github.com/deckerweb
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: post-author-taxonomy
 * Domain Path: /languages/
 * Update URI: https://github.com/deckerweb/post-author-taxonomy
 * GitHub Plugin URI: https://github.com/deckerweb/post-author-taxonomy
 *
 * Copyright © 2017–2026 David Decker – DECKERWEB.
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @package PostAuthorTaxonomy
 */
defined( 'ABSPATH' ) || exit;

define( 'PAT_PLUGIN_FILE', __FILE__ );
define( 'PAT_PLUGIN_DIR', __DIR__ . '/' );
require_once __DIR__ . '/includes/class-post-author-taxonomy.php';
require_once __DIR__ . '/includes/class-pat-changelog.php';
require_once __DIR__ . '/includes/class-pat-admin.php';
require_once __DIR__ . '/includes/class-pat-github-updates.php';
require_once __DIR__ . '/includes/class-pat-lifecycle.php';
require_once __DIR__ . '/includes/class-pat-attribution.php';
if ( is_admin() ) {
	require_once __DIR__ . '/includes/class-pat-tools.php';
	( new DDW_PAT_Tools() )->register();
}
( new DDW_PAT_Attribution() )->register();
new DDW_PAT_Admin();
( new \Deckerweb\PostAuthorTaxonomy\GitHubUpdates() )->register();
require_once __DIR__ . '/includes/deckerweb-plugin-library/bootstrap.php';
deckerweb_library_register_v2( __FILE__, array(), __DIR__ . '/includes/deckerweb-plugin-library' );
DDW_PAT_Lifecycle::register();
add_action( 'after_setup_theme', array( 'DDW_PAT_Lifecycle', 'load_builder' ) );
register_activation_hook( __FILE__, array( 'DDW_PAT_Lifecycle', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'DDW_PAT_Lifecycle', 'deactivate' ) );
