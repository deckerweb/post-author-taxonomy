<?php
/**
 * Read-only release history from the documentation shipped with this installation.
 *
 * @package PostAuthorTaxonomy
 */

namespace Deckerweb\PostAuthorTaxonomy;

defined( 'ABSPATH' ) || exit;

/** Render trusted local filenames as escaped, structured changelog content. */
final class Changelog {
	/**
	 * Resolve the documentation language from the current WordPress admin locale.
	 *
	 * @return bool Whether to use the German documentation.
	 */
	public static function is_german(): bool {
		return 1 === preg_match( '/^de(?:_|$)/i', determine_locale() );
	}

	/**
	 * Local documentation URL, also used when dialogs or JavaScript are unavailable.
	 *
	 * @return string Changelog URL.
	 */
	public static function url(): string {
		return plugins_url( self::is_german() ? 'docs/changelog-de.txt' : 'docs/changelog.txt', PAT_PLUGIN_FILE );
	}

	/**
	 * Extract only the changelog section; never interpret readme text as HTML.
	 *
	 * @return string Escaped headings and lists, or an empty string if unavailable.
	 */
	public static function content(): string {
		$file = PAT_PLUGIN_DIR . ( self::is_german() ? 'docs/changelog-de.txt' : 'docs/changelog.txt' );
		if ( ! is_readable( $file ) || filesize( $file ) > 262144 ) {
			return '';
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Read one of two fixed local documentation files; no user-supplied paths or remote requests.
		$text = file_get_contents( $file );
		if ( false === $text || ! preg_match( '/^== Changelog ==\s*\R(.*?)(?=^== [^\r\n]+ ==\s*$|\z)/ms', $text, $section ) ) {
			return '';
		}
		$html = '';
		$list = false;
		foreach ( preg_split( '/\R/', trim( $section[1] ) ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( preg_match( '/^= (.+) =$/', $line, $heading ) ) {
				$parts = preg_split( '/\s+—\s+/', $heading[1], 2 );
				$html .= ( $list ? '</ul>' : '' ) . '<section class="pat-changelog-release"><h3>' . esc_html( $parts[0] ) . '</h3>';
				if ( isset( $parts[1] ) ) {
					$html .= '<p class="pat-changelog-date">' . esc_html( $parts[1] ) . '</p>';
				}
				// The section is closed immediately; the following list remains a semantic sibling.
				$html .= '</section>';
				$list  = false;
			} elseif ( str_starts_with( $line, '* ' ) ) {
				$entry = substr( $line, 2 );
				$classes = array( 'New' => 'new', 'Neu' => 'new', 'Improved' => 'improved', 'Verbessert' => 'improved', 'Fixed' => 'fixed', 'Behoben' => 'fixed', 'Misc' => 'misc', 'Sonstiges' => 'misc' );
				if ( preg_match( '/^(New|Neu|Improved|Verbessert|Fixed|Behoben|Misc|Sonstiges):\s*(.*)$/u', $entry, $category ) ) {
					$entry = '<span class="pat-changelog-badge pat-changelog-' . esc_attr( $classes[ $category[1] ] ) . '">' . esc_html( $category[1] . ':' ) . '</span> ' . esc_html( $category[2] );
				} else {
					$entry = esc_html( $entry );
				}
				$html .= ( $list ? '' : '<ul>' ) . '<li>' . $entry . '</li>';
				$list  = true;
			} else {
				$html .= ( $list ? '</ul>' : '' ) . '<p>' . esc_html( $line ) . '</p>';
				$list  = false;
			}
		}
		return $html . ( $list ? '</ul>' : '' );
	}

	/**
	 * Dialog markup; the footer link remains usable if content cannot be read.
	 *
	 * @return void
	 */
	public static function dialog(): void {
		$content = self::content();
		if ( '' === $content ) {
			return;
		}
		$close = __( 'Close', 'post-author-taxonomy' );
		echo '<dialog id="pat-document-changelog" class="pat-document-dialog" hidden aria-labelledby="pat-document-changelog-title"><header class="pat-document-header"><h2 id="pat-document-changelog-title">Post Author Taxonomy · ' . esc_html__( 'Changelog', 'post-author-taxonomy' ) . '</h2><form method="dialog"><button type="submit" class="button" data-pat-close autofocus>' . esc_html( $close ) . '</button></form></header><div class="pat-document-content" tabindex="0">';
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- content() escapes every text node; only fixed heading, list and paragraph markup is emitted.
		echo '</div><p class="pat-document-source"><a href="' . esc_url( self::url() ) . '">' . esc_html( self::is_german() ? 'docs/changelog-de.txt' : 'docs/changelog.txt' ) . '</a></p></dialog>';
	}
}
