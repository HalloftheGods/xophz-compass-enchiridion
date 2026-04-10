<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/admin
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Enchiridion_Admin {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xophz-compass-enchiridion-admin.css', array(), $this->version, 'all' );
  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xophz-compass-enchiridion-admin.js', array( 'jquery' ), $this->version, false );
  }

  /**
   * Add menu item 
   *
   * @since    1.0.0
   */
  public function addToMenu() {
    Xophz_Compass::add_submenu( $this->plugin_name );
  }

  /**
   * Get all recipes.
   */
  public function get_recipes() {
    $recipes = Xophz_Compass_Enchiridion_Post_Type::get_all_recipes();
    
    // Group by category
    $grouped = array();
    foreach ( $recipes as $recipe ) {
      $category = $recipe['category'];
      if ( ! isset( $grouped[ $category ] ) ) {
        $grouped[ $category ] = array();
      }
      $grouped[ $category ][] = $recipe;
    }

    Xophz_Compass::output_json( array(
      'success' => true,
      'data'    => array(
        'recipes' => $recipes,
        'grouped' => $grouped,
        'stats'   => array(
          'total'   => count( $recipes ),
          'enabled' => count( array_filter( $recipes, function( $r ) { 
            return $r['enabled']; 
          } ) ),
        ),
      ),
    ) );
  }

  /**
   * Toggle a recipe's enabled state.
   */
  public function toggle_recipe() {
    $input = Xophz_Compass::get_input_json();
    if ( null === $input || ! is_object( $input ) ) {
      $input = (object) $_POST;
    }
    
    if ( empty( $input->id ) ) {
      Xophz_Compass::output_json( array(
        'success' => false,
        'message' => 'Recipe ID is required',
      ) );
      return;
    }

    $enabled = isset( $input->enabled ) ? (bool) $input->enabled : false;
    $result = Xophz_Compass_Enchiridion_Post_Type::toggle_recipe( $input->id, $enabled );

    Xophz_Compass::output_json( array(
      'success' => $result,
      'data'    => array(
        'id'      => $input->id,
        'enabled' => $enabled,
      ),
    ) );
  }

  /**
   * Get custom CSS and JS.
   */
  public function get_custom_code() {
    Xophz_Compass::output_json( array(
      'success' => true,
      'data'    => array(
        'css' => get_option( 'xophz_compass_enchiridion_custom_css', '' ),
        'js'  => get_option( 'xophz_compass_enchiridion_custom_js', '' ),
      ),
    ) );
  }

  /**
   * Save custom CSS and JS.
   */
  public function save_custom_code() {
    $input = Xophz_Compass::get_input_json();
    if ( null === $input || ! is_object( $input ) ) {
      $input = (object) $_POST;
    }
    
    if ( isset( $input->css ) ) {
      update_option( 'xophz_compass_enchiridion_custom_css', wp_unslash( $input->css ) );
    }
    
    if ( isset( $input->js ) ) {
      update_option( 'xophz_compass_enchiridion_custom_js', wp_unslash( $input->js ) );
    }

    Xophz_Compass::output_json( array(
      'success' => true,
      'message' => 'Custom code saved successfully',
    ) );
  }
}
