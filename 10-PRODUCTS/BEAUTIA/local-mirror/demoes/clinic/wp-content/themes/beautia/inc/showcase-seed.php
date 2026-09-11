<?php
/** Idempotent, explicitly invoked demo content installer. */
defined('ABSPATH') || exit;

function beautia_seed_post($demo,$key,$type,$title,$content='',$legacy='') {
    $found=get_posts(array('post_type'=>$type,'post_status'=>'any','posts_per_page'=>1,'meta_key'=>'_beautia_seed_key','meta_value'=>$demo.':'.$key));
    if(!$found && $demo==='nails' && $legacy) $found=get_posts(array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'title'=>$legacy));
    $plain=wp_strip_all_tags(preg_replace('/<\/(p|h[1-6]|li)>/i',' $0 ',$content));
    $post=array('post_type'=>$type,'post_title'=>$title,'post_content'=>$content,'post_excerpt'=>wp_trim_words($plain,32),'post_status'=>'publish','post_author'=>1,'comment_status'=>'closed');
    if($found) $post['ID']=$found[0]->ID;
    $id=wp_insert_post($post,true);
    if(is_wp_error($id)) throw new RuntimeException($id->get_error_message());
    update_post_meta($id,'_beautia_demo',$demo);
    update_post_meta($id,'_beautia_seed_key',$demo.':'.$key);
    return $id;
}

function beautia_seed_image($file,$id,$fallback) {
    foreach(array('file','fallback') as $candidate) {
        $value=${$candidate};
        if($value && '.png'===strtolower(substr($value,-4))) {
            $optimized=substr($value,0,-4).'.jpg';
            if(file_exists(BEAUTIA_DIR.'assets/img/'.$optimized)) ${$candidate}=$optimized;
        }
    }
    if(!$file || !file_exists(BEAUTIA_DIR.'assets/img/'.$file)) $file=$fallback;
    $cache=get_option('beautia_demo_asset_cache',array());
    if(!empty($cache[$file]) && get_post($cache[$file])) {set_post_thumbnail($id,$cache[$file]);return;}
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/image.php';
    $upload=wp_upload_bits($file,null,file_get_contents(BEAUTIA_DIR.'assets/img/'.$file));
    if($upload['error']) throw new RuntimeException($upload['error']);
    $type=wp_check_filetype($file);
    $attachment=wp_insert_attachment(array('post_title'=>pathinfo($file,PATHINFO_FILENAME),'post_mime_type'=>$type['type'],'post_status'=>'inherit'),$upload['file'],$id);
    wp_update_attachment_metadata($attachment,wp_generate_attachment_metadata($attachment,$upload['file']));
    update_post_meta($attachment,'_wp_attachment_image_alt',get_the_title($id));
    $cache[$file]=$attachment;update_option('beautia_demo_asset_cache',$cache,false);
    set_post_thumbnail($id,$attachment);
}

