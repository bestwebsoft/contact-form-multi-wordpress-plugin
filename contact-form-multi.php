<?php
/**
Plugin Name: Contact Form Multi by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/contact-form-multi/
Description: Add unlimited number of contact forms to WordPress website.
Author: BestWebSoft
Text Domain: contact-form-multi
Domain Path: /languages
Version: 1.3.1
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
 */

/*
	@ Copyright 2021  BestWebSoft  ( https://support.bestwebsoft.com )

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

if ( ! function_exists( 'cntctfrmmlt_admin_menu' ) ) {
	/**
	 * Function for adding menu and submenu
	 */
	function cntctfrmmlt_admin_menu() {
		bws_general_menu();
	}
}

if ( ! function_exists( 'cntctfrmmlt_init' ) ) {
	/**
	 * Function for connecting hooks-(init, admin_init)
	 */
	function cntctfrmmlt_init() {
		global $cntctfrmmlt_plugin_info;

		if ( in_array( 'contact-form-pro/contact_form_pro.php', (array) get_option( 'active_plugins', array() ), true ) || ( is_multisite() && in_array( 'contact-form-pro/contact_form_pro.php', (array) get_site_option( 'active_sitewide_plugins' ), true ) ) ) {
			require_once plugin_dir_path( __DIR__ ) . 'contact-form-pro/bws_menu/bws_include.php';
			bws_include_init( plugin_basename( plugin_dir_path( __DIR__ ) . 'contact-form-pro/contact_form_pro.php' ) );
		} else {
			require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
			bws_include_init( plugin_basename( __FILE__ ) );
		}

		if ( empty( $cntctfrmmlt_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$cntctfrmmlt_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $cntctfrmmlt_plugin_info, '4.5' );
	}
}

if ( ! function_exists( 'cntctfrmmlt_admin_init' ) ) {
	/**
	 * Function for connecting hooks-(init, admin_init)
	 */
	function cntctfrmmlt_admin_init() {
		global $bws_plugin_info, $cntctfrmmlt_plugin_info, $pagenow, $cntctfrmmlt_options;

		/* Add variable for bws_menu */
		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id'      => '123',
				'version' => $cntctfrmmlt_plugin_info['Version'],
			);
		}

		/* check for installed and activated Contact Form*/
		cntctfrmmlt_check();

		if ( isset( $_REQUEST['page'] ) && ( 'contact_form.php' === $_REQUEST['page'] || 'contact_form_pro.php' === $_REQUEST['page'] || 'contact-form-plus.php' === $_REQUEST['page'] || 'contact_form_pro_extra.php' === $_REQUEST['page'] ) ) {
			/*register defaults settings function*/
			cntctfrmmlt_settings_defaults();
			/*register main options function*/
			cntctfrmmlt_main_options();
		}
		if ( 'plugins.php' == $pagenow ) {
			/* Install the option defaults */
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				cntctfrmmlt_settings_defaults();
				bws_plugin_banner_go_pro( $cntctfrmmlt_options, $cntctfrmmlt_plugin_info, 'cntctfrmmlt', 'contact-form-multi', '93536843024dbb3360bfa9d6d6a1d297', '123', 'contact-form-multi' );
			}
		}
	}
}

