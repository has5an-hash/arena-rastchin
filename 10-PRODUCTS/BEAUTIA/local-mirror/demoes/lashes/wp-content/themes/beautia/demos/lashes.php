<?php
/**
 * Demo content: Brows & Lashes Bar (اکستنشن مژه و ابرو).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

return array(
	'services'     => array(
		array( 'title' => 'ست مژه ولوم', 'content' => 'فن‌های دست‌ساز سه‌بعدی تا شش‌بعدی، نقشه‌برداری‌شده متناسب با فرم چشم شما، برای ظاهری پرپشت و بی‌وزن.', 'price' => 1400000, 'duration' => 120, 'buffer' => 15, 'icon' => 'lash', 'category' => 'مژه', 'image' => 'svc-lash-1.jpg', 'highlights' => array( 'فن‌های کاملاً دست‌ساز', 'نقشه اختصاصی چشم', 'ترمیم سه‌هفته‌ای' ) ),
		array( 'title' => 'اکستنشن مژه کلاسیک', 'content' => 'یک تار اکستنشن روی هر مژه طبیعی، برای ظاهری طبیعی اما بهتر.', 'price' => 950000, 'duration' => 90, 'icon' => 'lash', 'category' => 'مژه', 'image' => '' ),
		array( 'title' => 'لیفت و رنگ مژه', 'content' => 'فر و رنگ مژه‌های طبیعی با دوامی شش‌هفته‌ای — بدون هیچ نگهداری.', 'price' => 700000, 'duration' => 60, 'icon' => 'sparkle', 'category' => 'مژه', 'image' => '' ),
		array( 'title' => 'لمینت ابرو', 'content' => 'ابروهای پرپشت و رو به بالا، همراه با فرم‌دهی و رنگ.', 'price' => 780000, 'duration' => 60, 'icon' => 'brow', 'category' => 'ابرو', 'image' => '' ),
		array( 'title' => 'میکروبلیدینگ', 'content' => 'ابروی نیمه‌دائم با تکنیک تار به تار، شامل جلسه ترمیم پس از شش هفته.', 'price' => 5200000, 'duration' => 150, 'icon' => 'brush', 'category' => 'ابرو', 'image' => '' ),
		array( 'title' => 'فرم و رنگ ابرو', 'content' => 'بند یا وکس، رنگ‌گذاری و مشاوره نقشه ابرو.', 'price' => 380000, 'duration' => 30, 'icon' => 'brow', 'category' => 'ابرو', 'image' => '' ),
	),
	'staff'        => array(
		array( 'name' => 'تارا جلالی', 'role' => 'مژه‌کار ارشد', 'bio' => 'متخصص نقشه‌برداری ولوم، بیش از ۴۰۰۰ ست اجراشده.', 'experience' => '9', 'image' => 'team-2.jpg' ),
		array( 'name' => 'ستاره وثوقی', 'role' => 'ابرو و آرایش دائم', 'bio' => 'میکروبلیدینگ و لمینت، دارای گواهی نظریه رنگ و پیگمنت.', 'experience' => '7', 'image' => 'team-1.jpg' ),
	),
	'portfolio'    => array(
		array( 'title' => 'ست ولوم ویسپی', 'category' => 'مژه', 'image' => 'svc-lash-1.jpg', 'content' => 'نقشه‌برداری ویسپی با بافت طبیعی.' ),
		array( 'title' => 'ابروی لمینت‌شده', 'category' => 'ابرو', 'image' => '', 'content' => 'لمینت به همراه رنگ ملایم.' ),
	),
	'gallery'      => array(
		array( 'title' => 'اکستنشن کلاسیک', 'before' => 'svc-lash-3.jpg', 'after' => 'svc-lash-1.jpg' ),
		array( 'title' => 'لیفت و رنگ مژه', 'before' => 'svc-lash-3.jpg', 'after' => 'svc-lash-2.jpg' ),
	),
	'testimonials' => array(
		array( 'name' => 'نیکی پ.', 'role' => 'مژه ولوم', 'rating' => 5, 'text' => 'سه هفته گذشته و تقریباً هیچ ریزشی نداشته. تارا برای هر ست نقشه جداگانه می‌کشد.' ),
		array( 'name' => 'آیدا م.', 'role' => 'لمینت', 'rating' => 5, 'text' => 'ابروهایم هیچ‌وقت حرف‌گوش‌کن نبودند. حالا هستند.' ),
	),
	'posts'        => array(
		array( 'title' => 'مراقبت بعد از اکستنشن مژه، رُک و ساده', 'content' => 'بله، بعد از ۲۴ ساعت می‌توانید خیسشان کنید. نه، نباید از پاک‌کننده روغنی استفاده کنید…', 'image' => 'svc-lash-1.jpg' ),
	),
	'pages'        => array(
		'home'     => array( 'title' => 'صفحه اصلی لش‌بار', 'content' => "[beautia_hero]\n[beautia_about]\n[beautia_services count=\"6\"]\n[beautia_before_after]\n[beautia_stats]\n[beautia_team count=\"2\"]\n[beautia_pricing]\n[beautia_steps]\n[beautia_testimonials]\n[beautia_faq]" ),
		'services' => array( 'title' => 'لیست خدمات', 'content' => '[beautia_services count="12"][beautia_pricing]' ),
		'contact'  => array( 'title' => 'تماس با ما', 'content' => '[beautia_hours][beautia_track]' ),
		'blog'     => array( 'title' => 'نکته‌ها', 'content' => '' ),
	),
	'menu'         => array(
		array( 'title' => 'خانه', 'page' => 'home' ),
		array( 'title' => 'خدمات', 'page' => 'services' ),
		array( 'title' => 'نکته‌ها', 'page' => 'blog' ),
		array( 'title' => 'تماس', 'page' => 'contact' ),
		array( 'title' => 'رزرو نوبت', 'page' => '', 'option_page' => 'booking' ),
	),
	'options'      => array( 'currency_symbol' => 'تومان', 'slot_step' => 15, 'cancel_hours' => 6, 'address' => 'تهران، سعادت‌آباد، میدان کاج', 'phone' => '۰۲۱-۲۲۹۹۱۰۱۰' ),
);
