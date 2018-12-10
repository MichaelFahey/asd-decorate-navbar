<?php
/**
 * The base code for the ASD Decorate Navbar plugin
 *
 * @package         ASD_Decorate_Navbar
 * Plugin Name:     ASD Decorate Navbar
 * Plugin URI:      https://artisansitedesigns.com/plugins/asd-decorate-navbar/
 * Description:     uses jQuery to prepend images or text to a standard Bootstrap navbar
 * Author:          Michael H Fahey
 * Author URI:      https://artisansitedesigns.com/staff/michael-h-fahey/
 * Text Domain:     asd_decorate_navbar
 * License:         GPL3
 * Version:         1.201812021
 *
 * ASD Decorate Navbar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * ASD Decorate Navbar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ASD Decorate Navbar. If not, see
 * https://www.gnu.org/licenses/gpl.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '' );
}

$asd_decorate_navbar_file_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
$asd_decorate_navbar_version   = $asd_decorate_navbar_file_data['Version'];

if ( ! defined( 'ASD_DECORATENAVBAR_DIR' ) ) {
	define( 'ASD_DECORATENAVBAR_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ASD_DECORATENAVBAR_URL' ) ) {
	define( 'ASD_DECORATENAVBAR_URL', plugin_dir_url( __FILE__ ) );
}

require_once 'includes/asd-admin-menu/asd-admin-menu.php';
require_once 'includes/asd-function-lib/asd-function-lib.php';

/** ----------------------------------------------------------------------------
 *   Function asd_decorate_navbar_admin_menu()
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_admin_menu() {

	/** ------------------------------------------------------------------------
	 *   add_submenu_page( string $parent_slug, string $page_title,
	 *                     string $menu_title, string $capability,
	 *                     string $menu_slug, callable $function = '' )
	 *  ----------------------------------------------------------------------*/
	add_submenu_page(
		'asd_settings',
		'Navbar Decorations',
		'Navbar Decorations',
		'manage_options',
		'asd_decorate_navbar_settings',
		'asd_decorate_navbar_screen'
	);
}
if ( is_admin() ) {
	add_action( 'admin_menu', 'asd_decorate_navbar_admin_menu', 12 );
}



/** ----------------------------------------------------------------------------
 *   Function asd_decorate_navbar_enqueues()
 *   Enqueues WordPress built-in jQuery, plugin-provided Bootstrap
 *   plugin css page.
 *   Hooks into wp_enqueue_scripts action
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_enqueues() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'asd-decorate-navbar', ASD_DECORATENAVBAR_URL . 'js/asd-decorate-navbar.js', array(), $asd_decorate_navbar_version, 'true' );
	wp_enqueue_style( 'asd-decorate-navbar', ASD_DECORATENAVBAR_URL . 'css/asd-decorate-navbar.css', array(), $asd_decorate_navbar_version );
}
add_action( 'wp_enqueue_scripts', 'asd_decorate_navbar_enqueues' );




/** ----------------------------------------------------------------------------
 *   function asd_decorate_navbar_wp_media_enqueues
 *   load js that facilitates the WP media selector
 *
 *   @param int $page  ID of page passed from action 'admin_enqueue_scripts'.
 */
function asd_decorate_navbar_wp_media_enqueues( $page ) {
	if ( 'artisan-site-designs_page_asd_decorate_navbar_settings' === $page ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'asd_decorate_navbar_wp_media_enqueues' );




/** ----------------------------------------------------------------------------
 *   Function asd_decorate_navbar_screen()
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient Permissions' );
	}
	?>
	<div class="wrap">
		<h1>Decorate Navbar Settings</h1>

		<div id="asd_decorate_navbar_fields_org">
			<form method="post" action="options.php">
				<?php
				/** -------------------------------------------------------------
				 *    settings_fields( $option_group )
				 *  ------------------------------------------------------------*/
				settings_fields( 'asd_decorate_navbar_section' );
				/** -------------------------------------------------------------
				 *    do_settings_sections( $page ).
				 *     (page = slug name of settings page)
				 *  ------------------------------------------------------------*/
				do_settings_sections( 'asd_decorate_navbar_settings' );
				submit_button( 'Save Navbar Settings' );
				?>
			</form>
		</div>

	</div>

	<?php
}



/** ----------------------------------------------------------------------------
 *   Function asd_decorate_navbar_register_settings()
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_register_settings() {

	/** ------------------------------------------------------------------------
	 *   add_settings_section( $id, $title, $callback, $page );
	 *  ----------------------------------------------------------------------*/
		add_settings_section(
			'asd_decorate_navbar_section',
			'Settings',
			'asd_decorate_navbar_fields',
			'asd_decorate_navbar_settings'
		);
	/** ------------------------------------------------------------------------
	 *   register_setting( string $option_group,
	 *                     string $option_name, array $args = array() )
	 *  ----------------------------------------------------------------------*/
		register_setting(
			'asd_decorate_navbar_section',
			'asd_decorate_navbar_text'
		);
		register_setting(
			'asd_decorate_navbar_section',
			'asd_decorate_navbar_display_select_setting'
		);
		register_setting(
			'asd_decorate_navbar_section',
			'asd_decorate_navbar_image'
		);

}
if ( is_admin() ) {
		add_action( 'admin_init', 'asd_decorate_navbar_register_settings' );
}


