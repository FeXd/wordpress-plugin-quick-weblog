<?php

/*
Plugin Name: Quick Weblog
Plugin URI: https://github.com/FeXd/wordpress-plugin-quick-weblog
Description: Quickly create a simple Post that highlights an existing news article.
Version: 0.0.3
Author: Arlin Schaffel
Author URI: https://github.com/FeXd
License: MIT
License URI: https://github.com/FeXd/wordpress-plugin-quick-weblog/blob/main/LICENSE.md
Text Domain: quick-weblog
*/

function quick_weblog_form()
{

  $api_key = get_option('quick_weblog_api_key', '');

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

    #quick-weblog-description {
      max-width: 520px;
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

  <script src="<?php echo plugin_dir_url(__FILE__); ?>quick-weblog.js"></script>

  <script>
    window.addEventListener("DOMContentLoaded", (event) => {
      document.getElementById("quick-weblog-auto").addEventListener("click", (click_event) => {
        click_event.preventDefault();
        fetchAndPopulateFormFields(document.getElementById("quick-weblog-url").value, "<?php echo esc_js(wp_kses($api_key, array())); ?>");
      });
    });
  </script>

  <p id="quick-weblog-description">Quickly create a simple Post that highlights an existing news article. Posts include a captioned image and quote with URL citation of original article. All fields are required.</p>

  <div class="card">
    <form id="quick-weblog" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <div>
        <label for="url"><?php _e('Post URL', 'quick-weblog'); ?></label>
        <input type="text" name="url" id="quick-weblog-url" required spellcheck="false">
      </div>

      <div>
        <button id="quick-weblog-auto">Auto Fill</button>
        <span id="quick-weblog-status">Attempt to auto fill form based on <strong>Post URL</strong>.</span>
      </div>

      <div>
        <label for="title"><?php _e('Post Title', 'quick-weblog'); ?></label>
        <input type="text" name="title" id="quick-weblog-title" required spellcheck="false">
      </div>

      <div>
        <label for="image_url"><?php _e('Image URL', 'quick-weblog'); ?></label>
        <input type="text" name="image_url" id="quick-weblog-image_url" required spellcheck="false">
      </div>

      <div>
        <label for="image_description"><?php _e('Image Description', 'quick-weblog'); ?></label>
        <input type="text" name="image_description" id="quick-weblog-image_description" required spellcheck="false">
      </div>

      <div>
        <label for="quote"><?php _e('Quote', 'quick-weblog'); ?></label>
        <textarea name="quote" id="quick-weblog-quote" rows="6" required spellcheck="false"></textarea>
      </div>

      <div>
        <label for="category"><?php _e('Category', 'quick-weblog'); ?></label>
        <?php wp_dropdown_categories(array('name' => 'category', 'orderby' => 'name', 'taxonomy' => 'category', 'selected' => 1)); ?>
      </div>

      <div>
        <label for="tags"><?php _e('Tags', 'quick-weblog'); ?></label>
        <input type="text" name="tags" id="quick-weblog-tags" required spellcheck="false">
      </div>

      <div>
        <label for="post_date"><?php _e('Post Date', 'quick-weblog'); ?> (Optional)</label>
        <input type="datetime-local" name="post_date" id="quick-weblog-post_date">
      </div>

      <div>
        <input type="hidden" name="action" value="quick_weblog_submit_form">
        <?php wp_nonce_field('quick_weblog_submit_form', 'quick_weblog_form_nonce'); ?>
        <input type="submit" value="<?php _e('Submit', 'quick-weblog'); ?>">
      </div>
    </form>
  </div>
<?php
}

function quick_weblog_add_menu_page()
{
  add_menu_page(
    __('Quick Weblog', 'quick-weblog'), // Page title
    __('Quick Weblog', 'quick-weblog'), // Menu title
    'manage_options', // Capability required to access the page
    'quick-weblog', // Menu slug
    'quick_weblog_menu_page', // Callback function to render the page
    'dashicons-welcome-write-blog', // Icon
    4.9021042 // Position in the menu
  );

  add_submenu_page(
    'quick-weblog', // Parent slug
    __('API Settings', 'quick-weblog'), // Page title
    __('API Settings', 'quick-weblog'), // Menu title
    'manage_options', // Capability required to access the page
    'quick-weblog-settings', // Menu slug
    'quick_weblog_settings_page' // Callback function to render the page
  );
}

