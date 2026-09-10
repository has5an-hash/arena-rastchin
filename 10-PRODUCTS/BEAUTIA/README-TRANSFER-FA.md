# بیوتیا — انتقال به Arena

مرجع اصلی: چت `وضعیت نهایی قالب بیوتیا/تحویل از کامپیوتر`.

## وضعیت فعلی قابل استفاده در Arena
- سورس قابل‌ویرایش فعلی قالب + Beautia Core در `source/`.
- آرشیو سورس در `archives/beautia-source-current.zip`.
- Runtime Base امن در `archives/beautia-nail-sanitized.zip`.
- SQL امن هر هفت دمو در `archives/beautia-databases-sanitized.zip`.
- Full File Manifest در `docs/FULL-FILE-MANIFEST.csv`.
- Handoff، Checkpoint، Work Log، QA، راهنمای فنی و Changelog در `docs/`.
- بسته یکپارچه اسناد در `beautia-docs-complete.zip`.
- کیت بازسازی ۷ دمو در `reconstruction/`.

## بازسازی ۷ دمو در Cloud
فایل `reconstruction/materialize-demo.py` از Runtime Base + دیتابیس هر دمو، محیط اجرایی فعلی همان دمو را می‌سازد و Media Library را از تصاویر واقعی موجود در سورس بازسازی می‌کند.

GitHub Actions روی Clone تازه با Git LFS این مسیر را برای هر هفت دمو اجرا و تأیید کرده است:
- `git lfs fsck`: OK
- nail: VALID
- clinic: VALID
- hair: VALID
- spa: VALID
- lashes: VALID
- makeup: VALID
- barber: VALID
- `ALL_SEVEN_DEMOS_VALID`

جزئیات ماشین‌خوان در `reconstruction/CURRENT-RECONSTRUCTION-AUDIT.json` است.

## امنیت
Repository عمومی است؛ credential، secret، salt/key قابل‌استفاده، hash ورود واقعی، OTP transient و SMS runtime log عمداً وارد آن نشده‌اند.

## مرز ادعا
آرشیوهای تاریخی exactِ root و شش live-install غیر-Nail در ابزار فعلی byte-accessible نیستند و ساختگی بازسازی نشده‌اند. این موضوع **مانع ادامه توسعه جاری در Arena نیست** چون سورس کامل فعلی، هفت DB امن، Runtime Base و بازساز Cloud-verified موجودند؛ اما برای ادعای «restore بیت‌به‌بیت نصب تاریخی» همچنان باید فایل‌های اصل تاریخی دوباره در دسترس قرار گیرند.
