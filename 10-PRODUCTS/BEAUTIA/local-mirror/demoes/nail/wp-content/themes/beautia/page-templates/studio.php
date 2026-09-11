<?php
/** Template Name: Beautia Studio */
defined('ABSPATH') || exit;
get_header();
while(have_posts()) { the_post();
    $built_with_elementor = 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true );
    if ( $built_with_elementor || trim( get_the_content() ) ) {
        the_content();
        continue;
    }
    $demo=beautia_demo_context(); $studio=beautia_studios()[$demo];
    /* Keep every studio visually and semantically independent. */
    $about_images=array(
        'nails'=>array('hero-nails.jpg','folio-nail-5.jpg'),
        'clinic'=>array('hero-clinic.jpg','folio-clinic-5.jpg'),
        'hair'=>array('hero-hair.jpg','folio-hair-5.jpg'),
        'spa'=>array('hero-spa.jpg','folio-spa-5.jpg'),
        'lashes'=>array('hero-lashes.jpg','folio-lashes-5.jpg'),
        'makeup'=>array('hero-makeup.jpg','folio-makeup-5.jpg'),
        'barber'=>array('hero-barber.jpg','folio-barber-5.jpg'),
    );
    $profiles=array(
        'nails'=>array('order'=>'services,fit,comparison,signature,portfolio,about,availability,testimonials,team,blog,faq','services'=>6,'portfolio'=>6,'team'=>3,'blog'=>3,'about'=>'ظرافت، از انتخاب رنگ تا آخرین جزئیات.','years'=>'8'),
        'clinic'=>array('order'=>'about,signature,services,team,availability,portfolio,testimonials,faq,blog','services'=>4,'portfolio'=>4,'team'=>4,'blog'=>2,'about'=>'زیبایی ایمن، بر پایه ارزیابی و پروتکل حرفه‌ای.','years'=>'11'),
        'hair'=>array('order'=>'portfolio,signature,services,team,about,availability,testimonials,blog,faq','services'=>6,'portfolio'=>8,'team'=>4,'blog'=>3,'about'=>'هر تغییر، با یک مشاوره دقیق شروع می‌شود.','years'=>'9'),
        'spa'=>array('order'=>'about,services,signature,availability,testimonials,portfolio,team,faq,blog','services'=>4,'portfolio'=>5,'team'=>3,'blog'=>2,'about'=>'آرامش، یک تجربه طراحی‌شده است.','years'=>'10'),
        'lashes'=>array('order'=>'portfolio,signature,services,about,availability,testimonials,team,faq,blog','services'=>5,'portfolio'=>7,'team'=>3,'blog'=>2,'about'=>'فرم چشم شما، نقطه شروع طراحی مژه است.','years'=>'7'),
        'makeup'=>array('order'=>'portfolio,team,signature,services,about,availability,testimonials,blog,faq','services'=>6,'portfolio'=>9,'team'=>4,'blog'=>3,'about'=>'چهره شما، امضای اصلی هر میکاپ است.','years'=>'12'),
        'barber'=>array('order'=>'services,team,signature,portfolio,availability,about,testimonials,faq,blog','services'=>5,'portfolio'=>6,'team'=>4,'blog'=>3,'about'=>'استایل دقیق، با شناخت فرم چهره ساخته می‌شود.','years'=>'10'),
    );
    $profile=$profiles[$demo];
    beautia_studio_hero();
    $saved=get_option('beautia_studio_content_overrides',array());
    $legacy_order='services,comparison,portfolio,availability,about,team,testimonials,blog,faq';
    $saved_order=isset($saved[$demo]['section_order'])?$saved[$demo]['section_order']:'';
    $order=(!$saved_order||$legacy_order===$saved_order)?$profile['order']:$saved_order;
    $sections=array_filter(array_map('sanitize_key',explode(',',$order)));
    if('nails'===$demo&&!in_array('fit',$sections,true)){
        $service_pos=array_search('services',$sections,true);
        array_splice($sections,false===$service_pos?0:$service_pos+1,0,array('fit'));
    }
    foreach($sections as $section){
        if('services'===$section)beautia_section_services(array('count'=>$profile['services']));
        elseif('fit'===$section&&'nails'===$demo)beautia_nail_fit_lab();
        elseif('comparison'===$section&&'nails'===$demo)beautia_nail_comparison();
        elseif('signature'===$section)beautia_studio_specialty();
        elseif('portfolio'===$section)beautia_section_portfolio(array('count'=>$profile['portfolio']));
        elseif('availability'===$section)beautia_studio_appointments();
        elseif('about'===$section)beautia_section_about(array('title'=>$profile['about'],'text'=>$studio['about'],'points'=>$studio['tips'],'image'=>beautia_img($about_images[$demo][0]),'image2'=>beautia_img($about_images[$demo][1]),'years'=>$profile['years']));
        elseif('team'===$section)beautia_section_team(array('count'=>$profile['team']));
        elseif('testimonials'===$section)beautia_section_testimonials();
        elseif('blog'===$section)beautia_section_blog($profile['blog']);
        elseif('faq'===$section)beautia_section_faq();
    }
}
get_footer();
