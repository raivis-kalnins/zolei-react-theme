<?php
if (!defined('ABSPATH')) { exit; }
function zolei_ajax_search() {
    check_ajax_referer('zolei_search_nonce','nonce');
    $term = sanitize_text_field(wp_unslash($_GET['term'] ?? ''));
    if (mb_strlen($term) < 2) { wp_send_json_success(array('items'=>array())); }
    $args = array('s'=>$term,'post_type'=>array('post','page','zolei_pdf'),'post_status'=>'publish','posts_per_page'=>8,'ignore_sticky_posts'=>true);
    if (function_exists('pll_current_language')) { $args['lang'] = zolei_lang(); }
    $q = new WP_Query($args);
    $items = array();
    while($q->have_posts()) { $q->the_post();
        $type = get_post_type();
        $url = get_permalink();
        if ($type === 'zolei_pdf') { $pdf = get_post_meta(get_the_ID(),'_zolei_pdf_url',true); if ($pdf) { $url = $pdf; } }
        $items[] = array('title'=>get_the_title(),'url'=>$url,'type'=>$type,'excerpt'=>wp_trim_words(wp_strip_all_tags(get_the_excerpt() ?: get_the_content()),14));
    }
    wp_reset_postdata();
    wp_send_json_success(array('items'=>$items,'searchUrl'=>home_url('/?s='.rawurlencode($term))));
}
add_action('wp_ajax_zolei_ajax_search','zolei_ajax_search');
add_action('wp_ajax_nopriv_zolei_ajax_search','zolei_ajax_search');
