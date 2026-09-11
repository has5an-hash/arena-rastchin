<?php
/**
 * Demo content: Hair Salon (سالن مو).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

return array(
	'services'     => array(
		array( 'title' => 'بالیاژ و گلاس', 'content' => 'روشن‌سازی نقاشی‌شده با دست به همراه گلاس تونر اختصاصی، برای یک عمق طبیعی و آفتاب‌خورده.', 'price' => 4800000, 'duration' => 180, 'buffer' => 20, 'icon' => 'brush', 'category' => 'رنگ', 'image' => 'svc-hair-1.jpg', 'highlights' => array( 'محافظ پیوند مو رایگان', 'تونر اختصاصی', 'رفرش گلاس رایگان بعد از شش هفته' ) ),
		array( 'title' => 'کوتاهی تخصصی و استایل', 'content' => 'کوتاهی خشک بر پایه مشاوره، متناسب با فرم صورت، تراکم مو و روشی که واقعاً در خانه موهایتان را حالت می‌دهید.', 'price' => 1200000, 'duration' => 60, 'icon' => 'scissors', 'category' => 'کوتاهی', 'image' => '', 'highlights' => array( 'تکنیک کوتاهی خشک', 'آموزش حالت‌دهی رایگان' ) ),
		array( 'title' => 'کراتینه صاف‌کننده', 'content' => 'درمان صاف‌کننده بدون فرمالدهید که وز مو را از بین می‌برد و زمان سشوار را تا چهار ماه نصف می‌کند.', 'price' => 3600000, 'duration' => 150, 'icon' => 'dryer', 'category' => 'درمان مو', 'image' => '', 'highlights' => array( 'بدون فرمالدهید', 'دوام تا چهار ماه' ) ),
		array( 'title' => 'شینیون عروس و جلسه پیش‌آزمون', 'content' => 'یک جلسه پیش‌آزمون به‌همراه آرایش مو در روز عروسی، با امکان حضور در محل.', 'price' => 6500000, 'duration' => 120, 'icon' => 'award', 'category' => 'مراسم', 'image' => '', 'highlights' => array( 'پیش‌آزمون رایگان', 'امکان اعزام به محل' ) ),
		array( 'title' => 'دتاکس پوست سر', 'content' => 'لایه‌برداری پاک‌کننده پوست سر، بخوردرمانی و پانزده دقیقه ماساژ.', 'price' => 900000, 'duration' => 45, 'icon' => 'leaf', 'category' => 'درمان مو', 'image' => '' ),
		array( 'title' => 'سشوار و فر موج‌دار', 'content' => 'سشوار سریع با موج‌های محافظت‌شده در برابر حرارت که سه روز دوام می‌آورد.', 'price' => 650000, 'duration' => 40, 'icon' => 'dryer', 'category' => 'حالت‌دهی', 'image' => '' ),
	),
	'staff'        => array(
		array( 'name' => 'دریا صدیقی', 'role' => 'کالریست ارشد', 'bio' => 'متخصص بالیاژ و اصلاح رنگ، آموزش‌دیده در میلان.', 'experience' => '13', 'image' => 'team-2.jpg' ),
		array( 'name' => 'کیان مرادی', 'role' => 'استایلیست ارشد', 'bio' => 'کوتاهی دقیق و معماری موی کوتاه.', 'experience' => '8', 'image' => '' ),
		array( 'name' => 'رؤیا امینی', 'role' => 'متخصص عروس', 'bio' => 'شینیون‌های مجلسی و عروس، با امکان حضور در محل مراسم.', 'experience' => '10', 'image' => 'team-1.jpg' ),
	),
	'portfolio'    => array(
		array( 'title' => 'بالیاژ آفتاب‌خورده', 'category' => 'رنگ', 'image' => 'hero-hair.jpg', 'content' => 'مسیر روشن‌سازی در سه جلسه.' ),
		array( 'title' => 'باب خط‌دار', 'category' => 'کوتاهی', 'image' => 'svc-hair-1.jpg', 'content' => 'باب صاف و دقیق.' ),
		array( 'title' => 'شینیون نرم عروس', 'category' => 'مراسم', 'image' => '', 'content' => 'شینیون پایین با بافت طبیعی.' ),
	),
	'gallery'      => array(
		array( 'title' => 'کراتینه صاف‌کننده', 'before' => 'svc-hair-5.jpg', 'after' => 'svc-hair-3.jpg' ),
		array( 'title' => 'سشوار و موج مجلسی', 'before' => 'svc-hair-5.jpg', 'after' => 'svc-hair-6.jpg' ),
	),
	'testimonials' => array(
		array( 'name' => 'پریسا ن.', 'role' => 'بالیاژ', 'rating' => 5, 'text' => 'دریا فاجعه رنگ خانگی من را در یک جلسه درست کرد. موهایم هنوز سالم است.' ),
		array( 'name' => 'ساناز د.', 'role' => 'کراتینه', 'rating' => 5, 'text' => 'چهار ماه صبح‌های بدون وز. ارزش هر ریالش را داشت.' ),
		array( 'name' => 'ندا ف.', 'role' => 'عروس', 'rating' => 5, 'text' => 'سر وقت به تالار آمدند و مدل مو ۱۴ ساعت رقص را دوام آورد.' ),
	),
	'posts'        => array(
		array( 'title' => 'بالیاژ یا هایلایت؟ تفاوت در چیست؟', 'content' => 'یکی با دست و آزاد نقاشی می‌شود و دیگری با فویل و سکشن‌بندی. این هم راهنمای انتخاب بین این دو…', 'image' => 'hero-hair.jpg' ),
		array( 'title' => 'چطور موی رنگ‌شده را سالم نگه داریم', 'content' => 'شست‌وشو با شامپوی بدون سولفات، محافظ حرارتی و هفته‌ای یک بار ماسک ترمیم پیوند مو…', 'image' => 'svc-hair-1.jpg' ),
	),
	'pages'        => array(
		'home'      => array( 'title' => 'صفحه اصلی سالن مو', 'content' => "[beautia_hero]\n[beautia_about]\n[beautia_services count=\"6\"]\n[beautia_before_after]\n[beautia_portfolio count=\"9\"]\n[beautia_stats]\n[beautia_team count=\"3\"]\n[beautia_pricing]\n[beautia_steps]\n[beautia_testimonials]\n[beautia_faq]\n[beautia_blog count=\"3\"]" ),
		'about'     => array( 'title' => 'سالن ما', 'content' => '<p>یک خانه موی مجله‌ای در قلب شهر.</p>[beautia_team count="3"]' ),
		'services'  => array( 'title' => 'لیست خدمات', 'content' => '[beautia_services count="12"][beautia_pricing]' ),
		'portfolio' => array( 'title' => 'لوک‌بوک', 'content' => '[beautia_portfolio count="24"]' ),
		'contact'   => array( 'title' => 'به ما سر بزنید', 'content' => '[beautia_hours][beautia_track]' ),
		'blog'      => array( 'title' => 'مجله', 'content' => '' ),
	),
	'menu'         => array(
		array( 'title' => 'خانه', 'page' => 'home' ),
		array( 'title' => 'سالن', 'page' => 'about' ),
		array( 'title' => 'خدمات', 'page' => 'services' ),
		array( 'title' => 'لوک‌بوک', 'page' => 'portfolio' ),
		array( 'title' => 'مجله', 'page' => 'blog' ),
		array( 'title' => 'آدرس', 'page' => 'contact' ),
		array( 'title' => 'رزرو نوبت', 'page' => '', 'option_page' => 'booking' ),
	),
	'options'      => array( 'currency_symbol' => 'تومان', 'slot_step' => 30, 'cancel_hours' => 12, 'address' => 'تهران، الهیه، کوچه گلستان ۶', 'phone' => '۰۲۱-۲۲۶۶۷۷۸۸' ),
);
