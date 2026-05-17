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

function zolei_pdf_types() {
    return array(
        'rezultati' => __('Rezultāti','zolei-react'),
        'reitings' => __('Reitingi','zolei-react'),
        'nolikums' => __('Nolikumi','zolei-react'),
        'protokoli' => __('Protokoli','zolei-react'),
        'arhivs' => __('Arhīvs','zolei-react'),
    );
}

function zolei_pdf_directory_defaults() {
    return array(
        'rezultati' => 'rezultati',
        'reitings' => 'reitingi',
        'nolikums' => 'nolikumi',
        'protokoli' => 'protokoli',
        'arhivs' => 'arhivs',
    );
}
function zolei_pdf_directories() {
    $saved = get_option('zolei_pdf_directories', array());
    $defaults = zolei_pdf_directory_defaults();
    $dirs = array();
    foreach ($defaults as $type => $dir) {
        $candidate = isset($saved[$type]) ? sanitize_text_field($saved[$type]) : $dir;
        $candidate = trim(str_replace('\\', '/', $candidate), '/');
        $candidate = preg_replace('~[^A-Za-z0-9_\-./]~', '', $candidate);
        $candidate = preg_replace('~\.{2,}~', '', $candidate);
        $dirs[$type] = $candidate ?: $dir;
    }
    return $dirs;
}
function zolei_pdf_dir_for_type($type) {
    $dirs = zolei_pdf_directories();
    $type = sanitize_key($type);
    return $dirs[$type] ?? ($dirs['rezultati'] ?? 'rezultati');
}
function zolei_pdf_months() {
    return array('janvaris'=>'Janvāris','februaris'=>'Februāris','marts'=>'Marts','aprilis'=>'Aprīlis','maijs'=>'Maijs','junijs'=>'Jūnijs','julijs'=>'Jūlijs','augusts'=>'Augusts','septembris'=>'Septembris','oktobris'=>'Oktobris','novembris'=>'Novembris','decembris'=>'Decembris');
}
function zolei_pdf_target_path($relative_path = '') {
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) { return array('', ''); }
    $relative_path = trim(str_replace('\\', '/', (string) $relative_path), '/');
    $relative_path = preg_replace('~\.{2,}~', '', $relative_path);
    $relative_path = preg_replace('~^wp-content/uploads/~', '', $relative_path);
    if ($relative_path === '') { return array('', ''); }
    return array(trailingslashit($upload['basedir']) . $relative_path, trailingslashit($upload['baseurl']) . str_replace('%2F', '/', rawurlencode($relative_path)));
}
function zolei_pdf_file_url_from_relative_path($relative_path) {
    list($path, $url) = zolei_pdf_target_path($relative_path);
    return $path && file_exists($path) ? $url : '';
}

function zolei_pdf_section_defaults() {
    return array(
        'rules' => array('enabled'=>1,'type'=>'nolikums','title_lv'=>'Zoles noteikumi','title_en'=>'Rules','text_lv'=>'PDF faili ir tie paši abās valodās; tulkots ir tikai sadaļas teksts un pogas.','text_en'=>'PDF files stay the same in both languages; only labels and text are translated.'),
        'results' => array('enabled'=>1,'type'=>'rezultati','title_lv'=>'Rezultāti','title_en'=>'Results','text_lv'=>'Turnīru rezultātu PDF faili.','text_en'=>'Tournament results PDF files.'),
        'ratings' => array('enabled'=>1,'type'=>'reitings','title_lv'=>'Reitingi','title_en'=>'Ratings','text_lv'=>'Meistaru, lielmeistaru un elites reitingu PDF faili.','text_en'=>'Masters, grandmasters and elite ratings PDF files.'),
        'protocols' => array('enabled'=>1,'type'=>'protokoli','title_lv'=>'Protokoli','title_en'=>'Protocols','text_lv'=>'Turnīru protokoli un saistītie PDF faili.','text_en'=>'Tournament protocols and related PDF files.'),
        'archive' => array('enabled'=>1,'type'=>'arhivs','title_lv'=>'Arhīvs','title_en'=>'Archive','text_lv'=>'Vēsturiskie dokumenti un PDF arhīvs.','text_en'=>'Historical documents and PDF archive.'),
    );
}
function zolei_pdf_sections() {
    $saved = get_option('zolei_pdf_sections', array());
    $defaults = zolei_pdf_section_defaults();
    foreach ($defaults as $key => $row) {
        if (!isset($saved[$key]) || !is_array($saved[$key])) { $saved[$key] = array(); }
        $saved[$key] = array_merge($row, $saved[$key]);
    }
    return $saved;
}


