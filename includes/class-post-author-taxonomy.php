<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'DDW_Post_Author_Taxonomy' ) ) {
/** Small, standalone taxonomy and rendering core, without frontend assets. */
class DDW_Post_Author_Taxonomy {
	public const VERSION = '1.4.0';
	public const TAXONOMY = 'pat-author';
	public const DOMAIN = 'post-author-taxonomy';
	/**
	 * Register taxonomy, translation and shortcode hooks unless only site registration is requested.
	 *
	 * @param bool $register_hooks Whether to attach request hooks; false is used during lifecycle actions.
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) { return; }
		add_action( 'init', array( $this, 'load_translations' ), 20 );
		add_action( 'init', array( $this, 'register_taxonomy' ), 100 );
		add_action( 'init', array( $this, 'register_meta' ), 101 );
		add_shortcode( 'pat-authors', array( $this, 'shortcode_authors_list' ) );
		add_shortcode( 'pat-author-box', array( $this, 'shortcode_author_box' ) );
		add_shortcode( 'pat-author-boxes', array( $this, 'shortcode_author_boxes' ) );
		add_filter( 'rest_request_before_callbacks', array( $this, 'validate_rest_profile' ), 10, 3 );
	}
	/**
	 * Load site and bundled language resources in the existing plugin textdomain.
	 *
	 * @return void
	 */
	public function load_translations() {
		$locale = apply_filters( 'plugin_locale', determine_locale(), self::DOMAIN );
		load_textdomain( self::DOMAIN, trailingslashit( WP_LANG_DIR ) . self::DOMAIN . '/' . self::DOMAIN . '-' . $locale . '.mo' );
		if ( defined( 'PAT_PLUGIN_FILE' ) ) {
			load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( PAT_PLUGIN_FILE ) ) . '/languages' );
		}
	}
	/**
	 * Register public author terms for configured post types, preserving site-language archive URLs.
	 *
	 * @return void
	 */
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
			'description'       => __( 'Authors for your selected public post types.', 'post-author-taxonomy' ),
		);
	

		$settings = get_option( 'pat_settings', array() );
		$post_types = is_array( $settings ) && is_array( $settings['post_types'] ?? null ) ? $settings['post_types'] : array( 'post' );
		/**
		 * Filter the post types to which public author terms are attached.
		 *
		 * @since 1.3.0
		 * @param array $post_types Configured post types; return the modified post-type array.
		 */
		$post_types = apply_filters( 'pat/taxonomy/post-types', $post_types );
		/**
		 * Filter author-taxonomy registration arguments.
		 *
		 * @since 1.0.0
		 * @param array $args Taxonomy registration values; return the modified arguments.
		 */
		register_taxonomy( self::TAXONOMY, $post_types, apply_filters( 'pat/taxonomy/params', $args ) );
	}
	/**
	 * Register local photo and HTTP/HTTPS website metadata with REST authorization.
	 *
	 * @return void
	 */
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
	/**
	 * Authorize metadata edits through taxonomy and object-level term capabilities.
	 *
	 * @param bool $allowed Previous authorization result from WordPress.
	 * @param string $meta_key Metadata key being authorized.
	 * @param int $object_id Term ID, or zero when no object is supplied.
	 * @return bool Whether the current user may edit the requested term metadata.
	 */
	public function can_edit_meta( $allowed = false, $meta_key = '', $object_id = 0 ) {
		$tax = get_taxonomy( self::TAXONOMY );
		return $tax && current_user_can( $tax->cap->edit_terms ) && ( ! $object_id || current_user_can( 'edit_term', (int) $object_id ) );
	}
	/**
	 * Apply admin-form media constraints before a native author REST callback can mutate terms.
	 * Native permission callbacks remain authoritative; unauthorized requests pass through to them.
	 *
	 * @param mixed $response Existing REST pre-callback response or error.
	 * @param array $handler Matched REST route handler.
	 * @param WP_REST_Request $request Validated REST request.
	 * @return mixed Existing response, or a localized media validation error before any write.
	 */
	public function validate_rest_profile( $response, $handler, $request ) {
		$tax = get_taxonomy( self::TAXONOMY );
		if ( null !== $response || ! $tax || ! in_array( $request->get_method(), array( 'POST', 'PUT', 'PATCH' ), true ) ) { return $response; }
		$base = $tax->rest_base ?: self::TAXONOMY;
		$namespace = $tax->rest_namespace ?: 'wp/v2';
		if ( ! preg_match( '~^/' . preg_quote( $namespace . '/' . $base, '~' ) . '(?:/\d+)?/?$~D', $request->get_route() ) || ! is_array( $handler['callback'] ?? null ) || ! ( $handler['callback'][0] instanceof WP_REST_Terms_Controller ) ) { return $response; }
		$id = (int) $request->get_param( 'id' );
		if ( ! current_user_can( $tax->cap->edit_terms ) || ( $id && ! current_user_can( 'edit_term', $id ) ) ) { return $response; }
		$meta = $request->get_param( 'meta' );
		if ( is_array( $meta ) && isset( $meta['pat_photo_id'] ) ) {
			$photo_id = absint( $meta['pat_photo_id'] );
			if ( $photo_id && ( ! wp_attachment_is_image( $photo_id ) || ! current_user_can( 'edit_post', $photo_id ) ) ) {
				return new WP_Error( 'pat_invalid_photo', __( 'Choose an image you can edit from the media library.', 'post-author-taxonomy' ), array( 'status' => 403 ) );
			}
		}
		return $response;
	}

	/**
	 * Limit author website values to safe HTTP/HTTPS URLs.
	 *
	 * @param mixed $url Submitted website value.
	 * @return string Sanitized website URL or an empty string.
	 */
	public function sanitize_website( $url ) {
		return esc_url_raw( is_scalar( $url ) ? (string) $url : '', array( 'http', 'https' ) );
	}
	/**
	 * Select an allowed semantic element with a safe fallback.
	 *
	 * @param string $value Requested element name.
	 * @param string $fallback Default element name.
	 * @param bool $heading Whether this is a heading element.
	 * @param bool $container Whether invalid paragraph nesting must be excluded.
	 * @return string Allowed element name.
	 */
	private function tag( $value, $fallback, $heading = false, $container = false ) {
		$allowed = $heading ? array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ) : array( 'div', 'span', 'p', 'section', 'article', 'aside' );
		if ( $container ) { $allowed = array( 'div', 'section', 'article', 'aside' ); }
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}
	/**
	 * Normalize individual CSS class tokens without merging their boundaries.
	 *
	 * @param string $value Space-separated custom class names.
	 * @return string Sanitized space-separated class list.
	 */
	private function classes( $value ) {
		$classes = preg_split( '/\s+/', trim( (string) $value ) );
		return implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );
	}
	/**
	 * Preserve permitted inline label formatting while removing active content.
	 *
	 * @param string $value Shortcode label or separator.
	 * @return string Safe inline HTML.
	 */
	private function label( $value ) {
		return wp_kses( (string) $value, array( 'span' => array( 'class' => true ), 'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array(), 'br' => array() ) );
	}
	/**
	 * Merge shortcode defaults and normalize supplied values to strings.
	 *
	 * @param array|string $atts User shortcode attributes.
	 * @param array $defaults Default attributes supplied by the plugin or its filters.
	 * @param string $shortcode Shortcode tag used by the native attribute filter.
	 * @return array<string,string> Normalized shortcode attributes.
	 */
	private function attributes( $atts, $defaults, $shortcode ) {
		$atts = shortcode_atts( $defaults, $atts, $shortcode );
		foreach ( $atts as $key => $value ) {
			$atts[ $key ] = is_scalar( $value ) ? (string) $value : (string) ( $defaults[ $key ] ?? '' );
		}
		return $atts;
	}
	/**
	 * Choose a supported link target with an archive fallback.
	 *
	 * @param string $value Requested archive, website or none mode.
	 * @return string Normalized link mode.
	 */
	private function link_mode( $value ) {
		return in_array( $value, array( 'archive', 'website', 'none' ), true ) ? $value : 'archive';
	}
	/**
	 * Resolve an escaped archive or website URL for one author.
	 *
	 * @param WP_Term $term Author taxonomy term.
	 * @param string $mode Archive, website or none link mode.
	 * @return string Escaped URL, or an empty string for absent/invalid targets.
	 */
	private function term_url( $term, $mode ) {
		if ( 'none' === $mode ) { return ''; }
		$url = 'website' === $mode ? get_term_meta( $term->term_id, 'pat_website', true ) : get_term_link( $term );
		return is_wp_error( $url ) ? '' : esc_url( $url );
	}
	/**
	 * Resolve one author using ID, slug, name or the current taxonomy archive in that order.
	 *
	 * @param array $atts Normalized selector attributes.
	 * @return WP_Term|null Matching author term, or null without a valid match.
	 */
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
	/**
	 * Render assigned author names for the current or explicitly selected post.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Safe author-list HTML, or an empty string without authors.
	 */
	public function shortcode_authors_list( $atts ) {
		/**
		 * Filter default attributes for the author-name list.
		 *
		 * @since 1.0.0
		 * @param array $defaults Default attributes; return the modified attribute array.
		 */
		$defaults = apply_filters( 'pat/shortcode/authors-list-defaults', array(
			'before' => __( 'Authors:', 'post-author-taxonomy' ), 'after' => '', 'sep' => ', ',
			'class' => '', 'wrapper' => 'span', 'link' => 'archive', 'post_id' => '', 'role' => '', 'roles' => 'no',
		) );
		$atts = $this->attributes( $atts, $defaults, 'pat-authors' );
		$post_id = '' === $atts['post_id'] ? get_the_ID() : absint( $atts['post_id'] );
		$terms = $post_id ? DDW_PAT_Attribution::terms( (int) $post_id, $atts['role'] ) : array();
		if ( ! $terms || is_wp_error( $terms ) ) { return ''; }
		$items = array();
		$role_map = 'yes' === $atts['roles'] ? DDW_PAT_Attribution::role_map( (int) $post_id ) : array();
		$role_labels = 'yes' === $atts['roles'] ? DDW_PAT_Attribution::roles() : array();
		foreach ( $terms as $term ) {
			$url = $this->term_url( $term, $this->link_mode( $atts['link'] ) );
			$name = esc_html( $term->name );
			$rel = 'archive' === $this->link_mode( $atts['link'] ) ? ' rel="tag"' : '';
			$role_label = 'yes' === $atts['roles'] ? ' <span class="pat-author-role">(' . esc_html( $role_labels[ $role_map[ $term->term_id ] ?? 'author' ] ) . ')</span>' : '';
			$items[] = ( $url ? '<a href="' . $url . '"' . $rel . '>' . $name . '</a>' : $name ) . $role_label;
		}
		if ( 'archive' === $this->link_mode( $atts['link'] ) ) { $items = apply_filters( 'term_links-' . self::TAXONOMY, $items ); }
		$before = '' !== $atts['before'] ? $this->label( $atts['before'] ) . ' ' : '';
		$after = '' !== $atts['after'] ? ' ' . $this->label( $atts['after'] ) : '';
		$tag = $this->tag( $atts['wrapper'], 'span' );
		$output = '<' . $tag . ' class="' . esc_attr( trim( 'pat-authors ' . $this->classes( $atts['class'] ) ) ) . '">' . $before . implode( $this->label( $atts['sep'] ), $items ) . $after . '</' . $tag . '>';
		/**
		 * Filter rendered author-name list markup.
		 *
		 * @since 1.0.0
		 * @param string $output Rendered HTML; return the replacement HTML.
		 * @param array $atts Normalized shortcode attributes.
		 */
		return apply_filters( 'pat/shortcode/authors-list', $output, $atts );
	}
	/**
	 * Provide configurable defaults for individual author profile boxes.
	 *
	 * @return array Shortcode defaults returned by the existing author-box filter.
	 */
	private function box_defaults() {
		/**
		 * Filter default attributes for individual author profiles.
		 *
		 * @since 1.2.0
		 * @param array $defaults Default attributes; return the modified attribute array.
		 */
		return apply_filters( 'pat/shortcode/author-box-defaults', array(
			'title' => 'yes', 'headline' => '', 'title_tag' => 'h4', 'id' => '', 'slug' => '', 'name' => '',
			'content_tag' => 'p', 'class' => '', 'wrapper' => 'div', 'photo' => 'yes', 'website' => 'yes', 'link' => 'none', 'roles' => 'no', 'role' => '', 'post_id' => '',
		) );
	}
	/**
	 * Render a local author profile with optional photo, biography and website.
	 *
	 * @param WP_Term $term Author term to display.
	 * @param array $atts Normalized box attributes.
	 * @param string $role_context Valid post-local editorial role key.
	 * @return string Escaped profile markup returned through the existing output filter.
	 */
	private function render_author_box( $term, $atts, $role_context = 'author' ) {
		$tag = $this->tag( $atts['wrapper'], 'div', false, true );
		$title_tag = $this->tag( $atts['title_tag'], 'h4', true );
		$content_tag = $this->tag( $atts['content_tag'], 'p' );
		$title = '' !== $atts['headline'] ? $atts['headline'] : ( 'yes' === $atts['title'] ? $term->name : '' );
		$html = 'yes' === $atts['roles'] ? '<p class="pat-author-box__role">' . esc_html( DDW_PAT_Attribution::roles()[ $role_context ] ?? DDW_PAT_Attribution::roles()['author'] ) . '</p>' : '';
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
		/**
		 * Filter rendered markup for one author profile.
		 *
		 * @since 1.2.0
		 * @param string $output Rendered HTML; return the replacement HTML.
		 * @param array $atts Normalized shortcode attributes.
		 */
		return apply_filters( 'pat/shortcode/author-box', $output, $atts );
	}
	/**
	 * Render one explicit author, or the queried author on a taxonomy archive.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Author profile markup or an empty string.
	 */
	public function shortcode_author_box( $atts ) {
		$atts = $this->attributes( $atts, $this->box_defaults(), 'pat-author-box' );
		$term = $this->resolve_author( $atts );
		$post_id = '' === $atts['post_id'] ? (int) get_the_ID() : absint( $atts['post_id'] );
		return $term ? $this->render_author_box( $term, $atts, 'yes' === $atts['roles'] ? DDW_PAT_Attribution::role( $post_id, $term->term_id ) : 'author' ) : '';
	}
	/**
	 * Render every author assigned to the current or explicitly selected post.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Combined profile markup or an empty string.
	 */
	public function shortcode_author_boxes( $atts ) {
		$defaults = $this->box_defaults();
		unset( $defaults['id'], $defaults['slug'], $defaults['name'] );
		$defaults['post_id'] = '';
		/**
		 * Filter default attributes for all profiles assigned to a post.
		 *
		 * @since 1.3.0
		 * @param array $defaults Default attributes; return the modified attribute array.
		 */
		$atts = $this->attributes( $atts, apply_filters( 'pat/shortcode/author-boxes-defaults', $defaults ), 'pat-author-boxes' );
		$post_id = '' === $atts['post_id'] ? get_the_ID() : absint( $atts['post_id'] );
		$terms = $post_id ? DDW_PAT_Attribution::terms( (int) $post_id, $atts['role'] ) : array();
		if ( ! $terms || is_wp_error( $terms ) ) { return ''; }
		$output = '';
		$role_map = 'yes' === $atts['roles'] ? DDW_PAT_Attribution::role_map( (int) $post_id ) : array();
		foreach ( $terms as $term ) {
			$box_atts = $atts;
			$box_atts['id'] = (string) $term->term_id;
			$box_atts['post_id'] = (string) $post_id;
			$output .= $this->render_author_box( $term, $box_atts, $role_map[ $term->term_id ] ?? 'author' );
		}
		/**
		 * Filter combined author-profile markup for one post.
		 *
		 * @since 1.3.0
		 * @param string $output Combined HTML; return the replacement HTML.
		 * @param array $atts Normalized shortcode attributes.
		 */
		return apply_filters( 'pat/shortcode/author-boxes', $output, $atts );
	}
}
new DDW_Post_Author_Taxonomy();
}
