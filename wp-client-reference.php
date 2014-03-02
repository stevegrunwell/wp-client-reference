<?php
/**
 * Plugin Name: WP Client Reference
 * Plugin URI: http://stevegrunwell.com/wp-client-reference
 * Description: Create a reference guide for clients right in the WordPress administration area
 * Version: 0.5
 * Author: Steve Grunwell
 * Author URI: http://stevegrunwell.com
 * License: GPL2
 *
 * @author Steve Grunwell <stevegrunwell@gmail.com>
 * @package WordPress
 * @subpackage WP Client Reference
 * @version 0.5
 */

/**
 * WP Client Reference class
 */
class WP_Client_Reference {

  /**
   * The previous post type option name, used when the custom post type name changes
   */
  const PREV_POST_TYPE_OPTION_NAME = '_wpclientref_prev_post_type';

  /**
   * The plugin option key
   */
  const SETTINGS_OPTION_NAME = 'wpclientref_settings';

  /**
   * Track user statuses in this option
   */
  const STATUS_OPTION_NAME = '_wpclientref_settings_status';

  /**
   * @var array $settings The plugin settings
   */
  public $settings;

  /**
   * @var str $views_dir System path to the plugin's views directory
   */
  public $views_dir;

  /**
   * Class constructor
   *
   * Perform the following operations:
   * 1. Get our options from wp_options, store them in $this->settings
   * 3. Register our custom post type
   * 4. Add the settings page and register our settings
   * 5. Register the client-facing "Knowledgebase" menu
   *
   * @uses add_action()
   * @uses get_option()
   */
  public function __construct() {
    $this->views_dir = dirname( __FILE__ ) . '/views/';
    $this->settings = get_option( 'wpclientref_settings' );
    $this->register_post_type();

    // Optional but it cleans up the "Help Articles" view
    add_filter( sprintf( 'manage_%s_posts_columns', $this->settings['post_type'] ), array( &$this, 'set_article_columns' ) );

    // Register the admin pages
    add_action( 'admin_menu', array( &$this, 'update_post_type_redirect' ) );
    add_action( 'admin_menu', array( &$this, 'add_settings_page' ) );
    add_action( 'admin_init', array( &$this, 'register_settings' ) );

    add_action( 'admin_menu', array( &$this, 'register_articles_menu' ) );
    if( isset( $_GET['page'] ) && $_GET['page'] == 'wpclientref_articles' ) {
      add_filter( 'get_the_excerpt', array( &$this, 'custom_excerpt' ) );
      $this->load_scripts_styles();
    }
    return;
  }

  /**
   * Magic method to help handle some of the more repetitive method calls (setting_{field_name} for a text input, for instance)
   *
   * @param str $function The method called
   * @param array $args Any arguments passed to the method
   *
   * @uses WPClientReference::setting_text_input()
   * @uses WPClientReference::setting_checkbox_input()
   */
  public function __call( $function, $args ) {
    if ( preg_match( '/^setting_text_(.+)/i', $function, $match ) ) {
      $this->setting_text_input( $match['1'] );
    } elseif ( preg_match( '/^setting_checkbox_(.+)/i', $function, $match ) ) {
      $this->setting_checkbox_input( $match['1'] );
    } else {
      trigger_error( sprintf( 'Invalid method call: WPClientReference::%s()', $function ), E_USER_WARNING );
    }
  }

  /**
   * Create the plugin settings page
   *
   * @uses add_submenu_page()
   */
  public function add_settings_page() {
    add_submenu_page(
      'edit.php?post_type=' . $this->settings['post_type'],
      __( 'Help Articles Options', 'wpclientref' ),
      __( 'Options', 'wpclientref' ),
      'manage_options',
      'wpclientref-settings',
      array( &$this, 'load_settings_view' )
    );
  }

  /**
   * Get the article's permalink within the context of the admin area
   *
   * @param int $id The article ID
   * @return str
   *
   * @uses admin_url()
   */
  public function article_permalink( $id ) {
    return admin_url( sprintf( 'admin.php?page=wpclientref_articles&article_id=%d', $id ) );
  }

  /**
   * Output custom excerpts (mainly here to bypass other filters for "continue reading" style links
   *
   * @global $post
   * @param str $excerpt The excerpt text to be filtered
   * @return str
   *
   * @uses WPClientReference::article_permalink()
   * @uses get_permalink()
   */
  public function custom_excerpt( $excerpt ) {
    global $post;
    return str_replace( get_permalink(), $this->article_permalink( $post->ID ), $excerpt );
  }

