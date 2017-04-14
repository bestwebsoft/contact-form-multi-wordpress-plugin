<?php
/*
Plugin Name: Contact Form Multi by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/contact-form-multi/
Description: Add unlimited number of contact forms to WordPress website.
Author: BestWebSoft
Text Domain: contact-form-multi
Domain Path: /languages
Version: 1.2.1
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*
	@ Copyright 2017  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
** Function for adding menu and submenu
*/
if ( ! function_exists( 'cntctfrmmlt_admin_menu' ) ) {
	function cntctfrmmlt_admin_menu() {
		bws_general_menu();
	}
}

/*Function for connecting hooks-(init, admin_init)*/
if ( ! function_exists( 'cntctfrmmlt_init' ) ) {
	function cntctfrmmlt_init() {
		global $cntctfrmmlt_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $cntctfrmmlt_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$cntctfrmmlt_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $cntctfrmmlt_plugin_info, '3.8' );
	}
}

/*Function for connecting hooks-(init, admin_init)*/
if ( ! function_exists( 'cntctfrmmlt_admin_init' ) ) {
	function cntctfrmmlt_admin_init() {
		global $bws_plugin_info, $cntctfrmmlt_plugin_info;

		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '123', 'version' => $cntctfrmmlt_plugin_info["Version"] );

		/* check for installed and activated Contact Form*/
		cntctfrmmlt_check();

		if ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] == 'contact_form.php' || $_REQUEST['page'] == 'contact_form_pro.php' || $_REQUEST['page'] == 'contact_form_pro_extra.php' ) ) {
			/*register defaults settings function*/
			cntctfrmmlt_settings_defaults();
			/*register main options function*/
			cntctfrmmlt_main_options();
		}
	}
}

if ( ! function_exists( 'cntctfrmmlt_plugins_loaded' ) ) {
	function cntctfrmmlt_plugins_loaded() {
		/* add language files. */
		load_plugin_textdomain( 'contact-form-multi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists( 'cntctfrmmlt_settings_defaults' ) ) {
	function cntctfrmmlt_settings_defaults() {
		global $cntctfrmmlt_options, $cntctfrmmlt_plugin_info, $cntctfrmmlt_options_main;

		/* Install the option defaults */
		$cntctfrmmlt_options_main = array(
			'plugin_option_version' => $cntctfrmmlt_plugin_info["Version"],
			'name_id_form'	=> array( 1 => 'NEW_FORM' ),
			'next_id_form'	=> 2,
			'id_form'		=> 1,
			'first_install'	=>	strtotime( "now" )
		);
		/*add options to database*/
		if ( ! get_option( 'cntctfrmmlt_options_main' ) )
			add_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );

		/* Get options from the database */
		$cntctfrmmlt_options = get_option( 'cntctfrmmlt_options_main' );

		if ( ! isset( $cntctfrmmlt_options['plugin_option_version'] ) || $cntctfrmmlt_options['plugin_option_version'] != $cntctfrmmlt_plugin_info["Version"] ) {
			$cntctfrmmlt_options = array_merge( $cntctfrmmlt_options_main, $cntctfrmmlt_options );
			$cntctfrmmlt_options['plugin_option_version'] = $cntctfrmmlt_plugin_info["Version"];
			update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options );
			cntctfrmmlt_plugin_activate();
		}
	}
}

/**
 * Activation plugin function
 */
if ( ! function_exists( 'cntctfrmmlt_plugin_activate' ) ) {
	function cntctfrmmlt_plugin_activate() {
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'cntctfrmmlt_delete' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'cntctfrmmlt_delete' );
		}
	}
}

