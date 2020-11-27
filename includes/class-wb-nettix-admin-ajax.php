<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function wb_nettix_sendMail() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$nettix_contact_nimi = $dataArr->{'nettix_contact_nimi'};
	$nettix_contact_email = $dataArr->{'nettix_contact_email'};
	$nettix_contact_puhelin = $dataArr->{'nettix_contact_puhelin'};
	$nettix_contact_url = $dataArr->{'nettix_contact_url'};
	$nettix_contact_viesti = str_replace('\n', '<br>', 'Lähettäjä: ' . $nettix_contact_nimi . '<br>' . 'Sähköposti: ' . $nettix_contact_email . '<br>' . 'Puhelin: ' . $nettix_contact_puhelin . '<br>' . 'Ajoneuvon url: ' . $nettix_contact_url . '<br>' . 'Viesti: ' . $dataArr->{'nettix_contact_viesti'});

	$to = esc_attr(get_option('nettix_viesti_email'));
	$subject = 'Tiedustelu ajoneuvosta';

	$headers[] = 'From:' . $nettix_contact_nimi . ' <' . $nettix_contact_email . '>';
	$headers[] = 'Content-Type: text/html; charset=UTF-8';

	wp_mail( $to, $subject, $nettix_contact_viesti, $headers );

	echo 'Viesti lähetetty!';
	wp_die();
}

add_action( 'wp_ajax_nopriv_sendMail', 'wb_nettix_sendMail' );
add_action( 'wp_ajax_sendMail', 'wb_nettix_sendMail' );

// Sähköpostit html muotoon
add_filter( 'wp_mail_content_type', 'wb_nettix_set_html_mail_content_type' );

function wb_nettix_set_html_mail_content_type() {
	return 'text/html';
}

function wb_nettix_loadVehicleSingle() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$id = $dataArr->{'id'};

	global $wpdb;
	$post_id = $wpdb->get_var( "select post_id from $wpdb->postmeta where meta_value = $id");

	if($post_id == null OR $post_id == '') {
		nettixCptVehicles();

		$post_id = $wpdb->get_var( "select post_id from $wpdb->postmeta where meta_value = $id");

		echo get_permalink( $post_id );
	} else {
		echo get_permalink( $post_id );
	}

	wp_die();
}

add_action( 'wp_ajax_nopriv_loadVehicleSingle', 'wb_nettix_loadVehicleSingle' );
add_action( 'wp_ajax_loadVehicleSingle', 'wb_nettix_loadVehicleSingle' );

function wb_nettix_getVehicles() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$ajoneuvotyyppi = $dataArr->{'ajoneuvotyyppi'};

	$vehicles = '';

	if($ajoneuvotyyppi == 'Autot') {
		if(file_exists(nettix_temp . '/nettiauto/vehicles_full_list.txt')) {
			$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
			$vehicles = json_encode($vehicles);
		}
	} else if($ajoneuvotyyppi == 'Motot') {
		if(file_exists(nettix_temp . '/nettimoto/vehicles_full_list.txt')) {
			$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
			$vehicles = json_encode($vehicles);
		}
	}

	if($vehicles == '') {
		nettixGetToken();

		if($ajoneuvotyyppi == 'Autot') {
			$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
			$vehicles = json_encode($vehicles);
		} else if($ajoneuvotyyppi == 'Motot') {
			$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
			$vehicles = json_encode($vehicles);
		}
	}

	echo $vehicles;
	wp_die();
}

add_action( 'wp_ajax_nopriv_getVehicles', 'wb_nettix_getVehicles' );
add_action( 'wp_ajax_getVehicles', 'wb_nettix_getVehicles' );

