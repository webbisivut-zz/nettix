<?php
/**
 * The Template for displaying all single posts for Nettix vehicles. Copy this to your theme's directory and do your adjustments.
 *
 * @package WordPress
 */
get_header(); ?>

<?php
	$page_id = get_the_ID();
	$id = get_post_meta($page_id, 'nettix_ajoneuvo_id', true);

	if ( shortcode_exists( 'nettix' ) ) {
		echo do_shortcode('[nettix cpt="true" id="' . $id . '"]');
	}
?>

<?php get_footer(); ?>