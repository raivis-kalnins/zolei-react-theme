<?php
if (!defined('ABSPATH')) { exit; }

function zolei_home_editable_fields() {
    return array(
        'Hero' => array(
            'kicker'=>array('label'=>'Eyebrow / kicker','type'=>'text'),
            'heroTitle'=>array('label'=>'Hero title','type'=>'text'),
            'heroText'=>array('label'=>'Hero intro text','type'=>'textarea'),
            'heroSubline'=>array('label'=>'Hero subline','type'=>'text'),
            'calendarBtn'=>array('label'=>'Primary button text','type'=>'text'),
            'rulesBtn'=>array('label'=>'Secondary button text','type'=>'text'),
            'cardTitle'=>array('label'=>'Hero card title','type'=>'text'),
            'cardText'=>array('label'=>'Hero card text','type'=>'textarea'),
            'openCalendar'=>array('label'=>'Hero card button text','type'=>'text'),
        ),
        'Quick links' => array(
            'quickKicker'=>array('label'=>'Section kicker','type'=>'text'),
            'quickTitle'=>array('label'=>'Section title','type'=>'text'),
            'quickText'=>array('label'=>'Section intro','type'=>'textarea'),
            'navCalendar'=>array('label'=>'Calendar card title','type'=>'text'),
            'navCalendarText'=>array('label'=>'Calendar card text','type'=>'textarea'),
            'navRules'=>array('label'=>'Rules card title','type'=>'text'),
            'navRulesText'=>array('label'=>'Rules card text','type'=>'textarea'),
            'navResults'=>array('label'=>'Results card title','type'=>'text'),
            'navResultsText'=>array('label'=>'Results card text','type'=>'textarea'),
            'navContact'=>array('label'=>'Contact card title','type'=>'text'),
            'navContactText'=>array('label'=>'Contact card text','type'=>'textarea'),
        ),
        'Tournament calendar' => array(
            'calendarKicker'=>array('label'=>'Calendar kicker','type'=>'text'),
            'calendarTitle'=>array('label'=>'Calendar title','type'=>'text'),
            'calendarText'=>array('label'=>'Calendar intro','type'=>'textarea'),
            'fullCalendar'=>array('label'=>'Calendar button text','type'=>'text'),
            'monthSource'=>array('label'=>'Month helper text','type'=>'textarea'),
            'monthPage'=>array('label'=>'Month link text','type'=>'text'),
        ),
        'Rules and notices' => array(
            'rulesKicker'=>array('label'=>'Rules kicker','type'=>'text'),
            'rulesTitle'=>array('label'=>'Rules title','type'=>'text'),
            'rulesText'=>array('label'=>'Rules intro','type'=>'textarea'),
            'rule1'=>array('label'=>'Rule point 1','type'=>'text'),
            'rule2'=>array('label'=>'Rule point 2','type'=>'text'),
            'rule3'=>array('label'=>'Rule point 3','type'=>'text'),
            'rule4'=>array('label'=>'Rule point 4','type'=>'text'),
            'fullRulesBtn'=>array('label'=>'Full rules button','type'=>'text'),
            'newsKicker'=>array('label'=>'Notice kicker','type'=>'text'),
            'newsTitle'=>array('label'=>'Notice title','type'=>'text'),
            'newsText'=>array('label'=>'Notice intro','type'=>'textarea'),
            'notice1Title'=>array('label'=>'Notice card 1 title','type'=>'text'),
            'notice1Text'=>array('label'=>'Notice card 1 text','type'=>'textarea'),
            'notice2Title'=>array('label'=>'Notice card 2 title','type'=>'text'),
            'notice2Text'=>array('label'=>'Notice card 2 text','type'=>'textarea'),
        ),
        'News and gallery' => array(
            'newsSliderKicker'=>array('label'=>'News kicker','type'=>'text'),
            'newsSliderTitle'=>array('label'=>'News title','type'=>'text'),
            'newsSliderText'=>array('label'=>'News intro','type'=>'textarea'),
            'allNews'=>array('label'=>'All news button','type'=>'text'),
            'galleryKicker'=>array('label'=>'Gallery kicker','type'=>'text'),
            'galleryTitle'=>array('label'=>'Gallery title','type'=>'text'),
            'galleryText'=>array('label'=>'Gallery intro','type'=>'textarea'),
        ),
        'Contact and form' => array(
            'contactKicker'=>array('label'=>'Contact kicker','type'=>'text'),
            'contactTitle'=>array('label'=>'Contact title','type'=>'text'),
            'contactText'=>array('label'=>'Contact intro','type'=>'textarea'),
            'contactBtn'=>array('label'=>'Contact button text','type'=>'text'),
            'formTitle'=>array('label'=>'Form title','type'=>'text'),
            'formIntro'=>array('label'=>'Form intro','type'=>'textarea'),
            'formName'=>array('label'=>'Name field label','type'=>'text'),
            'formEmail'=>array('label'=>'Email field label','type'=>'text'),
            'formPhone'=>array('label'=>'Phone field label','type'=>'text'),
            'formSubject'=>array('label'=>'Subject field label','type'=>'text'),
            'formMessage'=>array('label'=>'Message field label','type'=>'text'),
            'formSubmit'=>array('label'=>'Form submit button','type'=>'text'),
            'formSuccess'=>array('label'=>'Success message','type'=>'text'),
        ),
        'Partner and information' => array(
            'partnerKicker'=>array('label'=>'Partner kicker','type'=>'text'),
            'partnerTitle'=>array('label'=>'Partner title','type'=>'text'),
            'partnerText'=>array('label'=>'Partner text','type'=>'textarea'),
            'partnerButton'=>array('label'=>'Partner button','type'=>'text'),
            'infoKicker'=>array('label'=>'Info kicker','type'=>'text'),
            'infoTitle'=>array('label'=>'Info title','type'=>'text'),
            'infoText'=>array('label'=>'Info intro','type'=>'textarea'),
            'boardTitle'=>array('label'=>'Board card title','type'=>'text'),
            'boardText'=>array('label'=>'Board card text','type'=>'textarea'),
            'ethicsTitle'=>array('label'=>'Ethics card title','type'=>'text'),
            'ethicsText'=>array('label'=>'Ethics card text','type'=>'textarea'),
            'regulationsTitle'=>array('label'=>'Regulations card title','type'=>'text'),
            'regulationsText'=>array('label'=>'Regulations card text','type'=>'textarea'),
            'archiveSidebarTitle'=>array('label'=>'Archive side title','type'=>'text'),
            'archiveSidebarText'=>array('label'=>'Archive side text','type'=>'textarea'),
            'archiveSidebarPoint1'=>array('label'=>'Archive point 1','type'=>'text'),
            'archiveSidebarPoint2'=>array('label'=>'Archive point 2','type'=>'text'),
            'archiveSidebarPoint3'=>array('label'=>'Archive point 3','type'=>'text'),
        ),
    );
}