add_action('admin_menu', 'quick_weblog_add_menu_page');

function quick_weblog_menu_page()
{
?>
  <div class="wrap">
    <h1><?php _e('Quick Weblog', 'quick-weblog'); ?></h1>
    <?php quick_weblog_form(); ?>
  </div>
<?php
}

function quick_weblog_submit_form()
{
  // Check the nonce to verify the form submission
  if (!isset($_POST['quick_weblog_form_nonce']) || !wp_verify_nonce($_POST['quick_weblog_form_nonce'], 'quick_weblog_submit_form')) {
    wp_die(__('Error: Invalid nonce.', 'quick-weblog'));
  }

  // Get the form data
  $title = sanitize_text_field($_POST['title']);
  $image_url = sanitize_text_field($_POST['image_url']);
  $image_description = sanitize_text_field($_POST['image_description']);
  $quote = sanitize_text_field($_POST['quote']);
  $url = esc_url_raw($_POST['url']);
  $category = intval($_POST['category']);
  $tags = sanitize_text_field($_POST['tags']);
  $post_date = sanitize_text_field($_POST['post_date']);

  // Create block content
  $image_block = '<!-- wp:image {"url":"' . esc_attr($image_url) . '","alt":"' . esc_attr($image_description) . '"} -->' .
    '<figure class="wp-block-image">' .
    '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_description) . '">' .
    '<figcaption>' . esc_html($image_description) . '</figcaption>' .
    '</figure><!-- /wp:image -->';

  $quote_block = '<!-- wp:quote {"citation":"' . esc_attr($url) . '"} -->' .
    '<blockquote class="wp-block-quote">' .
    '<p>' . esc_html($quote) . '</p>' .
    '<cite><a href="' . esc_url($url) . '" target="_blank" rel="noreferrer noopener">' . esc_html($url) . '</a></cite>' .
    '</blockquote><!-- /wp:quote -->';

  $block_content = $image_block . $quote_block;

  // Create a new post
  $post_data = array(
    'post_title' => $title,
    'post_content' => $block_content,
    'post_category' => array($category),
    'tags_input' => $tags,
    'post_status' => 'publish',
    'post_date' => $post_date
  );
  $post_id = wp_insert_post($post_data);

  // Redirect to the new post
  wp_redirect(get_permalink($post_id));
  exit();
}

add_action('admin_post_quick_weblog_submit_form', 'quick_weblog_submit_form');

function quick_weblog_settings_init()
{
  add_settings_section('quick_weblog_api_section', 'API Settings', null, 'quick-weblog');
  add_settings_field('quick_weblog_api_key', 'API Key', 'quick_weblog_api_key_callback', 'quick-weblog', 'quick_weblog_api_section');

  register_setting('quick_weblog_settings', 'quick_weblog_use_api', 'boolval');
  register_setting('quick_weblog_settings', 'quick_weblog_api_key', 'sanitize_text_field');
}

add_action('admin_init', 'quick_weblog_settings_init');

function quick_weblog_api_key_callback()
{
  $value = get_option('quick_weblog_api_key', '');
  echo '<input type="text" name="quick_weblog_api_key" value="' . esc_attr($value) . '" />';
}

function quick_weblog_add_settings_link($links)
{
  $settings_link = '<a href="' . admin_url('options-general.php?page=quick-weblog') . '">' . __('Settings', 'quick-weblog') . '</a>';
  array_push($links, $settings_link);
  return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'quick_weblog_add_settings_link');

function quick_weblog_settings_page()
{
?>
  <div class="wrap">
    <h1><?php _e('API Settings', 'quick-weblog'); ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields('quick_weblog_settings'); ?>
      <?php do_settings_sections('quick-weblog'); ?>
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}

?>