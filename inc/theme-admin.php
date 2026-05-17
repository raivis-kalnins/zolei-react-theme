<?php
if (!defined('ABSPATH')) { exit; }
function zolei_admin_menu() {
    add_theme_page(__('Zolei theme settings','zolei-react'), __('Zolei settings','zolei-react'), 'manage_options', 'zolei-settings', 'zolei_admin_page');
}
add_action('admin_menu','zolei_admin_menu');
function zolei_admin_page() {
    if (!current_user_can('manage_options')) { return; }
    $saved = false;
    if (isset($_POST['zolei_save_settings'])) {
        check_admin_referer('zolei_save_settings_action','zolei_save_settings_nonce');
        update_option('zolei_contact_email', sanitize_email(wp_unslash($_POST['zolei_contact_email'] ?? '')));
        update_option('zolei_contact_phone', sanitize_text_field(wp_unslash($_POST['zolei_contact_phone'] ?? '')));
        update_option('zolei_hero_subline', sanitize_text_field(wp_unslash($_POST['zolei_hero_subline'] ?? '')));
        $gallery_raw = trim((string) wp_unslash($_POST['zolei_gallery_images'] ?? ''));
        $gallery = array_values(array_filter(array_map('esc_url_raw', preg_split('/\r?\n/', $gallery_raw))));
        if ($gallery) { update_option('zolei_gallery_images', $gallery); }
        $saved = true;
    }
    $gallery = implode("\n", zolei_gallery_images());
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Zolei.lv theme settings','zolei-react'); ?></h1>
      <?php if ($saved): ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.','zolei-react'); ?></p></div><?php endif; ?>
      <p><?php esc_html_e('These options feed the React headless homepage and default BBuilder content. Pages remain editable in Gutenberg / WP BBuilder.','zolei-react'); ?></p>
      <form method="post">
        <?php wp_nonce_field('zolei_save_settings_action','zolei_save_settings_nonce'); ?>
        <table class="form-table" role="presentation">
          <tr><th><label for="zolei_contact_email"><?php esc_html_e('Contact email','zolei-react'); ?></label></th><td><input class="regular-text" id="zolei_contact_email" name="zolei_contact_email" value="<?php echo esc_attr(get_option('zolei_contact_email','info@zolei.lv')); ?>"></td></tr>
          <tr><th><label for="zolei_contact_phone"><?php esc_html_e('Contact phone','zolei-react'); ?></label></th><td><input class="regular-text" id="zolei_contact_phone" name="zolei_contact_phone" value="<?php echo esc_attr(get_option('zolei_contact_phone','')); ?>"></td></tr>
          <tr><th><label for="zolei_hero_subline"><?php esc_html_e('Hero subline','zolei-react'); ?></label></th><td><input class="large-text" id="zolei_hero_subline" name="zolei_hero_subline" value="<?php echo esc_attr(get_option('zolei_hero_subline','Mārupe. Labvēlīgam lidojumam teicama starta vieta')); ?>"></td></tr>
          <tr><th><label for="zolei_gallery_images"><?php esc_html_e('Gallery image URLs','zolei-react'); ?></label></th><td><textarea class="large-text code" id="zolei_gallery_images" name="zolei_gallery_images" rows="8"><?php echo esc_textarea($gallery); ?></textarea><p class="description"><?php esc_html_e('One image URL per line. The homepage slider, load-more gallery grid and lightbox use these URLs.','zolei-react'); ?></p></td></tr>
        </table>
        <p><button type="submit" name="zolei_save_settings" class="button button-primary button-hero"><?php esc_html_e('Save settings','zolei-react'); ?></button></p>
      </form>
    </div>
    <?php
}