function beautia_seed_demo($demo) {
    if(!isset(beautia_studios()[$demo])) return new WP_Error('demo','Unknown demo');
    $GLOBALS['beautia_seeding']=true;$GLOBALS['beautia_seed_demo']=$demo;
    $studio=beautia_studios()[$demo];$data=Beautia_Demo_Importer::data($demo);
    require_once BEAUTIA_DIR.'inc/showcase-content.php';
    $realistic=beautia_studio_content($demo);
    $map=get_option('beautia_studio_map',array());
    $entry=isset($map[$demo])?$map[$demo]:array();
    $entry['options']=$data['options'];$entry['options']['sms_gateway']='log';
    $entry['options']['force_persian']=1;$entry['options']['persian_digits']=1;
    $entry['pages']=array();$services=array();$staff=array();
    $extra=array('nails'=>array('طراحی مینیمال تک‌رنگ','مشاوره فرم ناخن'),'clinic'=>array('جلسه مشاوره پوست','بررسی روتین شخصی'),'hair'=>array('استایل روزانه','مشاوره رنگ و کوتاهی'),'spa'=>array('آیین آرامش عصرگاهی','اسپای دست و پا','پکیج آرامش شخصی'),'lashes'=>array('مشاوره طراحی نگاه','فرم‌دهی طبیعی ابرو','ترمیم مژه'),'makeup'=>array('میکاپ روزانه','جلسه طراحی استایل','آموزش آرایش شخصی','میکاپ عکاسی'),'barber'=>array('استایل و حالت‌دهی','مشاوره فرم مو','پکیج پیرایش کامل'));
    $original_count=count($data['services']);
    while(count($data['services'])<6) {
        $i=count($data['services'])-$original_count;
        $data['services'][]=array('title'=>$extra[$demo][$i%count($extra[$demo])],'content'=>$studio['about'],'duration'=>45+15*$i,'price'=>350000+150000*$i,'icon'=>'sparkle','category'=>'خدمات ویژه','image'=>$studio['image']);
    }
    $prefix=array('nails'=>'nail','clinic'=>'clinic','hair'=>'hair','spa'=>'spa','lashes'=>'lash','makeup'=>'makeup','barber'=>'barber')[$demo];
    $photos=glob(BEAUTIA_DIR.'assets/img/svc-'.$prefix.'-*.jpg');
    $service_pools=array(
        'nails'=>array('svc-nail-1.jpg','svc-nail-2.jpg','svc-nail-3.jpg','svc-nail-4.jpg','svc-nail-5.jpg','svc-nail-6.jpg'),
        'clinic'=>array('svc-clinic-1.jpg','svc-clinic-2.jpg','svc-clinic-3.jpg','svc-clinic-4.jpg','svc-clinic-5.jpg','svc-clinic-6.jpg'),
        'hair'=>array('svc-hair-1.jpg','svc-hair-2.jpg','svc-hair-3.jpg','svc-hair-4.jpg','svc-hair-5.jpg','svc-hair-6.jpg'),
        'spa'=>array('svc-spa-1.jpg','svc-spa-2.jpg','svc-spa-3.jpg','svc-spa-4.jpg','svc-spa-5.jpg','svc-spa-6.jpg'),
        'lashes'=>array('svc-lash-1.jpg','svc-lash-2.jpg','svc-lash-3.jpg','svc-lash-4.jpg','svc-lash-5.jpg','svc-lash-6.jpg'),
        'makeup'=>array('svc-makeup-1.jpg','svc-makeup-2.jpg','svc-makeup-3.jpg','svc-makeup-4.jpg','svc-makeup-5.jpg','svc-makeup-6.jpg'),
        'barber'=>array('svc-barber-1.jpg','svc-barber-2.jpg','svc-barber-3.jpg','svc-barber-4.jpg','svc-barber-5.jpg','svc-barber-6.jpg','svc-barber-7.jpg','hero-barber.jpg'),
    );
    foreach($data['services'] as $i=>&$item)if(isset($service_pools[$demo][$i]))$item['image']=$service_pools[$demo][$i];
    unset($item);
    foreach($data['services'] as $i=>$item) {
        $copy='<p>'.$item['content'].'</p><h2>تجربه شما در این جلسه</h2><p>'.$studio['about'].'</p><h3>قبل از رزرو</h3><ul><li>'.implode('</li><li>',$studio['tips']).'</li></ul><p>تعرفه برای خدمت پایه است؛ طراحی اضافه و تغییرات موردنیاز پیش از شروع با شما هماهنگ می‌شود. لطفاً ده دقیقه پیش از زمان رزرو در مجموعه حضور داشته باشید.</p>';
        $id=beautia_seed_post($demo,'service-'.$i,'beautia_service',$item['title'],$copy,$item['title']);
        foreach(array('price','duration','buffer','icon','deposit','price_old') as $field) if(isset($item[$field])) update_post_meta($id,'_beautia_'.$field,$item[$field]);
        update_post_meta($id,'_beautia_bookable',1);update_post_meta($id,'_beautia_capacity',1);
        update_post_meta($id,'_beautia_highlights',implode("\n",isset($item['highlights'])?$item['highlights']:array('مشاوره پیش از شروع','انتخاب متناسب با سلیقه شما','هماهنگی زمان مراجعه')));
        update_post_meta($id,'_beautia_aftercare',implode("\n",$studio['tips']));
        wp_set_object_terms($id,$item['category']??'خدمات ویژه','beautia_service_cat');
        beautia_seed_image($item['image']??'',$id,$studio['image']);$services[]=$id;
    }
    $schedule=array();foreach(array('sat','sun','mon','tue','wed','thu','fri') as $day) $schedule[$day]=array('open'=>$day==='fri'?0:1,'from'=>'10:00','to'=>'20:00','break_from'=>'13:30','break_to'=>'14:30');
    $names=$demo==='barber'?array('آرمان راد','سامان مهر','رضا کیانی'):array('نیکا رحمانی','سارا کیان','مهسا تهرانی');
    $staff_photo_pools=array(
        'nails'=>array('team-nails-1.jpg','team-nails-2.jpg','team-nails-3.jpg'),
        'clinic'=>array('team-clinic-1.jpg','team-clinic-2.jpg','team-clinic-3.jpg'),
        'hair'=>array('team-hair-1.jpg','team-hair-2.jpg','team-hair-3.jpg'),
        'spa'=>array('team-spa-1.jpg','team-spa-2.jpg','team-spa-3.jpg'),
        'lashes'=>array('team-lashes-1.jpg','team-lashes-2.jpg','team-lashes-3.jpg'),
        'makeup'=>array('team-makeup-1.jpg','team-makeup-2.jpg','team-makeup-3.jpg'),
        'barber'=>array('team-barber-1.jpg','team-barber-2.jpg','team-barber-3.jpg'),
    );
    $staff_photos=$staff_photo_pools[$demo];
    while(count($data['staff'])<3) $data['staff'][]=array('name'=>$names[count($data['staff'])],'role'=>'متخصص '.$studio['fa'],'bio'=>$studio['about'],'experience'=>7,'image'=>$staff_photos[count($data['staff'])]);
    foreach($data['staff'] as $i=>&$staff_item)if(isset($staff_photos[$i]))$staff_item['image']=$staff_photos[$i];
    unset($staff_item);
    foreach($data['staff'] as $i=>$item) {
        $id=beautia_seed_post($demo,'staff-'.$i,'beautia_staff',$item['name'],'<p>'.$item['bio'].'</p><p>در '.$studio['name'].'، جلسه با شنیدن خواسته شما شروع می‌شود. برای انتخاب خدمت، هماهنگی زمان و مرور جزئیات می‌توانید از فرم رزرو استفاده کنید.</p>',$item['name']);
        update_post_meta($id,'_beautia_role',$item['role']);update_post_meta($id,'_beautia_experience',$item['experience']);update_post_meta($id,'_beautia_schedule',$schedule);
        beautia_seed_image($item['image']??'',$id,$studio['image']);$staff[]=$id;
    }
    foreach($services as $id) update_post_meta($id,'_beautia_staff_ids',$staff);
    $folio=$data['portfolio']??array();
    while(count($folio)<6) {
        $i=count($folio);$item=$data['services'][$i%count($data['services'])];
        $folio[]=array('title'=>$item['title'].' · دفتر الهام','image'=>$item['image']??$studio['image'],'category'=>$item['category']??'ایده‌های تازه','content'=>$studio['intro']);
    }
    // Portfolio photos deliberately use a separate, unique visual pool. This
    // prevents the common marketplace-demo flaw where the same service image
    // is repeated again in the inspiration gallery.
    $folio_pools=array(
        'nails'=>array('glam-nails.jpg','manicure-before-v2.jpg','manicure-after-v2.jpg','ba-nails-before.jpg','folio-nail-5.jpg','folio-nail-6.jpg'),
        'clinic'=>array('folio-clinic-1.jpg','folio-clinic-2.jpg','folio-clinic-3.jpg','folio-clinic-4.jpg','folio-clinic-5.jpg','folio-clinic-6.jpg'),
        'hair'=>array('glam-hair.jpg','folio-hair-2.jpg','folio-hair-3.jpg','folio-hair-4.jpg','folio-hair-5.jpg','folio-hair-6.jpg'),
        'spa'=>array('folio-spa-1.jpg','folio-spa-2.jpg','folio-spa-3.jpg','folio-spa-4.jpg','folio-spa-5.jpg','folio-spa-6.jpg'),
        'lashes'=>array('folio-lashes-1.jpg','folio-lashes-2.jpg','folio-lashes-3.jpg','folio-lashes-4.jpg','folio-lashes-5.jpg','folio-lashes-6.jpg'),
        'makeup'=>array('folio-makeup-1.jpg','folio-makeup-2.jpg','folio-makeup-3.jpg','folio-makeup-4.jpg','folio-makeup-5.jpg','folio-makeup-6.jpg'),
        'barber'=>array('folio-barber-1.jpg','folio-barber-2.jpg','folio-barber-3.jpg','folio-barber-4.jpg','folio-barber-5.jpg','folio-barber-6.jpg'),
    );
    foreach($folio as $i=>&$folio_item) {
        if(isset($folio_pools[$demo][$i]) && file_exists(BEAUTIA_DIR.'assets/img/'.$folio_pools[$demo][$i])) $folio_item['image']=$folio_pools[$demo][$i];
    }
    unset($folio_item);
    foreach($folio as $i=>$item) {
        if(empty($item['image']) && $photos)$item['image']=basename($photos[$i%count($photos)]);
        $id=beautia_seed_post($demo,'portfolio-'.$i,'beautia_portfolio',$item['title'],'<p>'.($item['content']??$studio['intro']).'</p><h2>جزئیات این انتخاب</h2><p>'.$realistic['details'][$i%count($realistic['details'])].'</p><h3>برای انتخاب مشابه</h3><p>این تصویر را در دفتر الهام خود ذخیره کنید. در جلسه، جزئیات دلخواهتان را نشان دهید تا انتخاب نهایی متناسب با شما انجام شود.</p>',$item['title']);
        wp_set_object_terms($id,$item['category']??'الهام','beautia_portfolio_cat');
        beautia_seed_image($item['image']??'',$id,$studio['image']);
    }
    foreach(($data['testimonials']??array()) as $i=>$item) {
        $texts=$realistic['quotes'];
        $id=beautia_seed_post($demo,'quote-'.$i,'beautia_testimonial',$item['name'],$texts[$i%3],$item['name']);
        update_post_meta($id,'_beautia_author_role',$data['services'][$i%count($data['services'])]['title']);update_post_meta($id,'_beautia_rating',5);
    }
    require_once BEAUTIA_DIR.'inc/showcase-editorial.php';
    $article_pools=array(
        'nails'=>array('article-nails-1.jpg','article-nails-2.jpg','article-nails-3.jpg'),
        'clinic'=>array('article-clinic-1.jpg','article-clinic-2.jpg','article-clinic-3.jpg'),
        'hair'=>array('article-hair-1.jpg','article-hair-2.jpg','article-hair-3.jpg'),
        'spa'=>array('article-spa-1.jpg','article-spa-2.jpg','article-spa-3.jpg'),
        'lashes'=>array('article-lashes-1.jpg','article-lashes-2.jpg','article-lashes-3.jpg'),
        'makeup'=>array('article-makeup-1.jpg','article-makeup-2.jpg','article-makeup-3.jpg'),
        'barber'=>array('article-barber-1.jpg','article-barber-2.jpg','article-barber-3.jpg'),
    );
    foreach(beautia_editorial($demo) as $i=>$article) {
        $id=beautia_seed_post($demo,'article-'.$i,'post',$article['title'],$article['content'],$data['posts'][$i]['title']??'');
        wp_set_object_terms($id,'مجله '.$studio['name'],'category');
        beautia_seed_image($article_pools[$demo][$i],$id,$studio['image']);
    }
    $home=beautia_seed_post($demo,'page-home','page',$studio['name'].' — '.$studio['fa'],'',$data['pages']['home']['title']);
    wp_update_post(array('ID'=>$home,'post_name'=>$studio['slug'],'post_parent'=>0));update_post_meta($home,'_wp_page_template','page-templates/studio.php');$entry['pages']['home']=$home;
    $pages=array(
        'about'=>array('درباره '.$studio['name'],'<p>'.$studio['about'].'</p><h2>پیش از مراجعه</h2><ul><li>'.implode('</li><li>',$studio['tips']).'</li></ul>[beautia_team count="3"]'),
        'services'=>array('خدمات و تعرفه‌ها','<p>فهرست خدمات تخصصی '.esc_html($studio['fa']).' در '.esc_html($studio['name']).'؛ جزئیات، زمان و تعرفه هر انتخاب را پیش از رزرو مقایسه کنید.</p>[beautia_services count="12"][beautia_pricing]'),
        'portfolio'=>array('دفتر الهام و نمونه‌کارها','<p>نمونه‌کارها و ایده‌های اختصاصی '.esc_html($studio['fa']).' در '.esc_html($studio['name']).'؛ انتخاب دلخواهتان را باز کنید یا برای مراجعه بعدی در دفتر الهام نگه دارید.</p>[beautia_portfolio count="24"]'),
        'blog'=>array('مجله '.$studio['name'],'<p>راهنماها و یادداشت‌های اختصاصی '.esc_html($studio['fa']).' از تیم '.esc_html($studio['name']).' برای انتخاب آگاهانه و مراقبت بهتر.</p>[beautia_blog count="12"]'),
        'contact'=>array('تماس و برنامه مراجعه','<div class="studio-contact"><h2>پیش از آمدن، هماهنگ کنیم.</h2><p>نشانی: '.esc_html($data['options']['address']).'</p><p>تلفن پذیرش: <b dir="ltr">'.esc_html($data['options']['phone']).'</b></p><p>شنبه تا پنجشنبه برای هماهنگی خدمت و زمان مراجعه همراه شما هستیم. برای انتخاب ساعت، از صفحه رزرو استفاده کنید.</p>[beautia_hours]</div>'),
        'booking'=>array('رزرو نوبت '.$studio['name'],''),
        'account'=>array('پنل من · '.$studio['name'],''),
        'login'=>array('ورود · '.$studio['name'],'')
    );
    foreach($pages as $key=>$p) {
        $legacy=$data['pages'][$key]['title']??'';
        $id=beautia_seed_post($demo,'page-'.$key,'page',$p[0],$p[1],$legacy);
        // Standalone demos keep clean top-level URLs. The page's demo binding
        // lives in metadata and must never be inferred from a nested URL path.
        wp_update_post(array('ID'=>$id,'post_parent'=>0,'post_name'=>$key));
        update_post_meta($id,'_wp_page_template',in_array($key,array('booking','account','login'),true)?'page-templates/'.$key.'.php':'default');
        $entry['pages'][$key]=$id;
    }
    $menu=wp_get_nav_menu_object('Beautia '.$demo);$menu_id=$menu?$menu->term_id:wp_create_nav_menu('Beautia '.$demo);
    $existing=wp_get_nav_menu_items($menu_id)?:array();$by_page=array();foreach($existing as $item)$by_page[$item->object_id]=$item->ID;
    $labels=array('home'=>'خانه','about'=>'درباره ما','services'=>'خدمات','portfolio'=>'نمونه‌کارها','blog'=>'مجله','contact'=>'تماس');$order=0;$menu_items=array();
    foreach($labels as $key=>$label){$pid=$entry['pages'][$key];$menu_items[$key]=wp_update_nav_menu_item($menu_id,$by_page[$pid]??0,array('menu-item-title'=>$label,'menu-item-object'=>'page','menu-item-object-id'=>$pid,'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-position'=>++$order));}
    // The services item is a native, plugin-free visual mega menu. Its child
    // cards are real service records with local imagery and accessible icons.
    if(!is_wp_error($menu_items['services'])){
        update_post_meta($menu_items['services'],'_beautia_mega','1');
        update_post_meta($menu_items['services'],'_beautia_mega_style','editorial');
        update_post_meta($menu_items['services'],'_beautia_menu_icon','sparkle');
        foreach(array_slice($services,0,6) as $service_index=>$service_id){
            $seed_key=$demo.':menu-service-'.$service_index;
            $found=get_posts(array('post_type'=>'nav_menu_item','post_status'=>'any','posts_per_page'=>1,'meta_key'=>'_beautia_seed_key','meta_value'=>$seed_key,'fields'=>'ids'));
            $thumb=get_the_post_thumbnail_url($service_id,'beautia-card');
            $item_id=wp_update_nav_menu_item($menu_id,$found?$found[0]:0,array('menu-item-title'=>get_the_title($service_id),'menu-item-object'=>'beautia_service','menu-item-object-id'=>$service_id,'menu-item-type'=>'post_type','menu-item-parent-id'=>$menu_items['services'],'menu-item-status'=>'publish','menu-item-position'=>++$order));
            if(!is_wp_error($item_id)){update_post_meta($item_id,'_beautia_seed_key',$seed_key);update_post_meta($item_id,'_beautia_menu_icon',array('sparkle','heart','calendar','user','shield','star')[$service_index]);update_post_meta($item_id,'_beautia_menu_image',esc_url_raw($thumb));}
        }
    }
    $entry['menu']=$menu_id;$entry['services']=$services;$entry['staff']=$staff;$map[$demo]=$entry;update_option('beautia_studio_map',$map,false);
    global $wpdb;$table=Beautia_Booking::table();
    foreach(array('confirmed','pending','completed','cancelled') as $i=>$status) {
        $code='DEMO-'.strtoupper($studio['slug']).'-'.($i+1);
        $existing_booking=$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE code=%s",$code));
        $customer=$realistic['customers'][$i];
        if($existing_booking){$wpdb->update($table,array('customer_name'=>$customer),array('id'=>$existing_booking,'source'=>'demo'));continue;}
        $day=strtotime(($i<2?'+':'-').($i+1).' days');while(wp_date('w',$day)==='5')$day=strtotime('+1 day',$day);
        $start=wp_date('Y-m-d',$day).' '.array('10:00:00','15:00:00','11:00:00','17:00:00')[$i];$duration=(int)get_post_meta($services[$i],'_beautia_duration',true);
        $wpdb->insert($table,array('code'=>$code,'service_id'=>$services[$i],'staff_id'=>$staff[$i%count($staff)],'customer_name'=>$customer,'customer_mobile'=>'','start_at'=>$start,'end_at'=>gmdate('Y-m-d H:i:s',strtotime($start)+$duration*60),'duration'=>$duration,'price'=>get_post_meta($services[$i],'_beautia_price',true),'status'=>$status,'source'=>'demo','admin_note'=>'نوبت نمایشی '.$studio['name'].'؛ پیامک ارسال نشود.','created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql'),'reminded'=>1));
    }
    unset($GLOBALS['beautia_seed_demo'],$GLOBALS['beautia_seeding']);
    return true;
}
