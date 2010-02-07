<?php
/*
Plugin Name: Vanilla Single Sign-on
Plugin URI: http://vanillaforums.org/addons/
Description: Vanilla Single Sign-on allows users to create and manage their accounts & sessions through Wordpress, and be automatically signed into a related Vanilla forum.
Version: 1.0.0
Author: Mark O'Sullivan
Author URI: http://markosullivan.ca/
*/

/*
Copyright 2009 Mark O'Sullivan
This file is part of the Vanilla Single Sign-on plugin for WordPress 2.9.
The Vanilla Single Sign-on plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
The Vanilla Single Sign-on plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with the Vanilla Single Sign-on plugin.  If not, see <http://www.gnu.org/licenses/>.
Contact Mark O'Sullivan at mark [at] vanillaforums [dot] com
*/

define('VANILLA_COOKIE', 'Vanilla'); // <-- You might need to change this if you've customized your cookie name in vanilla.

// Check to see if we should kill processing and display signed in user info
if (is_array($_GET) && array_key_exists('VanillaChallengeKey', $_GET)) {
	$VanillaChallengeKey = get_option('vanilla_sso_key');
	if ($VanillaChallengeKey != '' && $VanillaChallengeKey == $_GET['VanillaChallengeKey']) {
		// If the challenge key was provided to WordPress, and it validates to the one saved in this system (and it's not empty)
		// Spit out some json_encoded data about the user for Vanilla to use when authenticating/creating user accounts
		global $current_user;
		if (!function_exists('get_currentuserinfo'))
			require (ABSPATH . WPINC . '/pluggable.php');
			
      get_currentuserinfo();
		// Only report user info if the user is authenticated
		if ($current_user->ID != '') { 
			echo "UniqueID=".$current_user->ID
				."\nName=".$current_user->display_name
				."\nEmail=".$current_user->user_email
				."\nTransientKey=".wp_create_nonce('log-out') // Vanilla's "TransientKey" is the equivalent of WordPress' "wpnonce".
				."\nDateOfBirth=" // Format: 1975-09-16
				."\nGender=Male";
		}
		exit();
	}
}

add_action('admin_menu', 'vanilla_sso_menu');
function vanilla_sso_menu() {
  add_options_page('Vanilla Single Sign-on', 'Vanilla Single Sign-on', 8, 'vanilla-sso', 'vanilla_sso_options');
  add_submenu_page('VSSO', 'VSSO', 'VSSO', 8, 'vanilla-sso-info', 'vanilla_sso_info');
}

function vanilla_sso_options() {
	if (isset($_POST['save'])) {
		if (function_exists('current_user_can') && !current_user_can('manage_options'))
			die(__('Permission Denied'));
			
		$VanillaCookieDomain = array_key_exists('vanilla_cookie_domain', $_POST) ? $_POST['vanilla_cookie_domain'] : '';
		update_option('vanilla_cookie_domain', $VanillaCookieDomain);
	} else {
		$VanillaCookieDomain = get_option('vanilla_cookie_domain');
	}
	
	$Key = get_option('vanilla_sso_key');
	if ($Key == '') {
		$Characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
      $Key = '' ;
      for ($i = 0; $i < 16; ++$i) {
        $Offset = rand() % 35;
        $Key .= substr($Characters, $Offset, 1);
      }
		update_option('vanilla_sso_key', $Key);
	}
	$VanillaSignoutUrl = get_option('vanilla_signout_url');
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('Vanilla Single Sign-on Configuration'); ?></h2>
	<h3><?php _e('Vanilla Settings for WordPress'); ?></h3>
	<p>Grab this value from the single sign-on configuration screen in your Vanilla installation:</p>
	<form method="post">
		<table class="form-table">
			<tr>
				<th>Vanilla's Cookie Domain</th>
				<td><input type="text" name="vanilla_cookie_domain" value="<?php echo $VanillaCookieDomain; ?>" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="save" value="<?php _e('Save &raquo;'); ?>" /></p>
	</form>
	<h3><?php _e('WordPress Settings for Vanilla'); ?></h3>
	<p>Copy & paste the following information into the single sign-on configuration screen in your Vanilla installation:</p>
	<table class="form-table">
		<tr>
			<th>Authenticate Url</th>
			<td><span class="description"><?php echo site_url('?VanillaChallengeKey='.$Key, 'vanilla-sso-info'); ?></span></td>
		</tr>
		<tr>
			<th>Registration Url</th>
			<td><span class="description"><?php echo site_url('wp-login.php?action=register', 'login'); ?></span></td>
		</tr>
		<tr>
			<th>Sign-in Url</th>
			<td><span class="description"><?php echo wp_login_url(); ?>?redirect_to=%s</span></td>
		</tr>
		<tr>
			<th>Sign-out Url</th>
			<td><span class="description"><?php
				echo add_query_arg(array('action' => 'logout', '_wpnonce' => '{Session_TransientKey}'), site_url('wp-login.php', 'login'));
			?></span></td>
		</tr>
	</table>
</div>
<?php
}

