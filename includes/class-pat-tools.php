<?php
/** Scoped shortcode configuration, term-list convenience and local archive diagnostics. */
defined( 'ABSPATH' ) || exit;

/** Provide optional admin tools without changing saved shortcode defaults or frontend assets. */
final class DDW_PAT_Tools {
	/** Attach only administrative rendering and explicitly requested actions. @return void */
	public function register(): void {
		if ( ! is_admin() ) { return; }
		add_action( 'pat_admin_tools', array( $this, 'panel' ) );
		add_action( 'wp_ajax_pat_preview', array( $this, 'preview' ) );
		add_action( 'admin_post_pat_refresh_archives', array( $this, 'refresh_archives' ) );
		add_filter( 'manage_edit-pat-author_columns', array( $this, 'columns' ) );
		add_filter( 'manage_pat-author_custom_column', array( $this, 'column' ), 10, 3 );
	}
	/**
	 * Add profile overview and a reusable individual shortcode to this term table only.
	 * @param array $columns Existing term-table columns.
	 * @return array Term columns with photo/details and shortcode appended.
	 */
	public function columns( $columns ): array {
		$columns['pat_profile'] = __( 'Profile overview', 'post-author-taxonomy' );
		$columns['pat_shortcode'] = __( 'Author shortcode', 'post-author-taxonomy' );
		return $columns;
	}
	/**
	 * Render escaped local profile details and one contextual clipboard action.
	 * @param string $content Previous custom-column HTML.
	 * @param string $column Requested column identifier.
	 * @param int $term_id Author term ID.
	 * @return string Original content or fixed escaped profile/shortcode markup.
	 */
	public function column( $content, $column, $term_id ): string {
		$term = get_term( $term_id, 'pat-author' );
		if ( ! $term instanceof WP_Term ) { return (string) $content; }
		if ( 'pat_profile' === $column ) {
			$photo = absint( get_term_meta( $term_id, 'pat_photo_id', true ) );
			$details = array();
			if ( $photo && wp_attachment_is_image( $photo ) ) { $details[] = __( 'Photo', 'post-author-taxonomy' ); }
			if ( trim( wp_strip_all_tags( $term->description ) ) ) { $details[] = __( 'Biography', 'post-author-taxonomy' ); }
			if ( ( new DDW_Post_Author_Taxonomy( false ) )->sanitize_website( get_term_meta( $term_id, 'pat_website', true ) ) ) { $details[] = __( 'Website', 'post-author-taxonomy' ); }
			$html = $photo && wp_attachment_is_image( $photo ) ? wp_get_attachment_image( $photo, array( 40, 40 ), false, array( 'alt' => '', 'class' => 'pat-table-photo', 'loading' => 'lazy' ) ) : '';
			return $html . '<span>' . esc_html( $details ? implode( ' · ', $details ) : __( 'Name only; profile details are optional.', 'post-author-taxonomy' ) ) . '</span>';
		}
		if ( 'pat_shortcode' === $column ) {
			$code = '[pat-author-box id="' . absint( $term_id ) . '"]';
			return '<p><code>' . esc_html( $code ) . '</code> <button type="button" class="button button-small pat-copy" data-code="' . esc_attr( $code ) . '" data-copied="' . esc_attr__( 'Copied', 'post-author-taxonomy' ) . '" data-fallback="' . esc_attr__( 'Shortcode selected. Copy it with your keyboard.', 'post-author-taxonomy' ) . '">' . esc_html__( 'Copy', 'post-author-taxonomy' ) . '</button></p>';
		}
		return (string) $content;
	}
	/** Render an opt-in configurator and compact diagnostics inside the existing settings page. @return void */
	public function panel(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		echo '<section class="pat-panel pat-config-panel"><h2>' . esc_html__( 'Shortcode configurator', 'post-author-taxonomy' ) . '</h2><p>' . esc_html__( 'Choose an output, check it with a real post, then copy the shortcode. This tool does not change saved defaults.', 'post-author-taxonomy' ) . '</p><form class="pat-config-form"><div class="pat-config-grid">';
		self::select( 'output', __( 'Output', 'post-author-taxonomy' ), array( 'pat-authors' => __( 'Author names', 'post-author-taxonomy' ), 'pat-author-boxes' => __( 'All author profiles', 'post-author-taxonomy' ), 'pat-author-box' => __( 'One author profile', 'post-author-taxonomy' ) ) );
		self::select( 'context', __( 'Post context', 'post-author-taxonomy' ), array( 'current' => __( 'Current post or archive', 'post-author-taxonomy' ), 'fixed' => __( 'Use the selected post everywhere', 'post-author-taxonomy' ) ) );
		$tax = get_taxonomy( 'pat-author' );
		$posts = get_posts( array( 'post_type' => $tax ? $tax->object_type : array( 'post' ), 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ), 'numberposts' => 30 ) );
		$options = array( '' => __( 'Select a preview post', 'post-author-taxonomy' ) );
		foreach ( $posts as $post ) { if ( current_user_can( 'edit_post', $post->ID ) ) { $options[ $post->ID ] = get_the_title( $post ) ?: '#' . $post->ID; } }
		self::select( 'preview_post', __( 'Preview post', 'post-author-taxonomy' ), $options );
		echo '<p><label for="pat-config-post-override">' . esc_html__( 'Post ID (optional)', 'post-author-taxonomy' ) . '</label><input id="pat-config-post-override" name="post_override" type="number" min="1" step="1"><span class="description">' . esc_html__( 'For posts outside the recent preview list.', 'post-author-taxonomy' ) . '</span></p>';
		$terms = get_terms( array( 'taxonomy' => 'pat-author', 'hide_empty' => false, 'number' => 200 ) );
		$options = array( '' => __( 'Current archive author', 'post-author-taxonomy' ) );
		foreach ( is_array( $terms ) ? $terms : array() as $term ) { $options[ $term->term_id ] = $term->name; }
		self::select( 'author', __( 'Individual author', 'post-author-taxonomy' ), $options );
		self::select( 'link', __( 'Name links', 'post-author-taxonomy' ), array( 'archive' => __( 'Author archive', 'post-author-taxonomy' ), 'website' => __( 'Author website', 'post-author-taxonomy' ), 'none' => __( 'No links', 'post-author-taxonomy' ) ) );
		self::select( 'role', __( 'Role filter', 'post-author-taxonomy' ), array( '' => __( 'All roles', 'post-author-taxonomy' ) ) + DDW_PAT_Attribution::roles() );
		echo '</div><p><label><input type="checkbox" name="photo" checked> ' . esc_html__( 'Show photo', 'post-author-taxonomy' ) . '</label> <label><input type="checkbox" name="website" checked> ' . esc_html__( 'Show website', 'post-author-taxonomy' ) . '</label> <label><input type="checkbox" name="roles"> ' . esc_html__( 'Show editorial roles', 'post-author-taxonomy' ) . '</label></p><p><button type="button" class="button pat-preview-refresh">' . esc_html__( 'Refresh preview', 'post-author-taxonomy' ) . '</button></p><p><code class="pat-config-code">[pat-authors]</code> <button type="button" class="button pat-copy" data-code="[pat-authors]" data-copied="' . esc_attr__( 'Copied', 'post-author-taxonomy' ) . '" data-fallback="' . esc_attr__( 'Shortcode selected. Copy it with your keyboard.', 'post-author-taxonomy' ) . '">' . esc_html__( 'Copy', 'post-author-taxonomy' ) . '</button></p><p class="pat-config-status" role="status" aria-live="polite"></p><div class="pat-config-preview" aria-label="' . esc_attr__( 'Shortcode preview', 'post-author-taxonomy' ) . '"></div><p class="description">' . esc_html__( 'The preview uses your actual author data. Your theme or builder controls the final appearance. A current-archive author needs an author archive to render.', 'post-author-taxonomy' ) . '</p></form></section>';
		self::diagnostics();
	}
	/**
	 * Render one labeled configurator select from a fixed option map.
	 * @param string $name Unique form field suffix.
	 * @param string $label Translated visible label.
	 * @param array $options Scalar keys and labels.
	 * @return void
	 */
	private static function select( string $name, string $label, array $options ): void {
		echo '<p><label for="pat-config-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label><select id="pat-config-' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">';
		foreach ( $options as $value => $text ) { echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $text ) . '</option>'; }
		echo '</select></p>';
	}
	/**
	 * Build one known shortcode and safe attributes from bounded scalar controls.
	 * @param array $input Raw configurator field map; unknown fields are ignored.
	 * @return array{tag:string,attributes:array,code:string} Canonical shortcode representation.
	 */
	public static function configuration( array $input ): array {
		$tag = in_array( $input['output'] ?? '', array( 'pat-authors', 'pat-author-box', 'pat-author-boxes' ), true ) ? $input['output'] : 'pat-authors';
		$atts = array( 'link' => in_array( $input['link'] ?? '', array( 'archive', 'website', 'none' ), true ) ? $input['link'] : 'archive' );
		$role = is_string( $input['role'] ?? null ) ? $input['role'] : '';
		if ( isset( DDW_PAT_Attribution::roles()[ $role ] ) && 'pat-author-box' !== $tag ) { $atts['role'] = $role; }
		if ( 'yes' === ( $input['roles'] ?? '' ) ) { $atts['roles'] = 'yes'; }
		if ( 'pat-author-box' === $tag && is_scalar( $input['author'] ?? null ) && absint( $input['author'] ) ) { $atts['id'] = (string) absint( $input['author'] ); }
		if ( 'fixed' === ( $input['context'] ?? '' ) && is_scalar( $input['preview_post'] ?? null ) && absint( $input['preview_post'] ) ) { $atts['post_id'] = (string) absint( $input['preview_post'] ); }
		if ( 'pat-authors' !== $tag ) {
			$atts['photo'] = 'no' === ( $input['photo'] ?? '' ) ? 'no' : 'yes';
			$atts['website'] = 'no' === ( $input['website'] ?? '' ) ? 'no' : 'yes';
		}
		$code = '[' . $tag; foreach ( $atts as $name => $value ) { $code .= ' ' . $name . '="' . $value . '"'; } $code .= ']';
		return array( 'tag' => $tag, 'attributes' => $atts, 'code' => $code );
	}
	/** Render a permission-scoped preview without executing arbitrary submitted shortcode tags. @return void Sends JSON; does not write settings/content. */
	public function preview(): void {
		check_ajax_referer( 'pat_editor', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => __( 'You cannot use this preview.', 'post-author-taxonomy' ) ), 403 ); }
		$input = isset( $_POST['configuration'] ) && is_string( $_POST['configuration'] ) && strlen( $_POST['configuration'] ) <= 4096 ? json_decode( wp_unslash( $_POST['configuration'] ), true ) : null;
		if ( ! is_array( $input ) ) { wp_send_json_error( array( 'message' => __( 'Invalid preview configuration.', 'post-author-taxonomy' ) ), 400 ); }
		if ( 'fixed' === ( $input['context'] ?? '' ) && ( ! is_scalar( $input['preview_post'] ?? null ) || ! absint( $input['preview_post'] ) ) ) { wp_send_json_error( array( 'message' => __( 'Choose a preview post for a fixed context.', 'post-author-taxonomy' ) ), 400 ); }
		$config = self::configuration( $input );
		$id = is_scalar( $input['preview_post'] ?? null ) ? absint( $input['preview_post'] ) : 0;
		if ( $id && ( ! current_user_can( 'edit_post', $id ) || ! is_object_in_taxonomy( get_post_type( $id ), 'pat-author' ) ) ) { wp_send_json_error( array( 'message' => __( 'Choose a post you can edit with authors enabled.', 'post-author-taxonomy' ) ), 403 ); }
		$atts = $config['attributes']; $atts['post_id'] = (string) $id;
		$core = new DDW_Post_Author_Taxonomy( false );
		$method = array( 'pat-authors' => 'shortcode_authors_list', 'pat-author-box' => 'shortcode_author_box', 'pat-author-boxes' => 'shortcode_author_boxes' )[ $config['tag'] ];
		$html = $core->$method( $atts );
		wp_send_json_success( array( 'code' => $config['code'], 'html' => wp_kses_post( $html ), 'empty' => '' === $html ) );
	}
	/** Render local version/archive information without network calls or personal author content. @return void */
	public static function diagnostics(): void {
		$tax = get_taxonomy( 'pat-author' );
		$updater = '\Deckerweb\GitHubReleaseUpdater\V2\Updater';
		$runtime = $GLOBALS['deckerweb_library_runtime_v1'] ?? null;
		$library_version = is_object( $runtime ) && defined( get_class( $runtime ) . '::VERSION' ) ? constant( get_class( $runtime ) . '::VERSION' ) : __( 'Not loaded on this screen', 'post-author-taxonomy' );
		$update_version = defined( $updater . '::IMPLEMENTATION_VERSION' ) ? constant( $updater . '::IMPLEMENTATION_VERSION' ) : __( 'Not loaded on this screen', 'post-author-taxonomy' );
		echo '<section class="pat-panel pat-diagnostics"><details><summary>' . esc_html__( 'Local diagnostics', 'post-author-taxonomy' ) . '</summary><dl>';
		$items = array( __( 'Version', 'post-author-taxonomy' ) => DDW_Post_Author_Taxonomy::VERSION, __( 'Enabled post types', 'post-author-taxonomy' ) => $tax ? implode( ', ', $tax->object_type ) : '—', __( 'Author archive base', 'post-author-taxonomy' ) => $tax && is_array( $tax->rewrite ) ? $tax->rewrite['slug'] : '—', __( 'Permalinks', 'post-author-taxonomy' ) => get_option( 'permalink_structure' ) ?: __( 'Plain permalinks', 'post-author-taxonomy' ), 'deckerweb Library' => $library_version, 'deckerweb Updater' => $update_version, 'Bricks' => defined( 'BRICKS_VERSION' ) ? BRICKS_VERSION : __( 'Optional; not active', 'post-author-taxonomy' ) );
		foreach ( $items as $label => $value ) { echo '<dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd>'; }
		echo '</dl><form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post"><input type="hidden" name="action" value="pat_refresh_archives">';
		wp_nonce_field( 'pat_refresh_archives' );
		echo '<button type="submit" class="button">' . esc_html__( 'Refresh author archive rules', 'post-author-taxonomy' ) . '</button></form><p class="description">' . esc_html__( 'Use this once if author archive links return a 404. It preserves authors, posts and settings.', 'post-author-taxonomy' ) . '</p></details></section>';
	}
	/** Explicitly invalidate only this site's archive rules after an administrator request. @return void Redirects back to the existing settings page. */
	public function refresh_archives(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You cannot refresh archive rules.', 'post-author-taxonomy' ), '', array( 'response' => 403 ) ); }
		check_admin_referer( 'pat_refresh_archives' );
		update_option( 'pat_flush_rewrite', 1, true ); DDW_PAT_Lifecycle::refresh_rewrites();
		wp_safe_redirect( admin_url( 'options-general.php?page=post-author-taxonomy&pat_archives=refreshed' ) ); exit;
	}
}
