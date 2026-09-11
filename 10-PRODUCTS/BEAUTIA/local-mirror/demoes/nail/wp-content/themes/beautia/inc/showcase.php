<?php
/** Multi-demo routing, content isolation and shared studio experiences. */
defined('ABSPATH') || exit;

// Keep the actual document direction aligned with the Persian interface even
// when the host has not installed the WordPress Persian core language pack.
add_filter('language_attributes',function($attributes){
    if(beautia_get_option('force_persian',1))return 'lang="fa-IR" dir="rtl"';
    return $attributes;
});
add_filter('body_class',function($classes){
    if(beautia_get_option('force_persian',1) && !in_array('rtl',$classes,true))$classes[]='rtl';
    return $classes;
});

function beautia_studios() {
    $studios=array(
        'nails' => array('name'=>'نیل','fa'=>'استودیو ناخن','slug'=>'nail','tone'=>'لطیف، هنری، دوست‌داشتنی','title'=>'جزئیات کوچک،<br>حالِ خوبِ بزرگ.','intro'=>'یک رنگ تازه، یک فرم ظریف و زمانی که فقط برای شماست. در نیل، زیبایی از نوک انگشت‌ها شروع می‌شود.','image'=>'glam-salon.jpg','color'=>'#aa739a','soft'=>'#f6eaf0','moods'=>array('ساده و روزمره','درخشان و متفاوت','یک تغییر اساسی'),'ritual'=>'پالت حالِ امروز','about'=>'اینجا از ایده شما شروع می‌کنیم؛ رنگ، فرم و طول ناخن را با سلیقه و سبک زندگی‌تان هماهنگ می‌کنیم. قبل از شروع، جزئیات طراحی و زمان انجام کار با شما مرور می‌شود.','tips'=>array('تصویر طرح دلخواهتان را همراه داشته باشید.','طول و فرم را با فعالیت روزانه خود هماهنگ کنید.','برای رنگ نهایی، نمونه را در نور طبیعی ببینید.')),
        'clinic' => array('name'=>'کلینیک بیوتیا','fa'=>'کلینیک پوست و زیبایی','slug'=>'clinic','tone'=>'آرام، روشن، اطمینان‌بخش','title'=>'زیبایی، با شناخت<br>پوستِ خودتان.','intro'=>'فضایی روشن برای گفت‌وگو، بررسی و انتخاب آگاهانه. مسیر مراقبت شما با مشاوره تخصصی آغاز می‌شود.','image'=>'hero-clinic.jpg','color'=>'#427d76','soft'=>'#e9f3ef','moods'=>array('شروع با مشاوره','مراقبت روزمره','بررسی تخصصی'),'ritual'=>'مسیر مراقبت شما','about'=>'در این تجربه، تصمیم‌گیری با شنیدن نیاز شما آغاز می‌شود. سوابق، انتظارات و پرسش‌ها در جلسه مشاوره بررسی می‌شوند؛ انتخاب هر اقدام به ارزیابی متخصص نیاز دارد.','tips'=>array('فهرست محصولات و داروهای مصرفی را یادداشت کنید.','پرسش‌ها و انتظاراتتان را در جلسه مشاوره مطرح کنید.','برای برنامه شخصی، نظر متخصص را ملاک قرار دهید.')),
        'hair' => array('name'=>'خانه مو','fa'=>'خانه مو و رنگ','slug'=>'hair','tone'=>'جسور، مجله‌ای، طلایی','title'=>'موهای شما،<br>امضای شخصی شما.','intro'=>'از یک کوتاهی دقیق تا بازی نور در لابه‌لای رنگ. برای تغییری که شبیه خودتان باشد، به خانه مو خوش آمدید.','image'=>'glam-hair.jpg','color'=>'#c4a266','soft'=>'#25211d','moods'=>array('تازه‌کردن استایل','بازی با رنگ','تغییر چشمگیر'),'ritual'=>'استایل بعدی شما','about'=>'هر مو داستان خودش را دارد؛ بافت طبیعی، رنگ قبلی و زمانی که برای حالت‌دادن در اختیار دارید. اینجا طراحی استایل از همین جزئیات شروع می‌شود.','tips'=>array('چند تصویر از نتیجه دلخواهتان آماده کنید.','سابقه رنگ و خدمات قبلی مو را توضیح دهید.','درباره نگهداری استایل در خانه سؤال کنید.')),
        'spa' => array('name'=>'اسپای سوما','fa'=>'اسپا و آرامش','slug'=>'spa','tone'=>'طبیعی، آهسته، تنفس‌پذیر','title'=>'کمی مکث.<br>کمی بیشتر، خودتان.','intro'=>'نور ملایم، بافت‌های طبیعی و فرصتی برای فاصله‌گرفتن از شلوغی. قرار امروزتان، با آرامش است.','image'=>'hero-spa.jpg','color'=>'#798769','soft'=>'#eef0e7','moods'=>array('یک مکث کوتاه','آرامش عمیق','آیین کامل اسپا'),'ritual'=>'مکثِ مخصوص شما','about'=>'تجربه آرامش برای هر کس متفاوت است. زمان، شدت و فضای دلخواهتان را پیش از شروع با تیم هماهنگ کنید؛ این قرار با ریتم شما پیش می‌رود.','tips'=>array('چند دقیقه زودتر برسید تا با آرامش آماده شوید.','ترجیحات خود درباره نور و فشار را بیان کنید.','برای راحتی بیشتر لباس مناسب همراه داشته باشید.')),
        'lashes' => array('name'=>'آتلیه نگاه','fa'=>'آتلیه مژه و ابرو','slug'=>'lashes','tone'=>'نود، ظریف، مینیمال','title'=>'یک نگاه،<br>هزار جزئیاتِ زیبا.','intro'=>'قابی ظریف برای نگاه شما. از فرم طبیعی ابرو تا مژه‌هایی با طراحی متناسب با چهره، همه‌چیز با دقت انتخاب می‌شود.','image'=>'glam-portrait.jpg','color'=>'#9a715d','soft'=>'#f4ebe4','moods'=>array('طبیعی و سبک','فرم و تقارن','نگاه برجسته'),'ritual'=>'قاب نگاه شما','about'=>'در آتلیه نگاه، پیش از هر کاری درباره نتیجه دلخواه صحبت می‌کنیم. انتخاب فرم و حجم با توجه به ترجیح شما انجام می‌شود، نه یک نسخه یکسان برای همه.','tips'=>array('نمونه‌ای از فرم یا حجم دلخواهتان همراه بیاورید.','حساسیت‌ها و تجربه‌های قبلی را مطرح کنید.','روش نگهداری و زمان مراجعه بعدی را بپرسید.')),
        'makeup' => array('name'=>'استودیو میوز','fa'=>'میکاپ و آکادمی','slug'=>'makeup','tone'=>'خلاق، رنگی، بی‌پروا','title'=>'زیبایی، به روایت<br>خودِ شما.','intro'=>'از درخشش کریستالی روزانه تا یک حضور به‌یادماندنی. رنگ، نور و خلاقیت در کنار شخصیت شما معنا پیدا می‌کنند.','image'=>'glam-portrait.jpg','color'=>'#bb4e5f','soft'=>'#fae9e9','moods'=>array('درخشش طبیعی','مهمانی و مراسم','استایل هنری'),'ritual'=>'مودبوردِ چهره','about'=>'لباس، نور، زمان مراسم و سلیقه شما کنار هم یک تصویر کامل می‌سازند. در استودیو میوز، مشاوره پیش از اجرا کمک می‌کند نتیجه با حال‌وهوای همان روز هماهنگ باشد.','tips'=>array('رنگ لباس و نوع مراسم را با هنرمند در میان بگذارید.','تصویر مرجع را برای توضیح سلیقه خود استفاده کنید.','زمان آماده‌شدن و عکاسی را از قبل هماهنگ کنید.')),
        'barber' => array('name'=>'پیرایش اُک','fa'=>'باربرشاپ و پیرایش','slug'=>'barber','tone'=>'اصیل، گرم، دقیق','title'=>'خوش‌استایل،<br>تا آخرین جزئیات.','intro'=>'کوتاهی دقیق، فرم ریش و آیینی برای مرتب‌بودن. فضایی با حال‌وهوای کلاسیک و نگاه امروزی.','image'=>'hero-barber.jpg','color'=>'#b88953','soft'=>'#28231e','moods'=>array('مرتب و روزمره','فید و جزئیات','سرویس کامل'),'ritual'=>'امضای استایل شما','about'=>'یک استایل خوب باید فردا صبح هم قابل استفاده باشد. فرم مو، جهت رشد و زمانی که برای مرتب‌شدن دارید، در انتخاب مدل نقش دارند.','tips'=>array('مدل فعلی و تغییر موردنظرتان را توضیح دهید.','اندازه کوتاهی و مرز ریش را قبل از شروع مشخص کنید.','روش حالت‌دادن مدل جدید در خانه را یاد بگیرید.')),
    );
    $overrides=get_option('beautia_studio_content_overrides',array());
    foreach($studios as $slug=>&$studio)if(!empty($overrides[$slug]))foreach(array('title','intro','about','tone') as $field)if(isset($overrides[$slug][$field])&&''!==$overrides[$slug][$field])$studio[$field]=$field==='title'?nl2br(esc_html($overrides[$slug][$field])):$overrides[$slug][$field];
    unset($studio);
    return $studios;
}

