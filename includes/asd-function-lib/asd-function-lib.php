<?php
/**
 * Generic functions for use in WordPress plugins,
 *
 * @package    WordPress
 * @subpackage ASD_Function_Lib
 * Author:       Michael H Fahey
 * Author URI:   https://artisansitedesigns.com/staff/michael-h-fahey
 * Version:      1.201812042
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '' );
}

$asd_asdfunctionlib_file_data  = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
$this_asd_function_lib_version = $asd_asdfunctionlib_file_data['Version'];

if ( ! isset( $asd_function_lib_version ) ) {
	$asd_function_lib_version = $this_asd_function_lib_version;
} else {
	if ( $this_asd_function_lib_version > $asd_function_lib_version ) {
		$asd_function_lib_version = $this_asd_function_lib_version;
	}
}



if ( ! function_exists( 'asd_function_lib_widgets_enqueues' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_function_lib_widgets_enqueues()
	 *   Enqueues WordPress built-in jQuery, plugin-provided Bootstrap
	 *   plugin css page.
	 *   Hooks into wp_enqueue_scripts action
	 *
	 *   @param int $page - passed by the admin_enqueue_scripts action.
	 */
	function asd_function_lib_widgets_enqueues( $page ) {
		global $this_asd_function_lib_version;
		if ( 'artisan-site-designs_page_asd_decorate_navbar_settings' === $page ) {
			wp_enqueue_style( 'asd-function-lib', plugin_dir_url( __FILE__ ) . 'css/asd-function-lib.css', array(), $this_asd_function_lib_version );
		}
	}
	add_action( 'admin_enqueue_scripts', 'asd_function_lib_widgets_enqueues' );
}



/** ----------------------------------------------------------------------------
 *   some prefab arrays that come in handy
 *  ------------------------------------------------------------------------- */

if ( ! isset( $asd_truefalse_array ) ) {
	/** ----------------------------------------------------------------------------
	 *  array to populate true/false options
	 */
	$asd_truefalse_array = array(
		'true',
		'false',
	);
}

if ( ! isset( $asd_cpt_dashboard_display_options ) ) {
	/** ----------------------------------------------------------------------------
	 *   array to populate where custom post types show in dashboard
	 *  --------------------------------------------------------------------------*/
	$asd_cpt_dashboard_display_options = array( 'Both Menus', 'Main Dashboard Menu', 'ASD Submenu' );
}