  /**
   * Get articles for display (with optional filters)
   *
   * @param array $args Additional arguments to pass to get_pages()
   * @return array
   *
   * @uses get_pages()
   */
  public function get_articles( $args=array() ) {
    $defaults = array(
      'post_type' => $this->settings['post_type'],
      'sort_column' => 'menu_order',
      'sort_order' => 'ASC',
      'hierarchical' => true,
      'parent' => 0,
      'number' => 10,
      'offset' => 0,

    );
    return get_pages( array_merge( $defaults, $args ) );
  }

  /**
   * Populate the columns we set in $this->set_article_columns()
   *
   * @param str $col The column name
   * @param int $id The post ID
   * @return void
   */
  public function get_article_column_content( $col, $id ) {
    switch( $col ) {
      case 'article_excerpt':
        the_excerpt();
        break;
    }
  }

  /**
   * Create a list of breadcrumbs for articles
   *
   * @return str
   *
   * @uses get_the_title()
   */
  public function get_breadcrumbs() {
    global $post;

    $ancestors = array_reverse( $post->ancestors );
    $breadcrumbs = array( sprintf( '<li><a href="?page=wpclientref_articles">%s</a></li>', __( 'Home', 'wpclientref' ) ) );
    foreach ( $ancestors as $ancestor ) {
      $breadcrumbs[] = sprintf( '<li><a href="?page=wpclientrefarticles&article_id=%d">%s</a></li>', $ancestor, get_the_title( $ancestor ) );
    }
    $breadcrumbs[] = sprintf( '<li class="current-item">%s</li>', get_the_title() );
    return sprintf( '<ul class="subsubsub breadcrumbs">%s</ul>', implode( '', $breadcrumbs ) );
  }

  /**
   * Get the default plugin settings
   *
   * Settings:
   * hide_menu: (bool) Hide the client-facing menu
   * menu_page_title: (string) The title of the (client-facing) top-level menu page
   * menu_position: (int) The menu's position; lower numbers = higher in the list
   * post_type: (string) The slug for the WordPress custom post type
   *
   * @return array
   */
  public function get_default_settings() {
    return array(
      'hide_menu' => 0,
      'menu_page_title' => 'Knowledgebase',
      'menu_position' => 71,
      'post_type' => 'client_reference'
    );
  }

  /**
   * Get the status of the update to the plugin settings by this user (WPCLIENTREF_STATUS_KEY_PATTERN)
   * This will also set the row in wp_options to null
   *
   * @return mixed Either the status array or false
   *
   * @uses get_option()
   * @uses update_option()
   * @uses wp_get_current_user()
   */
  public function get_user_status() {
    $current_user = wp_get_current_user();
    $statuses = get_option( self::STATUS_OPTION_NAME );
    $status = ( isset( $statuses[ $current_user->ID ] ) ? $statuses[ $current_user->ID ] : false );
    $statuses[ $current_user->ID ] = array();
    update_option( self::STATUS_OPTION_NAME, $statuses );
    return $status;
  }

  /**
   * Get a list of all articles
   *
   * @return str
   *
   * @uses WPClientReference::article_permalink()
   * @uses wp_list_pages()
   */
  public function list_articles() {
    $opts = array(
      'post_type' => $this->settings['post_type'],
      'echo' => false,
      'sort_column' => 'menu_order',
      'title_li' => null
    );
    $pages = wp_list_pages( $opts );

    // This is awfully hackish but replace the links to stay in the admin area
    if ( preg_match_all( '/\<li.+page-item-([0-9]+).+href="(.[^"]+)"/i', $pages, $matches ) ) {
      foreach ( $matches['2'] as $k => $v ) {
        $pages = str_replace( sprintf( '"%s"', $v ), $this->article_permalink( $matches['1'][$k] ), $pages );
      }
    }
    return $pages;
  }

  /**
   * Load scripts and styles for the articles view
   *
   * @uses wp_enqueue_style()
   * @uses wp_enqueue_script()
   */
  public function load_scripts_styles() {
    wp_enqueue_style( 'wpclientref', $this->get_template_url( 'wpclientref.css' ), null, null, 'all' );
  }

  /**
   * Write the text before wpclientref_settings_advanced settings section
   */
  public function load_settings_view_advanced() {
    printf( '<p>If there are collisions with the custom post type of <code>%s</code> you can change it here:</p>', $this->settings['post_type'] );
  }

  /**
   * Load the plugin settings page
   */
  public function load_settings_view() {
    include $this->views_dir . 'admin-options.php';
    return;
  }

  /**
   * Write the text before wpclientref_settings_main settings section
   */
  public function load_settings_view_main() {
    // Nada
  }

