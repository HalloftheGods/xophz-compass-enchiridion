<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/includes
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Enchiridion_Activator {

  /**
   * Activate the plugin.
   *
   * @since    1.0.0
   */
  public static function activate() {
    if ( ! class_exists( 'Xophz_Compass' ) ) {  
      die( 'This plugin requires COMPASS to be active.</a></div>' );
    }

    // Register post type first so we can create posts
    require_once plugin_dir_path( __FILE__ ) . 'class-xophz-compass-enchiridion-post-type.php';
    Xophz_Compass_Enchiridion_Post_Type::init();

    // Seed pre-built recipes
    self::seed_recipes();

    // Create default categories
    self::seed_categories();
  }

  /**
   * Seed default categories.
   */
  private static function seed_categories() {
    $categories = array(
      'Security'      => 'Recipes that enhance your site security',
      'Performance'   => 'Recipes that optimize site performance',
      'Customization' => 'Recipes that customize WordPress behavior',
    );

    foreach ( $categories as $name => $description ) {
      if ( ! term_exists( $name, Xophz_Compass_Enchiridion_Post_Type::TAXONOMY ) ) {
        wp_insert_term( $name, Xophz_Compass_Enchiridion_Post_Type::TAXONOMY, array(
          'description' => $description,
        ) );
      }
    }
  }

  /**
   * Seed pre-built recipes.
   */
  private static function seed_recipes() {
    $recipes = self::get_prebuilt_recipes();

    foreach ( $recipes as $recipe ) {
      // Check if recipe already exists by title
      $existing = get_posts( array(
        'post_type'      => Xophz_Compass_Enchiridion_Post_Type::POST_TYPE,
        'title'          => $recipe['title'],
        'posts_per_page' => 1,
      ) );

      if ( empty( $existing ) ) {
        Xophz_Compass_Enchiridion_Post_Type::create_recipe( $recipe );
      }
    }
  }

  /**
   * Get the list of pre-built recipes.
   *
   * @return array
   */
  private static function get_prebuilt_recipes() {
    return array(
      // Security Recipes
      array(
        'title'       => 'Disable XML-RPC',
        'description' => 'Blocks XML-RPC requests to prevent brute force attacks and DDoS amplification.',
        'category'    => 'Security',
        'hook'        => 'init',
        'context'     => 'both',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::disable_xmlrpc',
        'icon'        => 'shield-alt',
        'enabled'     => false,
      ),
      array(
        'title'       => 'Hide WordPress Version',
        'description' => 'Removes the WordPress version meta tag from the site header.',
        'category'    => 'Security',
        'hook'        => 'init',
        'context'     => 'frontend',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::hide_wp_version',
        'icon'        => 'eye-slash',
        'enabled'     => false,
      ),
      array(
        'title'       => 'Disable File Editor',
        'description' => 'Disables the theme and plugin editor in the WordPress admin.',
        'category'    => 'Security',
        'hook'        => 'init',
        'context'     => 'admin',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::disable_file_editor',
        'icon'        => 'edit-slash',
        'enabled'     => false,
      ),

      // Performance Recipes
      array(
        'title'       => 'Disable Emoji Scripts',
        'description' => 'Removes emoji JavaScript and CSS from your site for faster loading.',
        'category'    => 'Performance',
        'hook'        => 'init',
        'context'     => 'frontend',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::disable_emojis',
        'icon'        => 'tachometer-alt',
        'enabled'     => false,
      ),
      array(
        'title'       => 'Reduce Heartbeat Interval',
        'description' => 'Slows the WordPress heartbeat API to 60 seconds to reduce server load.',
        'category'    => 'Performance',
        'hook'        => 'init',
        'context'     => 'admin',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::reduce_heartbeat',
        'icon'        => 'heartbeat',
        'enabled'     => false,
      ),
      array(
        'title'       => 'Disable Self-Pingbacks',
        'description' => 'Prevents WordPress from sending pingbacks to itself.',
        'category'    => 'Performance',
        'hook'        => 'pre_ping',
        'context'     => 'both',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::disable_self_pingbacks',
        'icon'        => 'bell-slash',
        'enabled'     => false,
      ),

      // Customization Recipes
      array(
        'title'       => 'Custom Login Logo',
        'description' => 'Replaces the WordPress logo on the login page with your site logo.',
        'category'    => 'Customization',
        'hook'        => 'login_head',
        'context'     => 'both',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::custom_login_logo',
        'icon'        => 'image',
        'enabled'     => false,
      ),
      array(
        'title'       => 'Custom Admin Footer',
        'description' => 'Replaces the default WordPress admin footer text.',
        'category'    => 'Customization',
        'hook'        => 'admin_footer_text',
        'context'     => 'admin',
        'code'        => 'Xophz_Compass_Enchiridion_Recipes::custom_admin_footer',
        'icon'        => 'pen-fancy',
        'enabled'     => false,
      ),
    );
  }
}
