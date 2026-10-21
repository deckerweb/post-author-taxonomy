#!/usr/bin/env python3
"""Export synchronized bilingual readmes, guides, FAQs and history from docs/content.json."""
from pathlib import Path
import json,re,html
ROOT=Path(__file__).resolve().parents[1]
DATA=json.loads((ROOT/'docs/content.json').read_text())
BASE='https://github.com/deckerweb/post-author-taxonomy'
WIKI=BASE+'/wiki/'
def anchor(title):
    """Return a stable ASCII anchor for either language."""
    value=title.lower().replace('ä','ae').replace('ö','oe').replace('ü','ue').replace('ß','ss')
    return re.sub(r'[^a-z0-9 -]','',value).replace(' ','-')
def page(kind,language):
    """Return an existing GitHub wiki URL with its canonical hyphen."""
    return WIKI+kind+'%E2%80%90'+language
def render_markdown(source):
    """Render the trusted, limited Markdown vocabulary used in this content source."""
    def inline(value):
        value=html.escape(value)
        value=re.sub(r'`([^`]+)`',r'<code>\1</code>',value)
        value=re.sub(r'\*\*([^*]+)\*\*',r'<strong>\1</strong>',value)
        def link(match):
            target=html.unescape(match[2])
            if not target.startswith(('https://','http://','#')):target=BASE+'/blob/master/'+target
            return '<a href="'+html.escape(target,quote=True)+'">'+match[1]+'</a>'
        return re.sub(r'\[([^\]]+)\]\(([^)]+)\)',link,value)
    result=[];lines=source.splitlines();i=0
    while i<len(lines):
        line=lines[i]
        if not line.strip():i+=1;continue
        if line.startswith('```'):
            code=[];i+=1
            while i<len(lines) and not lines[i].startswith('```'):code.append(lines[i]);i+=1
            result.append('<pre><code>'+html.escape('\n'.join(code))+'</code></pre>');i+=1;continue
        if line.startswith('|'):
            rows=[]
            while i<len(lines) and lines[i].startswith('|'):
                cells=[x.strip() for x in lines[i].strip('|').split('|')]
                if not all(re.fullmatch(r':?-+:?',x) for x in cells):rows.append(cells)
                i+=1
            result.append('<table><thead><tr>'+''.join('<th scope="col">'+inline(x)+'</th>' for x in rows[0])+'</tr></thead><tbody>'+''.join('<tr>'+''.join('<td>'+inline(x)+'</td>' for x in row)+'</tr>' for row in rows[1:])+'</tbody></table>');continue
        if re.match(r'^#{1,6} ',line):
            level=len(line)-len(line.lstrip('#'));result.append('<h'+str(level)+'>'+inline(line[level+1:])+'</h'+str(level)+'>');i+=1;continue
        ordered=bool(re.match(r'^\d+\. ',line));unordered=line.startswith(('- ','* '))
        if ordered or unordered:
            tag='ol' if ordered else 'ul';items=[]
            pattern=r'^\d+\. ' if ordered else r'^[*-] '
            while i<len(lines) and re.match(pattern,lines[i]):items.append('<li>'+inline(re.sub(pattern,'',lines[i]))+'</li>');i+=1
            result.append('<'+tag+'>'+''.join(items)+'</'+tag+'>');continue
        paragraph=[line];i+=1
        while i<len(lines) and lines[i].strip() and not re.match(r'^(?:#|```|\||[-*] |\d+\. )',lines[i]):paragraph.append(lines[i]);i+=1
        result.append('<p>'+inline(' '.join(paragraph))+'</p>')
    return ''.join(result)

