<?php
/**
 * Plugin Name: Request Resources
 * Plugin URI:  
 * Description: request resources pluggin
 * Version: 1.0.4
 * Author: Mike Socorec
 * Author URI: http://thedevfriend
 * Text Domain: request-resources
 * Domain Path: languages
 *
 * License: GPLv2 or later
 * Domain Path: languages
 *
 * @package request resources
 * @category Core
 * @author MIKE SOCOREC
 */

// Cree una función ayudante para un fácil acceso SDK.
function ens_fs() {
  
}

// Init Freemius.
ens_fs();
// Señal de que se inició SDK.
do_action( 'ens_fs_loaded' );

// Salir si se accede directamente
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Definiciones básicas de complementos
 * 
 * @package request resources
 * @since 1.0.4
 */
if( !defined( 'WPENS_VERSION' ) ) {
	define( 'WPENS_VERSION', '1.0.4' ); // plugin version
}
if( !defined( 'WPENS_PLUGIN_DIR' ) ) {
	define( 'WPENS_PLUGIN_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'WPENS_ADMIN_DIR' ) ) {
	define( 'WPENS_ADMIN_DIR', WPENS_PLUGIN_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'WPENS_PLUGIN_URL' ) ) {
	define( 'WPENS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}

/**
 * Load Text Domain
 * 
 * Locales found in:
 *   - WP_LANG_DIR/easy-online-booking/wpeob-LOCALE.mo
 *   - WP_LANG_DIR/plugins/wpeob-LOCALE.mo
 * 
 * @package request resources
 * @since 1.0.0
 */
function wpens_load_plugin_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wpens' );

	load_textdomain( 'wpens', WP_LANG_DIR . '/request-resources/wpens-' . $locale . '.mo' );
	load_plugin_textdomain( 'wpens', false, WPENS_PLUGIN_DIR . '/languages' );
}
add_action( 'load_plugins', 'wpens_load_plugin_textdomain' );

/**
 * Activation hook
 * 
 * Register plugin activation hook.
 * 
 * @package Guide requests
 * @since 1.0.0
 */

register_activation_hook( __FILE__, 'wpens_plugin_install' );

/**
 * Deactivation hook
 *
 * Register plugin deactivation hook.
 * 
 * @package Guide requests
 * @since 1.0.0
 */

register_deactivation_hook( __FILE__, 'wpens_plugin_uninstall' );

/**
 * Configuración de la configuración del complemento El gancho de activación del gancho de la llamada
 *
 * Configuración inicial de las opciones predeterminadas de configuración del complemento
 * y creaciones de tablas de bases de datos.
 * 
 * @package Guide requests
 * @since 1.0.0
 */
function wpens_plugin_install() {
	
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'ens_subscribers';

	$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		full_name varchar(255) NULL, 
		email varchar(255) NOT NULL,
		company_website text NULL,
		company_name text NULL,
		guide_type text NULL,
		in_newsletter text NULL,
		nucleo_empleados text NULL,
		user_ip varchar(100) NULL,
		date timestamp NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

/**
 * Configuración de complemento (On Deactivation)
 *
 * ¿Las tablas de caída en la base de datos y
 * Eliminar opciones de complemento.
 *
 * @package Guide requests
 * @since 1.0.0
 */
function wpens_plugin_uninstall() {
	
	global $wpdb;

	/*$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'ens_subscribers';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );*/
}

/**
* Cambiar el texto del pie de página para las revisiones
 */
add_filter( 'admin_footer_text', 'wpens_remove_footer_admin' );
function wpens_remove_footer_admin() {
	$screen =  get_current_screen();
	if( $screen->id == "toplevel_page_wpens-list" ){
		echo '<span id="footer-thankyou">';
		echo sprintf( __('If you like %1sGuide requests%2s please leave us a %3s★★★★★%4s rating. A huge thanks in advance!', 'wpens'),
			'<strong>', '</strong>',
			'<a href="https://wordpress.org/support/plugin/easy-newsletter-signups/reviews/?rate=5#new-post" target="_blank" class="ens-rating-link">',
			'</a>'
		 );
		echo '</span>';
	}
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wpens_add_action_links' );

function wpens_add_action_links( $links ) {
	  
}

/**
 * Initialize all global variables
 * 
 * @package Guide requests
 * @since 1.0.4
 */

global $wpens_scripts,$wpens_admin,$wpens_newsletter;


//Includes all scripts class file
require_once( WPENS_PLUGIN_DIR . '/includes/class-wpens-scripts.php');

//Includes shortcode class file
require_once ( WPENS_PLUGIN_DIR . '/includes/class-wpens-shortcodes.php');

//Includes public class file
require_once ( WPENS_PLUGIN_DIR . '/includes/class-wpens-public.php');

//Includes Admin file
require_once ( WPENS_ADMIN_DIR . '/class-wpens-admin.php');