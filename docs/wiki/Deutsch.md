# Post Author Taxonomy

![Post Author Taxonomy](https://github.com/deckerweb/post-author-taxonomy/raw/master/assets-github/banner-de.png)

**Autoren wie Tags. Gestalte mit deinem Builder.** Ein kleines WordPress-Plugin für mehrere Autorenangaben über die öffentliche Taxonomie `pat-author`. Autoren benötigen kein WordPress-Benutzerkonto.

**Version:** 1.4.1 · **Voraussetzungen:** WordPress 6.7+ / PHP 8.1+ · **Lizenz:** GPL v2 or later

[Download](https://github.com/deckerweb/post-author-taxonomy/releases/latest) · [Anleitung](https://github.com/deckerweb/post-author-taxonomy/wiki/Deutsch) · [English](https://github.com/deckerweb/post-author-taxonomy/wiki/English)

## Inhaltsverzeichnis

- [Auf einen Blick](#auf-einen-blick)
- [In drei Schritten starten](#in-drei-schritten-starten)
- [Shortcode-Konfigurator](#shortcode-konfigurator)
- [Autorenreihenfolge und redaktionelle Rollen](#autorenreihenfolge-und-redaktionelle-rollen)
- [Autorenverwaltung und lokale Diagnose](#autorenverwaltung-und-lokale-diagnose)
- [Dynamische Daten in Bricks](#dynamische-daten-in-bricks)
- [Profilpflege und Komfort](#profilpflege-und-komfort)
- [Shortcodes](#shortcodes)
- [Builder- und Template-Beispiele](#builder--und-template-beispiele)
- [Filter für Entwickler](#filter-fuer-entwickler)
- [Updates und deckerweb Library](#updates-und-deckerweb-library)
- [Snippet-Version](#snippet-version)
- [Hinweise beim Wechsel von 1.2.0](#hinweise-beim-wechsel-von-120)
- [Multisite und Datenhaltung](#multisite-und-datenhaltung)
- [Häufige Fragen](#haeufige-fragen)
- [Änderungsverlauf](#aenderungsverlauf)
- [Über das Plugin](#ueber-das-plugin)
- [Kontakt und Unterstützung](#kontakt-und-unterstuetzung)
- [Lizenz und Herkunft](#lizenz-und-herkunft)

<a name="auf-einen-blick"></a>

## Auf einen Blick

- Shortcode-Konfigurator mit echten Daten; Autorenreihenfolge je Beitrag und optionale redaktionelle Rollen.
- Fotoübersicht und Kopierbuttons in der Autorentabelle; lokale Diagnose; optionale Bricks-Tags.
- Mehrere Autoren ohne WordPress-Benutzerkonten.
- Lokale Fotos, Biografien und Websites in Autorenprofilen.
- Drei flexible Shortcodes und wählbare Linkziele.
- Beiträge, Seiten und öffentliche eigene Inhaltstypen.
- Websitebezogene Einstellungen und Netzwerkaktivierung.
- Reguläre WordPress-Updates und deckerweb Library.

<a name="in-drei-schritten-starten"></a>

## In drei Schritten starten

1. ZIP über Plugins → Installieren → Plugin hochladen installieren.
2. Einstellungen → Post Author Taxonomy → Autoren verwalten öffnen. Name, Biografie (Taxonomie-Beschreibung), optional Foto aus der Mediathek und Website hinterlegen; Autoren einem Beitrag zuweisen.
3. `[pat-authors]` oder `[pat-author-boxes]` im Inhalt oder Builder-Template einfügen.

Standardmäßig sind Beiträge aktiviert. Seiten und öffentliche eigene Inhaltstypen lassen sich in den Einstellungen ergänzen. Bestehende Autoren und Zuweisungen bleiben erhalten. Das Plugin ersetzt weder `post_author` noch Theme-Autorenzeilen, Benutzerkonten, Bearbeitungsrechte oder Autoren-Schema von SEO-Plugins.

<a name="shortcode-konfigurator"></a>

## Shortcode-Konfigurator

Öffne Einstellungen → Post Author Taxonomy → Shortcode-Konfigurator. Wähle Autorennamen, alle Profile oder ein Profil; stelle Verlinkung, optionale Fotos/Websites und redaktionelle Rollen ein. Wähle einen echten Beitrag für die Vorschau. Der aktuelle Kontext liefert einen wiederverwendbaren Template-Shortcode; ein fester Kontext ergänzt `post_id` für die Ausgabe des ausgewählten Beitrags an beliebiger Stelle. Kopiere den erzeugten Shortcode. Das Werkzeug speichert weder Vorgaben noch Inhalte. Die Vorschau nutzt tatsächliche Autorendaten; das endgültige Aussehen bestimmt dein Theme oder Builder.

Die Vorschau-Liste zeigt aktuelle Beiträge. Für einen älteren Beitrag kannst du seine Beitrags-ID eingeben; dieselben Bearbeitungsrechte werden geprüft.

<a name="autorenreihenfolge-und-redaktionelle-rollen"></a>

## Autorenreihenfolge und redaktionelle Rollen

Ordne Autoren im normalen Autoren-Bereich des Beitragseditors zu. Öffne Autorenreihenfolge und Rollen und klicke auf Zugeordnete Autoren aktualisieren. Ziehe die Zeilen oder verwende die zugänglichen Pfeiltasten; wähle Autor, Redaktionell geprüft von oder Übersetzung. Speichere den Beitrag. Neu zugeordnete Autoren werden angehängt; entfernte Zuordnungen erscheinen auch bei einer alten gespeicherten Reihenfolge nicht. Bestehende Beiträge behalten ihre bisherige Reihenfolge, bis du eine Reihenfolge speicherst.

Rollen gehören zur Mitarbeit an diesem Beitrag, nicht zum gemeinsamen Autorenprofil. Sie ändern weder `post_author` noch Bearbeitungsrechte oder SEO-Schema. Die Rollenanzeige ist standardmäßig aus. Mit `[pat-authors roles="yes"]` oder `[pat-author-boxes roles="yes"]` erscheinen die Bezeichnungen. `role="reviewer"`, `role="translator"` oder `role="author"` filtert Listen und Profilgruppen. Reguläre Shortcodes zeigen weiterhin alle zugeordneten Autoren.

Falls der Blockeditor den Bereich Meta-Boxen zugeklappt hat, vergrößere ihn über die Trennleiste; dort funktionieren auch die Pfeiltasten nach oben/unten. Unterstützt der Inhaltstyp Revisionen, wird die Mitarbeit in nativen Beitragsrevisionen mitgesichert.

<a name="autorenverwaltung-und-lokale-diagnose"></a>

## Autorenverwaltung und lokale Diagnose

Die Autorentabelle zeigt das lokale Foto und vorhandene Biografie-/Website-Angaben sowie einen Kopierbutton für den individuellen Autoren-Shortcode. Optionale Profildetails bleiben freiwillig.

Unter Einstellungen → Post Author Taxonomy → Lokale Diagnose stehen Plugin-/Komponentenversionen, aktivierte Inhaltstypen, Archivbasis, Permalink-Modus und der optionale Bricks-Status. Dabei gibt es keine externe Anfrage und keine persönlichen Autoreninhalte. Regeln für Autorenarchive erneuern ist eine ausdrückliche Administratoraktion bei Archiv-404s; Inhalte und Einstellungen bleiben erhalten.

<a name="dynamische-daten-in-bricks"></a>

## Dynamische Daten in Bricks

Bei aktivem Bricks enthält die öffentliche Auswahl dynamischer Daten eine Gruppe Post Author Taxonomy: `{pat_authors}`, `{pat_author_boxes}`, `{pat_author_name}`, `{pat_author_photo}`, `{pat_author_website}`. Das erste Profil folgt der gespeicherten Reihenfolge des aktuellen Beitrags; auf Autorenarchiven verwenden die einzelnen Felder den aktuellen Autor. Der Bild-Tag liefert im Bildkontext eine lokale Anhang-ID, damit Bricks Größen und srcset bereitstellen kann. Fremde Tags bleiben unverändert. Bricks-Code und Frontend-Stile werden nicht mitgeliefert. Reguläre Shortcodes funktionieren auch in anderen Buildern.

<a name="profilpflege-und-komfort"></a>

## Profilpflege und Komfort

Foto-Auswahl merkt sich das aktuelle Bild. Auswahl und Entfernung werden verständlich bestätigt; ohne Foto bleibt Entfernen deaktiviert. Profiländerungen werden erst beim Speichern übernommen. Kopiermeldungen sind für Screenreader zugänglich; bei gesperrter Zwischenablage wird der Shortcode zum Kopieren markiert. Die Einstellungsseite weist auf ungespeicherte Änderungen hin.

<a name="shortcodes"></a>

## Shortcodes

```text
[pat-authors]
[pat-authors link="none" before="Geschrieben von:" after="."]
[pat-authors link="website" post_id="42"]
[pat-author-boxes]
[pat-author-box slug="jane-doe"]
[pat-author-box id="21" photo="no" website="no"]
```

`[pat-author-box]` ohne Auswahl zeigt den aktuellen Autor **nur auf einem Taxonomie-Archiv von `pat-author`**. Auf normalen Seiten und Beiträgen bleibt die Ausgabe leer. Eine ausdrückliche Autorenauswahl funktioniert auf jeder Seite. Vorrang: `id`, dann `slug`, dann `name`. Eine ungültige angegebene ID führt nicht zum Ausweichen auf einen anderen Autor.

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

<a name="builder--und-template-beispiele"></a>

## Builder- und Template-Beispiele

**Bricks:** Shortcode-Element mit `[pat-author-boxes]` im Beitrags-Template platzieren. `{post_terms_pat-author}` kann die Taxonomie weiterhin nativ ausgeben. Für Autoren ein `pat-author`-Archiv-Template anlegen; darin `[pat-author-box]` für das Profil und eine normale Archiv-Abfrage für Beiträge nutzen.

**Breakdance / andere Builder:** Shortcode im entsprechenden Element einfügen. Die öffentliche Taxonomie steht für normale Taxonomie-Abfragen zur Verfügung. Profil-Metafelder: `pat_photo_id` (Medien-ID) und `pat_website` (URL). Builder-spezifische Integrationen wurden in dieser Version nicht vorausgesetzt oder getestet.

**Block-Editor:** Shortcode-Block verwenden. Der Core-Block für Beitragstaxonomien kann die registrierte Taxonomie darstellen. Kein eigener Plugin-Block enthalten.

**Klassische PHP-Templates:**
```php
<?php echo do_shortcode( '[pat-author-boxes]' ); ?>
```

<a name="filter-fuer-entwickler"></a>

## Filter für Entwickler

Bestehende Filter bleiben: `pat/taxonomy/params`, `pat/shortcode/authors-list-defaults`, `pat/shortcode/authors-list`, `pat/shortcode/author-box-defaults`, `pat/shortcode/author-box`, `pat/plugins-page/tax-link`, `pat/plugins-page/meta-links`.

Neu: `pat/taxonomy/post-types`, `pat/shortcode/author-boxes-defaults`, `pat/shortcode/author-boxes`. Ausgabe-Filter erhalten HTML und normalisierte Attribute. Plugin-eigene Ausgaben werden escaped; Ausgabe-Filter sind vertrauenswürdige PHP-Erweiterungspunkte.

<a name="updates-und-deckerweb-library"></a>

## Updates und deckerweb Library

Der integrierte **deckerweb Updater 2.2.0** liefert stabile GitHub-Versionen über reguläre WordPress-Plugin-Updates. Das Plugin benötigt keinen GitHub-Token. Paketidentität, Angebotsversion, Voraussetzungen und lokale Bootstrap-Dateien werden vor dem Austausch geprüft, auch bei Sammelupdates.

Die eingebundene **deckerweb Library 1.1.0** ergänzt den deckerweb-Tab unter Plugins → Installieren. Mehrere Hosts verwenden eine gemeinsame kompatible Laufzeit. Einstellungen → deckerweb Library verwaltet Anzeige, freiwilligen Online-Katalog und die optionale Löschung ihrer Einstellungen beim letzten Host. Der Online-Katalog ist zunächst ausgeschaltet. Ein bestehender Online-Katalog-Schalter bleibt erhalten. Installationen verwenden ausdrücklich freigegebene Pakete und geprüfte SHA256-Prüfsummen; Voraussetzungen werden nicht automatisch installiert.

Autorenfotos liegen lokal in der Mediathek. Der Updater fragt api.github.com nach Release-Metadaten; Downloads erfolgen über GitHub und dessen Download-Infrastruktur. Der freiwillige Katalog fragt nur die freigegebene deckerweb-Quelle ab. Externe Server erhalten die üblichen Verbindungsdaten, etwa IP-Adresse und HTTP-Clientkennung; Autoreninhalte und Zugangsdaten werden nicht übertragen. Keine Telemetrie und keine externen Avatar-Anfragen.

<a name="snippet-version"></a>

## Snippet-Version

Die historischen Snippet-Dateien der veröffentlichten [Version 1.3.0](https://github.com/deckerweb/post-author-taxonomy/releases/tag/v1.3.0) bleiben verfügbar. Sie enthalten nur Taxonomie und Shortcodes, ohne Einstellungen, Medieneditor, Updater oder Library. Verwende sie anstelle des installierten Plugins. Der reguläre Lieferweg ist das Plugin-ZIP.

<a name="hinweise-beim-wechsel-von-120"></a>

## Hinweise beim Wechsel von 1.2.0

Taxonomie-Key, Shortcodes, zentrale CSS-Klassen und übersetzter Archiv-Slug bleiben erhalten. Doppelte `after`-Ausgabe ist korrigiert; ungültige Autoren erzeugen keine Box. Begrenztes Label-HTML und erlaubte Elemente ersetzen uneingeschränktes Markup. Bisherige Labels mit anderem HTML müssen gegebenenfalls angepasst werden. Einzelne Boxen bleiben explizit, außer auf Taxonomie-Archiven. PHP 8.1 wird jetzt benötigt.

<a name="multisite-und-datenhaltung"></a>

## Multisite und Datenhaltung

Einstellungen, Autoren, Zuweisungen, Fotos und Websites gelten jeweils für die einzelne Website. Bei Netzwerkaktivierung steht das Plugin auf allen Websites bereit; die Einstellungsseite bleibt im jeweiligen Website-Admin. Neue Websites erhalten die normale Beitrags-Vorgabe. Archivregeln werden bei Bedarf nach Registrierung der Website-Sprache aktualisiert.

Deaktivierung erhält alle Inhalte und Einstellungen. Deinstallation entfernt nur temporäre Update-/Rewrite-Caches und führt die gemeinsame Library-Bereinigung aus. Autoren, Zuweisungen, Metadaten und `pat_settings` bleiben erhalten. Die Library bewahrt gemeinsame Daten, solange weitere Hosts installiert sind, auch deaktivierte. Ihre optionale Einstellungslöschung betrifft nur den letzten Host; installierte Plugins und ihre Inhalte bleiben erhalten.

Die optionale Autorenreihenfolge und Rollen liegen im Post-Meta-Feld `pat_attribution` und bleiben bei Deaktivierung/Deinstallation beim Beitrag erhalten.

<a name="haeufige-fragen"></a>

## Häufige Fragen

**Brauchen Autoren ein WordPress-Konto?**

Nein. Autorenprofile sind Taxonomie-Begriffe. Sie gewähren weder Zugang noch Bearbeitungsrechte oder Beitragsbesitz.

**Kann ein Beitrag mehrere Autoren haben?**

Ja. Weise mehrere Autoren zu. [pat-authors] zeigt ihre Namen, [pat-author-boxes] alle zugewiesenen Profile.

**Wie lege ich die Autorenreihenfolge fest?**

Ordne Autoren im Beitragseditor wie gewohnt zu und öffne Autorenreihenfolge und Rollen → Zugeordnete Autoren aktualisieren. Ziehe die Zeilen oder verwende die Pfeiltasten und speichere den Beitrag. Die Reihenfolge gilt nur für diesen Beitrag. Neue Autoren werden angehängt; alte entfernte Zuordnungen werden ignoriert.

**Ändern redaktionelle Rollen Rechte oder SEO-Schema?**

Nein. Autor, Redaktionell geprüft von und Übersetzung sind Angaben zur Mitarbeit je Beitrag. Sie ändern weder Zugang noch Bearbeitungsrechte, post_author oder SEO-Schema. Zeige sie ausdrücklich mit roles="yes" an und filtere Gruppen mit role="reviewer", role="translator" oder role="author".

**Speichert der Konfigurator Einstellungen?**

Nein. Er erzeugt einen Shortcode und eine Vorschau mit echten Daten. Der aktuelle Kontext ist in Templates wiederverwendbar; der feste Kontext enthält post_id. Für die Vorschau von Entwürfen oder privaten Beiträgen brauchst du Bearbeitungsrechte für den ausgewählten Beitrag.

**Was bleibt nach Deaktivierung oder Entfernung?**

Autoren, Zuweisungen, Profilinformationen und Einstellungen bleiben erhalten. Temporäre Caches werden bei Deinstallation entfernt; die Library prüft, ob weitere Hosts installiert sind. Archive sind während der Deaktivierung gegebenenfalls nicht erreichbar.

**Welche WordPress- und PHP-Versionen sind nötig?**

WordPress 6.7+ und PHP 8.1+. Die PHP-Mindestversion steigt gegenüber 1.2.0 von 7.4 auf 8.0, weil die Library PHP 8.1 benötigt. Funktionstests liefen mit WordPress 6.7 und 7.1.2 unter PHP 8.4.5.

[Alle Fragen nach Themen](https://github.com/deckerweb/post-author-taxonomy/wiki/FAQ%E2%80%90Deutsch)

<a name="aenderungsverlauf"></a>

## Änderungsverlauf

### 1.4.1 — 2026-10-21

- **Verbessert:** deckerweb Library 1.1.0 und Updater 2.2.0 mit freigegebenen Katalog-Updates und vollständigen deutschen Übersetzungen.
- **Behoben:** Changelog-Dialoge bleiben bis zum Öffnen geschlossen; natives Schließen und Dokumentationslinks bleiben als Rückfall verfügbar.
- **Sonstiges:** benötigt PHP 8.1 oder höher; WordPress 6.7 wird weiterhin unterstützt.

### 1.4.0 — 2026-10-10

- **Neu:** Shortcode-Konfigurator mit echter Beitragsvorschau und Kopieren; Autorenreihenfolge je Beitrag und optionale redaktionelle Rollen.
- **Neu:** Profilübersicht und individuelles Shortcode-Kopieren in der Autorenverwaltung, lokale Archivdiagnose und optionale dynamische Bricks-Tags.
- **Verbessert:** Admin-Meldungen erscheinen unter dem vollständigen Seitenkopf; Titel und Slogan bleiben zusammen.
- **Verbessert:** Vorauswahl des aktuellen Fotos, zugängliche Kopier-/Fotomeldungen, Hinweis auf ungespeicherte Einstellungen und strukturierter übersetzter Changelog.
- **Verbessert:** deckerweb Library 0.9.0, Updater 2.1.0, synchronisierte zweisprachige Dokumentation und vollständige deutsche Du-/Sie-Übersetzungen.
- **Verbessert:** Site-/Netzwerk-Lebenszyklus und datenbewahrende Deinstallation; gezielte Admin-Arbeit und bedarfsgerechte Archivregeln.
- **Behoben:** Bildrechteprüfung über REST vor Profiländerungen; teilweise übermittelte Formulare erhalten fehlende Profilfelder; Paketprüfung bei nativen Stapel-Updates.
- **Sonstiges:** neue Collective-Grafiken mit zentriertem Icon, nativen Vektorquellen und lizenzierten Schriftpfaden aus Space Grotesk; Daten und Mindestanforderungen erhalten.

### 1.3.0 — 2026-10-01

- **Neu:** automatische Autorenboxen, Archiv-Kontext, lokale Fotos und Websites.
- **Neu:** Autorenlinks zu Archiv, Website oder reine Namen; post_id-Unterstützung.
- **Neu:** Inhaltstyp-Einstellungen, kopierbare Beispiele und Header/Footer wie Brand Admin Schemes.
- **Neu:** gemeinsamer deckerweb Updater V2 und Library 0.2.0.
- **Verbessert:** erlaubte HTML-Elemente, sichere Ausgabe; deutsche und englische Dokumentation/Grafiken.
- **Behoben:** Warnungen bei fehlenden Autoren, doppelter Nachtext, mehrere CSS-Klassen und Namenssuche.
- **Sonstiges:** PHP-Mindestversion jetzt 8.0.
- **Sonstiges:** Autoren-Taxonomie-Daten, übersetzter Archiv-Slug und vorhandene Erweiterungsfilter.

### 1.2.0 — 2025-04-05

- **Sonstiges:** Klassenbasierter Kern, Autorenbox-Shortcode und mitgelieferte deutsche Übersetzungen.

### 1.0.0 — 2017-12-15

- **Sonstiges:** Erste öffentliche Version.

[Vollständiger Änderungsverlauf](https://github.com/deckerweb/post-author-taxonomy/wiki/Changelog%E2%80%90Deutsch)

<a name="ueber-das-plugin"></a>

## Über das Plugin

Entwickelt und herausgegeben von David Decker – DECKERWEB. Seit 2017 für Autorenangaben über eine öffentliche Taxonomie; keine Benutzerkontenverwaltung und kein eigenes Frontend-Design.

<a name="kontakt-und-unterstuetzung"></a>

## Kontakt und Unterstützung

[Issues](https://github.com/deckerweb/post-author-taxonomy/issues) · [Security / Sicherheit](https://github.com/deckerweb/post-author-taxonomy/blob/master/SECURITY.md) · [Ko-fi](https://ko-fi.com/deckerweb) · [Buy Me a Coffee](https://buymeacoffee.com/daveshine) · [PayPal](https://paypal.me/deckerweb)

<a name="lizenz-und-herkunft"></a>

## Lizenz und Herkunft

© 2017–2026 David Decker – DECKERWEB · GPL v2 or later.

[Grafiken und eingebundene Komponenten](https://github.com/deckerweb/post-author-taxonomy/blob/master/docs/ASSETS-de.md)