function beautia_demo_context() {
    if (!empty($GLOBALS['beautia_seed_demo'])) return $GLOBALS['beautia_seed_demo'];
    $id = isset($GLOBALS['wp_query']) && $GLOBALS['wp_query'] instanceof WP_Query ? get_queried_object_id() : 0;
    if ($id && is_singular()) {
        $demo = get_post_meta($id, '_beautia_demo', true);
        if (isset(beautia_studios()[$demo])) return $demo;
    }
    $requested = isset($_REQUEST['demo']) ? sanitize_key(wp_unslash($_REQUEST['demo'])) : '';
    if (isset(beautia_studios()[$requested])) return $requested;
    // AJAX uses the selected service as its authoritative context.
    if (wp_doing_ajax() && !empty($_POST['service_id'])) {
        $demo = get_post_meta(absint($_POST['service_id']), '_beautia_demo', true);
        if (isset(beautia_studios()[$demo])) return $demo;
    }
    return '';
}
function beautia_studio_url($demo, $page = 'home') {
    $map = get_option('beautia_studio_map', array());
    return !empty($map[$demo]['pages'][$page]) ? get_permalink($map[$demo]['pages'][$page]) : home_url('/');
}

// Scope all public content queries, including get_posts(), without changing admin lists.
add_action('pre_get_posts', function($query) {
    if ((is_admin() && !wp_doing_ajax()) || !empty($GLOBALS['beautia_seeding'])) return;
    $demo = beautia_demo_context();
    if (!$demo || $query->is_singular()) return;
    $types = (array) $query->get('post_type');
    if (!$types || $types === array('')) $types = array('post');
    if (!array_intersect($types, array('post','beautia_service','beautia_staff','beautia_portfolio','beautia_testimonial','beautia_gallery','any'))) return;
    $meta = $query->get('meta_query') ?: array();
    $query->set('meta_query', array('relation'=>'AND', $meta, array('key'=>'_beautia_demo','value'=>$demo)));
});
add_filter('get_terms_args', function($args, $taxonomies) {
    if ((is_admin() && !wp_doing_ajax()) || !empty($GLOBALS['beautia_seeding'])) return $args;
    $demo = beautia_demo_context();
    if (!$demo || !array_intersect((array)$taxonomies, array('beautia_service_cat','beautia_portfolio_cat','category'))) return $args;
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_beautia_demo' AND meta_value=%s", $demo));
    $args['object_ids'] = !empty($args['object_ids']) ? (array_intersect((array)$args['object_ids'],$ids) ?: array(0)) : ($ids ?: array(0));
    return $args;
}, 10, 2);
add_filter('theme_mod_nav_menu_locations', function($locations) {
    $demo = beautia_demo_context();
    $map = get_option('beautia_studio_map', array());
    if ($demo && !empty($map[$demo]['menu'])) $locations['primary'] = $map[$demo]['menu'];
    return $locations;
});
add_filter('body_class', function($classes) {
    $classes[] = 'beautia-collection';
    if (is_front_page() && get_option('beautia_showcase_enabled')) $classes[]='beautia-hub';
    if (beautia_demo_context()) $classes[]='studio-experience';
    return $classes;
});
add_filter('language_attributes', function($output) {
    if (beautia_demo_context() || (is_front_page() && get_option('beautia_showcase_enabled'))) {
        $output .= ' data-beautia-appearance="pastel"';
    }
    return $output;
});
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('beautia-collection', BEAUTIA_URI.'assets/css/collection.css', array('beautia-style'), BEAUTIA_VERSION);
    wp_enqueue_style('beautia-collection-hub', BEAUTIA_URI.'assets/css/collection-hub.css', array('beautia-collection'), BEAUTIA_VERSION);
    wp_enqueue_style('beautia-collection-responsive', BEAUTIA_URI.'assets/css/collection-responsive.css', array('beautia-collection-hub'), BEAUTIA_VERSION);
    // A single identity layer is authoritative. Loading historical visual
    // experiments together caused palette and component rules to bleed across
    // otherwise independent demos.
    wp_enqueue_style('beautia-identities', BEAUTIA_URI.'assets/css/demo-identities.css', array('beautia-collection-responsive'), beautia_asset_version('assets/css/demo-identities.css'));
    if ('nails' === beautia_get_active_demo()) {
        wp_enqueue_style('beautia-nail-luxury', BEAUTIA_URI.'assets/css/nail-luxury.css', array('beautia-identities'), beautia_asset_version('assets/css/nail-luxury.css'));
    }
    wp_enqueue_script('beautia-collection', BEAUTIA_URI.'assets/js/collection.js', array('beautia-app'), BEAUTIA_VERSION, true);
}, 30);
add_action('wp_head', function() {
    $demo=beautia_demo_context();
    if (!$demo) {
        if(is_front_page() && get_option('beautia_showcase_enabled')) echo '<meta property="og:image" content="'.esc_url(beautia_img('collection-social.jpg')).'" /><meta name="twitter:card" content="summary_large_image" /><meta name="twitter:title" content="بیوتیا — هفت جهان زیبایی" /><meta name="twitter:description" content="یک مجموعه، هفت امضای متفاوت" /><meta name="twitter:image" content="'.esc_url(beautia_img('collection-social.jpg')).'" />';
        return;
    }
    $studio=beautia_studios()[$demo];
    $description=is_singular(array('post','beautia_service','beautia_staff','beautia_portfolio')) ? wp_trim_words(wp_strip_all_tags(get_post_field('post_content',get_queried_object_id())),32) : $studio['intro'];
    $image=has_post_thumbnail(get_queried_object_id()) ? get_the_post_thumbnail_url(get_queried_object_id(),'large') : beautia_img($studio['image']);
    echo '<meta property="og:title" content="'.esc_attr(wp_get_document_title()).'" />';
    echo '<meta property="og:description" content="'.esc_attr($description).'" /><meta name="description" content="'.esc_attr($description).'" />';
    echo '<meta property="og:image" content="'.esc_url($image).'" /><meta name="twitter:card" content="summary_large_image" /><meta name="twitter:title" content="'.esc_attr(wp_get_document_title()).'" /><meta name="twitter:description" content="'.esc_attr($description).'" /><meta name="twitter:image" content="'.esc_url($image).'" />';
});

