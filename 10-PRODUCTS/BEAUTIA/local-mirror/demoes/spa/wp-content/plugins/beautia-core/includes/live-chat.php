<?php
/** Theme-native support inbox. Public conversations require an unguessable token. */
defined('ABSPATH') || exit;
add_action('init',function(){
 register_post_type('beautia_chat',array('labels'=>array('name'=>'گفت‌وگوهای پشتیبانی','singular_name'=>'گفت‌وگو','edit_item'=>'پاسخ به گفت‌وگو'),'public'=>false,'show_ui'=>true,'show_in_rest'=>false,'menu_icon'=>'dashicons-format-chat','supports'=>array('title'),'capability_type'=>'post','capabilities'=>array('create_posts'=>'do_not_allow','edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options','read_private_posts'=>'manage_options','delete_posts'=>'manage_options'),'map_meta_cap'=>true));
});
add_action('customize_register',function($c){
 $c->add_section('beautia_support',array('title'=>'گفت‌وگو و پشتیبانی','priority'=>45));
 foreach(array('support_enabled'=>array('نمایش دکمه گفت‌وگو','checkbox',true),'support_whatsapp'=>array('شماره واتساپ با کد کشور، مانند 98912…','text',''),'support_telegram'=>array('نام کاربری تلگرام، بدون @','text',''),'support_online'=>array('کارشناس اکنون آماده پاسخ‌گویی است','checkbox',false)) as $key=>$v){
  $c->add_setting('beautia_'.$key,array('default'=>$v[2],'sanitize_callback'=>$v[1]==='checkbox'?'rest_sanitize_boolean':'sanitize_text_field'));
  $c->add_control('beautia_'.$key,array('section'=>'beautia_support','label'=>$v[0],'type'=>$v[1]));
 }
});
function beautia_chat_messages($id){
 $rows=get_comments(array('post_id'=>$id,'type'=>'beautia_support','status'=>'approve','order'=>'ASC','number'=>200));
 return array_map(function($m){return array('id'=>(int)$m->comment_ID,'text'=>$m->comment_content,'role'=>$m->user_id?'agent':'visitor','time'=>mysql2date('H:i',$m->comment_date));},$rows);
}
function beautia_chat_endpoint(){
 check_ajax_referer('beautia_chat','nonce');
 if(!get_theme_mod('beautia_support_enabled',true))wp_send_json_error(array('message'=>'گفت‌وگو غیرفعال است.'),403);
 $token=sanitize_text_field(wp_unslash($_POST['token']??''));
 if(!preg_match('/^[a-f0-9]{64}$/',$token))wp_send_json_error(array('message'=>'شناسه گفت‌وگو معتبر نیست.'),400);
 $hash=hash('sha256',$token);$ids=get_posts(array('post_type'=>'beautia_chat','post_status'=>'private','fields'=>'ids','posts_per_page'=>1,'meta_key'=>'_chat_token','meta_value'=>$hash));$id=$ids?(int)$ids[0]:0;
 $text=sanitize_textarea_field(wp_unslash($_POST['message']??''));
 if($text!==''){
  if(mb_strlen($text)>2000)wp_send_json_error(array('message'=>'پیام را کوتاه‌تر از ۲۰۰۰ نویسه بنویسید.'),400);
  $rate='beautia_chat_'.hash('sha256',($_SERVER['REMOTE_ADDR']??'local'));$count=(int)get_transient($rate);
  if($count>=20)wp_send_json_error(array('message'=>'کمی صبر کنید و دوباره پیام بفرستید.'),429);
  set_transient($rate,$count+1,MINUTE_IN_SECONDS);
  if(!$id){$demo=sanitize_key($_POST['demo']??'');$name=isset(beautia_studios()[$demo])?beautia_studios()[$demo]['name']:'بیوتیا';$id=wp_insert_post(array('post_type'=>'beautia_chat','post_status'=>'private','post_title'=>$name.' · '.wp_date('Y/m/d H:i')),true);if(is_wp_error($id))wp_send_json_error(array('message'=>'ثبت پیام انجام نشد.'),500);update_post_meta($id,'_chat_token',$hash);update_post_meta($id,'_beautia_demo',$demo);}
  if(count(beautia_chat_messages($id))>=200)wp_send_json_error(array('message'=>'این گفت‌وگو به سقف پیام رسیده است.'),400);
  wp_insert_comment(array('comment_post_ID'=>$id,'comment_content'=>$text,'comment_type'=>'beautia_support','comment_approved'=>1,'comment_author'=>'مراجعه‌کننده','user_id'=>0));
  update_post_meta($id,'_chat_unread',1);
 }
 wp_send_json_success(array('messages'=>$id?beautia_chat_messages($id):array(),'online'=>(bool)get_theme_mod('beautia_support_online',false)));
}
add_action('wp_ajax_beautia_chat','beautia_chat_endpoint');add_action('wp_ajax_nopriv_beautia_chat','beautia_chat_endpoint');
add_action('add_meta_boxes_beautia_chat',function(){add_meta_box('beautia_support_messages','پیام‌ها و پاسخ کارشناس',function($post){
 update_post_meta($post->ID,'_chat_unread',0);
 foreach(beautia_chat_messages($post->ID) as $m)echo '<p style="padding:15px;background:'.($m['role']==='agent'?'#e9f3ef':'#f8f1f7').';border-radius:8px"><strong>'.($m['role']==='agent'?'کارشناس':'مراجعه‌کننده').' · '.esc_html($m['time']).'</strong><br>'.nl2br(esc_html($m['text'])).'</p>';
 wp_nonce_field('beautia_chat_reply','beautia_reply_nonce');echo '<label for="beautia_reply">پاسخ شما</label><textarea id="beautia_reply" name="beautia_reply" rows="4" maxlength="2000" style="width:100%"></textarea><p>پاسخ را بنویسید و دکمه «به‌روزرسانی» را بزنید. پاسخ در پنجره مراجعه‌کننده نمایش داده می‌شود.</p>';
},'beautia_chat','normal','high');});
add_action('save_post_beautia_chat',function($id){
 if(wp_is_post_revision($id)||!current_user_can('manage_options')||empty($_POST['beautia_reply_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beautia_reply_nonce'])),'beautia_chat_reply'))return;
 $text=sanitize_textarea_field(wp_unslash($_POST['beautia_reply']??''));if($text==='')return;
 wp_insert_comment(array('comment_post_ID'=>$id,'comment_content'=>mb_substr($text,0,2000),'comment_type'=>'beautia_support','comment_approved'=>1,'comment_author'=>'کارشناس','user_id'=>get_current_user_id()));
});
add_filter('manage_beautia_chat_posts_columns',function($cols){$cols['chat_unread']='پیام جدید';return $cols;});
add_action('manage_beautia_chat_posts_custom_column',function($col,$id){if($col==='chat_unread')echo get_post_meta($id,'_chat_unread',true)?'● خوانده نشده':'خوانده شده';},10,2);
add_action('wp_enqueue_scripts',function(){if(!get_theme_mod('beautia_support_enabled',true))return;
 wp_enqueue_style('beautia-chat',BEAUTIA_URI.'assets/css/live-chat.css',array(),BEAUTIA_VERSION);
 wp_enqueue_script('beautia-chat',BEAUTIA_URI.'assets/js/live-chat.js',array(),BEAUTIA_VERSION,true);
 wp_localize_script('beautia-chat','BeautiaChat',array('url'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('beautia_chat'),'demo'=>beautia_demo_context()));
});
add_action('wp_footer',function(){if(!get_theme_mod('beautia_support_enabled',true))return;
 $wa=preg_replace('/\D/','',get_theme_mod('beautia_support_whatsapp',''));$tg=preg_replace('/[^a-zA-Z0-9_]/','',get_theme_mod('beautia_support_telegram',''));
 ?>
 <div class="beautia-support" dir="rtl"><button class="support-toggle" aria-label="گفت‌وگو با ما" aria-controls="support-panel" aria-expanded="false"><?php beautia_icon('message',22); ?><span>گفت‌وگو با ما</span></button>
 <section id="support-panel" hidden aria-label="گفت‌وگو با پشتیبانی"><header><div><strong>کنار شما هستیم</strong><small class="support-presence">پیامتان را بگذارید؛ کارشناس پاسخ می‌دهد.</small></div><button class="support-close" aria-label="بستن گفت‌وگو">×</button></header>
 <?php if($wa||$tg): ?><nav class="support-channels" aria-label="راه‌های ارتباطی"><?php if($wa): ?><a href="https://wa.me/<?php echo esc_attr($wa); ?>" target="_blank" rel="noopener noreferrer">واتساپ ↗</a><?php endif; ?><?php if($tg): ?><a href="https://t.me/<?php echo esc_attr($tg); ?>" target="_blank" rel="noopener noreferrer">تلگرام ↗</a><?php endif; ?></nav><?php endif; ?>
 <div class="support-messages" role="log" aria-live="polite" aria-relevant="additions"><p class="support-welcome">سلام، خوش آمدید. درباره خدمات یا انتخاب نوبت چه سؤالی دارید؟</p></div><p class="support-error" role="status"></p><form class="support-form"><label class="screen-reader-text" for="support-message">متن پیام</label><textarea id="support-message" maxlength="2000" rows="2" required placeholder="پیامتان را بنویسید…"></textarea><button type="submit" aria-label="ارسال پیام">←</button></form></section></div>
 <?php
},30);
