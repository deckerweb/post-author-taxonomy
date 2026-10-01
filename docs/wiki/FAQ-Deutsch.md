# Häufige Fragen · Post Author Taxonomy

## Erste Schritte

### Brauchen Autoren ein WordPress-Konto?

Nein. Autorenprofile sind Taxonomie-Begriffe. Sie gewähren weder Zugang noch Bearbeitungsrechte oder Beitragsbesitz.

### Kann ein Beitrag mehrere Autoren haben?

Ja. Weise mehrere Autoren zu. [pat-authors] zeigt ihre Namen, [pat-author-boxes] alle zugewiesenen Profile.

### Ersetzt das Plugin die Autorenzeile des Themes?

Nein. Füge einen Shortcode im Inhalt oder Template ein und konfiguriere die native Autorenzeile deines Themes separat.

### Sind Autoren für Seiten und eigene Inhaltstypen möglich?

Ja. Aktiviere öffentliche Inhaltstypen unter Einstellungen → Post Author Taxonomy. Anhänge sind ausgenommen. Beiträge sind standardmäßig aktiviert.

## Profile und Archive

### Wo bearbeite ich Biografie, Foto und Website?

Öffne Autoren verwalten auf der Einstellungsseite. Nutze die Beschreibung für die Biografie, wähle ein lokales Mediathek-Bild und ergänze optional eine HTTP/HTTPS-Website.

### Werden Fotos von Gravatar geladen?

Nein. Fotos sind lokale WordPress-Medien. Das Plugin ruft keine externen Avatare ab.

### Warum bleibt [pat-author-box] auf einem Beitrag leer?

Ohne ausdrückliche Auswahl zeigt dieser Shortcode nur das aktuelle Profil eines pat-author-Taxonomie-Archivs. Nutze [pat-author-boxes] für alle zugewiesenen Autoren oder wähle einen Autor über id, slug oder name.

### Was passiert bei einer ungültigen Autoren-ID?

Die Box bleibt leer. ID hat Vorrang vor Slug und Name; eine ungültige ID wählt nicht stillschweigend ein anderes Profil.

### Kann ich Autoren pro Beitrag individuell sortieren?

Nein. Die Reihenfolge folgt WordPress, normalerweise nach Namen. Eine individuelle Reihenfolge pro Beitrag ist nicht enthalten.

### Zeigt ein Autorenarchiv die Beiträge?

Ja, über ein normales Archiv-Template deines Themes oder Builders. Der Profil-Shortcode ergänzt das Profil; die Archivabfrage listet zugehörige Inhalte.

### Wie lautet die Archiv-URL?

Der Taxonomie-Schlüssel bleibt pat-author. Der Standard-Slug lautet auf englischen Websites post-author und auf deutschen Websites beitragsautor. Entscheidend ist die Website-Sprache, nicht die persönliche Adminsprache. Taxonomie-Filter können die Registrierung anpassen.

## Shortcodes und Gestaltung

### Welche Linkziele gibt es?

Nutze link="archive", link="website" oder link="none". Listen verwenden die gespeicherte Vorgabe, Boxen zunächst none. Ohne Website bleibt der Name im Website-Modus unverlinkt.

### Kann ich Autoren eines anderen Beitrags anzeigen?

Ja. Nutze post_id mit [pat-authors] oder [pat-author-boxes], beispielsweise [pat-author-boxes post_id="42"].

### Kann ich Fotos oder Website-Links ausblenden?

Ja. Nutze photo="no" oder website="no" bei beiden Box-Shortcodes. Die Profilinformationen bleiben gespeichert.

### Ist HTML in Namen oder Biografien möglich?

Namen und Überschriften werden maskiert; Biografien sind reiner Text. Listenbeschriftungen erlauben begrenzte Inline-Formatierung. Wrapper und Überschriften sind auf dokumentierte Elemente begrenzt.

### Wo sind die Gestaltungsoptionen?

Dein Theme oder Builder bestimmt das Layout. Es werden weder Frontend-CSS noch JavaScript geladen. Gestalte die dokumentierten pat-author-box-Klassen im Template oder Stylesheet.

## Builder und Integration

### Funktioniert es mit Bricks, Breakdance oder dem Block-Editor?

Nutze ein Shortcode-Element beziehungsweise einen Shortcode-Block. Die öffentliche Taxonomie steht normalen Taxonomie-Abfragen zur Verfügung. Kommerzielle Builder wurden nicht separat getestet; eine spezielle Builder-Erweiterung ist nicht enthalten.

### Ergänzt es SEO-Autoren-Schema?

Nein. Konfiguriere strukturierte Daten in deinem SEO-Plugin oder Template. Taxonomie-Autoren werden nicht automatisch zu WordPress-Benutzern oder Schema-Personen.

### Ist die Snippet-Ausgabe gleichwertig mit dem Plugin?

Sie teilt Taxonomie und Shortcode-Kern, enthält aber keine Einstellungen, keinen Medieneditor, Updater oder Library. Verwende sie anstelle des Plugins. Inhaltstypen und Vorgaben lassen sich über PHP-Filter anpassen.

## Updates und Daten

### Wie kommen Updates an?

Der eingebettete deckerweb GitHub Release Updater V2 bietet stabile GitHub-Versionen über reguläre WordPress-Plugin-Updates an. Ein separates Updater-Plugin ist nicht nötig.

### Was macht die deckerweb Library?

Sie ergänzt den deckerweb-Tab unter Plugins → Installieren. Mehrere Kopien wählen eine gemeinsame Laufzeit. Der optionale Online-Katalog ist unter Einstellungen → deckerweb Library konfigurierbar; dieses Plugin aktiviert ihn nicht automatisch.

### Was bleibt nach Deaktivierung oder Entfernung?

Autoren, Zuweisungen und Profilinformationen bleiben in der Datenbank. Archive können während der Deaktivierung nicht mehr erreichbar sein. Eine löschende Deinstallation ist nicht enthalten.

### Welche WordPress- und PHP-Versionen sind nötig?

WordPress 6.7+ und PHP 8.0+. Die PHP-Mindestversion steigt gegenüber 1.2.0 von 7.4 auf 8.0, weil die Library PHP 8.0 benötigt. Funktionstests liefen mit WordPress 6.7 und 7.1.2 unter PHP 8.4.5.

### Wird ClassicPress offiziell unterstützt?

Nein. Kompatibilität ist willkommen, aber ClassicPress wird nicht offiziell unterstützt oder getestet.

### Was muss ich beim Update von 1.2.0 prüfen?

Autorendaten, Taxonomie-Schlüssel und Erweiterungsfilter bleiben erhalten. Prüfe eigene HTML-Beschriftungen und Wrapper, da die Ausgabe jetzt begrenzt ist. Der after-Nachtext erscheint einmal. PHP 8.0 ist erforderlich.

[Anleitung](https://github.com/deckerweb/post-author-taxonomy/wiki/Deutsch) · [Download](https://github.com/deckerweb/post-author-taxonomy/releases/latest)
