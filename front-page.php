<?php get_header(); ?>
<?php $payload = function_exists('zolei_home_payload') ? zolei_home_payload() : array(); ?>
<script id="zole-headless-preload" type="application/json"><?php echo wp_json_encode($payload); ?></script>
<div id="zole-headless-root" aria-live="polite"><section class="zole-hero"><div class="container"><p><?php esc_html_e('Loading homepage...', 'zolei-react'); ?></p></div></section></div>
<noscript><main class="zole-page-content"><div class="container"><h1><?php echo esc_html(zolei_i18n('Zolīte ar raksturu.','Zolīte with character.')); ?></h1><p><?php echo esc_html(zolei_i18n('Lūdzu ieslēdziet JavaScript, lai skatītu interaktīvo sākumlapu.','Please enable JavaScript to view the interactive homepage.')); ?></p></div></main></noscript>
<?php get_footer(); ?>