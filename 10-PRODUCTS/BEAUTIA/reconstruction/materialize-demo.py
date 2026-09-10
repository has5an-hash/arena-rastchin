#!/usr/bin/env python3
import argparse, zipfile, tempfile, shutil, re, os, json, hashlib
from pathlib import Path
from PIL import Image, ImageOps

DEMOS = ['nail','clinic','hair','spa','lashes','makeup','barber']

def normalize_source_basename(name, available):
    stem, ext = os.path.splitext(name)
    candidates=[name]
    m=re.match(r'^(.*)-\\d+x\\d+$', stem)
    if m:
        stem=m.group(1)
        candidates.append(stem+ext)
    cur=stem
    for _ in range(4):
        m=re.match(r'^(.*)-\\d+$', cur)
        if not m:
            break
        cur=m.group(1)
        candidates.append(cur+ext)
    for c in candidates:
        if c in available:
            return c
    return None

def extract_sql_refs(sql):
    refs=set()
    for m in re.finditer(r'([A-Za-z0-9._/%:-]+\\.(?:jpg|jpeg|png|webp|gif))', sql, re.I):
        token=m.group(1).replace('\\\\/','/').split('?',1)[0]
        refs.add(os.path.basename(token))
    attached=sorted(set(re.findall(r"'_wp_attached_file','([^']+)'", sql)))
    return sorted(refs), attached

def materialize(base_zip, db_zip, demo, out_dir):
    out_dir=Path(out_dir)
    work=out_dir/demo
    if work.exists():
        shutil.rmtree(work)
    out_dir.mkdir(parents=True, exist_ok=True)

    with tempfile.TemporaryDirectory() as td:
        td=Path(td)
        with zipfile.ZipFile(base_zip) as z:
            z.extractall(td)
        src=td/'nail'
        if not src.exists():
            raise RuntimeError('base ZIP must contain top-level nail/')
        shutil.copytree(src, work)

    cfg=work/'wp-config.php'
    s=cfg.read_text('utf-8')
    s=re.sub(r"define\\('DB_NAME',\\s*'[^']*'\\);", f"define('DB_NAME', 'beautia_{demo}');", s)
    s=re.sub(r"define\\('WP_HOME',\\s*'[^']*'\\);", f"define('WP_HOME', 'http://localhost/beautia/demoes/{demo}');", s)
    s=re.sub(r"define\\('WP_SITEURL',\\s*'[^']*'\\);", f"define('WP_SITEURL', 'http://localhost/beautia/demoes/{demo}');", s)
    cfg.write_text(s,'utf-8')

    ht=work/'.htaccess'
    if ht.exists():
        s=ht.read_text('utf-8').replace('/beautia/demoes/nail/','/beautia/demoes/'+demo+'/')
        ht.write_text(s,'utf-8')

    uploads=work/'wp-content'/'uploads'
    if uploads.exists():
        shutil.rmtree(uploads)
    uploads.mkdir(parents=True, exist_ok=True)
    for c in [work/'wp-content'/'cache', work/'wp-content'/'upgrade']:
        if c.exists():
            shutil.rmtree(c)

    with zipfile.ZipFile(db_zip) as dz:
        sql_name=f'beautia_{demo}.sql'
        sql=dz.read(sql_name).decode('utf-8','replace')
    refs, attached=extract_sql_refs(sql)

    theme_dir=work/'wp-content'/'themes'/'beautia'/'assets'/'img'
    available={p.name:p for p in theme_dir.iterdir() if p.is_file()}
    media_dir=uploads/'2026'/'09'
    media_dir.mkdir(parents=True, exist_ok=True)

    generated=[]
    missing=[]
    for bn in refs:
        src_name=normalize_source_basename(bn, available)
        if not src_name:
            missing.append(bn)
            continue
        src=available[src_name]
        dst=media_dir/bn
        m=re.search(r'-(\\d+)x(\\d+)(?=\\.[^.]+$)',bn)
        if m and src.suffix.lower() in ['.jpg','.jpeg','.png','.webp']:
            w,h=map(int,m.groups())
            try:
                with Image.open(src) as im:
                    im=ImageOps.fit(im.convert('RGB'),(w,h),method=Image.Resampling.LANCZOS)
                    im.save(dst,'JPEG',quality=84,optimize=True,progressive=True)
            except Exception:
                shutil.copy2(src,dst)
        else:
            shutil.copy2(src,dst)
        generated.append({'target':bn,'source':src_name,'size':dst.stat().st_size})

    attached_missing=[]
    for rel in attached:
        bn=os.path.basename(rel)
        target=uploads/rel
        if target.exists():
            continue
        src_name=normalize_source_basename(bn, available)
        if not src_name:
            attached_missing.append(rel)
            continue
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(available[src_name],target)

    dbdir=work/'_database'
    dbdir.mkdir(exist_ok=True)
    (dbdir/f'beautia_{demo}.sql').write_text(sql,'utf-8')
    (dbdir/'README.txt').write_text(
        'This is a reconstructed-current development package, not an exact historical archive.\\n'
        f'Import beautia_{demo}.sql into database beautia_{demo}.\\n'
        'Runtime secrets in wp-config.php are placeholders and must be replaced for real deployments.\\n',
        'utf-8'
    )

    check={
        'demo':demo,
        'db_name':f'beautia_{demo}',
        'home':f'http://localhost/beautia/demoes/{demo}',
        'sql_sha256':hashlib.sha256(sql.encode()).hexdigest(),
        'attached_originals':len(attached),
        'generated_media_files':len(generated),
        'unmapped_sql_image_basenames':missing,
        'missing_attached_originals':attached_missing,
        'status':'valid-current-reconstruction' if not attached_missing and not missing else 'incomplete',
        'historical_exact':False
    }
    (work/'CURRENT-RECONSTRUCTION-MANIFEST.json').write_text(
        json.dumps(check,ensure_ascii=False,indent=2),'utf-8'
    )
    return check

def main():
    ap=argparse.ArgumentParser()
    ap.add_argument('--base',required=True)
    ap.add_argument('--dbs',required=True)
    ap.add_argument('--demo',choices=DEMOS+['all'],default='all')
    ap.add_argument('--out',required=True)
    args=ap.parse_args()
    demos=DEMOS if args.demo=='all' else [args.demo]
    print(json.dumps(
        [materialize(args.base,args.dbs,d,args.out) for d in demos],
        ensure_ascii=False, indent=2
    ))

if __name__=='__main__':
    main()
