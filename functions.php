<?php
if (!defined('ABSPATH')) { exit; }

define('ZOLEI_THEME_VERSION', '1.0.7');

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
    wp_enqueue_style('zolei-fonts','https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap&subset=latin-ext',array(),null);
    wp_enqueue_style('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',array(),'5.3.3');
    wp_enqueue_style('zolei-theme',get_template_directory_uri().'/assets/css/theme.css',array('bootstrap','zolei-fonts'),filemtime(get_template_directory().'/assets/css/theme.css'));
    wp_enqueue_script('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',array(),'5.3.3',true);
    wp_enqueue_script('zolei-theme',get_template_directory_uri().'/assets/js/theme.js',array(),filemtime(get_template_directory().'/assets/js/theme.js'),true);
    wp_localize_script('zolei-theme', 'zoleiSearch', array('ajaxUrl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('zolei_search_nonce'),'lang'=>zolei_lang(),'labels'=>array('placeholder'=>zolei_i18n('Meklēt turnīrus, rezultātus, lapas...', 'Search tournaments, results, pages...'),'noResults'=>zolei_i18n('Nekas netika atrasts.', 'No results found.'),'viewAll'=>zolei_i18n('Skatīt visus rezultātus', 'View all results'),'searching'=>zolei_i18n('Meklē...', 'Searching...'))));
    if (is_front_page()) {
        wp_enqueue_script('zolei-headless-home',get_template_directory_uri().'/assets/js/headless-home.js',array('wp-element','bootstrap'),filemtime(get_template_directory().'/assets/js/headless-home.js'),true);
    }
}
add_action('wp_enqueue_scripts','zolei_scripts');

function zolei_lang_is_en() { return function_exists('pll_current_language') && pll_current_language('slug') === 'en'; }
function zolei_lang() { return function_exists('pll_current_language') ? pll_current_language('slug') : 'lv'; }
function zolei_i18n($lv,$en='') { return zolei_lang_is_en() ? ($en !== '' ? $en : $lv) : $lv; }

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
<li><a href="<?php echo esc_url(zolei_page_url('zoles-noteikumi','rules')); ?>"><?php echo esc_html(zolei_i18n('Noteikumi','Rules')); ?></a></li>
<li><a href="<?php echo esc_url(zolei_page_url('rezultati','results')); ?>"><?php echo esc_html(zolei_i18n('Rezultāti','Results')); ?></a></li>
<li><a href="<?php echo esc_url(zolei_page_url('galerija','gallery')); ?>"><?php echo esc_html(zolei_i18n('Galerija','Gallery')); ?></a></li>
<li><a href="<?php echo esc_url(zolei_page_url('kontakti','contact')); ?>"><?php echo esc_html(zolei_i18n('Kontakti','Contact')); ?></a></li>
</ul>
<?php }

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
        $source = !empty($entry['source']) ? esc_url_raw($entry['source']) : $full;
        return array('thumb' => $thumb, 'full' => $full ?: $thumb, 'source' => $source ?: $full ?: $thumb);
    }
    $full = zolei_gallery_original_url($entry);
    return array('thumb' => $full, 'full' => $full, 'source' => $full);
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
    );
}

