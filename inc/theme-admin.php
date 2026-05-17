<?php
if (!defined('ABSPATH')) { exit; }

function zolei_admin_menu() {
    add_theme_page(__('Zolei control panel','zolei-react'), __('Zolei control panel','zolei-react'), 'manage_options', 'zolei-settings', 'zolei_admin_page');
}
add_action('admin_menu','zolei_admin_menu');

function zolei_admin_sanitize_pdf_sections($raw) {
    $defaults = function_exists('zolei_pdf_section_defaults') ? zolei_pdf_section_defaults() : array();
    $types = function_exists('zolei_pdf_types') ? zolei_pdf_types() : array();
    $clean = array();
    foreach ($defaults as $key => $row) {
        $src = isset($raw[$key]) && is_array($raw[$key]) ? $raw[$key] : array();
        $type = sanitize_key($src['type'] ?? $row['type']);
        if (!isset($types[$type])) { $type = $row['type']; }
        $clean[$key] = array(
            'enabled' => !empty($src['enabled']) ? 1 : 0,
            'type' => $type,
            'title_lv' => sanitize_text_field(wp_unslash($src['title_lv'] ?? $row['title_lv'])),
            'title_en' => sanitize_text_field(wp_unslash($src['title_en'] ?? $row['title_en'])),
            'text_lv' => sanitize_textarea_field(wp_unslash($src['text_lv'] ?? $row['text_lv'])),
            'text_en' => sanitize_textarea_field(wp_unslash($src['text_en'] ?? $row['text_en'])),
        );
    }
    return $clean;
}

function zolei_admin_sanitize_pdf_directories($raw) {
    $defaults = function_exists('zolei_pdf_directory_defaults') ? zolei_pdf_directory_defaults() : array();
    $clean = array();
    foreach ($defaults as $type=>$dir) {
        $src = isset($raw[$type]) ? (string) wp_unslash($raw[$type]) : $dir;
        $src = trim(str_replace('\\','/',$src), '/');
        $src = preg_replace('~[^A-Za-z0-9_\-./]~', '', $src);
        $src = preg_replace('~\.{2,}~', '', $src);
        $clean[$type] = $src ?: $dir;
    }
    return $clean;
}

