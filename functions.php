<?php
/**
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.4
 */

/**
 * WP Client Reference class
 *
 * Honestly this is more of a namespaced set of functions than a true OOP class, but we won't be too picky when
 * it comes to plugins that could operate in any number of WordPress environments/configurations.
 *
 * @var public array $settings The plugin settings that get stored in wp_options
 */
class WPClientReference {
  public $settings;

  /**
   * Class constructor
   *
   * Perform the following operations:
   * 1. Get our options from wp_options, store them in $this->settings
   * 2. Register our custom taxonomies for our custom post type (copies of categories and tags)
   * 3. Register our custom post type
   * 4. Add the settings page and register our settings
   * 5. Register the client-facing "Knowledgebase" menu
   *
   * @return void
   */
  public function __construct(){
    $this->settings = get_option('wpclientref_settings');
    $this->register_category_taxonomy();
    $this->register_tag_taxonomy();
    $this->register_post_type();

    // Optional but it cleans up the "Help Articles" view
    add_filter(sprintf('manage_%s_posts_columns', $this->settings['post_type']), array(&$this, 'set_article_columns'));
    add_action('manage_posts_custom_column', 'get_article_column_content', 10, 2);

    add_action('admin_menu', array(&$this, 'add_settings_page'));
    add_action('admin_init', array(&$this, 'register_settings'));

    add_action('admin_menu', array(&$this, 'register_articles_menu'));
    return;
  }

  /**
   * Get the default plugin settings
   *
   * Settings:
   * menu_page_title: (string) The title of the (client-facing) top-level menu page
   * menu_position: (int) The menu's position; lower numbers = higher in the list
   * post_type: (string) The slug for the WordPress custom post type
   *
   * @return array
   */
  public function get_default_settings(){
    return array(
      'menu_page_title' => 'Knowledgebase',
      'menu_position' => 50,
      'post_type' => 'client_reference'
    );
  }

  /**
   * Functions to complete when the plugin is installed:
   * 1. Store our default settings if wpclientref_settings isn't already present
   *
   * @return void
   * @uses get_option()
   * @uses add_option()
   * @uses WPClientReference::get_default_settings()
   */
  public function install(){
    if( !get_option('wpclientref_settings') ){
      add_option('wpclientref_settings', self::get_default_settings());
    }
    return;
  }

  /**
   * Functions to run when the plugin is uninstalled
   *
   */
  public function uninstall(){
    //delete_option();
  }

  /**
   * Register wpclientref_category taxonomy to use with $this->settings['post_type']
   * @return object
   * @uses register_taxonomy()
   */
  public function register_category_taxonomy(){
    $args = array(
      'label' => 'Help Categories',
      'labels' => array(
        'name' => 'Categories',
        'singular_name' => 'Category'
      ),
      'public' => true,
      'hierarchical' => true
    );
    return register_taxonomy('wpclientref_category', $this->settings['post_type'], $args);
  }

  /**
   * Register wpclientref_category taxonomy to use with $this->settings['post_type']
   * @return object
   * @uses register_taxonomy()
   */
  public function register_tag_taxonomy(){
    $args = array(
      'label' => 'Help Tags',
      'labels' => array(
        'name' => 'Tags',
        'singular_name' => 'Tag'
      ),
      'public' => true,
      'hierarchical' => false
    );
    return register_taxonomy('wpclientref_tag', $this->settings['post_type'], $args);
  }

  /**
   * Register the client_reference custom post type
   * @return object
   * @uses register_post_type()
   */
  public function register_post_type(){
    $args = array(
      'labels' => array(
        'name' => 'Help Articles',
        'singular_name' => 'Help Article',
        'add_new_item' => 'Add New Article',
        'edit_item' => 'Edit Article',
        'new_item' => 'New Article',
        'search_items' => 'Search Articles',
        'not_found' => 'No articles found',
        'not_found_in_trash' => 'No articles found',
        'parent_item_colon' => 'Parent Article'
      ),
      'description' => 'Help documents',
      'public' => false,
      'show_ui' => true,
      // TODO: Capabilities
      'hierarchical' => true,
      'supports' => array('title', 'editor', 'author', 'excerpt', 'revisions', 'page-attributes'),
      'taxonomies' => array('wpclientref_category', 'wpclientref_tag'),
      'can_export' => true
    );
    return register_post_type($this->settings['post_type'], $args);
  }

  /**
   * Set the columns on the "Help Articles" screen
   * @param array $cols The columns passed during the manage_{$this->settings['post_type']}_posts_column action
   * @return array
   */
  public function set_article_columns($cols){
    $cols = array(
      'cb' => '<input type="checkbox" />',
      'title' => 'Title',
      'article_excerpt' => 'Article Excerpt',
      'date' => 'Date'
    );
    return $cols;
  }

  /**
   * Populate the columns we set in $this->set_article_columns()
   * TODO: Make this method work (low priority)
   * @param str $col The column name
   * @param int $id The post ID
   * @return void
   */
  public function get_article_column_content($col, $id){
    switch($col):
      case 'title':
        the_title();
        break;

      case 'article_excerpt':
        the_excerpt();
        break;

      case 'date':

        break;
    endswitch;
    return;
  }

  /**
   * Create the plugin settings page
   * @return void
   * @uses add_submenu_page()
   */
  public function add_settings_page(){
    add_submenu_page('edit.php?post_type=' . $this->settings['post_type'], 'Help Articles Options', 'Options', 'manage_options', 'wpclientref-settings', array(&$this, 'load_settings_view'));
    return;
  }

