# راهنمای انتشار این مخزن روی GitHub

## وضعیت فعلی

مقصد: `has5an-hash/arena-rastchin`
وضعیت طبق تصمیم مالک: Repository **Public** است. بنابراین فقط فایل‌های sanitize‌شده و بدون secret باید Push شوند.

این بسته شامل فایل‌های حجیم و باینری است. برای GitHub، Git LFS لازم است؛ فایل `.gitattributes` از قبل الگوهای ZIP/Video/PDF/Image را برای LFS مشخص کرده است.

## مراحل استاندارد برای فایل‌های حجیم

روی سیستمی که Git و Git LFS دارد:

```bash
git lfs install
git clone https://github.com/has5an-hash/arena-rastchin.git
cd arena-rastchin
git add .
git commit -m "Sync large Arena Rastchin assets"
git push
```

پوشه `98-PRIVATE-LOCAL-ONLY/` نباید commit شود. قبل از push با `git status` تأیید کنید که فایل حساس stage نشده باشد.

## نکته مهم درباره حجم

`beautia-nail-sanitized.zip` بیش از 100MB است، بنابراین بدون Git LFS از GitHub عادی عبور نمی‌کند. ویدیوهای آموزشی نیز باید با LFS یا Storage مناسب sync شوند.
