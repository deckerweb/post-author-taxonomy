<?php # -*- coding: utf-8 -*-
/*
Plugin Name:       Post Author Taxonomy
Plugin URI:        https://github.com/deckerweb/post-author-taxonomy
Description:       Registers a simple post authors taxonomy for the regular Posts post type. The taxonomy is not hierarchical, so it's similar to tags but for authors instead. Shortcodes for a list of authors and an Author Box are included. For developers there a lot of filters to extend it for more post types, plus optionally customize stuff.
Project:           Code Snippet: DDW Post Author Taxonomy
Version:           1.2.0
Author:            David Decker - DECKERWEB
Author URI:        https://deckerweb.de/
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       post-author-taxonomy
Domain Path:       /languages/
Requires WP:       6.7
Requires PHP:      7.4
GitHub Plugin URI: https://github.com/deckerweb/post-author-taxonomy
GitHub Branch:     master
Copyright:         © 2017-2025, David Decker - DECKERWEB
*/

/**
 * Exit if called directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit( 'Sorry, you are not allowed to access this file directly.' );


if ( ! class_exists( 'DDW_Post_Author_Taxonomy' ) ) :

class DDW_Post_Author_Taxonomy {

	/** Class constants & variables */
	private const VERSION = '1.2.0';
	private const DEFAULT_MENU_POSITION	= 999;  // default: 999

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action(    'init',           array( $this, 'register_taxonomy' ), 100 );
		add_shortcode( 'pat-authors',    array( $this, 'shortcode_authors_list' ) );
		add_shortcode( 'pat-author-box', array( $this, 'shortcode_author_box' ) );
		//add_filter(    'debug_information', array( $this, 'site_health_debug_info' ), 9 );
	}
	
	/**
	 * Register custom taxomoy: Post Authors
	 *
	 * @since  1.0.0 (2017.12.15)
	 * @since  1.1.0 Added more filters.
	 *
	 * @return object Taxonomy declaration
	 */
	public function register_taxonomy() {
	
		/** Load our plugin translation, as there are no language packs from wordpress.org */
		load_plugin_textdomain( 'post-author-taxonomy', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
		/** Define tax labels/ wording */
		$labels = array(
			'name'                       => _x( 'Authors', 'Taxonomy General Name', 'post-author-taxonomy' ),
			'singular_name'              => _x( 'Author', 'Taxonomy Singular Name', 'post-author-taxonomy' ),
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
			'slug'         => sanitize_key(
				_x(
					'post-author',
					'Translators: slug part for Post Author Taxonomy in the URLs',
					'post-author-taxonomy'
				)
			),
			'with_front'   => TRUE,
			'hierarchical' => FALSE,
		);
	
		/** Declare tax params */
		$args = array(
			'labels'            => $labels,
			'hierarchical'      => FALSE,
			'public'            => TRUE,
			'show_ui'           => TRUE,
			'show_admin_column' => TRUE,
			'show_in_nav_menus' => TRUE,
			'show_tagcloud'     => TRUE,
			'show_in_rest'      => TRUE,
			'rewrite'           => $rewrite,
			'meta_box_cb'       => 'post_categories_meta_box',	// only for Classic, not for Gutenberg Editor
			'description'       => __( 'A simple post authors taxonomy for the regular Posts post type.', 'post-author-taxonomy' ),
		);
	
		/** Finally, register the taxonomy */
		register_taxonomy(
			'pat-author',
			array( 'post' ),
			apply_filters(
				'pat/taxonomy/params',
				$args
			)
		);		
	}
	
	/**
	 * Output linked authors list.
	 *
	 * @since  1.1.0
	 *
	 * @param  array|string $atts Shortcode attributes. Empty string if no attributes.
	 * @return string List of authors.
	 */
	public function shortcode_authors_list( $atts ) {
	
		/** Bail early, if post type has no support for this taxonomy */
		if ( ! is_object_in_taxonomy( get_post_type(), 'pat-author' ) ) {
			return '';
		}
	
		/** Setup the Shortcode defaults */
		$defaults = apply_filters(
			'pat/shortcode/authors-list-defaults',
			array(
				'before'  => __( 'Authors:', 'post-author-taxonomy' ),
				'after'   => '',
				'sep'     => ', ',
				'class'   => '',
				'wrapper' => 'span',
			)
		);
	
		/** Setup the Shortcode attributes */
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'pat-authors'
		);
	
		$label_before = ! empty( $atts[ 'before' ] ) ? $atts[ 'before' ] . ' ' : '';
		
		/** Get the terms list (authors) with use of our arguments */
		$authors = get_the_term_list(
			get_the_ID(),
			'pat-author',
			$label_before,
			trim( $atts[ 'sep' ] ) . ' ',
			$atts[ 'after' ]
		);
	
		/** Bail if error with terms list */
		if ( is_wp_error( $authors ) ) {
				return '';
		}
	
		/** Bail if no authors (empty) */
		if ( empty( $authors ) ) {
				return '';
		}
	
		/** Prepare output */
		$output = sprintf(
			'<%1$s class="pat-authors%2$s">%3$s%4$s</%1$s>',
			sanitize_key( $atts[ 'wrapper' ] ),
			! empty( $atts[ 'class' ] ) ? ' ' . sanitize_html_class( $atts[ 'class' ] ) : '',
			$authors,
			! empty( $atts[ 'after' ] ) ? ' ' . $atts[ 'after' ] : ''
		);
	
		/** Return output, filterable */
		return apply_filters(
			'pat/shortcode/authors-list',
			$output,
			$atts
		);
	}
	
	/**
	 * Shortcode for an "Author Box"
	 *
	 * @since 1.2.0
	 *
	 * @param  array|string $atts Shortcode attributes. Empty string if no attributes.
	 * @return string List of authors.
	 */
	public function shortcode_author_box( $atts ) {
		
		/** Bail early, if post type has no support for this taxonomy */
		if ( ! is_object_in_taxonomy( get_post_type(), 'pat-author' ) ) {
			return '';
		}
		
		/** Setup the Shortcode defaults */
		$defaults = apply_filters(
			'pat/shortcode/author-box-defaults',
			array(
				'title'       => 'yes',
				'headline'    => '',
				'title_tag'   => 'h4',
				'id'          => '',
				'slug'        => '',
				'name'        => '',
				'content_tag' => 'p',
				'class'       => '',
				'wrapper'     => 'div',
			)
		);
		
		/** Setup the Shortcode attributes */
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'pat-author-box'
		);
		
		/** Default: get term ID from Shortcode */
		$term_id = $atts[ 'id' ];
		
		/** Alternative I: get Term ID by given slug */
		$term_by_slug = get_term_by( 'slug', sanitize_key( $atts[ 'slug' ] ), 'pat-author' );
		$term_id      = empty( $term_id ) ? $term_by_slug->term_id : $term_id;
		
		/** Alternative II: get Term ID by given name */
		$term_by_name = get_term_by( 'name', esc_html( $atts[ 'name' ] ), 'pat-author' );
		$term_id      = empty( $term_id ) ? $term_by_name->term_id : $term_id;
		
		$term_description = wp_strip_all_tags( term_description( absint( $term_id ), 'pat-author' ) );
		$term_description = sprintf(
			'<%1$s class="pat-author-box__content" itemprop="description">%2$s</%1$s>',
			sanitize_key( $atts[ 'content_tag' ] ),
			$term_description
		);
		
		/** Get author name by ID */
		$author_by_id = get_term_by( 'id', absint( $term_id ), 'pat-author' );
		
		/** Prepre Headline & Author Name */
		$headline    = ( ! empty( $atts[ 'headline' ] ) ) ? esc_html( $atts[ 'headline' ] ) : '';
		$author_name = ( 'yes' === sanitize_key( $atts[ 'title' ] ) ) ? $author_by_id->name : '';	
		
		/** Title logic: use headline, if empty use author name, if also empty, display nothing */
		$title = ! empty( $headline ) ? $headline : $author_name;
		
		$title_display = sprintf(
			'<%1$s class="pat-author-box__title">%2$s</%1$s>',
			sanitize_key( $atts[ 'title_tag' ] ),
			$title
		);
		
		$output = sprintf(
			'<%1$s class="pat-author-box%2$s">%3$s%4$s</%1$s>',
			sanitize_key( $atts[ 'wrapper' ] ),
			! empty( $atts[ 'class' ] ) ? ' ' . sanitize_html_class( $atts[ 'class' ] ) : '',
			! empty( $title ) ? $title_display : '',	// only empty if no default, and no headline set
			$term_description
		);
		
		/** Return output, filterable */
		return apply_filters(
			'pat/shortcode/author-box',
			$output,
			$atts
		);
	}
	
}  // end of class