function wb_nettix_getModels() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$ajoneuvotyyppi = $dataArr->{'ajoneuvotyyppi'};
	$ajoneuvolaji = $dataArr->{'ajoneuvolaji'};
	$id = $dataArr->{'id'};

	$modelsArr = [];
	$modelsArr2 = [];

	if($ajoneuvotyyppi == 'Autot') {
		$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
		$vehicles = json_decode($vehicles);

		foreach($vehicles as $vehicle) {
			if($vehicle->make->id == $id && $vehicle->vehicleType->id == $ajoneuvolaji OR $vehicle->make->id == $id && $ajoneuvolaji == '') {
				array_push($modelsArr,
					array(
						"name" => (string) $vehicle->model->name,
						"id" => (string) $vehicle->model->id
					)
				);
			}
		}
	} else if($ajoneuvotyyppi == 'Motot') {
		$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
		$vehicles = json_decode($vehicles);

		foreach($vehicles as $vehicle) {
			if($vehicle->make->id == $id && $vehicle->bikeType->id == $ajoneuvolaji OR $vehicle->make->id == $id && $ajoneuvolaji == '') {
				array_push($modelsArr,
					array(
						"name" => (string) $vehicle->model->name,
						"id" => (string) $vehicle->model->id
					)
				);
			}
		}
	}

	foreach($modelsArr as $value) {
		$modelsArr2[serialize($value)] = $value;
	}

	$array = array_values($modelsArr2);

	echo json_encode(unserialize(serialize($array)));
	wp_die();
}

add_action( 'wp_ajax_nopriv_getModels', 'wb_nettix_getModels' );
add_action( 'wp_ajax_getModels', 'wb_nettix_getModels' );

function wb_nettix_getAjoneuvolajit() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$ajoneuvotyyppi = $dataArr->{'ajoneuvotyyppi'};

	$modelsArr = [];
	$modelsArr2 = [];

	if($ajoneuvotyyppi == 'Autot') {
		$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
		$vehicles = json_decode($vehicles);

		foreach($vehicles as $vehicle) {
			array_push($modelsArr,
				array(
					"name" => (string) $vehicle->vehicleType->fi,
					"name_en" => (string) $vehicle->vehicleType->en,
					"id" => (string) $vehicle->vehicleType->id
				)
			);
		}
	} else if($ajoneuvotyyppi == 'Motot') {
		$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
		$vehicles = json_decode($vehicles);

		foreach($vehicles as $vehicle) {
				array_push($modelsArr,
					array(
						"name" => (string) $vehicle->bikeType->fi,
						"name_en" => (string) $vehicle->bikeType->en,
						"id" => (string) $vehicle->bikeType->id
					)
				);
		}
	}

	foreach($modelsArr as $value) {
		$modelsArr2[serialize($value)] = $value;
	}

	$array = array_values($modelsArr2);

	echo json_encode(unserialize(serialize($array)));
	wp_die();
}

add_action( 'wp_ajax_nopriv_getAjoneuvolajit', 'wb_nettix_getAjoneuvolajit' );
add_action( 'wp_ajax_getAjoneuvolajit', 'wb_nettix_getAjoneuvolajit' );

function wb_nettix_getVehicleDetails() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$api = esc_attr(get_option('nettix_palvelin'));

	if($api == 'tuotanto') {
		$url_start = 'https://api.nettix.fi/';
	} else {
		$url_start = 'https://api-test.nettix.fi/';
	}

	$ajoneuvotyyppi = $dataArr->{'ajoneuvotyyppi'};
	$id = $dataArr->{'id'};

	if($ajoneuvotyyppi == 'Autot') {
		$uri = $url_start . 'rest/car/ad/' . $id;
	} else if($ajoneuvotyyppi == 'Motot') {
		$uri = $url_start . 'rest/bike/ad/' . $id;
	}

	$token = file_get_contents(nettix_temp . '/token.txt');

	if(!$token) {
		$token = newToken();
	}

	$ch = curl_init();

	$header = array();
	$header[] = 'accept: application/json';
	$header[] = 'X-Access-Token: ' . $token;

	curl_setopt($ch, CURLOPT_URL, $uri);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$output = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($output);

	echo json_encode($output);
	wp_die();
}

add_action( 'wp_ajax_nopriv_getVehicleDetails', 'wb_nettix_getVehicleDetails' );
add_action( 'wp_ajax_getVehicleDetails', 'wb_nettix_getVehicleDetails' );

