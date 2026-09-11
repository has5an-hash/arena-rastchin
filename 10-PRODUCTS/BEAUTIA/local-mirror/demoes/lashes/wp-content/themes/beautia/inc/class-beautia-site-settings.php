<?php
/** Unified Persian design and content control center. */
defined( 'ABSPATH' ) || exit;

final class Beautia_Site_Settings {
	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_beautia_save_site_settings', array( __CLASS__, 'save' ) );
	}

	public static function menu() {
		add_submenu_page( 'beautia', 'تنظیمات ظاهر و محتوا', 'ظاهر و محتوای سایت', 'manage_options', 'beautia-site-settings', array( __CLASS__, 'page' ) );
	}

	private static function field( $name, $label, $value, $type = 'text', $help = '' ) {
		echo '<label class="beautia-control"><span>' . esc_html( $label ) . '</span>';
		if ( 'textarea' === $type ) echo '<textarea name="' . esc_attr( $name ) . '" rows="4">' . esc_textarea( $value ) . '</textarea>';
		elseif ( 'color' === $type ) echo '<input name="' . esc_attr( $name ) . '" type="color" value="' . esc_attr( $value ?: '#8e617d' ) . '">';
		else echo '<input name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '">';
		if ( $help ) echo '<small>' . esc_html( $help ) . '</small>';
		echo '</label>';
	}

	private static function checkbox( $name, $label, $value ) {
		echo '<label class="beautia-check"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . checked( 1, (int) $value, false ) . '><span>' . esc_html( $label ) . '</span></label>';
	}

	public static function page() {
		$studio_overrides = get_option( 'beautia_studio_content_overrides', array() );
		$demos = beautia_studios();
		?>
		<div class="wrap beautia-admin beautia-site-control" dir="rtl">
			<h1>مرکز کنترل بیوتیا</h1>
			<p>تنظیمات سراسری و محتوای اختصاصی هر دمو را از یک نقطه مدیریت کنید. برای طراحی آزاد تک‌صفحه‌ها نیز Elementor رایگان و ۳۵ ویجت بیوتیا در دسترس است.</p>
			<?php if ( isset( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div><?php endif; ?>
			<nav class="beautia-control-links"><a href="<?php echo esc_url( admin_url( 'admin.php?page=beautia-demos' ) ); ?>">نصب دموی آماده</a><a href="<?php echo esc_url( admin_url( 'admin.php?page=beautia-core-demos' ) ); ?>">آپلود بسته Core Demo</a><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">پیش‌نمایش زنده</a><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">منو و مگامنو</a></nav>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="beautia_save_site_settings"><?php wp_nonce_field( 'beautia_site_settings' ); ?>
			<div class="beautia-settings-grid">
			<section><h2>هویت و ارتباط</h2><?php
			self::field('site_title','نام سایت',get_bloginfo('name'));
			self::field('site_tagline','توضیح کوتاه برند',get_bloginfo('description'));
			foreach(array('phone'=>'تلفن','address'=>'نشانی','email'=>'ایمیل','instagram'=>'اینستاگرام','telegram'=>'تلگرام','whatsapp'=>'واتساپ','youtube'=>'یوتیوب','pinterest'=>'پینترست','map'=>'آدرس Embed نقشه') as $key=>$label) self::field('contact_'.$key,$label,get_theme_mod('beautia_'.$key,''),$key==='address'?'textarea':'text');
			?></section>
			<section><h2>هدر، منو و فوتر</h2><label class="beautia-control"><span>چیدمان هدر</span><select name="header_style"><?php foreach(array('classic'=>'کلاسیک','centered'=>'لوگو وسط','split'=>'منوی دوطرفه','transparent'=>'شفاف روی هیرو','compact'=>'فشرده') as $key=>$label) echo '<option value="'.esc_attr($key).'" '.selected(get_theme_mod('beautia_header_style','classic'),$key,false).'>'.esc_html($label).'</option>'; ?></select></label><?php
			self::field('header_cta','متن دکمه هدر',get_theme_mod('beautia_header_cta','رزرو نوبت'));
			self::field('copyright','متن کپی‌رایت',get_theme_mod('beautia_copyright',''),'textarea');
			self::checkbox('topbar','نمایش نوار بالایی',get_theme_mod('beautia_topbar',1));
			self::checkbox('sticky_header','هدر چسبان',get_theme_mod('beautia_sticky_header',1));
			self::checkbox('footer_cta','نمایش دعوت به رزرو پیش از فوتر',get_theme_mod('beautia_footer_cta',1));
			?><p class="description">تصویر، آیکن و یکی از سه مدل مگامنو برای هر آیتم از صفحه «منو و مگامنو» تنظیم می‌شود.</p></section>
			<section><h2>رنگ و تایپوگرافی</h2><?php foreach(array('accent'=>'رنگ اصلی','accent_2'=>'رنگ مکمل','dark'=>'تیترها','body'=>'متن','bg'=>'پس‌زمینه','soft'=>'سطح ملایم') as $key=>$label) self::field('color_'.$key,$label,get_theme_mod('beautia_color_'.$key,''),'color'); self::field('font_display','فونت تیترها',get_theme_mod('beautia_font_display','')); self::field('font_body','فونت متن',get_theme_mod('beautia_font_body','')); self::field('base_size','اندازه پایه متن',get_theme_mod('beautia_base_size',17),'number'); ?></section>
			<section><h2>جلوه‌ها و تجربه کاربری</h2><?php foreach(array('enable_animations'=>'انیمیشن ورود سکشن‌ها','enable_cursor'=>'نشانگر مغناطیسی','enable_parallax'=>'پارالاکس تصاویر','enable_marquee'=>'نوارهای متحرک','enable_grain'=>'بافت فیلم','enable_preloader'=>'پیش‌بارگذار','enable_sticky_cta'=>'نوار رزرو موبایل') as $key=>$label) self::checkbox($key,$label,get_theme_mod('beautia_'.$key,$key!=='enable_cursor'&&$key!=='enable_grain')); ?></section>
			</div>
			<h2 class="beautia-demo-heading">محتوا و ترکیب‌بندی هر دمو</h2><div class="beautia-demo-settings">
			<?php foreach($demos as $slug=>$demo): $saved=$studio_overrides[$slug]??array(); ?><details><summary><strong><?php echo esc_html($demo['name']); ?></strong><span><?php echo esc_html($demo['fa']); ?></span></summary><div class="beautia-demo-fields"><?php
			self::field("demo[$slug][title]",'تیتر اصلی',wp_strip_all_tags($saved['title']??$demo['title']),'textarea');
			self::field("demo[$slug][intro]",'متن معرفی',$saved['intro']??$demo['intro'],'textarea');
			self::field("demo[$slug][about]",'متن درباره مجموعه',$saved['about']??$demo['about'],'textarea');
			self::field("demo[$slug][tone]",'لحن/برچسب بصری',$saved['tone']??$demo['tone']);
			self::field("demo[$slug][section_order]",'ترتیب سکشن‌ها',$saved['section_order']??'services,comparison,signature,portfolio,availability,about,team,testimonials,blog,faq','text','شناسه signature سکشن تخصصی همان حوزه است؛ با جابه‌جایی یا حذف شناسه‌ها، ترتیب و نمایش سکشن‌ها تغییر می‌کند.');
			?></div></details><?php endforeach; ?></div>
			<?php submit_button('ذخیره همه تنظیمات'); ?></form>
		</div><style>.beautia-site-control{max-width:1280px}.beautia-control-links{display:flex;flex-wrap:wrap;gap:8px;margin:18px 0}.beautia-control-links a{padding:10px 14px;background:#fff;border:1px solid #d8c9d3;border-radius:10px;text-decoration:none}.beautia-settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.beautia-settings-grid>section,.beautia-demo-settings details{background:#fff;border:1px solid #e3d9df;border-radius:16px;padding:20px}.beautia-control{display:grid;gap:7px;margin:0 0 14px}.beautia-control>span{font-weight:700}.beautia-control input:not([type=color]),.beautia-control textarea,.beautia-control select{width:100%;max-width:none}.beautia-control input[type=color]{width:80px;height:42px}.beautia-control small{color:#6d6169}.beautia-check{display:flex;gap:9px;margin:11px 0}.beautia-demo-heading{margin-top:28px}.beautia-demo-settings{display:grid;gap:10px}.beautia-demo-settings summary{display:flex;justify-content:space-between;cursor:pointer;font-size:16px}.beautia-demo-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;padding-top:18px}@media(max-width:782px){.beautia-settings-grid,.beautia-demo-fields{grid-template-columns:1fr}}</style>
		<?php
	}

	public static function save() {
		if(!current_user_can('manage_options'))wp_die('دسترسی کافی ندارید.');
		check_admin_referer('beautia_site_settings');
		update_option('blogname',sanitize_text_field(wp_unslash($_POST['site_title']??'')));
		update_option('blogdescription',sanitize_text_field(wp_unslash($_POST['site_tagline']??'')));
		$header=sanitize_key(wp_unslash($_POST['header_style']??'classic')); if(!in_array($header,array('classic','centered','split','transparent','compact'),true))$header='classic'; set_theme_mod('beautia_header_style',$header);
		foreach(array('header_cta','copyright','font_display','font_body') as $key)set_theme_mod('beautia_'.$key,$key==='copyright'?wp_kses_post(wp_unslash($_POST[$key]??'')):sanitize_text_field(wp_unslash($_POST[$key]??'')));
		foreach(array('phone','address','email','instagram','telegram','whatsapp','youtube','pinterest','map') as $key)set_theme_mod('beautia_'.$key,esc_url_raw(wp_unslash($_POST['contact_'.$key]??''))&&in_array($key,array('instagram','telegram','youtube','pinterest','map'),true)?esc_url_raw(wp_unslash($_POST['contact_'.$key]??'')):sanitize_text_field(wp_unslash($_POST['contact_'.$key]??'')));
		foreach(array('accent','accent_2','dark','body','bg','soft') as $key)set_theme_mod('beautia_color_'.$key,sanitize_hex_color(wp_unslash($_POST['color_'.$key]??'')));
		set_theme_mod('beautia_base_size',min(24,max(12,absint($_POST['base_size']??17))));
		foreach(array('topbar','sticky_header','footer_cta','enable_animations','enable_cursor','enable_parallax','enable_marquee','enable_grain','enable_preloader','enable_sticky_cta') as $key)set_theme_mod('beautia_'.$key,isset($_POST[$key])?1:0);
		$allowed=array('services','comparison','signature','portfolio','availability','about','team','testimonials','blog','faq');$overrides=array();
		foreach((array)($_POST['demo']??array()) as $slug=>$row){$slug=sanitize_key($slug);if(!isset(beautia_studios()[$slug]))continue;$order=array_values(array_unique(array_intersect($allowed,array_map('sanitize_key',explode(',',wp_unslash($row['section_order']??''))))));$overrides[$slug]=array('title'=>sanitize_textarea_field(wp_unslash($row['title']??'')),'intro'=>sanitize_textarea_field(wp_unslash($row['intro']??'')),'about'=>sanitize_textarea_field(wp_unslash($row['about']??'')),'tone'=>sanitize_text_field(wp_unslash($row['tone']??'')),'section_order'=>implode(',',$order));}
		update_option('beautia_studio_content_overrides',$overrides,false);
		wp_safe_redirect(add_query_arg(array('page'=>'beautia-site-settings','updated'=>1),admin_url('admin.php')));exit;
	}
}
Beautia_Site_Settings::boot();
