<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<div class="zole-page">
<header class="zole-header">
  <div class="container zole-nav-inner">
    <?php zolei_brand(); ?>
    <button class="zole-menu-toggle" type="button" aria-expanded="false" aria-controls="zolePrimaryMenu" aria-label="<?php echo esc_attr(zolei_i18n('Atvērt izvēlni','Open menu')); ?>"><span></span><span></span><span></span></button>
    <nav id="zolePrimaryMenu" class="zole-menu" aria-label="<?php esc_attr_e('Primary menu','zolei-react'); ?>"><?php zolei_nav_menu(); ?></nav>
    <div class="zole-header-actions">
      <div class="zole-ajax-search" role="search">
        <button class="zole-search-toggle" type="button" aria-expanded="false" aria-controls="zoleHeaderSearch" aria-label="<?php echo esc_attr(zolei_i18n('Atvērt meklēšanu','Open search')); ?>">⌕</button>
        <form id="zoleHeaderSearch" class="zole-search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" autocomplete="off">
          <input type="search" name="s" placeholder="<?php echo esc_attr(zolei_i18n('Meklēt...', 'Search...')); ?>" aria-label="<?php echo esc_attr(zolei_i18n('Meklēt vietnē','Search site')); ?>">
          <button type="submit"><?php echo esc_html(zolei_i18n('Meklēt','Search')); ?></button>
          <div class="zole-search-results" aria-live="polite"></div>
        </form>
      </div>
      <?php zolei_language_switcher(); ?>
    </div>
  </div>
</header>
