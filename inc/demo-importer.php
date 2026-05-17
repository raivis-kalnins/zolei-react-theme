<?php
if (!defined('ABSPATH')) { exit; }
function zolei_demo_find_post($slug, $type='page') { return get_page_by_path($slug, OBJECT, $type); }
function zolei_demo_upsert($slug, $title, $content, $type='page', $lang='lv') {
    $post = zolei_demo_find_post($slug, $type);
    $arr = array('post_title'=>$title,'post_name'=>$slug,'post_type'=>$type,'post_status'=>'publish','post_content'=>$content);
    if ($post) { $arr['ID'] = $post->ID; $id = wp_update_post(wp_slash($arr), true); }
    else { $id = wp_insert_post(wp_slash($arr), true); }
    if (!is_wp_error($id) && function_exists('pll_set_post_language')) { pll_set_post_language($id, $lang); }
    return $id;
}
function zolei_demo_page_content($slug, $lang='lv') {
    if ($slug === 'sakums' || $slug === 'home') { return zolei_bbuilder_home_content($lang); }
    $is_en = $lang === 'en';
        if ($slug === 'rezultati' || $slug === 'results') {
        return '<!-- wp:heading {"level":2} --><h2>'.esc_html($is_en ? 'Results and PDF archive' : 'Rezultāti un PDF arhīvs').'</h2><!-- /wp:heading -->' . "\n" . '<!-- wp:shortcode -->[zolei_results]<!-- /wp:shortcode -->';
    }
    if ($slug === 'turniri' || $slug === 'calendar') {
        return '<!-- wp:heading {"level":2} --><h2>'.esc_html($is_en ? 'Tournament calendar' : 'Turnīru kalendārs').'</h2><!-- /wp:heading -->' . "\n" . '<!-- wp:shortcode -->[zolei_calendar]<!-- /wp:shortcode -->';
    }
    if ($slug === 'jaunumi' || $slug === 'news') {
        return '<!-- wp:heading {"level":2} --><h2>'.esc_html($is_en ? 'News' : 'Jaunumi').'</h2><!-- /wp:heading -->' . "\n" . '<!-- wp:paragraph --><p>'.esc_html($is_en ? 'Latest federation updates and tournament information.' : 'Jaunākās federācijas aktualitātes un turnīru informācija.').'</p><!-- /wp:paragraph -->';
    }
$title = $is_en ? 'Editable page content' : 'Rediģējams lapas saturs';
    $text = $is_en ? 'This page is created by the Zolei.lv importer and can be edited with Gutenberg and WP BBuilder blocks.' : 'Šī lapa ir izveidota ar Zolei.lv importētāju un ir rediģējama ar Gutenberg un WP BBuilder blokiem.';
    return '<!-- wp:wpbb/section {"className":"zole-imported-section"} -->' . "\n" .
           '<!-- wp:heading {"level":2} --><h2>' . esc_html($title) . '</h2><!-- /wp:heading -->' . "\n" .
           '<!-- wp:paragraph --><p>' . esc_html($text) . '</p><!-- /wp:paragraph -->' . "\n" .
           '<!-- wp:wpbb/cta-section {"title":"' . esc_attr($is_en ? 'Need to update this page?' : 'Vajag papildināt šo lapu?') . '","text":"' . esc_attr($is_en ? 'Use WP BBuilder blocks to add cards, tabs, files, forms and galleries.' : 'Izmanto WP BBuilder blokus, lai pievienotu kartītes, tabus, failus, formas un galerijas.') . '","buttonText":"' . esc_attr($is_en ? 'Contact' : 'Sazināties') . '","buttonUrl":"/kontakti/"} /-->' . "\n" .
           '<!-- /wp:wpbb/section -->';
}

