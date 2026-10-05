<?php
if (!defined('ABSPATH')) { exit; }

function zolei_has_major_seo_plugin() {
    return defined('AIOSEO_VERSION') || defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION');
}

function zolei_output_site_integrations_head() {
    $gtm = strtoupper(trim((string) get_option('zolei_gtm_container_id','')));
    $ga4 = strtoupper(trim((string) get_option('zolei_ga4_measurement_id','')));
    $verify = trim((string) get_option('zolei_google_site_verification',''));

    if ($verify !== '') {
        echo '<meta name="google-site-verification" content="' . esc_attr($verify) . '">' . "\n";
    }
    if (preg_match('/^GTM-[A-Z0-9]{4,20}$/', $gtm)) {
        echo "<!-- Google Tag Manager -->\n";
        echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc_js($gtm) . "');</script>\n";
        echo "<!-- End Google Tag Manager -->\n";
    } elseif (preg_match('/^G-[A-Z0-9]{6,20}$/', $ga4)) {
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($ga4) . '"></script>' . "\n";
        echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . esc_js($ga4) . "');</script>\n";
    }
}
add_action('wp_head','zolei_output_site_integrations_head',1);

function zolei_output_gtm_body() {
    $gtm = strtoupper(trim((string) get_option('zolei_gtm_container_id','')));
    if (!preg_match('/^GTM-[A-Z0-9]{4,20}$/', $gtm)) { return; }
    echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr($gtm) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
}
add_action('wp_body_open','zolei_output_gtm_body',1);

function zolei_output_basic_seo() {
    if (zolei_has_major_seo_plugin()) { return; }
    $title = wp_get_document_title();
    $description = trim((string) get_option('zolei_meta_description','Latvijas Zolītes federācija — turnīru kalendārs, noteikumi, rezultāti, reitingi un federācijas informācija.'));
    if (is_singular()) {
        $excerpt = trim(wp_strip_all_tags((string) get_the_excerpt()));
        if ($excerpt !== '') { $description = wp_trim_words($excerpt, 30, ''); }
    }
    $url = is_singular() ? get_permalink() : home_url('/');
    $logo = function_exists('zolei_asset_image_url') ? zolei_asset_image_url('zole-logo.jpg', false) : get_template_directory_uri() . '/assets/images/zole-logo.jpg';
    $social_image = function_exists('zolei_asset_image_url') ? zolei_asset_image_url('news-zolei-home.jpg', false) : get_template_directory_uri() . '/assets/images/news-zolei-home.jpg';

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($social_image) . '">' . "\n";
    echo '<meta property="og:image:width" content="1280">' . "\n";
    echo '<meta property="og:image:height" content="720">' . "\n";
    echo '<meta property="og:image:alt" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($social_image) . '">' . "\n";

    $schema = array(
        '@context'=>'https://schema.org',
        '@graph'=>array(
            array(
                '@type'=>'Organization',
                '@id'=>home_url('/#organization'),
                'name'=>'Latvijas Zolītes federācija',
                'url'=>home_url('/'),
                'logo'=>array('@type'=>'ImageObject','url'=>$logo,'width'=>536,'height'=>536),
                'email'=>get_option('zolei_contact_email','info@zolei.lv')
            ),
            array(
                '@type'=>'WebSite',
                '@id'=>home_url('/#website'),
                'url'=>home_url('/'),
                'name'=>get_bloginfo('name') ?: 'Zolei.lv',
                'publisher'=>array('@id'=>home_url('/#organization')),
                'inLanguage'=>function_exists('zolei_lang_is_en') && zolei_lang_is_en() ? 'en-GB' : 'lv-LV'
            )
        )
    );
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head','zolei_output_basic_seo',5);
