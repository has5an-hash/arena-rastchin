<?php
/**
 * Demo content: Spa & Wellness (اسپا و تندرستی).
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

return array(
	'services'     => array(
		array( 'title' => 'ماساژ آروماتراپی امضایی', 'content' => 'نود دقیقه ماساژ تمام‌بدن با ترکیبی اختصاصی از روغن‌های گیاهی که پس از یک مشاوره کوتاه سلامتی انتخاب می‌شود.', 'price' => 1900000, 'duration' => 90, 'icon' => 'lotus', 'category' => 'ماساژ', 'image' => 'svc-spa-1.jpg', 'highlights' => array( 'ترکیب روغن اختصاصی', 'تخت گرم‌شونده', 'آیین دمنوش گیاهی' ) ),
		array( 'title' => 'ماساژ سنگ داغ', 'content' => 'سنگ‌های بازالت و حرکات آهسته برای رها کردن تنش‌های عمیق عضلانی.', 'price' => 2200000, 'duration' => 80, 'icon' => 'stone', 'category' => 'ماساژ', 'image' => '' ),
		array( 'title' => 'رپ سم‌زدایی بدن', 'content' => 'پوشش خاک رس سبز و جلبک دریایی همراه با پتوی مادون قرمز و در پایان، کرم آبرسان.', 'price' => 1700000, 'duration' => 70, 'icon' => 'leaf', 'category' => 'بدن', 'image' => '' ),
		array( 'title' => 'فیشیال آرام‌بخش', 'content' => 'فیشیال ترمیم سد دفاعی برای پوست حساس: پاک‌سازی آنزیمی، کره‌های سرمایی و ماسک خنک‌کننده.', 'price' => 1500000, 'duration' => 60, 'icon' => 'droplet', 'category' => 'صورت', 'image' => '' ),
		array( 'title' => 'پکیج دونفره', 'content' => 'دو ماساژور، یک سوئیت، دو ساعت: ماساژ، آیین پا و پذیرایی ویژه.', 'price' => 4200000, 'duration' => 120, 'icon' => 'heart', 'category' => 'پکیج‌ها', 'image' => '' ),
		array( 'title' => 'ماساژ بارداری', 'content' => 'ماساژ ایمن دوران بارداری در حالت به‌پهلو، از سه‌ماهه دوم، توسط ماساژورهای دارای گواهی.', 'price' => 1600000, 'duration' => 60, 'icon' => 'shield', 'category' => 'ماساژ', 'image' => '' ),
	),
	'staff'        => array(
		array( 'name' => 'گلنار سپهری', 'role' => 'ماساژور ارشد', 'bio' => 'آموزش‌دیده در تکنیک تای و سوئدی، متخصص تنش‌های مزمن.', 'experience' => '12', 'image' => 'team-1.jpg' ),
		array( 'name' => 'هدی کریمی', 'role' => 'فیشیال و بدن', 'bio' => 'کارشناس زیبایی با وسواس روی سلامت سد دفاعی پوست و رویکردی ملایم و روشمند.', 'experience' => '8', 'image' => 'team-2.jpg' ),
	),
	'portfolio'    => array(
		array( 'title' => 'اتاق سکوت', 'category' => 'فضاها', 'image' => 'hero-spa.jpg', 'content' => 'لانج آرامش پس از درمان.' ),
		array( 'title' => 'بار دمنوش گیاهی', 'category' => 'فضاها', 'image' => 'svc-spa-1.jpg', 'content' => 'برای هر مهمان تازه دم می‌شود.' ),
	),
	'gallery'      => array(
		array( 'title' => 'فیشیال آرام‌بخش', 'before' => 'svc-spa-3.jpg', 'after' => 'svc-spa-4.jpg' ),
	),
	'testimonials' => array(
		array( 'name' => 'مریم س.', 'role' => 'ماساژ آروما', 'rating' => 5, 'text' => 'ده دقیقه اول خوابم برد. آدم دیگری بیدار شدم.' ),
		array( 'name' => 'علی ر.', 'role' => 'سنگ داغ', 'rating' => 5, 'text' => 'بهترین ماساژ بافت عمقی که تجربه کرده‌ام. رزرو آنلاین هم کمتر از یک دقیقه طول کشید.' ),
		array( 'name' => 'سحر ب.', 'role' => 'ماساژ بارداری', 'rating' => 5, 'text' => 'برای وضعیت قرارگیری بدنم واقعاً دقت کردند. کاملاً احساس امنیت داشتم.' ),
	),
	'posts'        => array(
		array( 'title' => 'پنج تمرین تنفسی برای یک هفته پرفشار', 'content' => 'تنفس جعبه‌ای، ۴-۷-۸، آه فیزیولوژیک… این‌ها را قبل از نوبت بعدی‌تان امتحان کنید.', 'image' => 'hero-spa.jpg' ),
		array( 'title' => 'هر چند وقت یک‌بار ماساژ بگیریم؟', 'content' => 'برای تنش مزمن هر دو هفته و برای نگهداری، ماهی یک بار. دلیلش را توضیح می‌دهیم…', 'image' => 'svc-spa-1.jpg' ),
	),
	'pages'        => array(
		'home'      => array( 'title' => 'صفحه اصلی اسپا', 'content' => "[beautia_hero]\n[beautia_about]\n[beautia_services count=\"6\"]\n[beautia_before_after]\n[beautia_features]\n[beautia_stats]\n[beautia_team count=\"2\"]\n[beautia_pricing]\n[beautia_steps]\n[beautia_testimonials]\n[beautia_faq]\n[beautia_blog count=\"3\"]" ),
		'about'     => array( 'title' => 'فلسفه ما', 'content' => '<p>درمان‌های آهسته، محصولات طبیعی و فضایی که بوی جنگل بعد از باران می‌دهد.</p>[beautia_team count="2"]' ),
		'services'  => array( 'title' => 'درمان‌ها', 'content' => '[beautia_services count="12"][beautia_pricing]' ),
		'contact'   => array( 'title' => 'ما را پیدا کنید', 'content' => '[beautia_hours][beautia_track]' ),
		'blog'      => array( 'title' => 'یادداشت‌های تندرستی', 'content' => '' ),
	),
	'menu'         => array(
		array( 'title' => 'خانه', 'page' => 'home' ),
		array( 'title' => 'فلسفه ما', 'page' => 'about' ),
		array( 'title' => 'درمان‌ها', 'page' => 'services' ),
		array( 'title' => 'یادداشت‌ها', 'page' => 'blog' ),
		array( 'title' => 'آدرس', 'page' => 'contact' ),
		array( 'title' => 'رزرو', 'page' => '', 'option_page' => 'booking' ),
	),
	'options'      => array( 'currency_symbol' => 'تومان', 'slot_step' => 30, 'cancel_hours' => 24, 'address' => 'تهران، جاده دربند، باغ شماره ۹', 'phone' => '۰۲۱-۲۲۴۴۵۵۶۶' ),
);
