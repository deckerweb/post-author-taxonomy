<?php
/** Settings and local author profile fields. */
defined( 'ABSPATH' ) || exit;
/** Provide capability-aware site settings and local author profile controls. */
final class DDW_PAT_Admin {
	/**
	 * Register frontend list defaults and screen-scoped administrative hooks.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'pat/shortcode/authors-list-defaults', array( $this, 'list_defaults' ) );
		if ( ! is_admin() ) { return; }
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'pat-author_add_form_fields', array( $this, 'add_fields' ) );
		add_action( 'pat-author_edit_form_fields', array( $this, 'edit_fields' ) );
		add_action( 'created_pat-author', array( $this, 'save_fields' ) );
		add_action( 'edited_pat-author', array( $this, 'save_fields' ) );
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( PAT_PLUGIN_FILE ), array( $this, 'links' ) );
	}
	/**
	 * Add the site-level settings page for users with manage_options.
	 *
	 * @return void
	 */
	public function menu() {
		add_options_page( 'Post Author Taxonomy', 'Post Author Taxonomy', 'manage_options', 'post-author-taxonomy', array( $this, 'page' ) );
	}
	/**
	 * Register site-scoped options and their validation callback.
	 *
	 * @return void
	 */
	public function settings() {
		register_setting( 'pat_settings', 'pat_settings', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_settings' ), 'default' => array( 'post_types' => array( 'post' ), 'link' => 'archive' ) ) );
	}
	/**
	 * Validate public post types and link targets; request rewrite refresh after a post-type change.
	 *
	 * @param mixed $input Submitted settings values.
	 * @return array Valid site settings, preserving Posts when no supported type was chosen.
	 */
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
		if ( ( is_array( $old ) ? ( $old['post_types'] ?? array( 'post' ) ) : array( 'post' ) ) !== $types ) { update_option( 'pat_flush_rewrite', 1, true ); }
		return $safe;
	}
	/**
	 * Apply the saved site link mode before rendering author lists.
	 *
	 * @param array $defaults Default shortcode values.
	 * @return array Defaults with the configured link mode.
	 */
	public function list_defaults( $defaults ) {
		$settings = get_option( 'pat_settings', array() );
		$defaults['link'] = is_array( $settings ) ? ( $settings['link'] ?? 'archive' ) : 'archive';
		return $defaults;
	}
	/**
	 * Enqueue local styles, scripts and media only on this plugin’s settings or term screens.
	 *
	 * @param string $hook WordPress admin page hook suffix.
	 * @return void
	 */
	public function assets( $hook ) {
		$screen = get_current_screen();
		$settings = 'settings_page_post-author-taxonomy' === $hook;
		$terms = $screen && 'pat-author' === $screen->taxonomy && in_array( $screen->base, array( 'edit-tags', 'term' ), true );
		$editor = $screen && 'post' === $screen->base && is_object_in_taxonomy( $screen->post_type, 'pat-author' );
		if ( ! $settings && ! $terms && ! $editor ) { return; }
		wp_enqueue_style( 'pat-admin', plugins_url( 'assets/admin.css', PAT_PLUGIN_FILE ), array(), DDW_Post_Author_Taxonomy::VERSION );
		wp_enqueue_script( 'pat-admin', plugins_url( 'assets/admin.js', PAT_PLUGIN_FILE ), array(), DDW_Post_Author_Taxonomy::VERSION, true );
		wp_localize_script( 'pat-admin', 'PAT_TOOLS', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'pat_editor' ), 'loading' => __( 'Loading preview…', 'post-author-taxonomy' ), 'empty' => __( 'No author output for this selection.', 'post-author-taxonomy' ), 'error' => __( 'The request failed. Please try again.', 'post-author-taxonomy' ), 'changed' => __( 'Order or roles changed. Save the post to apply them.', 'post-author-taxonomy' ), 'refreshed' => __( 'Assigned authors refreshed. Save the post to apply changes.', 'post-author-taxonomy' ) ) );
		if ( $terms ) {
			wp_enqueue_media();
			wp_localize_script( 'pat-admin', 'PAT_MEDIA', array( 'title' => __( 'Choose author photo', 'post-author-taxonomy' ), 'button' => __( 'Use photo', 'post-author-taxonomy' ) ) );
		}
	}
	/**
	 * Output nonce-protected local media and website controls.
	 *
	 * @param WP_Term|null $term Existing author term, or null for a new author.
	 * @return void
	 */
	private function field_content( $term = null ) {
		$id = $term ? absint( get_term_meta( $term->term_id, 'pat_photo_id', true ) ) : 0;
		$url = $term ? get_term_meta( $term->term_id, 'pat_website', true ) : '';
		wp_nonce_field( 'pat_save_profile', 'pat_profile_nonce' );
		echo '<div class="pat-photo-field" data-selected="' . esc_attr__( 'Photo selected. Save the author to apply it.', 'post-author-taxonomy' ) . '" data-removed="' . esc_attr__( 'Photo removed. Save the author to apply it.', 'post-author-taxonomy' ) . '"><input id="pat_photo_id" name="pat_photo_id" type="hidden" value="' . esc_attr( $id ) . '"><div class="pat-photo-preview">';
		if ( $id ) { echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'alt' => '' ) ); }
		echo '</div><button type="button" class="button pat-choose-photo">' . esc_html__( 'Choose photo', 'post-author-taxonomy' ) . '</button> <button type="button" class="button pat-remove-photo"' . disabled( ! $id, true, false ) . '>' . esc_html__( 'Remove photo', 'post-author-taxonomy' ) . '</button><p class="description">' . esc_html__( 'A local image from your media library. No external avatar service is used.', 'post-author-taxonomy' ) . '</p><p class="pat-photo-status description" role="status" aria-live="polite"></p></div>';
		echo '<p><label for="pat_website">' . esc_html__( 'Website', 'post-author-taxonomy' ) . '</label><br><input id="pat_website" name="pat_website" type="url" value="' . esc_attr( $url ) . '" class="regular-text" placeholder="https://"></p>';
	}
	/**
	 * Display profile fields on the Add Author form.
	 *
	 * @return void
	 */
	public function add_fields() {
		echo '<div class="form-field"><label>' . esc_html__( 'Author profile', 'post-author-taxonomy' ) . '</label>';
		$this->field_content();
		echo '</div>';
	}
	/**
	 * Display saved profile fields on the Edit Author form.
	 *
	 * @param WP_Term $term Author being edited.
	 * @return void
	 */
	public function edit_fields( $term ) {
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Author profile', 'post-author-taxonomy' ) . '</th><td>';
		$this->field_content( $term );
		echo '</td></tr>';
	}
	/**
	 * Save author profile metadata only after capability, nonce and media ownership checks.
	 *
	 * @param int $term_id Created or edited author ID.
	 * @return void
	 */
	public function save_fields( $term_id ) {
		$tax = get_taxonomy( 'pat-author' );
		if ( ! $tax || ! current_user_can( $tax->cap->edit_terms ) || ! current_user_can( 'edit_term', (int) $term_id ) || ! isset( $_POST['pat_profile_nonce'] ) || ! is_string( $_POST['pat_profile_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pat_profile_nonce'] ) ), 'pat_save_profile' ) ) { return; }
		if ( ( isset( $_POST['pat_photo_id'] ) && ! is_scalar( $_POST['pat_photo_id'] ) ) || ( isset( $_POST['pat_website'] ) && ! is_string( $_POST['pat_website'] ) ) ) { return; }
		$id = isset( $_POST['pat_photo_id'] ) ? absint( $_POST['pat_photo_id'] ) : 0;
		if ( $id && ( ! wp_attachment_is_image( $id ) || ! current_user_can( 'edit_post', $id ) ) ) { return; }
		if ( isset( $_POST['pat_photo_id'] ) ) { update_term_meta( $term_id, 'pat_photo_id', $id ); }
		if ( isset( $_POST['pat_website'] ) ) {
			$url = esc_url_raw( wp_unslash( $_POST['pat_website'] ), array( 'http', 'https' ) );
			update_term_meta( $term_id, 'pat_website', $url );
		}
	}
	/**
	 * Add capability-aware site settings and author-management action links.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Action links returned through the existing plugin filter.
	 */
	public function links( $links ) {
		if ( is_network_admin() ) { return $links; }
		$tax = get_taxonomy( 'pat-author' );
		if ( $tax && current_user_can( $tax->cap->edit_terms ) ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=pat-author' ) ) . '">' . esc_html__( 'Manage authors', 'post-author-taxonomy' ) . '</a>' );
		}
		if ( current_user_can( 'manage_options' ) ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=post-author-taxonomy' ) ) . '">' . esc_html__( 'Settings', 'post-author-taxonomy' ) . '</a>' );
		}
		/**
		 * Filter capability-aware author/settings links; return the modified link array.
		 *
		 * @since 1.0.0
		 * @param array $links Existing links; return the modified array.
		 */
		return apply_filters( 'pat/plugins-page/tax-link', $links );
	}
	/**
	 * Append optional support and newsletter links to this plugin only.
	 *
	 * @param array $links Existing plugin row metadata links.
	 * @param string $file Plugin basename being rendered.
	 * @return array Plugin metadata links.
	 */
	public function row_meta( $links, $file ) {
		if ( plugin_basename( PAT_PLUGIN_FILE ) !== $file ) { return $links; }
		$links[] = '<a href="https://ko-fi.com/deckerweb" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support the project', 'post-author-taxonomy' ) . '</a>';
		$links[] = '<a href="https://eepurl.com/gbAUUn" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Newsletter', 'post-author-taxonomy' ) . '</a>';
		/**
		 * Filter plugin metadata links; return the modified link array.
		 *
		 * @since 1.0.0
		 * @param array $links Existing links; return the modified array.
		 */
		return apply_filters( 'pat/plugins-page/meta-links', $links );
	}
	/**
	 * Render the authorized settings page and its accessible local changelog dialog.
	 *
	 * @return void
	 */
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$settings = get_option( 'pat_settings', array( 'post_types' => array( 'post' ), 'link' => 'archive' ) );
		$settings = is_array( $settings ) ? $settings : array();
		if ( ! is_array( $settings['post_types'] ?? null ) ) { $settings['post_types'] = array( 'post' ); }
		$german = 1 === preg_match( '/^de(?:_|$)/i', determine_locale() );
		$doc = 'https://github.com/deckerweb/post-author-taxonomy/wiki/' . ( $german ? 'Deutsch' : 'English' );
		$changelog = plugins_url( $german ? 'docs/changelog-de.txt' : 'docs/changelog.txt', PAT_PLUGIN_FILE );
		echo '<div class="wrap pat-root"><div class="pat-page-heading"><img src="' . esc_url( plugins_url( 'assets/icon.svg', PAT_PLUGIN_FILE ) ) . '" alt="" width="56" height="56"><div><h1>Post Author Taxonomy</h1><p>' . esc_html__( 'Your authors. Your layout. Your WordPress.', 'post-author-taxonomy' ) . '</p></div></div><hr class="wp-header-end">';
		settings_errors( 'pat_settings' );
		if ( is_string( $_GET['pat_archives'] ?? null ) && 'refreshed' === sanitize_key( wp_unslash( $_GET['pat_archives'] ) ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Author archive rules refreshed. All content and settings were kept.', 'post-author-taxonomy' ) . '</p></div>';
		}
		echo '<div class="pat-grid"><section class="pat-panel"><h2>' . esc_html__( 'Ready in three steps', 'post-author-taxonomy' ) . '</h2><ol><li>' . esc_html__( 'Create authors with a name, biography and optional photo.', 'post-author-taxonomy' ) . '</li><li>' . esc_html__( 'Assign authors to your posts.', 'post-author-taxonomy' ) . '</li><li>' . esc_html__( 'Add a shortcode to your content or builder template.', 'post-author-taxonomy' ) . '</li></ol><p><a class="button button-primary" href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=pat-author' ) ) . '">' . esc_html__( 'Manage authors', 'post-author-taxonomy' ) . '</a></p>';
		foreach ( array( '[pat-authors]' => __( 'Linked names for the current post', 'post-author-taxonomy' ), '[pat-authors link="none"]' => __( 'Names without links', 'post-author-taxonomy' ), '[pat-author-boxes]' => __( 'All author profiles for the current post', 'post-author-taxonomy' ), '[pat-author-box slug="jane-doe"]' => __( 'One author, on any page', 'post-author-taxonomy' ), '[pat-author-box]' => __( 'Current author on an author taxonomy archive', 'post-author-taxonomy' ) ) as $code => $label ) {
			echo '<p>' . esc_html( $label ) . '<br><code>' . esc_html( $code ) . '</code> <button type="button" class="button button-small pat-copy" data-code="' . esc_attr( $code ) . '" data-copied="' . esc_attr__( 'Copied', 'post-author-taxonomy' ) . '" data-fallback="' . esc_attr__( 'Shortcode selected. Copy it with your keyboard.', 'post-author-taxonomy' ) . '">' . esc_html__( 'Copy', 'post-author-taxonomy' ) . '</button></p>';
		}
		echo '<p class="description">' . esc_html__( 'Author attribution does not change WordPress user accounts or editing permissions.', 'post-author-taxonomy' ) . '</p></section><section class="pat-panel"><h2>' . esc_html__( 'Settings', 'post-author-taxonomy' ) . '</h2><form class="pat-settings-form" action="options.php" method="post" data-unsaved="' . esc_attr__( 'Unsaved changes. Save to apply them.', 'post-author-taxonomy' ) . '">';
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
		echo '<p class="description pat-settings-status" role="status" aria-live="polite"></p>';
		echo '</form></section></div>';
		/**
		 * Render local optional tools below the main settings panels.
		 * @since 1.4.0
		 */
		do_action( 'pat_admin_tools' );
		echo '<footer class="pat-footer" aria-label="' . esc_attr__( 'Plugin information', 'post-author-taxonomy' ) . '"><div><strong>Post Author Taxonomy</strong> <span>' . esc_html__( 'Version', 'post-author-taxonomy' ) . ' ' . esc_html( DDW_Post_Author_Taxonomy::VERSION ) . '</span> · <a data-pat-document="changelog" href="' . esc_url( $changelog ) . '">' . esc_html__( 'Changelog', 'post-author-taxonomy' ) . '</a> · <a href="' . esc_url( $doc ) . '">' . esc_html__( 'Documentation', 'post-author-taxonomy' ) . '</a><p>' . esc_html__( 'Your authors. Your layout. Your WordPress.', 'post-author-taxonomy' ) . '</p></div><div><span>© 2017–2026 <a href="https://github.com/deckerweb" target="_blank" rel="noopener noreferrer">David Decker – DECKERWEB</a></span><a href="https://github.com/deckerweb/post-author-taxonomy" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Plugin website', 'post-author-taxonomy' ) . '</a></div></footer></div>';
		\Deckerweb\PostAuthorTaxonomy\Changelog::dialog();
	}
}
