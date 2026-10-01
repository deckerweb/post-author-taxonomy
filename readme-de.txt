=== Post Author Taxonomy ===
Contributors: deckerweb
Tags: authors, taxonomy, multiple authors, guest authors
Requires at least: 6.7
Tested up to: 7.1.2
Requires PHP: 8.0
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Autoren wie Tags. Gestalte mit deinem Builder. Ein kleines WordPress-Plugin für mehrere Autorenangaben über die öffentliche Taxonomie `pat-author`. Autoren benötigen kein WordPress-Benutzerkonto.


Version: 1.3.0 · Voraussetzungen: WordPress 6.7+ / PHP 8.0+ · Lizenz: GPL v2 oder höher

Download (https://github.com/deckerweb/post-author-taxonomy/releases/latest) · Anleitung (https://github.com/deckerweb/post-author-taxonomy/wiki/Deutsch) · English (https://github.com/deckerweb/post-author-taxonomy/blob/master/README.md)

== Inhaltsverzeichnis ==

- In drei Schritten starten
- Shortcodes
- Builder- und Template-Beispiele
- Filter für Entwickler
- Updates und deckerweb Library
- Snippet-Version
- Hinweise beim Wechsel von 1.2.0
- Häufige Fragen
- Unterstützung und Lizenz
- Änderungsverlauf

Neue PHP-Mindestversion: Version 1.2.0 benötigte PHP 7.4. Version 1.3.0 enthält die deckerweb Library und benötigt deshalb PHP 8.0.

== In drei Schritten starten ==

1. ZIP über Plugins → Installieren → Plugin hochladen installieren.
2. Einstellungen → Post Author Taxonomy → Autoren verwalten öffnen. Name, Biografie (Taxonomie-Beschreibung), optional Foto aus der Mediathek und Website hinterlegen; Autoren einem Beitrag zuweisen.
3. `[pat-authors]` oder `[pat-author-boxes]` im Inhalt oder Builder-Template einfügen.

Standardmäßig sind Beiträge aktiviert. Seiten und öffentliche eigene Inhaltstypen lassen sich in den Einstellungen ergänzen. Bestehende Autoren und Zuweisungen bleiben erhalten. Das Plugin ersetzt weder `post_author` noch Theme-Autorenzeilen, Benutzerkonten, Bearbeitungsrechte oder Autoren-Schema von SEO-Plugins.

== Shortcodes ==

[pat-authors]
[pat-authors link="none" before="Geschrieben von:" after="."]
[pat-authors link="website" post_id="42"]
[pat-author-boxes]
[pat-author-box slug="jane-doe"]
[pat-author-box id="21" photo="no" website="no"]


`[pat-author-box]` ohne Auswahl zeigt den aktuellen Autor nur auf einem Taxonomie-Archiv von `pat-author`. Auf normalen Seiten und Beiträgen bleibt die Ausgabe leer. Eine ausdrückliche Autorenauswahl funktioniert auf jeder Seite. Vorrang: `id`, dann `slug`, dann `name`. Eine ungültige angegebene ID führt nicht zum Ausweichen auf einen anderen Autor.

`[pat-author-boxes]` zeigt alle Autoren des aktuellen Beitrags oder von `post_id`. Die Reihenfolge folgt WordPress, normalerweise nach Namen. Eine individuelle Reihenfolge pro Beitrag ist nicht enthalten.

| Attribut | Shortcodes | Standard / Verhalten |
| --- | --- | --- |
| `before`, `after`, `sep` | `pat-authors` | `Autoren:`, leer, `, `; begrenzte Inline-Formatierung |
| `link` | alle | Liste: Einstellung, zunächst `archive`. Boxen: `none`. Optionen: `archive`, `website`, `none` |
| `post_id` | `pat-authors`, `pat-author-boxes` | Aktueller Beitrag |
| `id`, `slug`, `name` | `pat-author-box` | Leer; Auswahl eines Autors |
| `title`, `headline`, `title_tag` | Boxen | `yes`, leer, `h4`; eigene Überschrift ersetzt den Namen |
| `photo`, `website` | Boxen | `yes`; mit `no` ausblenden |
| `content_tag` | Boxen | `p`; Biografie als reiner Text |
| `class`, `wrapper` | alle | Eigene Klassen leer; Liste: `span`, Box: `div` |

Listen-Wrapper/Inhaltselemente: `div`, `span`, `p`, `section`, `article`, `aside`. Box-Wrapper: `div`, `section`, `article`, `aside`. Überschriften: `h2`–`h6`, `p`, `div`, `span`. Nicht erlaubte Elemente fallen auf den Standard zurück. Namen und Überschriften werden escaped. Labels erlauben nur `span`, `strong`, `em`, `b`, `i`, `br`; anderes HTML wird entfernt. Im Website-Modus erscheinen Autoren ohne Website unverlinkt.

Das Plugin lädt kein CSS oder JavaScript im Frontend. Das Layout gestaltet dein Theme oder Builder. Stabile Klassen: `pat-authors`, `pat-author-box`, `pat-author-box__photo`, `pat-author-box__title`, `pat-author-box__content`, `pat-author-box__website`. Fotos verwenden responsive WordPress-Bildausgabe.

== Builder- und Template-Beispiele ==

Bricks: Shortcode-Element mit `[pat-author-boxes]` im Beitrags-Template platzieren. `{post_terms_pat-author}` kann die Taxonomie weiterhin nativ ausgeben. Für Autoren ein `pat-author`-Archiv-Template anlegen; darin `[pat-author-box]` für das Profil und eine normale Archiv-Abfrage für Beiträge nutzen.

Breakdance / andere Builder: Shortcode im entsprechenden Element einfügen. Die öffentliche Taxonomie steht für normale Taxonomie-Abfragen zur Verfügung. Profil-Metafelder: `pat_photo_id` (Medien-ID) und `pat_website` (URL). Builder-spezifische Integrationen wurden in dieser Version nicht vorausgesetzt oder getestet.

Block-Editor: Shortcode-Block verwenden. Der Core-Block für Beitragstaxonomien kann die registrierte Taxonomie darstellen. Kein eigener Plugin-Block enthalten.

Klassische PHP-Templates:
<?php echo do_shortcode( '[pat-author-boxes]' ); ?>


== Filter für Entwickler ==

Bestehende Filter bleiben: `pat/taxonomy/params`, `pat/shortcode/authors-list-defaults`, `pat/shortcode/authors-list`, `pat/shortcode/author-box-defaults`, `pat/shortcode/author-box`, `pat/plugins-page/tax-link`, `pat/plugins-page/meta-links`.

Neu: `pat/taxonomy/post-types`, `pat/shortcode/author-boxes-defaults`, `pat/shortcode/author-boxes`. Ausgabe-Filter erhalten HTML und normalisierte Attribute. Plugin-eigene Ausgaben werden escaped; Ausgabe-Filter sind vertrauenswürdige PHP-Erweiterungspunkte.

== Updates und deckerweb Library ==

Der integrierte deckerweb GitHub Release Updater V2 liefert WordPress-Updates aus stabilen GitHub-Releases. Ein weiteres Updater-Plugin ist nicht erforderlich. Er fragt Release-Metadaten bei GitHub ab, speichert Antworten zwischen und verwendet lokale sprachabhängige Icons/Banner. Update-Pakete werden vor dem Ersetzen auf Plugin-Identität, angebotene Version und WordPress/PHP-Anforderungen geprüft. Die Live-Installation einer zukünftigen Version wurde noch nicht getestet.

Die eingebundene deckerweb Library 0.2.0 ergänzt einen deckerweb-Tab unter Plugins → Installieren. Mehrere eingebundene Kopien wählen eine gemeinsame Laufzeit. Unter Einstellungen → deckerweb Library lässt sich die Anzeige ausblenden oder ein optionaler Online-Katalog von deckerweb konfigurieren. Dieses Plugin aktiviert keinen Online-Katalog für dich. Library-Installationen benötigen ZipArchive und verwenden freigegebene Release-ZIPs mit Hash-Prüfung. Der eingebettete Katalog enthält nur freigegebene Versionen; eine GitHub-Veröffentlichung ergänzt ihn nicht automatisch.

Autorenfotos liegen in der lokalen Mediathek; keine Gravatar-Anfragen. Beim Deaktivieren oder Entfernen bleiben Autoren, Zuweisungen und Metadaten in der Datenbank. Autorenarchive können während der Deaktivierung nicht erreichbar sein. Keine destruktive Deinstallation enthalten.

== Snippet-Version ==

Die separat erzeugte `.code-snippets.json` oder `.snippet.php` anstelle des Plugins verwenden. Sie enthält Taxonomie und Shortcodes mit denselben Korrekturen; Einstellungen, Medieneditor, Updater und Library sind nicht enthalten. Standard-Inhaltstyp: `post`; Anpassung über `pat/taxonomy/post-types`. Shortcode-Vorgaben über Filter anpassen. Eigene Übersetzungen können unter `wp-content/languages/post-author-taxonomy/` liegen.

== Hinweise beim Wechsel von 1.2.0 ==

Taxonomie-Key, Shortcodes, zentrale CSS-Klassen und übersetzter Archiv-Slug bleiben erhalten. Doppelte `after`-Ausgabe ist korrigiert; ungültige Autoren erzeugen keine Box. Begrenztes Label-HTML und erlaubte Elemente ersetzen uneingeschränktes Markup. Bisherige Labels mit anderem HTML müssen gegebenenfalls angepasst werden. Einzelne Boxen bleiben explizit, außer auf Taxonomie-Archiven. PHP 8.0 wird jetzt benötigt.


== Häufige Fragen ==

Brauchen Autoren ein WordPress-Konto?

Nein. Autorenprofile sind Taxonomie-Begriffe. Sie gewähren weder Zugang noch Bearbeitungsrechte oder Beitragsbesitz.

Kann ein Beitrag mehrere Autoren haben?

Ja. Weise mehrere Autoren zu. [pat-authors] zeigt ihre Namen, [pat-author-boxes] alle zugewiesenen Profile.

Warum bleibt [pat-author-box] auf einem Beitrag leer?

Ohne ausdrückliche Auswahl zeigt dieser Shortcode nur das aktuelle Profil eines pat-author-Taxonomie-Archivs. Nutze [pat-author-boxes] für alle zugewiesenen Autoren oder wähle einen Autor über id, slug oder name.

Wo sind die Gestaltungsoptionen?

Dein Theme oder Builder bestimmt das Layout. Es werden weder Frontend-CSS noch JavaScript geladen. Gestalte die dokumentierten pat-author-box-Klassen im Template oder Stylesheet.

Wie kommen Updates an?

Der eingebettete deckerweb GitHub Release Updater V2 bietet stabile GitHub-Versionen über reguläre WordPress-Plugin-Updates an. Ein separates Updater-Plugin ist nicht nötig.

Was bleibt nach Deaktivierung oder Entfernung?

Autoren, Zuweisungen und Profilinformationen bleiben in der Datenbank. Archive können während der Deaktivierung nicht mehr erreichbar sein. Eine löschende Deinstallation ist nicht enthalten.

Welche WordPress- und PHP-Versionen sind nötig?

WordPress 6.7+ und PHP 8.0+. Die PHP-Mindestversion steigt gegenüber 1.2.0 von 7.4 auf 8.0, weil die Library PHP 8.0 benötigt. Funktionstests liefen mit WordPress 6.7 und 7.1.2 unter PHP 8.4.5.

Alle Fragen nach Themen (https://github.com/deckerweb/post-author-taxonomy/wiki/FAQ%E2%80%90Deutsch)

== Unterstützung und Lizenz ==

Plugin-Website (https://github.com/deckerweb/post-author-taxonomy) · Unterstützen über Ko-fi (https://ko-fi.com/deckerweb) · Newsletter (https://eepurl.com/gbAUUn)

GPL-2.0-or-later. © 2017–2026 David Decker – DECKERWEB. Neue Grafiken als originale SVGs. Historische Versionen verwendeten Remix Icon.

== Changelog ==

= 1.3.0 — 2026-10-01 =

- Neu: automatische Autorenboxen, Archiv-Kontext, lokale Fotos und Websites.
- Neu: Autorenlinks zu Archiv, Website oder reine Namen; post_id-Unterstützung.
- Neu: Inhaltstyp-Einstellungen, kopierbare Beispiele und Header/Footer wie Brand Admin Schemes.
- Neu: gemeinsamer deckerweb Updater V2 und Library 0.2.0.
- Verbessert: erlaubte HTML-Elemente, sichere Ausgabe; deutsche und englische Dokumentation/Grafiken.
- Behoben: Warnungen bei fehlenden Autoren, doppelter Nachtext, mehrere CSS-Klassen und Namenssuche.
- Sonstiges: PHP-Mindestversion jetzt 8.0.
- Sonstiges: Autoren-Taxonomie-Daten, übersetzter Archiv-Slug und vorhandene Erweiterungsfilter.

= 1.2.0 — 2025-04-05 =

- Sonstiges: Klassenbasierter Kern, Autorenbox-Shortcode und mitgelieferte deutsche Übersetzungen.

= 1.1.0 — 2018-09-18 =

- Sonstiges: Interne private Version.

= 1.0.0 — 2017-12-15 =

- Sonstiges: Erste öffentliche Version.

Vollständiger Änderungsverlauf (https://github.com/deckerweb/post-author-taxonomy/wiki/Changelog%E2%80%90Deutsch)
