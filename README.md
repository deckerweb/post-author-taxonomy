# Post Author Taxonomy

**Simple & lightweight:** Registers a simple post authors taxonomy for the regular Posts post type. The taxonomy is not hierarchical, so it's similar to tags but for authors instead. Shortcodes for a list of authors and an Author Box are included. – It's a perfect drop-in solution. Also works fine as a code snippet.

![Post Author Taxonomy plugin banner](https://repository-images.githubusercontent.com/114375288/882286e7-ebfc-45da-b2a2-9aa0e3b0d758)

* Contributors: [David Decker](https://github.com/deckerweb), [contributors](https://github.com/deckerweb/post-author-taxonomy/graphs/contributors)
* Tags: authors, taxonomy, multiple authors, post, post-type, site builder
* Requires at least: 6.7
* Requires PHP: 7.4
* Stable tag: [master](https://github.com/deckerweb/post-author-taxonomy/releases/latest)
* Donate link: https://paypal.me/deckerweb
* License: GPL v2 or later

---

[Support Project](#support-the-project) | [Installation](#installation) | [Updates](#updates) | [Description](#description) | [Features](#features) | [Usage](#usage---examples) | [Shortcode Parameters](#shortcode-parameters) | [Page Builders](#page-builder-usage) [Block Editor](#block-editor-usage-gutenberg) | [Widgets](#widget-usage) | [Templates](#template-usage-developers) | [Filters](#plugin-filters-developers) | [FAQ](#frequently-asked-questions) | [Changelog](#changelog) | [Plugin Scope / Disclaimer](#plugin-scope--disclaimer)

---

## Support the Project

If you find this project helpful, consider showing your support by buying me a coffee! Your contribution helps me keep developing and improving this plugin.

Enjoying the plugin? Feel free to treat me to a cup of coffee ☕🙂 through the following options:

- [![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/W7W81BNTZE)
- [Buy me a coffee](https://buymeacoffee.com/daveshine)
- [PayPal donation](https://paypal.me/deckerweb)
- [Join my **newsletter** for DECKERWEB WordPress Plugins](https://eepurl.com/gbAUUn)

---

## Installation 

#### **Quick Install – as Plugin**
1. **Download ZIP:** [**post-author-taxonomy.zip**](https://github.com/deckerweb/post-author-taxonomy/releases/latest/download/post-author-taxonomy.zip)
2. Upload via WordPress Plugins > Add New > Upload Plugin
3. Once activated, you can use the shortcode – [see Usage below](#usage---examples)
 
#### **Alternative: Use as Code Snippet**
1. Below, download the appropriate snippet version
2. activate or deactivate in your snippets plugin

[**Download .json**](https://github.com/deckerweb/post-author-taxonomy/releases/latest/download/ddw-post-author-taxonomy.code-snippets.json) version for: _Code Snippets_ (free & Pro), _Advanced Scripts_ (Premium), _Scripts Organizer_ (Premium)  
--> just use their elegant script import features  
--> in _Scripts Organizer_ use the "Code Snippets Import"  

For all other snippet manager plugins just use our plugin's main .php file [`post-author-taxonomy.php`](https://github.com/deckerweb/post-author-taxonomy/blob/master/post-author-taxonomy.php) and use its content as snippet (bevor saving your snippet: please check for your plugin if the opening php tag needs to be removed or not!).

--> Please decide for one of both alternatives!

_Note:_ The snippet version cannot display translations currently. All taxonomy labels will appear in English. We plan on implementing a solution for translated labels also for snippets.

### Tested Compatibility
- **WordPress**: 6.7.2 / 6.8 Beta
- **PHP**: 8.0 – 8.3
- Requires at least: WP 6.7 / PHP 7.4

---

## Updates 

#### For Plugin Version:

1) Alternative 1: Just download a new [ZIP file](https://github.com/deckerweb/post-author-taxonomy/releases/latest/download/post-author-taxonomy.zip) (see above), upload and override existing version. Done.

2) Alternative 2: Use the (free) [**_Git Updater_ plugin**](https://git-updater.com/) and get updates automatically.

3) Alternative 3: Upcoming! – In future I will built-in our own deckerweb updater. This is currently being worked on for my plugins. Stay tuned!

#### For Code Snippet Version:

Just manually: Download the latest Snippet version (see above) and import it in your favorite snippets manager plugin. – You can delete the old snippet; then just activate the new one. Done.

---

## Description 

Very useful to add and display a list of authors to a blog post. If you don't want to use the built-in author, then this plugin/snippet is for you. By default WordPress can only handle one author per post (or post type). Handling multiple authors via a taxonomy is the simplest and most lightweight way to do that. You can then work with that data in your content, templates and your favorite builder.

*Backstory:* I needed something like that for a client project to display a simple list of post authors. The client didn't wanted to add each author as WordPress user therefore the plugin "Co Authors Plus" was not a possible option. Also, the taxonomy solution is simpler, more user friendly and more flexible.

---

## Features 

* By default an `Authors` Taxonomy added for regular WordPress Posts (post type: `post`)
* Shortcode for adding a list of authors, for example in a single post (template) – comma separated list of authors
* Shortcode for adding an author box, for example in a single post (or template) – one author per box
* Can be used with Classic Editor and the Block Editor (Gutenberg)
* Can be used perfectly fine with most Page Builders (Bricks, Breakdance, Oxygen, Elementor etc.) – just use the taxonomy data in your favorite builder's dynamic data feature, you won't need the Shortcodes I guess :-)
* Developer friendly: customize or extend via filters, styles and styling-friendly CSS classes
* Fully internationalized and translateable! -- German translations already packaged!
* Developed with security in mind: proper WordPress coding standards and security functions - escape all the things! :)

---

## Usage - Examples

(content upcoming)

---

## Shortcode Parameters

#### Shortcode: `pat-authors`

| Parameter | Description                                          | Default   | Usage                                                                      |
| --------- | ---------------------------------------------------- | --------- | -------------------------------------------------------------------------- |
| `before`  | label string before authors list                     | Authors:  | Normally the default should be just fine                                   |
| `after`   | label string after authors list                      | „“ (none) | could be needed in edge cases                                              |
| `sep`     | optional separator string                            | ?         | —                                                                          |
| `class`   | additional custom class for the wrapper              | „“ (none) | if ever needed for your styling                                            |
| `wrapper` | HTML wrapper element - any HTML5 wrapper is possible | `span`    | use default or your own tag, fitting your logical, semantic HTML structure |


#### Shortcode: `pat-author-box`

| Parameter      | Description                                                                           | Default                    | Usage                                                                                     |
| -------------- | ------------------------------------------------------------------------------------- | -------------------------- | ----------------------------------------------------------------------------------------- |
| `title`        | The Authors name (aka: name of the taxonomy term, for example: John Doe)              | `yes` (displays term name) | In most cases you would use this default, I guess …                                       |
| `headline`     | Your own „headline“ / title                                                           | „“ (none)                  | For edge cases … –—\> if yiu set a value this WILL BE used instead of the default `title`! |
| `title_tag`   | HTML tag for the title/ headline, typically you would use something between h2 and h6 | `h4`                       | use default or your own tag, fitting your logical, semantic HTML structure                |
| `id`           | ID of the taxonomy term (example: 21)                                                 | „“ (none)                  | set this                                                                                  |
| `slug`         | Slug of the taxonomy term (example: john-doe)                                         | „“ (none)                  | or this                                                                                   |
| `name`         | Name of the taxonomy term (example: John Doe)                                         | „“ (none)                  | or this                                                                                   |
| `content_tag` |                                                                                       | `p`                        | Normally the default will be just fine                                                    |
| `class`        | additional custom class for the wrapper                                               | „“ (none)                  | if ever needed for your styling                                                           |
| `wrapper`      | HTML wrapper element - any HTML5 wrapper is possible                                  | `div`                      | use default or your own tag, fitting your logical, semantic HTML structure                |

---

## Page Builder Usage

Since it is a normal taxonomy you can do tax queries in loops and all the other stuff you can do with tags and categories also. For example builders like Bricks, Oxygen (Classic and Version 6+), Breakdance, Elementor Pro, plus the new Etch work with this stuff by default. Just be happy 😉.

And yes, you can also use our Shortcodes in these builders if you want, just in a Shortcode Element/Widget or in the Richt Text Editor (Elementor, Bricks etc.)

#### Specific for _Bricks Builder_:

In **Bricks** you can also use the built-in _Dynamic Tags_ with our taxonomy!

* {post_terms_pat-author} – list of authors, comma separated, each author linked
* {post_terms_pat-author:plain} – Remove the links around the authors (terms) via :plain filter

The following dynamic data tags render data related to taxonomy terms (authors in our case), so building a Authors taxonomy archive template would open up the usage of these _Dynamic Tags_ in Bricks:
* {term_id} – Renders the term ID
* {term_name} – Renders the term name
* {term_slug} – Renders the term slug
* {term_count} – Renders the term count
* {term_taxonomy_slug} – Renders the term’s taxonomy slug.
* {term_url} – Renders the term archive link
* {term_description}

---

## Block Editor Usage (Gutenberg) 

#### Recommended Way (The New Way) 

Use the default, built-in blocks for displaying Taxonomies. In the block inserter just type in `authors` or just `tax` and you can choose the **Authors block** or the **Terms List block**. These also have styling options by default. Some block plugins/ themes might extend the styling options but you can already do the most important stuff with what WordPress offers by default.

#### The Old Way (Works Perfectly Fine, Still!) 

Place the Shortcode into the default **Shortcode block** or a **regular paragraph block** (yes, that one works just fine, too!).

---

## Widget Usage 

NO LONGER recommended! Widgets are outdated, I do not recommend them (and _didn't_ use them myself for lots of years already!).

Shortcode could be used with **Text widget** – if you have Shortcodes for Widgets activated. Possible via this filter:
```
add_filter( 'widget_text', 'do_shortcode' );
```
If using extended/ advanced text widget plugins, the Shortcode usage then is already enabled automatically... :-)

---

## Template Usage (Developers) 

The use of loops/ queries via modern page builders (Etch, Bricks, Breakdance) or via Block Editor powered Block Themes is recommended. However, you can still use the "old way" via Classic Themes and php/html-based templates. 

Then use WordPress' global `do_shortcode()` function as a template function, like so:

#### Authors List:
```
<?php do_shortcode( '[pat-authors]' ); ?>
```

#### Author Box:
```
<?php do_shortcode( '[pat-author-box]' ); ?>
```

--> parameters apply like for regular Shortcode usage (see above)!

---

## Plugin Filters (Developers)

* `pat/taxonomy/params` --> filter all Taxonomy parameters
* `pat/shortcode/authors-list-defaults` --> filter authors list Shortcode defaults
* `pat/shortcode/authors-list` --> filter authors list Shortcode output
* `pat/shortcode/author-box-defaults` --> filter author box Shortcode defaults
* `pat/shortcode/author-box` --> filter author box Shortcode output

---

## Frequently Asked Questions 

### Why not using just a custom fields plugin?
Of course you can do that. But not every small single site needs such a solution. Sometimes you need a simple & lightweight drop-in solution where you can just do your work without any configuration whatsoever.

### Why did you create this plugin?
Because once I needed it myself for a site I helped maintain. I put it on GitHub to make it available for Git Updater to deliver updates. Later on I liked the solution myself and decided to keep it for the long run.

### Why is this plugin not on wordpress.org plugin repository?
Because the restrictions there for plugin authors are becoming more and more. It would be possible, yes, but I don't want that anymore. The same for limited support forums for plugin authors on .org. I have decided to leave this whole thing behind me.

---

## Changelog 

#### Version History 

### 🎉 v1.2.0 – 2025-04-05
* Updated plugin after 8 years, yeah! – Brought back to its basic beauty. (How it should be!)
* New: Transformed code into class-based approach (more future-proof)
* New: Shortcode `pat-author-box` for displaying a simple author box (content = term description)
* New: Installable and updateable via [Git Updater plugin](https://git-updater.com/)
* New: Alternate install – Use this "plugin" as Code Snippet version – now officially promoted here in Readme and with downloadable `.json` file
* Improved: Shortcode `pat-authors` enhanced
* Improved: Tweaked translation loading (needed, for proper usage/display, as this plugin comes not from .org repository)
* Plugin: Add meta links on WP Plugins page
* Updated `.pot` file, plus packaged German translations, now including new `l10n.php` files!


### 🎉 v1.1.0 – 2018-09-18
* Unreleased _private_ version


### 🎉 v1.0.0 – 2017-12-15
* Initial _public_ release on GitHub

---

Icon used in promo graphics: © Remix Icon

Readme & Plugin Copyright: © 2017-2025, David Decker – DECKERWEB.de