add_filter('term_link', function($url){$demo=beautia_demo_context();return $demo?add_query_arg('demo',$demo,$url):$url;});
add_action('template_redirect', function(){
    if(!is_page() || beautia_demo_context() || !get_option('beautia_showcase_enabled'))return;
    $options=get_option('beautia_options',array());
    foreach(array('booking','login','account') as $key) if(!empty($options['page_'.$key]) && get_queried_object_id()===$options['page_'.$key]) {wp_safe_redirect(beautia_studio_url('nails',$key));exit;}
});

// Public demo requests cannot combine a service and specialist from different studios.
foreach(array('beautia_get_slots','beautia_service_staff','beautia_create_booking') as $action) {
    $guard=function(){
        $demo=beautia_demo_context();$service=absint($_POST['service_id']??0);$staff=absint($_POST['staff_id']??0);
        if($demo && $service && get_post_meta($service,'_beautia_demo',true)!==$demo) wp_send_json_error(array('message'=>'لطفاً خدمت همین دمو را انتخاب کنید.'),400);
        if($demo && $staff && get_post_meta($staff,'_beautia_demo',true)!==$demo) wp_send_json_error(array('message'=>'این متخصص متعلق به دموی دیگری است.'),400);
    };
    add_action('wp_ajax_'.$action,$guard,0);add_action('wp_ajax_nopriv_'.$action,$guard,0);
}

