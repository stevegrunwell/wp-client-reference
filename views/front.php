<?php
/**
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */
?>

<div class="wrap columns-2">
  <h2>Help Articles</h2>

  <div id="poststuff">
    <div id="side-info-column" class="inner-sidebar">
      <div id="side-sortables" class="meta-box-sortables">
        <div class="postbox">
          <div class="handlediv" title="Click to toggle"><br /></div>
          <h3 class="hndle"><span>Table of Contents</span></h3>
          <div class="inside">
            <ul class="tableofcontents">
              <?php echo $this->list_articles(); ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>