/* ads feature the main options */
if ( ! function_exists( 'cntctfrmmlt_main_options' ) ) {
	function cntctfrmmlt_main_options() {
		global $cntctfrmmlt_counts, $cntctfrmmlt_id_form, $key, $cntctfrmmlt_keys, $cntctfrmmlt_last_key, $cntctfrmmlt_options_main, $value;
		$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
		if ( ! isset( $_GET['id'] ) )
			$_SESSION['cntctfrmmlt_id_form'] = $cntctfrmmlt_options_main['id_form'];

		/*Update cntctfrmmlt_id_options in a database*/
		if ( isset( $_GET['id'] ) )
			$_SESSION['cntctfrmmlt_id_form'] = $_GET['id'];

		$cntctfrmmlt_options_main['id_form'] = $_SESSION['cntctfrmmlt_id_form'];
		update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );

		/*Deleting data from the database after pressing the delete*/
		if ( isset( $_GET['del'] ) ) {

			/*Remove the contact form from the database*/
			$cntctfrmmlt_args = 'cntctfrmmlt_options_' . $_GET['id'];
			delete_option( $cntctfrmmlt_args );
			/*Remove the contact form from the database*/

			/*remove values from a name_id_form*/
			$cntctfrmmlt_counts = $cntctfrmmlt_options_main['name_id_form'];
			unset( $cntctfrmmlt_counts[$cntctfrmmlt_options_main['id_form']] );
			$cntctfrmmlt_options_main['name_id_form'] = $cntctfrmmlt_counts;
			/*remove values from a name_id_form*/

			$cntctfrmmlt_keys = array_keys( $cntctfrmmlt_options_main['name_id_form'] );
			$cntctfrmmlt_last_key = end( $cntctfrmmlt_keys );
			$cntctfrmmlt_options_main['id_form'] = $cntctfrmmlt_last_key;
			update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
			$_SESSION['cntctfrmmlt_id_form'] = $cntctfrmmlt_last_key;
			if ( empty( $cntctfrmmlt_options_main['name_id_form'] ) ) {
				$cntctfrmmlt_options_main['id_form'] = 1;
				$cntctfrmmlt_options_main['name_id_form'] = array( 1 => 'NEW_FORM' );
				$cntctfrmmlt_options_main['next_id_form'] = 2;
				update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
				$_SESSION['cntctfrmmlt_id_form'] = 1;
			}
		}
	}
}

/* Function creates other links on admin page. */
if ( ! function_exists ( 'cntctfrmmlt_plugin_links' ) ) {
	function cntctfrmmlt_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="https://wordpress.org/plugins/contact-form-multi/faq/" target="_blank">' . __( 'FAQ','contact-form-multi' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support','contact-form-multi' ) . '</a>';
		}
		return $links;
	}
}

/*Update array cntctfrmmlt_options_main in a database*/
if ( ! function_exists ( 'cntctfrmmlt_action_callback' ) ) {
	function cntctfrmmlt_action_callback() {
		global $cntctfrmmlt_counts, $cntctfrmmlt_j, $cntctfrmmlt_key_form, $cntctfrmmlt_value, $cntctfrmmlt_id_key, $cntctfrmmlt_options_main;
		check_ajax_referer( plugin_basename( __FILE__ ), 'cntctfrmmlt_ajax_nonce_field' );
		$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
		/*update next_id_form, cntctfrmmlt_id_options*/
		if ( isset( $_POST['cntctfrmmlt_key_form'] ) ) {
			$cntctfrmmlt_id_key = $_POST['cntctfrmmlt_key_form'];
			$cntctfrmmlt_id_key += 1;
			$cntctfrmmlt_options_main['next_id_form'] = $cntctfrmmlt_id_key;
			$cntctfrmmlt_options_main['id_form'] = $_POST['cntctfrmmlt_key_form'];
			$_SESSION['cntctfrmmlt_id_form'] = $_POST['cntctfrmmlt_key_form'];
		}
		/*Update name and ID, options*/
		if ( isset( $_POST['cntctfrmmlt_name_form'] ) ) {
			foreach ( $_POST['cntctfrmmlt_name_form'] as $cntctfrmmlt_j ) {
				list( $key, $cntctfrmmlt_value ) = explode( ':', $cntctfrmmlt_j );
				$cntctfrmmlt_counts[$key] = strip_tags( stripslashes( $cntctfrmmlt_value ) );
				$cntctfrmmlt_options_main['name_id_form'] = $cntctfrmmlt_counts;
			}
		}
		update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
		exit;
	}
}

