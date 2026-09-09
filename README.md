# arena-rastchin

مخزن انتقال دانش، منابع رسمی، سورس‌های محصول و دستورالعمل‌های پروژه فروش قالب/افزونه در راست‌چین برای ادامه کار با Arena.ai.

## شروع سریع Arena

اول `AGENTS.md` و سپس `00-START-HERE/ARENA-READ-FIRST-FA.md` را بخوان.

## ساختار

- `00-START-HERE/` راهنمای شروع، فهرست محصولات و Source of Truth
- `01-PROJECT-RULES/` دستورالعمل مادر توسعه، Security، QA و Release
- `02-RTL-THEME-OFFICIAL/` استاندارد رسمی پروژه، لینک‌های زنده و دانش استخراج‌شده از آموزش‌های رسمی راست‌چین
- `10-PRODUCTS/ZARPULS/` وضعیت و اطلاعات زرپالس
- `10-PRODUCTS/BEAUTIA/` Handoff، Checkpoint، QA، راهنمای فنی و سورس‌های بیوتیا
- `10-PRODUCTS/MR-SEO/` وضعیت اولیه افزونه آقای سئو
- `20-NEW-PRODUCT-TEMPLATE/` الگوی ساخت محصول جدید با Arena
- `90-MANIFESTS/` وضعیت انتقال، checksumها، محدودیت‌ها و سیاست امنیت

## Loraniq Admin

لورانیک عمداً داخل این Repository نگهداری نمی‌شود. **تنها منبع زنده و مرجع فایل‌های Loraniq Admin این Repository است:**

```text
https://github.com/has5an-hash/loraniq-admin
```

Arena برای هر کار مربوط به لورانیک باید مستقیماً همان Repo را Clone/Read کند و از آخرین وضعیت آن ادامه دهد.

## دریافت Repositoryهای لینک‌شده

```bash
git clone --recurse-submodules https://github.com/has5an-hash/arena-rastchin.git
# یا داخل Clone موجود:
git submodule update --init --recursive
```

Submodule زرپالس در حال حاضر فقط Repository عمومی موجود را منعکس می‌کند و نباید بدون بررسی به‌عنوان سورس کامل افزونه فرض شود.

## قانون مرجع

در تعارض اطلاعات: قوانین به‌روز راست‌چین → منابع رسمی پروژه → آخرین دستور معتبر مالک → آخرین تصمیم/Checkpoint محصول. هیچ قابلیت یا نتیجه QA از روی README یا حدس پذیرفته نیست.

## امنیت مخزن عمومی

این Repository عمداً Public است. credential، password، salt، API key، token، داده شخصی، `wp-config.php` توسعه‌ای و log حساس نباید وارد آن شوند. نسخه‌های sanitize‌شده/مستندات امن جایگزین می‌شوند.

## فایل‌های باینری حجیم

برای باینری‌های حجیم، فقط زمانی وضعیت «Sync شده» ثبت می‌شود که بایت‌های واقعی و SHA-256 فایل مقصد با فایل مرجع تطبیق داده شوند. فایل‌های chunk شده باید با Manifest و دستور بازسازی قابل بازیابی بیت‌به‌بیت باشند.
