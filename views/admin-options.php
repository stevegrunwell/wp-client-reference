<?php
/**
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */

$status = $this->get_user_status();
?>

<div class="wrap">
  <h2>Article Options</h2>

<?php if( isset($_GET['settings-updated']) && is_array($status) && !empty($status) ): ?>
  <div id="message" class="<?php echo ( $status['status'] ? 'updated' : 'error' ); ?> below-h2">
    <p><?php echo implode('</p><p>', $status['messages']); ?></p>
  </div>
<?php endif; ?>

  <form action="options.php" method="post">
    <?php settings_fields('wpclientref_settings'); ?>
    <?php do_settings_sections('wpclientref_settings_page'); ?>
    <p><input name="submit" type="submit" class="button-primary" value="Save Changes" /></p>
  </form>

</div>