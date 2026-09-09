# arena-rastchin

مخزن انتقال دانش، منابع رسمی، فایل‌های محصول و دستورالعمل‌های پروژه فروش قالب/افزونه در راست‌چین برای ادامه کار با Arena.ai.

## شروع سریع

Arena ابتدا `00-START-HERE/ARENA-BOOTSTRAP-PROMPT-FA.md` را بخواند.

## ساختار

- `00-START-HERE/` راهنمای شروع و Source of Truth
- `01-PROJECT-RULES/` دستورالعمل مادر توسعه/QA/Release
- `02-RTL-THEME-OFFICIAL/` منابع رسمی، ویدیوها و مرجع استاندارد راست‌چین
- `10-PRODUCTS/ZARPULS/` وضعیت و اسناد زرپالس
- `10-PRODUCTS/BEAUTIA/` Handoff، QA، دیتابیس‌ها و آرشیو دموی بیوتیا
- `10-PRODUCTS/LORANIQ-ADMIN/` وضعیت و مشخصات لورانیک ادمین
- `10-PRODUCTS/MR-SEO/` وضعیت اولیه افزونه آقای سئو
- `90-MANIFESTS/` فهرست انتقال و محدودیت‌ها

## نکته امنیتی

فایل‌های دارای credential، salt، wp-config توسعه‌ای، لاگ حساس یا داده شخصی نباید وارد نسخه عمومی شوند. برای بیوتیا از آرشیو sanitize‌شده استفاده می‌شود.

## فایل‌های حجیم

`.gitattributes` برای Git LFS آماده شده است. فایل‌های خیلی حجیم که از اتصال فعلی GitHub قابل انتقال مستقیم نباشند در Manifest مشخص می‌شوند تا Arena یا یک محیط Git/LFS آن‌ها را sync کند.
