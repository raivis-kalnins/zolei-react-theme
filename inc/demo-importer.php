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
    $id = zolei_demo_upsert($slug, $title, $content, 'post', $lang);
    if (!is_wp_error($id) && $excerpt !== '') { wp_update_post(array('ID'=>$id, 'post_excerpt'=>wp_kses_post($excerpt))); }
    return $id;
}

function zolei_demo_blog_post_items() {
    return array(
        array(
            'lv'=>array(
                'slug'=>'jaunumi-zolei-lv-jauna-lapa',
                'title'=>'Jauna Zolei.lv sākumlapa spēlētājiem',
                'excerpt'=>'Vienā lapā apvienots turnīru kalendārs, noteikumi, rezultāti, reitingi un kontaktforma.',
                'content'=>'Jaunā Zolei.lv sākumlapa ir veidota kā ātrs ceļvedis Latvijas zolītes spēlētājiem. Tajā vienuviet redzams turnīru kalendārs, PDF rezultāti, reitingi, noteikumi un federācijas kontaktinformācija. Sadaļas ir rediģējamas administrācijā, bet publiskā puse paliek viegla un moderna.'
            ),
            'en'=>array(
                'slug'=>'news-zolei-lv-new-homepage',
                'title'=>'A new Zolei.lv homepage for players',
                'excerpt'=>'The one-page experience brings calendar, rules, results, ratings and contact options together.',
                'content'=>'The new Zolei.lv homepage is designed as a clear guide for Latvian Zolīte players. It brings the tournament calendar, PDF results, ratings, rules and federation contacts into one elegant page. Administrators can update the content in WordPress while visitors get a fast and modern experience.'
            ),
            'image'=>'news-zolei-home.jpg'
        ),
        array(
            'lv'=>array(
                'slug'=>'jaunumi-rezultati-reitingi-viena-vieta',
                'title'=>'Rezultāti un reitingi vienā vietā',
                'excerpt'=>'PDF dokumenti saglabā veco augšupielāžu struktūru, bet ir viegli meklējami jaunajā dizainā.',
                'content'=>'Rezultātu, reitingu, protokolu un arhīva PDF faili paliek esošajās wp-content/uploads mapēs, lai vecās saites un darba kārtība būtu saprotama. Jaunajā lapā tie tiek rādīti kā tīras kartītes ar meklēšanu, gada filtru un ērtu atvēršanas pogu.'
            ),
            'en'=>array(
                'slug'=>'news-results-ratings-one-place',
                'title'=>'Results and ratings in one place',
                'excerpt'=>'PDF documents keep the old upload directory structure but become easier to browse in the new design.',
                'content'=>'Results, ratings, protocols and archive PDFs stay in the existing wp-content/uploads folders so the old link structure and workflow remain familiar. On the new page they are displayed as clean cards with search, year filters and quick open buttons.'
            ),
            'image'=>'news-results-ratings.jpg'
        ),
        array(
            'lv'=>array(
                'slug'=>'jaunumi-turniru-informacija-pieteiksana',
                'title'=>'Turnīru informācijas pieteikšana kļūst vienkāršāka',
                'excerpt'=>'Kontaktforma palīdz iesūtīt turnīra datumu, vietu, formātu un kontaktpersonu latviski vai angliski.',
                'content'=>'Organizatori var iesūtīt turnīru informāciju, labojumus vai jautājumus caur vienkāršu kontaktformu. Forma darbojas latviski un angliski, un hCaptcha aizsardzība palīdz samazināt nevēlamu ziņojumu skaitu.'
            ),
            'en'=>array(
                'slug'=>'news-tournament-information-submission',
                'title'=>'Tournament information is easier to submit',
                'excerpt'=>'The contact form helps send tournament date, location, format and contact person in Latvian or English.',
                'content'=>'Organisers can send tournament information, corrections or questions through a simple contact form. The form works in Latvian and English, and hCaptcha protection helps reduce unwanted messages.'
            ),
            'image'=>'news-tournament-submit.jpg'
        ),
    );
}

