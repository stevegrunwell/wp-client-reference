<?php
/**
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */
?>

<div class="wrap columns-2 wpclientref">
  <h2><?php echo $this->settings['menu_page_title']; ?></h2>

  <div id="poststuff" class="metabox-holder has-right-sidebar">

    <?php include $this->get_template_path('sidebar.php'); ?>

    <div id="post-body">
      <div id="post-body-content">
      <?php foreach( $this->get_articles() as $post ): setup_postdata($post); ?>

        <div class="article">
          <h3><a href="<?php echo $this->article_permalink($post->ID); ?>"><?php the_title(); ?></a></h3>
          <?php the_excerpt(); ?>
        </div>

      <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>