add_action('add_meta_boxes',function(){
    foreach(array('post','beautia_service','beautia_staff','beautia_portfolio','beautia_testimonial','beautia_gallery') as $type) {
        add_meta_box('beautia-studio-assignment','دموی بیوتیا',function($post){
            wp_nonce_field('beautia_assign_studio','beautia_studio_nonce');
            echo '<p>این محتوا در کدام دمو نمایش داده شود؟</p><select name="beautia_studio" style="width:100%"><option value="">انتخاب دمو</option>';
            foreach(beautia_studios() as $key=>$studio)echo '<option value="'.esc_attr($key).'" '.selected(get_post_meta($post->ID,'_beautia_demo',true),$key,false).'>'.esc_html($studio['name'].' — '.$studio['fa']).'</option>';
            echo '</select>';
        },$type,'side','high');
    }
});
add_action('save_post',function($id){
    if(empty($_POST['beautia_studio_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beautia_studio_nonce'])),'beautia_assign_studio') || !current_user_can('edit_post',$id) || wp_is_post_revision($id))return;
    $demo=sanitize_key($_POST['beautia_studio']??'');
    if(isset(beautia_studios()[$demo]))update_post_meta($id,'_beautia_demo',$demo);
});

add_action('restrict_manage_posts',function($type){
    if(!in_array($type,array('post','page','beautia_service','beautia_staff','beautia_portfolio','beautia_testimonial','beautia_gallery'),true))return;
    echo '<select name="studio_filter"><option value="">همه دموهای بیوتیا</option>';
    foreach(beautia_studios() as $key=>$studio)echo '<option value="'.esc_attr($key).'" '.selected(sanitize_key($_GET['studio_filter']??''),$key,false).'>'.esc_html($studio['name']).'</option>';
    echo '</select>';
});
add_action('pre_get_posts',function($query){
    if(!is_admin() || !$query->is_main_query())return;
    $demo=sanitize_key($_GET['studio_filter']??'');
    if(isset(beautia_studios()[$demo]))$query->set('meta_query',array(array('key'=>'_beautia_demo','value'=>$demo)));
});