function zolei_demo_import_blog_posts() {
    $count = 0;
    foreach (zolei_demo_blog_post_items() as $item) {
        $translations = array();
        foreach (array('lv','en') as $lang) {
            if ($lang === 'en' && !function_exists('pll_set_post_language')) { continue; }
            $row = $item[$lang];
            $content = '<!-- wp:paragraph --><p>' . esc_html($row['content']) . '</p><!-- /wp:paragraph -->';
            $id = zolei_demo_upsert_post($row['slug'], $row['title'], $content, $lang, $row['excerpt']);
            if (is_wp_error($id)) { continue; }
            $image_url = preg_match('~^https?://~', $item['image']) ? $item['image'] : get_template_directory_uri() . '/assets/images/' . ltrim($item['image'], '/');
            update_post_meta($id, '_zolei_blog_image', esc_url_raw($image_url));
            if (function_exists('pll_set_post_language')) { pll_set_post_language($id, $lang); }
            $translations[$lang] = $id;
            $count++;
        }
        if (function_exists('pll_save_post_translations') && isset($translations['lv'], $translations['en'])) {
            pll_save_post_translations($translations);
        }
    }
    return $count;
}

function zolei_demo_manifest_path($file) {
    return get_template_directory() . '/assets/data/' . basename($file);
}

function zolei_demo_read_manifest($file) {
    $path = zolei_demo_manifest_path($file);
    if (!file_exists($path) || !is_readable($path)) { return array(); }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    return is_array($data) ? $data : array();
}

function zolei_demo_pdf_manifest_count() { return count(zolei_demo_read_manifest('zolei-pdfs.json')); }
function zolei_demo_content_manifest_count() { return count(zolei_demo_read_manifest('zolei-content.json')); }

function zolei_demo_enhance_legacy_content($content, $slug, $title='') {
    $content = trim((string) $content);
    $slug = sanitize_title($slug);
    $extra = '';
    if (in_array($slug, array('rezultati','results'), true)) { $extra .= "\n<!-- wp:shortcode -->[zolei_pdf_section section=\"results\"]<!-- /wp:shortcode -->"; }
    if (in_array($slug, array('reitingi','ratings','meistari','meistari-lielmeistari'), true)) { $extra .= "\n<!-- wp:shortcode -->[zolei_pdf_section section=\"ratings\"]<!-- /wp:shortcode -->"; }
    if (in_array($slug, array('zoles-noteikumi','rules','nolikumi'), true)) { $extra .= "\n<!-- wp:shortcode -->[zolei_pdf_section section=\"rules\"]<!-- /wp:shortcode -->"; }
    if (in_array($slug, array('arhivs','archive'), true)) { $extra .= "\n<!-- wp:shortcode -->[zolei_pdf_section section=\"archive\"]<!-- /wp:shortcode -->"; }
    if (in_array($slug, array('protokoli'), true)) { $extra .= "\n<!-- wp:shortcode -->[zolei_pdf_section section=\"protocols\"]<!-- /wp:shortcode -->"; }
    if (in_array($slug, array('turniri','calendar'), true)) { $extra .= "\n<!-- wp:shortcode -->[zolei_calendar]<!-- /wp:shortcode -->"; }

    if ($content === '' && $extra === '') { return ''; }
    $content = str_replace(array('http://zolei.lv','http://www.zolei.lv'), array('https://zolei.lv','https://zolei.lv'), $content);
    return '<!-- wp:wpbb/section {"className":"zole-legacy-content"} -->' . "\n" .
           ($title !== '' ? '<!-- wp:heading {"level":2} --><h2>' . esc_html($title) . '</h2><!-- /wp:heading -->' . "\n" : '') .
           $content . $extra . "\n" .
           '<!-- /wp:wpbb/section -->';
}

function zolei_demo_legacy_content_for($slug, $fallback='', $title='') {
    foreach (zolei_demo_read_manifest('zolei-content.json') as $entry) {
        if (!empty($entry['slug']) && sanitize_title($entry['slug']) === sanitize_title($slug) && ($entry['type'] ?? '') === 'page') {
            $content = zolei_demo_enhance_legacy_content($entry['content'] ?? '', $slug, $entry['title'] ?? $title);
            return $content !== '' ? $content : $fallback;
        }
    }
    return $fallback;
}

