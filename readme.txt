=== Post Author Taxonomy ===
Contributors: deckerweb
Tags: authors, taxonomy, multiple authors, guest authors
Requires at least: 6.7
Tested up to: 7.1.3
Requires PHP: 8.1
Stable tag: 1.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Authors as a public taxonomy: multiple authors, local profiles and flexible shortcodes.

== Description ==

Authors as tags. Designed in your builder. A small WordPress plugin for multiple author attribution using the public `pat-author` taxonomy. Authors do not need WordPress user accounts.

Version: 1.4.2 · Requires: WordPress 6.7+ / PHP 8.1+ · License: GPL v2 or later

Download (https://github.com/deckerweb/post-author-taxonomy/releases/latest) · User guide (https://github.com/deckerweb/post-author-taxonomy/wiki/English) · Deutsch (https://github.com/deckerweb/post-author-taxonomy/blob/master/README-de.md)

== At a glance ==

- Real-data shortcode configurator; per-post author order and optional editorial roles.
- Author-table photo overview and copy buttons; local diagnostics; optional Bricks tags.
- Several authors without WordPress user accounts.
- Local photos, biographies and author websites.
- Three flexible shortcodes and selectable link targets.
- Posts, pages and public custom post types.
- Site-level settings and network activation.
- Regular WordPress updates and deckerweb Library.

== Installation ==

1. Install the ZIP via Plugins → Add New → Upload Plugin.
2. Open Settings → Post Author Taxonomy → Manage authors. Add a name, a biography (term description), optional local photo and website; assign authors to a post.
3. Put `[pat-authors]` or `[pat-author-boxes]` in the content or your builder template.

Posts are enabled by default. Enable pages and public custom post types on the settings page. Existing authors and assignments are kept. This plugin does not replace `post_author`, native theme bylines, user accounts, editing permissions or SEO-plugin author schema.

== Shortcode configurator ==

Open Settings → Post Author Taxonomy → Shortcode configurator. Choose author names, all profiles or one profile; set name links, optional photos/websites and editorial roles. Select a real post for the preview. Current context produces a reusable template shortcode; fixed context adds `post_id` for output from the selected post everywhere. Copy the generated shortcode. The tool does not save defaults or content. The preview uses actual author data; the final appearance remains controlled by your theme or builder.

The preview list shows recent posts. Enter a Post ID to use an older post; the same edit permissions are checked.

== Author order and editorial roles ==

In the post editor, assign authors in the normal Authors panel. Open Author order and roles, then Refresh assigned authors. Drag the rows or use the accessible arrow buttons; choose Author, Reviewed by or Translation for each author. Save the post. Newly assigned authors are appended; removed assignments are never rendered from an old saved order. Existing posts retain their original order until an order is saved.

Roles belong to the attribution on that post, not to the shared author profile. They do not change `post_author`, editing permissions or SEO schema. Role output is off by default. Use `[pat-authors roles="yes"]` or `[pat-author-boxes roles="yes"]` to show labels. Use `role="reviewer"`, `role="translator"` or `role="author"` to filter lists and profile groups. Regular shortcodes keep all assigned authors.

If the block editor collapses its Meta boxes area, expand it with its resize separator; the up/down arrow keys work there too. Attribution metadata is included in native post revisions when the post type supports revisions.

== Author management and local diagnostics ==

The Authors table shows a local photo and available biography/website details, plus a copy button for the individual author shortcode. Optional profile details are never required.

Settings → Post Author Taxonomy → Local diagnostics shows the plugin/component versions, enabled post types, archive base, permalink mode and optional Bricks status. It makes no external request and shows no personal author content. Refresh author archive rules is an explicit administrator action for archive 404s; it keeps all content and settings.

== Bricks dynamic data ==

When Bricks is active, the public dynamic-data picker has a Post Author Taxonomy group: `{pat_authors}`, `{pat_author_boxes}`, `{pat_author_name}`, `{pat_author_photo}`, `{pat_author_website}`. The first profile follows the saved order for the current post; individual fields use the current author on author archives. The image tag returns a local attachment ID in image context so Bricks can supply sizes/srcset. Foreign tags remain untouched. No Bricks code or frontend styles are bundled. The regular shortcodes also work with other builders.

== Profile editing and convenience ==

Photo selection remembers the current image. Selection and removal receive clear feedback; Remove stays disabled without a photo. Profile changes apply only after saving. Copy feedback is accessible to screen readers; a blocked clipboard selects the shortcode for manual copying. Settings show a reminder when changes have not been saved.

== Shortcodes ==

[pat-authors]
[pat-authors link="none" before="Written by:" after="."]
[pat-authors link="website" post_id="42"]
[pat-author-boxes]
[pat-author-box slug="jane-doe"]
[pat-author-box id="21" photo="no" website="no"]


`[pat-author-box]` without a selector displays the queried author only on a `pat-author` taxonomy archive. On an ordinary page or post it returns empty output. Explicit selectors work on any page. Selection precedence is `id`, then `slug`, then `name`; an invalid explicit ID does not fall through to a different selector.

`[pat-author-boxes]` displays all authors assigned to the current post, or to `post_id`. Terms follow the order provided by WordPress (normally name order). No custom per-post author ordering is introduced.

| Attribute | Shortcodes | Default / behavior |
| --- | --- | --- |
| `before`, `after`, `sep` | `pat-authors` | `Authors:`, empty, `, `; limited inline formatting only |
| `link` | all | List: setting (`archive` by default). Boxes: `none`. Options: `archive`, `website`, `none` |
| `post_id` | `pat-authors`, `pat-author-boxes` | Current post |
| `id`, `slug`, `name` | `pat-author-box` | Empty; choose one author |
| `title`, `headline`, `title_tag` | boxes | `yes`, empty, `h4`; headline overrides author name |
| `photo`, `website` | boxes | `yes`; use `no` to hide |
| `content_tag` | boxes | `p`; biography is plain text |
| `class`, `wrapper` | all | Empty custom classes; list: `span`, box: `div` |

List wrappers/content elements: `div`, `span`, `p`, `section`, `article`, `aside`. Box wrappers: `div`, `section`, `article`, `aside`. Title elements: `h2`–`h6`, `p`, `div`, `span`. Unapproved elements fall back to defaults. Names and headlines are escaped. Labels allow only `span`, `strong`, `em`, `b`, `i`, `br`; other HTML is removed. Website mode falls back to plain names when no URL is set.

No front-end CSS or JavaScript is loaded: the theme/builder controls the presentation. Stable classes: `pat-authors`, `pat-author-box`, `pat-author-box__photo`, `pat-author-box__title`, `pat-author-box__content`, `pat-author-box__website`. Optional photos use responsive WordPress attachment markup.

== Builder and template recipes ==

Bricks: place a Shortcode element with `[pat-author-boxes]` in your single-post template. Native `{post_terms_pat-author}` can still render the taxonomy. Create a `pat-author` archive template; use `[pat-author-box]` for the current profile and a normal archive post query.

Breakdance / other builders: put the shortcode in the corresponding Shortcode element. The public taxonomy is available to normal taxonomy queries; profile metadata is `pat_photo_id` (attachment ID) and `pat_website` (URL). No builder-specific integration has been assumed or tested in this release.

Block editor: use a Shortcode block. Core Post Terms can show the registered taxonomy. No custom plugin block is bundled.

Classic PHP templates:
<?php echo do_shortcode( '[pat-author-boxes]' ); ?>


== Developer filters ==

Existing filters remain: `pat/taxonomy/params`, `pat/shortcode/authors-list-defaults`, `pat/shortcode/authors-list`, `pat/shortcode/author-box-defaults`, `pat/shortcode/author-box`, `pat/plugins-page/tax-link`, `pat/plugins-page/meta-links`.

New: `pat/taxonomy/post-types`, `pat/shortcode/author-boxes-defaults`, `pat/shortcode/author-boxes`. Output filters receive the rendered HTML and normalized attributes. Plugin callbacks escape their own output; output filters are trusted PHP extension points.

== Updates and deckerweb Library ==

The embedded deckerweb Updater 2.2.0 supplies stable GitHub releases through regular WordPress plugin updates. This public plugin needs no GitHub token. Package identity, offered version, requirements and local bootstrap includes are checked before replacement, including bulk updates.

The bundled deckerweb Library 1.1.0 adds the deckerweb tab to Plugins → Add New. Several hosts elect one compatible runtime. Settings → deckerweb Library controls discovery, the optional online catalog and optional settings deletion when the final host is removed. The online catalog starts disabled; an existing opt-in is retained. Installations use explicitly approved packages with verified SHA256 digests; prerequisites are not automatically installed.

Author photos stay in the local media library. The updater requests release metadata from api.github.com; packages use GitHub and its download infrastructure. The optional catalog contacts only its approved deckerweb source. External servers receive normal connection information such as IP address and HTTP client identification; author content and credentials are not sent. No telemetry or external avatar requests.

== Snippet edition ==

The historical snippet exports of published version 1.3.0 (https://github.com/deckerweb/post-author-taxonomy/releases/tag/v1.3.0) remain available. They contain taxonomy and shortcodes only, without settings, media editor, updater or Library. Use them instead of the installed plugin. The regular distribution is the plugin ZIP.

== Compatibility notes for 1.2.0 users ==

The taxonomy key, shortcodes, primary CSS classes and translated archive slug remain. The duplicate `after` output is fixed; invalid authors now produce no box. Limited label HTML and approved tags replace unrestricted markup. Existing custom labels using other HTML may need adaptation. Singular author boxes remain explicit except on taxonomy archives. PHP 8.1 is now required.

== Multisite and data ==

Settings, authors, assignments, photos and websites belong to each individual site. Network activation makes the plugin available across the network; its settings remain in each site's admin. New sites receive the normal Posts default. Archive rules refresh when needed after registering in the site's language.

Deactivation preserves all content and settings. Uninstall removes only temporary update/rewrite caches and runs shared Library cleanup. Author terms, assignments, metadata and `pat_settings` remain. The Library preserves shared data while other hosts are installed, including inactive hosts. Its optional settings deletion applies only to the final host; installed plugins and their content remain.

The optional author order/roles are stored in the post meta key `pat_attribution` and retained with the post on deactivation/uninstall.

== Frequently Asked Questions ==

= Do authors need a WordPress account? =

No. Author profiles are taxonomy terms. They do not grant login access, editing permissions or ownership of posts.

= Can a post have several authors? =

Yes. Assign several terms in the author taxonomy. [pat-authors] displays their names and [pat-author-boxes] displays all assigned profiles.

= How do I set author order? =

In the post editor, assign authors normally, then use Author order and roles → Refresh assigned authors. Drag rows or use the arrows and save the post. The order applies only to that post. Newly assigned authors append; old removed assignments are ignored.

= Do editorial roles change access or SEO schema? =

No. Author, Reviewed by and Translation are attribution labels per post. They do not change login access, editing permissions, post_author or SEO schema. Show them explicitly with roles="yes", and filter groups with role="reviewer", role="translator" or role="author".

= Does the configurator save settings? =

No. It creates a shortcode and a real-data preview. Current context is reusable in templates; fixed context includes post_id. Previewing drafts/private posts requires permission to edit the selected post.

= What remains after deactivation or removal? =

Authors, assignments, profile metadata and settings remain. Uninstall removes temporary caches; Library cleanup checks whether other hosts are still installed. Archives may stop resolving while the plugin is inactive.

= Which WordPress and PHP versions are required? =

WordPress 6.7+ and PHP 8.1+. The minimum PHP version increased from 7.4 in version 1.2.0 because the bundled Library requires PHP 8.1. Functional tests used WordPress 6.7 and 7.1.2 with PHP 8.4.5.

All questions by topic (https://github.com/deckerweb/post-author-taxonomy/wiki/FAQ%E2%80%90English)

== Changelog ==
= 1.4.2 — 2026-10-22 =
* Fixed: the bundled updater 2.2.1 remains active when another plugin loads an older updater first.
* Improved: release caches are isolated by updater version and repository access mode.
= 1.4.1 — 2026-10-21 =
* Improved: deckerweb Library 1.1.0 and updater 2.2.0 with approved catalog update support and complete German translations.
* Fixed: changelog dialogs remain closed until opened and retain native close and documentation fallbacks.
* Misc: requires PHP 8.1 or later; WordPress 6.7 remains supported.
= 1.4.0 — 2026-10-10 =
* New: shortcode configurator with real-post preview and copy; per-post author order and optional editorial roles.
* New: profile overview and individual shortcode copying in author management, local archive diagnostics and optional Bricks dynamic-data tags.
* Improved: admin notices appear below the complete page header, keeping title and slogan together.
* Improved: current-photo selection, accessible copy/photo feedback, unsaved-settings notice and structured localized changelog.
* Improved: deckerweb Library 0.9.0, updater 2.1.0, synchronized bilingual documentation and complete German/formal German translations.
* Improved: site/network lifecycle and data-preserving uninstall; scoped admin work and conditional rewrite refresh.
* Fixed: REST image permission validation before profile changes; partial forms retain omitted profile fields; native bulk-update package validation.
* Misc: new Collective artwork with centered icon, native vector sources and licensed Space Grotesk glyph paths; existing data and minimum requirements retained.
= 1.3.0 — 2026-10-01 =
* New: automatic author boxes, archive context, local photo and website fields.
* New: archive, website or plain author links; post_id support.
* New: post-type settings, copyable recipes and Brand Admin Schemes-style header/footer.
* New: shared deckerweb Updater V2 and Library 0.2.0.
* Improved: bounded HTML elements and escaped output; German and English documentation/artwork.
* Fixed: missing-term warnings, duplicate suffix, multiple CSS classes and premature name escaping.
* Misc: minimum PHP version is now 8.0.
* Misc: author taxonomy data, translated rewrite slug and existing extension hooks.
= 1.2.0 — 2025-04-05 =
* Misc: Class-based core, author box shortcode and bundled German translations.
= 1.0.0 — 2017-12-15 =
* Misc: Initial public release.

== About the plugin ==

Developed and published by David Decker – DECKERWEB. Public-taxonomy author attribution since 2017, without user-account management or an imposed frontend design.

== Contact and support ==

Issues (https://github.com/deckerweb/post-author-taxonomy/issues) · Security / Sicherheit (https://github.com/deckerweb/post-author-taxonomy/blob/master/SECURITY.md) · Ko-fi (https://ko-fi.com/deckerweb) · Buy Me a Coffee (https://buymeacoffee.com/daveshine) · PayPal (https://paypal.me/deckerweb)

== License and origin ==

© 2017–2026 David Decker – DECKERWEB · GPL v2 or later.

Artwork and bundled components (https://github.com/deckerweb/post-author-taxonomy/blob/master/docs/ASSETS.md)
