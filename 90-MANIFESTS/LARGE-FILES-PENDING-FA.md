# وضعیت فایل‌های حجیم پس از انتقال

آخرین به‌روزرسانی: 2026-09-10

## منتقل‌شده و Verify شده

### منابع رسمی راست‌چین
- `02-RTL-THEME-OFFICIAL/ویدیوی آموزش کامل تمام نکات مهم برای فروشندگی افزونه و قالب در راستچین.mp4` — Git LFS، SHA-256 تأیید شده.
- `02-RTL-THEME-OFFICIAL/راهنمای لایسنس گذاری راستچین.webm` — Git LFS، SHA-256 تأیید شده.
- `02-RTL-THEME-OFFICIAL/مرجع-ویدیوهای-راستچین.zip` — Git LFS، SHA-256 تأیید شده.
- فایل MD و DOCX استانداردهای انتشار نیز موجود هستند.

### بیوتیا
- `10-PRODUCTS/BEAUTIA/archives/beautia-nail-sanitized.zip` — 108,964,494 bytes، SHA-256: `8d1d680f8636d2fc784d41b4f0b72c1ca088a2079b811e6c21e73b660b95e609`.
- `10-PRODUCTS/BEAUTIA/archives/beautia-source-current.zip` — 50,064,947 bytes، SHA-256: `1f5b957a5f3540d266edf4b313c2be88806ae3509bf09cabb71685537b247fce`.
- `10-PRODUCTS/BEAUTIA/archives/beautia-databases-sanitized.zip` — 212,850 bytes، SHA-256: `a61a6c07552b28a3d8fec7a6e47ff278da6a28460db5c560222feaa9d08108bc`.
- `10-PRODUCTS/BEAUTIA/beautia-docs-complete.zip` — 484,251 bytes، SHA-256: `6e715a5c9189e306e65bf2995261cdb27c7ab010f44d77ea90e5dca7448f0a64`.
- `10-PRODUCTS/BEAUTIA/docs/FULL-FILE-MANIFEST.csv` — 3,725,515 bytes، SHA-256: `880646d1901b276860d1fcb61c43827868a6d557a89e680b2981a31b05700675`.
- سورس قابل‌ویرایش فعلی نیز در `10-PRODUCTS/BEAUTIA/source/` با 293 فایل قرار دارد.

## امنیت نسخه Public
نسخه خام `nail.zip` و دیتابیس خام عمداً Public نشده‌اند. salt/keyهای wp-config، hash ورود، OTP transientها و SMS runtime log از نسخه انتقالی حذف/خنثی شده‌اند.

## آرشیوهای exact قدیمی که بایت آن‌ها در ابزار فعلی در دسترس نیست
- `beautia-root-complete.zip`
- `beautia-demo-clinic-complete.zip`
- `beautia-demo-hair-complete.zip`
- `beautia-demo-spa-complete.zip`
- `beautia-demo-lashes-complete.zip`
- `beautia-demo-makeup-complete.zip`
- `beautia-demo-barber-complete.zip`

وجود نام این فایل‌ها در `UPLOAD-MANIFEST-FA.md` نشان می‌دهد قبلاً در Sources پروژه ثبت شده بودند، اما در این نشست بایت فایل‌ها از File Library/Drive قابل دریافت نیست. این فایل‌ها نباید حدس زده یا با بسته ساختگی جایگزین شوند.

برای ادامه توسعه Arena، سورس کامل فعلی + دیتابیس‌های sanitize‌شده هفت دمو + Nail زنده sanitize‌شده + اسناد و Full File Manifest اکنون در Repo موجود است.