/*Function to add stylesheets and scripts for admin bar*/
if ( ! function_exists ( 'cntctfrmmlt_scripts' ) ) {
	function cntctfrmmlt_scripts() {
		global $cntctfrmmlt_plugin_info;
		if ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] == 'contact_form.php' || $_REQUEST['page'] == 'contact_form_pro.php' || $_REQUEST['page'] == 'contact_form_pro_extra.php' ) ) {

			wp_enqueue_style( 'cntctfrmml_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $cntctfrmmlt_plugin_info['Version'] );
			wp_enqueue_script( 'cntctfrmmlt_script', plugins_url( 'js/script.js', __FILE__ ), array(), $cntctfrmmlt_plugin_info['Version'] );

			/* script vars */
			$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
			$site_url_if_multisite = is_multisite() ? site_url() : '';

			$cntctfrmmlt_count = array();
			if ( $cntctfrmmlt_options_main ) {
				foreach ( $cntctfrmmlt_options_main['name_id_form'] as $key => $value ) {
					$cntctfrmmlt_count[ $key ] = $value;
				}
			}

			$script_vars = array(
				'cntctfrmmlt_nonce' 			=> wp_create_nonce( plugin_basename( __FILE__ ), 'cntctfrmmlt_ajax_nonce_field' ),
				'cntctfrmmlt_delete_message' 	=> __( 'Are you sure you want to delete the form?', 'contact-form-multi' ),
				'cntctfrmmlt_id_form' 			=> $cntctfrmmlt_options_main['id_form'],
				'cntctfrmmlt_location'			=> $site_url_if_multisite . $_SERVER['PHP_SELF'] . ( is_plugin_active( 'contact-form-plugin/contact_form.php' ) ? '?page=contact_form.php' : '?page=contact_form_pro.php' ),
				'cntctfrmmlt_action_slug'		=> ( isset( $_GET['action'] ) ? '&action=' . $_GET['action'] : '' ),
				'cntctfrmmlt_key_id' 			=> $cntctfrmmlt_options_main['next_id_form'],
				'cntctfrmmlt_count'				=> $cntctfrmmlt_count,
			);
			wp_localize_script( 'cntctfrmmlt_script', 'cntctfrmmlt_script_vars', $script_vars );
		}
	}
}

/* Сhecking for the existence of Contact Form Plugin or Contact Form Pro Plugin */
if ( ! function_exists( 'cntctfrmmlt_check' ) ) {
	function cntctfrmmlt_check() {
		global $cntctfrmmlt_contact_form_not_found, $cntctfrmmlt_contact_form_not_active;
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();

		if ( ! ( array_key_exists( 'contact-form-plugin/contact_form.php', $all_plugins ) || array_key_exists( 'contact-form-pro/contact_form_pro.php', $all_plugins ) ) ) {
			$cntctfrmmlt_contact_form_not_found = __( 'Contact Form Plugin has not been found.', 'contact-form-multi' ) . '</br>' . __( 'You should install and activate this plugin for the correct work with Contact Form Multi plugin.', 'contact-form-multi' ) . '</br>' . __( 'You can download Contact Form Plugin from', 'contact-form-multi' ) . ' <a href="' . esc_url( 'https://bestwebsoft.com/products/wordpress/plugins/contact-form/' ) . '" title="' . __( 'Developers website', 'contact-form-multi' ). '"target="_blank">' . __( 'website of plugin Authors', 'contact-form-multi' ) . '</a> ' . __( 'or', 'contact-form-multi' ) . ' <a href="' . esc_url( 'https://wordpress.org' ) .'" title="Wordpress" target="_blank">'. __( 'Wordpress.', 'contact-form-multi' ) . '</a>';
		} else {
			if ( ! ( is_plugin_active( 'contact-form-plugin/contact_form.php' ) || is_plugin_active( 'contact-form-pro/contact_form_pro.php' ) ) ) {
				$cntctfrmmlt_contact_form_not_active = __( 'Contact Form Plugin is not active.', 'contact-form-multi' ) . '</br>' . __( 'You should activate this plugin for the correct work with Contact Form Multi plugin.', 'contact-form-multi' );
			}
			/* old version */
			if ( ( is_plugin_active( 'contact-form-plugin/contact_form.php' ) && isset( $all_plugins['contact-form-plugin/contact_form.php']['Version'] ) && $all_plugins['contact-form-plugin/contact_form.php']['Version'] < '3.74' ) ||
				( is_plugin_active( 'contact-form-pro/contact_form_pro.php' ) && isset( $all_plugins['contact-form-pro/contact_form_pro.php']['Version'] ) && $all_plugins['contact-form-pro/contact_form_pro.php']['Version'] < '1.23' ) ) {
				$cntctfrmmlt_contact_form_not_found = __( 'Contact Form Plugin has old version.', 'contact-form-multi' ) . '</br>' . __( 'You need to update this plugin for correct work with Contact Form Multi plugin.', 'contact-form-multi' );
			}
		}
	}
}

