# Post Author Taxonomy

**Simple & lightweight:** Registers a simple post authors taxonomy for the regular Posts post type. The taxonomy is not hierarchical, so it's similar to tags but for authors instead. Shortcodes for a list of authors and an Author Box are included.

![Post Author Taxonomy plugin banner](https://repository-images.githubusercontent.com/114375288/882286e7-ebfc-45da-b2a2-9aa0e3b0d758)

### Tested Compatibility
- **WordPress**: 6.7.2 / 6.8 Beta
- **PHP**: 8.0 – 8.3
- Requires at least: WP 6.7 / PHP 7.4

---

[Support Project](#support-the-project) | [Installation](#installation) | [Description](#description) | [Features](#features) | [Usage](#usage---examples) | [Shortcode Parameters](#shortcode-parameters) | [Widgets](#widget-usage) | [Templates](#template-usage-developers) | [Filters](#plugin-filters-developers) | [Changelog](#changelog--version-history) | [Plugin Scope / Disclaimer](#plugin-scope--disclaimer)

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

(content upcoming)

---

## Widget Usage

NO LONGER recommended! Widgets are outdated, I do not recommend them (and _didn't_ use them myself for lots of years already!).

Shortcode could be used with "Text" widget -- if you have Shortcodes for Widgets activated. Possible via this filter:
```
add_filter( 'widget_text', 'do_shortcode' );
```
If using extended/ advanced text widget plugins, the Shortcode usage then is already enabled automatically... :-)

---

## Template Usage (Developers)

Use WordPress' global `do_shortcode()` function as a template function, like so:
```
<?php do_shortcode( '[pat-authors]' ); ?>
```
--> parameters apply like for regular Shortcode usage (see above)!

---

## Plugin Filters (Developers)

* `pat/filter/taxonomy/params` --> filter all Taxonomy parameters
* `pat/filter/shortcode/authors_list_defaults` --> filter authors list Shortcode defaults
* `pat/filter/shortcode/authors-list` --> filter authors list Shortcode output
* `pat/filter/shortcode/author_box_defaults` --> filter author box Shortcode defaults
* `pat/filter/shortcode/author-box` --> filter author box Shortcode output

---

## Changelog – Version History

### 🎉 v1.2.0 – 2025-03-??
* Updated plugin after 8 years, yeah! – Brought back to its basic beauty. (How it should be!)
* New: Transformed code into class-based approach (more future-proof)
* New: Shortcode `pat-author-box` for displaying a simple author box (content = term description)
* New: Alternate install: Use "plugin" as Code Snippet version – now officially promoted here in Readme and with downloadable `.json` file
* Improved: Shortcode `pat-authors` enhanced
* Change: Simplified additional translation loading (needed, for proper usage/display, as this plugin comes not from .org repository)
* Plugin: Add meta links on WP Plugins page
* Updated `.pot` file, plus packaged German translations


### 🎉 v1.1.0 – 2018-09-18
* Unreleased _private_ version


### 🎉 v1.0.0 – 2017-12-15
* Initial _public_ release on GitHub

---

Icon used in promo graphics: © Remix Icon

Copyright: © 2017-2025, David Decker – DECKERWEB.de