/** Start instance of Class */
new DDW_Post_Author_Taxonomy();
	
endif;


if ( ! function_exists( 'ddw_pat_plugin_action_link' ) ) :
	
add_action( 'admin_init', 'ddw_pat_plugin_action_link', 100 );
/**
 * Finally setup the plugin for the main tasks.
 *
 * @since 1.1.0
 */
function ddw_pat_plugin_action_link() {

	/** Add links to Settings and Menu pages to Plugins page */
	if ( ( is_admin() || is_network_admin() ) ) {

		add_filter(
			'plugin_action_links_' . plugin_basename( __FILE__ ),
			'ddw_pat_custom_taxonomy_links'
		);

		add_filter(
			'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ),
			'ddw_pat_custom_taxonomy_links'
		);

	}  // end if

}  // end function

endif;


if ( ! function_exists( 'ddw_pat_custom_taxonomy_links' ) ) :

/**
 * Add the taxonomy link to Plugins page.
 *
 * @since  1.1.0
 *
 * @param  array $pat_links (Default) Array of plugin action links.
 * @return strings $pat_links Settings & Menu Admin links.
 */
function ddw_pat_custom_taxonomy_links( $pat_links ) {

	/** Taxonomy Page link */
	$pat_tax_link = sprintf(
		'<a class="dashicons-before dashicons-admin-users" href="%s" title="%s">%s</a>',
		esc_url( admin_url( 'edit-tags.php?taxonomy=pat-author' ) ),
		/* translators: Title attribute for Post Author Taxonomy tax link */
		esc_html__( 'Post Authors', 'post-author-taxonomy' ),
		esc_attr_x( 'Post Authors', 'For Post Authors Taxonomy Plugin', 'post-author-taxonomy' )
	);

	/** Set the order of the links */
	if ( ! empty( $pat_tax_link ) ) {
		array_unshift( $pat_links, $pat_tax_link );
	}

	/** Display plugin settings links */
	return apply_filters(
		'pat/plugins-page/tax-link',
		$pat_links
	);

}  // end function

