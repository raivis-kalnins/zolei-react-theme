<?php
if (!defined('ABSPATH')) { exit; }

define('ZOLEI_THEME_VERSION', '1.9.0');

function zolei_setup() {
    load_theme_textdomain('zolei-react', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height'=>160,'width'=>160,'flex-height'=>true,'flex-width'=>true));
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_post_type_support('page', 'excerpt');
    add_editor_style('assets/css/theme.css');
    register_nav_menus(array('primary'=>__('Galvenā izvēlne','zolei-react'),'footer'=>__('Kājenes izvēlne','zolei-react')));
}
add_action('after_setup_theme','zolei_setup');

function zolei_scripts() {
    $ver = wp_get_theme()->get('Version') ?: ZOLEI_THEME_VERSION;
    wp_enqueue_style('zolei-fonts','https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&family=Cormorant+Garamond:wght@500;600;700&display=swap&subset=latin-ext',array(),null);
    wp_enqueue_style('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',array(),'5.3.3');
    wp_enqueue_style('zolei-theme',get_template_directory_uri().'/assets/css/theme.css',array('bootstrap','zolei-fonts'),filemtime(get_template_directory().'/assets/css/theme.css'));
    wp_enqueue_script('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',array(),'5.3.3',true);
    wp_enqueue_script('zolei-theme',get_template_directory_uri().'/assets/js/theme.js',array(),filemtime(get_template_directory().'/assets/js/theme.js'),true);
    wp_localize_script('zolei-theme', 'zoleiSearch', array('ajaxUrl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('zolei_search_nonce'),'lang'=>zolei_lang(),'labels'=>array('placeholder'=>zolei_i18n('Meklēt turnīrus, rezultātus, lapas...', 'Search tournaments, results, pages...'),'noResults'=>zolei_i18n('Nekas netika atrasts.', 'No results found.'),'viewAll'=>zolei_i18n('Skatīt visus rezultātus', 'View all results'),'searching'=>zolei_i18n('Meklē...', 'Searching...'))));
    wp_localize_script('zolei-theme', 'zoleiForm', array(
        'ajaxUrl'=>admin_url('admin-ajax.php'),
        'nonce'=>wp_create_nonce('zolei_contact_nonce'),
        'lang'=>zolei_lang(),
        'hcaptchaSiteKey'=>zolei_hcaptcha_site_key(),
        'hcaptchaApiUrl'=>zolei_hcaptcha_api_url(),
        'labels'=>array(
            'sending'=>zolei_i18n('Nosūta...', 'Sending...'),
            'success'=>zolei_i18n('Paldies! Ziņa nosūtīta.', 'Thank you! Your message was sent.'),
            'error'=>zolei_i18n('Neizdevās nosūtīt ziņu. Lūdzu, mēģini vēlreiz.', 'Could not send the message. Please try again.'),
            'validation'=>zolei_i18n('Lūdzu, aizpildi obligātos laukus.', 'Please fill in the required fields.')
        )
    ));
    if (is_front_page()) {
        wp_enqueue_script('zolei-headless-home',get_template_directory_uri().'/assets/js/headless-home.js',array('wp-element','bootstrap'),filemtime(get_template_directory().'/assets/js/headless-home.js'),true);
    }
}
add_action('wp_enqueue_scripts','zolei_scripts');

function zolei_lang_is_en() { return function_exists('pll_current_language') && pll_current_language('slug') === 'en'; }
function zolei_lang() { return function_exists('pll_current_language') ? pll_current_language('slug') : 'lv'; }
function zolei_i18n($lv,$en='') { return zolei_lang_is_en() ? ($en !== '' ? $en : $lv) : $lv; }

function zolei_hcaptcha_site_key() { return trim((string) get_option('zolei_hcaptcha_site_key', '')); }
function zolei_hcaptcha_secret_key() { return trim((string) get_option('zolei_hcaptcha_secret_key', '')); }
function zolei_hcaptcha_enabled() { return zolei_hcaptcha_site_key() !== ''; }
function zolei_hcaptcha_api_url() {
    if (!zolei_hcaptcha_enabled()) { return ''; }
    return add_query_arg(array('render'=>'explicit','hl'=>zolei_lang_is_en() ? 'en' : 'lv'), 'https://js.hcaptcha.com/1/api.js');
}
function zolei_verify_hcaptcha($token) {
    $secret = zolei_hcaptcha_secret_key();
    if (!zolei_hcaptcha_enabled() || $secret === '') { return true; }
    if ($token === '') { return false; }
    $response = wp_remote_post('https://hcaptcha.com/siteverify', array(
        'timeout' => 8,
        'body' => array('secret'=>$secret, 'response'=>$token, 'remoteip'=>isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '')
    ));
    if (is_wp_error($response)) { return false; }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    return !empty($body['success']);
}
function zolei_contact_ajax() {
    check_ajax_referer('zolei_contact_nonce', 'nonce');
    $website = trim((string) wp_unslash($_POST['website'] ?? ''));
    if ($website !== '') { wp_send_json_success(array('message'=>zolei_i18n('Paldies! Ziņa nosūtīta.', 'Thank you! Your message was sent.'))); }
    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $subject = sanitize_text_field(wp_unslash($_POST['subject'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    $captcha = sanitize_text_field(wp_unslash($_POST['h-captcha-response'] ?? ''));
    if ($name === '' || $email === '' || $message === '' || !is_email($email)) {
        wp_send_json_error(array('message'=>zolei_i18n('Lūdzu, aizpildi vārdu, e-pastu un ziņu.', 'Please enter your name, email and message.')), 400);
    }
    if (!zolei_verify_hcaptcha($captcha)) {
        wp_send_json_error(array('message'=>zolei_i18n('hCaptcha pārbaude neizdevās.', 'hCaptcha verification failed.')), 400);
    }
    $to = get_option('zolei_contact_email', get_option('admin_email'));
    if (!is_email($to)) { $to = get_option('admin_email'); }
    $mail_subject = $subject ? $subject : zolei_i18n('Ziņa no Zolei.lv formas', 'Message from Zolei.lv form');
    $body = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n{$message}";
    $headers = array('Reply-To: '.$name.' <'.$email.'>');
    $sent = wp_mail($to, $mail_subject, $body, $headers);
    if (!$sent) { wp_send_json_error(array('message'=>zolei_i18n('Neizdevās nosūtīt ziņu. Lūdzu, mēģini vēlreiz.', 'Could not send the message. Please try again.')), 500); }
    wp_send_json_success(array('message'=>zolei_i18n('Paldies! Ziņa nosūtīta.', 'Thank you! Your message was sent.')));
}
add_action('wp_ajax_zolei_contact_submit', 'zolei_contact_ajax');
add_action('wp_ajax_nopriv_zolei_contact_submit', 'zolei_contact_ajax');

function zolei_language_switcher() {
    echo '<div class="zole-lang" aria-label="'.esc_attr__('Language switcher','zolei-react').'">';
    if (function_exists('pll_the_languages')) {
        $langs = pll_the_languages(array('raw'=>1,'hide_if_empty'=>0));
        if (is_array($langs)) {
            foreach ($langs as $lang) {
                $slug = isset($lang['slug']) ? sanitize_key($lang['slug']) : '';
                $url = !empty($lang['url']) ? $lang['url'] : '#';
                $active = !empty($lang['current_lang']) ? ' active' : '';
                $label = $slug === 'en' ? 'EN' : ($slug === 'lv' ? 'LV' : strtoupper($slug));
                $flag_url = '';
                $flag_html = '';

                if (!empty($lang['flag_url'])) {
                    $flag_url = $lang['flag_url'];
                } elseif (!empty($lang['flag']) && is_string($lang['flag'])) {
                    // Polylang versions differ: raw flag may be HTML or may be a URL string.
                    if (strpos($lang['flag'], '<img') !== false) {
                        $flag_html = $lang['flag'];
                    } elseif (filter_var($lang['flag'], FILTER_VALIDATE_URL) || preg_match('~\.(png|jpe?g|gif|svg)(\?.*)?$~i', $lang['flag'])) {
                        $flag_url = $lang['flag'];
                    }
                }

                echo '<a class="zole-lang-link'.$active.'" href="'.esc_url($url).'" hreflang="'.esc_attr($slug).'" aria-label="'.esc_attr($label).'">';
                if ($flag_url) {
                    echo '<img src="'.esc_url($flag_url).'" alt="" loading="lazy">';
                } elseif ($flag_html) {
                    echo wp_kses($flag_html, array('img'=>array('src'=>true,'alt'=>true,'class'=>true,'width'=>true,'height'=>true,'loading'=>true,'decoding'=>true)));
                } else {
                    echo '<span class="zole-fallback-flag">'.($slug === 'en' ? '🇬🇧' : '🇱🇻').'</span>';
                }
                echo '<span class="zole-lang-label">'.esc_html($label).'</span></a>';
            }
        }
    } else {
        echo '<a class="zole-lang-link active" href="'.esc_url(home_url('/')).'"><span class="zole-fallback-flag">🇱🇻</span><span class="zole-lang-label">LV</span></a>';
        echo '<a class="zole-lang-link" href="'.esc_url(home_url('/en/')).'"><span class="zole-fallback-flag">🇬🇧</span><span class="zole-lang-label">EN</span></a>';
    }
    echo '</div>';
}

function zolei_page_url($lv_slug,$en_slug='') {
    $slug = zolei_lang_is_en() && $en_slug ? $en_slug : $lv_slug;
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page->ID) : home_url('/'.trim($slug,'/').'/');
}
function zolei_brand() {
    $name = get_bloginfo('name');
    if (!$name || strtolower(trim($name)) === 'my blog') { $name = 'Zolei.lv'; }
    echo '<a class="zole-brand" href="'.esc_url(home_url('/')).'" rel="home">';
    if (has_custom_logo()) { the_custom_logo(); } else { echo '<img src="'.esc_url(get_template_directory_uri().'/assets/images/zole-logo.jpg').'" alt="'.esc_attr($name).'">'; }
    echo '<span class="zole-brand-copy"><strong class="zole-brand-title">'.esc_html($name).'</strong><span class="zole-brand-tag">'.esc_html(zolei_i18n('Latvijas Zolītes federācija','Latvian Zolīte Federation')).'</span></span></a>';
}
function zolei_nav_menu() {
    wp_nav_menu(array('theme_location'=>'primary','container'=>false,'menu_class'=>'','fallback_cb'=>'zolei_menu_fallback'));
}
function zolei_menu_fallback() { ?>
<ul>
<li><a href="<?php echo esc_url(home_url('/#calendar')); ?>"><?php echo esc_html(zolei_i18n('Turnīri','Calendar')); ?></a></li>
<li><a href="<?php echo esc_url(home_url('/#rules')); ?>"><?php echo esc_html(zolei_i18n('Noteikumi','Rules')); ?></a></li>
<li><a href="<?php echo esc_url(home_url('/#results')); ?>"><?php echo esc_html(zolei_i18n('Rezultāti','Results')); ?></a></li>
<li><a href="<?php echo esc_url(home_url('/#ratings')); ?>"><?php echo esc_html(zolei_i18n('Reitingi','Ratings')); ?></a></li>
<li><a href="<?php echo esc_url(home_url('/#gallery')); ?>"><?php echo esc_html(zolei_i18n('Galerija','Gallery')); ?></a></li>
<li><a href="<?php echo esc_url(home_url('/#contact')); ?>"><?php echo esc_html(zolei_i18n('Kontakti','Contact')); ?></a></li>
</ul>
<?php }


function zolei_asset_image_url($file) {
    $file = ltrim((string) $file, '/');
    return get_template_directory_uri() . '/assets/images/' . $file;
}

function zolei_prefer_avif_url($url) {
    $url = trim((string) $url);
    if ($url === '') { return $url; }
    if (preg_match('~\.avif($|\?)~i', $url)) { return $url; }
    $path = '';
    $upload = wp_upload_dir();
    if (!empty($upload['baseurl']) && strpos($url, $upload['baseurl']) === 0) {
        $rel = ltrim(substr($url, strlen($upload['baseurl'])), '/');
        $path = trailingslashit($upload['basedir']) . preg_replace('~\?.*$~', '', $rel);
    } elseif (strpos($url, get_template_directory_uri()) === 0) {
        $rel = ltrim(substr($url, strlen(get_template_directory_uri())), '/');
        $path = trailingslashit(get_template_directory()) . preg_replace('~\?.*$~', '', $rel);
    }
    if ($path && preg_match('~\.(jpe?g|png|webp)$~i', $path)) {
        $avif_path = preg_replace('~\.(jpe?g|png|webp)$~i', '.avif', $path);
        if ($avif_path && file_exists($avif_path)) {
            return preg_replace('~\.(jpe?g|png|webp)(\?.*)?$~i', '.avif$2', $url);
        }
    }
    return $url;
}

function zolei_image_to_avif($source_path, $max_width = 1800, $quality = 78) {
    $source_path = (string) $source_path;
    if (!file_exists($source_path) || !is_readable($source_path)) { return false; }
    if (!preg_match('~\.(jpe?g|png|webp)$~i', $source_path)) { return false; }
    $dest = preg_replace('~\.(jpe?g|png|webp)$~i', '.avif', $source_path);
    if (!$dest) { return false; }
    $editor = wp_get_image_editor($source_path);
    if (is_wp_error($editor)) { return false; }
    $size = $editor->get_size();
    if (!empty($size['width']) && intval($size['width']) > $max_width) { $editor->resize($max_width, null, false); }
    $saved = $editor->save($dest, 'image/avif');
    if (is_wp_error($saved) || !file_exists($dest)) { return false; }
    return $dest;
}

function zolei_make_gallery_versions($source_path, $base_name = '') {
    $source_path = (string) $source_path;
    if (!file_exists($source_path)) { return false; }
    $dir = dirname($source_path);
    $name = $base_name ? sanitize_file_name($base_name) : basename($source_path);
    $name = preg_replace('~\.(jpe?g|png|webp|avif)$~i', '', $name);
    $full_jpg = trailingslashit($dir) . $name . '-full.jpg';
    $thumb_jpg = trailingslashit($dir) . $name . '-thumb.jpg';
    $mobile_jpg = trailingslashit($dir) . $name . '-mobile.jpg';
    $specs = array(
        array($full_jpg, 2200, 0, false, 90),
        array($mobile_jpg, 1600, 0, false, 88),
        array($thumb_jpg, 1400, 1050, true, 88),
    );
    foreach ($specs as $spec) {
        $editor = wp_get_image_editor($source_path);
        if (!is_wp_error($editor)) {
            if (!empty($spec[3])) { $editor->resize($spec[1], $spec[2], true); }
            else { $editor->resize($spec[1], null, false); }
            $editor->save($spec[0], 'image/jpeg');
            zolei_image_to_avif($spec[0], $spec[1], $spec[4]);
        }
    }
    return array('full'=>$full_jpg, 'thumb'=>$thumb_jpg, 'mobile'=>$mobile_jpg);
}

add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    $path = get_attached_file($attachment_id);
    if ($path && preg_match('~\.(jpe?g|png|webp)$~i', $path)) { zolei_image_to_avif($path, 1920, 80); }
    return $metadata;
}, 20, 2);

function zolei_default_months() { return json_decode('[{"slug": "janvaris", "lv": "Janvāris", "en": "January", "events": [{"day": "03", "title": "03.01.2026. Olaines novada kauss zolītē 2025/2026, 4. no 6 posmiem, Olaines Sporta namā, Zemgales ielā 33a, sākums 11:00, formāts 5 kārtas pa 20 partijām, klasiskā zole. Dalība bez maksas. Info: Mārtiņš 20 44 33 77.", "url": "https://zolei.lv/janvaris/"}, {"day": "04", "title": "04.01.2026. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1, info Arnolds 29235464.", "url": "https://zolei.lv/janvaris/"}, {"day": "10", "title": "10.01.2026. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, F30/1, sākums 10:00, info Leons 29196702.", "url": "https://zolei.lv/janvaris/"}, {"day": "11", "title": "11.01.2026. KA būves kauss, Saldus Druva, kafejnīca Saules rats. Elites reitinga turnīrs, formāts 30+10/1, 7×28, sākums 10:00, info Kaspars 29666129.", "url": "https://zolei.lv/janvaris/"}, {"day": "17", "title": "17.01.2026. Bauskas zoles turnīrs, Minču štelles / Truck parking, Codes pag., formāts 30+10/1, sākums 10:00, info Guntars 29279632.", "url": "https://zolei.lv/janvaris/"}, {"day": "18", "title": "18.01.2026. Elites kauss 1. posms, Abzaļi, Ādažu novads, formāts 7×28, 25+10/1, sākums 10:00, info Elgars 22315099, info@zolei.lv.", "url": "https://zolei.lv/janvaris/"}, {"day": "23", "title": "23.01.2026. Mores ziemas kauss zolītē, Mores pagasta Tautas nams, sākums 19:00, formāts 4 kārtas pa 16 partijām, dalība bez maksas.", "url": "https://zolei.lv/janvaris/"}, {"day": "24", "title": "24.01.2026. Arņa Kleinberga piemiņas turnīrs Tukumā, Tukuma ledus halle, formāts 30+10/1, 28×8, sākums 10:00, info Juris 28263622.", "url": "https://zolei.lv/janvaris/"}, {"day": "25", "title": "25.01.2026. Latvijas zoles čempionāts 2025, 4. posms / Māra Vēja piemiņas turnīrs, Abzaļi, Ādažu novads, TOP 40, sākums 10:00.", "url": "https://zolei.lv/janvaris/"}, {"day": "30", "title": "30.01.2026. Garkalnes kauss zolītē 2026, 1. no 5 posmiem, Kultūras centrs Berģi, Brīvības gatve 455, Rīga, sākums 19:00.", "url": "https://zolei.lv/janvaris/"}, {"day": "31", "title": "31.01.2026. Barkavas kausa izcīņa, Barkavas kultūras nams, formāts 30/2 8/2, sākums 10:00, info Andris 28704633.", "url": "https://zolei.lv/janvaris/"}]}, {"slug": "februaris", "lv": "Februāris", "en": "February", "events": [{"day": "07", "title": "07.02.2026. Olaines novada kauss zolītē 2025/2026, 5. no 6 posmiem, Olaines Sporta nams, sākums 11:00, formāts 5 kārtas pa 20 partijām.", "url": "https://zolei.lv/februaris/"}, {"day": "07", "title": "07.02.2026. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1, info Arnolds 29235464.", "url": "https://zolei.lv/februaris/"}, {"day": "14", "title": "14.02.2026. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00, info Leons 29196702.", "url": "https://zolei.lv/februaris/"}, {"day": "14", "title": "14.02.2026. Mārupes pagasta zoles turnīrs, 1. posms, Babītes Sporta komplekss, Jūrmalas iela 17, sākums 11:00.", "url": "https://zolei.lv/februaris/"}, {"day": "15", "title": "15.02.2026. Elites kauss 2. posms, Abzaļi, Ādažu novads, formāts 7×28, 30+10/1, sākums 10:00, info Elgars 22315099.", "url": "https://zolei.lv/februaris/"}, {"day": "19", "title": "19.02.2026. Ceturtdienas Priekuļu turnīrs (rondo), Hotel Tigra, Priekuļi, formāts 25+15/1, 7/24, sākums 18:30.", "url": "https://zolei.lv/februaris/"}, {"day": "27", "title": "27.02.2026. Garkalnes kauss zolītē 2026, 2. no 5 posmiem, Kultūras centrs Berģi, Rīga, sākums 19:00.", "url": "https://zolei.lv/februaris/"}, {"day": "28", "title": "28.02.2026. 7×7 Zolmeistars, Veidenbauma iela 2, Priekuļi, Cēsis, formāts 14/24, 40+25/1, sākums 09:00.", "url": "https://zolei.lv/februaris/"}]}, {"slug": "marts", "lv": "Marts", "en": "March", "events": [{"day": "07", "title": "07.03.2026. Olaines novada kauss zolītē 2025/2026, 6. no 6 posmiem, Olaines Sporta nams, sākums 11:00.", "url": "https://zolei.lv/marts/"}, {"day": "07", "title": "07.03.2026. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1.", "url": "https://zolei.lv/marts/"}, {"day": "08", "title": "08.03.2026. Elites kauss 3. posms, Vilces iela 8, Svēte, formāts 7×28, 30+10/1, sākums 10:00.", "url": "https://zolei.lv/marts/"}, {"day": "14", "title": "14.03.2026. Kokneses Zolmeistars, Kokneses sporta centrs, formāts 30/1, 7/28, sākums 10:00, info Egīls 27867867.", "url": "https://zolei.lv/marts/"}, {"day": "14", "title": "14.03.2026. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00.", "url": "https://zolei.lv/marts/"}, {"day": "22", "title": "22.03.2026. Ulda Spēlmaņa piemiņas turnīrs, Saldus, kafejnīca Mežavējš, formāts 35+15/1, 7×28, sākums 10:00.", "url": "https://zolei.lv/marts/"}, {"day": "28", "title": "28.03.2026. Rīgas zolītes turnīrs, Ulbroka, restorāns Azerbaidžāna, formāts 40+10/1, 8/28, sākums 10:00.", "url": "https://zolei.lv/marts/"}, {"day": "29", "title": "29.03.2026. Rēzeknes zolītes turnīrs, Rēzekne, Atbrīvošanas aleja 100, formāts 30+10/1, 8/28, sākums 10:00.", "url": "https://zolei.lv/marts/"}]}, {"slug": "aprilis", "lv": "Aprīlis", "en": "April", "events": [{"day": "04", "title": "04.04.2026. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1.", "url": "https://zolei.lv/aprilis/"}, {"day": "06", "title": "06.04.2026. Sēlijas Lieldienu ZOLES turnīrs, Ērberģes muiža, Aizkraukles novads, formāts 20/0.5, 7×24, sākums 10:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "06", "title": "06.04.2026. Lieldienu zolītes spēle, Krimuldas Tautas nams, sākums 11:00, formāts 4 kārtas pa 20 partijām.", "url": "https://zolei.lv/aprilis/"}, {"day": "10", "title": "10.04.2026. Garkalnes kauss zolītē 2026, 3. posms, Kultūras centrs Berģi, Rīga, sākums 19:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "11", "title": "11.04.2026. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "12", "title": "12.04.2026. Latvijas 15. senioru čempionāts (60+), Rīga, Republikas laukums 1, kafejnīca Stragorod, formāts 7/26, sākums 12:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "12", "title": "12.04.2026. Rīgas zolītes turnīrs, Ulbroka, restorāns Azerbaidžāna, formāts 40+10/1, 8/28, sākums 10:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "18", "title": "18.04.2026. Aizkraukles novada zolītes turnīrs, Aizkraukles sporta centrs, formāts 7×28, 15+15/1, sākums 10:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "19", "title": "19.04.2026. Elites kauss 4. posms, Abzaļi, Ādažu novads, formāts 7×28, 30+10/1, sākums 10:00.", "url": "https://zolei.lv/aprilis/"}, {"day": "24", "title": "24.04.2026. Mores pavasara kauss zolītē, Mores pagasta Tautas nams, sākums 19:00, formāts 4 kārtas pa 16 partijām.", "url": "https://zolei.lv/aprilis/"}, {"day": "25", "title": "25.04.2026. Zoles turnīrs Tukumā, Tukuma ledus halle, formāts 30+10/1, 8/28.", "url": "https://zolei.lv/aprilis/"}, {"day": "26", "title": "26.04.2026. Rēzeknes zolītes turnīrs, Atbrīvošanas aleja 100, formāts 30+10/1, 8/28, sākums 10:00.", "url": "https://zolei.lv/aprilis/"}]}, {"slug": "maijs", "lv": "Maijs", "en": "May", "events": [{"day": "02", "title": "02.05.2026. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1.", "url": "https://zolei.lv/maijs/"}, {"day": "09", "title": "09.05.2026. 7×7 Zolmeistars, Veidenbauma iela 2, Priekuļi, Cēsis, formāts 14/24, sākums 09:00.", "url": "https://zolei.lv/maijs/"}, {"day": "17", "title": "17.05.2026. Latvijas zoles čempionāts 2026, 1. posms, Abzaļi, Ādažu novads, formāts 7×28, 30+10/1, sākums 10:00.", "url": "https://zolei.lv/maijs/"}]}, {"slug": "junijs", "lv": "Jūnijs", "en": "June", "events": [{"day": "14", "title": "14.06.2026. Divcīņu čempionāts, Abzaļi, Ādažu novads, Elites reitinga turnīrs, formāts 7×28, 30+10/1, sākums 10:00, info Elgars 22315099.", "url": "https://zolei.lv/junijs/"}]}, {"slug": "julijs", "lv": "Jūlijs", "en": "July", "events": [{"day": "11", "title": "11.–12.07.2026. Dižsvētku Valkas zolītes turnīrs. 11.07 klasika, tautas klase; 12.07 Elites reitinga turnīrs.", "url": "https://zolei.lv/julijs/"}, {"day": "19", "title": "19.07.2026. Edgara Taranova piemiņas turnīrs, Elites kauss 5. posms, Abzaļi, Ādažu novads, formāts 7×28, 30+10/1, sākums 10:00.", "url": "https://zolei.lv/julijs/"}]}, {"slug": "augusts", "lv": "Augusts", "en": "August", "events": [{"day": "02", "title": "02.08.2025. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1.", "url": "https://zolei.lv/augusts/"}, {"day": "09", "title": "09.08.2025. Bauskas zoles turnīrs, Minču štelles / Truck parking, Codes pag., formāts 30+10/1, sākums 10:00.", "url": "https://zolei.lv/augusts/"}, {"day": "16", "title": "16.08.2025. Sadraudzības turnīrs 4×4, Cesvaines pils lielā zāle, reģistrācija 09:30–09:50, formāts 30+10/1.", "url": "https://zolei.lv/augusts/"}, {"day": "16", "title": "16.08.2025. Amatas sporta svētku zolītes turnīrs, Zvārtas iela 1, Līvu ciemats, sākums 10:00, dalība bez maksas.", "url": "https://zolei.lv/augusts/"}, {"day": "16", "title": "16.08.2025. Mores zolītes vasaras kauss, Mores pagasta Tautas nams, sākums 16:00, formāts 4 kārtas pa 20 partijām.", "url": "https://zolei.lv/augusts/"}, {"day": "24", "title": "24.08.2025. Komandu zoles čempionāts 2024, Vilces iela 8, Svēte, formāts 25+10/1, 7×28, sākums 10:00.", "url": "https://zolei.lv/augusts/"}, {"day": "30", "title": "30.08.2025. KA būves kauss, Saldus Druva, kafejnīca Saules rats, formāts 25+10/1, 7×28, sākums 10:00.", "url": "https://zolei.lv/augusts/"}]}, {"slug": "septembris", "lv": "Septembris", "en": "September", "events": [{"day": "06", "title": "06.09.2025. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00, formāts 30+10/1.", "url": "https://zolei.lv/septembris/"}, {"day": "06", "title": "06.09.2025. VB DISTILLERY kauss zolītē 2025, 5. posms, MaxStokā.", "url": "https://zolei.lv/septembris/"}, {"day": "13", "title": "13.09.2025. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00.", "url": "https://zolei.lv/septembris/"}, {"day": "19", "title": "19.09.2025. Garkalnes kauss zolītē 2025, 3. no 5 posmiem, Kultūras centrs Berģi, Rīga, sākums 19:00.", "url": "https://zolei.lv/septembris/"}, {"day": "20", "title": "20.09.2025. Zoles turnīrs Tukumā, Tukuma ledus halle, formāts 8/28, dalība 30+10/1.", "url": "https://zolei.lv/septembris/"}, {"day": "21", "title": "21.09.2025. Latvijas zoles čempionāts 2025, 2. posms, Rīga, Vienības gatve 87H, TOP 60, sākums 10:00.", "url": "https://zolei.lv/septembris/"}, {"day": "27", "title": "27.09.2025. Bauskas zoles turnīrs, Minču štelles / Truck parking, Codes pag., formāts 30+10/1, sākums 10:00.", "url": "https://zolei.lv/septembris/"}, {"day": "28", "title": "28.09.2025. KA būves kauss, Saldus Druva, kafejnīca Saules rats, formāts 25+10/1, 7×28, sākums 10:00.", "url": "https://zolei.lv/septembris/"}]}, {"slug": "oktobris", "lv": "Oktobris", "en": "October", "events": [{"day": "03", "title": "03.10.2025. Garkalnes kauss zolītē 2025, 4. no 5 posmiem, Kultūras centrs Berģi, Rīga, sākums 19:00.", "url": "https://zolei.lv/oktobris/"}, {"day": "04", "title": "04.10.2025. Ogres 4×4 turnīrs, Spoon kafe, Brīvības iela 1, Ogre, formāts 4×4/28, 30+10/1.", "url": "https://zolei.lv/oktobris/"}, {"day": "04", "title": "04.10.2025. VB DISTILLERY kauss zolītē 2025, 6. posms, Redditch.", "url": "https://zolei.lv/oktobris/"}, {"day": "04", "title": "04.10.2025. Olaines novada kauss zolītē 2025/2026, 1. no 6 posmiem, Olaines Sporta nams, sākums 11:00.", "url": "https://zolei.lv/oktobris/"}, {"day": "05", "title": "05.10.2025. Zoles turnīrs Tukumā, Tukuma ledus halle, formāts 8/28, info Arnolds un Juris.", "url": "https://zolei.lv/oktobris/"}, {"day": "11", "title": "11.10.2025. KA būves kauss, Saldus Druva, kafejnīca Saules rats, formāts 25+10/1, 7×28.", "url": "https://zolei.lv/oktobris/"}, {"day": "11", "title": "11.10.2025. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00.", "url": "https://zolei.lv/oktobris/"}, {"day": "19", "title": "19.10.2025. Elites kauss 2025, 6. posms, Vilces iela 8, Svēte, formāts 25+10/1, sākums 10:00.", "url": "https://zolei.lv/oktobris/"}, {"day": "24", "title": "24.10.2025. Mores rudens kauss zolītē 2025, Mores pagasta Tautas nams, sākums 19:00, dalība bez maksas.", "url": "https://zolei.lv/oktobris/"}]}, {"slug": "novembris", "lv": "Novembris", "en": "November", "events": [{"day": "01", "title": "01.11.2025. Ogres turnīrs, Spoon kafe, Ogre, formāts 7×28, 30/10, balvas un papildus balvu fonds.", "url": "https://zolei.lv/novembris/"}, {"day": "01", "title": "01.11.2025. Olaines novada kauss zolītē 2025/2026, 2. no 6 posmiem, Olaines Sporta nams, sākums 11:00.", "url": "https://zolei.lv/novembris/"}, {"day": "02", "title": "02.11.2025. Jelgavas zolītes čempionāts, Vilces iela 8, Svēte, sākums 10:00.", "url": "https://zolei.lv/novembris/"}, {"day": "07", "title": "07.11.2025. Garkalnes kauss zolītē 2025, 5. no 5 posmiem, Kultūras centrs Berģi, Rīga, sākums 19:00.", "url": "https://zolei.lv/novembris/"}, {"day": "08", "title": "08.11.2025. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00.", "url": "https://zolei.lv/novembris/"}, {"day": "16", "title": "16.11.2025. Latvijas zoles čempionāts 2025, 3. posms, Vilces iela 8, Svēte, TOP 60, sākums 10:00.", "url": "https://zolei.lv/novembris/"}, {"day": "16", "title": "16.11.2025. Tautas zolītes čempionāts, 1. no 3 posmiem, restorāns Stargorod, Rīga, sākums 10:00.", "url": "https://zolei.lv/novembris/"}, {"day": "23", "title": "23.11.2025. KA būves kauss, Saldus Druva, kafejnīca Saules rats, sākums 10:00.", "url": "https://zolei.lv/novembris/"}, {"day": "23", "title": "23.11.2025. Tautas zolītes čempionāts, 2. no 3 posmiem, restorāns Stargorod, Rīga, sākums 10:00.", "url": "https://zolei.lv/novembris/"}]}, {"slug": "decembris", "lv": "Decembris", "en": "December", "events": [{"day": "06", "title": "06.12.2025. Fināls zolītē, Bauskā, kafejnīca Pie Pils, reģistrācija 09:15–09:45, sākums 10:00.", "url": "https://zolei.lv/decembris/"}, {"day": "06", "title": "06.12.2025. VB DISTILLERY kauss zolītē 2025, 7. posms, Leicesterā.", "url": "https://zolei.lv/decembris/"}, {"day": "13", "title": "13.12.2025. Kalsnavas zolītes čempionāts, Jaunkalsnava, Vesetas iela 8, sākums 10:00.", "url": "https://zolei.lv/decembris/"}, {"day": "13", "title": "13.12.2025. Olaines novada kauss zolītē 2025/2026, 3. no 6 posmiem, Olaines Sporta nams, sākums 11:00.", "url": "https://zolei.lv/decembris/"}, {"day": "21", "title": "21.12.2025. Elites kauss 7. posms, Abzaļi, Ādažu novads, formāts 25+10/1, sākums 10:00.", "url": "https://zolei.lv/decembris/"}, {"day": "26", "title": "26.12.2025. Preiļu novada atklātais čempionāts zolītē, Aglona, Somersēta, formāts 20/10/8 pa 24/1, sākums 10:00.", "url": "https://zolei.lv/decembris/"}, {"day": "27", "title": "27.12.2025. Latvijas zoles čempionāts 2025 pārceltais posms, Hercogs Garden, Mārupe, formāts 7×28, sākums 10:00.", "url": "https://zolei.lv/decembris/"}, {"day": "28", "title": "28.12.2025. Bauskas zoles turnīrs, Minču štelles / Truck parking, Codes pag., formāts 30+10/1, sākums 10:00.", "url": "https://zolei.lv/decembris/"}]}]', true); }
function zolei_default_gallery() { return json_decode('["https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01973.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01948.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01931.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01906.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01899.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01892.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01885.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01880.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01879.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01869.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01848.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01842.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01821.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01815.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01805.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01799.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01797.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01790.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01767.JPG", "https://zolei.lv/wp-content/gallery/zolei_2024/thumbs/thumbs_DSC01762.JPG", "https://zolei.lv/wp-content/uploads/2014/01/DSC07147-500x375.jpg", "https://zolei.lv/wp-content/uploads/2014/01/DSC07148-500x375.jpg", "https://zolei.lv/wp-content/uploads/2014/01/DSC07149-500x375.jpg", "https://zolei.lv/wp-content/uploads/2014/01/DSC07151-500x375.jpg"]', true); }
function zolei_calendar_months() {
    $months = get_option('zolei_calendar_months');
    return (is_array($months) && !empty($months)) ? $months : zolei_default_months();
}

function zolei_gallery_original_url($url) {
    $url = trim((string) $url);
    if ($url === '') { return ''; }
    $url = str_replace('/thumbs/thumbs_', '/', $url);
    $url = preg_replace('~-(\d{2,4})x(\d{2,4})(\.(?:jpe?g|png|webp|avif))$~i', '$3', $url);
    return $url;
}

function zolei_gallery_entry_to_item($entry) {
    if (is_array($entry)) {
        $full = !empty($entry['full']) ? esc_url_raw($entry['full']) : '';
        $thumb = !empty($entry['thumb']) ? esc_url_raw($entry['thumb']) : $full;
        $mobile = !empty($entry['mobile']) ? esc_url_raw($entry['mobile']) : '';
        $source = !empty($entry['source']) ? esc_url_raw($entry['source']) : $full;
        $full_clean = zolei_gallery_original_url($full ?: $source ?: $mobile ?: $thumb);
        // Prefer a larger preview source where possible so gallery cards stay sharp on retina screens.
        $mobile_clean = $mobile ?: $full_clean ?: $thumb;
        $thumb_clean = $thumb ?: $mobile_clean ?: $full_clean;
        return array(
            'thumb' => zolei_prefer_avif_url($thumb_clean),
            'mobile' => zolei_prefer_avif_url($mobile_clean),
            'full' => zolei_prefer_avif_url($full_clean),
            'source' => $source ?: $full_clean,
        );
    }
    $full = zolei_gallery_original_url($entry);
    return array('thumb' => zolei_prefer_avif_url($full), 'mobile' => zolei_prefer_avif_url($full), 'full' => zolei_prefer_avif_url($full), 'source' => $full);
}

function zolei_gallery_items() {
    $images = get_option('zolei_gallery_images');
    if (!is_array($images) || empty($images)) { $images = zolei_default_gallery(); }
    $items = array();
    foreach ($images as $entry) {
        $item = zolei_gallery_entry_to_item($entry);
        if (!empty($item['full'])) { $items[] = $item; }
    }
    return $items;
}

function zolei_gallery_images() {
    $items = zolei_gallery_items();
    return array_values(array_map(function($item){ return $item['full']; }, $items));
}

function zolei_settings() {
    return array(
        'contact_email' => get_option('zolei_contact_email','info@zolei.lv'),
        'contact_phone' => get_option('zolei_contact_phone',''),
        'hero_subline' => get_option('zolei_hero_subline', zolei_i18n('Mārupe. Labvēlīgam lidojumam teicama starta vieta','Mārupe — a good starting place for a favourable flight')),
        'hcaptchaSiteKey' => zolei_hcaptcha_site_key(),
    );
}

function zolei_home_news($limit = 3) {
    $args = array('post_type'=>'post','posts_per_page'=>$limit,'post_status'=>'publish','ignore_sticky_posts'=>true);
    if (function_exists('pll_current_language')) { $args['lang'] = zolei_lang(); }
    $q = new WP_Query($args);
    $items = array();
    $i = 0;
    while ($q->have_posts()) { $q->the_post();
        $id = get_the_ID();
        $title = get_the_title();
        $custom_image = esc_url_raw(get_post_meta($id, '_zolei_blog_image', true));
        $news_fallbacks = array(zolei_asset_image_url('news-zolei-home.jpg'), zolei_asset_image_url('news-results-ratings.jpg'), zolei_asset_image_url('news-tournament-submit.jpg'));
        $fallback = $news_fallbacks[$i % count($news_fallbacks)];
        $image = get_the_post_thumbnail_url($id, 'large') ?: ($custom_image ?: $fallback);
        $image = zolei_prefer_avif_url($image);
        $raw_content = get_the_content(null, false, $id);
        $plain = trim(wp_strip_all_tags(strip_shortcodes($raw_content)));
        $items[] = array(
            'id' => $id,
            'title' => $title,
            'url' => get_permalink($id),
            'date' => get_the_date('d.m.Y', $id),
            'excerpt' => wp_trim_words(get_the_excerpt($id) ?: $plain, 24),
            'content' => wpautop(wp_kses_post($raw_content ?: get_the_excerpt($id))),
            'image' => $image ?: get_template_directory_uri().'/assets/images/zole-logo.jpg',
        );
        $i++;
    }
    wp_reset_postdata();
    return $items;
}



function zolei_default_one_page_sections() {
    return array(
        array('key'=>'calendar','title'=>'Turnīri','nav'=>'Turnīri','order'=>10,'content'=>'[zolei_calendar]','shortcode'=>'','pdf_type'=>''),
        array('key'=>'rules','title'=>'Zoles noteikumi','nav'=>'Noteikumi','order'=>20,'content'=>'Klasiskās zoles noteikumi un turnīru informācija.','shortcode'=>'[zolei_results type="nolikums"]','pdf_type'=>'nolikums'),
        array('key'=>'results','title'=>'Rezultāti','nav'=>'Rezultāti','order'=>30,'content'=>'Rezultātu PDF faili no esošās Zolei.lv lapas.','shortcode'=>'[zolei_results type="rezultati"]','pdf_type'=>'rezultati'),
        array('key'=>'ratings','title'=>'Reitingi','nav'=>'Reitingi','order'=>40,'content'=>'Spēlētāju reitingi un meistaru/lielmeistaru materiāli.','shortcode'=>'[zolei_results type="reitings"]','pdf_type'=>'reitings'),
        array('key'=>'protocols','title'=>'Protokoli','nav'=>'Protokoli','order'=>50,'content'=>'Sacensību protokolu faili.','shortcode'=>'[zolei_results type="protokoli"]','pdf_type'=>'protokoli'),
        array('key'=>'archive','title'=>'Arhīvs','nav'=>'Arhīvs','order'=>60,'content'=>'Vēsturiskie dokumenti un arhīva materiāli.','shortcode'=>'[zolei_results type="arhivs"]','pdf_type'=>'arhivs'),
        array('key'=>'gallery','title'=>'Galerija','nav'=>'Galerija','order'=>70,'content'=>'Foto mirkļi no Zolītes turnīriem.','shortcode'=>'','pdf_type'=>''),
        array('key'=>'contact','title'=>'Kontakti','nav'=>'Kontakti','order'=>80,'content'=>'Sazinieties ar Latvijas Zolītes federāciju: info@zolei.lv','shortcode'=>'','pdf_type'=>''),
    );
}


function zolei_section_label($key, $field, $fallback = '') {
    $map = array(
        'calendar'=>array('title'=>array('Turnīri','Tournaments'), 'nav'=>array('Turnīri','Tournaments')),
        'rules'=>array('title'=>array('Zoles noteikumi','Zolīte rules'), 'nav'=>array('Noteikumi','Rules')),
        'results'=>array('title'=>array('Rezultāti','Results'), 'nav'=>array('Rezultāti','Results')),
        'ratings'=>array('title'=>array('Reitingi','Ratings'), 'nav'=>array('Reitingi','Ratings')),
        'protocols'=>array('title'=>array('Protokoli','Protocols'), 'nav'=>array('Protokoli','Protocols')),
        'archive'=>array('title'=>array('Arhīvs','Archive'), 'nav'=>array('Arhīvs','Archive')),
        'gallery'=>array('title'=>array('Galerija','Gallery'), 'nav'=>array('Galerija','Gallery')),
        'contact'=>array('title'=>array('Kontakti','Contact'), 'nav'=>array('Kontakti','Contact')),
    );
    if (isset($map[$key][$field])) { return zolei_lang_is_en() ? $map[$key][$field][1] : $map[$key][$field][0]; }
    return $fallback;
}

function zolei_translate_default_section_html($key, $html) {
    if (!zolei_lang_is_en()) { return $html; }
    $map = array(
        'calendar' => array('' => 'The tournament calendar is shown as a compact, easy-to-read monthly block.'),
        'rules' => array('Klasiskās zoles noteikumi un turnīru informācija.' => 'Classic Zolīte rules and tournament information.'),
        'results' => array('Rezultātu PDF faili no esošās Zolei.lv lapas.' => 'Result PDF files from the existing Zolei.lv website.'),
        'ratings' => array('Spēlētāju reitingi un meistaru/lielmeistaru materiāli.' => 'Player ratings and master/grandmaster materials.'),
        'protocols' => array('Sacensību protokolu faili.' => 'Competition protocol files.'),
        'archive' => array('Vēsturiskie dokumenti un arhīva materiāli.' => 'Historic documents and archive materials.'),
        'gallery' => array('Foto mirkļi no Zolītes turnīriem.' => 'Photo moments from Zolīte tournaments.'),
        'contact' => array('Sazinieties ar Latvijas Zolītes federāciju: info@zolei.lv' => 'Contact the Latvian Zolīte Federation: info@zolei.lv'),
    );
    if (!isset($map[$key])) { return $html; }
    return strtr($html, $map[$key]);
}

function zolei_get_one_page_sections() {
    $q = new WP_Query(array('post_type'=>'zolei_section','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>array('menu_order'=>'ASC','date'=>'ASC')));
    $items = array();
    if ($q->have_posts()) {
        while($q->have_posts()) { $q->the_post();
            $id = get_the_ID();
            if (get_post_meta($id,'_zolei_section_active',true)==='0') { continue; }
            $key = sanitize_title(get_post_meta($id,'_zolei_section_key',true) ?: get_post_field('post_name',$id));
            $shortcode = get_post_meta($id,'_zolei_section_shortcode',true);
            $html = apply_filters('the_content', get_the_content(null,false,$id));
            if ($shortcode) { $html .= do_shortcode($shortcode); }
            $items[] = array('id'=>$key,'title'=>zolei_section_label($key,'title',get_the_title()),'nav'=>zolei_section_label($key,'nav',get_post_meta($id,'_zolei_section_nav_label',true) ?: get_the_title()),'html'=>zolei_translate_default_section_html($key,$html),'pdfType'=>get_post_meta($id,'_zolei_section_pdf_type',true));
        }
        wp_reset_postdata();
    }
    if (!$items) {
        foreach(zolei_default_one_page_sections() as $sec){
            $html = apply_filters('the_content', $sec['content']);
            if (!empty($sec['shortcode'])) { $html .= do_shortcode($sec['shortcode']); }
            $items[] = array('id'=>$sec['key'],'title'=>zolei_section_label($sec['key'],'title',$sec['title']),'nav'=>zolei_section_label($sec['key'],'nav',$sec['nav']),'html'=>zolei_translate_default_section_html($sec['key'],$html),'pdfType'=>$sec['pdf_type']);
        }
    }
    return $items;
}

function zolei_home_payload() {
    $is_en = zolei_lang_is_en();
    $months = zolei_calendar_months();
    foreach ($months as $i => $m) {
        $months[$i]['name'] = $is_en ? ($m['en'] ?? $m['lv']) : ($m['lv'] ?? $m['en']);
        $months[$i]['url'] = home_url('/'.($m['slug'] ?? '').'/');
    }
    $t = $is_en ? array(
        'kicker'=>'Latvian Zolīte Federation','heroTitle'=>'Zolīte with character.','heroText'=>'Tournament calendar, rules, results and ratings in one elegant, easy-to-use place for players and organisers.','heroSubline'=>'Mārupe — a good starting place for a favourable flight','calendarBtn'=>'View calendar','rulesBtn'=>'Classic rules','monthTabs'=>'month tabs','eventsPreview'=>'events in calendar','twoLang'=>'two languages','cardTitle'=>'The next table is waiting','cardText'=>'Find the next tournament, format, location and contact details.','openCalendar'=>'Open calendar','quickKicker'=>'Main sections','quickTitle'=>'A clear route for every player.','quickText'=>'The homepage shows the essential routes first: where to play, how the game works, where to check results and how to contact organisers.','navCalendar'=>'Calendar','navCalendarText'=>'A 12-month tab calendar with the current month opened automatically.','navRules'=>'Rules','navRulesText'=>'Classic Zolīte basics and the full rule page.','navResults'=>'Results','navResultsText'=>'PDF results, regulations, ratings and archive.','navContact'=>'Contact','navContactText'=>'Address, email and tournament submission call-to-action.','calendarKicker'=>'Tournament calendar','calendarTitle'=>'12 months, easy to browse.','calendarText'=>'The tournament calendar is shown as a compact, easy-to-read monthly block. The current month opens automatically and content remains editable in admin.','fullCalendar'=>'Full calendar','monthSource'=>'Events imported from the current Zolei.lv month pages.','monthPage'=>'Month page','rulesKicker'=>'Classic Zolīte','rulesTitle'=>'Rules without confusion.','rulesText'=>'A short rules block helps new players understand the game quickly and gives experienced players an easy reference.','rule1'=>'The game uses 26 cards.','rule2'=>'Trumps: queens, jacks and then diamonds.','rule3'=>'The declarer needs at least 61 points.','rule4'=>'Strategy, memory and calm play win.','newsKicker'=>'News','newsTitle'=>'Important notices stay visible.','newsText'=>'Championship stages, calendar changes, ethics code and board information should be easy to find.','notice1Title'=>'Championship stages','notice1Text'=>'Use clear cards for updates and link them to full information pages.','notice2Title'=>'Ethics code','notice2Text'=>'Fair play, respectful behaviour and clear tournament principles.','galleryKicker'=>'Photo gallery','galleryTitle'=>'Moments from Latvian Zolīte tables.','galleryText'=>'A homepage gallery slider and expandable photo wall use existing Zolei.lv gallery images, bringing the atmosphere of Latvian tournament tables into the new site.','gallerySlideTitle'=>'Tournament atmosphere','gallerySlideText'=>'Players, winners and moments from the Zolīte community.','contactKicker'=>'Contact','contactTitle'=>'Add a tournament or update information.','contactText'=>'Send date, location, start time, format and contact person so players can find everything in time.','newsSliderKicker'=>'News','newsSliderTitle'=>'Zolei.lv news and stories.','newsSliderText'=>'Three latest posts with images, short descriptions and a lightbox reading view.','blogLightboxHint'=>'Click a card to read the story','blogImageLabel'=>'Image','blogClose'=>'Close','shareFacebook'=>'Facebook','shareLinkedin'=>'LinkedIn','shareWhatsapp'=>'WhatsApp','shareX'=>'X','copyLink'=>'Copy link','copiedLink'=>'Copied','copyPrompt'=>'Copy link:','readMore'=>'Read more','allNews'=>'All news','contactBtn'=>'Contact us','galleryLoadMore'=>'Load more photos','galleryOpen'=>'Open photo','galleryClose'=>'Close','previous'=>'Previous','next'=>'Next','galleryCount'=>'photos from the live Zolei.lv gallery','formTitle'=>'Send information','formIntro'=>'Submit a tournament, correction or question. The same form works in English and Latvian.','formName'=>'Name','formEmail'=>'Email','formPhone'=>'Phone','formSubject'=>'Subject','formMessage'=>'Message','formSubmit'=>'Send message','formSuccess'=>'Thank you! Your message was sent.','formRequired'=>'Required field','partnerKicker'=>'Partner','partnerTitle'=>'Play Zolīte online','partnerText'=>'A partner link for players who want to practise and play outside tournaments too.','partnerButton'=>'Open partner page','infoKicker'=>'Information','infoTitle'=>'Board, ethics and regulations in one view.','infoText'=>'Important legacy website sections are available as quick modal windows, so visitors can stay on the homepage.','boardTitle'=>'Board and contacts','boardText'=>'Latvian Zolīte Federation board and contact information.','boardHtml'=>'<p><strong>Uldis Vītols:</strong> info@zolei.lv</p><p><strong>Elgars Sapats:</strong> info@zolei.lv, phone 22315099</p>','ethicsTitle'=>'Ethics code','ethicsText'=>'Fair play, respect for opponents and clear tournament behaviour.','ethicsHtml'=>'<p><strong>The Zolīte ethics code</strong> defines basic principles for player conduct.</p><ol><li>Play according to the rules.</li><li>Treat other players with respect.</li><li>Do not cheat or take unfair advantage of others.</li><li>Control yourself and respect tournament organisers.</li><li>Resolve disputes calmly and correctly.</li></ol>','regulationsTitle'=>'Regulations','regulationsText'=>'Tournament regulations and game documents in the PDF section.','regulationsHtml'=>'<p>The regulations section stores tournament rules, posters and competition-related PDF documents.</p><p>PDF files remain the same for Latvian and English; only titles and descriptions are translated.</p>','openModal'=>'Open','closeModal'=>'Close','archiveSidebarTitle'=>'Archive section','archiveSidebarText'=>'The archive keeps historic tournament results, cups, ratings and other documents from the existing Zolei.lv website. Search by year or title while the folder structure stays like the old wp-content/uploads/arhivs directory.','archiveSidebarPoint1'=>'Old folders and PDF paths are preserved.','archiveSidebarPoint2'=>'Search by year, tournament or file name.','archiveSidebarPoint3'=>'New archive PDFs can be added in the admin PDF manager.','formHcaptchaMissing'=>'Configure hCaptcha site key in admin settings.'
    ) : array(
        'kicker'=>'Latvijas Zolītes federācija','heroTitle'=>'Zolīte ar raksturu.','heroText'=>'Turnīru kalendārs, noteikumi, rezultāti un reitingi vienā elegantā, viegli lietojamā vietā spēlētājiem un organizatoriem.','heroSubline'=>'Mārupe. Labvēlīgam lidojumam teicama starta vieta','calendarBtn'=>'Skatīt turnīrus','rulesBtn'=>'Klasiskās zoles noteikumi','monthTabs'=>'mēnešu tabs','eventsPreview'=>'notikumi kalendārā','twoLang'=>'divas valodas','cardTitle'=>'Nākamais galds gaida','cardText'=>'Atrodi tuvāko turnīru, formātu, vietu un kontaktinformāciju.','openCalendar'=>'Atvērt kalendāru','quickKicker'=>'Galvenās sadaļas','quickTitle'=>'Skaidrs ceļš katram spēlētājam.','quickText'=>'','navCalendar'=>'Turnīri','navCalendarText'=>'12 mēnešu tab kalendārs ar automātiski atvērtu pašreizējo mēnesi.','navRules'=>'Noteikumi','navRulesText'=>'Klasiskās zoles pamati un pilnā noteikumu lapa.','navResults'=>'Rezultāti','navResultsText'=>'PDF rezultāti, nolikumi, reitingi un arhīvs.','navContact'=>'Saziņa','navContactText'=>'Adrese, epasts un skaidrs turnīra pieteikšanas aicinājums.','calendarKicker'=>'Turnīru kalendārs','calendarTitle'=>'12 mēneši, viegli pārskatāmi.','calendarText'=>'WordPress versijā pašreizējais mēnesis tiek atvērts servera pusē ar current_time(\'n\'), bet mēnešu saturs paliek rediģējams administrācijā.','fullCalendar'=>'Pilns kalendārs','monthSource'=>'Notikumi importēti no esošajām Zolei.lv mēnešu lapām.','monthPage'=>'Mēneša lapa','rulesKicker'=>'Klasiskā zole','rulesTitle'=>'Noteikumi bez sarežģījumiem.','rulesText'=>'Īsais noteikumu bloks palīdz jaunam spēlētājam ātri saprast spēles pamatu, bet pieredzējušam — pārbaudīt detaļas.','rule1'=>'Spēlē izmanto 26 kārtis.','rule2'=>'Trumpji: dāmas, kalpi, pēc tam kāravi.','rule3'=>'Lielajam vajag vismaz 61 aci.','rule4'=>'Uzvar stratēģija, atmiņa un mierīga spēle.','newsKicker'=>'Aktualitātes','newsTitle'=>'Svarīgi paziņojumi paliek redzami.','newsText'=>'Čempionātu posmi, kalendāra izmaiņas, ētikas kodekss un valdes informācija ir jāatrod ātri.','notice1Title'=>'Latvijas čempionāta posmi','notice1Text'=>'Aktualitāšu kartītes palīdz izcelt būtisko un aizvest uz pilnu informāciju.','notice2Title'=>'Ētikas kodekss','notice2Text'=>'Godīga spēle, cieņpilna uzvedība un skaidri turnīra principi.','galleryKicker'=>'Foto galerija','galleryTitle'=>'Spēles mirkļi no Latvijas zoles galdiem.','galleryText'=>'Sākumlapas galerijas slīdnis un paplašināmā foto siena izmanto esošās Zolei.lv galerijas bildes, lai jaunajā lapā ienestu Latvijas turnīru atmosfēru.','gallerySlideTitle'=>'Turnīru atmosfēra','gallerySlideText'=>'Spēlētāji, uzvarētāji un zoles kopienas mirkļi.','contactKicker'=>'Kontakti','contactTitle'=>'Pievieno turnīru vai precizē informāciju.','contactText'=>'Nosūti datumu, vietu, sākuma laiku, formātu un kontaktpersonu — spēlētājiem viss būs viegli atrodams.','newsSliderKicker'=>'Jaunumi','newsSliderTitle'=>'Zolei.lv jaunumi un stāsti.','newsSliderText'=>'Trīs jaunākie ieraksti ar attēliem, īsu aprakstu un ērtu lightbox lasīšanas skatu.','blogLightboxHint'=>'Nospied uz kartītes, lai lasītu','blogImageLabel'=>'Attēls','blogClose'=>'Aizvērt','shareFacebook'=>'Facebook','shareLinkedin'=>'LinkedIn','shareWhatsapp'=>'WhatsApp','shareX'=>'X','copyLink'=>'Kopēt saiti','copiedLink'=>'Nokopēts','copyPrompt'=>'Kopēt saiti:','readMore'=>'Lasīt vairāk','allNews'=>'Visi jaunumi','contactBtn'=>'Sazināties','galleryLoadMore'=>'Ielādēt vairāk foto','galleryOpen'=>'Atvērt foto','galleryClose'=>'Aizvērt','previous'=>'Iepriekšējais','next'=>'Nākamais','galleryCount'=>'foto no esošās Zolei.lv galerijas','formTitle'=>'Nosūtīt informāciju','formIntro'=>'Piesaki turnīru, precizējumu vai jautājumu. Forma strādā latviski un angliski.','formName'=>'Vārds','formEmail'=>'E-pasts','formPhone'=>'Tālrunis','formSubject'=>'Temats','formMessage'=>'Ziņa','formSubmit'=>'Nosūtīt ziņu','formSuccess'=>'Paldies! Ziņa nosūtīta.','formRequired'=>'Obligāts lauks','partnerKicker'=>'Partneris','partnerTitle'=>'Uzspēlē Zoli tiešsaistē','partnerText'=>'Partnera saite spēlētājiem, kuri vēlas trenēties un spēlēt arī ārpus turnīriem.','partnerButton'=>'Atvērt partnera lapu','infoKicker'=>'Informācija','infoTitle'=>'Valde, ētika un nolikumi vienā skatā.','infoText'=>'','boardTitle'=>'Valde un kontakti','boardText'=>'Latvijas Zolītes federācijas kontaktinformācija un valdes saziņa.','boardHtml'=>'<p><strong>Uldis Vītols:</strong> info@zolei.lv</p><p><strong>Elgars Sapats:</strong> info@zolei.lv, tālr. 22315099</p>','ethicsTitle'=>'Ētikas kodekss','ethicsText'=>'Godīga spēle, cieņa pret pretinieku un skaidra uzvedība turnīros.','ethicsHtml'=>'<p><strong>Zolītes spēles ētikas kodekss</strong> nosaka spēlētāju uzvedības pamatprincipus.</p><ol><li>Spēlē atbilstoši noteikumiem.</li><li>Izturies pret citiem spēlētājiem ar cieņu.</li><li>Nemānies un negodīgi neizmanto citus spēlētājus.</li><li>Kontrolē sevi un cieni turnīra organizatorus.</li><li>Strīdus risini mierīgi un korekti.</li></ol>','regulationsTitle'=>'Nolikumi','regulationsText'=>'Turnīru nolikumi un spēles dokumenti PDF sadaļā.','regulationsHtml'=>'<p>Nolikumu sadaļā tiek glabāti turnīru noteikumi, afišas un ar sacensībām saistītie PDF dokumenti.</p><p>PDF faili paliek vieni un tie paši latviešu un angļu skatam; tulkoti ir tikai virsraksti un apraksti.</p>','openModal'=>'Atvērt','closeModal'=>'Aizvērt','archiveSidebarTitle'=>'Arhīva sadaļa','archiveSidebarText'=>'Arhīvā saglabāti vēsturiskie turnīru rezultāti, kausi, reitingi un citi dokumenti no esošās Zolei.lv mājaslapas. Meklē pēc gada vai nosaukuma, bet mapju struktūra paliek kā vecajā wp-content/uploads/arhivs direktorijā.','archiveSidebarPoint1'=>'Saglabātas vecās mapes un PDF ceļi.','archiveSidebarPoint2'=>'Var meklēt pēc gada, turnīra vai faila nosaukuma.','archiveSidebarPoint3'=>'Jauni arhīva PDF pievienojami administrācijas PDF pārvaldniekā.','formHcaptchaMissing'=>'hCaptcha atslēgu var iestatīt administrācijā.'
    );
    return array(
        'lang' => $is_en ? 'en':'lv',
        'logo' => get_template_directory_uri().'/assets/images/zole-logo.jpg',
        'partnerBanner' => get_template_directory_uri().'/assets/images/zole.gif',
        'months' => $months,
        'gallery' => zolei_gallery_items(),
        'news' => zolei_home_news(3),
        'eventCount' => array_sum(array_map(function($m){ return isset($m['events']) && is_array($m['events']) ? count($m['events']) : 0; }, $months)),
        'currentMonth' => intval(current_time('n')),
        'labels' => $t,
        'settings' => zolei_settings(),
        'sections' => zolei_get_one_page_sections(),
        'urls' => array('calendar'=>home_url('/#calendar'),'rules'=>home_url('/#rules'),'results'=>home_url('/#results'),'contact'=>home_url('/#contact'),'fullCalendar'=>home_url('/#calendar'),'news'=>home_url('/#news'),'partner'=>'https://goo.gl/g7jJpm','board'=>home_url('/valde-un-kontakti/'),'ethics'=>home_url('/etikas-kodekss/'),'regulations'=>home_url('/nolikumi/')),
    );
}
function zolei_rest_home() { return rest_ensure_response(zolei_home_payload()); }
add_action('rest_api_init', function() { register_rest_route('zolei/v1','/home',array('methods'=>'GET','callback'=>'zolei_rest_home','permission_callback'=>'__return_true')); });

function zolei_bbuilder_home_content($lang='lv') {
    $is_en = $lang === 'en';
    $title = $is_en ? 'Zolīte with character.' : 'Zolīte ar raksturu.';
    $text = $is_en ? 'Headless React homepage renders the live design, while this page content remains editable with WP BBuilder blocks.' : 'Headless React sākumlapa attēlo dzīvo dizainu, bet lapas saturs paliek rediģējams ar WP BBuilder blokiem.';
    $button = $is_en ? 'View calendar' : 'Skatīt turnīrus';
    $fields = $is_en ? array(
        array('type'=>'text','name'=>'name','label'=>'Name','required'=>true,'width'=>6,'placeholder'=>'Your name'),
        array('type'=>'email','name'=>'email','label'=>'Email','required'=>true,'width'=>6,'placeholder'=>'you@email.com'),
        array('type'=>'textarea','name'=>'message','label'=>'Message','required'=>true,'width'=>12,'placeholder'=>'Tournament details or question'),
    ) : array(
        array('type'=>'text','name'=>'name','label'=>'Vārds','required'=>true,'width'=>6,'placeholder'=>'Jūsu vārds'),
        array('type'=>'email','name'=>'email','label'=>'E-pasts','required'=>true,'width'=>6,'placeholder'=>'jusu@epasts.lv'),
        array('type'=>'textarea','name'=>'message','label'=>'Ziņa','required'=>true,'width'=>12,'placeholder'=>'Turnīra informācija vai jautājums'),
    );
    $form_attrs = array('title'=>$is_en?'Contact the federation':'Sazināties ar federāciju','buttonText'=>$is_en?'Send':'Nosūtīt','recipient'=>'info@zolei.lv','fieldsJson'=>wp_json_encode($fields),'formClass'=>'zole-bbuilder-form');
    return '<!-- wp:wpbb/hero '.wp_json_encode(array('title'=>$title,'text'=>$text,'buttonText'=>$button,'buttonUrl'=>'#calendar','theme'=>'dark','titleSize'=>'display-3')).' /-->' . "
" .
           '<!-- wp:wpbb/tabs {"className":"zole-bbuilder-month-tabs"} -->' . "
" . '<!-- /wp:wpbb/tabs -->' . "
" .
           '<!-- wp:wpbb/cta-section '.wp_json_encode(array('title'=>$is_en?'Add a tournament':'Pievieno turnīru','text'=>$is_en?'Send details so players can find the event in time.':'Nosūti informāciju, lai spēlētāji turnīru atrod laikus.','buttonText'=>$is_en?'Contact':'Sazināties','buttonUrl'=>'/kontakti/')).' /-->' . "
" .
           '<!-- wp:wpbb/dynamic-form '.wp_json_encode($form_attrs).' /-->';
}

require_once get_template_directory() . '/inc/content-types.php';
require_once get_template_directory() . '/inc/pdf-compression.php';
require_once get_template_directory() . '/inc/ajax-search.php';
require_once get_template_directory() . '/inc/theme-admin.php';
require_once get_template_directory() . '/inc/demo-importer.php';
