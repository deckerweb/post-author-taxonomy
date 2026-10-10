#!/usr/bin/env python3
"""Build a local verification ZIP from an explicit runtime allowlist; never publish or bump a version."""
from pathlib import Path
import argparse,zipfile,hashlib,json
ROOT=Path(__file__).resolve().parents[1]
parser=argparse.ArgumentParser();parser.add_argument('destination');args=parser.parse_args()
out=Path(args.destination).resolve();out.parent.mkdir(parents=True,exist_ok=True)
files=[ROOT/f for f in ['post-author-taxonomy.php','uninstall.php','index.php','LICENSE','SECURITY.md','README.md','README-de.md','readme.txt','readme-de.txt']]
files += [f for f in (ROOT/'includes').rglob('*') if f.is_file()]
files += [f for f in (ROOT/'languages').iterdir() if f.suffix in ['.po','.mo','.php','.pot']]
files += [f for f in (ROOT/'assets').iterdir() if f.is_file()]
files += [f for f in (ROOT/'assets-github').iterdir() if f.is_file()]
files += [ROOT/'docs'/f for f in ['changelog.txt','changelog-de.txt','guide.html','guide-de.html','ASSETS.md','ASSETS-de.md','GLOSSARY.md','GLOSSARY-de.md']]
for f in files:
    if not f.is_file():raise ValueError('Missing runtime file: '+str(f))
with zipfile.ZipFile(out,'w',zipfile.ZIP_DEFLATED) as archive:
    for f in sorted(set(files)):
        archive.write(f,Path('post-author-taxonomy')/f.relative_to(ROOT))
with zipfile.ZipFile(out) as archive:
    for name in archive.namelist():
        assert not any(x in name.lower() for x in ['roadmap','abgleich','steckbrief','sub-spec','/tests/','/tools/','/wiki/','.git/','snippet']),name
    assert 'post-author-taxonomy/uninstall.php' in archive.namelist()
    assert 'post-author-taxonomy/includes/deckerweb-plugin-library/compatibility.json' in archive.namelist()
(out.parent/(out.name+'.sha256')).write_text(hashlib.sha256(out.read_bytes()).hexdigest()+'  '+out.name+'\n')
print('Local verification ZIP: '+str(out)+'; '+str(len(set(files)))+' allowlisted runtime files; internal/tool/snippet exclusions checked.')