if ( ! function_exists( 'cntctfrmmlt_plugins_loaded' ) ) {
	/**
	 * Add language files
	 */
	function cntctfrmmlt_plugins_loaded() {
		load_plugin_textdomain( 'contact-form-multi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists( 'cntctfrmmlt_settings_defaults' ) ) {
	/**
	 * Install the option defaults
	 */
	function cntctfrmmlt_settings_defaults() {
		global $cntctfrmmlt_options, $cntctfrmmlt_plugin_info, $cntctfrmmlt_options_main;

		$cntctfrmmlt_options_main = array(
			'plugin_option_version' => $cntctfrmmlt_plugin_info['Version'],
			'name_id_form'          => array( 1 => __( 'NEW_FORM', 'contact-form-multi' ) ),
			'next_id_form'          => 2,
			'id_form'               => 1,
			'first_install'         => strtotime( 'now' ),
		);
		/*add options to database*/
		if ( ! get_option( 'cntctfrmmlt_options_main' ) ) {
			add_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
		}

		/* Get options from the database */
		$cntctfrmmlt_options = get_option( 'cntctfrmmlt_options_main' );

		if ( ! isset( $cntctfrmmlt_options['plugin_option_version'] ) || $cntctfrmmlt_options['plugin_option_version'] != $cntctfrmmlt_plugin_info['Version'] ) {
			$cntctfrmmlt_options                          = array_merge( $cntctfrmmlt_options_main, $cntctfrmmlt_options );
			$cntctfrmmlt_options['plugin_option_version'] = $cntctfrmmlt_plugin_info['Version'];
			update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options );
			cntctfrmmlt_plugin_activate();
		}
	}
}

if ( ! function_exists( 'cntctfrmmlt_plugin_activate' ) ) {
	/**
	 * Activation plugin function
	 */
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

if ( ! function_exists( 'cntctfrmmlt_main_options' ) ) {
	/**
	 * Ads feature the main options
	 */
	function cntctfrmmlt_main_options() {
		global $cntctfrmmlt_counts, $key, $cntctfrmmlt_keys, $cntctfrmmlt_last_key, $cntctfrmmlt_options_main, $value, $cntctfrmmlt_id_form;
		$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
		if ( ! isset( $_GET['id'] ) ) {
			$cntctfrmmlt_id_form = $cntctfrmmlt_options_main['id_form'];
		}
		/*Update cntctfrmmlt_id_options in a database*/
		if ( isset( $_GET['id'] ) ) {
			$cntctfrmmlt_id_form = absint( $_GET['id'] );
		}
		$cntctfrmmlt_options_main['id_form'] = $cntctfrmmlt_id_form;
		update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );

		/*Deleting data from the database after pressing the delete*/
		if ( isset( $_GET['del'] ) ) {

			/*Remove the contact form from the database*/
			$cntctfrmmlt_args = 'cntctfrmmlt_options_' . absint( $_GET['id'] );
			delete_option( $cntctfrmmlt_args );
			/*Remove the contact form from the database*/

			/*remove values from a name_id_form*/
			$cntctfrmmlt_counts = $cntctfrmmlt_options_main['name_id_form'];
			unset( $cntctfrmmlt_counts[ $cntctfrmmlt_options_main['id_form'] ] );
			$cntctfrmmlt_options_main['name_id_form'] = $cntctfrmmlt_counts;
			/*remove values from a name_id_form*/

			$cntctfrmmlt_keys                    = array_keys( $cntctfrmmlt_options_main['name_id_form'] );
			$cntctfrmmlt_last_key                = end( $cntctfrmmlt_keys );
			$cntctfrmmlt_options_main['id_form'] = $cntctfrmmlt_last_key;
			update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
			$cntctfrmmlt_id_form = $cntctfrmmlt_last_key;
			if ( empty( $cntctfrmmlt_options_main['name_id_form'] ) ) {
				$cntctfrmmlt_options_main['id_form']      = 1;
				$cntctfrmmlt_options_main['name_id_form'] = array( 1 => __( 'NEW_FORM', 'contact-form-multi' ) );
				$cntctfrmmlt_options_main['next_id_form'] = 2;
				update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
				$cntctfrmmlt_id_form = 1;
			}
		}
	}
}

if ( ! function_exists( 'cntctfrmmlt_plugin_links' ) ) {
	/**
	 * Function creates other links on admin page
	 *
	 * @param array  $links Links array.
	 * @param string $file  File name.
	 */
	function cntctfrmmlt_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="https://wordpress.org/plugins/contact-form-multi/faq/" target="_blank">' . __( 'FAQ', 'contact-form-multi' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'contact-form-multi' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'cntctfrmmlt_action_callback' ) ) {
	/**
	 * Update array cntctfrmmlt_options_main in a database
	 */
	function cntctfrmmlt_action_callback() {
		global $cntctfrmmlt_counts, $cntctfrmmlt_j, $cntctfrmmlt_id_form, $cntctfrmmlt_value, $cntctfrmmlt_id_key, $cntctfrmmlt_options_main;
		check_ajax_referer( plugin_basename( __FILE__ ), 'cntctfrmmlt_ajax_nonce_field' );
		$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
		/* Update next_id_form, cntctfrmmlt_id_options */
		if ( isset( $_POST['cntctfrmmlt_key_form'] ) ) {
			$cntctfrmmlt_id_key                       = absint( $_POST['cntctfrmmlt_key_form'] );
			$cntctfrmmlt_id_key                      += 1;
			$cntctfrmmlt_options_main['next_id_form'] = $cntctfrmmlt_id_key;
			$cntctfrmmlt_options_main['id_form']      = absint( $_POST['cntctfrmmlt_key_form'] );
			$cntctfrmmlt_id_form                      = absint( $_POST['cntctfrmmlt_key_form'] );
		}
		/* Update name and ID, options */
		if ( isset( $_POST['cntctfrmmlt_name_form'] ) ) {
			foreach ( $_POST['cntctfrmmlt_name_form'] as $cntctfrmmlt_j ) {
				list( $key, $cntctfrmmlt_value )          = explode( ':', $cntctfrmmlt_j );
				$cntctfrmmlt_counts[ $key ]               = strip_tags( sanitize_textarea_field( wp_unslash( $cntctfrmmlt_value ) ) );
				$cntctfrmmlt_options_main['name_id_form'] = $cntctfrmmlt_counts;
			}
		}
		update_option( 'cntctfrmmlt_options_main', $cntctfrmmlt_options_main );
		exit;
	}
}

if ( ! function_exists( 'cntctfrmmlt_scripts' ) ) {
	/**
	 * Function to add stylesheets and scripts for admin bar
	 */
	function cntctfrmmlt_scripts() {
		global $cntctfrmmlt_plugin_info;
		if ( isset( $_REQUEST['page'] ) && ( 'contact_form.php' === $_REQUEST['page'] || 'contact_form_pro.php' === $_REQUEST['page'] || 'contact-form-plus.php' === $_REQUEST['page'] || 'contact_form_pro_extra.php' === $_REQUEST['page'] ) ) {

			wp_enqueue_style( 'cntctfrmml_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $cntctfrmmlt_plugin_info['Version'] );
			wp_enqueue_script( 'cntctfrmmlt_script', plugins_url( 'js/script.js', __FILE__ ), array(), $cntctfrmmlt_plugin_info['Version'] );

			/* script vars */
			$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
			$site_url_if_multisite    = is_multisite() ? site_url() : '';

			$cntctfrmmlt_count = array();

			if ( $cntctfrmmlt_options_main ) {
				foreach ( $cntctfrmmlt_options_main['name_id_form'] as $key => $value ) {
					$cntctfrmmlt_count[ $key ] = $value;
				}
			}
			if ( is_plugin_active( 'contact-form-plugin/contact_form.php' ) ) {
				$plugin_active = '?page=contact_form.php';
			} elseif ( is_plugin_active( 'contact-form-plus/contact-form-plus.php' ) ) {
				$plugin_active = '?page=contact-form-plus.php';
			} elseif ( is_plugin_active( 'contact-form-pro/contact_form_pro.php' ) ) {
				$plugin_active = '?page=contact_form_pro.php';
			}

			$script_vars = array(
				'cntctfrmmlt_nonce'          => wp_create_nonce( plugin_basename( __FILE__ ), 'cntctfrmmlt_ajax_nonce_field' ),
				'cntctfrmmlt_delete_message' => __( 'Are you sure you want to delete the form?', 'contact-form-multi' ),
				'cntctfrmmlt_id_form'        => $cntctfrmmlt_options_main['id_form'],
				'cntctfrmmlt_location'       => $site_url_if_multisite . $_SERVER['PHP_SELF'] . $plugin_active,
				'cntctfrmmlt_action_slug'    => ( isset( $_GET['action'] ) ? '&action=' . sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '' ),
				'cntctfrmmlt_key_id'         => $cntctfrmmlt_options_main['next_id_form'],
				'cntctfrmmlt_count'          => $cntctfrmmlt_count,
			);
			wp_localize_script( 'cntctfrmmlt_script', 'cntctfrmmlt_script_vars', $script_vars );
		}
	}
}

if ( ! function_exists( 'cntctfrmmlt_check' ) ) {
	/**
	 * Сhecking for the existence of Contact Form Plugin or Contact Form Pro Plugin
	 */
	function cntctfrmmlt_check() {
		global $cntctfrmmlt_contact_form_not_found, $cntctfrmmlt_contact_form_not_active;
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( ! ( array_key_exists( 'contact-form-plugin/contact_form.php', $all_plugins ) || array_key_exists( 'contact-form-pro/contact_form_pro.php', $all_plugins ) || array_key_exists( 'contact-form-plus/contact-form-plus.php', $all_plugins ) ) ) {
			$cntctfrmmlt_contact_form_not_found = __( 'Contact Form Plugin has not been found.', 'contact-form-multi' ) . '</br>' . __( 'You should install and activate this plugin for the correct work with Contact Form Multi plugin.', 'contact-form-multi' ) . '</br>' . __( 'You can download Contact Form Plugin from', 'contact-form-multi' ) . ' <a href="' . esc_url( 'https://bestwebsoft.com/products/wordpress/plugins/contact-form/' ) . '" title="' . __( 'Developers website', 'contact-form-multi' ) . '"target="_blank">' . __( 'website of plugin Authors', 'contact-form-multi' ) . '</a> ' . __( 'or', 'contact-form-multi' ) . ' <a href="' . esc_url( 'https://wordpress.org' ) . '" title="Wordpress" target="_blank">' . __( 'WordPress.', 'contact-form-multi' ) . '</a>';
		} else {
			if ( ! ( is_plugin_active( 'contact-form-plugin/contact_form.php' ) || is_plugin_active( 'contact-form-pro/contact_form_pro.php' ) || is_plugin_active( 'contact-form-plus/contact-form-plus.php' ) ) ) {
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

if ( ! function_exists( 'cntctfrmmlt_show_notices' ) ) {
	/**
	 * Add notises on plugins page if Contact Form plugin is not installed or not active
	 */
	function cntctfrmmlt_show_notices() {
		global $hook_suffix, $cntctfrmmlt_contact_form_not_found, $cntctfrmmlt_contact_form_not_active, $cntctfrm_plugin_info;
		if ( 'plugins.php' === $hook_suffix || ( isset( $_REQUEST['page'] ) && 'bws_panel' === $_REQUEST['page'] ) || ( isset( $_REQUEST['page'] ) && ( 'contact_form.php' === $_REQUEST['page'] || 'contact_form_pro.php' === $_REQUEST['page'] || 'contact-form-plus.php' === $_REQUEST['page'] ) ) ) {
			if ( '' != $cntctfrmmlt_contact_form_not_found || '' != $cntctfrmmlt_contact_form_not_active ) { ?>
				<div class="error">
					<p><strong><?php esc_html_e( 'WARNING:', 'contact-form-multi' ); ?></strong> <?php echo wp_kses_post( $cntctfrmmlt_contact_form_not_found . $cntctfrmmlt_contact_form_not_active ); ?></p>
				</div>
			<?php } ?>
			<noscript>
				<div class="error">
					<p><?php esc_html_e( 'Please enable JavaScript in your browser!', 'contact-form-multi' ); ?></p>
				</div>
			</noscript>
			<?php if ( ( is_plugin_active( 'contact-form-plugin/contact_form.php' ) || is_plugin_active( 'contact-form-pro/contact_form_pro.php' ) || is_plugin_active( 'contact-form-plus/contact-form-plus.php' ) ) && version_compare( $cntctfrm_plugin_info['Version'], '4.1.2', '<' ) ) { ?>
				<div class="error">
					<p><strong><?php esc_html_e( "Contact Form Multi plugin doesn't support your current version of Contact Form plugin. Please update Contact Form plugin to version 4.1.2 or higher.", 'contact-form-multi' ); ?></strong></p>
				</div>
				<?php
			}
		}
	}
}

if ( ! function_exists( 'cntctfrmmlt_delete' ) ) {
	/**
	 * Function for delete options
	 */
	function cntctfrmmlt_delete() {
		global $wpdb;
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
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
				$cntctfrmmlt_blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				$cntctfrmmlt_original_blog_id = get_current_blog_id();
				foreach ( $cntctfrmmlt_blog_ids as $cntctfrmmlt_blog_id ) {
					switch_to_blog( $cntctfrmmlt_blog_id );
					$cntctfrmmlt_options_main = get_option( 'cntctfrmmlt_options_main' );
					foreach ( $cntctfrmmlt_options_main['name_id_form'] as $key => $value ) {
						delete_option( 'cntctfrmmlt_options_' . $key );
					}
					delete_option( 'cntctfrmmlt_options_main' );
					delete_option( 'cntctfrmmlt_options' );
				}
				switch_to_blog( $cntctfrmmlt_original_blog_id );
			}
		}

		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'cntctfrmmlt_plugin_activate' );
/* Hook for add menu */
add_action( 'admin_menu', 'cntctfrmmlt_admin_menu' );
/* Hook calls functions for init and admin_init hooks */
add_action( 'init', 'cntctfrmmlt_init' );
add_action( 'admin_init', 'cntctfrmmlt_admin_init', 9 );
add_action( 'plugins_loaded', 'cntctfrmmlt_plugins_loaded' );
/* hook for adding scripts and styles */
add_action( 'admin_enqueue_scripts', 'cntctfrmmlt_scripts' );
/* Additional links on the plugin page*/
add_filter( 'plugin_row_meta', 'cntctfrmmlt_plugin_links', 10, 2 );
/* Check for installed and activated Contact Form plugin */
add_action( 'admin_notices', 'cntctfrmmlt_show_notices' );
/* Hooks for ajax */
add_action( 'wp_ajax_cntctfrmmlt_action', 'cntctfrmmlt_action_callback' );
