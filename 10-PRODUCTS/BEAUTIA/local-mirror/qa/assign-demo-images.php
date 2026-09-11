<?php
declare(strict_types=1);
if($argc<4)exit(2);
$folder=preg_replace('/[^a-z]/','',$argv[1]);$postType=preg_replace('/[^a-z_]/','',$argv[2]??'');$files=array_slice($argv,3);
if(!in_array($folder,['nail','clinic','spa','hair','makeup','lashes','barber'],true)||!in_array($postType,['beautia_portfolio','beautia_staff','post'],true))exit(2);
require dirname(__DIR__).'/demoes/'.$folder.'/wp-load.php';
require_once ABSPATH.'wp-admin/includes/image.php';
$posts=get_posts(['post_type'=>$postType,'post_status'=>'any','posts_per_page'=>count($files),'orderby'=>'ID','order'=>'ASC']);
if(count($posts)!==count($files))throw new RuntimeException('Post/file count mismatch');
foreach($posts as $i=>$post){$filename=basename($files[$i]);$source=get_template_directory().'/assets/img/'.$filename;if(!is_file($source))throw new RuntimeException('Missing '.$source);$existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','meta_key'=>'_beautia_source_asset','meta_value'=>$filename,'posts_per_page'=>1,'fields'=>'ids']);if($existing){$attachment=(int)$existing[0];}else{$upload=wp_upload_bits($filename,null,file_get_contents($source));if($upload['error'])throw new RuntimeException($upload['error']);$attachment=wp_insert_attachment(['post_mime_type'=>'image/jpeg','post_title'=>$post->post_title,'post_status'=>'inherit'],$upload['file']);wp_update_attachment_metadata($attachment,wp_generate_attachment_metadata($attachment,$upload['file']));update_post_meta($attachment,'_beautia_source_asset',$filename);}update_post_meta($attachment,'_wp_attachment_image_alt',$post->post_title);set_post_thumbnail($post->ID,$attachment);echo $post->ID."\t".$filename.PHP_EOL;}
