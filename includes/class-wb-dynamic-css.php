<?php
function wb_nettix_styles_dynamic() {
	$tausta = esc_attr(get_option('nettix_tausta'));
	$paavari1 = esc_attr(get_option('nettix_paavari1'));
	$paavari2 = esc_attr(get_option('nettix_paavari2'));
	$painikkeet1 = esc_attr(get_option('nettix_painikkeet1'));
	$painikkeet2 = esc_attr(get_option('nettix_painikkeet2'));
	$painikkeet3 = esc_attr(get_option('nettix_painikkeet3'));
	$kohteen_tausta = esc_attr(get_option('nettix_kohteen_tausta'));
	$kohteen_varjostus = esc_attr(get_option('nettix_kohteen_varjostus'));

	// Oletukset
	if($tausta == '' || $tausta == null) {
		$tausta = '#ffffff';
	}

	if($paavari1 == '' || $paavari1 == null) {
		$paavari1 = '#ba0000';
	}

	if($paavari2 == '' || $paavari2 == null) {
		$paavari2 = '#eeeeee';
	}

	if($painikkeet1 == '' || $painikkeet1 == null) {
		$painikkeet1 = '#218900';
	}

	if($painikkeet2 == '' || $painikkeet2 == null) {
		$painikkeet2 = '#ff5a00';
	}

	if($painikkeet3 == '' || $painikkeet3 == null) {
		$painikkeet3 = '#ffffff';
	}

	if($kohteen_tausta == '' || $kohteen_tausta == null) {
		$kohteen_tausta = '#f2f2f2';
	}

	?>

	<style>
		.nettix_takaisin_hakutuloksiin {
			color: <?php echo $paavari1; ?>;
		}

		.nettix_hakutulokset_wrap {
			background: <?php echo $tausta; ?>;
		}

		.nettix_lisatiedot_btn,
		.nettix_selected,
		.nettix_pagination:hover {
			background: <?php echo $paavari1; ?>;
			color: <?php echo $painikkeet3; ?>;
		}

		.nettix_hakukone,
		.nettix_grey_bg,
		.nettix_sidebar {
			background: <?php echo $paavari2; ?>;
		}

		#nettix_haku_btn,
		#nettix_tyhjenna_btn,
		.nettix_phone,
		.nettix_email {
			background: <?php echo $painikkeet1; ?>;
			color: <?php echo $painikkeet3; ?>;
		}

		#nettix_tyhjenna_btn,
		.nettix_email {
			background: <?php echo $painikkeet2; ?>;
			color: <?php echo $painikkeet3; ?>;
		}

		.nettix_vehicle {
			background: <?php echo $kohteen_tausta; ?>;
		}

		.nettix_thumb {
			border: 5px solid <?php echo $tausta; ?>;
		}

		.nettix_vehicles_thumb {
			cursor: pointer;
			border: 5px solid <?php echo $tausta; ?>;
		}

		.main_img_wrap {
			background: <?php echo $paavari2; ?>;
		}

		.wb_counter_input_range {
			background: #888;
		}

		.wb_counter_input_range::-webkit-slider-thumb {
			background: <?php echo $paavari1; ?>;
		}
		
		.wb_counter_input_range::-moz-range-thumb {
			background: <?php echo $paavari1; ?>;
		}

		/* Range*/
		.wb_counter_input_range {
			background: <?php echo $tausta; ?>;
		}

		.wb_counter_input_range::-webkit-slider-thumb {
			background: <?php echo $paavari1; ?>; 
		}
		
		.wb_counter_input_range::-moz-range-thumb {
			background: <?php echo $paavari1; ?>; 
		}
		
		#wb_counted_wrap {
			background: <?php echo $paavari2; ?>;
		}

		select.wb_laina_aika,
		input.wb_counter_input {
			background: <?php echo $tausta; ?>;
		}

		input.wb_counter_button {
			color: <?php echo $tausta; ?>;
			background: <?php echo $painikkeet1; ?>;
		}

		<?php if($kohteen_varjostus == 'kylla') { ?>
			.nettix_vehicle {
				box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
			}
		<?php } ?>

	</style>

<?php }

add_action( 'wp_head', 'wb_nettix_styles_dynamic', 100 );
?>
