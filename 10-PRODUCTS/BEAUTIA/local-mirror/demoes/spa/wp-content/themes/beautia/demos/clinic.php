<?php
/**
 * Demo content: Aesthetic & Medical Clinic (کلینیک زیبایی و پوست).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

return array(
	'services'     => array(
		array( 'title' => 'هیدرافیشیال پاک‌سازی عمیق', 'content' => 'پاک‌سازی گردابی در سطح پزشکی، لایه‌برداری ملایم اسیدی، تخلیه منافذ و تزریق آنتی‌اکسیدان، همه در یک جلسه ۶۰ دقیقه‌ای.', 'price' => 2400000, 'duration' => 60, 'icon' => 'droplet', 'category' => 'پوست', 'image' => 'svc-clinic-1.jpg', 'highlights' => array( 'بدون دوره نقاهت', 'درخشش قابل مشاهده در همان روز', 'ایمن برای پوست حساس' ) ),
		array( 'title' => 'بوتاکس — یک‌سوم بالای صورت', 'content' => 'تزریق در پیشانی، خط اخم و پنجه‌کلاغی توسط پزشک دارای مجوز، با رویکرد حفظ حالت طبیعی چهره.', 'price' => 3900000, 'duration' => 30, 'icon' => 'syringe', 'category' => 'تزریقات', 'image' => '', 'highlights' => array( 'انجام توسط پزشک', 'نتیجه در ۵ تا ۷ روز', 'ویزیت بازبینی رایگان پس از دو هفته' ) ),
		array( 'title' => 'فیلر لب', 'content' => 'حجم‌دهی و کانتور لب با اسید هیالورونیک به روش کانولا، برای کبودی حداقلی و نتیجه‌ای نرم و آبرسان.', 'price' => 5600000, 'duration' => 45, 'icon' => 'lips', 'category' => 'تزریقات', 'image' => '', 'deposit' => 1000000, 'aftercare' => array( 'تا ۴۸ ساعت از ورزش سنگین، سونا و ماساژ صورت پرهیز کنید.', 'تا ۲۴ ساعت روی ناحیه تزریق آرایش نکنید.', 'کمپرس سرد ملایم به کاهش ورم کمک می‌کند.', 'شب اول با سر بالاتر از سطح بدن بخوابید.' ), 'highlights' => array( 'تکنیک کانولا', 'ماده قابل برگشت', 'بی‌حسی رایگان' ) ),
		array( 'title' => 'لیزر موهای زائد — تمام بدن', 'content' => 'لیزر دایود ۸۰۸ نانومتر با سیستم خنک‌کننده تماسی، مناسب همه انواع پوست.', 'price' => 4200000, 'duration' => 90, 'icon' => 'flame', 'category' => 'لیزر', 'image' => '', 'highlights' => array( 'مناسب همه تیپ‌های پوستی', 'هندپیس خنک‌کننده', 'امکان خرید پکیج' ) ),
		array( 'title' => 'میکرونیدلینگ + پی‌آرپی', 'content' => 'کلاژن‌سازی به همراه پلاسمای غنی از پلاکت خودتان، برای اسکار، بافت پوست و سفتی.', 'price' => 3100000, 'duration' => 75, 'icon' => 'sparkle', 'category' => 'پوست', 'image' => '', 'highlights' => array( 'پروتکل جای جوش', 'پی‌آرپی از خون خودتان', 'برنامه سه‌جلسه‌ای' ) ),
		array( 'title' => 'ویزیت و مشاوره پزشک', 'content' => 'بیست دقیقه آنالیز پوست و طراحی برنامه درمانی با متخصص پوست. هزینه ویزیت از اولین درمان کسر می‌شود.', 'price' => 500000, 'duration' => 20, 'icon' => 'stethoscope', 'category' => 'مشاوره', 'image' => '', 'highlights' => array( 'اسکن دیجیتال پوست', 'برنامه درمانی مکتوب' ) ),
	),
	'staff'        => array(
		array( 'name' => 'دکتر لیلا فرهادی', 'role' => 'متخصص پوست و مو', 'bio' => 'متخصص بورد پوست با فوق‌تخصص در تزریقات و پزشکی لیزر.', 'experience' => '15', 'image' => 'team-3.jpg' ),
		array( 'name' => 'دکتر آرمان نبوی', 'role' => 'پزشک زیبایی', 'bio' => 'متمرکز بر هارمونیزاسیون طبیعی صورت و لیفت بدون جراحی.', 'experience' => '10', 'image' => '' ),
		array( 'name' => 'نیلوفر قدیری', 'role' => 'کارشناس زیبایی بالینی', 'bio' => 'متخصص هیدرافیشیال و میکرونیدلینگ، وسواسی روی سلامت سد دفاعی پوست.', 'experience' => '7', 'image' => 'team-1.jpg' ),
	),
	'portfolio'    => array(
		array( 'title' => 'برنامه درمان اسکار آکنه — سه جلسه', 'category' => 'پوست', 'image' => 'hero-clinic.jpg', 'content' => 'میکرونیدلینگ همراه با پی‌آرپی.' ),
		array( 'title' => 'کانتور طبیعی لب', 'category' => 'تزریقات', 'image' => 'svc-clinic-1.jpg', 'content' => '۰.۸ سی‌سی اسید هیالورونیک با کانولا.' ),
		array( 'title' => 'پروتکل درخشندگی', 'category' => 'پوست', 'image' => '', 'content' => 'هیدرافیشیال به همراه مزوتراپی.' ),
	),
	'gallery'      => array(
		array( 'title' => 'هیدرافیشیال، یک جلسه', 'before' => 'svc-clinic-6.jpg', 'after' => 'svc-clinic-1.jpg' ),
		array( 'title' => 'میکرونیدلینگ و پی‌آرپی', 'before' => 'svc-clinic-6.jpg', 'after' => 'svc-clinic-5.jpg' ),
	),
	'testimonials' => array(
		array( 'name' => 'شیرین الف.', 'role' => 'هیدرافیشیال', 'rating' => 5, 'text' => 'حرفه‌ای، آرام و واقعاً صادق — حتی مرا از انجام درمانی که لازم نداشتم منصرف کردند.' ),
		array( 'name' => 'مونا ت.', 'role' => 'بوتاکس', 'rating' => 5, 'text' => 'نتیجه کاملاً طبیعی، بدون حالت یخ‌زده. پیامک یادآوری قبل از ویزیت بازبینی هم نکته قشنگی بود.' ),
		array( 'name' => 'رضا ه.', 'role' => 'لیزر', 'rating' => 5, 'text' => 'شش جلسه و تقریباً هیچ رشد مجددی نداشتم. کادر همه مراحل را توضیح می‌دهند.' ),
	),
	'posts'        => array(
		array( 'title' => 'بعد از اولین جلسه فیلر چه انتظاری داشته باشیم؟', 'content' => 'ورم تا ۴۸ ساعت طبیعی است. این هم پروتکل کامل مراقبت بعد از تزریق، شامل کارهایی که باید از آن‌ها پرهیز کنید…', 'image' => 'hero-clinic.jpg' ),
		array( 'title' => 'ساختن یک روتین مراقبت پوست در سطح پزشکی', 'content' => 'شوینده، ویتامین C، رتینوئید و ضدآفتاب. بقیه اختیاری است. این هم ترتیب درست استفاده از آن‌ها…', 'image' => 'svc-clinic-1.jpg' ),
	),
	'pages'        => array(
		'home'      => array( 'title' => 'صفحه اصلی کلینیک', 'content' => "[beautia_hero]\n[beautia_about]\n[beautia_services count=\"6\"]\n[beautia_features]\n[beautia_stats]\n[beautia_team count=\"3\"]\n[beautia_before_after]\n[beautia_steps]\n[beautia_testimonials]\n[beautia_faq]\n[beautia_blog count=\"3\"]" ),
		'about'     => array( 'title' => 'درباره کلینیک', 'content' => '<p>کلینیکی زیر نظر پزشک، با استریلیزاسیون در سطح بیمارستانی و پروتکل‌های مبتنی بر شواهد علمی.</p>[beautia_stats][beautia_team count="3"]' ),
		'services'  => array( 'title' => 'درمان‌ها', 'content' => '[beautia_services count="12"][beautia_pricing]' ),
		'portfolio' => array( 'title' => 'نتایج درمان', 'content' => '[beautia_before_after][beautia_portfolio count="24"]' ),
		'contact'   => array( 'title' => 'تماس با ما', 'content' => '[beautia_hours][beautia_track]' ),
		'blog'      => array( 'title' => 'راهنمای مراجعان', 'content' => '' ),
	),
	'menu'         => array(
		array( 'title' => 'خانه', 'page' => 'home' ),
		array( 'title' => 'درباره ما', 'page' => 'about' ),
		array( 'title' => 'درمان‌ها', 'page' => 'services' ),
		array( 'title' => 'نتایج', 'page' => 'portfolio' ),
		array( 'title' => 'راهنماها', 'page' => 'blog' ),
		array( 'title' => 'تماس', 'page' => 'contact' ),
		array( 'title' => 'رزرو مشاوره', 'page' => '', 'option_page' => 'booking' ),
	),
	'options'      => array( 'currency_symbol' => 'تومان', 'slot_step' => 20, 'cancel_hours' => 24, 'auto_confirm' => 0, 'address' => 'تهران، خیابان فرشته، برج پزشکان، طبقه چهارم', 'phone' => '۰۲۱-۲۲۰۰۳۳۴۴' ),
);