/*Add notises on plugins page if Contact Form plugin is not installed or not active*/
if ( ! function_exists( 'cntctfrmmlt_show_notices' ) ) {
	function cntctfrmmlt_show_notices() {
		global $hook_suffix, $cntctfrmmlt_contact_form_not_found, $cntctfrmmlt_contact_form_not_active;
		if ( $hook_suffix == 'plugins.php' || ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'bws_panel' ) || ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] == 'contact_form.php' || $_REQUEST['page'] == 'contact_form_pro.php' ) ) ) {
			if ( '' != $cntctfrmmlt_contact_form_not_found || '' != $cntctfrmmlt_contact_form_not_active ) { ?>
				<div class="error">
					<p><strong><?php _e( 'WARNING:', 'contact-form-multi' ); ?></strong> <?php echo $cntctfrmmlt_contact_form_not_found . $cntctfrmmlt_contact_form_not_active; ?></p>
				</div>
			<?php } ?>
			<noscript>
				<div class="error">
					<p><?php _e( 'Please enable JavaScript in your browser!', 'contact-form-multi'); ?></p>
				</div>
			</noscript>
		<?php }
	}
}

if ( ! function_exists ( 'cntctfrmmlt_plugin_banner' ) ) {
	function cntctfrmmlt_plugin_banner() {
		global $hook_suffix;
		if ( 'plugins.php' == $hook_suffix ) {
			global $cntctfrmmlt_plugin_info, $wp_version;
			$cntctfrmmlt_options = get_option( 'cntctfrmmlt_options_main' );
			if ( isset( $cntctfrmmlt_options['first_install'] ) && strtotime( '-1 week' ) > $cntctfrmmlt_options['first_install'] ) {
				bws_plugin_banner( $cntctfrmmlt_plugin_info, 'cntctfrmmlt', 'contact-form-multi', '93536843024dbb3360bfa9d6d6a1d297', '123', '//ps.w.org/contact-form-multi/assets/icon-128x128.png' );
			}
		}
	}
}

/*Function for delete options*/
if ( ! function_exists ( 'cntctfrmmlt_delete' ) ) {
	function cntctfrmmlt_delete() {
		global $wpdb;
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'contact-form-multi-pro/contact-form-multi-pro.php', $all_plugins ) ) {
			if ( ! is_multisite() ) {
				$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
				foreach ( $cntctfrmmlt_options_main['name_id_form'] as $key => $value ) {
					delete_option( 'cntctfrmmlt_options_' . $key );
				}
				delete_option( 'cntctfrmmlt_options_main' );
				delete_option( 'cntctfrmmlt_options' );
			} else {
				$cntctfrmmlt_blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				$cntctfrmmlt_original_blog_id = get_current_blog_id();
				foreach ( $cntctfrmmlt_blog_ids as $cntctfrmmlt_blog_id ) {
					switch_to_blog( $cntctfrmmlt_blog_id );
					$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
					foreach ( $cntctfrmmlt_options_main['name_id_form'] as $key => $value ) {
						delete_option( 'cntctfrmmlt_options_'. $key );
					}
					delete_option( 'cntctfrmmlt_options_main' );
					delete_option( 'cntctfrmmlt_options' );
				}
				switch_to_blog( $cntctfrmmlt_original_blog_id );
			}
		}

		/**
		 * @deprecated since 1.2.0
		 * @todo remove after 01.06.2017
		 * clearing 'uninstall_plugins' option
		 */
		if ( is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				$uninstallable_plugins = (array) get_option('uninstall_plugins');
				unset( $uninstallable_plugins[ plugin_basename( __FILE__ ) ] );
				update_option('uninstall_plugins', $uninstallable_plugins);
			}
			switch_to_blog( $old_blog );
		}
		/* deprecated (end) */

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'cntctfrmmlt_plugin_activate' );
/* hook for add menu */
add_action( 'admin_menu', 'cntctfrmmlt_admin_menu' );
/* Hook calls functions for init and admin_init hooks */
add_action( 'init', 'cntctfrmmlt_init' );
add_action( 'admin_init', 'cntctfrmmlt_admin_init' );
add_action( 'plugins_loaded', 'cntctfrmmlt_plugins_loaded' );
/* hook for adding scripts and styles */
add_action( 'admin_enqueue_scripts', 'cntctfrmmlt_scripts' );
/* Additional links on the plugin page*/
add_filter( 'plugin_row_meta', 'cntctfrmmlt_plugin_links', 10, 2 );
/* check for installed and activated Contact Form plugin */
add_action( 'admin_notices', 'cntctfrmmlt_show_notices' );
/* hooks for ajax */
add_action( 'wp_ajax_cntctfrmmlt_action', 'cntctfrmmlt_action_callback' );
add_action( 'admin_notices', 'cntctfrmmlt_plugin_banner' );