function zolei_homepage_editor_menu() {
    add_theme_page(__('Homepage editor','zolei-react'), __('Homepage editor','zolei-react'), 'manage_options', 'zolei-homepage-editor', 'zolei_homepage_editor_page');
}
add_action('admin_menu','zolei_homepage_editor_menu',15);

function zolei_homepage_editor_admin_assets($hook) {
    if ($hook !== 'appearance_page_zolei-homepage-editor') { return; }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts','zolei_homepage_editor_admin_assets');

function zolei_homepage_editor_sanitize_content($raw) {
    $fields = zolei_home_editable_fields();
    $allowed = array();
    foreach ($fields as $group) { foreach ($group as $key=>$def) { $allowed[$key] = $def; } }
    $out = array();
    foreach (array('lv','en') as $lang) {
        $out[$lang] = array();
        $src = isset($raw[$lang]) && is_array($raw[$lang]) ? $raw[$lang] : array();
        foreach ($allowed as $key=>$def) {
            $value = isset($src[$key]) ? wp_unslash($src[$key]) : '';
            $out[$lang][$key] = ($def['type'] === 'textarea') ? sanitize_textarea_field($value) : sanitize_text_field($value);
        }
    }
    return $out;
}

function zolei_homepage_editor_page() {
    if (!current_user_can('manage_options')) { return; }
    $saved = false;
    if (isset($_POST['zolei_homepage_save'])) {
        check_admin_referer('zolei_homepage_save_action','zolei_homepage_nonce');
        update_option('zolei_home_content_overrides', zolei_homepage_editor_sanitize_content($_POST['zolei_home_content'] ?? array()));
        update_option('zolei_home_partner_url', esc_url_raw(wp_unslash($_POST['zolei_home_partner_url'] ?? '')));
        update_option('zolei_home_rules_url', esc_url_raw(wp_unslash($_POST['zolei_home_rules_url'] ?? '')));
        update_option('zolei_home_hero_image_id', absint($_POST['zolei_home_hero_image_id'] ?? 0));
        update_option('zolei_home_partner_image_id', absint($_POST['zolei_home_partner_image_id'] ?? 0));
        $year = absint($_POST['zolei_calendar_default_year'] ?? 0);
        if ($year !== 0 && ($year < 2000 || $year > 2100)) { $year = 0; }
        update_option('zolei_calendar_default_year', $year);
        $news_count = absint($_POST['zolei_home_news_count'] ?? 3);
        update_option('zolei_home_news_count', max(1,min(12,$news_count)));
        $saved = true;
    }
    $content = get_option('zolei_home_content_overrides', array());
    if (!is_array($content)) { $content = array(); }
    $fields = zolei_home_editable_fields();
    $hero_id = absint(get_option('zolei_home_hero_image_id',0));
    $partner_id = absint(get_option('zolei_home_partner_image_id',0));
    $hero_preview = $hero_id ? wp_get_attachment_image_url($hero_id,'medium') : '';
    $partner_preview = $partner_id ? wp_get_attachment_image_url($partner_id,'medium') : '';
    $years = function_exists('zolei_tournament_years') ? zolei_tournament_years() : array((int)current_time('Y'));
    $selected_year = absint(get_option('zolei_calendar_default_year', 0));
    $front_id = (int) get_option('page_on_front');
    ?>
    <div class="wrap zole-home-editor">
      <h1><?php esc_html_e('Homepage editor','zolei-react'); ?></h1>
      <p class="description"><?php esc_html_e('Edit the visible React homepage without changing WP BBuilder. Blank fields keep the built-in theme text. Existing WP BBuilder Hero, CTA and Dynamic Form block values on the front page are also read as theme-side overrides.','zolei-react'); ?></p>
      <?php if ($saved): ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Homepage settings saved.','zolei-react'); ?></p></div><?php endif; ?>
      <div class="zole-home-shortcuts">
        <a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=zolei_tournament')); ?>"><?php esc_html_e('Manage tournaments','zolei-react'); ?></a>
        <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type=zolei_tournament')); ?>"><?php esc_html_e('Add tournament','zolei-react'); ?></a>
        <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=zolei_section')); ?>"><?php esc_html_e('Edit homepage sections','zolei-react'); ?></a>
        <?php if ($front_id): ?><a class="button" href="<?php echo esc_url(get_edit_post_link($front_id)); ?>"><?php esc_html_e('Edit Homepage blocks','zolei-react'); ?></a><?php endif; ?>
        <a class="button" href="<?php echo esc_url(admin_url('themes.php?page=zolei-settings')); ?>"><?php esc_html_e('Open full Zolei control panel','zolei-react'); ?></a>
      </div>
      <form method="post">
        <?php wp_nonce_field('zolei_homepage_save_action','zolei_homepage_nonce'); ?>
        <section class="zole-home-card">
          <h2><?php esc_html_e('Homepage behaviour and media','zolei-react'); ?></h2>
          <div class="zole-home-grid">
            <label><?php esc_html_e('Default tournament year','zolei-react'); ?><select name="zolei_calendar_default_year"><option value="0" <?php selected($selected_year,0); ?>><?php echo esc_html(sprintf(__('Automatic - current year (%d)','zolei-react'), (int) current_time('Y'))); ?></option><?php foreach ($years as $year): ?><option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year,$year); ?>><?php echo esc_html($year); ?></option><?php endforeach; ?></select></label>
            <label><?php esc_html_e('Homepage news count','zolei-react'); ?><input type="number" min="1" max="12" name="zolei_home_news_count" value="<?php echo esc_attr(absint(get_option('zolei_home_news_count',3))); ?>"></label>
            <label><?php esc_html_e('Rules button URL','zolei-react'); ?><input type="url" name="zolei_home_rules_url" value="<?php echo esc_attr(get_option('zolei_home_rules_url','')); ?>" placeholder="<?php echo esc_attr(home_url('/zoles-noteikumi/')); ?>"></label>
            <label><?php esc_html_e('Partner button URL','zolei-react'); ?><input type="url" name="zolei_home_partner_url" value="<?php echo esc_attr(get_option('zolei_home_partner_url','')); ?>" placeholder="https://..."></label>
          </div>
          <div class="zole-media-grid">
            <div class="zole-media-field"><strong><?php esc_html_e('Hero card image','zolei-react'); ?></strong><div class="zole-media-preview" id="zole-hero-preview"><?php if($hero_preview): ?><img src="<?php echo esc_url($hero_preview); ?>" alt=""><?php endif; ?></div><input type="hidden" id="zole_home_hero_image_id" name="zolei_home_hero_image_id" value="<?php echo esc_attr($hero_id); ?>"><button type="button" class="button zole-media-pick" data-target="zole_home_hero_image_id" data-preview="zole-hero-preview"><?php esc_html_e('Choose image','zolei-react'); ?></button><button type="button" class="button zole-media-clear" data-target="zole_home_hero_image_id" data-preview="zole-hero-preview"><?php esc_html_e('Use theme default','zolei-react'); ?></button></div>
            <div class="zole-media-field"><strong><?php esc_html_e('Partner banner image','zolei-react'); ?></strong><div class="zole-media-preview" id="zole-partner-preview"><?php if($partner_preview): ?><img src="<?php echo esc_url($partner_preview); ?>" alt=""><?php endif; ?></div><input type="hidden" id="zole_home_partner_image_id" name="zolei_home_partner_image_id" value="<?php echo esc_attr($partner_id); ?>"><button type="button" class="button zole-media-pick" data-target="zole_home_partner_image_id" data-preview="zole-partner-preview"><?php esc_html_e('Choose image','zolei-react'); ?></button><button type="button" class="button zole-media-clear" data-target="zole_home_partner_image_id" data-preview="zole-partner-preview"><?php esc_html_e('Use theme default','zolei-react'); ?></button></div>
          </div>
        </section>
        <?php foreach ($fields as $group_name=>$group): ?>
        <section class="zole-home-card">
          <h2><?php echo esc_html($group_name); ?></h2>
          <div class="zole-home-field-head"><span><?php esc_html_e('Field','zolei-react'); ?></span><span>LV</span><span>EN</span></div>
          <?php foreach ($group as $key=>$def): $lv=$content['lv'][$key] ?? ''; $en=$content['en'][$key] ?? ''; ?>
          <div class="zole-home-field-row">
            <label for="zole-<?php echo esc_attr($key); ?>-lv"><strong><?php echo esc_html($def['label']); ?></strong><code><?php echo esc_html($key); ?></code></label>
            <div><?php if($def['type']==='textarea'): ?><textarea id="zole-<?php echo esc_attr($key); ?>-lv" rows="3" name="zolei_home_content[lv][<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($lv); ?></textarea><?php else: ?><input id="zole-<?php echo esc_attr($key); ?>-lv" type="text" name="zolei_home_content[lv][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($lv); ?>"><?php endif; ?></div>
            <div><?php if($def['type']==='textarea'): ?><textarea rows="3" name="zolei_home_content[en][<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($en); ?></textarea><?php else: ?><input type="text" name="zolei_home_content[en][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($en); ?>"><?php endif; ?></div>
          </div>
          <?php endforeach; ?>
        </section>
        <?php endforeach; ?>
        <p class="submit"><button type="submit" name="zolei_homepage_save" class="button button-primary button-hero"><?php esc_html_e('Save homepage','zolei-react'); ?></button></p>
      </form>
    </div>
    <style>
    .zole-home-editor{max-width:1500px}.zole-home-shortcuts{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0}.zole-home-card{background:#fff;border:1px solid #dcdcde;border-radius:16px;padding:20px;margin:18px 0;box-shadow:0 10px 28px rgba(0,0,0,.04)}.zole-home-card h2{margin-top:0}.zole-home-grid{display:grid;grid-template-columns:repeat(2,minmax(280px,1fr));gap:14px}.zole-home-grid label{font-weight:600}.zole-home-grid input,.zole-home-grid select{display:block;width:100%;max-width:none;margin-top:6px}.zole-home-field-head,.zole-home-field-row{display:grid;grid-template-columns:220px 1fr 1fr;gap:12px;align-items:start}.zole-home-field-head{font-weight:700;color:#50575e;border-bottom:1px solid #dcdcde;padding:0 0 8px}.zole-home-field-row{padding:12px 0;border-bottom:1px solid #eef0f1}.zole-home-field-row:last-child{border-bottom:0}.zole-home-field-row label{display:grid;gap:4px}.zole-home-field-row code{font-weight:400;font-size:11px}.zole-home-field-row input,.zole-home-field-row textarea{width:100%;max-width:none}.zole-media-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:18px}.zole-media-field{border:1px solid #e1e3e5;border-radius:12px;padding:14px}.zole-media-preview{min-height:80px;margin:10px 0;background:#f6f7f7;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden}.zole-media-preview img{max-width:100%;max-height:160px;display:block}.zole-media-field .button{margin-right:6px}@media(max-width:900px){.zole-home-grid,.zole-media-grid,.zole-home-field-head,.zole-home-field-row{grid-template-columns:1fr}.zole-home-field-head{display:none}}
    </style>
    <script>
    jQuery(function($){
      $('.zole-media-pick').on('click',function(e){e.preventDefault();var btn=$(this),target=$('#'+btn.data('target')),preview=$('#'+btn.data('preview'));var frame=wp.media({title:'Choose image',button:{text:'Use image'},multiple:false,library:{type:'image'}});frame.on('select',function(){var img=frame.state().get('selection').first().toJSON();target.val(img.id);preview.html('<img src="'+(img.sizes&&img.sizes.medium?img.sizes.medium.url:img.url)+'" alt="">');});frame.open();});
      $('.zole-media-clear').on('click',function(e){e.preventDefault();$('#'+$(this).data('target')).val('');$('#'+$(this).data('preview')).empty();});
    });
    </script>
    <?php
}

function zolei_apply_home_content_overrides($labels) {
    $saved = get_option('zolei_home_content_overrides', array());
    if (!is_array($saved)) { return $labels; }
    $lang = function_exists('zolei_lang_is_en') && zolei_lang_is_en() ? 'en' : 'lv';
    $values = isset($saved[$lang]) && is_array($saved[$lang]) ? $saved[$lang] : array();
    foreach ($values as $key=>$value) {
        if ($value !== '' && array_key_exists($key,$labels)) { $labels[$key] = $value; }
    }
    return $labels;
}

function zolei_front_page_block_overrides($labels, $urls) {
    $front_id = (int) get_option('page_on_front');
    if (!$front_id) { return array('labels'=>$labels,'urls'=>$urls); }
    $content = (string) get_post_field('post_content',$front_id);
    if ($content === '' || !function_exists('parse_blocks')) { return array('labels'=>$labels,'urls'=>$urls); }
    $walk = function($blocks) use (&$walk,&$labels,&$urls) {
        foreach ((array)$blocks as $block) {
            $name = $block['blockName'] ?? '';
            $a = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();
            if ($name === 'wpbb/hero') {
                if (!empty($a['title'])) { $labels['heroTitle'] = sanitize_text_field($a['title']); }
                if (!empty($a['text'])) { $labels['heroText'] = sanitize_textarea_field($a['text']); }
                if (!empty($a['buttonText'])) { $labels['calendarBtn'] = sanitize_text_field($a['buttonText']); }
                if (!empty($a['buttonUrl'])) { $urls['calendar'] = esc_url_raw($a['buttonUrl']); }
            } elseif ($name === 'wpbb/cta-section') {
                if (!empty($a['title'])) { $labels['contactTitle'] = sanitize_text_field($a['title']); }
                if (!empty($a['text'])) { $labels['contactText'] = sanitize_textarea_field($a['text']); }
                if (!empty($a['buttonText'])) { $labels['contactBtn'] = sanitize_text_field($a['buttonText']); }
                if (!empty($a['buttonUrl'])) { $urls['contact'] = esc_url_raw($a['buttonUrl']); }
            } elseif ($name === 'wpbb/dynamic-form') {
                if (!empty($a['title'])) { $labels['formTitle'] = sanitize_text_field($a['title']); }
                if (!empty($a['buttonText'])) { $labels['formSubmit'] = sanitize_text_field($a['buttonText']); }
            }
            if (!empty($block['innerBlocks'])) { $walk($block['innerBlocks']); }
        }
    };
    $walk(parse_blocks($content));
    return array('labels'=>$labels,'urls'=>$urls);
}

function zolei_home_media_settings() {
    $hero_id = absint(get_option('zolei_home_hero_image_id',0));
    $partner_id = absint(get_option('zolei_home_partner_image_id',0));
    return array(
        'hero_image'=>$hero_id ? (string) wp_get_attachment_image_url($hero_id,'large') : '',
        'partner_image'=>$partner_id ? (string) wp_get_attachment_image_url($partner_id,'large') : '',
    );
}
function zolei_home_partner_banner_url() {
    $m = zolei_home_media_settings();
    return $m['partner_image'];
}
function zolei_home_partner_url() {
    $url = trim((string)get_option('zolei_home_partner_url',''));
    return $url !== '' ? $url : 'https://goo.gl/g7jJpm';
}
function zolei_home_rules_url() {
    $url = trim((string)get_option('zolei_home_rules_url',''));
    return $url !== '' ? $url : home_url('/zoles-noteikumi/');
}

function zolei_migrate_seeded_homepage_copy_once() {
    if (get_option('zolei_homepage_copy_330_done')) { return; }
    $pages = get_posts(array('post_type'=>'page','post_status'=>'any','posts_per_page'=>-1,'s'=>'Headless React','suppress_filters'=>true));
    $pairs = array(
        'Headless React homepage renders the live design, while this page content remains editable with WP BBuilder blocks.' => 'Tournament calendar, rules, results and ratings in one elegant, easy-to-use place for players and organisers.',
        'Headless React sākumlapa attēlo dzīvo dizainu, bet lapas saturs paliek rediģējams ar WP BBuilder blokiem.' => 'Turnīru kalendārs, noteikumi, rezultāti un reitingi vienā elegantā, viegli lietojamā vietā spēlētājiem un organizatoriem.',
    );
    foreach ($pages as $page) {
        $content = (string)$page->post_content;
        $updated = strtr($content, $pairs);
        if ($updated !== $content) { wp_update_post(wp_slash(array('ID'=>$page->ID,'post_content'=>$updated))); }
    }
    update_option('zolei_homepage_copy_330_done',1,false);
}
add_action('admin_init','zolei_migrate_seeded_homepage_copy_once',35);

function zolei_theme_editor_compat_assets() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'page') { return; }
    $file = get_template_directory().'/assets/js/editor-compat.js';
    if (!file_exists($file)) { return; }
    wp_enqueue_script('zolei-editor-compat', get_template_directory_uri().'/assets/js/editor-compat.js', array('wp-blocks','wp-element','wp-components','wp-block-editor','wp-i18n','wp-dom-ready'), filemtime($file), true);
}
add_action('enqueue_block_editor_assets','zolei_theme_editor_compat_assets',20);