function zolei_admin_create_pdf_record($args) {
    $title = sanitize_text_field($args['title'] ?? 'PDF');
    $slug = sanitize_title($args['slug'] ?? $title);
    $id = wp_insert_post(wp_slash(array('post_type'=>'zolei_pdf','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>'')), true);
    if (is_wp_error($id)) { return $id; }
    update_post_meta($id,'_zolei_pdf_url',esc_url_raw($args['url'] ?? ''));
    update_post_meta($id,'_zolei_pdf_source_url',esc_url_raw($args['source_url'] ?? ($args['url'] ?? '')));
    update_post_meta($id,'_zolei_pdf_relative_path',sanitize_text_field($args['relative_path'] ?? ''));
    update_post_meta($id,'_zolei_pdf_directory',sanitize_text_field($args['directory'] ?? ''));
    update_post_meta($id,'_zolei_pdf_month',sanitize_key($args['month'] ?? ''));
    update_post_meta($id,'_zolei_pdf_type',sanitize_key($args['type'] ?? 'rezultati'));
    update_post_meta($id,'_zolei_pdf_year',absint($args['year'] ?? 0));
    if (isset($args['compressed'])) { update_post_meta($id,'_zolei_pdf_compressed', !empty($args['compressed']) ? 1 : 0); }
    if (isset($args['original_size'])) { update_post_meta($id,'_zolei_pdf_original_size', absint($args['original_size'])); }
    if (isset($args['compressed_size'])) { update_post_meta($id,'_zolei_pdf_compressed_size', absint($args['compressed_size'])); }
    if(function_exists('pll_set_post_language')) { pll_set_post_language($id,'lv'); }
    return $id;
}

function zolei_admin_handle_pdf_upload() {
    if (empty($_FILES['zolei_pdf_file']) || !is_array($_FILES['zolei_pdf_file'])) { return new WP_Error('no_file', __('No PDF selected.','zolei-react')); }
    $file = $_FILES['zolei_pdf_file'];
    if (!empty($file['error'])) { return new WP_Error('upload_error', __('Upload failed.','zolei-react')); }
    $name = sanitize_file_name($file['name'] ?? 'document.pdf');
    if (!preg_match('/\.pdf$/i', $name)) { return new WP_Error('not_pdf', __('Only PDF files are allowed.','zolei-react')); }
    $check = wp_check_filetype($name, array('pdf'=>'application/pdf'));
    if (($check['ext'] ?? '') !== 'pdf') { return new WP_Error('bad_type', __('Only PDF files are allowed.','zolei-react')); }
    $type = sanitize_key(wp_unslash($_POST['zolei_upload_pdf_type'] ?? 'rezultati'));
    if (!array_key_exists($type, zolei_pdf_types())) { $type = 'rezultati'; }
    $base_dir = zolei_pdf_dir_for_type($type);
    $subdir = trim(str_replace('\\','/', (string) wp_unslash($_POST['zolei_upload_subdir'] ?? '')), '/');
    $subdir = preg_replace('~[^A-Za-z0-9_\-./]~', '', $subdir);
    $subdir = preg_replace('~\.{2,}~', '', $subdir);
    $dir = trim($base_dir . ($subdir ? '/' . $subdir : ''), '/');
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) { return new WP_Error('upload_dir', $upload['error']); }
    $target_dir = trailingslashit($upload['basedir']) . $dir;
    wp_mkdir_p($target_dir);
    $filename = wp_unique_filename($target_dir, $name);
    $target = trailingslashit($target_dir) . $filename;
    if (!@move_uploaded_file($file['tmp_name'], $target)) { return new WP_Error('move_failed', __('Could not move uploaded PDF into the selected directory.','zolei-react')); }
    @chmod($target, 0644);
    $compression = function_exists('zolei_pdf_compress_file') ? zolei_pdf_compress_file($target) : array('ok'=>false,'original_size'=>@filesize($target),'compressed_size'=>@filesize($target),'message'=>'');
    $relative = trim($dir . '/' . $filename, '/');
    $url = trailingslashit($upload['baseurl']) . str_replace('%2F', '/', rawurlencode($relative));
    $title = sanitize_text_field(wp_unslash($_POST['zolei_upload_title'] ?? ''));
    if ($title === '') { $title = preg_replace('/\.pdf$/i', '', $filename); }
    return zolei_admin_create_pdf_record(array(
        'title'=>$title,
        'slug'=>sanitize_title($relative),
        'url'=>$url,
        'source_url'=>$url,
        'relative_path'=>$relative,
        'directory'=>$dir,
        'type'=>$type,
        'month'=>sanitize_key(wp_unslash($_POST['zolei_upload_month'] ?? '')),
        'year'=>absint($_POST['zolei_upload_year'] ?? date('Y')),
        'compressed'=>!empty($compression['ok']),
        'original_size'=>absint($compression['original_size'] ?? 0),
        'compressed_size'=>absint(($compression['compressed_size'] ?? 0) ?: @filesize($target)),
    ));
}

function zolei_admin_delete_pdf_record($post_id, $delete_file = false) {
    $post_id = absint($post_id);
    if (!$post_id || get_post_type($post_id) !== 'zolei_pdf') { return false; }
    if ($delete_file) {
        $relative = trim(str_replace('\\','/', (string) get_post_meta($post_id,'_zolei_pdf_relative_path',true)), '/');
        if ($relative && function_exists('zolei_pdf_target_path')) {
            list($path) = zolei_pdf_target_path($relative);
            $upload = wp_upload_dir();
            $base = realpath($upload['basedir']);
            $real = $path && file_exists($path) ? realpath($path) : false;
            if ($base && $real && strpos($real, $base) === 0 && is_file($real)) { @unlink($real); }
        }
    }
    return (bool) wp_delete_post($post_id, true);
}

function zolei_admin_pdf_rows($type = '') {
    $args = array('post_type'=>'zolei_pdf','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC');
    if ($type) { $args['meta_key'] = '_zolei_pdf_type'; $args['meta_value'] = sanitize_key($type); }
    return get_posts($args);
}

function zolei_admin_pdf_count($type) {
    $q = new WP_Query(array('post_type'=>'zolei_pdf','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_key'=>'_zolei_pdf_type','meta_value'=>$type));
    $count = intval($q->found_posts); wp_reset_postdata(); return $count;
}


function zolei_admin_handle_gallery_upload() {
    if (empty($_FILES['zolei_gallery_files']) || empty($_FILES['zolei_gallery_files']['name']) || !is_array($_FILES['zolei_gallery_files']['name'])) {
        return new WP_Error('no_gallery_files', __('No gallery images selected.','zolei-react'));
    }
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) { return new WP_Error('upload_dir', $upload['error']); }
    $dir = trailingslashit($upload['basedir']) . 'zolei-gallery';
    $urlbase = trailingslashit($upload['baseurl']) . 'zolei-gallery';
    wp_mkdir_p($dir);
    $current = get_option('zolei_gallery_images');
    if (!is_array($current)) { $current = array(); }
    $count = 0;
    $files = $_FILES['zolei_gallery_files'];
    foreach ($files['name'] as $idx => $original_name) {
        if (empty($original_name) || !empty($files['error'][$idx])) { continue; }
        $name = sanitize_file_name($original_name);
        $type = wp_check_filetype($name, array('jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'));
        if (empty($type['type'])) { continue; }
        $filename = wp_unique_filename($dir, $name);
        $target = trailingslashit($dir) . $filename;
        if (!@move_uploaded_file($files['tmp_name'][$idx], $target)) { continue; }
        @chmod($target, 0644);
        $versions = function_exists('zolei_make_gallery_versions') ? zolei_make_gallery_versions($target, $filename) : false;
        if (is_array($versions) && !empty($versions['full']) && !empty($versions['thumb'])) {
            $full_name = basename($versions['full']);
            $thumb_name = basename($versions['thumb']);
            $mobile_name = !empty($versions['mobile']) ? basename($versions['mobile']) : $thumb_name;
            $full_url = trailingslashit($urlbase) . $full_name;
            $thumb_url = trailingslashit($urlbase) . $thumb_name;
            $mobile_url = trailingslashit($urlbase) . $mobile_name;
            if (function_exists('zolei_prefer_avif_url')) { $full_url = zolei_prefer_avif_url($full_url); $thumb_url = zolei_prefer_avif_url($thumb_url); $mobile_url = zolei_prefer_avif_url($mobile_url); }
            $current[] = array('thumb'=>$thumb_url, 'mobile'=>$mobile_url, 'full'=>$full_url, 'source'=>trailingslashit($urlbase) . basename($target));
            $count++;
        } else {
            $src_url = trailingslashit($urlbase) . basename($target);
            $current[] = array('thumb'=>$src_url, 'full'=>$src_url, 'source'=>$src_url);
            $count++;
        }
    }
    update_option('zolei_gallery_images', array_values($current));
    return $count;
}

function zolei_admin_reset_gallery_references() {
    $items = function_exists('zolei_default_gallery') ? zolei_default_gallery() : array();
    update_option('zolei_gallery_images', $items);
    return count($items);
}

function zolei_admin_page() {
    if (!current_user_can('manage_options')) { return; }
    $saved = false; $pdf_message = ''; $pdf_error = ''; $gallery_message = ''; $gallery_error = '';
    if (isset($_POST['zolei_save_settings'])) {
        check_admin_referer('zolei_save_settings_action','zolei_save_settings_nonce');
        update_option('zolei_contact_email', sanitize_email(wp_unslash($_POST['zolei_contact_email'] ?? '')));
        update_option('zolei_contact_phone', sanitize_text_field(wp_unslash($_POST['zolei_contact_phone'] ?? '')));
        update_option('zolei_hero_subline', sanitize_text_field(wp_unslash($_POST['zolei_hero_subline'] ?? '')));
        update_option('zolei_hcaptcha_site_key', sanitize_text_field(wp_unslash($_POST['zolei_hcaptcha_site_key'] ?? '')));
        update_option('zolei_hcaptcha_secret_key', sanitize_text_field(wp_unslash($_POST['zolei_hcaptcha_secret_key'] ?? '')));
        update_option('zolei_pdf_download_on_import', !empty($_POST['zolei_pdf_download_on_import']) ? 1 : 0);
        update_option('zolei_pdf_compression_enabled', !empty($_POST['zolei_pdf_compression_enabled']) ? 1 : 0);
        $quality = sanitize_key(wp_unslash($_POST['zolei_pdf_compression_quality'] ?? 'ebook'));
        if (!function_exists('zolei_pdf_compression_quality_options') || !array_key_exists($quality, zolei_pdf_compression_quality_options())) { $quality = 'ebook'; }
        update_option('zolei_pdf_compression_quality', $quality);
        update_option('zolei_pdf_compression_binary', sanitize_text_field(wp_unslash($_POST['zolei_pdf_compression_binary'] ?? '')));
        update_option('zolei_pdf_sections', zolei_admin_sanitize_pdf_sections($_POST['zolei_pdf_sections'] ?? array()));
        update_option('zolei_pdf_directories', zolei_admin_sanitize_pdf_directories($_POST['zolei_pdf_directories'] ?? array()));
        $saved = true;
    }
    if (isset($_POST['zolei_import_pdf_manifest'])) {
        check_admin_referer('zolei_pdf_manifest_action','zolei_pdf_manifest_nonce');
        if (function_exists('zolei_demo_import_pdfs')) { $pdf_message = sprintf(__('Imported/updated %d PDF records and copied packaged PDFs into uploads directories.','zolei-react'), intval(zolei_demo_import_pdfs())); }
    }
    if (isset($_POST['zolei_upload_pdf_submit'])) {
        check_admin_referer('zolei_pdf_upload_action','zolei_pdf_upload_nonce');
        $result = zolei_admin_handle_pdf_upload();
        if (is_wp_error($result)) { $pdf_error = $result->get_error_message(); } else { $pdf_message = __('PDF uploaded, compressed if possible, and added to the selected directory.','zolei-react'); }
    }
    if (isset($_POST['zolei_delete_pdf_submit'])) {
        check_admin_referer('zolei_pdf_delete_action','zolei_pdf_delete_nonce');
        if (zolei_admin_delete_pdf_record(absint($_POST['zolei_delete_pdf_id'] ?? 0), !empty($_POST['zolei_delete_pdf_file']))) { $pdf_message = __('PDF record deleted.','zolei-react'); }
    }
    if (isset($_POST['zolei_gallery_upload_submit'])) {
        check_admin_referer('zolei_gallery_upload_action','zolei_gallery_upload_nonce');
        $result = zolei_admin_handle_gallery_upload();
        if (is_wp_error($result)) { $gallery_error = $result->get_error_message(); }
        else { $gallery_message = sprintf(__('Uploaded and optimized %d gallery images. AVIF is used automatically when supported by the server.','zolei-react'), intval($result)); }
    }
    if (isset($_POST['zolei_gallery_reset_submit'])) {
        check_admin_referer('zolei_gallery_reset_action','zolei_gallery_reset_nonce');
        $gallery_message = sprintf(__('Restored %d old-site gallery references.','zolei-react'), intval(zolei_admin_reset_gallery_references()));
    }

    $types = function_exists('zolei_pdf_types') ? zolei_pdf_types() : array();
    $dirs = function_exists('zolei_pdf_directories') ? zolei_pdf_directories() : array();
    $pdf_sections = function_exists('zolei_pdf_sections') ? zolei_pdf_sections() : array();
    $months = function_exists('zolei_pdf_months') ? zolei_pdf_months() : array();
    $compression_options = function_exists('zolei_pdf_compression_quality_options') ? zolei_pdf_compression_quality_options() : array('ebook'=>'Recommended good quality');
    $compression_quality = get_option('zolei_pdf_compression_quality','ebook');
    $compression_status = function_exists('zolei_pdf_compression_status') ? zolei_pdf_compression_status() : '';
    $manifest_count = 0;
    $manifest_file = get_template_directory().'/assets/data/zolei-pdfs.json';
    if (file_exists($manifest_file)) { $manifest_data = json_decode(file_get_contents($manifest_file), true); if (is_array($manifest_data)) { $manifest_count = count($manifest_data); } }
    ?>
    <div class="wrap zole-admin-wrap">
      <h1><?php esc_html_e('Zolei one-page control panel','zolei-react'); ?></h1>
      <p class="description"><?php esc_html_e('Manage the one-page React frontend from one place. PDFs stay in the same old-site upload directories and are shared by LV/EN; only labels and section text are translated.','zolei-react'); ?></p>
      <?php if($saved): ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.','zolei-react'); ?></p></div><?php endif; ?>
      <?php if($pdf_message): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($pdf_message); ?></p></div><?php endif; ?>
      <?php if($pdf_error): ?><div class="notice notice-error is-dismissible"><p><?php echo esc_html($pdf_error); ?></p></div><?php endif; ?>
      <?php if($gallery_message): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($gallery_message); ?></p></div><?php endif; ?>
      <?php if($gallery_error): ?><div class="notice notice-error is-dismissible"><p><?php echo esc_html($gallery_error); ?></p></div><?php endif; ?>

      <form method="post" class="zole-admin-card zole-admin-main-settings">
        <?php wp_nonce_field('zolei_save_settings_action','zolei_save_settings_nonce'); ?>
        <h2><?php esc_html_e('Frontend, form and PDF settings','zolei-react'); ?></h2>
        <div class="zole-admin-grid-2">
          <label><?php esc_html_e('Contact email','zolei-react'); ?><input class="regular-text" name="zolei_contact_email" value="<?php echo esc_attr(get_option('zolei_contact_email','info@zolei.lv')); ?>"></label>
          <label><?php esc_html_e('Contact phone','zolei-react'); ?><input class="regular-text" name="zolei_contact_phone" value="<?php echo esc_attr(get_option('zolei_contact_phone','')); ?>"></label>
          <label><?php esc_html_e('Hero subline','zolei-react'); ?><input class="large-text" name="zolei_hero_subline" value="<?php echo esc_attr(get_option('zolei_hero_subline','')); ?>"></label>
          <label><?php esc_html_e('hCaptcha site key','zolei-react'); ?><input class="large-text code" name="zolei_hcaptcha_site_key" value="<?php echo esc_attr(get_option('zolei_hcaptcha_site_key','')); ?>"></label>
          <label><?php esc_html_e('hCaptcha secret key','zolei-react'); ?><input class="large-text code" name="zolei_hcaptcha_secret_key" value="<?php echo esc_attr(get_option('zolei_hcaptcha_secret_key','')); ?>"></label>
          <label><?php esc_html_e('Ghostscript binary path','zolei-react'); ?><input class="regular-text code" name="zolei_pdf_compression_binary" value="<?php echo esc_attr(get_option('zolei_pdf_compression_binary','')); ?>" placeholder="gs"><span class="description"><?php echo esc_html($compression_status); ?></span></label>
        </div>
        <p><label><input type="checkbox" name="zolei_pdf_compression_enabled" value="1" <?php checked(function_exists('zolei_pdf_compression_enabled') ? zolei_pdf_compression_enabled() : true); ?>> <?php esc_html_e('Automatically compress demo-import PDFs and every new PDF upload. Keep original only if compression is not smaller.','zolei-react'); ?></label></p>
        <p><label><?php esc_html_e('Compression quality','zolei-react'); ?> <select name="zolei_pdf_compression_quality"><?php foreach($compression_options as $qkey=>$qlabel): ?><option value="<?php echo esc_attr($qkey); ?>" <?php selected($compression_quality,$qkey); ?>><?php echo esc_html($qlabel); ?></option><?php endforeach; ?></select></label></p>
        <h3><?php esc_html_e('PDF section titles and translations','zolei-react'); ?></h3>
        <div class="zole-admin-section-table">
          <?php foreach ($pdf_sections as $key=>$row): $type_for_row = $row['type']; ?>
          <div class="zole-admin-section-row">
            <div><label><input type="checkbox" name="zolei_pdf_sections[<?php echo esc_attr($key); ?>][enabled]" value="1" <?php checked(!empty($row['enabled'])); ?>> <strong><?php echo esc_html(ucfirst($key)); ?></strong></label><input type="hidden" name="zolei_pdf_sections[<?php echo esc_attr($key); ?>][type]" value="<?php echo esc_attr($type_for_row); ?>"></div>
            <label><?php esc_html_e('Upload directory','zolei-react'); ?><input class="regular-text code" name="zolei_pdf_directories[<?php echo esc_attr($type_for_row); ?>]" value="<?php echo esc_attr($dirs[$type_for_row] ?? ''); ?>"></label>
            <label>LV<input class="regular-text" name="zolei_pdf_sections[<?php echo esc_attr($key); ?>][title_lv]" value="<?php echo esc_attr($row['title_lv']); ?>"><textarea rows="2" name="zolei_pdf_sections[<?php echo esc_attr($key); ?>][text_lv]" class="large-text"><?php echo esc_textarea($row['text_lv']); ?></textarea></label>
            <label>EN<input class="regular-text" name="zolei_pdf_sections[<?php echo esc_attr($key); ?>][title_en]" value="<?php echo esc_attr($row['title_en']); ?>"><textarea rows="2" name="zolei_pdf_sections[<?php echo esc_attr($key); ?>][text_en]" class="large-text"><?php echo esc_textarea($row['text_en']); ?></textarea></label>
          </div>
          <?php endforeach; ?>
        </div>
        <p><button type="submit" name="zolei_save_settings" class="button button-primary button-hero"><?php esc_html_e('Save settings','zolei-react'); ?></button></p>
      </form>

      <div class="zole-admin-card">
        <h2><?php esc_html_e('Directory-based PDF manager','zolei-react'); ?></h2>
        <p><?php printf(esc_html__('%d old-site PDFs are packaged in the theme. Demo import copies them to the same wp-content/uploads directories and creates records automatically.','zolei-react'), intval($manifest_count)); ?></p>
        <form method="post" style="margin:12px 0 22px;">
          <?php wp_nonce_field('zolei_pdf_manifest_action','zolei_pdf_manifest_nonce'); ?>
          <button type="submit" name="zolei_import_pdf_manifest" class="button button-secondary"><?php esc_html_e('Import / update all demo PDFs now','zolei-react'); ?></button>
          <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=zolei_pdf')); ?>"><?php esc_html_e('Open raw PDF records','zolei-react'); ?></a>
        </form>
        <div class="zole-pdf-directory-grid">
          <?php foreach($types as $type=>$label): $rows=zolei_admin_pdf_rows($type); ?>
          <section class="zole-pdf-directory-card">
            <header><h3><?php echo esc_html($label); ?></h3><code>wp-content/uploads/<?php echo esc_html($dirs[$type] ?? ''); ?></code><span><?php echo esc_html(count($rows)); ?> PDFs</span></header>
            <form method="post" enctype="multipart/form-data" class="zole-pdf-upload-line">
              <?php wp_nonce_field('zolei_pdf_upload_action','zolei_pdf_upload_nonce'); ?>
              <input type="hidden" name="zolei_upload_pdf_type" value="<?php echo esc_attr($type); ?>">
              <input type="file" name="zolei_pdf_file" accept="application/pdf,.pdf" required>
              <input name="zolei_upload_title" placeholder="<?php esc_attr_e('Title / filename','zolei-react'); ?>">
              <input class="code" name="zolei_upload_subdir" placeholder="<?php esc_attr_e('Optional subdirectory, e.g. 2026-GADS','zolei-react'); ?>">
              <input type="number" name="zolei_upload_year" value="<?php echo esc_attr(date('Y')); ?>" min="2000" max="2100">
              <select name="zolei_upload_month"><option value=""><?php esc_html_e('No month','zolei-react'); ?></option><?php foreach($months as $mkey=>$mlabel): ?><option value="<?php echo esc_attr($mkey); ?>"><?php echo esc_html($mlabel); ?></option><?php endforeach; ?></select>
              <button type="submit" name="zolei_upload_pdf_submit" class="button button-primary"><?php esc_html_e('Upload + compress','zolei-react'); ?></button>
            </form>
            <div class="zole-pdf-list">
              <?php foreach(array_slice($rows,0,14) as $pdf): $url=get_post_meta($pdf->ID,'_zolei_pdf_url',true); $rel=get_post_meta($pdf->ID,'_zolei_pdf_relative_path',true); $year=get_post_meta($pdf->ID,'_zolei_pdf_year',true); ?>
              <div class="zole-pdf-row">
                <div><strong><?php echo esc_html(get_the_title($pdf)); ?></strong><small><?php echo esc_html($year); ?> · <code><?php echo esc_html($rel); ?></code></small></div>
                <div class="zole-pdf-row-actions"><?php if($url): ?><a class="button button-small" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">PDF</a><?php endif; ?><a class="button button-small" href="<?php echo esc_url(get_edit_post_link($pdf->ID)); ?>"><?php esc_html_e('Edit','zolei-react'); ?></a><form method="post" onsubmit="return confirm('<?php echo esc_js(__('Delete this PDF record?','zolei-react')); ?>');"><?php wp_nonce_field('zolei_pdf_delete_action','zolei_pdf_delete_nonce'); ?><input type="hidden" name="zolei_delete_pdf_id" value="<?php echo esc_attr($pdf->ID); ?>"><label><input type="checkbox" name="zolei_delete_pdf_file" value="1"> file</label><button class="button button-small" name="zolei_delete_pdf_submit" type="submit"><?php esc_html_e('Delete','zolei-react'); ?></button></form></div>
              </div>
              <?php endforeach; if(!$rows): ?><p class="description"><?php esc_html_e('No PDFs yet. Import demo PDFs or upload a file into this directory.','zolei-react'); ?></p><?php endif; ?>
              <?php if(count($rows)>14): ?><p><a href="<?php echo esc_url(admin_url('edit.php?post_type=zolei_pdf&zolei_pdf_type='.$type)); ?>"><?php printf(esc_html__('View all %d files','zolei-react'), count($rows)); ?></a></p><?php endif; ?>
            </div>
          </section>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="zole-admin-card">
        <h2><?php esc_html_e('Gallery image manager','zolei-react'); ?></h2>
        <p><?php esc_html_e('Upload new gallery photos here. The theme creates crisp high-resolution thumbnails, mobile images and AVIF versions when the server supports AVIF. The homepage shows 10 photos first and loads 10 more each click.','zolei-react'); ?></p>
        <div class="zole-gallery-admin-grid">
          <form method="post" enctype="multipart/form-data" class="zole-gallery-upload-box">
            <?php wp_nonce_field('zolei_gallery_upload_action','zolei_gallery_upload_nonce'); ?>
            <label><strong><?php esc_html_e('Upload gallery images','zolei-react'); ?></strong><input type="file" name="zolei_gallery_files[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
            <button type="submit" name="zolei_gallery_upload_submit" class="button button-primary"><?php esc_html_e('Upload + AVIF optimize','zolei-react'); ?></button>
          </form>
          <form method="post" class="zole-gallery-upload-box">
            <?php wp_nonce_field('zolei_gallery_reset_action','zolei_gallery_reset_nonce'); ?>
            <p><?php printf(esc_html__('Current gallery items: %d','zolei-react'), count(function_exists('zolei_gallery_items') ? zolei_gallery_items() : array())); ?></p>
            <button type="submit" name="zolei_gallery_reset_submit" class="button"><?php esc_html_e('Restore old-site gallery references','zolei-react'); ?></button>
          </form>
        </div>
      </div>
    </div>
    <style>
      .zole-admin-wrap{max-width:1320px}.zole-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:16px;padding:22px;margin:20px 0;box-shadow:0 12px 28px rgba(0,0,0,.04)}
      .zole-admin-grid-2{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:16px}.zole-admin-grid-2 label{font-weight:600}.zole-admin-grid-2 input{display:block;margin-top:6px;width:100%}.zole-admin-grid-2 .description{display:block;margin-top:6px;font-weight:400}
      .zole-admin-section-table{display:grid;gap:12px}.zole-admin-section-row{display:grid;grid-template-columns:150px 230px 1fr 1fr;gap:12px;align-items:start;background:#f6f7f7;border:1px solid #e3e5e7;border-radius:12px;padding:12px}.zole-admin-section-row label{font-weight:600}.zole-admin-section-row input,.zole-admin-section-row textarea{width:100%;margin-top:5px}
      .zole-pdf-directory-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:18px}.zole-pdf-directory-card{border:1px solid #e0e2e4;border-radius:14px;background:#fbfbfc;overflow:hidden}.zole-pdf-directory-card header{display:grid;gap:4px;background:#123d2b;color:#fff;padding:16px}.zole-pdf-directory-card header h3{color:#fff;margin:0}.zole-pdf-directory-card header code{color:#ffe8ad;background:rgba(255,255,255,.08);padding:3px 6px;border-radius:6px}.zole-pdf-directory-card header span{font-weight:700;color:#f7d777}
      .zole-pdf-upload-line{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:14px;border-bottom:1px solid #e4e6e8}.zole-pdf-upload-line input,.zole-pdf-upload-line select{width:100%;max-width:none}.zole-pdf-upload-line button{justify-self:start}.zole-pdf-list{padding:8px 14px 14px}.zole-pdf-row{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #eceef0;padding:10px 0}.zole-pdf-row:last-child{border-bottom:0}.zole-pdf-row small{display:block;color:#646970;margin-top:3px}.zole-pdf-row-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap}.zole-pdf-row-actions form{display:flex;gap:6px;align-items:center;margin:0}.zole-gallery-admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}.zole-gallery-upload-box{display:grid;gap:12px;border:1px solid #e0e2e4;background:#fbfbfc;border-radius:14px;padding:16px}.zole-gallery-upload-box input[type=file]{display:block;margin-top:8px}
      @media(max-width:900px){.zole-admin-grid-2,.zole-admin-section-row,.zole-pdf-upload-line{grid-template-columns:1fr}.zole-pdf-row{display:block}.zole-pdf-row-actions{margin-top:8px}}
    </style>
    <?php
}