if ( ! function_exists( 'asd_media_library_selector_control' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_media_library_selector_control( $params_array )
	 *  ----------------------------------------------------------------------------
	 *
	 *   @param array $params_array -  array of options for the select.
	 */
	function asd_media_library_selector_control( $params_array ) {

		$settingname = $params_array['settingname'];

		$settingval = '';
		$settingurl = '';
		if ( $settingname ) {
			$settingval = get_option( $settingname );
		}
		if ( $settingval ) {
			$settingurl = wp_get_attachment_url( $settingval );
		}
		echo '<div class="asd-media-library-selector-preview">' . "\r\n";
		echo '<img id="' . esc_attr( $settingname ) . '_preview' .
				'" src="' . esc_url( $settingurl ) . '" /><br>' . "\r\n";
		echo '</div>' . "\r\n";

		echo '<input type="hidden" name="' . esc_attr( $settingname ) . '" id="' .
		esc_attr( $settingname ) . '" value="' . esc_attr( $settingval ) . '" class="regular-text" />' . "\r\n";

		echo '<input type="button" class="button-primary" value="' . esc_attr( $params_array['buttontext'] ) .
				'" id="' . esc_attr( $settingname ) . '_button"/>';

		?>
			<script type="text/javascript">
			jQuery( document ).ready( function($) {
				jQuery( 'input#<?php echo esc_attr( $settingname . '_button' ); ?>' ).click(
				function(e) {
					e.preventDefault();
					var image_frame; 
					if (image_frame) {
						image_frame.open();
					}
					image_frame = wp.media( {
						title: 'Select Media',
						multiple : false,
						library : {
							type : 'image',
						}
					});
					image_frame.on( 'close',function() { 
						var selection   = image_frame.state().get( 'selection' );
						var gallery_ids = new Array();
						var my_index = 0;
						selection.each( function(attachment) {
							gallery_ids[my_index] = attachment['id'];
							my_index++;
						});
						var ids = gallery_ids.join( "," );
						jQuery( 'input#<?php echo esc_attr( $settingname ); ?>' ).val( ids );
						Refresh_Image( ids );
					});
					image_frame.on( 'open',function() {
						var selection = image_frame.state().get( 'selection' );
						ids           = jQuery( 'input#<?php echo esc_attr( $settingname ); ?>' ).val().split( ',' );
						ids.forEach( function(id) {
							attachment = wp.media.attachment( id );
							attachment.fetch();
							selection.add( attachment ? [ attachment ] : [] );
						});
					});
					image_frame.open();
				}
			);
		});

			// Ajax request to refresh the image preview
			function Refresh_Image (the_id){
				var data = { 
					action: '<?php echo esc_attr( $settingname . '_action' ); ?>',
					id: the_id,
					image_nonce: '<?php echo wp_create_nonce( 'asd_media_library_selector_control' ); ?>'
				};
				jQuery.get(ajaxurl, data, function(response) {

					if(response.success === true) {
						jQuery('#<?php echo esc_attr( $settingname . '_preview' ); ?>').replaceWith( response.data.image ).attr("id", "<?php echo esc_attr( $settingname . '_preview' ); ?>"  );
					}
				});
			}
			</script>

			<?php

	}
}



if ( ! function_exists( 'asd_select_option_insert' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_select_option_insert( $params_array )
	 *  ----------------------------------------------------------------------------
	 *
	 *   @param array $params_array -  array of options for the select.
	 */
	function asd_select_option_insert( $params_array ) {
		$settingname   = $params_array['settingname'];
		$selectoptions = $params_array['selectoptions'];
		?>

		<select style="width:200px;display:inline-block;" class="postform" id="<?php echo esc_attr( $settingname ) . '.fld'; ?>" name="<?php echo esc_attr( $settingname ); ?>"> 
			<?php
			foreach ( $selectoptions as $array_value ) :
				$selected = '';
				if ( get_option( $settingname ) === $array_value ) {
					$selected = 'selected';
				}
				?>
				<option value="<?php echo esc_attr( $array_value ); ?>" class="" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $array_value ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}



if ( ! function_exists( 'asd_truefalse_select_insert' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_truefalse_select_insert( $settingname )
	 *   A shortcut function to create an HTML select field based only on a
	 *   setting name
	 *  ----------------------------------------------------------------------------
	 *
	 *   @param string $settingname -  the name of the setting.
	 */
	function asd_truefalse_select_insert( $settingname ) {
		global $asd_truefalse_array;
		?>

		<select style="width:200px;display:inline-block;" class="postform" id="<?php echo esc_attr( $settingname ) . '.fld'; ?>" name="<?php echo esc_attr( $settingname ); ?>"> 
		<?php
		foreach ( $asd_truefalse_array as $array_value ) :
			$selected = '';
			if ( get_option( $settingname ) === $array_value ) {
				$selected = 'selected';
			}
			?>
			<option value="<?php echo esc_attr( $array_value ); ?>" class="" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $array_value ); ?></option>
		<?php endforeach; ?>
		</select>
		<?php
	}
}

if ( ! function_exists( 'asd_fld_insert' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_fld_insert( $settingname )
	 *   A shortcut function to create an HTML input field based only on a
	 *   setting name
	 *  ----------------------------------------------------------------------------
	 *
	 *   @param string $settingname -  the name of the setting.
	 */
	function asd_fld_insert( $settingname ) {
		echo '<input id="' . esc_attr( $settingname ) . '_fld" ';
		echo 'name="' . esc_attr( $settingname ) . '[text_string]" ';
		echo 'value="' . esc_attr( get_option( $settingname )['text_string'] ) . '" ';
		echo " size='40' type='text' />";
		echo "\r\n";
	}
}


if ( ! function_exists( 'asd_fld_insert_narrow' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_fld_insert_narrow( $settingname )
	 *   A shortcut function to create a narrower HTML input field based only on a
	 *   setting name
	 *  ----------------------------------------------------------------------------
	 *
	 *   @param string $settingname -  the name of the setting.
	 */
	function asd_fld_insert_narrow( $settingname ) {
		echo '<input id="' . esc_attr( $settingname ) . '_fld" ';
		echo 'name="' . esc_attr( $settingname ) . '[text_string]" ';
		echo 'value="' . esc_attr( get_option( $settingname )['text_string'] ) . '" ';
		echo " size='10' type='text' />";
		echo "\r\n";
	}
}

if ( ! function_exists( 'asd_fld_insert_narrow6' ) ) {
	/** ----------------------------------------------------------------------------
	 *   Function asd_fld_insert_narrow6( $settingname )
	 *   A shortcut function to create a narrower HTML input field based only on a
	 *   setting name
	 *  ----------------------------------------------------------------------------
	 *
	 *   @param string $settingname -  the name of the setting.
	 */
	function asd_fld_insert_narrow6( $settingname ) {
		echo '<input id="' . esc_attr( $settingname ) . '_fld" ';
		echo 'name="' . esc_attr( $settingname ) . '" ';
		echo 'value="' . esc_attr( get_option( $settingname ) ) . '" ';
		echo " size='5' type='text' />";
		echo "\r\n";
	}
}

if ( ! function_exists( 'short_content' ) ) {
	/** ----------------------------------------------------------------------------
	 *   similar to an excerpt, the first 296 characters of content broken
	 *   between words at whitespace, with tags forced to balance
	 *
	 *   @param string $text  -  the content to be excerpted.
	 */
	function short_content( $text ) {
		$length = 295;
		if ( strlen( $text ) < $length + 10 ) {
			return $text;
		} else {
			$break_pos = strpos( $text, ' ', $length );
			$visible   = substr( $text, 0, $break_pos );
			return balanceTags( $visible, true );
		}
	}
}