for lang,de in [('en',False),('de',True)]:
    row=DATA['languages'][lang];suffix='-de' if de else '';language='Deutsch' if de else 'English'
    faqtitle='Häufige Fragen' if de else 'FAQ';historytitle='Änderungsverlauf' if de else 'Changelog'
    sections=[('Auf einen Blick' if de else 'At a glance','\n'.join('- '+x for x in row['glance']))]
    sections += [(s['title'],s['body']) for s in row['sections']]
    shortfaq=[row['faq'][i] for i in row['readme_faq_indices']]
    fq='\n\n'.join('**'+f['question']+'**\n\n'+f['answer'] for f in shortfaq)
    fq+='\n\n['+('Alle Fragen nach Themen' if de else 'All questions by topic')+']('+page('FAQ',language)+')'
    sections.append((faqtitle,fq))
    history=re.sub(r'^== Changelog ==\n','',row['history']);history=re.sub(r'^= (.+) =$',r'### \1',history,flags=re.M)
    history=re.sub(r'^\* ([^:]+):',r'- **\1:**',history,flags=re.M)
    # Markdown needs blank lines between version headings and their lists.
    history=re.sub(r'(?m)^(### .+)\n(?!\n)',r'\1\n\n',history)
    history=re.sub(r'(?m)([^\n])\n(### )',r'\1\n\n\2',history)
    sections.append((historytitle,history.strip()+'\n\n['+('Vollständiger Änderungsverlauf' if de else 'Complete changelog')+']('+page('Changelog',language)+')'))
    sections.append(('Über das Plugin' if de else 'About the plugin',row['project']))
    support='[Issues]('+BASE+'/issues) · [Security / Sicherheit](SECURITY.md) · [Ko-fi](https://ko-fi.com/deckerweb) · [Buy Me a Coffee](https://buymeacoffee.com/daveshine) · [PayPal](https://paypal.me/deckerweb)'
    sections.append(('Kontakt und Unterstützung' if de else 'Contact and support',support))
    sections.append(('Lizenz und Herkunft' if de else 'License and origin','© 2017–2026 David Decker – DECKERWEB · GPL v2 or later.\n\n['+('Grafiken und eingebundene Komponenten' if de else 'Artwork and bundled components')+'](docs/ASSETS'+suffix+'.md)'))
    metadata=('**Version:** '+DATA['version']+' · **'+('Voraussetzungen' if de else 'Requires')+':** WordPress '+DATA['requires_wp']+'+ / PHP '+DATA['requires_php']+'+ · **'+('Lizenz' if de else 'License')+':** GPL v2 or later')
    links='[Download]('+BASE+'/releases/latest) · ['+('Anleitung' if de else 'User guide')+']('+WIKI+language+') · ['+('English' if de else 'Deutsch')+']('+('README.md' if de else 'README-de.md')+')'
    toc='## '+('Inhaltsverzeichnis' if de else 'Contents')+'\n\n'+'\n'.join('- ['+title+'](#'+anchor(title)+')' for title,_ in sections)
    head='# Post Author Taxonomy\n\n![Post Author Taxonomy]('+row['banner']+')\n\n'+row['about']+'\n\n'+metadata+'\n\n'+links+'\n\n'+toc+'\n\n'
    markdown=head+'\n\n'.join('<a name="'+anchor(title)+'"></a>\n\n## '+title+'\n\n'+body for title,body in sections)+'\n'
    (ROOT/f'README{suffix}.md').write_text(markdown)
    header='=== Post Author Taxonomy ===\nContributors: deckerweb\nTags: authors, taxonomy, multiple authors, guest authors\nRequires at least: '+DATA['requires_wp']+'\nTested up to: '+DATA['tested_up_to']+'\nRequires PHP: '+DATA['requires_php']+'\nStable tag: '+DATA['version']+'\nLicense: GPLv2 or later\nLicense URI: https://www.gnu.org/licenses/gpl-2.0.html\n\n'
    short='Autoren als öffentliche Taxonomie: mehrere Autoren, lokale Profile und flexible Shortcodes.' if de else 'Authors as a public taxonomy: multiple authors, local profiles and flexible shortcodes.'
    text=header+short+'\n\n== Description ==\n\n'+row['about']+'\n\n'+metadata+'\n\n'+links+'\n\n'
    for title,body in sections:
        if title==faqtitle:
            text+='== Frequently Asked Questions ==\n\n'+'\n\n'.join('= '+f['question']+' =\n\n'+f['answer'] for f in shortfaq)+'\n\n'+fq.split('\n\n')[-1]+'\n\n'
        elif title==historytitle:text+=row['history']+'\n'
        else:
            marker='Installation' if title in ['Start in three steps','In drei Schritten starten'] else title
            text+='== '+marker+' ==\n\n'+body+'\n\n'
    text=text.replace('**','');text=re.sub(r'\[([^\]]+)\]\(([^)]+)\)',lambda m:m[1]+' ('+(BASE+'/blob/master/'+m[2] if not m[2].startswith('http') else m[2])+')',text)
    text=text.replace('```text\n','').replace('```php\n','').replace('```','')
    (ROOT/f'readme{suffix}.txt').write_text(text.rstrip()+'\n')
    (ROOT/'docs'/f'changelog{suffix}.txt').write_text(row['history'])
    (ROOT/'docs'/f'CHANGELOG{suffix}.md').write_text('# '+historytitle+' · Post Author Taxonomy\n\n'+history)
    wiki=ROOT/'docs/wiki';wiki.mkdir(exist_ok=True)
    guide=markdown.replace('('+row['banner']+')','('+BASE+'/raw/master/'+row['banner']+')').replace('(README-de.md)','('+WIKI+'Deutsch)').replace('(README.md)','('+WIKI+'English)').replace('(SECURITY.md)','('+BASE+'/blob/master/SECURITY.md)').replace('(docs/ASSETS','('+BASE+'/blob/master/docs/ASSETS')
    (wiki/f'{language}.md').write_text(guide)
    full='# '+('Fragen nach Themen' if de else 'FAQ by topic')+' · Post Author Taxonomy\n\n['+('English' if de else 'Deutsch')+']('+page('FAQ','English' if de else 'Deutsch')+')\n\n';group=None
    for f in row['faq']:
        if f['group']!=group:full+='## '+f['group']+'\n\n';group=f['group']
        full+='### '+f['question']+'\n\n'+f['answer']+'\n\n'
    full+='['+('Anleitung' if de else 'User guide')+']('+WIKI+language+')\n'
    (wiki/f'FAQ-{language}.md').write_text(full)
    (wiki/f'Changelog-{language}.md').write_text('# '+historytitle+' · Post Author Taxonomy\n\n'+history)
    # Offline guides retain semantic headings, tables and links without fetching remote assets.
    offline = '<!doctype html><html lang="'+lang+'"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Post Author Taxonomy</title></head><body><main><h1>Post Author Taxonomy</h1>'
    offline += '<p><a href="'+WIKI+language+'">'+('Anleitung' if de else 'User guide')+'</a> · <a href="'+page('FAQ',language)+'">'+faqtitle+'</a></p>'
    offline += render_markdown(row['about'])
    offline += ''.join('<section><h2>'+html.escape(title)+'</h2>'+render_markdown(body)+'</section>' for title,body in sections)
    (ROOT/'docs'/f'guide{suffix}.html').write_text(offline+'</main></body></html>')

