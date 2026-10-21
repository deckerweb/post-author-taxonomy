#!/usr/bin/env python3
"""Build host POT, PO, MO and PHP catalogs with the embedded component's host-domain messages."""
from pathlib import Path
import re,json,gettext,subprocess
ROOT=Path(__file__).resolve().parents[1]
DICTIONARY=json.loads((ROOT/'languages/messages.json').read_text())
entries={}
pattern=re.compile(r"\b(?:__|_x|esc_html__|esc_attr__|esc_html_x|esc_attr_x)\(\s*'((?:\\.|[^'])*)'\s*,\s*'((?:\\.|[^'])*)'(?:\s*,\s*'((?:\\.|[^'])*)')?\s*\)")
for f in list((ROOT/'includes').glob('class-*.php'))+[ROOT/'post-author-taxonomy.php']:
    for m in pattern.finditer(f.read_text()):
        if (m[3] or m[2])!='post-author-taxonomy':continue
        message=m[1].replace("\\'", "'");context=m[2] if m[3] else None
        key=(context+'\x04' if context else '')+message
        entries[key]=(message,context,str(f.relative_to(ROOT)),f.read_text()[:m.start()].count('\n')+1)
# Library runtime merges these messages into the elected host domain as well.
component=gettext.GNUTranslations(open(ROOT/'includes/deckerweb-plugin-library/languages/de_DE.mo','rb'))._catalog
for key in component:
    if isinstance(key,str) and key:
        parts=key.split('\x04',1);entries.setdefault(key,(parts[-1],parts[0] if len(parts)==2 else None,'includes/deckerweb-plugin-library/languages/de_DE.mo',1))
for locale in ['de_DE','de_DE_formal','POT']:
    q=lambda s:json.dumps(s,ensure_ascii=False)
    header='Project-Id-Version: Post Author Taxonomy 1.4.1\nPOT-Creation-Date: 2026-10-21 12:00+0200\nPO-Revision-Date: 2026-10-21 12:00+0200\nLast-Translator: David Decker – DECKERWEB\nLanguage-Team: German\nLanguage: '+('' if locale=='POT' else locale)+'\nMIME-Version: 1.0\nContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit\nPlural-Forms: nplurals=2; plural=(n != 1);\n'
    lines=['msgid ""','msgstr '+q(header),''];messages={}
    for key,(message,context,file,line) in sorted(entries.items()):
        value='' if locale=='POT' else DICTIONARY[locale].get(key,'')
        if locale!='POT' and not value:raise ValueError('Missing '+locale+' translation: '+key)
        lines.append('#: '+file+':'+str(line))
        if re.search(r'%(?:\d+\$)?[sd]',message):lines.append('#, php-format')
        if context:lines.append('msgctxt '+q(context))
        lines+=['msgid '+q(message),'msgstr '+q(value),'']
        if value:messages[key]=value
    name='post-author-taxonomy.pot' if locale=='POT' else 'post-author-taxonomy-'+locale+'.po'
    (ROOT/'languages'/name).write_text('\n'.join(lines))
    if locale!='POT':
        subprocess.run(['msgfmt','--check','-o',str(ROOT/'languages'/name.replace('.po','.mo')),str(ROOT/'languages'/name)],check=True)
        def phpquote(value):
            """Encode a PHP literal while preserving gettext context separators."""
            return "'"+value.replace('\\','\\\\').replace("'","\\'").replace('\x04',"' . chr(4) . '")+"'"
        php="<?php\n// Generated from the matching PO catalog.\nreturn ['domain'=>'post-author-taxonomy','language'=>"+phpquote(locale)+",'plural-forms'=>'nplurals=2; plural=(n != 1);','messages'=>[\n"
        php+=''.join(phpquote(k)+'=>'+phpquote(v)+',\n' for k,v in messages.items())+']];\n'
        (ROOT/'languages'/name.replace('.po','.l10n.php')).write_text(php)
print(str(len(entries))+' complete messages in informal/formal German, using only the host domain.')
