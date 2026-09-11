<?php
/**
 * Sidebar.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;

// Keep the magazine sidebar Persian and scoped to the current studio.
$studio_demo = function_exists('beautia_demo_context') ? beautia_demo_context() : '';
if ($studio_demo) {
    $recent = new WP_Query(array('post_type'=>'post','posts_per_page'=>5,'no_found_rows'=>true,'meta_query'=>array(array('key'=>'_beautia_demo','value'=>$studio_demo))));
    echo '<aside class="content-aside widget-area"><section class="widget"><h2 class="widget-title">تازه‌های مجله</h2><ul>';
    while ($recent->have_posts()) { $recent->the_post(); echo '<li><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></li>'; }
    wp_reset_postdata();
    echo '</ul></section><section class="widget"><h2 class="widget-title">وقتِ زیبایی شما</h2><p>خدمات و متخصصان این مجموعه را ببینید و زمان مناسب خودتان را انتخاب کنید.</p><a class="btn btn-primary" href="'.esc_url(beautia_studio_url($studio_demo,'booking')).'">رزرو نوبت ←</a></section></aside>';
    return;
}

if ( ! is_active_sidebar( 'sidebar-main' ) ) {
	return;
}
?>
<aside class="content-aside widget-area">
	<?php dynamic_sidebar( 'sidebar-main' ); ?>
</aside>
