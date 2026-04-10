<?php

/**
 * Pre-built recipe implementations.
 *
 * @since      1.0.0
 * @package    Xophz_Compass_Enchiridion
 * @subpackage Xophz_Compass_Enchiridion/includes
 */

class Xophz_Compass_Enchiridion_Recipes {

  /**
   * Disable XML-RPC.
   */
  public static function disable_xmlrpc() {
    add_filter( 'xmlrpc_enabled', '__return_false' );
    add_filter( 'wp_headers', function( $headers ) {
      unset( $headers['X-Pingback'] );
      return $headers;
    } );
  }

  /**
   * Hide WordPress version.
   */
  public static function hide_wp_version() {
    remove_action( 'wp_head', 'wp_generator' );
    add_filter( 'the_generator', '__return_empty_string' );
  }

  /**
   * Disable file editor.
   */
  public static function disable_file_editor() {
    if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
      define( 'DISALLOW_FILE_EDIT', true );
    }
  }

  /**
   * Disable emoji scripts.
   */
  public static function disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

    add_filter( 'tiny_mce_plugins', function( $plugins ) {
      return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
    } );

    add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
      if ( 'dns-prefetch' === $relation_type ) {
        $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/' );
        $urls = array_filter( $urls, function( $url ) use ( $emoji_svg_url ) {
          return strpos( $url, $emoji_svg_url ) === false;
        } );
      }
      return $urls;
    }, 10, 2 );
  }

  /**
   * Reduce heartbeat interval.
   */
  public static function reduce_heartbeat() {
    add_filter( 'heartbeat_settings', function( $settings ) {
      $settings['interval'] = 60;
      return $settings;
    } );
  }

  /**
   * Disable self-pingbacks.
   */
  public static function disable_self_pingbacks( &$links ) {
    $home = get_option( 'home' );
    foreach ( $links as $l => $link ) {
      if ( 0 === strpos( $link, $home ) ) {
        unset( $links[ $l ] );
      }
    }
  }

  /**
   * Custom login logo.
   */
  public static function custom_login_logo() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
      $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
      if ( $logo_url ) {
        printf( '<style>
          #login h1 a, .login h1 a {
            background-image: url(%s);
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            width: 100%%;
            height: 80px;
          }
        </style>', esc_url( $logo_url ) );
      }
    }
  }

  /**
   * Custom admin footer.
   */
  public static function custom_admin_footer() {
    return sprintf(
      'Powered by <a href="%s" target="_blank">%s</a>',
      esc_url( home_url() ),
      esc_html( get_bloginfo( 'name' ) )
    );
  }
}