  /**
   * View controller
   *
   * @return void
   *
   * @uses WPClientReference::get_template_path()
   * @uses get_post()
   */
  public function load_template() {
    global $post;
    $template = 'front.php';
    if ( isset( $_GET['article_id'] ) ) {
      if ( $post = get_post( intval( $_GET['article_id'] ) ) ) {
        $template = 'single.php';
      }
    }
    include $this->get_template_path( $template, false );
  }

  /**
   * Register the "public" articles menu
   *
   * @uses add_menu_page()
   */
  public function register_articles_menu() {
    if ( ! $this->settings['hide_menu'] ) {
      add_menu_page(
        __( 'Help', 'wpclientref' ),
        $this->settings['menu_page_title'],
        'edit_posts',
        'wpclientref_articles',
        array( &$this, 'load_template' ),
        'dashicons-editor-help',
        $this->settings['menu_position']
      );
    }
  }

  /**
   * Register the plugin settings page with the WordPress Settings API
   *
   * @uses register_setting()
   * @uses add_settings_section()
   * @uses add_settings_field()
   */
  public function register_settings() {
    register_setting( 'wpclientref_settings', 'wpclientref_settings', array( &$this, 'validate_settings' ) );
    add_settings_section(
      'wpclientref_settings_main',
      __( 'Basic Settings', 'wpclientref' ),
      array( &$this, 'load_settings_view_main' ),
      'wpclientref_settings_page'
    );
    add_settings_field(
      'menu_page_title',
      __( 'Menu page title', 'wpclientref' ),
      array( &$this, 'setting_text_menu_page_title' ),
      'wpclientref_settings_page',
      'wpclientref_settings_main'
    );
    add_settings_field(
      'menu_position',
      __( 'Menu position', 'wpclientref' ),
      array( &$this, 'setting_text_menu_position' ),
      'wpclientref_settings_page',
      'wpclientref_settings_main'
    );
    add_settings_field(
      'hide_menu',
      __( 'Hide menu', 'wpclientref' ),
      array( &$this, 'setting_checkbox_hide_menu' ),
      'wpclientref_settings_page',
      'wpclientref_settings_main'
    );
    add_settings_section(
      'wpclientref_settings_advanced',
      __( 'Change Custom Post Type', 'wpclientref' ),
      array( &$this, 'load_settings_view_advanced' ),
      'wpclientref_settings_page'
      );
    add_settings_field(
      'post_type',
      __( 'Custom post type', 'wpclientref' ),
      array( &$this, 'setting_text_post_type' ),
      'wpclientref_settings_page',
      'wpclientref_settings_advanced'
    );
  }

  /**
   * Create a checkbox for settings[hide_menu]
   *
   * @uses WPClientReference::setting_checkbox_input()
   */
  public function setting_checkbox_hide_menu() {
    $args = array(
      'label' => sprintf( __( 'Hide the "%s" menu item', 'wpclientref' ), $this->settings['menu_page_title'] )
    );
    $this->setting_checkbox_input( 'hide_menu', $args );
  }

  /**
   * Create a checkbox input for the settings page
   *
   * Available $args:
   * class: (str) CSS class to add to the input[type="checkbox"]
   * label: (str) Contents of a <label> element to wrap around the checkbox
   *
   * @param str $key The setting key
   * @param str $label Contents of a <label> element to wrap around the checkbox
   */
  public function setting_checkbox_input( $key, $args = array() ) {
    if ( isset( $args['label'] ) && $args['label'] != '' ) {
      echo '<label>';
    }
    printf( '<input name="wpclientref_settings[%s]" id="wpclientref_settings[%s]" type="checkbox" value="1" class="%s" %s />',
      $key,
      $key,
      ( isset( $args['class'] ) ? $args['class'] : '' ),
      ( isset( $this->settings[ $key ] ) && $this->settings[ $key ] ? 'checked="checked"' : '' )
    );
    if( isset( $args['label'] ) && $args['label'] != '' ) {
      printf( ' %s</label>', $args['label'] );
    }
  }

  /**
   * Create a basic text input for the settings page
   *
   * Available $args:
   * class: (str) CSS class to add to the input
   *
   * @param str $key The setting key
   * @param array $args Additional arguments
   */
  public function setting_text_input( $key, $args = array() ) {
    printf( '<input name="wpclientref_settings[%s]" id="wpclientref_settings[%s]" type="text" value="%s" class="%s" />',
      $key,
      $key,
      $this->settings[$key],
      ( isset( $args['class'] ) ? $args['class'] : '' )
    );
  }

