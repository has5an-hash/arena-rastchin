<?php
declare(strict_types=1);
$folder=preg_replace('/[^a-z]/','',$argv[1]??'');
if(!in_array($folder,['nail','clinic','spa','hair','makeup','lashes','barber'],true))exit(2);
require __DIR__.'/demoes/'.$folder.'/wp-load.php';
$rows=[];
foreach(['beautia_service','beautia_portfolio','beautia_staff','beautia_testimonial','post','page'] as $type){
 $posts=get_posts(['post_type'=>$type,'post_status'=>'any','posts_per_page'=>-1,'orderby'=>'ID','order'=>'ASC']);
 foreach($posts as $post){
  $thumb=get_post_thumbnail_id($post->ID);
  $plain=trim(preg_replace('/\s+/u',' ',wp_strip_all_tags($post->post_content)));
  $rows[]=['type'=>$type,'id'=>$post->ID,'title'=>$post->post_title,'demo'=>get_post_meta($post->ID,'_beautia_demo',true),'image'=>$thumb?basename((string)get_attached_file($thumb)):'','content_hash'=>$plain?hash('sha256',$plain):'','literal_slashes'=>substr_count($post->post_content,'\\n\\n')+substr_count($post->post_title,'\\n\\n')];
 }
}
$mods=get_theme_mods();
echo json_encode(['folder'=>$folder,'url'=>home_url('/'),'active_demo'=>function_exists('beautia_get_active_demo')?beautia_get_active_demo():'','showcase'=>get_option('beautia_showcase_enabled'),'colors'=>array_filter($mods,fn($v,$k)=>str_starts_with((string)$k,'beautia_color_'),ARRAY_FILTER_USE_BOTH),'rows'=>$rows],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