function beautia_collection_bar() {
    if (!get_option('beautia_showcase_enabled')) return;
    $demo=beautia_demo_context();
    echo '<div class="collection-bar"><div class="container"><a href="'.esc_url(home_url('/')).'">'.beautia_get_icon('arrow',14).' مجموعه بیوتیا</a><span>پیش‌نمایش قالب · تجربه یک مجموعه زیبایی</span><details class="studio-switch"><summary>تغییر دمو '.beautia_get_icon('chevron',14).'</summary><nav aria-label="انتخاب دمو">';
    foreach(beautia_studios() as $key=>$s) echo '<a '.($demo===$key?'aria-current="page"':'').' href="'.esc_url(beautia_studio_url($key)).'"><b dir="rtl">'.esc_html($s['name']).'</b><span>'.esc_html($s['fa']).'</span></a>';
    echo '</nav></details></div></div>';
}

function beautia_studio_hero() {
    $demo=beautia_demo_context(); $s=beautia_studios()[$demo];
    $services=get_posts(array('post_type'=>'beautia_service','posts_per_page'=>12,'orderby'=>'ID','order'=>'ASC'));
    ?>
    <section class="studio-hero">
      <div class="container studio-hero-grid">
        <div class="studio-hero-copy"><span class="studio-kicker"><i></i><?php echo esc_html($s['fa']); ?> · بیوتیا</span>
          <h1><?php echo wp_kses_post($s['title']); ?></h1><p><?php echo esc_html($s['intro']); ?></p>
          <form class="studio-reservation" action="<?php echo esc_url(beautia_get_page_url('booking')); ?>" method="get">
            <h2>رزرو آنلاین نوبت</h2>
            <label for="studio-service">خدمت دلخواه شما</label>
            <select id="studio-service" name="service" required><?php foreach($services as $service): ?><option value="<?php echo (int)$service->ID; ?>"><?php echo esc_html($service->post_title); ?></option><?php endforeach; ?></select>
            <label for="studio-staff">انتخاب متخصص</label>
            <select id="studio-staff" name="staff"><option value="0">اولین متخصص آزاد</option><?php foreach(get_posts(array('post_type'=>'beautia_staff','posts_per_page'=>20)) as $specialist): ?><option value="<?php echo (int)$specialist->ID; ?>"><?php echo esc_html($specialist->post_title); ?></option><?php endforeach; ?></select>
            <button class="btn btn-primary" type="submit">مشاهده زمان‌های خالی <?php beautia_icon('arrow',17,'btn-ico'); ?></button>
            <a class="text-link" href="#portfolio">اول نمونه‌کارها را ببینم <?php beautia_icon('arrow',15); ?></a>
          </form>
          <div class="studio-mini-facts"><span><?php beautia_icon('calendar',18); ?>انتخاب زمان آنلاین</span><span><?php beautia_icon('sparkle',18); ?>تجربه‌ای به سلیقه شما</span></div>
          <span class="studio-signature" dir="rtl"><?php echo esc_html($s['name']); ?> <em>از مجموعه بیوتیا</em></span>
        </div>
        <div class="studio-visual"><div class="studio-photo"><img src="<?php echo esc_url(beautia_img($s['image'])); ?>" alt="<?php echo esc_attr($s['fa']); ?>" fetchpriority="high" width="1000" height="1100" /></div>
          <span class="photo-caption">زیبایی در جزئیات است <i>✦</i></span>
          <div class="floating-note"><span>قرار بعدی؟</span><strong>یک وقت برای خودت.</strong><a href="<?php echo esc_url(beautia_get_page_url('booking')); ?>">دیدن زمان‌های خالی ←</a></div>
        </div>
      </div>
      <div class="container studio-ribbon"><span>با حوصله انتخاب کن.</span><i>✦</i><span>با سلیقه خودت بدرخش.</span><i>✦</i><span dir="rtl"><?php echo esc_html($s['tone']); ?></span></div>
    </section>
    <section class="section studio-discovery" id="inspiration"><div class="container discovery-grid"><div><span class="eyebrow">یک پیشنهاد کوچک برای شما</span><h2 class="sec-title"><?php echo esc_html($s['ritual']); ?></h2><p>امروز دلتان چه تغییری می‌خواهد؟ حال‌وهوایتان را انتخاب کنید و یک پیشنهاد از خدمات همین مجموعه ببینید.</p><div class="mood-tabs" role="group" aria-label="حال‌وهوای دلخواه">
    <?php foreach($s['moods'] as $i=>$mood): ?><button type="button" data-mood="<?php echo $i; ?>" aria-pressed="<?php echo $i===0?'true':'false'; ?>"><?php echo esc_html($mood); ?></button><?php endforeach; ?>
    </div><span class="small-note">انتخاب سلیقه‌ای است؛ جزئیات را با متخصص هماهنگ کنید.</span></div><div class="mood-results" aria-live="polite">
    <?php foreach(array_slice($services,0,3) as $i=>$service): ?><article class="mood-result" data-mood-result="<?php echo $i; ?>" <?php echo $i?'hidden':''; ?>><span class="mood-result-art" aria-hidden="true"><?php beautia_icon('sparkle',34); ?><i><?php echo esc_html(beautia_use_persian_digits()?beautia_en_to_fa_digits($i+1):$i+1); ?></i></span><div><span>پیشنهاد برای این حال‌وهوا</span><h3><?php echo esc_html($service->post_title); ?></h3><p><?php echo esc_html(beautia_price(get_post_meta($service->ID,'_beautia_price',true))); ?></p><a class="text-link" href="<?php echo esc_url(add_query_arg('service',$service->ID,beautia_get_page_url('booking'))); ?>">این یکی را دوست دارم ←</a></div></article><?php endforeach; ?>
    </div></div></section>
    <?php
}

