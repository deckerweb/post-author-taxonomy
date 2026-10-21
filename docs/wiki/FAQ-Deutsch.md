# Fragen nach Themen · Post Author Taxonomy

[English](https://github.com/deckerweb/post-author-taxonomy/wiki/FAQ%E2%80%90English)

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

### Wie lege ich die Autorenreihenfolge fest?

Ordne Autoren im Beitragseditor wie gewohnt zu und öffne Autorenreihenfolge und Rollen → Zugeordnete Autoren aktualisieren. Ziehe die Zeilen oder verwende die Pfeiltasten und speichere den Beitrag. Die Reihenfolge gilt nur für diesen Beitrag. Neue Autoren werden angehängt; alte entfernte Zuordnungen werden ignoriert.

### Ändern redaktionelle Rollen Rechte oder SEO-Schema?

Nein. Autor, Redaktionell geprüft von und Übersetzung sind Angaben zur Mitarbeit je Beitrag. Sie ändern weder Zugang noch Bearbeitungsrechte, post_author oder SEO-Schema. Zeige sie ausdrücklich mit roles="yes" an und filtere Gruppen mit role="reviewer", role="translator" oder role="author".

### Speichert der Konfigurator Einstellungen?

Nein. Er erzeugt einen Shortcode und eine Vorschau mit echten Daten. Der aktuelle Kontext ist in Templates wiederverwendbar; der feste Kontext enthält post_id. Für die Vorschau von Entwürfen oder privaten Beiträgen brauchst du Bearbeitungsrechte für den ausgewählten Beitrag.

### Welche dynamischen Bricks-Tags gibt es?

Die optionale Gruppe Post Author Taxonomy enthält pat_authors, pat_author_boxes, pat_author_name, pat_author_photo und pat_author_website. Verwende die Tags in Bricks mit geschweiften Klammern. Das erste Profil folgt der Beitragsreihenfolge; das Foto liefert im Bildkontext eine Anhang-ID als Array. Andere Builder können weiterhin die regulären Shortcodes verwenden.

### Wie behebe ich 404-Fehler in Autorenarchiven?

Prüfe Einstellungen → Post Author Taxonomy → Lokale Diagnose und erneuere die Regeln für Autorenarchive einmal. Die Aktion erfordert einen Administrator und löscht weder Autoren noch Einstellungen oder Beiträge. Besteht das Problem weiter, prüfe Permalink- und Theme-/Routing-Konflikte.

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

Autoren, Zuweisungen, Profilinformationen und Einstellungen bleiben erhalten. Temporäre Caches werden bei Deinstallation entfernt; die Library prüft, ob weitere Hosts installiert sind. Archive sind während der Deaktivierung gegebenenfalls nicht erreichbar.

### Welche WordPress- und PHP-Versionen sind nötig?

WordPress 6.7+ und PHP 8.1+. Die PHP-Mindestversion steigt gegenüber 1.2.0 von 7.4 auf 8.0, weil die Library PHP 8.1 benötigt. Funktionstests liefen mit WordPress 6.7 und 7.1.2 unter PHP 8.4.5.

### Wird ClassicPress offiziell unterstützt?

Nein. Kompatibilität ist willkommen, aber ClassicPress wird nicht offiziell unterstützt oder getestet.

### Was muss ich beim Update von 1.2.0 prüfen?

Autorendaten, Taxonomie-Schlüssel und Erweiterungsfilter bleiben erhalten. Prüfe eigene HTML-Beschriftungen und Wrapper, da die Ausgabe jetzt begrenzt ist. Der after-Nachtext erscheint einmal. PHP 8.1 ist erforderlich.

### Kann ich das Plugin im Netzwerk aktivieren?

Einstellungen, Autoren, Zuweisungen, Fotos und Websites gelten jeweils für die einzelne Website. Bei Netzwerkaktivierung steht das Plugin auf allen Websites bereit; die Einstellungsseite bleibt im jeweiligen Website-Admin. Neue Websites erhalten die normale Beitrags-Vorgabe. Archivregeln werden bei Bedarf nach Registrierung der Website-Sprache aktualisiert.

### Welche Daten gehen an externe Server?

Autorenfotos liegen lokal in der Mediathek. Der Updater fragt api.github.com nach Release-Metadaten; Downloads erfolgen über GitHub und dessen Download-Infrastruktur. Der freiwillige Katalog fragt nur die freigegebene deckerweb-Quelle ab. Externe Server erhalten die üblichen Verbindungsdaten, etwa IP-Adresse und HTTP-Clientkennung; Autoreninhalte und Zugangsdaten werden nicht übertragen. Keine Telemetrie und keine externen Avatar-Anfragen.

### Wie melde ich Sicherheitslücken?

Nutze den privaten GitHub-Sicherheitsmeldeweg im Repository des Host-Plugins, auch bei Library-Problemen. Melde Versionen, Reproduktion und Auswirkungen ohne Zugangsdaten. Ist die private Meldung nicht verfügbar, veröffentliche keine Sicherheitsdetails in Issues oder Discussions.

### Warum pausieren die Plugin-Updates?

Hat ein anderes Plugin zuerst einen älteren gemeinsamen Updater geladen, zeigt Post Author Taxonomy einen Hinweis und pausiert seine neue Integration. Aktualisiere den anderen deckerweb Host auf einen kompatiblen Updater-Stand. Die Autorenfunktionen bleiben nutzbar; fremde Dateien oder Update-Hooks werden nicht umgeschrieben.

## Technische Details

### Wer darf Autorenfotos ändern?

Zum Bearbeiten eines Profils brauchst du die Berechtigung für den Autorenbegriff. Das ausgewählte Foto muss ein Bild sein, das du in der lokalen Mediathek bearbeiten darfst. Dieselben Prüfungen gelten über die REST-API. Fehlt ein Foto- oder Website-Feld in einem teilweise übermittelten Profilformular, bleibt der gespeicherte Wert erhalten. Eine übermittelte Null beziehungsweise eine leere Website entfernt den Wert ausdrücklich.

[Anleitung](https://github.com/deckerweb/post-author-taxonomy/wiki/Deutsch)