function zolei_demo_import_legacy_content() {
    $count = 0;
    $skip_slugs = array('sakums','home');
    foreach (zolei_demo_read_manifest('zolei-content.json') as $entry) {
        $type = ($entry['type'] ?? '') === 'post' ? 'post' : 'page';
        $slug = sanitize_title($entry['slug'] ?? '');
        $title = sanitize_text_field($entry['title'] ?? '');
        if ($slug === '' || $title === '' || in_array($slug, $skip_slugs, true)) { continue; }
        $content = zolei_demo_enhance_legacy_content($entry['content'] ?? '', $slug, $title);
        if ($content === '') { continue; }
        $id = zolei_demo_upsert($slug, $title, $content, $type, 'lv');
        if (!is_wp_error($id)) {
            wp_update_post(array('ID'=>$id, 'menu_order'=>intval($entry['menu_order'] ?? 0), 'post_excerpt'=>wp_kses_post($entry['excerpt'] ?? '')));
            if (function_exists('pll_set_post_language')) { pll_set_post_language($id, 'lv'); }
            $count++;
        }
    }
    return $count;
}

function zolei_demo_import_pdfs($download_files = false) {
    $pdfs = zolei_demo_read_manifest('zolei-pdfs.json');
    if (!$pdfs) { return 0; }
    $count = 0;
    foreach($pdfs as $p) {
        $title = sanitize_text_field($p['title'] ?? ($p['filename'] ?? 'PDF'));
        $slug = sanitize_title($p['slug'] ?? $title);
        $type = sanitize_key($p['type'] ?? 'rezultati');
        $relative_path = trim(str_replace('\\', '/', (string) ($p['relative_path'] ?? '')), '/');
        $relative_path = preg_replace('~\.{2,}~', '', $relative_path);
        $source_url = esc_url_raw($p['source_url'] ?? '');
        if (!$title) { continue; }

        $url = '';
        if ($relative_path) {
            $url = zolei_demo_copy_packaged_pdf($relative_path);
            if (!$url) { $url = function_exists('zolei_pdf_file_url_from_relative_path') ? zolei_pdf_file_url_from_relative_path($relative_path) : ''; }
        }
        if (!$url) { $url = $source_url; }
        if (!$url) { continue; }

        $existing = get_page_by_path($slug, OBJECT, 'zolei_pdf');
        $arr = array('post_type'=>'zolei_pdf','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>'');
        if ($existing) { $arr['ID'] = $existing->ID; $id = wp_update_post(wp_slash($arr), true); } else { $id = wp_insert_post(wp_slash($arr), true); }
        if (is_wp_error($id)) { continue; }

        update_post_meta($id,'_zolei_pdf_url',$url);
        update_post_meta($id,'_zolei_pdf_source_url',$source_url ?: $url);
        update_post_meta($id,'_zolei_pdf_relative_path',$relative_path);
        update_post_meta($id,'_zolei_pdf_directory',sanitize_text_field($p['directory'] ?? dirname($relative_path)));
        update_post_meta($id,'_zolei_pdf_month',sanitize_key($p['month'] ?? ''));
        update_post_meta($id,'_zolei_pdf_type',$type);
        update_post_meta($id,'_zolei_pdf_year',absint($p['year'] ?? 0));
        if ($relative_path) {
            list($pdf_path_for_meta) = function_exists('zolei_pdf_target_path') ? zolei_pdf_target_path($relative_path) : array('');
            if ($pdf_path_for_meta && file_exists($pdf_path_for_meta)) {
                update_post_meta($id,'_zolei_pdf_compressed',1);
                update_post_meta($id,'_zolei_pdf_compressed_size',filesize($pdf_path_for_meta));
            }
        }
        if(function_exists('pll_set_post_language')) { pll_set_post_language($id,'lv'); }
        $count++;
    }
    update_option('zolei_pdf_manifest_imported', current_time('mysql'));
    return $count;
}

function zolei_demo_copy_packaged_pdf($relative_path) {
    $relative_path = trim(str_replace('\\', '/', (string) $relative_path), '/');
    $relative_path = preg_replace('~\.{2,}~', '', $relative_path);
    if ($relative_path === '') { return ''; }
    $source = get_template_directory() . '/assets/old-uploads/' . $relative_path;
    if (!file_exists($source) || !is_readable($source)) { return ''; }
    if (!function_exists('zolei_pdf_target_path')) { return ''; }
    list($target, $url) = zolei_pdf_target_path($relative_path);
    if (!$target || !$url) { return ''; }
    wp_mkdir_p(dirname($target));
    if (!file_exists($target)) { copy($source, $target); }
    if (file_exists($target) && function_exists('zolei_pdf_compress_file')) { zolei_pdf_compress_file($target); }
    return file_exists($target) ? $url : '';
}

function zolei_demo_sideload_pdf($url, $title='') {
    if (!$url || !current_user_can('upload_files')) { return ''; }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $existing = get_posts(array('post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>1,'meta_key'=>'_zolei_source_url','meta_value'=>$url,'fields'=>'ids'));
    if (!empty($existing)) { return wp_get_attachment_url(intval($existing[0])); }
    $tmp = download_url($url, 45);
    if (is_wp_error($tmp)) { return ''; }
    $name = basename(parse_url($url, PHP_URL_PATH));
    if (!preg_match('/\.pdf$/i', $name)) { $name = sanitize_file_name($title ?: 'zolei-document') . '.pdf'; }
    $file = array('name'=>$name, 'tmp_name'=>$tmp);
    $attachment_id = media_handle_sideload($file, 0, $title ?: $name);
    if (is_wp_error($attachment_id)) { @unlink($tmp); return ''; }
    update_post_meta($attachment_id, '_zolei_source_url', esc_url_raw($url));
    return wp_get_attachment_url($attachment_id) ?: '';
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
    $full_avif = zolei_demo_make_avif_variant($attachment_id, 1920, 84, 'zolei-1920');
    $thumb_avif = zolei_demo_make_avif_variant($attachment_id, 1280, 82, 'zolei-thumb');
    $full = $full_avif ?: (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id));
    $thumb = $thumb_avif ?: (wp_get_attachment_image_url($attachment_id, 'medium_large') ?: $full);
    return array('thumb'=>esc_url_raw($thumb), 'full'=>esc_url_raw($full), 'source'=>esc_url_raw($url), 'attachment_id'=>$attachment_id);
}

function zolei_demo_import_gallery_media($limit = 24, $download = false) {
    $items = array();
    $sources = function_exists('zolei_default_gallery') ? zolei_default_gallery() : array();
    $count = 0;
    foreach ($sources as $src) {
        if ($limit && $count >= $limit) { break; }
        if ($download) {
            $item = zolei_demo_sideload_gallery_image($src);
        } else {
            $full = function_exists('zolei_gallery_original_url') ? zolei_gallery_original_url($src) : $src;
            $item = array('thumb'=>esc_url_raw($src), 'full'=>esc_url_raw($full), 'source'=>esc_url_raw($src));
        }
        if ($item && !empty($item['full'])) { $items[] = $item; $count++; }
    }
    if ($items) { update_option('zolei_gallery_images', $items); }
    return $count;
}



function zolei_demo_upsert_section($key, $title, $content, $order=10, $shortcode='', $pdf_type='', $nav='') {
    $existing = get_page_by_path($key, OBJECT, 'zolei_section');
    $arr = array('post_title'=>$title,'post_name'=>$key,'post_type'=>'zolei_section','post_status'=>'publish','post_content'=>$content,'menu_order'=>intval($order));
    if ($existing) { $arr['ID']=$existing->ID; $id=wp_update_post(wp_slash($arr), true); }
    else { $id=wp_insert_post(wp_slash($arr), true); }
    if (!is_wp_error($id)) {
        update_post_meta($id,'_zolei_section_key',sanitize_title($key));
        update_post_meta($id,'_zolei_section_nav_label',sanitize_text_field($nav ?: $title));
        update_post_meta($id,'_zolei_section_shortcode',wp_kses_post($shortcode));
        update_post_meta($id,'_zolei_section_pdf_type',sanitize_key($pdf_type));
        update_post_meta($id,'_zolei_section_active','1');
    }
    return $id;
}

function zolei_demo_legacy_content_for_slug($slug) {
    foreach (zolei_demo_read_manifest('zolei-content.json') as $entry) {
        if (($entry['slug'] ?? '') === $slug) { return zolei_demo_enhance_legacy_content($entry['content'] ?? '', $slug, $entry['title'] ?? ''); }
    }
    return '';
}

function zolei_demo_import_one_page_sections() {
    $count = 0;
    $map = array(
        array('calendar','Turnīri',10,'[zolei_calendar]','','Turnīri',''),
        array('rules','Zoles noteikumi',20,'[zolei_pdf_section section="rules"]','nolikums','Noteikumi',''),
        array('results','Rezultāti',30,'[zolei_pdf_section section="results"]','rezultati','Rezultāti',''),
        array('ratings','Reitingi',40,'[zolei_pdf_section section="ratings"]','reitings','Reitingi',''),
        array('protocols','Protokoli',50,'[zolei_pdf_section section="protocols"]','protokoli','Protokoli',''),
        array('archive','Arhīvs',60,'[zolei_pdf_section section="archive"]','arhivs','Arhīvs',''),
        array('gallery','Galerija',70,'','','Galerija',''),
        array('contact','Kontakti',80,'','','Kontakti',''),
    );
    foreach($map as $row){
        list($key,$title,$order,$shortcode,$pdf_type,$nav,$content) = $row;
        $id = zolei_demo_upsert_section($key,$title,$content,$order,$shortcode,$pdf_type,$nav);
        if(!is_wp_error($id)) $count++;
    }
    return $count;
}

function zolei_demo_trash_extra_generated_pages() {
    $keep = array('sakums','home');
    $generated = array('jaunumi','turniri','zoles-noteikumi','rezultati','reitingi','meistari','galerija','kontakti','news','calendar','rules','results','ratings','masters','gallery','ethics-code','contact');
    foreach($generated as $slug){
        if(in_array($slug,$keep,true)) continue;
        $page = zolei_demo_find_post($slug,'page');
        if($page && $page->ID != intval(get_option('page_on_front'))) { wp_trash_post($page->ID); }
    }
}


function zolei_demo_import_info_pages() {
    $pages = array(
        array('valde-un-kontakti', 'Valde un kontakti', '<!-- wp:heading --><h2>Valde un kontakti</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong>Uldis Vītols:</strong> info@zolei.lv</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>Elgars Sapats:</strong> info@zolei.lv, Tel. 22315099</p><!-- /wp:paragraph -->', 'lv'),
        array('etikas-kodekss', 'Ētikas kodekss', '<!-- wp:heading --><h2>Zolītes spēles ētikas kodekss</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Ētikas kodekss nosaka spēlētāju uzvedības pamatprincipus un palīdz uzturēt godīgu, cieņpilnu spēles vidi.</p><!-- /wp:paragraph --><!-- wp:list {"ordered":true} --><ol><li>Spēlē atbilstoši noteikumiem.</li><li>Izturies pret citiem spēlētājiem ar cieņu.</li><li>Nemānies un negodīgi neizmanto citus spēlētājus.</li><li>Cieni turnīra organizētāju un inventāru.</li><li>Strīdus risini mierīgi un korekti.</li></ol><!-- /wp:list -->', 'lv'),
        array('nolikumi', 'Nolikumi', '<!-- wp:heading --><h2>Nolikumi</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Turnīru nolikumi un saistītie PDF dokumenti.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[zolei_pdf_section section="rules"]<!-- /wp:shortcode -->', 'lv'),
    );
    $count = 0;
    foreach ($pages as $p) {
        $id = zolei_demo_upsert($p[0], $p[1], $p[2], 'page', $p[3]);
        if (!is_wp_error($id)) { $count++; }
    }
    return $count;
}

function zolei_demo_import() {
    update_option('blogname','Zolei.lv');
    update_option('blogdescription','Latvijas Zolītes federācija');
    $result = array('pages'=>0,'sections'=>0,'events'=>0,'posts'=>0,'pdfs'=>0,'gallery'=>0,'legacy'=>0,'menu'=>false);
    $home_content = zolei_bbuilder_home_content('lv');
    $home_id = zolei_demo_upsert('sakums', 'Sākums', $home_content, 'page', 'lv');
    if (!is_wp_error($home_id)) { $result['pages']++; update_post_meta($home_id,'_zolei_single_page_shell','1'); }
    if (function_exists('pll_set_post_language')) {
        $home_en = zolei_demo_upsert('home', 'Home', zolei_bbuilder_home_content('en'), 'page', 'en');
        if (!is_wp_error($home_en)) { $result['pages']++; pll_set_post_language($home_en,'en'); }
        if (function_exists('pll_save_post_translations') && !is_wp_error($home_id) && !is_wp_error($home_en)) { pll_save_post_translations(array('lv'=>$home_id,'en'=>$home_en)); }
    }
    $months = zolei_default_months();
    update_option('zolei_calendar_months', $months);
    $result['gallery'] = zolei_demo_import_gallery_media(24, false);
    $result['posts'] = zolei_demo_import_blog_posts();
    $result['pages'] += zolei_demo_import_info_pages();
    if (!get_option('zolei_pdf_sections') && function_exists('zolei_pdf_section_defaults')) { update_option('zolei_pdf_sections', zolei_pdf_section_defaults()); }
    $result['sections'] = zolei_demo_import_one_page_sections();
    // First demo import intentionally does not copy/import PDFs. Use the separate PDF import action later from admin.
    $result['pdfs'] = 0;
    foreach ($months as $m) { if (!empty($m['events']) && is_array($m['events'])) { $result['events'] += count($m['events']); } }
    $front = zolei_demo_find_post('sakums','page');
    if ($front) { update_option('show_on_front','page'); update_option('page_on_front',$front->ID); }
    zolei_demo_trash_extra_generated_pages();
    $menu_name = 'Zolei.lv one page';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu($menu_name);
    if (!is_wp_error($menu_id)) {
        foreach((wp_get_nav_menu_items($menu_id) ?: array()) as $item){ wp_delete_post($item->ID, true); }
        $anchors = array('calendar'=>'Turnīri','rules'=>'Noteikumi','results'=>'Rezultāti','ratings'=>'Reitingi','protocols'=>'Protokoli','archive'=>'Arhīvs','gallery'=>'Galerija','news'=>'Jaunumi','contact'=>'Kontakti');
        foreach($anchors as $anchor=>$label){ wp_update_nav_menu_item($menu_id, 0, array('menu-item-title'=>$label,'menu-item-url'=>home_url('/#'.$anchor),'menu-item-type'=>'custom','menu-item-status'=>'publish')); }
        $loc = get_theme_mod('nav_menu_locations'); if (!is_array($loc)) { $loc = array(); }
        $loc['primary'] = $menu_id; $loc['footer'] = $menu_id; set_theme_mod('nav_menu_locations', $loc);
        $result['menu'] = true;
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
        $message = sprintf(__('Imported/updated: %1$d single front page, %2$d clean one-page sections, %3$d calendar events and demo news posts. PDFs were not imported; use the separate PDF import button when ready.','zolei-react'), intval($r['pages']), intval($r['sections']), intval($r['events']));
    }
    if (isset($_POST['zolei_import_demo_pdfs'])) {
        check_admin_referer('zolei_import_demo_pdfs_action','zolei_import_demo_pdfs_nonce');
        $pdf_count = function_exists('zolei_demo_import_pdfs') ? zolei_demo_import_pdfs(false) : 0;
        $message = sprintf(__('Imported/updated %d PDF records and copied packaged PDFs into the configured uploads directories.','zolei-react'), intval($pdf_count));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Zolei.lv demo import','zolei-react'); ?></h1>
        <?php if ($message): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div><?php endif; ?>
        <p><?php esc_html_e('Creates one clean React headless front page, anchor menu, remote gallery references and monthly calendar data. The first demo import does not copy/register PDFs or download gallery images, so it avoids server timeouts/fatal import errors.','zolei-react'); ?></p>
        <p><strong><?php echo esc_html(zolei_demo_content_manifest_count()); ?></strong> <?php esc_html_e('legacy content items packaged.','zolei-react'); ?> <strong><?php echo esc_html(zolei_demo_pdf_manifest_count()); ?></strong> <?php esc_html_e('PDF files packaged for later import.','zolei-react'); ?></p>
        <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:18px;max-width:980px;margin-top:18px;">
          <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;">
            <h2 style="margin-top:0;"><?php esc_html_e('1. First demo without PDFs','zolei-react'); ?></h2>
            <p><?php esc_html_e('Use this first. It builds the pretty one-page website, calendar, gallery references, menu and settings without copying hundreds of PDFs or downloading media files.','zolei-react'); ?></p>
            <form method="post">
                <?php wp_nonce_field('zolei_import_demo_action','zolei_import_demo_nonce'); ?>
                <p><button type="submit" name="zolei_import_demo" class="button button-primary button-hero"><?php esc_html_e('Import / Update one-page site only','zolei-react'); ?></button></p>
            </form>
          </div>
          <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;">
            <h2 style="margin-top:0;"><?php esc_html_e('2. Import PDFs later','zolei-react'); ?></h2>
            <p><?php esc_html_e('Run this only after the first demo works. It copies all packaged PDFs into the old-style uploads directories and creates PDF records.','zolei-react'); ?></p>
            <form method="post">
                <?php wp_nonce_field('zolei_import_demo_pdfs_action','zolei_import_demo_pdfs_nonce'); ?>
                <p><button type="submit" name="zolei_import_demo_pdfs" class="button button-secondary button-hero"><?php esc_html_e('Import packaged PDFs only','zolei-react'); ?></button></p>
            </form>
          </div>
        </div>
    </div>
    <?php
}
