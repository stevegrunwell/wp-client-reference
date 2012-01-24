<?php
/**
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */

setup_postdata($post);
?>

<div class="wrap columns-2 wpclientref">
  <h2><?php the_title(); ?></h2>
  <?php echo $this->get_breadcrumbs(); ?>

  <div id="poststuff" class="metabox-holder has-right-sidebar">

    <?php include $this->get_template_path('sidebar.php'); ?>

    <div id="post-body">
      <div id="post-body-content">
        <?php the_content(); ?>
      </div>
    </div>

    <div class="clear"></div>

  </div>
</div>