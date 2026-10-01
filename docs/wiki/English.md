# Post Author Taxonomy

**Authors as tags. Designed in your builder.** A small WordPress plugin for multiple author attribution using the public `pat-author` taxonomy. Authors do not need WordPress user accounts.

![Post Author Taxonomy](https://github.com/deckerweb/post-author-taxonomy/raw/master/assets/banner-1544x500.png)

**Version:** 1.3.0 · **Requires:** WordPress 6.7+ / PHP 8.0+ · **License:** GPL v2 or later

[Download](https://github.com/deckerweb/post-author-taxonomy/releases/latest) · [User guide](https://github.com/deckerweb/post-author-taxonomy/wiki/English) · [Deutsch](https://github.com/deckerweb/post-author-taxonomy/wiki/Deutsch)

## Contents

- [Start in three steps](#start-in-three-steps)
- [Shortcodes](#shortcodes)
- [Builder and template recipes](#builder-and-template-recipes)
- [Developer filters](#developer-filters)
- [Updates and deckerweb Library](#updates-and-deckerweb-library)
- [Snippet edition](#snippet-edition)
- [Compatibility notes for 1.2.0 users](#compatibility-notes-for-120-users)
- [FAQ](#faq)
- [Support and license](#support-and-license)
- [Changelog](#changelog)

**PHP minimum changed:** version 1.2.0 required PHP 7.4. Version 1.3.0 includes the deckerweb Library, which requires PHP 8.0.

<a name="start-in-three-steps"></a>

## Start in three steps

1. Install the ZIP via Plugins → Add New → Upload Plugin.
2. Open Settings → Post Author Taxonomy → Manage authors. Add a name, a biography (term description), optional local photo and website; assign authors to a post.
3. Put `[pat-authors]` or `[pat-author-boxes]` in the content or your builder template.

Posts are enabled by default. Enable pages and public custom post types on the settings page. Existing authors and assignments are kept. This plugin does not replace `post_author`, native theme bylines, user accounts, editing permissions or SEO-plugin author schema.

<a name="shortcodes"></a>

## Shortcodes

```text
[pat-authors]
[pat-authors link="none" before="Written by:" after="."]
[pat-authors link="website" post_id="42"]
[pat-author-boxes]
[pat-author-box slug="jane-doe"]
[pat-author-box id="21" photo="no" website="no"]
```

`[pat-author-box]` without a selector displays the queried author **only on a `pat-author` taxonomy archive**. On an ordinary page or post it returns empty output. Explicit selectors work on any page. Selection precedence is `id`, then `slug`, then `name`; an invalid explicit ID does not fall through to a different selector.

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

<a name="builder-and-template-recipes"></a>

## Builder and template recipes

**Bricks:** place a Shortcode element with `[pat-author-boxes]` in your single-post template. Native `{post_terms_pat-author}` can still render the taxonomy. Create a `pat-author` archive template; use `[pat-author-box]` for the current profile and a normal archive post query.

**Breakdance / other builders:** put the shortcode in the corresponding Shortcode element. The public taxonomy is available to normal taxonomy queries; profile metadata is `pat_photo_id` (attachment ID) and `pat_website` (URL). No builder-specific integration has been assumed or tested in this release.

**Block editor:** use a Shortcode block. Core Post Terms can show the registered taxonomy. No custom plugin block is bundled.

**Classic PHP templates:**
```php
<?php echo do_shortcode( '[pat-author-boxes]' ); ?>
```

<a name="developer-filters"></a>

## Developer filters

Existing filters remain: `pat/taxonomy/params`, `pat/shortcode/authors-list-defaults`, `pat/shortcode/authors-list`, `pat/shortcode/author-box-defaults`, `pat/shortcode/author-box`, `pat/plugins-page/tax-link`, `pat/plugins-page/meta-links`.

New: `pat/taxonomy/post-types`, `pat/shortcode/author-boxes-defaults`, `pat/shortcode/author-boxes`. Output filters receive the rendered HTML and normalized attributes. Plugin callbacks escape their own output; output filters are trusted PHP extension points.

<a name="updates-and-deckerweb-library"></a>

## Updates and deckerweb Library

The embedded deckerweb GitHub Release Updater V2 supplies updates through WordPress from stable GitHub releases. No additional updater plugin is required. It checks release metadata on GitHub, caches responses and uses localized local icons/banners. Update packages are checked for plugin identity, offered version and WordPress/PHP requirements before replacement. Live installation of a future release has not been tested.

The bundled **deckerweb Library 0.2.0** adds a deckerweb tab to Plugins → Add New. Multiple embedded copies elect one runtime. Settings → deckerweb Library lets you hide discovery or configure its optional first-party online catalog. This plugin does not enable an online catalog on your behalf. Library installs require ZipArchive and use approved, hash-verified release ZIPs. The embedded catalog includes only approved releases; GitHub publication alone does not add an entry.

Author photos are local media files; no Gravatar requests. Removing/deactivating the plugin leaves author terms, assignments and metadata in the database. Taxonomy archives may stop resolving while it is inactive. No destructive uninstall routine is provided.

<a name="snippet-edition"></a>

## Snippet edition

Use the separately generated `.code-snippets.json` or `.snippet.php` **instead of** the installed plugin. It includes the taxonomy and shortcodes, with the same fixes; it excludes the admin settings, media editor, updater and Library. Default post type: `post`; configure via `pat/taxonomy/post-types`. Register settings defaults via shortcode filters. Custom translations may be placed in `wp-content/languages/post-author-taxonomy/`.

<a name="compatibility-notes-for-120-users"></a>

## Compatibility notes for 1.2.0 users

The taxonomy key, shortcodes, primary CSS classes and translated archive slug remain. The duplicate `after` output is fixed; invalid authors now produce no box. Limited label HTML and approved tags replace unrestricted markup. Existing custom labels using other HTML may need adaptation. Singular author boxes remain explicit except on taxonomy archives. PHP 8.0 is now required.


<a name="faq"></a>

## FAQ

**Do authors need a WordPress account?**

No. Author profiles are taxonomy terms. They do not grant login access, editing permissions or ownership of posts.

**Can a post have several authors?**

Yes. Assign several terms in the author taxonomy. [pat-authors] displays their names and [pat-author-boxes] displays all assigned profiles.

**Why is [pat-author-box] empty on a normal post?**

Without an explicit selector it shows only the current pat-author taxonomy archive profile. Use [pat-author-boxes] for a post’s assigned authors or select one author with id, slug or name.

**Where are the styling options?**

Your theme or builder owns the layout. No frontend stylesheet or JavaScript is loaded. Style the documented pat-author-box and child classes in your template or stylesheet.

**How do updates arrive?**

The embedded deckerweb GitHub Release Updater V2 offers stable GitHub releases through regular WordPress plugin updates. No separate updater plugin is needed.

**What remains after deactivation or removal?**

Author terms, assignments and profile metadata remain in the database. Archives may stop resolving while the plugin is inactive. There is no destructive uninstall routine.

**Which WordPress and PHP versions are required?**

WordPress 6.7+ and PHP 8.0+. The minimum PHP version increased from 7.4 in version 1.2.0 because the bundled Library requires PHP 8.0. Functional tests used WordPress 6.7 and 7.1.2 with PHP 8.4.5.

[All questions by topic](https://github.com/deckerweb/post-author-taxonomy/wiki/FAQ%E2%80%90English)

<a name="support-and-license"></a>

## Support and license

[Plugin website](https://github.com/deckerweb/post-author-taxonomy) · [Support via Ko-fi](https://ko-fi.com/deckerweb) · [Newsletter](https://eepurl.com/gbAUUn)

GPL-2.0-or-later. © 2017–2026 David Decker – DECKERWEB. New artwork is original SVG. Historical releases used Remix Icon graphics.

<a name="changelog"></a>

## Changelog

### 1.3.0 — 2026-10-01

- **New:** automatic author boxes, archive context, local photo and website fields.
- **New:** archive, website or plain author links; post_id support.
- **New:** post-type settings, copyable recipes and Brand Admin Schemes-style header/footer.
- **New:** shared deckerweb Updater V2 and Library 0.2.0.
- **Improved:** bounded HTML elements and escaped output; German and English documentation/artwork.
- **Fixed:** missing-term warnings, duplicate suffix, multiple CSS classes and premature name escaping.
- **Misc:** minimum PHP version is now 8.0.
- **Misc:** author taxonomy data, translated rewrite slug and existing extension hooks.

### 1.2.0 — 2025-04-05

- **Misc:** Class-based core, author box shortcode and bundled German translations.

### 1.1.0 — 2018-09-18

- **Misc:** Internal private release.

### 1.0.0 — 2017-12-15

- **Misc:** Initial public release.

[Complete changelog](https://github.com/deckerweb/post-author-taxonomy/wiki/Changelog%E2%80%90English)
