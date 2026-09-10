# بیوتیا — انتقال به Arena

مرجع اصلی: چت `وضعیت نهایی قالب بیوتیا/تحویل از کامپیوتر`.

## آنچه اکنون در Repo موجود است
- سورس قابل‌ویرایش فعلی قالب + Beautia Core در `source/`.
- آرشیو همان سورس در `archives/beautia-source-current.zip`.
- نصب کامل Nail به‌صورت sanitize‌شده در `archives/beautia-nail-sanitized.zip`.
- SQL هر هفت دیتابیس به‌صورت sanitize‌شده در `archives/beautia-databases-sanitized.zip`.
- Full File Manifest در `docs/FULL-FILE-MANIFEST.csv`.
- Handoff، Checkpoint، Work Log، QA، راهنمای فنی، Changelog و سایر اسناد در `docs/`.
- بسته یکپارچه اسناد در `beautia-docs-complete.zip`.
- وضعیت hash/اندازه و فایل‌های unavailable در `BEAUTIA-TRANSFER-MANIFEST.json`.

## امنیت
Repository عمومی است؛ نسخه خام wp-config، credential، salt/key، hash ورود قابل استفاده، OTP transient و SMS runtime log عمداً وارد آن نشده‌اند.

## محدودیت دقیق
در Upload Manifest تاریخی نام root archive و هفت live-install archive ثبت شده است. بایت exact شش live-install غیر-Nail و root archive در ابزارهای فعلی قابل دریافت نیست. بنابراین آن‌ها ساختگی بازسازی نشده‌اند. Arena برای توسعه جاری باید از Source کامل + هفت DB sanitize‌شده + Nail sanitize‌شده استفاده کند و این محدودیت را برای هر ادعای «restore دقیق نصب تاریخی» در نظر بگیرد.
