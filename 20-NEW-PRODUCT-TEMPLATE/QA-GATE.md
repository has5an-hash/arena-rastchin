# QA Gate محصول راست‌چین

هر مورد باید با شواهد واقعی PASS شود؛ «کد را نگاه کردم» جای تست اجراشده نیست.

## Technical
- نصب از صفر
- فعال‌سازی/غیرفعال‌سازی/حذف بدون خطای مهم
- PHP/JS/HTML/CSS standards
- Database/REST/AJAX/Cron/Queue
- خطاهای PHP/Console/Network

## Security
- sanitize + validate + escape
- nonce + capability/permission
- prepared SQL
- XSS/CSRF/SQLi
- Upload/API/secret/token handling
- Rate limiting/auth/session در صورت ارتباط

## Visual / RTL / Responsive
- Desktop / Tablet / Mobile
- alignment / spacing / grid / typography
- RTL فارسی صحیح
- overflow / overlap / z-index
- menu / modal / dropdown / sticky/fixed
- normal / hover / focus / active / disabled / loading / error / empty
- contrast و accessibility

## Performance
- تصاویر/فونت/CSS/JS/query/cache/lazy load
- درخواست خارجی غیرضروری صفر یا توجیه‌شده
- بررسی Core Web Vitals در حد قابل اندازه‌گیری محصول

## Integration
- WordPress/WooCommerce/Elementor فقط نسخه‌هایی که واقعاً تست شده‌اند
- بدون وابستگی پولی اجباری مگر تأییدشده

## Installation / Package
- حذف log/cache/backup/test credential
- نسخه‌ها هماهنگ
- ZIP structure صحیح
- Restore/installer واقعی در صورت وجود
- تست قابلیت اصلی پس از نصب مجدد

## RTLTheme
- منابع رسمی پروژه خوانده شده
- قوانین زنده مرتبط دوباره بررسی شده
- License flow رسمی
- صفحه فروش فقط قابلیت اثبات‌شده
- تصاویر/AI/نام/فایل‌ها/لینک‌ها/پشتیبانی مطابق قانون جاری

## نتیجه
- Critical blockers: 0
- High blockers: 0
- وضعیت نهایی: PASS فقط با شواهد ثبت‌شده
