<?php if (!defined('ABSPATH')) { exit; }
$share_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
if (is_front_page()) { $share_url = home_url('/'); }
$share_title = get_bloginfo('name') ?: 'Zolei.lv';
?>
<footer class="zole-footer">
  <div class="container zole-footer-grid">
    <div class="zole-footer-brand">
      <strong><?php echo esc_html($share_title); ?></strong>
      <span><?php echo esc_html(zolei_i18n('Latvijas zoles spēlētāju vieta','Latvian Zolīte player hub')); ?></span>
    </div>
    <div class="zole-footer-contact">
      <span><?php echo esc_html(zolei_i18n('Saziņa','Contact')); ?></span>
      <a href="mailto:<?php echo esc_attr(get_option('zolei_contact_email','info@zolei.lv')); ?>"><?php echo esc_html(get_option('zolei_contact_email','info@zolei.lv')); ?></a>
    </div>
    <div class="zole-footer-share" aria-label="<?php echo esc_attr(zolei_i18n('Dalīties','Share')); ?>">
      <span><?php echo esc_html(zolei_i18n('Dalīties','Share')); ?></span>
      <div class="zole-footer-socials">
        <a href="<?php echo esc_url('https://wa.me/?text=' . rawurlencode($share_title . ' - ' . $share_url)); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">☏</a>
        <a href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($share_url)); ?>" target="_blank" rel="noopener" aria-label="Facebook">f</a>
        <a href="<?php echo esc_url('https://twitter.com/intent/tweet?url=' . rawurlencode($share_url) . '&text=' . rawurlencode($share_title)); ?>" target="_blank" rel="noopener" aria-label="X">𝕏</a>
        <button type="button" class="zole-copy-link" data-copy-url="<?php echo esc_url($share_url); ?>" aria-label="<?php echo esc_attr(zolei_i18n('Kopēt saiti','Copy link')); ?>">⛓</button>
      </div>
    </div>
  </div>
</footer></div><?php wp_footer(); ?></body></html>