$display_select_settings = array(
	'Logo',
	'Logo and Text',
	'Image',
	'Image and Text',
	'Text only',
);







/** ----------------------------------------------------------------------------
 *   Function asd_decorate_navbar_fields()
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_fields() {

	/** ------------------------------------------------------------------------
		add_settings_field( $id, $title, $callback, $page, $section, $args );
	 *  ----------------------------------------------------------------------*/
	add_settings_field(
		'asd_decorate_navbar_text',
		'Navbar Text',
		'asd_fld_insert',
		'asd_decorate_navbar_settings',
		'asd_decorate_navbar_section',
		'asd_decorate_navbar_text'
	);

	global $display_select_settings;

	add_settings_field(
		'asd_decorate_navbar_display_select_setting',
		'What To Insert',
		'asd_select_option_insert',
		'asd_decorate_navbar_settings',
		'asd_decorate_navbar_section',
		array(
			'settingname'   => 'asd_decorate_navbar_display_select_setting',
			'selectoptions' => $display_select_settings,
		)
	);

	add_settings_field(
		'asd_decorate_navbar_image',
		'other Image',
		'asd_media_library_selector_control',
		'asd_decorate_navbar_settings',
		'asd_decorate_navbar_section',
		array(
			'settingname' => 'asd_decorate_navbar_image',
			'buttontext'  => 'Select Image',
		)
	);

}


/** ----------------------------------------------------------------------------
 *   function asd_decorate_navbar_image_ajax()
 *   hooks an ajax function which sends the attachment image, used only in the
 *   admin dashboard to update the image that has been selected from the
 *   media library.
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_image_ajax() {

	if ( isset( $_GET['image_nonce'] ) ) {
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['image_nonce'] ) ), 'asd_media_library_selector_control' ) ) {
			if ( isset( $_GET['id'] ) ) {

				$image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'medium', false, array( 'id' => 'asd_decorate_navbar_image_preview' ) );
				$data  = array(
					'image' => $image,
				);
				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}
		}
	}
}
add_action( 'wp_ajax_asd_decorate_navbar_image_action', 'asd_decorate_navbar_image_ajax' );



/** ----------------------------------------------------------------------------
 *   function asd_decorate_navbar_print_script()
 *   prints decorate navbar script to footer
 *  --------------------------------------------------------------------------*/
function asd_decorate_navbar_print_script() {
	$asd_decorate_navbar_display_select_setting = get_option( 'asd_decorate_navbar_display_select_setting' );
	$asd_decorate_navbar_text                   = get_option( 'asd_decorate_navbar_text' )['text_string'];
	$asd_decorate_navbar_image                  = get_option( 'asd_decorate_navbar_image' );

	echo '<script type="text/javascript">' . "\r\n";

	global $display_select_settings;

	if ( ( $display_select_settings[0] === $asd_decorate_navbar_display_select_setting ) ||
		( $display_select_settings[1] === $asd_decorate_navbar_display_select_setting ) ) {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$custom_logo_image_url = wp_get_attachment_image_src( $custom_logo_id, 'medium' );
			echo '   decorate_navbar_icon( "' . esc_url( $custom_logo_image_url[0] ) . '", "' . esc_url( get_home_url() ) . '" );' . "\r\n";
		}
	}

	if ( ( $display_select_settings[2] === $asd_decorate_navbar_display_select_setting ) ||
		( $display_select_settings[3] === $asd_decorate_navbar_display_select_setting ) ) {
		if ( $asd_decorate_navbar_image ) {
			$image_url = wp_get_attachment_image_src( $asd_decorate_navbar_image, 'medium' );
			echo '   decorate_navbar_icon( "' . esc_url( $image_url[0] ) . '", "' . esc_url( get_home_url() ) . '" );' . "\r\n";
		}
	}

	if ( ( $display_select_settings[1] === $asd_decorate_navbar_display_select_setting ) ||
		( $display_select_settings[3] === $asd_decorate_navbar_display_select_setting ) ||
		( $display_select_settings[4] === $asd_decorate_navbar_display_select_setting ) ) {
		if ( $asd_decorate_navbar_text ) {
			echo '   decorate_navbar_name( "' . esc_attr( $asd_decorate_navbar_text ) . '" );' . "\r\n";
		}
	}

	echo '</script>' . "\r\n";

}
add_filter( 'wp_print_footer_scripts', 'asd_decorate_navbar_print_script' );



/** ----------------------------------------------------------------------------
 *   Function asd_decorate_navbar_plugin_action_links()
 *   Adds links to the Dashboard Plugin page for this plugin.
 *   Hooks into the plugin_action_links_asd-decorate_navbar-widgets filter
 *  ----------------------------------------------------------------------------
 *
 *   @param Array $actions -  Returned as an array of html links.
 */
function asd_decorate_navbar_plugin_action_links( $actions ) {
	if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
		$actions[0] = '<a target="_blank" href="https://artisansitedesigns.com/plugins/asd-decorate_navbar/">Help</a>';
		$actions[1] = '<a href="' . admin_url() . 'admin.php?page=asd_decorate_navbar_settings">Settings</a>';
	}
		return apply_filters( 'asd_decorate_navbar_actions', $actions );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'asd_decorate_navbar_plugin_action_links' );
