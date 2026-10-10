<?php
/** Per-post attribution order and optional editorial roles, without changing term assignments. */
defined( 'ABSPATH' ) || exit;

/** Store small post-local attribution records and provide one ordered rendering source. */
final class DDW_PAT_Attribution {
	public const META = 'pat_attribution';
	/** Attach metadata and editor hooks after the host taxonomy exists. @return void */
	public function register(): void {
		add_action( 'init', array( $this, 'meta' ), 105 );
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'boxes' ) );
			add_action( 'save_post', array( $this, 'save' ), 100, 2 );
			add_action( 'wp_ajax_pat_attribution_rows', array( $this, 'refresh' ) );
		}
	}
	/** Return the fixed editorial vocabulary translated at output time. @return array<string,string> Role keys and labels. */
	public static function roles(): array {
		return array( 'author' => __( 'Author', 'post-author-taxonomy' ), 'reviewer' => __( 'Reviewed by', 'post-author-taxonomy' ), 'translator' => __( 'Translation', 'post-author-taxonomy' ) );
	}
	/** Register optional REST-editable metadata for configured content types. @return void */
	public function meta(): void {
		$tax = get_taxonomy( 'pat-author' );
		foreach ( $tax ? $tax->object_type : array() as $type ) {
			register_post_meta( $type, self::META, array(
				'type' => 'array', 'single' => true, 'default' => array(), 'revisions_enabled' => post_type_supports( $type, 'revisions' ),
				'sanitize_callback' => array( __CLASS__, 'sanitize' ), 'auth_callback' => array( $this, 'authorize' ),
				'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'maxItems' => 500, 'items' => array( 'type' => 'object', 'additionalProperties' => false, 'required' => array( 'id', 'role' ), 'properties' => array( 'id' => array( 'type' => 'integer', 'minimum' => 1 ), 'role' => array( 'type' => 'string', 'enum' => array_keys( self::roles() ) ) ) ) ) ),
			) );
		}
	}
	/**
	 * Authorize a metadata mutation through this post's native permissions.
	 * @param bool $allowed Existing metadata authorization result.
	 * @param string $key Metadata key under consideration.
	 * @param int $post_id Post whose metadata would change.
	 * @return bool Whether the current user can edit this post and assign its author terms.
	 */
	public function authorize( $allowed, $key, $post_id ): bool {
		$tax = get_taxonomy( 'pat-author' );
		return $tax && current_user_can( 'edit_post', (int) $post_id ) && current_user_can( $tax->cap->assign_terms );
	}
	/**
	 * Normalize unique positive IDs and known role keys, with a bounded payload.
	 * @param mixed $value Submitted ordered attribution records.
	 * @return array<array{id:int,role:string}> Valid records in submitted order.
	 */
	public static function sanitize( $value ): array {
		if ( ! is_array( $value ) ) { return array(); }
		$result = array(); $seen = array(); $roles = self::roles();
		foreach ( array_slice( $value, 0, 500 ) as $item ) {
			if ( ! is_array( $item ) || ! is_scalar( $item['id'] ?? null ) || ! is_string( $item['role'] ?? null ) ) { continue; }
			$id = absint( $item['id'] );
			if ( ! $id || isset( $seen[ $id ] ) || ! isset( $roles[ $item['role'] ] ) ) { continue; }
			$result[] = array( 'id' => $id, 'role' => $item['role'] ); $seen[ $id ] = true;
		}
		return $result;
	}
	/**
	 * Resolve the role for one author in one post without granting ownership or permissions.
	 * @param int $post_id Attribution context post.
	 * @param int $term_id Author term.
	 * @return string Saved role, or author as the non-breaking default.
	 */
	public static function role( int $post_id, int $term_id ): string {
		return self::role_map( $post_id )[ $term_id ] ?? 'author';
	}
	/**
	 * Read normalized role keys once for a whole list without repeated record scans.
	 * @param int $post_id Attribution context post.
	 * @return array<int,string> Term IDs mapped to known role keys.
	 */
	public static function role_map( int $post_id ): array {
		return array_column( self::sanitize( get_post_meta( $post_id, self::META, true ) ), 'role', 'id' );
	}

	/**
	 * Apply saved order only to real current assignments, then append newly assigned authors.
	 * @param int $post_id Post whose assigned terms are requested.
	 * @param string $role Optional role filter; an empty value keeps every assigned author.
	 * @return array<WP_Term> Ordered assigned terms, or an empty array for errors/unknown roles.
	 */
	public static function terms( int $post_id, string $role = '' ): array {
		$terms = get_the_terms( $post_id, 'pat-author' );
		if ( ! is_array( $terms ) || ( '' !== $role && ! isset( self::roles()[ $role ] ) ) ) { return array(); }
		$remaining = array(); foreach ( $terms as $term ) { $remaining[ $term->term_id ] = $term; }
		$ordered = array();
		foreach ( self::sanitize( get_post_meta( $post_id, self::META, true ) ) as $item ) {
			if ( isset( $remaining[ $item['id'] ] ) ) { $ordered[] = $remaining[ $item['id'] ]; unset( $remaining[ $item['id'] ] ); }
		}
		$ordered = array_merge( $ordered, array_values( $remaining ) );
		if ( '' !== $role ) { $role_map = self::role_map( $post_id ); $ordered = array_values( array_filter( $ordered, static fn( $term ) => ( $role_map[ $term->term_id ] ?? 'author' ) === $role ) ); }
		return $ordered;
	}
	/**
	 * Add an editor panel only where the taxonomy is enabled and editable.
	 * @param string $post_type Current editor's registered post type.
	 * @return void
	 */
	public function boxes( $post_type ): void {
		$tax = get_taxonomy( 'pat-author' );
		if ( $tax && in_array( $post_type, $tax->object_type, true ) && current_user_can( $tax->cap->assign_terms ) ) {
			add_meta_box( 'pat-attribution', __( 'Author order and roles', 'post-author-taxonomy' ), array( $this, 'box' ), $post_type, 'normal', 'default', array( '__block_editor_compatible_meta_box' => true ) );
		}
	}
	/**
	 * Render draggable records with equivalent keyboard buttons and explicit persistence.
	 * @param WP_Post $post Post being edited.
	 * @return void
	 */
	public function box( $post ): void {
		wp_nonce_field( 'pat_save_attribution_' . $post->ID, 'pat_attribution_nonce' );
		$value = self::sanitize( get_post_meta( $post->ID, self::META, true ) );
		echo '<div class="pat-attribution" data-post="' . esc_attr( $post->ID ) . '"><p>' . esc_html__( 'Assign authors in the normal Authors panel, then refresh this list. Drag rows or use the arrow buttons. Order and roles apply only to this post after saving.', 'post-author-taxonomy' ) . '</p><input type="hidden" name="pat_attribution_data" class="pat-attribution-data" value="' . esc_attr( wp_json_encode( $value ) ) . '"><ul class="pat-attribution-list">';
		echo self::rows( self::terms( $post->ID ), $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rows() escapes each value into fixed markup.
		echo '</ul><button type="button" class="button pat-refresh-authors">' . esc_html__( 'Refresh assigned authors', 'post-author-taxonomy' ) . '</button><p class="description">' . esc_html__( 'Roles are optional attribution labels. They do not change WordPress permissions or SEO schema. Use roles="yes" in a shortcode to show them.', 'post-author-taxonomy' ) . '</p><p class="pat-attribution-status" role="status" aria-live="polite"></p></div>';
	}
	/**
	 * Render escaped editor rows for selected authors.
	 * @param array<WP_Term> $terms Author terms selected in the editor.
	 * @param int $post_id Existing saved role context.
	 * @return string Fixed row markup with escaped names and attributes.
	 */
	public static function rows( array $terms, int $post_id ): string {
		if ( ! $terms ) { return '<li class="pat-attribution-empty">' . esc_html__( 'No authors assigned yet.', 'post-author-taxonomy' ) . '</li>'; }
		$html = ''; $role_map = self::role_map( $post_id ); $role_labels = self::roles();
		foreach ( $terms as $term ) {
			$html .= '<li class="pat-attribution-row" draggable="true" data-id="' . esc_attr( $term->term_id ) . '"><span class="pat-drag-handle" aria-hidden="true">⋮⋮</span><strong>' . esc_html( $term->name ) . '</strong><label><span class="screen-reader-text">' . esc_html__( 'Role', 'post-author-taxonomy' ) . ': ' . esc_html( $term->name ) . '</span><select class="pat-role">';
			foreach ( $role_labels as $key => $label ) { $html .= '<option value="' . esc_attr( $key ) . '"' . selected( $role_map[ $term->term_id ] ?? 'author', $key, false ) . '>' . esc_html( $label ) . '</option>'; }
			$html .= '</select></label><button type="button" class="button pat-move-up" aria-label="' . esc_attr( __( 'Move up', 'post-author-taxonomy' ) . ': ' . $term->name ) . '">↑</button><button type="button" class="button pat-move-down" aria-label="' . esc_attr( __( 'Move down', 'post-author-taxonomy' ) . ': ' . $term->name ) . '">↓</button></li>';
		}
		return $html;
	}
	/**
	 * Save submitted panel records after native post/term permissions and an object-specific nonce.
	 * @param int $post_id Post being saved.
	 * @param WP_Post $post Current post object.
	 * @return void Missing/invalid panels, autosaves and revisions preserve existing data.
	 */
	public function save( $post_id, $post ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! $this->authorize( false, self::META, $post_id ) ) { return; }
		if ( ! is_string( $_POST['pat_attribution_nonce'] ?? null ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pat_attribution_nonce'] ) ), 'pat_save_attribution_' . $post_id ) || ! is_string( $_POST['pat_attribution_data'] ?? null ) || strlen( $_POST['pat_attribution_data'] ) > 65536 ) { return; }
		$value = json_decode( wp_unslash( $_POST['pat_attribution_data'] ), true );
		if ( ! is_array( $value ) || ! is_object_in_taxonomy( $post->post_type, 'pat-author' ) ) { return; }
		$assigned = array_map( static fn( $term ) => $term->term_id, self::terms( $post_id ) );
		$value = array_values( array_filter( self::sanitize( $value ), static fn( $item ) => in_array( $item['id'], $assigned, true ) ) );
		update_post_meta( $post_id, self::META, $value );
	}
	/** Return current editor selections without mutating terms or post data. @return void Sends a scoped AJAX response. */
	public function refresh(): void {
		check_ajax_referer( 'pat_editor', 'nonce' );
		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! $this->authorize( false, self::META, $post_id ) || ! is_object_in_taxonomy( get_post_type( $post_id ), 'pat-author' ) ) { wp_send_json_error( array( 'message' => __( 'You cannot edit authors for this post.', 'post-author-taxonomy' ) ), 403 ); }
		$ids = isset( $_POST['ids'] ) && is_string( $_POST['ids'] ) ? json_decode( wp_unslash( $_POST['ids'] ), true ) : null;
		$names = isset( $_POST['names'] ) && is_string( $_POST['names'] ) ? explode( ',', wp_unslash( $_POST['names'] ) ) : array();
		$terms = array();
		if ( is_array( $ids ) ) {
			foreach ( array_slice( $ids, 0, 500 ) as $id ) { if ( is_scalar( $id ) ) { $term = get_term( absint( $id ), 'pat-author' ); if ( $term instanceof WP_Term ) { $terms[ $term->term_id ] = $term; } } }
		} elseif ( $names ) {
			foreach ( array_slice( $names, 0, 500 ) as $name ) { $term = get_term_by( 'name', trim( sanitize_text_field( $name ) ), 'pat-author' ); if ( $term instanceof WP_Term ) { $terms[ $term->term_id ] = $term; } }
		} else { $terms = self::terms( $post_id ); }
		wp_send_json_success( array( 'html' => self::rows( array_values( $terms ), $post_id ) ) );
	}
}
