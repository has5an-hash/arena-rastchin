# بازسازی دموهای فعلی بیوتیا برای Arena

این پوشه برای ساخت محیط اجرایی فعلی هر ۷ دمو بدون وابستگی به کامپیوتر مالک است.

## ورودی‌های مرجع
- `../archives/beautia-nail-sanitized.zip`: Runtime Base امن و کامل وردپرس.
- `../archives/beautia-databases-sanitized.zip`: SQL امن هر ۷ دمو.
- `../source/`: سورس فعلی قالب و Beautia Core.

## اجرا
Python 3 و Pillow لازم است:

```bash
python -m pip install Pillow
python materialize-demo.py \
  --base ../archives/beautia-nail-sanitized.zip \
  --dbs ../archives/beautia-databases-sanitized.zip \
  --demo all \
  --out ./runtime
```

برای یک دمو، مقدار `--demo` یکی از `nail clinic hair spa lashes makeup barber` باشد.

اسکریپت برای هر دمو:
1. Runtime Base امن را استخراج می‌کند.
2. `DB_NAME`، `WP_HOME`، `WP_SITEURL` و RewriteBase را برای همان دمو تنظیم می‌کند.
3. uploads قدیمی Nail را حذف می‌کند.
4. Media Library موردنیاز دیتابیس همان دمو را از تصاویر واقعی سورس مشترک بازسازی می‌کند.
5. اندازه‌های تصویری اشاره‌شده در SQL را تولید می‌کند.
6. SQL همان دمو را داخل `_database/` قرار می‌دهد.
7. `CURRENT-RECONSTRUCTION-MANIFEST.json` می‌سازد.

## نتیجه ممیزی
اجرای واقعی روی هر ۷ دمو انجام شد. همه ۷ مورد `valid-current-reconstruction` شدند:
- missing attachment: صفر
- unmapped media: صفر

جزئیات در `CURRENT-RECONSTRUCTION-AUDIT.json` ثبت شده است.

## مرز ادعا
این خروجی‌ها **آرشیو تاریخی exact نیستند** و نباید جای `beautia-demo-*-complete.zip`های تاریخی معرفی شوند. آرشیوهای historical فقط وقتی exact محسوب می‌شوند که بایت اصلی آن فایل‌ها دوباره در دسترس قرار گیرد.

این کیت برای ادامه توسعه/QA در Arena است، نه اثبات restore بیت‌به‌بیت محیط تاریخی.
