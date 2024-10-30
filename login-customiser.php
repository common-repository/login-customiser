<?php
/*
Plugin Name: Login Customiser
Plugin URI: http://www.poeticcoding.co.uk/plugins/login-customiser
Description: Simple plugin to customise WP-Login
Author: Poetic Coding
Version: 0.1
Author URI: http://www.poeticcoding.co.uk
*/

class LoginCustomiser {
	const VERSION = '0.1';
	private $_settings;
	private $_optionsName = 'LoginCustomiser';
	private $_optionsGroup = 'LoginCustomiser-options';

	public function __construct() {
		$this->_getSettings();
		if(is_admin()) {
			add_action('admin_init', array($this, 'registerOptions'));
			add_action('admin_menu', array($this,'adminMenu'));
		}
		add_action('login_redirect', array($this, 'redirect_login'), 10, 3);
		register_activation_hook(__FILE__, array($this, 'activatePlugin'));
		register_deactivation_hook(__FILE__, array($this, 'deactivatePlugin'));
	}
	
	public function registerOptions() {
		register_setting($this->_optionsGroup, $this->_optionsName);
	}
	
	public function activatePlugin() {
		update_option($this->_optionsName, $this->_settings);
	}
	
	public function deactivatePlugin() {
		delete_option($this->_optionsName);
	}
	
	public function getSetting( $settingName, $default = false ) {
		if (empty($this->_settings)) {
			$this->_getSettings();
		}
		if ( isset($this->_settings[$settingName]) ) {
			return $this->_settings[$settingName];
		} else {
			return $default;
		}
	}
	
	private function _getSettings() {
		if(empty($this->_settings)) {
			$this->_settings = get_option($this->_optionsName);
		}
		if(!is_array($this->_settings)) {
			$this->_settings = array();
		}
		$defaults = array(
			'version'	=>	self::VERSION,
			'login_url'	=>	'home'
		);
		$this->_settings = wp_parse_args($this->_settings, $defaults);
	}
	
	public function adminMenu() {
		add_options_page('Login Customiser', 'Login Customiser', 'manage_options', 'LoginCustomiser', array($this, 'options'));
	}
	
	public function redirect_login($redirect_to, $url, $user) {
		if(isset($_POST['wp-submit']) && count($user->caps) > 0) {
			if($this->_settings['login_url'] == 'home') {
				$url = get_bloginfo('url');
			} else {
				$url = $this->_settings['login_url'];
				if($url[0]!='/') $url = '/'.$url;
			}
			wp_safe_redirect($url);
		}
	}
	
	public function options() {
		?>
		<div class="wrap">
			<?php screen_icon('tools'); ?><h2>Private Feed Key</h2>
			<form method="post" action="options.php">
				<?php settings_fields($this->_optionsGroup); ?>
				<?php do_settings_sections($this->_optionsGroup); ?>
				<fieldset class="options" style="border: none">
					<p>
						<em>Login Customiser</em> allows you to change which page on your site, a user is redirected to upon login.<br>
						<b>Please Note:</b> The URL must be relative to your WordPress site, or it will be ignored. This is a security feature built into WordPress.
					</p>
					<h3>Location</h3>
					<table class="form-table">
						<tr valign="top">
							<th width="200px" scope="row">URL</th>
							<td><input name="<?php echo $this->_optionsName; ?>[login_url]" id="<?php echo $this->_optionsName; ?>_login_url" type="text" value="<?php echo esc_attr($this->_settings['login_url']); ?>" /></td>
							<td><span style="color: #555; font-size: .85em;">Choose url</em></span></td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</fieldset>
			</form>
		</div>
		<?php
	}
	
}
$LoginCustomiser = new LoginCustomiser();
?>