function zolei_demo_upsert_post($slug, $title, $content, $lang='lv', $excerpt='') {
    return zolei_demo_upsert($slug, $title, $content, 'post', $lang);
}
function zolei_demo_import_pdfs() {
    $pdfs = array(
        array('L_2_Elites_kauss_kopv_2026__4.pdf','https://zolei.lv/wp-content/uploads/rezultati/L_2_Elites_kauss_kopv_2026__4.pdf','aprilis','rezultati',2026),
        array('L_1_Elites_kauss_2026_4.pdf','https://zolei.lv/wp-content/uploads/rezultati/L_1_Elites_kauss_2026_4.pdf','aprilis','rezultati',2026),
        array('K_9_AIZKRAUKLE_2026_1.pdf','https://zolei.lv/wp-content/uploads/rezultati/K_9_AIZKRAUKLE_2026_1.pdf','aprilis','rezultati',2026),
        array('K_8_Selijas_Lieldienu_ZOLE_2026.pdf','https://zolei.lv/wp-content/uploads/rezultati/K_8_Selijas_Lieldienu_ZOLE_2026.pdf','aprilis','rezultati',2026),
        array('reitings-2026_03_ELITES.pdf','https://zolei.lv/wp-content/uploads/reitingi/reitings-2026_03_ELITES.pdf','marts','reitings',2026),
        array('reitings-2026_02_ELITES.pdf','https://zolei.lv/wp-content/uploads/reitingi/reitings-2026_02_ELITES.pdf','februaris','reitings',2026),
        array('reitings-2026_01_ELITES.pdf','https://zolei.lv/wp-content/uploads/reitingi/reitings-2026_01_ELITES.pdf','janvaris','reitings',2026)
    );
    $count = 0;
    foreach($pdfs as $p) {
        $existing = get_page_by_path(sanitize_title($p[0]), OBJECT, 'zolei_pdf');
        $arr = array('post_type'=>'zolei_pdf','post_status'=>'publish','post_title'=>$p[0],'post_name'=>sanitize_title($p[0]),'post_content'=>'');
        if ($existing) { $arr['ID'] = $existing->ID; $id = wp_update_post($arr, true); } else { $id = wp_insert_post($arr, true); }
        if (!is_wp_error($id)) { update_post_meta($id,'_zolei_pdf_url',$p[1]); update_post_meta($id,'_zolei_pdf_month',$p[2]); update_post_meta($id,'_zolei_pdf_type',$p[3]); update_post_meta($id,'_zolei_pdf_year',$p[4]); if(function_exists('pll_set_post_language')) pll_set_post_language($id,'lv'); $count++; }
    }
    return $count;
}
function zolei_demo_import_news() {
    $lv_posts = array(
        array('jaunumi-latvijas-cempionats','Latvijas čempionāta posmi un atlases kārtība','<!-- wp:paragraph --><p>Aktuālā informācija par Latvijas zoles čempionāta posmiem, dalībnieku atlasi un turnīru norises kārtību.</p><!-- /wp:paragraph -->'),
        array('jaunumi-elites-kauss','Elites kausa kalendārs','<!-- wp:paragraph --><p>Elites kausa posmi palīdz uzturēt sportisku konkurenci un skaidru reitinga sistēmu visa gada garumā.</p><!-- /wp:paragraph -->'),
        array('jaunumi-etikas-kodekss','Ētikas kodekss spēlētājiem','<!-- wp:paragraph --><p>Godīga spēle, cieņpilna uzvedība pie galda un atbildīga attieksme pret pules pierakstu ir zoles kopienas pamats.</p><!-- /wp:paragraph -->'),
        array('jaunumi-rezultati-reitingi','Rezultāti un reitingi vienuviet','<!-- wp:paragraph --><p>Rezultātu un reitingu sadaļās pieejami PDF protokoli, kopvērtējumi un arhīva materiāli.</p><!-- /wp:paragraph -->'),
        array('jaunumi-pievieno-turniru','Kā pievienot turnīru kalendāram','<!-- wp:paragraph --><p>Organizatoriem jānosūta datums, vieta, sākuma laiks, formāts, dalības informācija un kontaktpersona.</p><!-- /wp:paragraph -->')
    );
    $en_posts = array(
        array('news-latvian-championship','Latvian championship stages','<!-- wp:paragraph --><p>Current information about championship stages, qualification and tournament organisation.</p><!-- /wp:paragraph -->'),
        array('news-elite-cup','Elite Cup calendar','<!-- wp:paragraph --><p>Elite Cup stages keep competition active and support a transparent rating system throughout the year.</p><!-- /wp:paragraph -->'),
        array('news-ethics-code','Ethics code for players','<!-- wp:paragraph --><p>Fair play, respectful behaviour and responsible scoring are the foundation of the Zolīte community.</p><!-- /wp:paragraph -->'),
        array('news-results-ratings','Results and ratings in one place','<!-- wp:paragraph --><p>The results and ratings sections include PDF protocols, standings and archive documents.</p><!-- /wp:paragraph -->'),
        array('news-add-tournament','How to add a tournament','<!-- wp:paragraph --><p>Organisers should send date, location, start time, format, participation information and contact person.</p><!-- /wp:paragraph -->')
    );
    $ids_lv=array(); $ids_en=array(); $count=0;
    foreach($lv_posts as $p){ $id=zolei_demo_upsert_post($p[0],$p[1],$p[2],'lv'); if(!is_wp_error($id)){ if(function_exists('pll_set_post_language')) pll_set_post_language($id,'lv'); $ids_lv[]=$id; $count++; } }
    if(function_exists('pll_set_post_language')){ foreach($en_posts as $p){ $id=zolei_demo_upsert_post($p[0],$p[1],$p[2],'en'); if(!is_wp_error($id)){ pll_set_post_language($id,'en'); $ids_en[]=$id; $count++; } } }
    if(function_exists('pll_save_post_translations')){ for($i=0;$i<min(count($ids_lv),count($ids_en));$i++){ pll_save_post_translations(array('lv'=>$ids_lv[$i],'en'=>$ids_en[$i])); } }
    return $count;
}