  /**
   * Create the text input for settings[post_type]
   *
   * @uses WPClientReference::setting_text_input()
   */
  public function setting_text_post_type() {
    $args = array(
      'class' => 'code'
    );
    $this->setting_text_input( 'post_type', $args );
  }

  /**
   * Redirect the user after successfully changing the settings[post_type]
   * Read more: https://github.com/stevegrunwell/wp-client-reference/issues/1
   *
   * @uses get_option()
   * @uses delete_option()
   */
  public function update_post_type_redirect() {
    if ( $type = get_option( self::PREV_POST_TYPE_OPTION_NAME ) ) {
      $url = sprintf( 'edit.php?post_type=%s&page=wpclientref-settings&settings-updated=true', $this->settings['post_type'] );
      delete_option( self::PREV_POST_TYPE_OPTION_NAME );
      header( "Location: $url" );
      exit;
    }
  }

  /**
   * Validate our settings
   *
   * @param array $post POST data
   * @return array
   *
   * @uses WPClientReference::update_post_type()
   * @uses WPClientReference::set_user_status()
   * @uses update_option()
   */
  public function validate_settings( $post ) {
    // Apparently the WordPress settings API doesn't have a great error reporting system yet. In the meantime, we'll fake it
    // by creating a second entry in wp_options to store status messages on a per-user basis
    $status = array(
      'status' => false,
      'messages' => array()
    );

    // By default, just re-save the existing $this->settings. We'll override this with each successful validation
    $save = $this->settings;

    // menu_page_title
    $post['menu_page_title'] = trim( filter_var( $post['menu_page_title'], FILTER_SANITIZE_STRING ) );
    if ( $post['menu_page_title'] == '' ) {
      $status['messages'][] = __( 'Menu page title cannot be empty', 'wpclientref' );
    } else {
      $save['menu_page_title'] = $post['menu_page_title'];
    }

    // menu_position
    $post['menu_position'] = intval( $post['menu_position'] );
    if ( $post['menu_position'] < 0 ) {
      $status['messages'][] = __( 'Menu position cannot be negative', 'wpclientref' );
    } else {
      $save['menu_position'] = $post['menu_position'];
    }

    // hide_menu
    $save['hide_menu'] = ( isset( $post['hide_menu'] ) && intval( $post['hide_menu'] ) > 0 );

    // post_type
    $post['post_type'] = preg_replace( '/[^a-z0-9_]/i', '', strtolower( $post['post_type'] ) );
    if ( strlen( $post['post_type'] ) > 20 ) {
      $status['messages'][] = __( 'Post type name cannot be longer than 20 characters', 'wpclientref' );
    } elseif ( $post['post_type'] == '' ) {
      $status['messages'][] = __( 'Post type cannot be empty', 'wpclientref' );
    } else {
      // If settings[post_type] has changed we need to update the wp_posts table
      if ( $save['post_type'] !== $post['post_type'] ) {
        update_option( PREV_POST_TYPE_OPTION_NAME, $save['post_type'] );
        $this->update_post_type( $save['post_type'], $post['post_type'] );
      }
      $save['post_type'] = $post['post_type'];
    }

    // If $status['message'] is empty we've passed all of our validations
    if ( empty( $status['messages'] ) ) {
      $status['status'] = true;
      $status['messages'][] = __( 'Your settings have been saved.', 'wpclientref' );
    }
    $this->set_user_status( $status );

    return $save;
  }

  /**
   * Functions to complete when the plugin is installed:
   * 1. Store our default settings if wpclientref_settings isn't already present
   *
   * @uses get_option()
   * @uses add_option()
   * @uses WPClientReference::get_default_settings()
   */
  public static function install() {
    if ( ! get_option( self::SETTINGS_OPTION_NAME ) ) {
      add_option( self::SETTINGS_OPTION_NAME, self::get_default_settings() );
    }
  }

  /**
   * Functions to run when the plugin is uninstalled
   *
   * @uses delete_option()
   */
  public static function uninstall() {
    delete_option( self::SETTINGS_OPTION_NAME );
    delete_option( self::STATUS_OPTION_NAME );
  }

  /**
   * Determine if a file exists in the site's current theme
   *
   * @param str $file The view filename
   * @return bool
   *
   * @uses trailingslashit()
   */
  protected function file_exists_in_theme( $file ) {
    $filepath = trailingslashit( TEMPLATEPATH ) . '/wpclientref-views/' . $file;
    return file_exists( $filepath );
  }

