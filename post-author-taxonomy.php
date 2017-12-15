<?php # -*- coding: utf-8 -*-
/**
 * Main plugin file.
 * @package           Post Author Taxonomy
 * @author            David Decker
 * @copyright         Copyright (c) 2017, David Decker - DECKERWEB
 * @license           GPL-2.0+
 * @link              https://deckerweb.de/twitter
 *
 * @wordpress-plugin
 * Plugin Name:       Post Author Taxonomy
 * Plugin URI:        https://github.com/deckerweb/post-author-taxonomy
 * Description:       Registers a simple post authors taxonomy for the regular Posts post type. The taxonomy is not hierarchical, so it's similar to taks but for authors instead.
 * Version:           2017.12.15
 * Author:            David Decker - DECKERWEB
 * Author URI:        https://deckerweb.de/
 * License:           GPL-2.0+
 * License URI:       http://www.opensource.org/licenses/gpl-license.php
 * Text Domain:       post-author-taxonomy
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/deckerweb/post-author-taxonomy
 * GitHub Branch:     master
 *
 * Copyright (c) 2017 David Decker - DECKERWEB
 */

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'init', 'ddw_pat_load_translations', 1 );
/**
 * Load the text domain for translation of the plugin.
 *
 * @since 2017.12.15
 *
 * @uses  load_textdomain()	       To load translations first from WP_LANG_DIR
 *                                 sub folder.
 * @uses  load_plugin_textdomain() To additionally load default translations
 *                                 from plugin folder (default).
 */
function ddw_pat_load_translations() {

	/** Set unique textdomain string */
	$pat_textdomain = 'post-author-taxonomy';

	/** The 'plugin_locale' filter is also used by default in load_plugin_textdomain() */
	$locale = apply_filters( 'plugin_locale', get_locale(), $pat_textdomain );

	/** Set filter for WordPress languages directory */
	$siu_wp_lang_dir = apply_filters(
		'pat_filter_wp_lang_dir',
		trailingslashit( WP_LANG_DIR ) . 'plugins/' . $pat_textdomain . '-' . $locale . '.mo'
	);

	/** Translations: First, look in WordPress' "languages" folder = custom & update-secure! */
	load_textdomain(
		$pat_textdomain,
		$pat_wp_lang_dir
	);

	/** Translations: Secondly, look in plugin's "languages" folder = default */
	load_plugin_textdomain(
		$pat_textdomain,
		FALSE,
		trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'languages'
	);

}  // end function


add_action( 'init', 'ddw_pat_register_author_tax', 1 );
/**
 * Register custom taxomoy: Post Authors
 *
 * @since  2017.12.15
 *
 * @return object Taxonomy declaration
 */
function ddw_pat_register_author_tax() {

	/** Define tax labels/ wording */
	$labels = array(
		'name'                       => _x( 'Authors', 'Taxonomy General Name', 'post-author-taxonomy' ),
		'singular_name'              => _x( 'Author', 'Taxonomy Singular Name', 'post-author-taxonomy' ),
		'menu_name'                  => __( 'Authors', 'post-author-taxonomy' ),
		'all_items'                  => __( 'All Authors', 'post-author-taxonomy' ),
		'parent_item'                => __( 'Parent Author', 'post-author-taxonomy' ),
		'parent_item_colon'          => __( 'Parent Author:', 'post-author-taxonomy' ),
		'new_item_name'              => __( 'New Author Name', 'post-author-taxonomy' ),
		'add_new_item'               => __( 'Add New Author', 'post-author-taxonomy' ),
		'edit_item'                  => __( 'Edit Author', 'post-author-taxonomy' ),
		'update_item'                => __( 'Update Author', 'post-author-taxonomy' ),
		'view_item'                  => __( 'View Author', 'post-author-taxonomy' ),
		'separate_items_with_commas' => __( 'Separate Authors with commas', 'post-author-taxonomy' ),
		'add_or_remove_items'        => __( 'Add or remove Authors', 'post-author-taxonomy' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'post-author-taxonomy' ),
		'popular_items'              => __( 'Popular Authors', 'post-author-taxonomy' ),
		'search_items'               => __( 'Search Auhtors', 'post-author-taxonomy' ),
		'not_found'                  => __( 'Not Found', 'post-author-taxonomy' ),
		'no_terms'                   => __( 'No Authors', 'post-author-taxonomy' ),
		'items_list'                 => __( 'Authors list', 'post-author-taxonomy' ),
		'items_list_navigation'      => __( 'Authors list navigation', 'post-author-taxonomy' ),
	);

    /** Declare rewrite rules */
	$rewrite = array(
		/* translators: slug part for Post Author Taxonomy in the URLs */
		'slug'                       => _x( 'post-author', 'Translators: slug part for Post Author Taxonomy in the URLs', 'post-author-taxonomy' ),
		'with_front'                 => true,
		'hierarchical'               => false,
	);

    /** Declare tax params */
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => $rewrite,
	);

	/** Finally, register the taxonomy */
	register_taxonomy(
		'pat-author',
		array( 'post' ),
		apply_filters( 'pat-author-tax-params', $args )
	);

}  // end function
