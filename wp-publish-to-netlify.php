<?php
/**
 * Plugin Name:       WP Publish to Netlify
 * Plugin URI:        https://wp-publish-to-netlify.dmbk.io
 * GitHub Plugin URI: afragen/github-updater
 * GitHub Plugin URI: https://github.com/afragen/github-updater
 * Description:       Plugin to trigger build to both a staging and production netlify site.
 * Author:            Dain Blodorn Kim
 * Author URI:        https://dain.kim
 * Text Domain:       wp-publish-to-netlify
 * Domain Path:       /languages
 * Version:           0.1.1
 * License:           MIT
 *
 * @package           WP Publish to Netlify
*/

/*
  Copyright (c) <year> <copyright holders>

  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
*/

defined('ABSPATH') or die ('You do not have access to this file!');

if ( ! class_exists( 'WPPTN_Plugin' ) ) {
  
  class WPPTN_Plugin {
    
    // Class Constructor
    public function __construct() {
      $this->version = '0.1';
      $this->name = 'WP Publish to Netlify';
      $this->slug = 'wp-publish-to-netlify';
      $this->prefix = 'wpptn_';
      $this->wpptn_set_options();
      if(is_admin()){
        add_action('admin_menu', array($this, 'wpptn_create_settings_page'));
        add_action('admin_bar_menu', array($this, 'admin_bar_menu_production' ), 999 );
      }
    }

    // Add the menu item and page
    public function wpptn_create_settings_page() {
      $callback = array($this, 'wpptn_settings_page_content');
      add_submenu_page('options-general.php', $this->name, $this->name, 'level_8', __FILE__, $callback, '');
    }

    public function admin_bar_menu_production( $wp_admin_bar ) {
			$args = array(
				'id'    => $this->prefix.'-publish-production',
				'title' => __( '
          <div style="display: flex; flex-direction: row;">
            <span>Publish Production Site</span>
            <div style="display: flex; align-items: center; padding-left: 10px;">
              <img src=" ' . $this->netlify_badge . ' "/>
            </div>
          </div>', $this->slug ),
				'href'  => $this->wpptn_netlify_webhooks(),
			);
			$wp_admin_bar->add_node( $args );
		}

    // Add Production Webhook to options
    public function wpptn_set_options(){
      $opt = get_option( $this->prefix.'option');
      if(isset($opt['webhook_url'])){
        $this->webhook_url = $opt['webhook_url'];
      }else{
        $this->webhook_url = null;
      }
      if(isset($opt['netlify_badge'])){
        $this->netlify_badge = $opt['netlify_badge'];
      }else{
        $this->netlify_badge = null;
      }
    }

    // Production Webhook Function
    public function wpptn_netlify_webhooks() {
      if(isset($this->webhook_url) && $this->webhook_url){
        $url = $this->webhook_url;
        $data = array();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
      }
    }

    // Views
    // SETTINGS PAGE LAYOUT
    public function wpptn_settings_page_content() {
      if ( isset($_POST[ $this->prefix.'option'])): ?>
        <?php
          check_admin_referer( $this->slug );
          $opt = $_POST[ $this->prefix.'option'];
          update_option( $this->prefix.'option', $opt);
        ?>
        <div class="updated fade">
          <p><strong><?php _e('Production Webhook Saved.'); ?></strong></p>
        </div>
      <?php endif; ?>
        <div class="wrap">
          <h2><?php echo $this->name ?></h2>
          <form action="" method="post">
            <?php
              wp_nonce_field( $this->slug );
              $this->wpptn_set_options();
            ?>
            <div style="display: flex; flex-direction: column;">
              <hr>
              <h2>Production:</h2>
              <label>Production Webhook URL:</label>
              <input style="width: 100%; max-width: 700px;" name="<?php echo $this->prefix; ?>option[webhook_url]" type="text" id="input_url" value="<?php echo $this->webhook_url ?>" class="regular-text" placeholder="https://api.netlify.com/build_hooks/xxxxxxxxxxxxxxxxxxxxxxxx" />
              <br>
              <label>Netlify Build Status Badge:</label>
              <input style="width: 100%; max-width: 700px;" name="<?php echo $this->prefix; ?>option[netlify_badge]" type="text" id="input_url" value="<?php echo $this->netlify_badge ?>" class="regular-text" placeholder="Past Build Status Image Here." />
            </div>
            <p class="submit">
              <input type="submit" name="Submit" class="button-primary" value="Save Netlify Options"/>
            </p>
            <hr>
          </form>
        </div>
      <?php
    }
  }

  global $wp_publish_to_netlify;
  $wp_publish_to_netlify = new WPPTN_Plugin;

}