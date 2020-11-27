<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !is_admin() ) {
	function nettix( $atts ) {
		ob_start();

		extract(shortcode_atts(array(
			'language'  => '',
			'cpt'  => '',
			'id'  => '',
		), $atts));

		$laskuri_activated = apply_filters('wb_nettix_laskuri_activation_code', '');

		echo '<div id="nettix"></div>';
		echo '<div id="nettix_lang">' . $language . '</div>';
		echo '<div id="nettix_cpt">' . $cpt . '</div>';
		echo '<div id="nettix_singleid">' . $id . '</div>';
		echo '<div id="nettix_laskuri">' . $laskuri_activated . '</div>';

		$clean_variable = ob_get_clean();
		return $clean_variable;
	}
	
	add_shortcode('nettix', 'nettix');
}