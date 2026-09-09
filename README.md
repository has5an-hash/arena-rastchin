# arena-rastchin

مخزن انتقال دانش، منابع رسمی، سورس‌های محصول و دستورالعمل‌های پروژه فروش قالب/افزونه در راست‌چین برای ادامه کار با Arena.ai.

## شروع سریع Arena

اول `AGENTS.md` و سپس `00-START-HERE/ARENA-READ-FIRST-FA.md` را بخوان.

## ساختار

- `00-START-HERE/` راهنمای شروع، فهرست محصولات و Source of Truth
- `01-PROJECT-RULES/` دستورالعمل مادر توسعه، Security، QA و Release
- `02-RTL-THEME-OFFICIAL/` استاندارد رسمی پروژه، لینک‌های زنده و دانش استخراج‌شده از آموزش‌های رسمی راست‌چین
- `10-PRODUCTS/ZARPULS/` وضعیت، راهنمای کاربر، Readme و Repository دارایی عمومی زرپالس؛ سورس کامل توسعه در انتقال حاضر اثبات/موجود نیست
- `10-PRODUCTS/BEAUTIA/` Handoff، Checkpoint، QA، راهنمای فنی و سورس‌های قابل‌انتقال بیوتیا
- `10-PRODUCTS/LORANIQ-ADMIN/` وضعیت، Build Spec و سورس کامل جاری به‌شکل Submodule
- `10-PRODUCTS/MR-SEO/` وضعیت اولیه افزونه آقای سئو
- `20-NEW-PRODUCT-TEMPLATE/` الگوی ساخت محصول جدید با Arena
- `90-MANIFESTS/` وضعیت انتقال، محدودیت‌ها و سیاست امنیت

## دریافت Repositoryهای لینک‌شده

```bash
git clone --recurse-submodules https://github.com/has5an-hash/arena-rastchin.git
# یا داخل Clone موجود:
git submodule update --init --recursive
```

Submodule زرپالس در حال حاضر فقط Repository عمومی دارایی موجود را منعکس می‌کند و نباید به‌عنوان سورس کامل افزونه فرض شود.

## قانون مرجع

در تعارض اطلاعات: قوانین به‌روز راست‌چین → منابع رسمی پروژه → آخرین دستور معتبر مالک → آخرین تصمیم/Checkpoint محصول. هیچ قابلیت یا نتیجه QA از روی README یا حدس پذیرفته نیست.

## امنیت مخزن عمومی

این Repository عمداً Public است. credential، password، salt، API key، token، داده شخصی، `wp-config.php` توسعه‌ای و log حساس نباید وارد آن شوند. نسخه‌های sanitize‌شده/مستندات امن جایگزین می‌شوند.

## فایل‌های باینری حجیم

اتصال GitHub مورد استفاده برای این انتقال امکان Push مستقیم فایل محلی حجیم/LFS را ندارد. فایل‌های لازمِ قابل‌خواندن، دانش استخراج‌شده و سورس‌های موجود منتقل/لینک شده‌اند و موارد باینری باقی‌مانده دقیقاً در `90-MANIFESTS/` ثبت می‌شوند؛ هیچ موردی نباید به اشتباه «آپلودشده» اعلام شود.
