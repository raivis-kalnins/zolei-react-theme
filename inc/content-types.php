<?php
if (!defined('ABSPATH')) { exit; }

function zolei_register_pdf_type() {
    register_post_type('zolei_pdf', array(
        'labels' => array(
            'name' => __('Zolei PDF files','zolei-react'),
            'singular_name' => __('Zolei PDF file','zolei-react'),
            'add_new_item' => __('Add PDF file','zolei-react'),
            'edit_item' => __('Edit PDF file','zolei-react'),
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-media-document',
        'supports' => array('title','editor','excerpt'),
        'has_archive' => false,
        'rewrite' => array('slug'=>'dokumenti'),
        'show_in_rest' => true,
    ));
}
add_action('init','zolei_register_pdf_type');

function zolei_pdf_meta_box() {
    add_meta_box('zolei_pdf_details', __('PDF details','zolei-react'), 'zolei_pdf_meta_box_html', 'zolei_pdf', 'normal', 'high');
}
add_action('add_meta_boxes','zolei_pdf_meta_box');

function zolei_pdf_meta_box_html($post) {
    wp_nonce_field('zolei_save_pdf_meta','zolei_pdf_nonce');
    $url = get_post_meta($post->ID,'_zolei_pdf_url',true);
    $month = get_post_meta($post->ID,'_zolei_pdf_month',true);
    $type = get_post_meta($post->ID,'_zolei_pdf_type',true);
    $year = get_post_meta($post->ID,'_zolei_pdf_year',true);
    $months = array('janvaris'=>'Janvāris','februaris'=>'Februāris','marts'=>'Marts','aprilis'=>'Aprīlis','maijs'=>'Maijs','junijs'=>'Jūnijs','julijs'=>'Jūlijs','augusts'=>'Augusts','septembris'=>'Septembris','oktobris'=>'Oktobris','novembris'=>'Novembris','decembris'=>'Decembris');
    $types = array('rezultati'=>'Rezultāti','reitings'=>'Reitings','nolikums'=>'Nolikums','arhivs'=>'Arhīvs');
    ?>
    <p><label><strong><?php esc_html_e('PDF URL','zolei-react'); ?></strong></label></p>
    <p><input type="url" class="large-text" id="zolei_pdf_url" name="zolei_pdf_url" value="<?php echo esc_attr($url); ?>"> <button type="button" class="button" id="zolei_pdf_upload"><?php esc_html_e('Choose/upload PDF','zolei-react'); ?></button></p>
    <p><label><strong><?php esc_html_e('Month','zolei-react'); ?></strong></label></p>
    <p><select name="zolei_pdf_month"><option value=""><?php esc_html_e('No month','zolei-react'); ?></option><?php foreach($months as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($month,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></p>
    <p><label><strong><?php esc_html_e('Type','zolei-react'); ?></strong></label></p>
    <p><select name="zolei_pdf_type"><?php foreach($types as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($type ?: 'rezultati',$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></p>
    <p><label><strong><?php esc_html_e('Year','zolei-react'); ?></strong></label></p>
    <p><input type="number" name="zolei_pdf_year" value="<?php echo esc_attr($year ?: date('Y')); ?>" min="2000" max="2100"></p>
    <script>
    jQuery(function($){$('#zolei_pdf_upload').on('click',function(e){e.preventDefault();var frame=wp.media({title:'<?php echo esc_js(__('Choose PDF','zolei-react')); ?>',button:{text:'<?php echo esc_js(__('Use this file','zolei-react')); ?>'},multiple:false,library:{type:'application/pdf'}});frame.on('select',function(){var f=frame.state().get('selection').first().toJSON();$('#zolei_pdf_url').val(f.url);});frame.open();});});
    </script>
    <?php
}

function zolei_save_pdf_meta($post_id) {
    if (!isset($_POST['zolei_pdf_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['zolei_pdf_nonce'])),'zolei_save_pdf_meta')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post',$post_id)) { return; }
    update_post_meta($post_id,'_zolei_pdf_url',esc_url_raw(wp_unslash($_POST['zolei_pdf_url'] ?? '')));
    update_post_meta($post_id,'_zolei_pdf_month',sanitize_key(wp_unslash($_POST['zolei_pdf_month'] ?? '')));
    update_post_meta($post_id,'_zolei_pdf_type',sanitize_key(wp_unslash($_POST['zolei_pdf_type'] ?? 'rezultati')));
    update_post_meta($post_id,'_zolei_pdf_year',absint($_POST['zolei_pdf_year'] ?? date('Y')));
}
add_action('save_post_zolei_pdf','zolei_save_pdf_meta');

function zolei_pdf_admin_assets($hook) {
    global $post;
    if (($hook === 'post.php' || $hook === 'post-new.php') && $post && $post->post_type === 'zolei_pdf') { wp_enqueue_media(); }
}
add_action('admin_enqueue_scripts','zolei_pdf_admin_assets');

function zolei_results_shortcode($atts = array()) {
    $atts = shortcode_atts(array('type'=>'','month'=>'','limit'=>-1), $atts, 'zolei_results');
    $meta = array();
    if ($atts['type']) { $meta[] = array('key'=>'_zolei_pdf_type','value'=>sanitize_key($atts['type'])); }
    if ($atts['month']) { $meta[] = array('key'=>'_zolei_pdf_month','value'=>sanitize_key($atts['month'])); }
    $q = new WP_Query(array('post_type'=>'zolei_pdf','posts_per_page'=>intval($atts['limit']),'post_status'=>'publish','meta_query'=>$meta,'orderby'=>'date','order'=>'DESC'));
    ob_start();
    echo '<div class="zole-results-list">';
    if ($q->have_posts()) {
        while ($q->have_posts()) { $q->the_post();
            $url = get_post_meta(get_the_ID(),'_zolei_pdf_url',true);
            $type = get_post_meta(get_the_ID(),'_zolei_pdf_type',true);
            $month = get_post_meta(get_the_ID(),'_zolei_pdf_month',true);
            $year = get_post_meta(get_the_ID(),'_zolei_pdf_year',true);
            echo '<article class="zole-result-item"><div><span class="zole-result-type">'.esc_html(ucfirst($type ?: 'PDF')).'</span><h3>'.esc_html(get_the_title()).'</h3><p>'.esc_html(trim(($month ? $month.' ' : '').($year ?: ''))).'</p></div>';
            if ($url) { echo '<a class="zole-btn zole-btn-green" target="_blank" rel="noopener" href="'.esc_url($url).'">PDF</a>'; }
            echo '</article>';
        }
    } else {
        echo '<p>'.esc_html(zolei_i18n('PDF faili vēl nav pievienoti.','No PDF files have been added yet.')).'</p>';
    }
    wp_reset_postdata();
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('zolei_results','zolei_results_shortcode');

function zolei_calendar_shortcode() {
    $months = zolei_calendar_months(); $current = intval(current_time('n')); ob_start();
    echo '<div class="zole-shortcode-calendar"><div class="zole-month-tabs" role="tablist">';
    foreach($months as $i=>$m){ $active = ($i+1)===$current ? ' active' : ''; echo '<button class="zole-month-tab'.$active.'" type="button" data-bs-toggle="pill" data-bs-target="#sc-'.$m['slug'].'"><span>'.esc_html(zolei_lang_is_en()?($m['en']??$m['lv']):($m['lv']??$m['en'])).'</span><em>'.count($m['events']??array()).'</em></button>'; }
    echo '</div><div class="tab-content zole-calendar-panel">';
    foreach($months as $i=>$m){ $active = ($i+1)===$current ? ' show active' : ''; echo '<div id="sc-'.$m['slug'].'" class="tab-pane fade'.$active.'"><div class="zole-event-grid">'; foreach(($m['events']??array()) as $ev){ echo '<article class="zole-event"><div class="zole-event-date">'.esc_html($ev['day']??'').'</div><p>'.esc_html($ev['title']??'').'</p></article>'; } echo '</div></div>'; }
    echo '</div></div>'; return ob_get_clean();
}
add_shortcode('zolei_calendar','zolei_calendar_shortcode');
