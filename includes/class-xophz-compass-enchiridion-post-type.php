<?php

/**
 * Register custom post type and taxonomy for recipes.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/includes
 */

class Xophz_Compass_Enchiridion_Post_Type {

  /**
   * The post type name.
   */
  const POST_TYPE = 'cmp_ench_recipe';

  /**
   * The taxonomy name.
   */
  const TAXONOMY = 'cmp_ench_category';

  /**
   * Register the custom post type.
   */
  public static function register_post_type() {
    $labels = array(
      'name'               => _x( 'Recipes', 'post type general name', 'xophz-compass-enchiridion' ),
      'singular_name'      => _x( 'Recipe', 'post type singular name', 'xophz-compass-enchiridion' ),
      'menu_name'          => _x( 'Recipes', 'admin menu', 'xophz-compass-enchiridion' ),
      'add_new'            => _x( 'Add New', 'recipe', 'xophz-compass-enchiridion' ),
      'add_new_item'       => __( 'Add New Recipe', 'xophz-compass-enchiridion' ),
      'edit_item'          => __( 'Edit Recipe', 'xophz-compass-enchiridion' ),
      'new_item'           => __( 'New Recipe', 'xophz-compass-enchiridion' ),
      'view_item'          => __( 'View Recipe', 'xophz-compass-enchiridion' ),
      'search_items'       => __( 'Search Recipes', 'xophz-compass-enchiridion' ),
      'not_found'          => __( 'No recipes found', 'xophz-compass-enchiridion' ),
      'not_found_in_trash' => __( 'No recipes found in Trash', 'xophz-compass-enchiridion' ),
    );

    $args = array(
      'labels'              => $labels,
      'public'              => false,
      'publicly_queryable'  => false,
      'show_ui'             => false,
      'show_in_menu'        => false,
      'query_var'           => false,
      'capability_type'     => 'post',
      'has_archive'         => false,
      'hierarchical'        => false,
      'supports'            => array( 'title', 'custom-fields' ),
    );

    register_post_type( self::POST_TYPE, $args );
  }

  /**
   * Register the taxonomy.
   */
  public static function register_taxonomy() {
    $labels = array(
      'name'              => _x( 'Recipe Categories', 'taxonomy general name', 'xophz-compass-enchiridion' ),
      'singular_name'     => _x( 'Recipe Category', 'taxonomy singular name', 'xophz-compass-enchiridion' ),
      'search_items'      => __( 'Search Categories', 'xophz-compass-enchiridion' ),
      'all_items'         => __( 'All Categories', 'xophz-compass-enchiridion' ),
      'edit_item'         => __( 'Edit Category', 'xophz-compass-enchiridion' ),
      'update_item'       => __( 'Update Category', 'xophz-compass-enchiridion' ),
      'add_new_item'      => __( 'Add New Category', 'xophz-compass-enchiridion' ),
      'new_item_name'     => __( 'New Category Name', 'xophz-compass-enchiridion' ),
      'menu_name'         => __( 'Categories', 'xophz-compass-enchiridion' ),
    );

    $args = array(
      'hierarchical'      => true,
      'labels'            => $labels,
      'show_ui'           => false,
      'show_admin_column' => false,
      'query_var'         => false,
      'public'            => false,
    );

    register_taxonomy( self::TAXONOMY, self::POST_TYPE, $args );
  }

  /**
   * Initialize - register post type and taxonomy.
   */
  public static function init() {
    self::register_post_type();
    self::register_taxonomy();
  }

  /**
   * Get a recipe by ID.
   *
   * @param int $id Recipe post ID.
   * @return array|null Recipe data or null if not found.
   */
  public static function get_recipe( $id ) {
    $post = get_post( $id );
    if ( ! $post || $post->post_type !== self::POST_TYPE ) {
      return null;
    }

    return self::format_recipe( $post );
  }

  /**
   * Get all recipes.
   *
   * @return array Array of recipe data.
   */
  public static function get_all_recipes() {
    $posts = get_posts( array(
      'post_type'      => self::POST_TYPE,
      'posts_per_page' => -1,
      'post_status'    => 'publish',
      'orderby'        => 'title',
      'order'          => 'ASC',
    ) );

    $recipes = array();
    foreach ( $posts as $post ) {
      $recipes[] = self::format_recipe( $post );
    }

    return $recipes;
  }

  /**
   * Format a recipe post into a data array.
   *
   * @param WP_Post $post The recipe post.
   * @return array Formatted recipe data.
   */
  private static function format_recipe( $post ) {
    $categories = wp_get_post_terms( $post->ID, self::TAXONOMY, array( 'fields' => 'names' ) );
    
    return array(
      'id'          => $post->ID,
      'title'       => $post->post_title,
      'description' => $post->post_excerpt,
      'category'    => ! empty( $categories ) ? $categories[0] : 'Uncategorized',
      'hook'        => get_post_meta( $post->ID, '_compass_recipe_hook', true ) ?: 'init',
      'priority'    => (int) get_post_meta( $post->ID, '_compass_recipe_priority', true ) ?: 10,
      'context'     => get_post_meta( $post->ID, '_compass_recipe_context', true ) ?: 'both',
      'enabled'     => (bool) get_post_meta( $post->ID, '_compass_recipe_enabled', true ),
      'icon'        => get_post_meta( $post->ID, '_compass_recipe_icon', true ) ?: 'scroll',
    );
  }

  /**
   * Toggle a recipe's enabled state.
   *
   * @param int  $id      Recipe post ID.
   * @param bool $enabled Whether to enable or disable.
   * @return bool True on success.
   */
  public static function toggle_recipe( $id, $enabled ) {
    return update_post_meta( $id, '_compass_recipe_enabled', $enabled ? 1 : 0 );
  }

  /**
   * Create a new recipe.
   *
   * @param array $data Recipe data.
   * @return int|WP_Error Post ID on success, WP_Error on failure.
   */
  public static function create_recipe( $data ) {
    $post_id = wp_insert_post( array(
      'post_type'    => self::POST_TYPE,
      'post_title'   => $data['title'],
      'post_excerpt' => isset( $data['description'] ) ? $data['description'] : '',
      'post_status'  => 'publish',
    ) );

    if ( is_wp_error( $post_id ) ) {
      return $post_id;
    }

    // Set meta
    update_post_meta( $post_id, '_compass_recipe_code', isset( $data['code'] ) ? $data['code'] : '' );
    update_post_meta( $post_id, '_compass_recipe_hook', isset( $data['hook'] ) ? $data['hook'] : 'init' );
    update_post_meta( $post_id, '_compass_recipe_priority', isset( $data['priority'] ) ? (int) $data['priority'] : 10 );
    update_post_meta( $post_id, '_compass_recipe_context', isset( $data['context'] ) ? $data['context'] : 'both' );
    update_post_meta( $post_id, '_compass_recipe_enabled', isset( $data['enabled'] ) ? ( $data['enabled'] ? 1 : 0 ) : 0 );
    update_post_meta( $post_id, '_compass_recipe_icon', isset( $data['icon'] ) ? $data['icon'] : 'scroll' );

    // Set category
    if ( ! empty( $data['category'] ) ) {
      wp_set_object_terms( $post_id, $data['category'], self::TAXONOMY );
    }

    return $post_id;
  }
}
