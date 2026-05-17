<?php get_header(); ?>
<main class="zole-page-content zole-not-found">
  <div class="container">
    <div class="zole-404-card">
      <div class="zole-404-symbol">♠</div>
      <div class="zole-kicker"><?php echo esc_html(zolei_i18n('404 kļūda','404 error')); ?></div>
      <h1><?php echo esc_html(zolei_i18n('Šī kārts nav galdā.','This card is not on the table.')); ?></h1>
      <p><?php echo esc_html(zolei_i18n('Lapa nav atrasta. Izmantojiet meklēšanu vai atgriezieties sākumlapā, lai atrastu turnīrus, rezultātus un noteikumus.','The page was not found. Use search or return to the homepage to find tournaments, results and rules.')); ?></p>
      <div class="zole-404-search"><?php get_search_form(); ?></div>
      <div class="zole-actions justify-content-center"><a class="zole-btn zole-btn-gold" href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(zolei_i18n('Uz sākumlapu','Back home')); ?></a><a class="zole-btn zole-btn-ghost" href="<?php echo esc_url(home_url('/#calendar')); ?>"><?php echo esc_html(zolei_i18n('Turnīru kalendārs','Tournament calendar')); ?></a></div>
    </div>
  </div>
</main>
<?php get_footer(); ?>