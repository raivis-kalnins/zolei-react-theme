<form role="search" method="get" class="zole-default-search-form" action="<?php echo esc_url(home_url('/')); ?>">
  <label class="screen-reader-text" for="s"><?php echo esc_html(zolei_i18n('Meklēt','Search')); ?></label>
  <input type="search" id="s" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php echo esc_attr(zolei_i18n('Meklēt vietnē...', 'Search the site...')); ?>">
  <button type="submit"><?php echo esc_html(zolei_i18n('Meklēt','Search')); ?></button>
</form>