function beautia_studio_appointments() {
    $services=get_posts(array('post_type'=>'beautia_service','posts_per_page'=>1,'orderby'=>'ID','order'=>'ASC'));
    if (!$services) return;
    $service=$services[0]; $slots=array();
    for($day=1;$day<=7;$day++) {
        $date=wp_date('Y-m-d',strtotime('+'.$day.' days'));
        $slots=Beautia_Booking::get_slots($service->ID,0,$date);
        if ($slots) break;
    }
    ?><section class="section studio-availability"><div class="container availability-wrap"><div><span class="eyebrow">جای خالی برای یک حال خوب</span><h2 class="sec-title">تقویمِ قرارهای قشنگ</h2><p>زمان‌های قابل رزرو برای «<?php echo esc_html($service->post_title); ?>»<br><?php echo esc_html(beautia_format_date($date)); ?></p></div><div class="availability-slots">
    <?php if($slots): foreach(array_slice($slots,0,4) as $slot): ?><a href="<?php echo esc_url(add_query_arg(array('service'=>$service->ID,'date'=>$date),beautia_get_page_url('booking'))); ?>"><span>قابل رزرو</span><strong dir="ltr"><?php echo esc_html(beautia_en_to_fa_digits($slot['time'])); ?></strong><span>انتخاب این روز ←</span></a><?php endforeach; else: ?><p>برای روزهای دیگر، تقویم رزرو را باز کنید.</p><?php endif; ?>
    </div><a class="text-link" href="<?php echo esc_url(beautia_get_page_url('booking')); ?>">تقویم کامل ←</a></div></section><?php
}

/**
 * A domain-specific decision aid for each showcase.
 * The component contract is shared, while its purpose, vocabulary and density
 * belong exclusively to the active studio.
 */