  /**
   * Load the plugin settings page
   * @global WPCLIENTREF_VIEWS_DIR
   * @return void
   */
  public function load_settings_view(){
    include WPCLIENTREF_VIEWS_DIR . 'admin-options.php';
    return;
  }

  /**
   * Register the plugin settings page with the WordPress Settings API
   * @return void
   * @uses register_setting()
   * @uses add_settings_section()
   * @uses add_settings_field()
   */
  public function register_settings(){
    register_setting('wpclientref_settings', 'wpclientref_settings', array(&$this, 'validate_settings'));
    add_settings_section('wpclientref_settings_main', 'Basic Settings', array(&$this, 'load_settings_view_main'), 'wpclientref_settings_page');
    add_settings_field('menu_page_title', 'Menu page title', array(&$this, 'setting_text_menu_page_title'), 'wpclientref_settings_page', 'wpclientref_settings_main');
    add_settings_field('menu_position', 'Menu position', array(&$this, 'setting_text_menu_position'), 'wpclientref_settings_page', 'wpclientref_settings_main');
    add_settings_section('wpclientref_settings_advanced', 'Advanced Settings', array(&$this, 'load_settings_view_advanced'), 'wpclientref_settings_page');
    add_settings_field('post_type', 'Custom post type', array(&$this, 'setting_text_post_type'), 'wpclientref_settings_page', 'wpclientref_settings_advanced');
  }

  /**
   * Write the text before wpclientref_settings_main settings section
   * @return void
   */
  public function load_settings_view_main(){
    echo '<p>Main settings will go here</p>';
    return;
  }

  /**
   * Write the text before wpclientref_settings_advanced settings section
   * @return void
   */
  public function load_settings_view_advanced(){
    echo '<p><em>These settings should only be changed if you know what you\'re doing!</em></p>';
    return;
  }

  /**
   * Magic method to help handle some of the more repetitive method calls (setting_{field_name} for a text input, for instance)
   * @param str $function The method called
   * @param array $args Any arguments passed to the method
   * @return void
   * @uses WPClientReference::setting_text_input()
   */
  public function __call($function, $args){
    if( preg_match('/^setting_text_(.+)/i', $function, $match) ){
      $this->setting_text_input($match['1']);
    } else {
      // Invalid method call
      // TODO: Throw a proper error
      die('Invalid method call');
    }
    return;
  }

  /**
   * Create a basic text input for the settings page
   * @param str $key The setting key
   * @return void
   */
  public function setting_text_input($key){
    echo sprintf('<input name="wpclientref_settings[%s]" id="wpclientref_settings[%s]" type="text" value="%s" />', $key, $key, $this->settings[$key]);
    return true;
  }

  /**
   * Validate our settings
   * @param array $post POST data
   * @return array
   * @uses update_option()
   */
  public function validate_settings($post){
    // Apparently the WordPress settings API doesn't have a great error reporting system yet. In the meantime, we'll fake it
    // by creating a second entry in wp_options to store status messages on a per-user basis
    $status = array(
      'status' => false,
      'messages' => array()
    );

    $save = array();

    // Menu page title
    $post['menu_page_title'] = trim(filter_var($post['menu_page_title'], FILTER_SANITIZE_STRING));
    if( $post['menu_page_title'] == '' ){
      $status['messages'][] = 'Menu page title cannot be empty';
    } else {
      $save['menu_page_title'] = $post['menu_page_title'];
    }

    // Post type
    $post['post_type'] = preg_replace('/[^a-z0-9_]/i', '', strtolower($post['post_type']));
    if( strlen($post['post_type']) > 20 ){
      $status['messages'][] = 'Post type name cannot be longer than 20 characters';
    } else if( $post['post_type'] == '' ){
      $status['messages'][] = 'Post type cannot be empty';
    } else {
      $save['post_type'] = $post['post_type'];
    }

    // If $status['message'] is empty we've passed all of our validations
    if( empty($status['messages']) ){
      $status['status'] = true;
      $status['messages'][] = 'Your settings have been saved.';
    }
    update_option($this->get_settings_status_key(), $status);

    return $post;
  }

  /**
   * Get the key for the field in wp_options for the status message for the current user
   * @global WPCLIENTREF_STATUS_KEY_PATTERN
   * @return str
   */
  public function get_settings_status_key(){
    $current_user = wp_get_current_user();
    return sprintf(WPCLIENTREF_STATUS_KEY_PATTERN, $current_user->ID);
  }

  /**
   * Get the status of the update to the plugin settings by this user (WPCLIENTREF_STATUS_KEY_PATTERN)
   * This will also set the row in wp_options to null
   * @return array
   * @uses WPClientReference::get_settings_status_key()
   * @uses get_option()
   * @uses update_option()
   */
  public function get_settings_status(){
    $key = $this->get_settings_status_key();
    $status = get_option($key);
    update_option($key, null);
    return $status;
  }

  /**
   * Register the "public" articles menu
   * @return void
   * @uses add_menu_page()
   */
  public function register_articles_menu(){
    add_menu_page('Help', $this->settings['menu_page_title'], 'edit_posts', 'wpclientref_articles', array(&$this, 'load_front_page'), '', 70);
    //add_submenu_page('wpclientref_articles', 'Help', 'Help Articles', 'edit_posts', 'wpclientref_articles', array(&$this, 'load_front_page'));
  }

  /**
   * Load the front page of the articles menu (views/front.php)
   * @return void
   */
  public function load_front_page(){
    include WPCLIENTREF_VIEWS_DIR . 'front.php';
    return;
  }

}

?>