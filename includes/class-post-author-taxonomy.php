<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'DDW_Post_Author_Taxonomy' ) ) {
/** Small, standalone taxonomy and rendering core, also used by the snippet export. */
class DDW_Post_Author_Taxonomy {
	public const VERSION = '1.3.0';
	public const TAXONOMY = 'pat-author';
	public const DOMAIN = 'post-author-taxonomy';
	public function __construct() {
		add_action( 'init', array( $this, 'load_translations' ), 20 );
		add_action( 'init', array( $this, 'register_taxonomy' ), 100 );
		add_action( 'init', array( $this, 'register_meta' ), 101 );
		add_shortcode( 'pat-authors', array( $this, 'shortcode_authors_list' ) );
		add_shortcode( 'pat-author-box', array( $this, 'shortcode_author_box' ) );
		add_shortcode( 'pat-author-boxes', array( $this, 'shortcode_author_boxes' ) );
	}
	public function load_translations() {
		$locale = apply_filters( 'plugin_locale', determine_locale(), self::DOMAIN );
		load_textdomain( self::DOMAIN, trailingslashit( WP_LANG_DIR ) . self::DOMAIN . '/' . self::DOMAIN . '-' . $locale . '.mo' );
		if ( defined( 'PAT_PLUGIN_FILE' ) ) {
			load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( PAT_PLUGIN_FILE ) ) . '/languages' );
		}
	}
	public function register_taxonomy() {
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
			'search_items'               => __( 'Search Authors', 'post-author-taxonomy' ),
			'not_found'                  => __( 'Not Found', 'post-author-taxonomy' ),
			'no_terms'                   => __( 'No Authors', 'post-author-taxonomy' ),
			'items_list'                 => __( 'Authors list', 'post-author-taxonomy' ),
			'items_list_navigation'      => __( 'Authors list navigation', 'post-author-taxonomy' ),
		);
	
		// Archive URLs follow the site language, independent of an administrator's language.
		$site_locale = get_option( 'WPLANG' ) ?: 'en_US';
		$switched = switch_to_locale( $site_locale );
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
	
		if ( $switched ) { restore_previous_locale(); }
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
			'description'       => __( 'A simple post authors taxonomy for the regular Posts post type.', 'post-author-taxonomy' ),
		);
	

		$post_types = get_option( 'pat_settings', array() )['post_types'] ?? array( 'post' );
		$post_types = apply_filters( 'pat/taxonomy/post-types', $post_types );
		register_taxonomy( self::TAXONOMY, $post_types, apply_filters( 'pat/taxonomy/params', $args ) );
	}
	public function register_meta() {
		register_term_meta( self::TAXONOMY, 'pat_photo_id', array(
			'type' => 'integer', 'single' => true, 'show_in_rest' => true,
			'sanitize_callback' => 'absint', 'auth_callback' => array( $this, 'can_edit_meta' ),
		) );
		register_term_meta( self::TAXONOMY, 'pat_website', array(
			'type' => 'string', 'single' => true, 'show_in_rest' => true,
			'sanitize_callback' => array( $this, 'sanitize_website' ), 'auth_callback' => array( $this, 'can_edit_meta' ),
		) );
	}
	public function can_edit_meta() {
		$tax = get_taxonomy( self::TAXONOMY );
		return $tax && current_user_can( $tax->cap->edit_terms );
	}
	public function sanitize_website( $url ) {
		return esc_url_raw( is_scalar( $url ) ? (string) $url : '', array( 'http', 'https' ) );
	}
	/** Permit semantic wrappers only; malformed tags fall back to the default. */
	private function tag( $value, $fallback, $heading = false, $container = false ) {
		$allowed = $heading ? array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ) : array( 'div', 'span', 'p', 'section', 'article', 'aside' );
		if ( $container ) { $allowed = array( 'div', 'section', 'article', 'aside' ); }
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}
	private function classes( $value ) {
		$classes = preg_split( '/\s+/', trim( (string) $value ) );
		return implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );
	}
	/** Existing labels may contain limited inline formatting, but no active content. */
	private function label( $value ) {
		return wp_kses( (string) $value, array( 'span' => array( 'class' => true ), 'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array(), 'br' => array() ) );
	}
	private function attributes( $atts, $defaults, $shortcode ) {
		$atts = shortcode_atts( $defaults, $atts, $shortcode );
		foreach ( $atts as $key => $value ) {
			$atts[ $key ] = is_scalar( $value ) ? (string) $value : (string) ( $defaults[ $key ] ?? '' );
		}
		return $atts;
	}
	private function link_mode( $value ) {
		return in_array( $value, array( 'archive', 'website', 'none' ), true ) ? $value : 'archive';
	}
	private function term_url( $term, $mode ) {
		if ( 'none' === $mode ) { return ''; }
		$url = 'website' === $mode ? get_term_meta( $term->term_id, 'pat_website', true ) : get_term_link( $term );
		return is_wp_error( $url ) ? '' : esc_url( $url );
	}
	/** Resolve one selector only, with explicit precedence; never guess after an invalid ID. */
	private function resolve_author( $atts ) {
		if ( '' !== $atts['id'] ) {
			$term = ctype_digit( $atts['id'] ) && (int) $atts['id'] > 0 ? get_term( (int) $atts['id'], self::TAXONOMY ) : false;
		} elseif ( '' !== $atts['slug'] ) {
			$term = get_term_by( 'slug', $atts['slug'], self::TAXONOMY );
		} elseif ( '' !== $atts['name'] ) {
			$term = get_term_by( 'name', $atts['name'], self::TAXONOMY );
		} elseif ( is_tax( self::TAXONOMY ) ) {
			$term = get_queried_object();
		} else { return null; }
		return $term instanceof WP_Term && self::TAXONOMY === $term->taxonomy ? $term : null;
	}
	public function shortcode_authors_list( $atts ) {
		$defaults = apply_filters( 'pat/shortcode/authors-list-defaults', array(
			'before' => __( 'Authors:', 'post-author-taxonomy' ), 'after' => '', 'sep' => ', ',
			'class' => '', 'wrapper' => 'span', 'link' => 'archive', 'post_id' => '',
		) );
		$atts = $this->attributes( $atts, $defaults, 'pat-authors' );
		$post_id = '' === $atts['post_id'] ? get_the_ID() : absint( $atts['post_id'] );
		$terms = $post_id ? get_the_terms( $post_id, self::TAXONOMY ) : false;
		if ( ! $terms || is_wp_error( $terms ) ) { return ''; }
		$items = array();
		foreach ( $terms as $term ) {
			$url = $this->term_url( $term, $this->link_mode( $atts['link'] ) );
			$name = esc_html( $term->name );
			$rel = 'archive' === $this->link_mode( $atts['link'] ) ? ' rel="tag"' : '';
			$items[] = $url ? '<a href="' . $url . '"' . $rel . '>' . $name . '</a>' : $name;
		}
		if ( 'archive' === $this->link_mode( $atts['link'] ) ) { $items = apply_filters( 'term_links-' . self::TAXONOMY, $items ); }
		$before = '' !== $atts['before'] ? $this->label( $atts['before'] ) . ' ' : '';
		$after = '' !== $atts['after'] ? ' ' . $this->label( $atts['after'] ) : '';
		$tag = $this->tag( $atts['wrapper'], 'span' );
		$output = '<' . $tag . ' class="' . esc_attr( trim( 'pat-authors ' . $this->classes( $atts['class'] ) ) ) . '">' . $before . implode( $this->label( $atts['sep'] ), $items ) . $after . '</' . $tag . '>';
		return apply_filters( 'pat/shortcode/authors-list', $output, $atts );
	}
	private function box_defaults() {
		return apply_filters( 'pat/shortcode/author-box-defaults', array(
			'title' => 'yes', 'headline' => '', 'title_tag' => 'h4', 'id' => '', 'slug' => '', 'name' => '',
			'content_tag' => 'p', 'class' => '', 'wrapper' => 'div', 'photo' => 'yes', 'website' => 'yes', 'link' => 'none',
		) );
	}
	private function render_author_box( $term, $atts ) {
		$tag = $this->tag( $atts['wrapper'], 'div', false, true );
		$title_tag = $this->tag( $atts['title_tag'], 'h4', true );
		$content_tag = $this->tag( $atts['content_tag'], 'p' );
		$title = '' !== $atts['headline'] ? $atts['headline'] : ( 'yes' === $atts['title'] ? $term->name : '' );
		$html = '';
		if ( 'yes' === $atts['photo'] ) {
			$photo = absint( get_term_meta( $term->term_id, 'pat_photo_id', true ) );
			if ( $photo && wp_attachment_is_image( $photo ) ) {
				$html .= wp_get_attachment_image( $photo, 'thumbnail', false, array( 'class' => 'pat-author-box__photo', 'alt' => $term->name, 'loading' => 'lazy' ) );
			}
		}
		if ( '' !== $title ) {
			$text = esc_html( $title );
			$url = $this->term_url( $term, $this->link_mode( $atts['link'] ) );
			$html .= '<' . $title_tag . ' class="pat-author-box__title">' . ( $url ? '<a href="' . $url . '">' . $text . '</a>' : $text ) . '</' . $title_tag . '>';
		}
		$description = wp_strip_all_tags( term_description( $term->term_id, self::TAXONOMY ) );
		if ( '' !== trim( $description ) ) {
			$html .= '<' . $content_tag . ' class="pat-author-box__content">' . esc_html( $description ) . '</' . $content_tag . '>';
		}
		$website = $this->term_url( $term, 'website' );
		if ( 'yes' === $atts['website'] && $website ) {
			/* translators: %s: author name. */
			$html .= '<a class="pat-author-box__website" href="' . $website . '">' . esc_html( sprintf( __( 'Website of %s', 'post-author-taxonomy' ), $term->name ) ) . '</a>';
		}
		if ( '' === $html ) { return ''; }
		$output = '<' . $tag . ' class="' . esc_attr( trim( 'pat-author-box ' . $this->classes( $atts['class'] ) ) ) . '">' . $html . '</' . $tag . '>';
		return apply_filters( 'pat/shortcode/author-box', $output, $atts );
	}
	public function shortcode_author_box( $atts ) {
		$atts = $this->attributes( $atts, $this->box_defaults(), 'pat-author-box' );
		$term = $this->resolve_author( $atts );
		return $term ? $this->render_author_box( $term, $atts ) : '';
	}
	public function shortcode_author_boxes( $atts ) {
		$defaults = $this->box_defaults();
		unset( $defaults['id'], $defaults['slug'], $defaults['name'] );
		$defaults['post_id'] = '';
		$atts = $this->attributes( $atts, apply_filters( 'pat/shortcode/author-boxes-defaults', $defaults ), 'pat-author-boxes' );
		$post_id = '' === $atts['post_id'] ? get_the_ID() : absint( $atts['post_id'] );
		$terms = $post_id ? get_the_terms( $post_id, self::TAXONOMY ) : false;
		if ( ! $terms || is_wp_error( $terms ) ) { return ''; }
		$output = '';
		foreach ( $terms as $term ) {
			$box_atts = $atts;
			$box_atts['id'] = (string) $term->term_id;
			$output .= $this->render_author_box( $term, $box_atts );
		}
		return apply_filters( 'pat/shortcode/author-boxes', $output, $atts );
	}
}
new DDW_Post_Author_Taxonomy();
}