function beautia_studio_specialty() {
    $demo=beautia_demo_context();
    $content=array(
        'nails'=>array('kicker'=>'راهنمای انتخاب طرح','title'=>'طرحی که با دست شما هماهنگ است','intro'=>'از فرم ناخن و سبک روزانه شروع کنید؛ رنگ و جزئیات را در جلسه با نیل‌آرتیست نهایی می‌کنیم.','items'=>array(
            array('01','کوتاه و روزمره','فرم گرد یا اسکووال با رنگ‌های نود و دوام مناسب کار روزانه.'),
            array('02','کشیده و مینیمال','فرم بادامی با خطوط ظریف، فرنچ مدرن یا یک نقطه درخشان.'),
            array('03','مجلسی و چشمگیر','فرم بالرین با کروم، نگین‌گذاری کنترل‌شده یا طراحی برجسته.'),
            array('04','ترمیم و مراقبت','بررسی رشد، سلامت کوتیکول و انتخاب زمان درست برای ترمیم.'),
        )),
        'clinic'=>array('kicker'=>'مسیر مراجعه ایمن','title'=>'از ارزیابی تا پیگیری، مرحله‌به‌مرحله','intro'=>'انتخاب درمان پیش از معاینه قطعی نمی‌شود؛ مسیر شفاف کمک می‌کند با انتظار واقع‌بینانه تصمیم بگیرید.','items'=>array(
            array('01','ارزیابی اولیه','شرح هدف، سوابق، داروها و بررسی شرایط پوست توسط متخصص.'),
            array('02','پیشنهاد پروتکل','توضیح گزینه‌ها، محدودیت‌ها، تعداد جلسات و مراقبت‌های لازم.'),
            array('03','اجرای ثبت‌شده','ثبت جزئیات جلسه، مواد مصرفی و رعایت کنترل‌های بهداشتی.'),
            array('04','پیگیری نتیجه','بررسی پاسخ پوست و اصلاح برنامه فقط بر اساس ارزیابی بعدی.'),
        )),
        'hair'=>array('kicker'=>'انتخاب استایلیست','title'=>'مهارت مناسب برای تغییر دلخواه شما','intro'=>'هر متخصص امضای خودش را دارد؛ نوع تغییر را انتخاب کنید تا رزرو هدفمندتری داشته باشید.','items'=>array(
            array('کات','فرم و کوتاهی','برای بازطراحی فرم، لایه‌سازی و استایلی که در خانه هم قابل اجرا باشد.'),
            array('کالر','رنگ و لایت','برای بالیاژ، اصلاح تناژ و برنامه‌ای سازگار با سابقه رنگ مو.'),
            array('کر','بافت و احیا','برای شناخت الگوی فر، کاهش وز و انتخاب روتین مراقبت متناسب.'),
        )),
        'spa'=>array('kicker'=>'آیین آرامش','title'=>'یک مسیر آرام، از ورود تا بازگشت','intro'=>'تجربه اسپا فقط یک خدمت نیست؛ ریتم جلسه برای رهاشدن تدریجی بدن طراحی می‌شود.','items'=>array(
            array('01','ورود و انتخاب رایحه','گفت‌وگوی کوتاه درباره حال بدن، فشار دلخواه و حساسیت‌ها.'),
            array('02','گرم‌کردن و تنفس','آماده‌سازی آرام بدن با گرما، رایحه و چند دقیقه تنفس.'),
            array('03','درمان اصلی','اجرای ماساژ یا ریتوال انتخابی با ریتم و فشار هماهنگ.'),
            array('04','چای و مکث پایانی','زمان کوتاه برای بازگشت، نوشیدنی گرم و توصیه مراقبتی.'),
        )),
        'lashes'=>array('kicker'=>'راهنمای فرم چشم','title'=>'نقشه مژه، مخصوص چشم‌های شما','intro'=>'طول بیشتر همیشه انتخاب بهتر نیست؛ قوس، تراکم و جهت باید با فرم چشم و مژه طبیعی هماهنگ باشد.','items'=>array(
            array('Natural','طبیعی و باز','افزایش ظریف طول با تمرکز روی بازتر دیده‌شدن چشم.'),
            array('Cat','کشیده و گربه‌ای','اوج طول در بخش بیرونی برای چشم‌هایی که تحمل وزن مناسب دارند.'),
            array('Doll','مرکزی و عروسکی','تمرکز طول در مرکز برای تأکید روی گردی و روشنایی نگاه.'),
        )),
        'makeup'=>array('kicker'=>'انتخاب پکیج مراسم','title'=>'میکاپ متناسب با نور، زمان و حال‌وهوای شما','intro'=>'نوع مراسم و شرایط نور، انتخاب بافت و ماندگاری را تعیین می‌کند؛ ظاهر نهایی باید همچنان شبیه خود شما باشد.','items'=>array(
            array('DAY','روز و فضای باز','پوست سبک، کنترل برق و رنگ‌هایی که زیر نور طبیعی دقیق بمانند.'),
            array('NIGHT','شب و نور مصنوعی','عمق بیشتر، تعریف چشم و بافتی آماده عکاسی در نور کم.'),
            array('BRIDE','عروس و ماندگاری','جلسه مشاوره، تست هماهنگی و برنامه زمانی بدون شتاب.'),
            array('EDITORIAL','کمپین و فشن','طراحی کانسپت، هماهنگی با استایل و اجرای مناسب قاب دوربین.'),
        )),
        'barber'=>array('kicker'=>'باشگاه آقایان','title'=>'برنامه‌ای برای همیشه مرتب‌ماندن','intro'=>'فاصله مراجعه را با سرعت رشد مو و ریش تنظیم کنید؛ پکیج منظم، استایل را بین دو قرار حفظ می‌کند.','items'=>array(
            array('ESSENTIAL','مرتب و روزمره','کوتاهی دوره‌ای، اصلاح دور گردن و راهنمای حالت‌دادن در خانه.'),
            array('SIGNATURE','مو و ریش هماهنگ','طراحی هم‌زمان فرم مو و ریش با اولویت تناسب چهره.'),
            array('RITUAL','آیین کامل پیرایش','مو، ریش، حوله داغ و پاک‌سازی در یک قرار کامل و آرام.'),
        )),
    );
    if(!isset($content[$demo]))return;
    $c=$content[$demo];
    ?><section class="section studio-specialty specialty-<?php echo esc_attr($demo); ?>" id="studio-specialty"><div class="container"><div class="specialty-head"><div><span class="eyebrow"><?php echo esc_html($c['kicker']); ?></span><h2 class="sec-title"><?php echo esc_html($c['title']); ?></h2></div><p><?php echo esc_html($c['intro']); ?></p></div><div class="specialty-grid specialty-count-<?php echo count($c['items']); ?>">
    <?php foreach($c['items'] as $item): ?><article><span class="specialty-code"><?php echo esc_html($item[0]); ?></span><h3><?php echo esc_html($item[1]); ?></h3><p><?php echo esc_html($item[2]); ?></p></article><?php endforeach; ?>
    </div><a class="btn btn-outline" href="<?php echo esc_url(beautia_get_page_url('booking')); ?>">مشاوره و رزرو <?php beautia_icon('arrow',16,'btn-ico'); ?></a></div></section><?php
}

