<?php

/*
Plugin Name: Quick Weblog
Plugin URI: https://fexd.com/wordpress/plugins/quick-weblog
Description: Create new weblog posts quickly and easily.
Version: 0.0.1
Author: Arlin Schaffel
Author URI: https://fexd.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: quick-weblog
Domain Path: /languages/
*/

function quick_weblog_form() {
  ?>
  <style>
    #quick-weblog {
      padding: 1em 0;
    }
    #quick-weblog div {
      padding: 0.75em 0;
    }
    #quick-weblog div:nth-last-child(1) {
      padding-bottom: 0;
    } 
    #quick-weblog div:nth-child(1) {
      padding-top: 0;
    } 
    #quick-weblog div label {
      font-weight: 600;
    }
    #quick-weblog div input,
    #quick-weblog div textarea,
    #quick-weblog div select {
      display: block;
      max-width: 600px;
      width: 99%;
    }
    #quick-weblog div input[type=submit] {
      width: auto;
    }
  </style>

  <div class="card">
    <form id="quick-weblog" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
      <div>
        <label for="url"><?php _e( 'Post URL', 'quick-weblog' ); ?></label>
        <input type="text" name="url" id="url" required>
      </div>

      <div>
        <label for="title"><?php _e( 'Post Title', 'quick-weblog' ); ?></label>
        <input type="text" name="title" id="title" required>
      </div>

      <div>
        <label for="image_url"><?php _e( 'Image URL', 'quick-weblog' ); ?></label>
        <input type="text" name="image_url" id="image_url" required>
      </div>

      <div>
        <label for="image_description"><?php _e( 'Image Description', 'quick-weblog' ); ?></label>
        <input type="text" name="image_description" id="image_description" required>
      </div>

      <div>
        <label for="quote"><?php _e( 'Quote', 'quick-weblog' ); ?></label>
        <textarea name="quote" id="quote" rows="6" required></textarea>
      </div>

      <div>
        <label for="category"><?php _e( 'Category', 'quick-weblog' ); ?></label>
        <?php wp_dropdown_categories( array( 'name' => 'category', 'orderby' => 'name', 'taxonomy' => 'category', 'selected' => 1) ); ?>
      </div>

      <div>
        <label for="tags"><?php _e( 'Tags', 'quick-weblog' ); ?></label>
        <input type="text" name="tags" id="tags" required>
      </div>

      <div>
        <input type="hidden" name="action" value="quick_weblog_submit_form">
        <?php wp_nonce_field( 'quick_weblog_submit_form', 'quick_weblog_form_nonce' ); ?>
        <input type="submit" value="<?php _e( 'Submit', 'quick-weblog' ); ?>">
      </div>
    </form>
  </div>
  <?php
}

function quick_weblog_add_form_to_page() {
  add_shortcode( 'quick-weblog-form', 'quick_weblog_form' );
}
add_action( 'init', 'quick_weblog_add_form_to_page' );

function quick_weblog_add_menu_page() {
  add_menu_page(
    __( 'Quick Weblog', 'quick-weblog' ), // Page title
    __( 'Quick Weblog', 'quick-weblog' ), // Menu title
    'manage_options', // Capability required to access the page
    'quick-weblog', // Menu slug
    'quick_weblog_menu_page', // Callback function to render the page
    'dashicons-admin-post', // Icon
    30 // Position in the menu
  );
}
add_action( 'admin_menu', 'quick_weblog_add_menu_page' );

function quick_weblog_menu_page() {
  ?>
  <div class="wrap">
    <h1><?php _e( 'Quick Weblog', 'quick-weblog' ); ?></h1>
    <?php quick_weblog_form(); ?>
  </div>
  <?php
}

function quick_weblog_submit_form() {
  // Check the nonce to verify the form submission
  if ( ! isset( $_POST['quick_weblog_form_nonce'] ) || ! wp_verify_nonce( $_POST['quick_weblog_form_nonce'], 'quick_weblog_submit_form' ) ) {
    wp_die( __( 'Error: Invalid nonce.', 'quick-weblog' ) );
  }

  // Get the form data
  $title = sanitize_text_field( $_POST['title'] );
  $image_url = sanitize_text_field( $_POST['image_url'] );
  $image_description = sanitize_text_field( $_POST['image_description'] );
  $quote = sanitize_text_field( $_POST['quote'] );
  $url = esc_url_raw( $_POST['url'] );
  $category = intval( $_POST['category'] );
  $tags = sanitize_text_field( $_POST['tags'] );

  // Create a new post
  $post_data = array(
    'post_title' => $title,
    'post_content' => sprintf( '
      <blockquote class="wp-block-quote">
        <figure class="wp-block-image">
          <img decoding="async" src="%s" alt>
          <figcaption class="wp-element-caption">%s</figcaption>
        </figure><p>%s</p>
        <cite><a href="%s" target="_blank" rel="noreferrer noopener">%s</a></cite>
      </blockquote>'
      , $image_url, $image_description, $quote, $url, $url ),
    'post_category' => array( $category ),
    'tags_input' => $tags,
    'post_status' => 'publish'
  );

  /*
  * Create a block programmatically and serialize it.
  * https://developer.wordpress.org/reference/functions/serialize_block/
  */
  $block_name = 'core/paragraph';
  $innerHTML  = 'Sample paragraph text.';

  $converted_block = new WP_Block_Parser_Block( $block_name, array(), array(), $innerHTML, array( $innerHTML ) );
  // WP_CLI::log( print_r( $converted_block, true ) );

  $serialized_block = serialize_block( (array) $converted_block );
  // WP_CLI::log( $serialized_block );

  // Create a new post
  $post_data = array(
    'post_title' => $title,
    'post_content' => $serialized_block,
    'post_category' => array( $category ),
    'tags_input' => $tags,
    'post_status' => 'publish'
  );

  $post_id = wp_insert_post( $post_data );

  // Redirect to the new post
  wp_redirect( get_permalink( $post_id ) );
  exit();
}
add_action( 'admin_post_quick_weblog_submit_form', 'quick_weblog_submit_form' );

?>