function zolei_register_section_type() {
    register_post_type('zolei_section', array(
        'labels'=>array('name'=>__('One-page sections','zolei-react'),'singular_name'=>__('One-page section','zolei-react'),'add_new_item'=>__('Add section','zolei-react'),'edit_item'=>__('Edit section','zolei-react')),
        'public'=>false,
        'show_ui'=>true,
        'show_in_menu'=>false,
        'show_in_rest'=>true,
        'menu_icon'=>'dashicons-layout',
        'supports'=>array('title','editor','page-attributes','revisions'),
        'rewrite'=>false,
    ));
}
add_action('init','zolei_register_section_type');

function zolei_section_meta_box() {
    add_meta_box('zolei_section_details', __('One-page section settings','zolei-react'), 'zolei_section_meta_box_html', 'zolei_section', 'side', 'high');
}
add_action('add_meta_boxes','zolei_section_meta_box');
function zolei_section_meta_box_html($post) {
    wp_nonce_field('zolei_save_section_meta','zolei_section_nonce');
    $key=get_post_meta($post->ID,'_zolei_section_key',true);
    $nav=get_post_meta($post->ID,'_zolei_section_nav_label',true);
    $active=get_post_meta($post->ID,'_zolei_section_active',true); if($active==='') $active='1';
    $shortcode=get_post_meta($post->ID,'_zolei_section_shortcode',true);
    $pdf_type=get_post_meta($post->ID,'_zolei_section_pdf_type',true);
    ?>
    <p><label><?php esc_html_e('Anchor / section key','zolei-react'); ?></label><input class="widefat" name="zolei_section_key" value="<?php echo esc_attr($key ?: $post->post_name); ?>" placeholder="results"></p>
    <p><label><?php esc_html_e('Navigation label','zolei-react'); ?></label><input class="widefat" name="zolei_section_nav_label" value="<?php echo esc_attr($nav ?: $post->post_title); ?>"></p>
    <p><label><?php esc_html_e('Extra shortcode rendered under content','zolei-react'); ?></label><input class="widefat" name="zolei_section_shortcode" value="<?php echo esc_attr($shortcode); ?>" placeholder='[zolei_results type="rezultati"]'></p>
    <p><label><?php esc_html_e('PDF type for manager','zolei-react'); ?></label><select class="widefat" name="zolei_section_pdf_type"><option value=""><?php esc_html_e('No PDF list','zolei-react'); ?></option><?php foreach(zolei_pdf_types() as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($pdf_type,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></p>
    <p><label><input type="checkbox" name="zolei_section_active" value="1" <?php checked($active,'1'); ?>> <?php esc_html_e('Show on one-page site','zolei-react'); ?></label></p>
    <?php
}
function zolei_save_section_meta($post_id){
    if(!isset($_POST['zolei_section_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['zolei_section_nonce'])),'zolei_save_section_meta')) return;
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    update_post_meta($post_id,'_zolei_section_key',sanitize_title(wp_unslash($_POST['zolei_section_key'] ?? '')));
    update_post_meta($post_id,'_zolei_section_nav_label',sanitize_text_field(wp_unslash($_POST['zolei_section_nav_label'] ?? '')));
    update_post_meta($post_id,'_zolei_section_shortcode',wp_kses_post(wp_unslash($_POST['zolei_section_shortcode'] ?? '')));
    update_post_meta($post_id,'_zolei_section_pdf_type',sanitize_key(wp_unslash($_POST['zolei_section_pdf_type'] ?? '')));
    update_post_meta($post_id,'_zolei_section_active',!empty($_POST['zolei_section_active'])?'1':'0');
}
add_action('save_post_zolei_section','zolei_save_section_meta');

function zolei_section_columns($columns){ $columns['menu_order']=__('Order','zolei-react'); $columns['zolei_key']=__('Anchor','zolei-react'); $columns['zolei_pdf_type']=__('PDF type','zolei-react'); return $columns; }
add_filter('manage_zolei_section_posts_columns','zolei_section_columns');
function zolei_section_column_content($column,$post_id){ if($column==='menu_order') echo esc_html(get_post_field('menu_order',$post_id)); if($column==='zolei_key') echo '<code>'.esc_html(get_post_meta($post_id,'_zolei_section_key',true) ?: get_post_field('post_name',$post_id)).'</code>'; if($column==='zolei_pdf_type') echo esc_html(get_post_meta($post_id,'_zolei_section_pdf_type',true)); }
add_action('manage_zolei_section_posts_custom_column','zolei_section_column_content',10,2);

function zolei_pdf_meta_box() {
    add_meta_box('zolei_pdf_details', __('PDF details','zolei-react'), 'zolei_pdf_meta_box_html', 'zolei_pdf', 'normal', 'high');
}
add_action('add_meta_boxes','zolei_pdf_meta_box');

function zolei_pdf_meta_box_html($post) {
    wp_nonce_field('zolei_save_pdf_meta','zolei_pdf_nonce');
    $url = get_post_meta($post->ID,'_zolei_pdf_url',true);
    $rel = get_post_meta($post->ID,'_zolei_pdf_relative_path',true);
    $month = get_post_meta($post->ID,'_zolei_pdf_month',true);
    $type = get_post_meta($post->ID,'_zolei_pdf_type',true);
    $year = get_post_meta($post->ID,'_zolei_pdf_year',true);
    $months = zolei_pdf_months();
    $types = zolei_pdf_types();
    ?>
    <p><label><strong><?php esc_html_e('PDF URL','zolei-react'); ?></strong></label></p>
    <p><input type="url" class="large-text" id="zolei_pdf_url" name="zolei_pdf_url" value="<?php echo esc_attr($url); ?>"> <button type="button" class="button" id="zolei_pdf_upload"><?php esc_html_e('Choose/upload PDF','zolei-react'); ?></button></p>
    <p><label><strong><?php esc_html_e('Relative uploads path','zolei-react'); ?></strong></label></p>
    <p><input type="text" class="large-text" name="zolei_pdf_relative_path" value="<?php echo esc_attr($rel); ?>" placeholder="rezultati/file.pdf"><br><span class="description"><?php esc_html_e('Keeps the file in the same wp-content/uploads directory structure as the old website.','zolei-react'); ?></span></p>
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
    update_post_meta($post_id,'_zolei_pdf_relative_path',sanitize_text_field(wp_unslash($_POST['zolei_pdf_relative_path'] ?? '')));
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


function zolei_pretty_pdf_title($raw_title, $url = '', $year = '') {
    $title = trim(wp_strip_all_tags((string)$raw_title));
    if ($url) {
        $path_title = basename(parse_url($url, PHP_URL_PATH) ?: '');
        if ($path_title) { $title = $path_title; }
    }
    $title = preg_replace('/\.pdf$/i', '', $title);
    $title = rawurldecode($title);
    $title = str_replace(array('_','-','+'), ' ', $title);
    $title = preg_replace('/\s+/', ' ', $title);

    // Keep meaningful years/dates, remove ID-like number chunks.
    $tokens = preg_split('/\s+/', $title);
    $clean = array();
    foreach ($tokens as $token) {
        $plain = trim($token, " .,:;()[]{}_");
        if ($plain === '') { continue; }
        $is_year = preg_match('/^(19|20)\d{2}$/', $plain);
        $is_date = preg_match('/^(19|20)\d{2}[\.\-\/](0?[1-9]|1[0-2])([\.\-\/](0?[1-9]|[12]\d|3[01]))?$/', $plain);
        $is_month_day = preg_match('/^(0?[1-9]|[12]\d|3[01])[\.\-\/](0?[1-9]|1[0-2])$/', $plain);
        $is_only_digits = preg_match('/^\d+$/', $plain);
        if ($is_only_digits && !$is_year && !$is_date && !$is_month_day) { continue; }
        if (preg_match('/^[a-f0-9]{8,}$/i', $plain) && !$is_year) { continue; }
        $clean[] = $plain;
    }
    $title = trim(preg_replace('/\s+/', ' ', implode(' ', $clean)));
    if ($title === '') { $title = trim((string)$raw_title); }
    if ($title === '') { $title = __('PDF document','zolei-react'); }

    // Light title casing without damaging Latvian diacritics too much.
    if (function_exists('mb_convert_case')) {
        $title = mb_convert_case(mb_strtolower($title, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $title = preg_replace('/\bPdf\b/u', 'PDF', $title);
    }
    $safe_year = absint($year);
    if ($safe_year && strpos($title, (string)$safe_year) === false) { $title .= ' ' . $safe_year; }
    if (function_exists('mb_strlen') && mb_strlen($title, 'UTF-8') > 64) {
        $title = rtrim(mb_substr($title, 0, 61, 'UTF-8')) . '...';
    } elseif (strlen($title) > 64) {
        $title = rtrim(substr($title, 0, 61)) . '...';
    }
    return $title;
}

function zolei_results_shortcode($atts = array()) {
    $atts = shortcode_atts(array('type'=>'','month'=>'','year'=>'','limit'=>-1,'filters'=>'1'), $atts, 'zolei_results');
    $type_filter = sanitize_key($atts['type']);
    $meta = array('relation'=>'AND');
    if ($type_filter) { $meta[] = array('key'=>'_zolei_pdf_type','value'=>$type_filter); }
    if ($atts['month']) { $meta[] = array('key'=>'_zolei_pdf_month','value'=>sanitize_key($atts['month'])); }
    if ($atts['year']) { $meta[] = array('key'=>'_zolei_pdf_year','value'=>absint($atts['year'])); }
    $q = new WP_Query(array('post_type'=>'zolei_pdf','posts_per_page'=>intval($atts['limit']),'post_status'=>'publish','meta_query'=>count($meta) > 1 ? $meta : array(),'orderby'=>array('meta_value_num'=>'DESC','date'=>'DESC'),'meta_key'=>'_zolei_pdf_year'));
    $types = zolei_pdf_types();
    $months = zolei_pdf_months();
    ob_start();
    echo '<div class="zole-results-wrap" data-pdf-type="'.esc_attr($type_filter).'">';
    if ($atts['filters'] === '1') {
        $year_meta = array();
        if ($type_filter) { $year_meta[] = array('key'=>'_zolei_pdf_type','value'=>$type_filter); }
        $years = get_posts(array('post_type'=>'zolei_pdf','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>$year_meta));
        $year_values = array();
        foreach($years as $pid){ $y = absint(get_post_meta($pid,'_zolei_pdf_year',true)); if($y) { $year_values[$y] = $y; } }
        rsort($year_values);
        echo '<div class="zole-pdf-toolbar"><input type="search" class="zole-pdf-search" placeholder="'.esc_attr(zolei_i18n('Meklēt PDF...', 'Search PDFs...')).'"><select class="zole-pdf-year"><option value="">'.esc_html(zolei_i18n('Visi gadi','All years')).'</option>';
        foreach($year_values as $y){ echo '<option value="'.esc_attr($y).'">'.esc_html($y).'</option>'; }
        echo '</select>';
        if (!$type_filter) {
            echo '<select class="zole-pdf-type"><option value="">'.esc_html(zolei_i18n('Visi veidi','All types')).'</option>';
            foreach($types as $k=>$v){ echo '<option value="'.esc_attr($k).'">'.esc_html($v).'</option>'; }
            echo '</select>';
        }
        echo '</div>';
    }
    if ($q->have_posts()) {
        $initial_count = ($type_filter === 'protokoli') ? 4 : 6;
        $step_count = ($type_filter === 'protokoli') ? 4 : 3;
        echo '<div class="zole-result-grid" data-zole-pdf-grid="1" data-initial="'.esc_attr($initial_count).'" data-step="'.esc_attr($step_count).'">';
        while($q->have_posts()){ $q->the_post();
            $url = get_post_meta(get_the_ID(),'_zolei_pdf_url',true);
            $type = get_post_meta(get_the_ID(),'_zolei_pdf_type',true);
            $month = get_post_meta(get_the_ID(),'_zolei_pdf_month',true);
            $year_raw = get_post_meta(get_the_ID(),'_zolei_pdf_year',true);
            $year = absint($year_raw);
            $pretty_title = zolei_pretty_pdf_title(get_the_title(), $url, $year);
            $meta_parts = array();
            if (!empty($months[$month])) { $meta_parts[] = $months[$month]; }
            if ($year > 0) { $meta_parts[] = (string)$year; }
            $meta_text = trim(implode(' ', $meta_parts));
            $search = strtolower($pretty_title.' '.get_the_title().' '.($year > 0 ? $year : '').' '.$type.' '.$month);
            echo '<article class="zole-result-card" data-year="'.esc_attr($year > 0 ? $year : '').'" data-type="'.esc_attr($type).'" data-search="'.esc_attr($search).'">';
            echo '<div class="zole-result-card-main"><span class="zole-doc-type">'.esc_html($types[$type] ?? $type).'</span><h3 title="'.esc_attr($pretty_title).'">'.esc_html($pretty_title).'</h3>';
            if ($meta_text !== '') { echo '<p>'.esc_html($meta_text).'</p>'; }
            echo '</div>';
            if ($url) {
                $open_label = 'PDF';
                echo '<a class="zole-pdf-open zole-pdf-preview-trigger" href="'.esc_url($url).'" target="_blank" rel="noopener" data-pdf-url="'.esc_url($url).'" data-pdf-title="'.esc_attr($pretty_title).'" data-preview-label="'.esc_attr(zolei_i18n('PDF priekšskatījums','PDF preview')).'" data-open-label="'.esc_attr(zolei_i18n('Atvērt jaunā cilnē','Open new tab')).'" data-download-label="'.esc_attr(zolei_i18n('Lejupielādēt','Download')).'" data-close-label="'.esc_attr(zolei_i18n('Aizvērt','Close')).'" data-help-label="'.esc_attr(zolei_i18n('Ja priekšskatījumu ir grūti salasīt, atver PDF jaunā cilnē vai lejupielādē failu.','If the preview is hard to read, open the PDF in a new tab or download it.')).'" aria-label="'.esc_attr($open_label . ': ' . $pretty_title).'">'.esc_html($open_label).'</a>';
            }
            echo '</article>';
        }
        echo '</div>';
        echo '<div class="zole-pdf-load-more-wrap"><button type="button" class="zole-btn zole-btn-green zole-pdf-load-more">'.esc_html(zolei_i18n('Rādīt vēl','Load more')).'</button></div>';
    } else {
        echo '<div class="zole-empty-pdf"><strong>'.esc_html(zolei_i18n('Šai sadaļai PDF vēl nav piesaistīti.', 'No PDFs are linked to this section yet.')).'</strong><p>'.esc_html(zolei_i18n('Pievieno vai piesaisti PDF failus administrācijas Zolei settings sadaļā.', 'Add or assign PDF files in Zolei settings.')).'</p></div>';
    }
    wp_reset_postdata();
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('zolei_results','zolei_results_shortcode');

function zolei_pdf_section_shortcode($atts = array()) {
    $atts = shortcode_atts(array('section'=>'results','filters'=>'1'), $atts, 'zolei_pdf_section');
    $key = sanitize_key($atts['section']);
    $sections = zolei_pdf_sections();
    if (empty($sections[$key]) || empty($sections[$key]['enabled'])) { return ''; }
    $row = $sections[$key];
    $title = zolei_lang_is_en() ? ($row['title_en'] ?? $row['title_lv'] ?? '') : ($row['title_lv'] ?? '');
    $text = zolei_lang_is_en() ? ($row['text_en'] ?? $row['text_lv'] ?? '') : ($row['text_lv'] ?? '');
    $type = sanitize_key($row['type'] ?? '');
    ob_start();
    echo '<div class="zole-pdf-section zole-pdf-section-'.esc_attr($key).'">';
    if ($title || $text) { echo '<div class="zole-pdf-section-head">'; if ($title) echo '<h3>'.esc_html($title).'</h3>'; if ($text) echo '<p>'.esc_html($text).'</p>'; echo '</div>'; }
    echo do_shortcode('[zolei_results type="'.esc_attr($type).'" filters="'.esc_attr($atts['filters']).'"]');
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('zolei_pdf_section','zolei_pdf_section_shortcode');

function zolei_calendar_shortcode() {
    $months = zolei_calendar_months(); $current = intval(current_time('n')); ob_start();
    echo '<div class="zole-shortcode-calendar"><div class="zole-month-tabs" role="tablist">';
    foreach($months as $i=>$m){ $active = ($i+1)===$current ? ' active' : ''; echo '<button class="zole-month-tab'.$active.'" type="button" data-bs-toggle="pill" data-bs-target="#sc-'.$m['slug'].'"><span>'.esc_html(zolei_lang_is_en()?($m['en']??$m['lv']):($m['lv']??$m['en'])).'</span><em>'.count($m['events']??array()).'</em></button>'; }
    echo '</div><div class="tab-content zole-calendar-panel">';
    foreach($months as $i=>$m){ $active = ($i+1)===$current ? ' show active' : ''; echo '<div id="sc-'.$m['slug'].'" class="tab-pane fade'.$active.'"><div class="zole-event-grid">'; foreach(($m['events']??array()) as $ev){ echo '<article class="zole-event"><div class="zole-event-date">'.esc_html($ev['day']??'').'</div><p>'.esc_html($ev['title']??'').'</p></article>'; } echo '</div></div>'; }
    echo '</div></div>'; return ob_get_clean();
}
add_shortcode('zolei_calendar','zolei_calendar_shortcode');

function zolei_pdf_columns($columns) {
    $columns['zolei_pdf_type'] = __('Type','zolei-react');
    $columns['zolei_pdf_year'] = __('Year','zolei-react');
    $columns['zolei_pdf_url'] = __('PDF URL','zolei-react');
    return $columns;
}
add_filter('manage_zolei_pdf_posts_columns','zolei_pdf_columns');

function zolei_pdf_column_content($column, $post_id) {
    if ($column === 'zolei_pdf_type') { echo esc_html(get_post_meta($post_id,'_zolei_pdf_type',true)); }
    if ($column === 'zolei_pdf_year') { echo esc_html(get_post_meta($post_id,'_zolei_pdf_year',true)); }
    if ($column === 'zolei_pdf_url') {
        $url = get_post_meta($post_id,'_zolei_pdf_url',true);
        if ($url) { echo '<a href="'.esc_url($url).'" target="_blank" rel="noopener">'.esc_html__('Open PDF','zolei-react').'</a>'; }
    }
}
add_action('manage_zolei_pdf_posts_custom_column','zolei_pdf_column_content',10,2);

function zolei_pdf_sortable_columns($columns) {
    $columns['zolei_pdf_year'] = 'zolei_pdf_year';
    $columns['zolei_pdf_type'] = 'zolei_pdf_type';
    return $columns;
}
add_filter('manage_edit-zolei_pdf_sortable_columns','zolei_pdf_sortable_columns');

function zolei_pdf_admin_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) { return; }
    if ($query->get('post_type') !== 'zolei_pdf') { return; }
    if ($query->get('orderby') === 'zolei_pdf_year') { $query->set('meta_key','_zolei_pdf_year'); $query->set('orderby','meta_value_num'); }
    if ($query->get('orderby') === 'zolei_pdf_type') { $query->set('meta_key','_zolei_pdf_type'); $query->set('orderby','meta_value'); }
}
add_action('pre_get_posts','zolei_pdf_admin_orderby');
