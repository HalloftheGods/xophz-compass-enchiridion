<?php

/**
 * Execute enabled recipes at runtime.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/includes
 */

class Xophz_Compass_Enchiridion_Executor {

  /**
   * Instance of this class.
   *
   * @var Xophz_Compass_Enchiridion_Executor
   */
  private static $instance = null;

  /**
   * Loaded recipes.
   *
   * @var array
   */
  private $recipes = array();

  /**
   * Get singleton instance.
   *
   * @return Xophz_Compass_Enchiridion_Executor
   */
  public static function get_instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Initialize the executor.
   */
  public function init() {
    // Load and execute recipes after post types are registered
    add_action( 'init', array( $this, 'load_recipes' ), 5 );
    
    // Inject custom CSS
    add_action( 'wp_head', array( $this, 'inject_custom_css' ), 999 );
    add_action( 'admin_head', array( $this, 'inject_custom_css' ), 999 );
    
    // Inject custom JS
    add_action( 'wp_footer', array( $this, 'inject_custom_js' ), 999 );
    add_action( 'admin_footer', array( $this, 'inject_custom_js' ), 999 );
  }

  /**
   * Load and execute enabled recipes.
   */
  public function load_recipes() {
    $this->recipes = $this->get_enabled_recipes();
    
    foreach ( $this->recipes as $recipe ) {
      $this->execute_recipe( $recipe );
    }
  }

  /**
   * Get all enabled recipes from the database.
   *
   * @return array
   */
  private function get_enabled_recipes() {
    $posts = get_posts( array(
      'post_type'      => Xophz_Compass_Enchiridion_Post_Type::POST_TYPE,
      'posts_per_page' => -1,
      'post_status'    => 'publish',
      'meta_query'     => array(
        array(
          'key'     => '_compass_recipe_enabled',
          'value'   => '1',
          'compare' => '=',
        ),
      ),
    ) );

    $recipes = array();
    foreach ( $posts as $post ) {
      $recipes[] = array(
        'id'       => $post->ID,
        'title'    => $post->post_title,
        'code'     => get_post_meta( $post->ID, '_compass_recipe_code', true ),
        'hook'     => get_post_meta( $post->ID, '_compass_recipe_hook', true ) ?: 'init',
        'priority' => (int) get_post_meta( $post->ID, '_compass_recipe_priority', true ) ?: 10,
        'context'  => get_post_meta( $post->ID, '_compass_recipe_context', true ) ?: 'both',
      );
    }

    return $recipes;
  }

  /**
   * Execute a single recipe.
   *
   * @param array $recipe Recipe data.
   */
  private function execute_recipe( $recipe ) {
    // Check context
    if ( $recipe['context'] === 'frontend' && is_admin() ) {
      return;
    }
    if ( $recipe['context'] === 'admin' && ! is_admin() ) {
      return;
    }

    // Get the code
    $code = $recipe['code'];
    if ( empty( $code ) ) {
      return;
    }

    // Hook the code
    $hook = $recipe['hook'];
    $priority = $recipe['priority'];

    // For 'init' hook, execute immediately since we're already past init
    if ( $hook === 'init' && did_action( 'init' ) ) {
      $this->run_code( $code, $recipe['id'] );
    } else {
      // Add to the specified hook
      add_action( $hook, function() use ( $code, $recipe ) {
        $this->run_code( $code, $recipe['id'] );
      }, $priority );
    }
  }

  /**
   * Safely run recipe code.
   *
   * @param string $code      The PHP code to execute.
   * @param int    $recipe_id The recipe ID for error logging.
   */
  private function run_code( $code, $recipe_id ) {
    try {
      // The code is stored as a callable function name or inline code
      // For security, we use predefined functions rather than eval()
      if ( is_callable( $code ) ) {
        call_user_func( $code );
      }
    } catch ( Exception $e ) {
      error_log( sprintf( 
        '[Enchiridion] Recipe #%d failed: %s', 
        $recipe_id, 
        $e->getMessage() 
      ) );
    }
  }

  /**
   * Inject custom CSS into the page head.
   */
  public function inject_custom_css() {
    $css = get_option( 'xophz_compass_enchiridion_custom_css', '' );
    if ( ! empty( $css ) ) {
      printf( "<style id=\"compass-enchiridion-custom-css\">\n%s\n</style>\n", esc_html( $css ) );
    }
  }

  /**
   * Inject custom JS into the page footer.
   */
  public function inject_custom_js() {
    $js = get_option( 'xophz_compass_enchiridion_custom_js', '' );
    if ( ! empty( $js ) ) {
      printf( "<script id=\"compass-enchiridion-custom-js\">\n%s\n</script>\n", $js );
    }
  }
}
