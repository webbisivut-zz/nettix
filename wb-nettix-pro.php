<?php
/*
 * Plugin Name: WordPress Nettix Pro
 * Version: 1.4.1
 * Plugin URI: https://webbisivut.org/
 * Description: WordPress Nettix Pro lisäosa
 * Author: Webbisivut.org
 * Author URI: https://webbisivut.org/
 * Requires at least: 4.0
 * Tested up to: 5.7
 *
 * Text Domain: wb-nettix-pro
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Webbisivut.org
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-wb-nettix-pro.php' );
require_once( 'includes/class-wb-nettix-pro-settings.php' );
require_once( 'includes/class-wb-nettix-pro-functions.php' );
require_once( 'includes/class-wb-nettix-admin-ajax.php' );
require_once( 'includes/class-wb-nettix-pro-shortcode.php' );

// Load dynamic CSS
require_once( 'includes/class-wb-dynamic-css.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wb-nettix-pro-admin-api.php' );
require_once( 'includes/lib/class-wb-nettix-pro-post-type.php' );
require_once( 'includes/lib/class-wb-nettix-pro-taxonomy.php' );

$nettix_cpt = esc_attr(get_option('nettix_cpt'));

if($nettix_cpt == 'kylla') {
	WB_Nettix_Pro()->register_post_type( 'nettix_ajoneuvot', __( 'Nettix ajoneuvot', 'wb-nettix-pro' ), __( 'Nettix ajoneuvot', 'wb-nettix-pro' ) );

	// Voidaan luoda taxonomyt tarvittaessa, lisäämällä rivit teeman functions.php tiedostoon:
	//WB_Nettix_Pro()->register_taxonomy( 'autot', __( 'Autot', 'wb-nettix-pro' ), __( 'Autot', 'wb-nettix-pro' ), 'nettix_ajoneuvot' );
	//WB_Nettix_Pro()->register_taxonomy( 'motot', __( 'Motot', 'wb-nettix-pro' ), __( 'Motot', 'wb-nettix-pro' ), 'nettix_ajoneuvot' );
}

require 'plugin-update-checker/plugin-update-checker.php';
$WbNettixUpdateChecker = PucFactory::buildUpdateChecker( 'http://webbisivut.org/updates/?action=get_metadata&slug=wb-nettix', __FILE__, 'wb-nettix' );

/**
 * Returns the main instance of WB_Nettix_Pro to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WB_Nettix_Pro
 */
function WB_Nettix_Pro () {
	$instance = WB_Nettix_Pro::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WB_Nettix_Pro_Settings::instance( $instance );
	}

	return $instance;
}

WB_Nettix_Pro();

define( 'nettix_plugin_path', plugin_dir_path( __FILE__ ) );
define( 'nettix_temp', nettix_plugin_path . 'temp' );
define( 'nettix_error', nettix_plugin_path . 'logs' );