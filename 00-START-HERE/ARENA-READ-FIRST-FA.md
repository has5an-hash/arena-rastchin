# اول این فایل را بخوان — تحویل پروژه به Arena

این Repository برای ادامه پروژه‌های فروش در راست‌چین از ChatGPT به Arena آماده شده است.

## ترتیب شروع
1. `../AGENTS.md`
2. `ARENA-BOOTSTRAP-PROMPT-FA.md`
3. `../01-PROJECT-RULES/MASTER-PROJECT-INSTRUCTIONS-FA.md`
4. `../02-RTL-THEME-OFFICIAL/استانداردهای-انتشار-محصول-در-راستچین-مرجع-پروژه.md`
5. `SOURCE-OF-TRUTH-FA.md`
6. `PRODUCT-INDEX-FA.md`

بعد پوشه محصول را بخوان.

## شروع بیوتیا
برای بیوتیا:
1. `../10-PRODUCTS/BEAUTIA/README-TRANSFER-FA.md`
2. `../10-PRODUCTS/BEAUTIA/docs/CHECKPOINT.json`
3. `../10-PRODUCTS/BEAUTIA/docs/PROJECT-HANDOFF-FA.parts/README.md` و سپس همه partها به ترتیب
4. `../10-PRODUCTS/BEAUTIA/docs/WORK-LOG-FA.md`
5. `../10-PRODUCTS/BEAUTIA/BEAUTIA-TRANSFER-MANIFEST.json`
6. `../10-PRODUCTS/BEAUTIA/reconstruction/README-FA.md`

اگر محیط اجرایی ۷ دمو لازم است، از `reconstruction/materialize-demo.py` استفاده کن. این مسیر در GitHub Actions روی Clone تازه همراه Git LFS برای هر هفت دمو Validate شده است.

## وضعیت فایل‌های بزرگ
فایل‌های باینری لازم و قابل‌انتقال با hash کنترل شده‌اند و موارد حجیم روی Git LFS قرار دارند. هیچ فایل حساس unsanitized نباید از روی Manifest بازسازی یا پابلیک شود.

## قاعده ادامه
هیچ‌وقت صرفاً بر اساس README یا Changelog ادعای قابلیت نکن. کد، QA و آخرین checkpoint را با هم تطبیق بده. اگر وضعیت نامطمئن است، آن را «نیازمند تأیید» ثبت کن.

آرشیوهای historical exact قدیمی که بایت اصلی‌شان در دسترس نیست، با خروجی reconstructed-current اشتباه گرفته نشوند.