// Make sure that wordpress cookies don't set the path variable and are accessible to all subdomains.
if ( !function_exists('wp_set_auth_cookie') ) :
/**
 * Sets the authentication cookies based User ID.
 *
 * The $remember parameter increases the time that the cookie will be kept. The
 * default the cookie is kept without remembering is two days. When $remember is
 * set, the cookies will be kept for 14 days or two weeks.
 *
 * @since 2.5
 *
 * @param int $user_id User ID
 * @param bool $remember Whether to remember the user or not
 */
function wp_set_auth_cookie($user_id, $remember = false, $secure = '') {
	if ( $remember ) {
		$expiration = $expire = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, $remember);
	} else {
		$expiration = time() + apply_filters('auth_cookie_expiration', 172800, $user_id, $remember);
		$expire = 0;
	}

	if ( '' === $secure )
		$secure = is_ssl() ? true : false;

	if ( $secure ) {
		$auth_cookie_name = SECURE_AUTH_COOKIE;
		$scheme = 'secure_auth';
	} else {
		$auth_cookie_name = AUTH_COOKIE;
		$scheme = 'auth';
	}

	$auth_cookie = wp_generate_auth_cookie($user_id, $expiration, $scheme);
	$logged_in_cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

	do_action('set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme);
	do_action('set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in');
	$VanillaCookiePath = '/';
	$VanillaCookieDomain = get_option('vanilla_cookie_domain');

	// Set httponly if the php version is >= 5.2.0
	if ( version_compare(phpversion(), '5.2.0', 'ge') ) {
		setcookie($auth_cookie_name, $auth_cookie, $expire, $VanillaCookiePath, $VanillaCookieDomain, $secure, true);
		setcookie($auth_cookie_name, $auth_cookie, $expire, $VanillaCookiePath, $VanillaCookieDomain, $secure, true);
		setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, $VanillaCookiePath, $VanillaCookieDomain, false, true);
		if ( COOKIEPATH != SITECOOKIEPATH )
			setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, $VanillaCookiePath, $VanillaCookieDomain, false, true);
	} else {
		$cookie_domain = $VanillaCookieDomain;
		if ( !empty($cookie_domain) )
			$cookie_domain .= '; HttpOnly';
		setcookie($auth_cookie_name, $auth_cookie, $expire, $VanillaCookiePath, $cookie_domain, $secure);
		setcookie($auth_cookie_name, $auth_cookie, $expire, $VanillaCookiePath, $cookie_domain, $secure);
		setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, $VanillaCookiePath, $cookie_domain);
		if ( COOKIEPATH != SITECOOKIEPATH )
			setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, $VanillaCookiePath, $cookie_domain);
	}
}
endif;

if ( !function_exists('wp_clear_auth_cookie') ) :
/**
 * Removes all of the cookies associated with authentication.
 *
 * @since 2.5
 */
function wp_clear_auth_cookie() {
	do_action('clear_auth_cookie');
	
	$VanillaCookiePath = '/';
	$VanillaCookieDomain = get_option('vanilla_cookie_domain');

	setcookie(AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);

	// Old cookies
	setcookie(AUTH_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(AUTH_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
	setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);

	// Even older cookies
	setcookie(USER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(USER_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);

	// WordPress' new Vanilla SSO cookies
	setcookie(AUTH_COOKIE, ' ', time() - 31536000, $VanillaCookiePath, $VanillaCookieDomain);
	setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, $VanillaCookiePath, $VanillaCookieDomain);
	setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, $VanillaCookiePath, $VanillaCookieDomain);
	setcookie(USER_COOKIE, ' ', time() - 31536000, $VanillaCookiePath, $VanillaCookieDomain);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, $VanillaCookiePath, $VanillaCookieDomain);
	
	// Destroy Vanilla's session cookie as well
	setcookie(VANILLA_COOKIE, ' ', time() - 31536000, $VanillaCookiePath, $VanillaCookieDomain);
}
endif;