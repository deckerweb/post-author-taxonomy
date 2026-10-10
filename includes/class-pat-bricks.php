<?php
/** Optional integration through the public Bricks dynamic-data filters; no builder code is bundled. */
defined( 'ABSPATH' ) || exit;

/** Expose existing author output and the first ordered profile to Bricks contexts. */
final class DDW_PAT_Bricks {
	/** Wait until the active theme and the host taxonomy are available. @return void */
	public function register(): void { add_action( 'init', array( $this, 'boot' ), 110 ); }
	/** Attach public dynamic-data filters only when Bricks is active. @return void */
	public function boot(): void {
		if ( ! defined( 'BRICKS_VERSION' ) ) { return; }
		add_filter( 'bricks/dynamic_tags_list', array( $this, 'tags' ) );
		add_filter( 'bricks/dynamic_data/render_tag', array( $this, 'render' ), 20, 3 );
		add_filter( 'bricks/dynamic_data/render_content', array( $this, 'content' ), 20, 3 );
		add_filter( 'bricks/frontend/render_data', array( $this, 'content' ), 20, 2 );
	}
	/**
	 * Append namespaced author tags without altering any existing provider.
	 * @param array $tags Existing public dynamic-data choices.
	 * @return array Existing choices plus five host tags.
	 */
	public function tags( $tags ): array {
		$labels = array( 'pat_authors' => __( 'Author names', 'post-author-taxonomy' ), 'pat_author_boxes' => __( 'All author profiles', 'post-author-taxonomy' ), 'pat_author_name' => __( 'First author name', 'post-author-taxonomy' ), 'pat_author_photo' => __( 'First author photo', 'post-author-taxonomy' ), 'pat_author_website' => __( 'First author website', 'post-author-taxonomy' ) );
		foreach ( $labels as $key => $label ) { $tags[] = array( 'name' => '{' . $key . '}', 'label' => $label, 'group' => 'Post Author Taxonomy' ); }
		return $tags;
	}
	/**
	 * Render only exact host tags in the supplied post or author-archive context.
	 * @param mixed $tag Bricks tag identifier, possibly already resolved by another provider.
	 * @param WP_Post|WP_Term|int|null $post Bricks current post context.
	 * @param string $context Text, image or link output context.
	 * @return mixed Untouched foreign tag, escaped host output, or an attachment-ID array for images.
	 */
	public function render( $tag, $post, $context = 'text' ) {
		if ( ! is_string( $tag ) ) { return $tag; }
		$key = trim( $tag, '{}' );
		if ( ! in_array( $key, array( 'pat_authors', 'pat_author_boxes', 'pat_author_name', 'pat_author_photo', 'pat_author_website' ), true ) ) { return $tag; }
		$id = $post instanceof WP_Post ? $post->ID : ( is_numeric( $post ) ? absint( $post ) : (int) get_the_ID() );
		$core = new DDW_Post_Author_Taxonomy( false );
		if ( 'pat_authors' === $key ) { return $core->shortcode_authors_list( array( 'post_id' => (string) $id, 'before' => '', 'link' => 'none' ) ); }
		if ( 'pat_author_boxes' === $key ) { return $core->shortcode_author_boxes( array( 'post_id' => (string) $id ) ); }
		$terms = $id ? DDW_PAT_Attribution::terms( $id ) : array();
		$term = $post instanceof WP_Term && 'pat-author' === $post->taxonomy ? $post : ( is_tax( 'pat-author' ) ? get_queried_object() : ( $terms[0] ?? null ) );
		if ( ! $term instanceof WP_Term || 'pat-author' !== $term->taxonomy ) { return 'image' === $context ? array() : ''; }
		if ( 'pat_author_name' === $key ) { return esc_html( $term->name ); }
		if ( 'pat_author_website' === $key ) { return esc_url( $core->sanitize_website( get_term_meta( $term->term_id, 'pat_website', true ) ) ); }
		$photo = absint( get_term_meta( $term->term_id, 'pat_photo_id', true ) );
		if ( ! $photo || ! wp_attachment_is_image( $photo ) ) { return 'image' === $context ? array() : ''; }
		return 'image' === $context ? array( $photo ) : esc_url( wp_get_attachment_image_url( $photo, 'full' ) ?: '' );
	}
	/**
	 * Replace exact host tokens in larger strings, leaving foreign tags and non-strings intact.
	 * @param mixed $content Bricks content potentially containing dynamic-data tokens.
	 * @param WP_Post|WP_Term|int|null $post Bricks current post context.
	 * @param string $context Requested text/image/link context.
	 * @return mixed Original content type or a string with host tokens rendered.
	 */
	public function content( $content, $post, $context = 'text' ) {
		if ( ! is_string( $content ) || ! str_contains( $content, '{pat_' ) ) { return $content; }
		if ( 'image' === $context && preg_match( '/^\{pat_author_photo\}$/D', $content ) ) { return $this->render( $content, $post, $context ); }
		return preg_replace_callback( '/\{(?:pat_authors|pat_author_boxes|pat_author_name|pat_author_photo|pat_author_website)\}/', function( $match ) use ( $post, $context ) { $value = $this->render( $match[0], $post, $context ); return is_scalar( $value ) ? (string) $value : ''; }, $content );
	}
}