  /**
   * Check for a file within the theme's directory. If it exists, use that rather than the default views/{filename}
   *
   * @param str $file The filename to look for
   * @param bool $path_only Return only the path or include the file at the end?
   * @return str
   *
   * @uses WPClientReference::file_exists_in_theme()
   * @uses trailingslashit()
   */
  protected function get_template_path( $file, $path_only = false ) {
    $theme_path = trailingslashit( TEMPLATEPATH ) . 'wpclientref-views/';
    $path = ( $this->file_exists_in_theme( $file ) ? $theme_path : $this->views_dir );
    if ( ! $path_only ) {
      $path .= $file;
    }
    return $path;
  }

  /**
   * Similar to WPClientReference::get_template_path() but return a URL instead of a system path
   *
   * @param str $file The filename to look for
   * @param bool $path_only Return only the path or include the file at the end?
   * @return str
   *
   * @uses WPClientReference::file_exists_in_theme()
   * @uses get_bloginfo()
   * @uses plugins_url()
   * @uses trailingslashit()
   */
  protected function get_template_url( $file, $path_only = false ) {
    if ( $this->file_exists_in_theme( $file ) ) {
      $url = trailingslashit( get_template_directory_uri() ) . 'wpclientref-views/';
    } else {
      $url = plugins_url( null, __FILE__ ) . '/views/';
    }

    if ( ! $path_only ) {
      $url .= $file;
    }
    return $url;
  }

  /**
   * Register the client_reference custom post type
   *
   * @return object
   *
   * @uses register_post_type()
   */
  public function register_post_type() {
    $args = array(
      'labels' => array(
        'name' => __( 'Articles', 'wpclientref' ),
        'singular_name' => __( 'Article', 'wpclientref' ),
        'add_new_item' => __( 'Add New Article', 'wpclientref' ),
        'edit_item' => __( 'Edit Article', 'wpclientref' ),
        'new_item' => __( 'New Article', 'wpclientref' ),
        'search_items' => __( 'Search Articles', 'wpclientref' ),
        'not_found' => __( 'No articles found', 'wpclientref' ),
        'not_found_in_trash' => __( 'No articles found', 'wpclientref' ),
        'parent_item_colon' => __( 'Parent Article', 'wpclientref' ),
      ),
      'description' => __( 'WP Client Reference articles', 'wpclientref' ),
      'public' => false,
      'show_ui' => true,
      'menu_icon' => 'dashicons-admin-page',
      'hierarchical' => true,
      'rewrite' => false,
      'supports' => array( 'title', 'editor', 'author', 'excerpt', 'revisions', 'page-attributes' ),
      'can_export' => true
    );
    return register_post_type( $this->settings['post_type'], $args );
  }

  /**
   * Set the columns on the "Help Articles" screen
   *
   * @param array $cols The columns passed during the manage_{$this->settings['post_type']}_posts_column action
   * @return array
   */
  public function set_article_columns( $cols ) {
    return array(
      'cb' => '<input type="checkbox" />',
      'title' => 'Title',
      //'article_excerpt' => 'Article Excerpt',
      'date' => 'Date'
    );
  }

  /**
   * Set $status in self::STATUS_OPTION_NAME for the current user
   *
   * @param array $status Status from WPClientReference::validate_settings()
   * @return bool
   *
   * @uses wp_get_current_user()
   * @uses get_option()
   * @uses update_option()
   */
  protected function set_user_status( $status = array() ) {
    $current_user = wp_get_current_user();
    $statuses = get_option( self::STATUS_OPTION_NAME );
    if( ! $statuses || ! is_array( $statuses ) ) {
      $statuses = array();
    }
    $statuses[ $current_user->ID ] = $status;
    return update_option( self::STATUS_OPTION_NAME, $statuses );
  }

  /**
   * Update all posts of type $src to type $dest
   *
   * @global $wpdb
   * @param str $src The post type to convert from
   * @param str $dest The post type to convert to
   * @return bool
   */
  protected function update_post_type( $src = '', $dest = '' ) {
    global $wpdb;
    if ( $src != '' && $dest != '' ) {
      if ( $wpdb->update( $wpdb->prefix . 'posts', array( 'post_type' => $dest ), array( 'post_type' => $src ), '%s' ) ) {
        return true;
      }
    }
    return false;
  }

}

/**
 * Bootstrap the plugin
 */
function wpclientref_init() {
  $GLOBALS['wpclientref'] = new WP_Client_Reference;
}

// Only get this party started in the admin area
if ( is_admin() ) {
  add_action( 'init', 'wpclientref_init' );
}

/** Plugin activation and uninstallation procedures */
register_activation_hook( __FILE__, array( 'WP_Client_Reference', 'install' ) );
register_uninstall_hook( __FILE__, array( 'WP_Client_Reference', 'uninstall' ) );