function wb_nettix_getSettings() {
	// Yleiset
	$rajapinnat = get_option('nettix_rajapinnat');
	$yrityksen_nimi = esc_attr(get_option('nettix_yrityksen_nimi'));
	$yrityksen_osoite = esc_attr(get_option('nettix_yrityksen_osoite'));
	$yrityksen_postinumero = esc_attr(get_option('nettix_yrityksen_postinumero'));
	$yrityksen_paikkakunta = esc_attr(get_option('nettix_yrityksen_paikkakunta'));
	$yrityksen_paikkakunta2 = esc_attr(get_option('nettix_yrityksen_paikkakunta2'));
	$yrityksen_puhelin = esc_attr(get_option('nettix_yrityksen_puhelin'));
	$yrityksen_email = esc_attr(get_option('nettix_yrityksen_email'));
	$ga = esc_attr(get_option('nettix_ga'));
	$jarjestys = esc_attr(get_option('nettix_jarjestys'));
	$teema = esc_attr(get_option('nettix_teema'));

	// Vaihtoehtoinen paikkakunta
	$paikkakunta2 = esc_attr(get_option('nettix_paikkakunta2'));
	$maakunta2 = esc_attr(get_option('nettix_maakunta2'));
	$yrityksen_osoite2 = esc_attr(get_option('nettix_yrityksen_osoite2'));
	$yrityksen_postinumero2 = esc_attr(get_option('nettix_yrityksen_postinumero2'));
	$yrityksen_paikkakunta2 = esc_attr(get_option('nettix_yrityksen_paikkakunta2'));

	// Kohdesivu
	$kuvan_koko = esc_attr(get_option('nettix_kuvan_koko'));

	// Hakukone
	$pikahaku = esc_attr(get_option('nettix_pikahaku'));
	$rows = esc_attr(get_option('nettix_tulokset'));
	$vapaa_haku = esc_attr(get_option('nettix_vapaa_haku'));
	$kilometrit = esc_attr(get_option('nettix_kilometrit'));
	$hinta = esc_attr(get_option('nettix_hinta'));
	$tilavuus = esc_attr(get_option('nettix_tilavuus'));
	$vuosimalli = esc_attr(get_option('nettix_vuosimalli'));
	$paikkakunta = esc_attr(get_option('nettix_paikkakunta'));
	$maakunta = esc_attr(get_option('nettix_maakunta'));

	// Vimpaimet
	$mainos = esc_attr(get_option('nettix_mainos'));
	$lisatiedot = esc_attr(get_option('nettix_lisatiedot'));
	$viesti = esc_attr(get_option('nettix_viesti'));
	$sijainti = esc_attr(get_option('nettix_sijainti'));
	$mainosteksti = esc_attr(get_option('nettix_mainosteksti'));
	$mainos_linkki = esc_attr(get_option('nettix_mainos_linkki'));
	$googlemaps = esc_attr(get_option('nettix_googlemaps'));
	$googlemaps2 = esc_attr(get_option('nettix_googlemaps2'));
	$jakonapit = esc_attr(get_option('nettix_jakonapit'));

	// Cpt
	$cpt = esc_attr(get_option('nettix_cpt'));

	$arr = array(
		'rajapinnat' => $rajapinnat,
		'yrityksen_nimi' => $yrityksen_nimi,
		'yrityksen_osoite' => $yrityksen_osoite,
		'yrityksen_postinumero' => $yrityksen_postinumero,
		'yrityksen_paikkakunta' => $yrityksen_paikkakunta,
		'yrityksen_puhelin' => $yrityksen_puhelin,
		'yrityksen_email' => $yrityksen_email,
		'paikkakunta2' => $paikkakunta2,
		'maakunta2' => $maakunta2,
		'yrityksen_osoite2' => $yrityksen_osoite2,
		'yrityksen_postinumero2' => $yrityksen_postinumero2,
		'yrityksen_paikkakunta2' => $yrityksen_paikkakunta2,
		'ga' => $ga,
		'jarjestys' => $jarjestys,
		'teema' => $teema,
		'pikahaku' => $pikahaku,
		'kuvan_koko' => $kuvan_koko,
		'rows' => $rows,
		'vapaa_haku' => $vapaa_haku,
		'kilometrit' => $kilometrit,
		'hinta' => $hinta,
		'tilavuus' => $tilavuus,
		'vuosimalli' => $vuosimalli,
		'paikkakunta' => $paikkakunta,
		'maakunta' => $maakunta,
		'mainos' => $mainos,
		'lisatiedot' => $lisatiedot,
		'viesti' => $viesti,
		'sijainti' => $sijainti,
		'mainosteksti' => $mainosteksti,
		'mainos_linkki' => $mainos_linkki,
		'googlemaps' => $googlemaps,
		'googlemaps2' => $googlemaps2,
		'jakonapit' => $jakonapit,
		'cpt' => $cpt
	);

	echo json_encode($arr);
	wp_die();
}