function zolei_demo_file_url_from_path($path) {
    $uploads = wp_get_upload_dir();
    $basedir = wp_normalize_path($uploads['basedir']);
    $path = wp_normalize_path($path);
    if (strpos($path, $basedir) !== 0) { return ''; }
    return $uploads['baseurl'] . str_replace($basedir, '', $path);
}

function zolei_demo_make_avif_variant($attachment_id, $width = 1920, $quality = 78, $suffix = 'zolei-1920') {
    $source = function_exists('wp_get_original_image_path') ? wp_get_original_image_path($attachment_id) : get_attached_file($attachment_id);
    if (!$source || !file_exists($source) || !function_exists('wp_get_image_editor')) { return ''; }
    $editor = wp_get_image_editor($source);
    if (is_wp_error($editor)) { return ''; }
    $size = $editor->get_size();
    if (!empty($size['width']) && intval($size['width']) > $width) { $editor->resize($width, null, false); }
    if (method_exists($editor, 'set_quality')) { $editor->set_quality($quality); }
    $dir = dirname($source);
    $name = pathinfo($source, PATHINFO_FILENAME);
    $file = trailingslashit($dir) . sanitize_file_name($name . '-' . $suffix . '.avif');
    $saved = $editor->save($file, 'image/avif');
    if (is_wp_error($saved) || empty($saved['path']) || !file_exists($saved['path'])) { return ''; }
    return zolei_demo_file_url_from_path($saved['path']);
}

