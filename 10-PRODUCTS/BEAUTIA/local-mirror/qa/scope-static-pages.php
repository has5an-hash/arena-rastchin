<?php
declare(strict_types=1);

$folder=preg_replace('/[^a-z]/','',$argv[1]??'');
if(!in_array($folder,['nail','clinic','spa','hair','makeup','lashes','barber'],true))exit(2);
require dirname(__DIR__).'/demoes/'.$folder.'/wp-load.php';
$demo=$folder==='nail'?'nails':$folder;
$studio=beautia_studios()[$demo];
$pages=[
    'services'=>'<p>فهرست خدمات تخصصی '.esc_html($studio['fa']).' در '.esc_html($studio['name']).'؛ جزئیات، زمان و تعرفه هر انتخاب را پیش از رزرو مقایسه کنید.</p>[beautia_services count="12"][beautia_pricing]',
    'portfolio'=>'<p>نمونه‌کارها و ایده‌های اختصاصی '.esc_html($studio['fa']).' در '.esc_html($studio['name']).'؛ انتخاب دلخواهتان را باز کنید یا برای مراجعه بعدی در دفتر الهام نگه دارید.</p>[beautia_portfolio count="24"]',
    'blog'=>'<p>راهنماها و یادداشت‌های اختصاصی '.esc_html($studio['fa']).' از تیم '.esc_html($studio['name']).' برای انتخاب آگاهانه و مراقبت بهتر.</p>[beautia_blog count="12"]',
];
foreach($pages as $slug=>$content){
    $page=get_page_by_path($slug,OBJECT,'page');
    if(!$page)throw new RuntimeException('Missing page '.$slug);
    $result=wp_update_post(['ID'=>$page->ID,'post_content'=>$content],true);
    if(is_wp_error($result))throw new RuntimeException($result->get_error_message());
    echo $folder."\t".$slug."\t".$page->ID.PHP_EOL;
}