add_action( 'wp_ajax_nopriv_getSettings', 'wb_nettix_getSettings' );
add_action( 'wp_ajax_getSettings', 'wb_nettix_getSettings' );

function wb_nettix_getVehiclesSearch() {
	$data = esc_js($_POST['sendData']);
	$dataArr = json_decode(str_replace('&quot;', '"', $data));

	$ajoneuvotyyppi = $dataArr->{'ajoneuvotyyppi'};
	$ajoneuvolaji = $dataArr->{'ajoneuvolaji'};
	$merkki = $dataArr->{'merkki'};
	$malli = $dataArr->{'malli'};
	$vapaa_haku = $dataArr->{'vapaa_haku'};

	$kilometrit_alkaen = $dataArr->{'kilometrit_alkaen'};
	$kilometrit_paattyen = $dataArr->{'kilometrit_paattyen'};
	$hinta_alkaen = $dataArr->{'hinta_alkaen'};
	$hinta_paattyen = $dataArr->{'hinta_paattyen'};

	$tilavuus_alkaen = $dataArr->{'tilavuus_alkaen'};
	$tilavuus_paattyen = $dataArr->{'tilavuus_paattyen'};
	$vuosimalli_alkaen = $dataArr->{'vuosimalli_alkaen'};
	$vuosimalli_paattyen = $dataArr->{'vuosimalli_paattyen'};
	
	$kaupunki = $dataArr->{'kaupunki'};
	$maakunta = $dataArr->{'maakunta'};

	$pagenumber = $dataArr->{'pagenumber'};
	$aloitus = $dataArr->{'aloitus'};

	$rows = esc_attr(get_option('nettix_tulokset'));

	if($rows == '') {
		$rows = 30;
	}

	$resultsArr = [];
	$total = 0;

	// Haku
	if($aloitus != 1) {
		$api = esc_attr(get_option('nettix_palvelin'));
		$id = esc_attr(get_option('nettix_id'));
		$id2 = esc_attr(get_option('nettix_id2'));
		$paikkakunta2 = esc_attr(get_option('nettix_paikkakunta2'));
		$maakunta2 = esc_attr(get_option('nettix_maakunta2'));
		$jarjestys = esc_attr(get_option('nettix_jarjestys'));
		$lajittelu = esc_attr(get_option('nettix_lajittelu'));

		if($lajittelu == '') {
			$lajittelu = 'price';
		}

		if($kaupunki == $paikkakunta2 OR $maakunta == $maakunta2) {
			if($id2 !== '' && $id2 !== null) {
				$id = $id2;
			}
		}

		if($kaupunki == '' && $maakunta == '' && $id2 != '' && $id2 != null) {
			$hakukerroin = 2;

			if($ajoneuvotyyppi == 'Autot') {
				$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
				$output_assoc = json_decode($vehicles);
			} else if($ajoneuvotyyppi == 'Motot') {
				$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
				$output_assoc = json_decode($vehicles);
			}
		} else {
			$hakukerroin = 1;
		}

		if($api == 'tuotanto') {
			$url_start = 'https://api.nettix.fi/';
		} else {
			$url_start = 'https://api-test.nettix.fi/';
		}

		$mergedOutput = '';
		
		for($b=0; $b < $hakukerroin; $b++) {
			if($b == 1 && $id2 != '' && $id2 != null) {
				$id = $id2;
			}

			$rimpsu = '&sortOrder=' . $jarjestys . '&region=' . $maakunta . '&town=' . $kaupunki . '&make=' . $merkki . '&model=' . $malli . '&kilometersFrom=' . $kilometrit_alkaen . '&kilometersTo=' . $kilometrit_paattyen . '&priceFrom=' . $hinta_alkaen . '&priceTo=' . $hinta_paattyen . '&engineSizeFrom=' . $tilavuus_alkaen . '&engineSizeTo=' . $tilavuus_paattyen . '&yearFrom=' . $vuosimalli_alkaen . '&yearTo=' . $vuosimalli_paattyen;

			if($ajoneuvotyyppi == 'Autot') {
				$uri = $url_start . 'rest/car/search?page=' . $pagenumber . '&userId=' . $id . '&rows=' . $rows . '&vehicleType=' . $ajoneuvolaji  . '&sortBy=' . $lajittelu . '&sortOrder=asc&includeMakeModel=true&accessoriesCondition=and' . $rimpsu;

				$uri2 = $url_start . 'rest/car/search-count?includeMakeModel=true&userId=' . $id . '&vehicleType=' . $ajoneuvolaji  . '&accessoriesCondition=and' . $rimpsu;
			} else if($ajoneuvotyyppi == 'Motot') {
				$uri = $url_start . 'rest/bike/search?page=' . $pagenumber . '&userId=' . $id . '&rows=' . $rows . '&bikeType=' . $ajoneuvolaji  . '&sortBy=' . $lajittelu . '&sortOrder=asc&includeMakeModel=true&accessoriesCondition=and' . $rimpsu;

				$uri2 = $url_start . 'rest/bike/search-count?includeMakeModel=true&userId=' . $id . '&bikeType=' . $ajoneuvolaji  . '&accessoriesCondition=and' . $rimpsu;
			}

			$token = file_get_contents(nettix_temp . '/token.txt');

			if(!$token) {
				$token = newToken();
			}

			$ch = curl_init();
			$ch2 = curl_init();

			$header = array();
			$header[] = 'accept: application/json';
			$header[] = 'X-Access-Token: ' . $token;

			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$output = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($output);

			if(is_array($json) && !empty($json)) {
				$output_2 = substr($output, 0, -1);
				$output_2 = substr($output_2, 1);
	
				$mergedOutput .= $output_2 . ',';
			}

			curl_setopt($ch2, CURLOPT_URL, $uri2);
			curl_setopt($ch2, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

			$output2 = curl_exec($ch2);
			curl_close($ch2);
			$json2 = json_decode($output2);

			$total += (int)$json2->total;
		}
		
		$mergedOutput = substr($mergedOutput, 0, -1);
		$output_new = '[' . $mergedOutput . ']';
	} else {
		// Ei haku, haetaan ajoneuvot tempistä
		if($ajoneuvotyyppi == 'Autot') {
			$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
			$output_assoc = json_decode($vehicles);

			$vehicles = json_encode($vehicles);
		} else if($ajoneuvotyyppi == 'Motot') {
			$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
			$output_assoc = json_decode($vehicles);

			$vehicles = json_encode($vehicles);
		}

		$output_new = json_decode($vehicles);
	}

	if($aloitus == 1) {
		$arr2 = array();
		
		foreach($output_assoc as $vehicle) {
			array_push($arr2, $vehicle->id);
		}

		if($total == 0) {
			$total = sizeof($arr2);
		}
		
	}

	array_push($resultsArr, $output_new);
	array_push($resultsArr, json_encode(array('total' => $total)));

	echo json_encode($resultsArr);
	wp_die();
}

add_action( 'wp_ajax_nopriv_getVehiclesSearch', 'wb_nettix_getVehiclesSearch' );
add_action( 'wp_ajax_getVehiclesSearch', 'wb_nettix_getVehiclesSearch' );