function zolei_demo_sideload_gallery_image($url) {
    $url = function_exists('zolei_gallery_original_url') ? zolei_gallery_original_url($url) : $url;
    if (!$url) { return null; }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $existing = get_posts(array('post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>1,'meta_key'=>'_zolei_source_url','meta_value'=>$url,'fields'=>'ids'));
    $attachment_id = !empty($existing) ? intval($existing[0]) : 0;
    if (!$attachment_id) {
        $tmp = download_url($url, 45);
        if (is_wp_error($tmp)) { return array('thumb'=>$url, 'full'=>$url, 'source'=>$url); }
        $file = array('name'=>basename(parse_url($url, PHP_URL_PATH)), 'tmp_name'=>$tmp);
        $attachment_id = media_handle_sideload($file, 0, 'Zolei.lv gallery photo');
        if (is_wp_error($attachment_id)) { @unlink($tmp); return array('thumb'=>$url, 'full'=>$url, 'source'=>$url); }
        update_post_meta($attachment_id, '_zolei_source_url', esc_url_raw($url));
    }
    $full_avif = zolei_demo_make_avif_variant($attachment_id, 1920, 78, 'zolei-1920');
    $thumb_avif = zolei_demo_make_avif_variant($attachment_id, 640, 70, 'zolei-thumb');
    $full = $full_avif ?: (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id));
    $thumb = $thumb_avif ?: (wp_get_attachment_image_url($attachment_id, 'medium_large') ?: $full);
    return array('thumb'=>esc_url_raw($thumb), 'full'=>esc_url_raw($full), 'source'=>esc_url_raw($url), 'attachment_id'=>$attachment_id);
}

function zolei_demo_import_gallery_media($limit = 24) {
    $items = array();
    $sources = zolei_default_gallery();
    $count = 0;
    foreach ($sources as $src) {
        if ($limit && $count >= $limit) { break; }
        $item = zolei_demo_sideload_gallery_image($src);
        if ($item && !empty($item['full'])) { $items[] = $item; $count++; }
    }
    if ($items) { update_option('zolei_gallery_images', $items); }
    return $count;
}

function zolei_demo_import() {
    update_option('blogname','Zolei.lv');
    update_option('blogdescription','Latvijas Zolītes federācija');
    $result = array('pages'=>0,'events'=>0,'posts'=>0,'pdfs'=>0,'gallery'=>0,'menu'=>false);
    $lv = array(
        array('sakums','Sākums'), array('jaunumi','Jaunumi'), array('turniri','Turnīri'), array('zoles-noteikumi','Zoles noteikumi'), array('rezultati','Rezultāti'), array('reitingi','Reitingi'), array('meistari','Meistari'), array('galerija','Galerija'), array('etikas-kodekss','Ētikas kodekss'), array('kontakti','Kontakti')
    );
    $en = array(
        array('home','Home'), array('news','News'), array('calendar','Calendar'), array('rules','Rules'), array('results','Results'), array('ratings','Ratings'), array('masters','Masters'), array('gallery','Gallery'), array('ethics-code','Ethics code'), array('contact','Contact')
    );
    $ids_lv = array(); $ids_en = array();
    foreach ($lv as $p) {
        $id = zolei_demo_upsert($p[0], $p[1], zolei_demo_page_content($p[0], 'lv'), 'page', 'lv');
        if (!is_wp_error($id)) { $ids_lv[$p[0]] = $id; $result['pages']++; }
    }
    if (function_exists('pll_set_post_language')) {
        foreach ($en as $p) {
            $id = zolei_demo_upsert($p[0], $p[1], zolei_demo_page_content($p[0], 'en'), 'page', 'en');
            if (!is_wp_error($id)) { $ids_en[$p[0]] = $id; $result['pages']++; }
        }
    }
    if (function_exists('pll_save_post_translations')) {
        $pairs = array('sakums'=>'home','jaunumi'=>'news','turniri'=>'calendar','zoles-noteikumi'=>'rules','rezultati'=>'results','reitingi'=>'ratings','meistari'=>'masters','galerija'=>'gallery','etikas-kodekss'=>'ethics-code','kontakti'=>'contact');
        foreach ($pairs as $lv_slug=>$en_slug) {
            if (isset($ids_lv[$lv_slug], $ids_en[$en_slug])) { pll_save_post_translations(array('lv'=>$ids_lv[$lv_slug], 'en'=>$ids_en[$en_slug])); }
        }
    }
    $months = zolei_default_months();
    update_option('zolei_calendar_months', $months);
    $result['gallery'] = zolei_demo_import_gallery_media(24);
    $result['posts'] = zolei_demo_import_news();
    $result['pdfs'] = zolei_demo_import_pdfs();
    foreach ($months as $m) { if (!empty($m['events']) && is_array($m['events'])) { $result['events'] += count($m['events']); } }
    $front = zolei_demo_find_post('sakums','page');
    if ($front) { update_option('show_on_front','page'); update_option('page_on_front',$front->ID); }
    $posts_page = zolei_demo_find_post('jaunumi','page');
    if ($posts_page) { update_option('page_for_posts', $posts_page->ID); }
    $menu_name = 'Zolei.lv LV';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu($menu_name);
    if (!is_wp_error($menu_id)) {
        $items = wp_get_nav_menu_items($menu_id);
        if (empty($items)) {
            foreach (array('sakums','jaunumi','turniri','zoles-noteikumi','rezultati','galerija','kontakti') as $slug) {
                $page = zolei_demo_find_post($slug, 'page');
                if ($page) {
                    wp_update_nav_menu_item($menu_id, 0, array('menu-item-title'=>$page->post_title,'menu-item-object'=>'page','menu-item-object-id'=>$page->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish'));
                }
            }
        }
        $loc = get_theme_mod('nav_menu_locations'); if (!is_array($loc)) { $loc = array(); }
        $loc['primary'] = $menu_id; $loc['footer'] = $menu_id; set_theme_mod('nav_menu_locations', $loc);
        $result['menu'] = true;
    }
    
    if (function_exists('pll_set_post_language')) {
        $menu_name_en = 'Zolei.lv EN';
        $menu_en = wp_get_nav_menu_object($menu_name_en);
        $menu_en_id = $menu_en ? $menu_en->term_id : wp_create_nav_menu($menu_name_en);
        if (!is_wp_error($menu_en_id)) {
            $items_en = wp_get_nav_menu_items($menu_en_id);
            if (empty($items_en)) {
                foreach (array('home','news','calendar','rules','results','gallery','contact') as $slug) {
                    $page = zolei_demo_find_post($slug, 'page');
                    if ($page) { wp_update_nav_menu_item($menu_en_id, 0, array('menu-item-title'=>$page->post_title,'menu-item-object'=>'page','menu-item-object-id'=>$page->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish')); }
                }
            }
            if (function_exists('pll_set_term_language')) { pll_set_term_language($menu_id, 'lv'); pll_set_term_language($menu_en_id, 'en'); }
        }
    }

    update_option('zolei_demo_imported', current_time('mysql'));
    return $result;
}
function zolei_demo_admin_menu() {
    add_theme_page(__('Zolei demo import','zolei-react'), __('Zolei demo import','zolei-react'), 'manage_options', 'zolei-demo-import', 'zolei_demo_page');
}
add_action('admin_menu','zolei_demo_admin_menu');
function zolei_demo_page() {
    if (!current_user_can('manage_options')) { return; }
    $message = '';
    if (isset($_POST['zolei_import_demo'])) {
        check_admin_referer('zolei_import_demo_action','zolei_import_demo_nonce');
        $r = zolei_demo_import();
        $message = sprintf(__('Imported/updated: %1$d pages, %2$d calendar events, demo news and PDF files. Menus, translations and front page configured.','zolei-react'), intval($r['pages']), intval($r['events']));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Zolei.lv demo import','zolei-react'); ?></h1>
        <?php if ($message): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div><?php endif; ?>
        <p><?php esc_html_e('Creates Latvian and English pages, WP BBuilder starter blocks, menu, front-page settings, locally imported optimized gallery images and monthly calendar data from the current Zolei.lv preview. Polylang links translations when available.','zolei-react'); ?></p>
        <form method="post">
            <?php wp_nonce_field('zolei_import_demo_action','zolei_import_demo_nonce'); ?>
            <p><button type="submit" name="zolei_import_demo" class="button button-primary button-hero"><?php esc_html_e('Import / Update Zolei.lv content','zolei-react'); ?></button></p>
        </form>
    </div>
    <?php
}
