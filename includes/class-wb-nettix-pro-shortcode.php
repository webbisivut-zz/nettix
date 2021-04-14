<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Nettix_Shortcodes_Class {
	
	/**
	 * The constructor
	 * @since 	1.0.0
	 */
	public function __construct() {
		add_shortcode('nettix', array($this, 'nettix_function'));	
	}

	/**
	 * Main Nettix Shortcode
	 * @since 	1.0.0
	 */
	public function nettix_function( $atts ) {
		ob_start();

		extract(shortcode_atts(array(
			'language'  => '',
			'cpt'  => '',
			'id'  => '',
			'latest' => ''
		), $atts));

		$laskuri_activated = apply_filters('wb_nettix_laskuri_activation_code', true);

		echo '<div id="nettix"></div>';
		echo '<div id="nettix_lang">' . $language . '</div>';
		echo '<div id="nettix_cpt">' . $cpt . '</div>';
		echo '<div id="nettix_singleid">' . $id . '</div>';
		echo '<div id="nettix_laskuri">' . $laskuri_activated . '</div>';
		echo '<div id="nettix_latest">' . $latest . '</div>';

		$clean_variable = ob_get_clean();
		return $clean_variable;
	}
}

new Nettix_Shortcodes_Class();