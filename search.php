<?php get_header(); ?>
<main class="zole-page-content zole-search-page">
  <div class="container">
    <div class="zole-search-hero">
      <div class="zole-kicker" style="color:var(--green-700);"><?php echo esc_html(zolei_i18n('Meklēšana','Search')); ?></div>
      <h1 class="entry-title"><?php printf(esc_html(zolei_i18n('Meklēšanas rezultāti: %s','Search results: %s')), '<span>'.esc_html(get_search_query()).'</span>'); ?></h1>
      <?php get_search_form(); ?>
    </div>
    <div class="row g-4">
      <?php if (have_posts()): while(have_posts()): the_post(); ?>
        <div class="col-md-6 col-lg-4">
          <article <?php post_class('zole-card zole-search-card'); ?>>
            <div class="zole-icon"><?php echo get_post_type()==='zolei_pdf' ? '📄' : '♣'; ?></div>
            <h2 class="h4"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 22)); ?></p>
            <a class="fw-bold text-success" href="<?php the_permalink(); ?>"><?php echo esc_html(zolei_i18n('Atvērt','Open')); ?> →</a>
          </article>
        </div>
      <?php endwhile; else: ?>
        <div class="col-lg-8"><div class="zole-panel"><h2><?php echo esc_html(zolei_i18n('Nekas netika atrasts.','Nothing found.')); ?></h2><p><?php echo esc_html(zolei_i18n('Pamēģiniet citu atslēgvārdu vai apskatiet turnīru kalendāru.','Try another keyword or browse the tournament calendar.')); ?></p><a class="zole-btn zole-btn-green" href="<?php echo esc_url(home_url('/#calendar')); ?>"><?php echo esc_html(zolei_i18n('Skatīt kalendāru','View calendar')); ?></a></div></div>
      <?php endif; ?>
    </div>
    <div class="mt-5"><?php the_posts_pagination(array('mid_size'=>2)); ?></div>
  </div>
</main>
<?php get_footer(); ?>