endif;


if ( ! function_exists( 'ddw_pat_pluginrow_meta' ) ) :
	
add_filter( 'plugin_row_meta', 'ddw_pat_pluginrow_meta', 10, 2 );
/**
 * Add plugin related links to plugin page.
 *
 * @param array  $ddwp_meta (Default) Array of plugin meta links.
 * @param string $ddwp_file File location of plugin.
 * @return array $ddwp_meta (Modified) Array of plugin links/ meta.
 */
function ddw_pat_pluginrow_meta( $ddwp_meta, $ddwp_file ) {
 
	if ( ! current_user_can( 'install_plugins' ) ) return $ddwp_meta;
	
	/** Get current user */
	$user = wp_get_current_user();
	
	/** Build Newsletter URL */
	$url_nl = sprintf(
		'https://deckerweb.us2.list-manage.com/subscribe?u=e09bef034abf80704e5ff9809&amp;id=380976af88&amp;MERGE0=%1$s&amp;MERGE1=%2$s',
		esc_attr( $user->user_email ),
		esc_attr( $user->user_firstname )
	);
	
	/** List additional links only for this plugin */
	if ( $ddwp_file === trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . basename( __FILE__ ) ) {
		$ddwp_meta[] = sprintf(
			'<a class="button button-inline" href="https://ko-fi.com/deckerweb" target="_blank" rel="nofollow noopener noreferrer" title="%1$s">❤ <b>%1$s</b></a>',
			esc_html_x( 'Donate', 'Plugins page listing', 'post-author-taxonomy' )
		);
		
		$ddwp_meta[] = sprintf(
			'<a class="button-primary" href="%1$s" target="_blank" rel="nofollow noopener noreferrer" title="%2$s">⚡ <b>%2$s</b></a>',
			$url_nl,
			esc_html_x( 'Join our Newsletter', 'Plugins page listing', 'post-author-taxonomy' )
		);
	}  // end if
	
	return apply_filters( 'pat/plugins-page/meta-links', $ddwp_meta );

}  // end function

endif;