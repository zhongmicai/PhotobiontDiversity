<?php
/*
Plugin Name: RSS Links Manager
Plugin URI: http://www.tobiaseisenschmidt.de/products/rss-links-manager-wordpress/
Description: Manage and customise your RSS feed links. Are you using Feedburner? Just enter your Feedburner URL. Are you using Disqus or Facebook comments? Just deactivate WordPress' comments feed.
Author: Tobias Eisenschmidt
Version: 0.1.1
Author URI: http://www.tobiaseisenschmidt.de
License: The MIT License
*/

/**
 * Set the wp-content and plugin urls/paths
 */
if(!defined('WP_CONTENT_URL'))	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if(!defined('WP_CONTENT_DIR'))	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if(!defined('WP_PLUGIN_URL')) 	define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if(!defined('WP_PLUGIN_DIR'))	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

if(!class_exists('RSSLinksManager')) {

	/**
	 * RSS Links Manager Class
	 *
	 * @author		Tobias Eisenschmidt
	 * @copyright	(c) 2012 Tobias Eisenschmidt
	 * @license		http://files.phaino.net/wordpress/rss-links-manager/license
	 */
	class RSSLinksManager {
	
		/**
		 * @var	array	Plugin options
		 */
		var $options		= array();
		
		/**
		 * @var	array	Options reference
		 */
		var $options_name	= 'rss_links_manager_options';
		
		/**
		 * @var	array	Internationalisation (i18n) domain
		 */
		var $textdomain		= 'rss-links-manager';
		
		/**
		 * PHP4 constructor
		 */
		function RSSLinksManager() {
		
			$this->__construct();
		}

		/**
		 * PHP5 constructor
		 */
		function __construct() {
		
			// Get directory name (default: rss-links-manager)
			$dirname = dirname(plugin_basename(__FILE__));

			// Get i18n file
			load_plugin_textdomain($this->textdomain, false, "$dirname/i18n/");

			// Get feed settings
			$this->get_options();
		
			// Remove main and comments feed
			remove_action('wp_head', 'feed_links', 2);
			
			// Remove extra feeds if disabled
			if($this->options['extras']['status'] === false) remove_action('wp_head', 'feed_links_extra', 3);
			
			// Add custom feed links to wp_head
			add_action('wp_head', array(&$this, 'feed_links'), 2);

			// Add plugin options link to admin menu
			add_action('admin_menu', array(&$this, 'admin_menu_link'));
			
			// Add settings link to plugin page
			add_filter('plugin_row_meta', array(&$this, 'plugin_settings_link'), 10, 2);
			
			// Register uninstall hook
			register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
		}

		/**
		 * Add plugin options page 
		 */
		function admin_menu_link() {
		
			add_options_page('RSS Links Manager', 'RSS Links Manager', 'manage_options', basename(__FILE__), array(&$this, 'admin_options_page'));
		}
		
		/**
		 * Add settings link to the plugin's link array
		 */
		function plugin_settings_link($links, $file) {
		
			$basename = plugin_basename(__FILE__);

			if($file == $basename) {
			
				$links[] = '<a href="options-general.php?page=' . basename(__FILE__) .'">' . __('Settings', $this->textdomain) . '</a>';
			}
			
			return $links;
		}

		/**
		 * Get plugin options
		 */
		function get_options() {
		
			if(!$options = get_option($this->options_name)) {
			
				// Default options
			
				$options = array(
				
					'main' => array(
					
						'url'	=> get_bloginfo('url') . '/feed/',
						'title'	=> get_bloginfo('name') . ' » Feed',
						'type'	=> feed_content_type(),
						'status'=> true
					),
					
					'comments' => array(
					
						'url'	=> get_bloginfo('url') . '/comments/feed/',
						'title'	=> get_bloginfo('name') . ' » Comments Feed',
						'type'	=> feed_content_type(),
						'status'=> true
					),
					
					'extras' => array(
					
						'status'=> true
					)
				
				);
				
				update_option($this->options_name, $options);
			}
			
			$this->options = $options;
		}

		/**
		 * Plugin options page
		 */
		function admin_options_page() {
		
			// Save options
					
			if(isset($_POST['rss_links_manager_save'])) {
			
				if(wp_verify_nonce($_POST['_wpnonce'], 'rss-links-manager-update-options')) {
							
					$this->options = array(
					
						'main' => array(
						
							'url'	=> $_POST['feed_url'],
							'title'	=> $_POST['feed_title'],
							'type'	=> $_POST['feed_type'],
							'status'=> (isset($_POST['feed_status']) && $_POST['feed_status'] === 'on') ? true : false
						),
						
						'comments' => array(
						
							'url'	=> $_POST['coms_url'],
							'title'	=> $_POST['coms_title'],
							'type'	=> $_POST['coms_type'],
							'status'=> (isset($_POST['coms_status']) && $_POST['coms_status'] === 'on') ? true : false
						),
						
						'extras' => array(
						
							'status'=> (isset($_POST['extra_status']) && $_POST['extra_status'] === 'on') ? true : false
						)
					
					);

					update_option($this->options_name, $this->options);

					echo '<div class="updated"><p>' . __('Your changes were <strong>successfully saved</strong>!', $this->textdomain) . '</p></div>';
				}
				
				else {
				
					echo '<div class="error"><p>' . __('Whoops! There was a problem with the data you posted. Please try again.', $this->textdomain) . '</p></div>';
				}
			}
			
			?>
			
<div class="wrap">
<div class="icon32" id="icon-options-general"><br/></div>
<h2>RSS Links Manager</h2>
<form method="post" id="rss_links_manager_options">
<?php wp_nonce_field('rss-links-manager-update-options'); ?>
	<h3><?php _e('Main Feed', $this->textdomain); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Feed Address (URL)', $this->textdomain); ?></th>
			<td><input spellcheck="false" name="feed_url" type="text" id="feed_url" size="40" value="<?php echo stripslashes(htmlspecialchars($this->options['main']['url'])); ?>"/>
			<span class="description"><?php _e('e.g. your <a href="http://feedburner.google.com/" target="_blank">Feedburner</a> address.', $this->textdomain); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Feed Title', $this->textdomain); ?></th>
			<td><input spellcheck="false" name="feed_title" type="text" id="feed_title" size="40" value="<?php echo stripslashes(htmlspecialchars($this->options['main']['title'])); ?>"/>
			<span class="description"><?php _e('Title for the main feed link.', $this->textdomain); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('MIME Type', $this->textdomain); ?></th>
			<td><input spellcheck="false" name="feed_type" type="text" id="feed_type" size="40" value="<?php echo stripslashes(htmlspecialchars($this->options['main']['type'])); ?>"/>
			<span class="description"><?php _e('If unsure, leave untouched.', $this->textdomain); ?></span></td>
		</tr>
	</table>
	<p>&nbsp;</p>
	<h3><?php _e('Comments Feed', $this->textdomain); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Feed Address (URL)', $this->textdomain); ?></th>
			<td><input spellcheck="false" name="coms_url" type="text" id="coms_url" size="40" value="<?php echo stripslashes(htmlspecialchars($this->options['comments']['url'])); ?>"/>
			<span class="description"><?php _e('e.g. your <a href="http://feedburner.google.com/" target="_blank">Feedburner</a> address.', $this->textdomain); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Feed Title', $this->textdomain); ?></th>
			<td><input spellcheck="false" name="coms_title" type="text" id="coms_title" size="40" value="<?php echo stripslashes(htmlspecialchars($this->options['comments']['title'])); ?>"/>
			<span class="description"><?php _e('Title for the comments feed link.', $this->textdomain); ?></span>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('MIME Type', $this->textdomain); ?></th>
			<td><input spellcheck="false" name="coms_type" type="text" id="coms_type" size="40" value="<?php echo stripslashes(htmlspecialchars($this->options['comments']['type'])); ?>"/>
			<span class="description"><?php _e('If unsure, leave untouched.', $this->textdomain); ?></span></td>
		</tr>
	</table>
	<p>&nbsp;</p>
	<h3><?php _e('Available Feeds', $this->textdomain); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Activate the following feeds', $this->textdomain); ?></th>
			<td><label for="extra_feeds">
				<input type="checkbox" id="feed_status" name="feed_status" <?php echo ($this->options['main']['status'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Main Feed', $this->textdomain); ?></label>
				<br>
				<label for="extra_feeds">
				<input type="checkbox" id="coms_status" name="coms_status" <?php echo ($this->options['comments']['status'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Comments Feed', $this->textdomain); ?></label>
				<br>
				<label for="extra_feeds">
				<input type="checkbox" id="extra_status" name="extra_status" <?php echo ($this->options['extras']['status'] === true) ? "checked='checked'" : ""; ?>/> <?php _e('Extra Feeds (i.e. Categories, Tags, etc.)', $this->textdomain); ?></label></td>
		</tr>
	</table>
	<p class="submit">
		<input type="submit" value="Save Changes" name="rss_links_manager_save" class="button-primary" />
	</p>
</form>
<h2><?php _e('Links', $this->textdomain); ?></h2>
<ul>
	<li>&raquo; <a href="http://www.tobiaseisenschmidt.de">Tobias Eisenschmidt</a> (Blog)</li>
	<li>&raquo; <a href="http://twitter.com/teisenschmidt">@teisenschmidt</a> (Twitter)</li>
</ul>
</div>
			
			<?php
		}

		/**
		 * Generate custom feed links 
		 */
		function feed_links() {
		
			$feeds = $this->options;
			
			// Remove extra feeds array
			unset($feeds['extras']);
		
			// Output feed links
			foreach($feeds as $feed) {
			
				if($feed['status'] === true) {
				
					echo '<link rel="alternate" type="' . stripslashes(esc_html($feed['type'])) . '" title="' . stripslashes(esc_html($feed['title'])) . '" href="' . esc_url($feed['url']) . "\" />\n";
				}
			}
		}
		
		/**
		 * Uninstall hook
		 */
		function uninstall() {
		
			if(__FILE__ != WP_UNINSTALL_PLUGIN) return;
			
			// Delete options on uninstall
			delete_option($this->options_name);
		}
	}
}

// Initialise plugin
if(class_exists('RSSLinksManager')) new RSSLinksManager();