function beautia_nail_comparison() {
    ?><section class="section nail-comparison" id="results"><div class="container nail-compare-grid"><div><span class="eyebrow">از سادگی تا درخشش</span><h2 class="sec-title">همان دست.<br>یک حسِ تازه.</h2><p>دستگیره را آرام جابه‌جا کنید و تغییر رنگ و پرداخت ناخن‌ها را ببینید. قبل و بعد، کنار هم و در یک قاب.</p><div class="comparison-notes"><span>۰۱ <b>فرم طبیعی</b></span><span>۰۲ <b>طراحی برجسته</b></span><span>۰۳ <b>درخشش کریستالی</b></span></div><small>تصاویر ساخته‌شده برای نمایش قابلیت مقایسه.</small></div><div><?php beautia_compare(beautia_img('manicure-before-v2.jpg'),beautia_img('manicure-after-v2.jpg'),array('title'=>'نمایش تعاملی تغییر · نیل','start'=>50)); ?></div></div></section><?php
}

/**
 * Nail Fit: an original, lightweight consultation aid for choosing a practical
 * nail style from lifestyle, preferred length and visual mood.
 */
function beautia_nail_fit_lab() {
    ?><section class="section nail-fit-lab" id="nail-fit"><div class="container"><div class="nail-fit-head"><div><span class="eyebrow">مشاور هوشمند انتخاب ناخن</span><h2 class="sec-title">Nail Fit؛ طرحی که با زندگی شما جور است</h2></div><p>سه انتخاب کوتاه انجام دهید تا فرم، پرداخت و زمان ترمیم مناسب شما پیشنهاد شود. نتیجه برای شروع مشاوره است و نیل‌آرتیست آن را با شرایط ناخن طبیعی شما نهایی می‌کند.</p></div>
    <div class="nail-fit-shell" data-nail-fit>
      <div class="nail-fit-questions">
        <fieldset><legend><b>۱</b> سبک روز شما</legend><div class="nail-fit-options"><button type="button" data-fit-key="life" data-fit-value="daily" aria-pressed="true">روزمره و اداری</button><button type="button" data-fit-key="life" data-fit-value="active" aria-pressed="false">فعال و پرتحرک</button><button type="button" data-fit-key="life" data-fit-value="event" aria-pressed="false">مراسم و مهمانی</button></div></fieldset>
        <fieldset><legend><b>۲</b> طول دلخواه</legend><div class="nail-fit-options"><button type="button" data-fit-key="length" data-fit-value="short" aria-pressed="true">کوتاه</button><button type="button" data-fit-key="length" data-fit-value="medium" aria-pressed="false">متوسط</button><button type="button" data-fit-key="length" data-fit-value="long" aria-pressed="false">بلند</button></div></fieldset>
        <fieldset><legend><b>۳</b> حال‌وهوای طراحی</legend><div class="nail-fit-options"><button type="button" data-fit-key="mood" data-fit-value="minimal" aria-pressed="true">مینیمال</button><button type="button" data-fit-key="mood" data-fit-value="gloss" aria-pressed="false">براق و لوکس</button><button type="button" data-fit-key="mood" data-fit-value="art" aria-pressed="false">نیل‌آرت خاص</button></div></fieldset>
      </div>
      <aside class="nail-fit-result" aria-live="polite"><span class="nail-fit-mark" aria-hidden="true">NF</span><small>نسخه پیشنهادی شما</small><h3 data-fit-title>اسکووال کوتاه و مینیمال</h3><dl><div><dt>فرم مناسب</dt><dd data-fit-shape>اسکووال کوتاه</dd></div><div><dt>پرداخت پیشنهادی</dt><dd data-fit-finish>نود شیری با خط ظریف</dd></div><div><dt>چرخه مراقبت</dt><dd data-fit-care>ترمیم هر ۳ تا ۴ هفته</dd></div></dl><a class="btn btn-primary" href="<?php echo esc_url(beautia_get_page_url('booking')); ?>">رزرو مشاوره با این پیشنهاد <?php beautia_icon('arrow',16,'btn-ico'); ?></a></aside>
    </div></div></section><?php
}