function zolei_home_news($limit = 5) {
    $args = array('post_type'=>'post','posts_per_page'=>$limit,'post_status'=>'publish','ignore_sticky_posts'=>true);
    if (function_exists('pll_current_language')) { $args['lang'] = zolei_lang(); }
    $q = new WP_Query($args);
    $items = array();
    $fallbacks = zolei_gallery_images();
    $i = 0;
    while ($q->have_posts()) { $q->the_post();
        $title = get_the_title();
        $fallback = !empty($fallbacks) ? $fallbacks[$i % count($fallbacks)] : get_template_directory_uri().'/assets/images/zole-logo.jpg';
        if (stripos($title, 'Elites') !== false || stripos($title, 'Elite') !== false) {
            $fallback = 'https://zolei.lv/wp-content/gallery/zolei_2024/DSC01790.JPG';
        }
        $items[] = array(
            'title' => $title,
            'url' => get_permalink(),
            'date' => get_the_date('d.m.Y'),
            'excerpt' => wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 22),
            'image' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: $fallback,
        );
        $i++;
    }
    wp_reset_postdata();
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
        'kicker'=>'Latvian Zolīte Federation','heroTitle'=>'Zolīte with character.','heroText'=>'Tournament calendar, rules, results and ratings in one elegant, easy-to-use place for players and organisers.','heroSubline'=>'Mārupe — a good starting place for a favourable flight','calendarBtn'=>'View calendar','rulesBtn'=>'Classic rules','monthTabs'=>'month tabs','eventsPreview'=>'events in calendar','twoLang'=>'two languages','cardTitle'=>'The next table is waiting','cardText'=>'Find the next tournament, format, location and contact details.','openCalendar'=>'Open calendar','quickKicker'=>'Main sections','quickTitle'=>'A clear route for every player.','quickText'=>'The homepage shows the essential routes first: where to play, how the game works, where to check results and how to contact organisers.','navCalendar'=>'Calendar','navCalendarText'=>'A 12-month tab calendar with the current month opened automatically.','navRules'=>'Rules','navRulesText'=>'Classic Zolīte basics and the full rule page.','navResults'=>'Results','navResultsText'=>'PDF results, regulations, ratings and archive.','navContact'=>'Contact','navContactText'=>'Address, email and tournament submission call-to-action.','calendarKicker'=>'Tournament calendar','calendarTitle'=>'12 months, easy to browse.','calendarText'=>'The WordPress version opens the current month server-side with current_time(\'n\') and keeps all month content editable in admin.','fullCalendar'=>'Full calendar','monthSource'=>'Events imported from the current Zolei.lv month pages.','monthPage'=>'Month page','rulesKicker'=>'Classic Zolīte','rulesTitle'=>'Rules without confusion.','rulesText'=>'A short rules block helps new players understand the game quickly and gives experienced players an easy reference.','rule1'=>'The game uses 26 cards.','rule2'=>'Trumps: queens, jacks and then diamonds.','rule3'=>'The declarer needs at least 61 points.','rule4'=>'Strategy, memory and calm play win.','newsKicker'=>'News','newsTitle'=>'Important notices stay visible.','newsText'=>'Championship stages, calendar changes, ethics code and board information should be easy to find.','notice1Title'=>'Championship stages','notice1Text'=>'Use clear cards for updates and link them to full information pages.','notice2Title'=>'Ethics code','notice2Text'=>'Fair play, respectful behaviour and clear tournament principles.','galleryKicker'=>'Photo gallery','galleryTitle'=>'Moments from Latvian Zolīte tables.','galleryText'=>'A homepage gallery slider and expandable photo wall use existing Zolei.lv gallery images, bringing the atmosphere of Latvian tournament tables into the new site.','gallerySlideTitle'=>'Tournament atmosphere','gallerySlideText'=>'Players, winners and moments from the Zolīte community.','contactKicker'=>'Contact','contactTitle'=>'Add a tournament or update information.','contactText'=>'Send date, location, start time, format and contact person so players can find everything in time.','newsSliderKicker'=>'Latest news','newsSliderTitle'=>'News and federation updates.','newsSliderText'=>'Read announcements, tournament changes and useful federation information.','readMore'=>'Read more','allNews'=>'All news','contactBtn'=>'Contact us','galleryLoadMore'=>'Load more photos','galleryOpen'=>'Open photo','galleryClose'=>'Close','galleryCount'=>'photos from the live Zolei.lv gallery'
    ) : array(
        'kicker'=>'Latvijas Zolītes federācija','heroTitle'=>'Zolīte ar raksturu.','heroText'=>'Turnīru kalendārs, noteikumi, rezultāti un reitingi vienā elegantā, viegli lietojamā vietā spēlētājiem un organizatoriem.','heroSubline'=>'Mārupe. Labvēlīgam lidojumam teicama starta vieta','calendarBtn'=>'Skatīt turnīrus','rulesBtn'=>'Klasiskās zoles noteikumi','monthTabs'=>'mēnešu tabs','eventsPreview'=>'notikumi kalendārā','twoLang'=>'divas valodas','cardTitle'=>'Nākamais galds gaida','cardText'=>'Atrodi tuvāko turnīru, formātu, vietu un kontaktinformāciju.','openCalendar'=>'Atvērt kalendāru','quickKicker'=>'Galvenās sadaļas','quickTitle'=>'Skaidrs ceļš katram spēlētājam.','quickText'=>'Sākumlapa vispirms parāda būtiskāko: kur spēlēt, kādi ir noteikumi, kur pārbaudīt rezultātus un kā sazināties ar organizatoriem.','navCalendar'=>'Turnīri','navCalendarText'=>'12 mēnešu tab kalendārs ar automātiski atvērtu pašreizējo mēnesi.','navRules'=>'Noteikumi','navRulesText'=>'Klasiskās zoles pamati un pilnā noteikumu lapa.','navResults'=>'Rezultāti','navResultsText'=>'PDF rezultāti, nolikumi, reitingi un arhīvs.','navContact'=>'Saziņa','navContactText'=>'Adrese, epasts un skaidrs turnīra pieteikšanas aicinājums.','calendarKicker'=>'Turnīru kalendārs','calendarTitle'=>'12 mēneši, viegli pārskatāmi.','calendarText'=>'WordPress versijā pašreizējais mēnesis tiek atvērts servera pusē ar current_time(\'n\'), bet mēnešu saturs paliek rediģējams administrācijā.','fullCalendar'=>'Pilns kalendārs','monthSource'=>'Notikumi importēti no esošajām Zolei.lv mēnešu lapām.','monthPage'=>'Mēneša lapa','rulesKicker'=>'Klasiskā zole','rulesTitle'=>'Noteikumi bez sarežģījumiem.','rulesText'=>'Īsais noteikumu bloks palīdz jaunam spēlētājam ātri saprast spēles pamatu, bet pieredzējušam — pārbaudīt detaļas.','rule1'=>'Spēlē izmanto 26 kārtis.','rule2'=>'Trumpji: dāmas, kalpi, pēc tam kāravi.','rule3'=>'Lielajam vajag vismaz 61 aci.','rule4'=>'Uzvar stratēģija, atmiņa un mierīga spēle.','newsKicker'=>'Aktualitātes','newsTitle'=>'Svarīgi paziņojumi paliek redzami.','newsText'=>'Čempionātu posmi, kalendāra izmaiņas, ētikas kodekss un valdes informācija ir jāatrod ātri.','notice1Title'=>'Latvijas čempionāta posmi','notice1Text'=>'Aktualitāšu kartītes palīdz izcelt būtisko un aizvest uz pilnu informāciju.','notice2Title'=>'Ētikas kodekss','notice2Text'=>'Godīga spēle, cieņpilna uzvedība un skaidri turnīra principi.','galleryKicker'=>'Foto galerija','galleryTitle'=>'Spēles mirkļi no Latvijas zoles galdiem.','galleryText'=>'Sākumlapas galerijas slīdnis un paplašināmā foto siena izmanto esošās Zolei.lv galerijas bildes, lai jaunajā lapā ienestu Latvijas turnīru atmosfēru.','gallerySlideTitle'=>'Turnīru atmosfēra','gallerySlideText'=>'Spēlētāji, uzvarētāji un zoles kopienas mirkļi.','contactKicker'=>'Kontakti','contactTitle'=>'Pievieno turnīru vai precizē informāciju.','contactText'=>'Nosūti datumu, vietu, sākuma laiku, formātu un kontaktpersonu — spēlētājiem viss būs viegli atrodams.','newsSliderKicker'=>'Jaunumi','newsSliderTitle'=>'Aktualitātes un federācijas ziņas.','newsSliderText'=>'Lasiet paziņojumus, turnīru izmaiņas un noderīgu federācijas informāciju.','readMore'=>'Lasīt vairāk','allNews'=>'Visi jaunumi','contactBtn'=>'Sazināties','galleryLoadMore'=>'Ielādēt vairāk foto','galleryOpen'=>'Atvērt foto','galleryClose'=>'Aizvērt','galleryCount'=>'foto no esošās Zolei.lv galerijas'
    );
    return array(
        'lang' => $is_en ? 'en':'lv',
        'logo' => get_template_directory_uri().'/assets/images/zole-logo.jpg',
        'months' => $months,
        'gallery' => zolei_gallery_items(),
        'news' => zolei_home_news(5),
        'eventCount' => array_sum(array_map(function($m){ return isset($m['events']) && is_array($m['events']) ? count($m['events']) : 0; }, $months)),
        'currentMonth' => intval(current_time('n')),
        'labels' => $t,
        'settings' => zolei_settings(),
        'urls' => array('calendar'=>home_url('/#calendar'),'rules'=>zolei_page_url('zoles-noteikumi','rules'),'results'=>zolei_page_url('rezultati','results'),'contact'=>zolei_page_url('kontakti','contact'),'fullCalendar'=>zolei_page_url('turniri','calendar'),'news'=>get_permalink(get_option('page_for_posts')) ?: home_url('/jaunumi/')),
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
require_once get_template_directory() . '/inc/ajax-search.php';
require_once get_template_directory() . '/inc/theme-admin.php';
require_once get_template_directory() . '/inc/demo-importer.php';