terms=DATA['glossary']
for suffix,title in [('', 'Terminology'),('-de','Begriffsverzeichnis')]:
    table='| English | Deutsch (Du) | Deutsch (Sie) |\n| --- | --- | --- |\n'+'\n'.join('| '+' | '.join(term)+' |' for term in terms)
    (ROOT/'docs'/f'GLOSSARY{suffix}.md').write_text('# '+title+' · Post Author Taxonomy\n\n'+table+'\n')
# Navigation is also exported from the common content source.
home='# Post Author Taxonomy · Documentation / Dokumentation\n\n'
home+='![Post Author Taxonomy]('+BASE+'/raw/master/'+DATA['languages']['en']['banner']+')\n\n'
home+='**Authors as tags. Designed in your builder. / Autoren wie Tags. Gestalte mit deinem Builder.**\n\nMultiple authors without user accounts, with flexible profiles and shortcodes. / Mehrere Autoren ohne Benutzerkonten, mit flexiblen Profilen und Shortcodes.\n\n| English | Deutsch |\n| --- | --- |\n'
for name,en,de in [('','User guide','Anleitung'),('FAQ','FAQ by topic','Fragen nach Themen'),('Changelog','Changelog','Änderungsverlauf')]:
    url_en=page(name,'English') if name else WIKI+'English';url_de=page(name,'Deutsch') if name else WIKI+'Deutsch'
    home+='| ['+en+']('+url_en+') | ['+de+']('+url_de+') |\n'
home+='\n[Download / Releases]('+BASE+'/releases/latest) · [Security / Sicherheit]('+BASE+'/security)\n\n© 2017–2026 David Decker – DECKERWEB · GPL v2 or later\n'
(ROOT/'docs/wiki/Home.md').write_text(home)
sidebar=''
for language in ['English','Deutsch']:
    sidebar+='\n## '+language+'\n\n'
    for kind,label in [('', 'User guide' if language=='English' else 'Anleitung'),('FAQ','FAQ by topic' if language=='English' else 'Fragen nach Themen'),('Changelog','Changelog' if language=='English' else 'Änderungsverlauf')]:
        sidebar+='- ['+label+']('+(page(kind,language) if kind else WIKI+language)+')\n'
    sidebar+='- ['+('Help and security' if language=='English' else 'Hilfe und Sicherheit')+']('+BASE+'/security/policy)\n'
(ROOT/'docs/wiki/_Sidebar.md').write_text(sidebar)
footer='**Post Author Taxonomy · Documentation / Dokumentation**\n\n'
footer+='[Wiki home / Wiki-Startseite]('+WIKI+') · [Repository]('+BASE+') · [Downloads & Releases]('+BASE+'/releases)\n\n'
footer+='[Help & bugs / Hilfe & Fehler]('+BASE+'/issues) · [Discussions / Austausch]('+BASE+'/discussions) · [Security / Sicherheit]('+BASE+'/security/policy)\n\n'
footer+='© 2017–2026 David Decker – DECKERWEB.  \nPlugin: [GPL v2 or later / GPL v2 oder höher](https://www.gnu.org/licenses/gpl-2.0.html).\n'
(ROOT/'docs/wiki/_Footer.md').write_text(footer)
print('EN/DE readmes, '+str(len(DATA['languages']['en']['faq']))+' FAQ answers per language, history and local guides exported from docs/content.json.')
