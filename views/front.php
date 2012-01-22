<?php
/**
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */
?>

<div class="wrap columns-2">
  <h2><?php echo $this->settings['menu_page_title']; ?></h2>

  <div id="poststuff" class="metabox-holder has-right-sidebar">

    <?php include $this->get_template_path('sidebar.php'); ?>

    <div id="post-body">
      <div id="post-body-content">
        <p>Foo bar</p>
      </div>
    </div>

  </div>
</div>