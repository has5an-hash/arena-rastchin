<?php
/**
 * Demo content: Makeup Artist & Academy (میکاپ آرتیست و آکادمی).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

return array(
	'services'     => array(
		array( 'title' => 'میکاپ عروس', 'content' => 'گریم کامل عروس همراه با جلسه پیش‌آزمون، بیس ایربراش و تست ماندگاری ۱۴ ساعته.', 'price' => 7500000, 'duration' => 120, 'icon' => 'lips', 'category' => 'میکاپ', 'image' => 'svc-makeup-1.jpg', 'highlights' => array( 'پیش‌آزمون رایگان', 'بیس ایربراش', 'هدیه کیت ترمیم آرایش' ) ),
		array( 'title' => 'میکاپ مجلسی و شب', 'content' => 'یک گریم کامل، ساخته‌شده برای فلاش دوربین و شب‌های طولانی.', 'price' => 2200000, 'duration' => 75, 'icon' => 'sparkle', 'category' => 'میکاپ', 'image' => 'svc-makeup-2.jpg' ),
		array( 'title' => 'میکاپ ادیتوریال و عکاسی', 'content' => 'گریم مفهومی برای لوک‌بوک، کمپین و نمونه‌کار حرفه‌ای.', 'price' => 3800000, 'duration' => 90, 'icon' => 'camera', 'category' => 'میکاپ', 'image' => 'glam-portrait.jpg' ),
		array( 'title' => 'کلاس خصوصی آرایش شخصی', 'content' => 'دو ساعت آموزش خصوصی روی صورت خودتان، به‌همراه لیست مکتوب محصولات پیشنهادی.', 'price' => 2900000, 'duration' => 120, 'icon' => 'brush', 'category' => 'آکادمی', 'image' => 'hero-makeup.jpg' ),
		array( 'title' => 'دوره حرفه‌ای میکاپ آرتیستی', 'content' => 'دوره ده‌جلسه‌ای دارای مدرک: نظریه رنگ، آماده‌سازی پوست، اصلاح فرم، عروس و ادیتوریال.', 'price' => 38000000, 'duration' => 180, 'icon' => 'award', 'category' => 'آکادمی', 'image' => '' ),
		array( 'title' => 'فیشیال آماده‌سازی پوست', 'content' => 'سی دقیقه آماده‌سازی پیش از مراسم تا بیس آرایش کاملاً یکدست بنشیند.', 'price' => 900000, 'duration' => 30, 'icon' => 'droplet', 'category' => 'میکاپ', 'image' => '' ),
	),
	'staff'        => array(
		array( 'name' => 'بهار انصاری', 'role' => 'بنیان‌گذار و میکاپ آرتیست ارشد', 'bio' => 'پانزده سال کار در حوزه عروس و ادیتوریال، با آثار منتشرشده در سه مجله.', 'experience' => '15', 'image' => 'team-2.jpg' ),
		array( 'name' => 'ملیکا شریفی', 'role' => 'مدرس آکادمی', 'bio' => 'مدرس نظریه رنگ و تکنیک‌های اصلاح فرم صورت.', 'experience' => '9', 'image' => 'team-5.jpg' ),
	),
	'portfolio'    => array(
		array( 'title' => 'گریم نرم عروس', 'category' => 'عروس', 'image' => 'svc-makeup-1.jpg', 'content' => 'عروس با پالت نود و بیس گلَس اسکین.' ),
		array( 'title' => 'قرمز ادیتوریال', 'category' => 'ادیتوریال', 'image' => 'svc-makeup-2.jpg', 'content' => 'رژ قرمز گرافیکی و پلک براق.' ),
	),
	'gallery'      => array(
		array( 'title' => 'میکاپ مجلسی', 'before' => 'svc-makeup-2.jpg', 'after' => 'svc-makeup-1.jpg' ),
	),
	'testimonials' => array(
		array( 'name' => 'کیمیا ل.', 'role' => 'عروس', 'rating' => 5, 'text' => 'چهارده ساعت، سه بار گریه، و حتی یک بار هم نیاز به ترمیم نشد.' ),
		array( 'name' => 'دنیا س.', 'role' => 'دوره میکاپ', 'rating' => 5, 'text' => 'چهار ماه بعد از فارغ‌التحصیلی سالن خودم را افتتاح کردم.' ),
	),
	'posts'        => array(
		array( 'title' => 'چطور یک کیت آرایش شروع‌کننده بسازیم که واقعاً کار کند', 'content' => 'ده محصول، سه براش. بقیه می‌توانند صبر کنند تا وقتی مشتری ثابت داشته باشید…', 'image' => 'svc-makeup-1.jpg' ),
	),
	'pages'        => array(
		'home'      => array( 'title' => 'صفحه اصلی استودیو میکاپ', 'content' => "[beautia_hero]\n[beautia_about]\n[beautia_services count=\"6\"]\n[beautia_before_after]\n[beautia_portfolio count=\"9\"]\n[beautia_stats]\n[beautia_team count=\"2\"]\n[beautia_pricing]\n[beautia_steps]\n[beautia_testimonials]\n[beautia_faq]\n[beautia_blog count=\"3\"]" ),
		'academy'   => array( 'title' => 'آکادمی', 'content' => '<p>آموزش تخصصی آرایش در گروه‌های کوچک، با مدل واقعی و ارائه مدرک.</p>[beautia_services category="akademy" count="6"]' ),
		'portfolio' => array( 'title' => 'نمونه‌کارها', 'content' => '[beautia_portfolio count="24"]' ),
		'contact'   => array( 'title' => 'تماس با ما', 'content' => '[beautia_hours][beautia_track]' ),
		'blog'      => array( 'title' => 'وبلاگ', 'content' => '' ),
	),
	'menu'         => array(
		array( 'title' => 'خانه', 'page' => 'home' ),
		array( 'title' => 'آکادمی', 'page' => 'academy' ),
		array( 'title' => 'نمونه‌کارها', 'page' => 'portfolio' ),
		array( 'title' => 'وبلاگ', 'page' => 'blog' ),
		array( 'title' => 'تماس', 'page' => 'contact' ),
		array( 'title' => 'رزرو نوبت', 'page' => '', 'option_page' => 'booking' ),
	),
	'options'      => array( 'currency_symbol' => 'تومان', 'slot_step' => 30, 'cancel_hours' => 48, 'address' => 'تهران، بلوار میرداماد، پلاک ۱۱۲', 'phone' => '۰۲۱-۲۲۷۷۹۰۹۰' ),
);
