# Arena Agent Instructions — پروژه فروش محصولات راست‌چین

این مخزن مرجع انتقال پروژه «فروش قالب و افزونه در راست‌چین» به Arena است.

## قبل از هر کاری
1. `00-START-HERE/ARENA-BOOTSTRAP-PROMPT-FA.md`
2. `01-PROJECT-RULES/MASTER-PROJECT-INSTRUCTIONS-FA.md`
3. `02-RTL-THEME-OFFICIAL/استانداردهای-انتشار-محصول-در-راستچین-مرجع-پروژه.md`
4. `00-START-HERE/SOURCE-OF-TRUTH-FA.md`
5. مرجع همان محصول.

## اصول قطعی
- هیچ قابلیت، نسخه، سازگاری، نتیجه تست یا وضعیت انتشار را حدس نزن.
- قوانین به‌روز راست‌چین در اولویت‌اند؛ برای موضوعات متغیر نسخه زنده سایت رسمی را هم بررسی کن.
- اگر درخواست مالک با استاندارد راست‌چین تعارض داشت، قبل از اجرا هشدار بده و نسخه سازگار پیشنهاد کن.
- از آخرین checkpoint معتبر ادامه بده و کار انجام‌شده را بی‌دلیل از صفر تکرار نکن.
- Security، Performance، RTL/Persian، Responsive، UX/UI، Accessibility، Installation، Packaging، License و Marketplace QA بخشی از Definition of Done هستند.
- محصول فقط پس از Gate نهایی واقعی «آماده انتشار» است.
- secret، password، salt، API key، داده شخصی، log حساس و credential نباید وارد مخزن عمومی شوند.
- برای صفحه فروش و تصاویر مارکت فقط قابلیت‌های واقعی و اثبات‌شده استفاده شود و محدودیت‌های جاری راست‌چین درباره AI دوباره بررسی شوند.

## محصولات مرجع
- زرپالس: `10-PRODUCTS/ZARPULS/`
- بیوتیا: `10-PRODUCTS/BEAUTIA/` — برای محیط اجرایی ۷ دمو ابتدا `README-TRANSFER-FA.md` و سپس `reconstruction/README-FA.md` را بخوان؛ بازساز ۷ دمو در GitHub Actions Cloud-verified است.
- آقای سئو: `10-PRODUCTS/MR-SEO/`
- **لورانیک ادمین: فقط `https://github.com/has5an-hash/loraniq-admin`**

### قانون قطعی Loraniq Admin
برای لورانیک هیچ فایل کپی‌شده، ZIP، Build Spec، Snapshot یا Submodule در این Repository را مرجع ندان. مستقیماً Repository `has5an-hash/loraniq-admin` را Clone/Read کن و از آخرین وضعیت همان Repository ادامه بده.

## هدف Arena
Arena می‌تواند محصول جدید بسازد یا محصولات موجود را ادامه دهد، اما باید وضعیت واقعی فایل‌ها و منابع مرجع را مبنا بگیرد. برای محصول جدید از ساختار و Gateهای پروژه استفاده کن؛ از ادعا یا قابلیت ساختگی برای سریع‌ترشدن انتشار استفاده نکن.
