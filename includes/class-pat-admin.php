<?php
/** Settings and local author profile fields. */
defined( 'ABSPATH' ) || exit;
final class DDW_PAT_Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'pat-author_add_form_fields', array( $this, 'add_fields' ) );
		add_action( 'pat-author_edit_form_fields', array( $this, 'edit_fields' ) );
		add_action( 'created_pat-author', array( $this, 'save_fields' ) );
		add_action( 'edited_pat-author', array( $this, 'save_fields' ) );
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( PAT_PLUGIN_FILE ), array( $this, 'links' ) );
		add_filter( 'pat/shortcode/authors-list-defaults', array( $this, 'list_defaults' ) );
	}
	public function menu() {
		add_options_page( 'Post Author Taxonomy', 'Post Author Taxonomy', 'manage_options', 'post-author-taxonomy', array( $this, 'page' ) );
	}
	public function settings() {
		register_setting( 'pat_settings', 'pat_settings', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_settings' ), 'default' => array( 'post_types' => array( 'post' ), 'link' => 'archive' ) ) );
	}
	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		$allowed = array_diff( array_keys( get_post_types( array( 'public' => true ) ) ), array( 'attachment' ) );
		$selected = is_array( $input['post_types'] ?? null ) ? array_filter( $input['post_types'], 'is_string' ) : array();
		$types = array_values( array_intersect( $allowed, $selected ) );
		if ( ! $types ) {
			$types = array( 'post' );
			add_settings_error( 'pat_settings', 'pat_post_types', __( 'Select at least one post type. Posts have been kept enabled.', 'post-author-taxonomy' ), 'warning' );
		}
		$safe = array( 'post_types' => $types, 'link' => in_array( $input['link'] ?? '', array( 'archive', 'website', 'none' ), true ) ? $input['link'] : 'archive' );
		$old = get_option( 'pat_settings', array( 'post_types' => array( 'post' ) ) );
		if ( ( $old['post_types'] ?? array( 'post' ) ) !== $types ) { update_option( 'pat_flush_rewrite', 1, false ); }
		return $safe;
	}
	public function list_defaults( $defaults ) {
		$defaults['link'] = get_option( 'pat_settings', array() )['link'] ?? 'archive';
		return $defaults;
	}
	public function assets( $hook ) {
		if ( get_option( 'pat_flush_rewrite' ) && did_action( 'init' ) ) {
			flush_rewrite_rules( false );
			delete_option( 'pat_flush_rewrite' );
		}
		$screen = get_current_screen();
		$settings = 'settings_page_post-author-taxonomy' === $hook;
		$terms = $screen && 'pat-author' === $screen->taxonomy && in_array( $screen->base, array( 'edit-tags', 'term' ), true );
		if ( ! $settings && ! $terms ) { return; }
		wp_enqueue_style( 'pat-admin', plugins_url( 'assets/admin.css', PAT_PLUGIN_FILE ), array(), DDW_Post_Author_Taxonomy::VERSION );
		wp_enqueue_script( 'pat-admin', plugins_url( 'assets/admin.js', PAT_PLUGIN_FILE ), array(), DDW_Post_Author_Taxonomy::VERSION, true );
		if ( $terms ) {
			wp_enqueue_media();
			wp_localize_script( 'pat-admin', 'PAT_MEDIA', array( 'title' => __( 'Choose author photo', 'post-author-taxonomy' ), 'button' => __( 'Use photo', 'post-author-taxonomy' ) ) );
		}
	}
	private function field_content( $term = null ) {
		$id = $term ? absint( get_term_meta( $term->term_id, 'pat_photo_id', true ) ) : 0;
		$url = $term ? get_term_meta( $term->term_id, 'pat_website', true ) : '';
		wp_nonce_field( 'pat_save_profile', 'pat_profile_nonce' );
		echo '<div class="pat-photo-field"><input id="pat_photo_id" name="pat_photo_id" type="hidden" value="' . esc_attr( $id ) . '"><div class="pat-photo-preview">';
		if ( $id ) { echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'alt' => '' ) ); }
		echo '</div><button type="button" class="button pat-choose-photo">' . esc_html__( 'Choose photo', 'post-author-taxonomy' ) . '</button> <button type="button" class="button pat-remove-photo">' . esc_html__( 'Remove photo', 'post-author-taxonomy' ) . '</button><p class="description">' . esc_html__( 'A local image from your media library. No external avatar service is used.', 'post-author-taxonomy' ) . '</p></div>';
		echo '<p><label for="pat_website">' . esc_html__( 'Website', 'post-author-taxonomy' ) . '</label><br><input id="pat_website" name="pat_website" type="url" value="' . esc_attr( $url ) . '" class="regular-text" placeholder="https://"></p>';
	}
	public function add_fields() {
		echo '<div class="form-field"><label>' . esc_html__( 'Author profile', 'post-author-taxonomy' ) . '</label>';
		$this->field_content();
		echo '</div>';
	}
	public function edit_fields( $term ) {
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Author profile', 'post-author-taxonomy' ) . '</th><td>';
		$this->field_content( $term );
		echo '</td></tr>';
	}
	public function save_fields( $term_id ) {
		$tax = get_taxonomy( 'pat-author' );
		if ( ! $tax || ! current_user_can( $tax->cap->edit_terms ) || ! isset( $_POST['pat_profile_nonce'] ) || ! is_string( $_POST['pat_profile_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pat_profile_nonce'] ) ), 'pat_save_profile' ) ) { return; }
		$id = isset( $_POST['pat_photo_id'] ) && is_scalar( $_POST['pat_photo_id'] ) ? absint( $_POST['pat_photo_id'] ) : 0;
		if ( $id && ( ! wp_attachment_is_image( $id ) || ! current_user_can( 'edit_post', $id ) ) ) { return; }
		update_term_meta( $term_id, 'pat_photo_id', $id );
		$url = isset( $_POST['pat_website'] ) && is_string( $_POST['pat_website'] ) ? esc_url_raw( wp_unslash( $_POST['pat_website'] ), array( 'http', 'https' ) ) : '';
		update_term_meta( $term_id, 'pat_website', $url );
	}
	public function links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=pat-author' ) ) . '">' . esc_html__( 'Manage authors', 'post-author-taxonomy' ) . '</a>' );
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=post-author-taxonomy' ) ) . '">' . esc_html__( 'Settings', 'post-author-taxonomy' ) . '</a>' );
		return apply_filters( 'pat/plugins-page/tax-link', $links );
	}
	public function row_meta( $links, $file ) {
		if ( plugin_basename( PAT_PLUGIN_FILE ) !== $file ) { return $links; }
		$links[] = '<a href="https://ko-fi.com/deckerweb" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support the project', 'post-author-taxonomy' ) . '</a>';
		$links[] = '<a href="https://eepurl.com/gbAUUn" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Newsletter', 'post-author-taxonomy' ) . '</a>';
		return apply_filters( 'pat/plugins-page/meta-links', $links );
	}
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$settings = get_option( 'pat_settings', array( 'post_types' => array( 'post' ), 'link' => 'archive' ) );
		$german = 1 === preg_match( '/^de(?:_|$)/i', determine_locale() );
		$doc = 'https://github.com/deckerweb/post-author-taxonomy/wiki/' . ( $german ? 'Deutsch' : 'English' );
		$changelog = plugins_url( $german ? 'docs/changelog-de.txt' : 'docs/changelog.txt', PAT_PLUGIN_FILE );
		echo '<div class="wrap pat-root"><div class="pat-page-heading"><img src="' . esc_url( plugins_url( 'assets/icon.svg', PAT_PLUGIN_FILE ) ) . '" alt="" width="56" height="56"><div><h1>Post Author Taxonomy</h1><p>' . esc_html__( 'Your authors. Your layout. Your WordPress.', 'post-author-taxonomy' ) . '</p></div></div>';
		settings_errors( 'pat_settings' );
		echo '<div class="pat-grid"><section class="pat-panel"><h2>' . esc_html__( 'Ready in three steps', 'post-author-taxonomy' ) . '</h2><ol><li>' . esc_html__( 'Create authors with a name, biography and optional photo.', 'post-author-taxonomy' ) . '</li><li>' . esc_html__( 'Assign authors to your posts.', 'post-author-taxonomy' ) . '</li><li>' . esc_html__( 'Add a shortcode to your content or builder template.', 'post-author-taxonomy' ) . '</li></ol><p><a class="button button-primary" href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=pat-author' ) ) . '">' . esc_html__( 'Manage authors', 'post-author-taxonomy' ) . '</a></p>';
		foreach ( array( '[pat-authors]' => __( 'Linked names for the current post', 'post-author-taxonomy' ), '[pat-authors link="none"]' => __( 'Names without links', 'post-author-taxonomy' ), '[pat-author-boxes]' => __( 'All author profiles for the current post', 'post-author-taxonomy' ), '[pat-author-box slug="jane-doe"]' => __( 'One author, on any page', 'post-author-taxonomy' ), '[pat-author-box]' => __( 'Current author on an author taxonomy archive', 'post-author-taxonomy' ) ) as $code => $label ) {
			echo '<p>' . esc_html( $label ) . '<br><code>' . esc_html( $code ) . '</code> <button type="button" class="button button-small pat-copy" data-code="' . esc_attr( $code ) . '" data-copied="' . esc_attr__( 'Copied', 'post-author-taxonomy' ) . '">' . esc_html__( 'Copy', 'post-author-taxonomy' ) . '</button></p>';
		}
		echo '<p class="description">' . esc_html__( 'Author attribution does not change WordPress user accounts or editing permissions.', 'post-author-taxonomy' ) . '</p></section><section class="pat-panel"><h2>' . esc_html__( 'Settings', 'post-author-taxonomy' ) . '</h2><form action="options.php" method="post">';
		settings_fields( 'pat_settings' );
		echo '<fieldset><legend><strong>' . esc_html__( 'Enable authors for', 'post-author-taxonomy' ) . '</strong></legend>';
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			if ( 'attachment' === $type->name ) { continue; }
			echo '<p><label><input type="checkbox" name="pat_settings[post_types][]" value="' . esc_attr( $type->name ) . '" ' . checked( in_array( $type->name, $settings['post_types'] ?? array( 'post' ), true ), true, false ) . '> ' . esc_html( $type->labels->name ) . '</label></p>';
		}
		echo '</fieldset><p><label for="pat-link"><strong>' . esc_html__( 'Default links in author lists', 'post-author-taxonomy' ) . '</strong></label></p><select id="pat-link" name="pat_settings[link]">';
		foreach ( array( 'archive' => __( 'Author archive', 'post-author-taxonomy' ), 'website' => __( 'Author website', 'post-author-taxonomy' ), 'none' => __( 'No links', 'post-author-taxonomy' ) ) as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $settings['link'] ?? 'archive', $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select><p class="description">' . esc_html__( 'A shortcode link attribute overrides this default. Authors without a website appear as plain text in website mode.', 'post-author-taxonomy' ) . '</p>';
		submit_button();
		echo '</form></section></div><footer class="pat-footer" aria-label="' . esc_attr__( 'Plugin information', 'post-author-taxonomy' ) . '"><div><strong>Post Author Taxonomy</strong> <span>' . esc_html__( 'Version', 'post-author-taxonomy' ) . ' ' . esc_html( DDW_Post_Author_Taxonomy::VERSION ) . '</span> · <a data-pat-document="changelog" href="' . esc_url( $changelog ) . '">' . esc_html__( 'Changelog', 'post-author-taxonomy' ) . '</a> · <a href="' . esc_url( $doc ) . '">' . esc_html__( 'Documentation', 'post-author-taxonomy' ) . '</a><p>' . esc_html__( 'Your authors. Your layout. Your WordPress.', 'post-author-taxonomy' ) . '</p></div><div><span>© 2017–2026 <a href="https://github.com/deckerweb" target="_blank" rel="noopener noreferrer">David Decker – DECKERWEB</a></span><a href="https://github.com/deckerweb/post-author-taxonomy" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Plugin website', 'post-author-taxonomy' ) . '</a></div></footer></div>';
		\Deckerweb\PostAuthorTaxonomy\Changelog::dialog();
	}
}
