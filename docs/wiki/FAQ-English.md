# FAQ by topic · Post Author Taxonomy

[Deutsch](https://github.com/deckerweb/post-author-taxonomy/wiki/FAQ%E2%80%90Deutsch)

## Getting started

### Do authors need a WordPress account?

No. Author profiles are taxonomy terms. They do not grant login access, editing permissions or ownership of posts.

### Can a post have several authors?

Yes. Assign several terms in the author taxonomy. [pat-authors] displays their names and [pat-author-boxes] displays all assigned profiles.

### Does this replace the theme’s author line?

No. Add a shortcode to your content or template and configure your theme’s native byline separately.

### Can I use authors on pages or custom post types?

Yes. Enable public post types under Settings → Post Author Taxonomy. Attachments are excluded. Posts are enabled by default.

## Profiles and archives

### Where do I edit the biography, photo and website?

Open Manage authors from the settings page. Use the term description for the biography, choose a local media-library image and enter an optional HTTP/HTTPS website URL.

### Are photos fetched from Gravatar?

No. Photos are local WordPress attachments. The plugin does not request external avatars.

### Why is [pat-author-box] empty on a normal post?

Without an explicit selector it shows only the current pat-author taxonomy archive profile. Use [pat-author-boxes] for a post’s assigned authors or select one author with id, slug or name.

### What if an explicit author ID is invalid?

The box is empty. ID takes precedence over slug and name; an invalid ID does not silently select a different profile.

### Can I order authors individually per post?

No. Terms follow the order returned by WordPress, normally name order. Individual co-author ordering is not included.

### Does an author archive list posts?

Yes, through a normal theme or builder archive template. The profile shortcode adds the profile; the theme’s archive query lists associated content.

### What is the archive URL?

The taxonomy key stays pat-author. The default archive slug is post-author in English and beitragsautor on a German site. It follows the site language, not an administrator’s personal language. Existing taxonomy filters can customize registration.

## Shortcodes and layout

### Which link targets are available?

Use link="archive", link="website" or link="none". Lists use the saved default; boxes default to none. A missing website produces a plain name in website mode.

### Can I display authors from another post?

Yes. Use post_id with [pat-authors] or [pat-author-boxes], for example [pat-author-boxes post_id="42"].

### Can I hide photos or website links?

Yes. Use photo="no" or website="no" on either box shortcode. These attributes do not delete profile metadata.

### Can I use HTML in names or biographies?

Names and headlines are escaped; biographies are plain text. List labels permit limited inline formatting. Wrapper and heading tags are restricted to documented elements.

### Where are the styling options?

Your theme or builder owns the layout. No frontend stylesheet or JavaScript is loaded. Style the documented pat-author-box and child classes in your template or stylesheet.

### How do I set author order?

In the post editor, assign authors normally, then use Author order and roles → Refresh assigned authors. Drag rows or use the arrows and save the post. The order applies only to that post. Newly assigned authors append; old removed assignments are ignored.

### Do editorial roles change access or SEO schema?

No. Author, Reviewed by and Translation are attribution labels per post. They do not change login access, editing permissions, post_author or SEO schema. Show them explicitly with roles="yes", and filter groups with role="reviewer", role="translator" or role="author".

### Does the configurator save settings?

No. It creates a shortcode and a real-data preview. Current context is reusable in templates; fixed context includes post_id. Previewing drafts/private posts requires permission to edit the selected post.

### Which Bricks dynamic tags are available?

The optional Post Author Taxonomy group supplies pat_authors, pat_author_boxes, pat_author_name, pat_author_photo and pat_author_website. Put braces around a tag in Bricks. The first profile follows the current post order; photo returns an attachment-ID array in image context. Other builders can use the unchanged shortcodes.

### How do I repair author archive 404s?

Check Settings → Post Author Taxonomy → Local diagnostics, then use Refresh author archive rules once. The action requires an administrator and does not delete authors, settings or posts. If the problem remains, inspect permalink and theme/routing conflicts.

## Builders and integration

### Does it work with Bricks, Breakdance or the block editor?

Use a Shortcode element or block. The taxonomy is public and available to normal taxonomy queries. Commercial builders were not separately tested; there is no dedicated builder extension.

### Does it add SEO author schema?

No. Configure structured data in your SEO plugin or template. Taxonomy authors do not automatically become WordPress users or schema persons.

### Is the snippet edition equivalent to the plugin?

It shares the taxonomy and shortcode core but omits settings, the media editor, updater and Library. Use it instead of the plugin. Configure post types and defaults through PHP filters.

## Updates and data

### How do updates arrive?

The embedded deckerweb GitHub Release Updater V2 offers stable GitHub releases through regular WordPress plugin updates. No separate updater plugin is needed.

### What does the deckerweb Library do?

It adds a deckerweb discovery tab under Plugins → Add New. Shared copies elect one runtime. Its optional first-party online catalog is configurable in Settings → deckerweb Library; this plugin does not enable it automatically.

### What remains after deactivation or removal?

Authors, assignments, profile metadata and settings remain. Uninstall removes temporary caches; Library cleanup checks whether other hosts are still installed. Archives may stop resolving while the plugin is inactive.

### Which WordPress and PHP versions are required?

WordPress 6.7+ and PHP 8.1+. The minimum PHP version increased from 7.4 in version 1.2.0 because the bundled Library requires PHP 8.1. Functional tests used WordPress 6.7 and 7.1.2 with PHP 8.4.5.

### Is ClassicPress officially supported?

No. Compatibility is welcome but ClassicPress is not officially supported or tested.

### What should I check when upgrading from 1.2.0?

Author data, taxonomy key and extension hooks remain. Review custom label HTML and wrapper tags because output is now restricted. The after suffix is printed once. PHP 8.1 is required.

### Can I activate it across a network?

Settings, authors, assignments, photos and websites belong to each individual site. Network activation makes the plugin available across the network; its settings remain in each site's admin. New sites receive the normal Posts default. Archive rules refresh when needed after registering in the site's language.

### Which information is sent to external servers?

Author photos stay in the local media library. The updater requests release metadata from api.github.com; packages use GitHub and its download infrastructure. The optional catalog contacts only its approved deckerweb source. External servers receive normal connection information such as IP address and HTTP client identification; author content and credentials are not sent. No telemetry or external avatar requests.

### How do I report a security vulnerability?

Use private GitHub vulnerability reporting in the host repository, including Library issues. Supply versions, reproduction and impact without credentials. If private reporting is unavailable, do not disclose security details in public issues or discussions.

### Why are plugin updates paused?

If another plugin loaded an older shared updater first, Post Author Taxonomy shows a notice and pauses its new integration. Update the other deckerweb host to a compatible updater version. Author features remain available; other files and updater hooks are not rewritten.

## Technical details

### Who can change author photos?

Profile editing requires permission to edit the author term. A selected photo must be an image the editor can edit in the local media library. The same checks apply through the REST API. Omitting a photo or website from a partial profile form keeps the saved value; submitting zero or an empty website explicitly clears it.

[User guide](https://github.com/deckerweb/post-author-taxonomy/wiki/English)
