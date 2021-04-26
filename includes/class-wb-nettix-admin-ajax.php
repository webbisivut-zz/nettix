<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WB_Nettix_Pro_Admin_Ajax {

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		add_action( 'wp_ajax_nopriv_sendMail', array($this, 'wb_nettix_sendMail' ));
		add_action( 'wp_ajax_sendMail', array($this, 'wb_nettix_sendMail' ));		
		add_action( 'wp_ajax_nopriv_sendMailVehicleInfo', array($this, 'wb_nettix_sendMailVehicleInfo' ));
		add_action( 'wp_ajax_sendMailVehicleInfo', array($this, 'wb_nettix_sendMailVehicleInfo' ));
		add_action( 'wp_ajax_nopriv_loadVehicleSingle', array($this, 'wb_nettix_loadVehicleSingle' ));
		add_action( 'wp_ajax_loadVehicleSingle', array($this, 'wb_nettix_loadVehicleSingle' ));
		add_action( 'wp_ajax_nopriv_getVehicles', array($this, 'wb_nettix_getVehicles' ));
		add_action( 'wp_ajax_getVehicles', array($this, 'wb_nettix_getVehicles' ));

		add_action( 'wp_ajax_nopriv_getVehiclesSearch', array($this, 'wb_nettix_getVehiclesSearch' ));
		add_action( 'wp_ajax_getVehiclesSearch', array($this, 'wb_nettix_getVehiclesSearch' ));
	
		add_action( 'wp_ajax_nopriv_getSettings', array($this, 'wb_nettix_getSettings' ));
		add_action( 'wp_ajax_getSettings', array($this, 'wb_nettix_getSettings' ));
	
		add_action( 'wp_ajax_nopriv_getVehicleDetails', array($this, 'wb_nettix_getVehicleDetails' ));
		add_action( 'wp_ajax_getVehicleDetails', array($this, 'wb_nettix_getVehicleDetails' ));
		add_action( 'wp_ajax_nopriv_getAjoneuvolajit', array($this, 'wb_nettix_getAjoneuvolajit' ));
		add_action( 'wp_ajax_getAjoneuvolajit', array($this, 'wb_nettix_getAjoneuvolajit' ));
		add_action( 'wp_ajax_nopriv_getModels', array($this, 'wb_nettix_getModels' ));
		add_action( 'wp_ajax_getModels', array($this, 'wb_nettix_getModels' ));

		add_filter( 'wp_mail_content_type', array($this, 'wb_nettix_set_html_mail_content_type' ));
	}

	/**
	 * Send emails
	 * @since   1.0.0
	 * @return  void
	 */
	public function wb_nettix_sendMail() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$nettix_contact_nimi = sanitize_text_field($dataArr->{'nettix_contact_nimi'});
		$nettix_contact_email = sanitize_email($dataArr->{'nettix_contact_email'});
		$nettix_contact_puhelin = sanitize_text_field($dataArr->{'nettix_contact_puhelin'});
		$nettix_contact_url = esc_url_raw($dataArr->{'nettix_contact_url'});
		$nettix_contact_viesti = str_replace('\n', '<br>', 'Lähettäjä: ' . $nettix_contact_nimi . '<br>' . 'Sähköposti: ' . $nettix_contact_email . '<br>' . 'Puhelin: ' . $nettix_contact_puhelin . '<br>' . 'Ajoneuvon url: ' . $nettix_contact_url . '<br>' . 'Viesti: ' . $dataArr->{'nettix_contact_viesti'});

		$to = esc_attr(get_option('nettix_viesti_email'));
		$subject = 'Tiedustelu ajoneuvosta';

		$headers[] = 'From:' . $nettix_contact_nimi . ' <' . $nettix_contact_email . '>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		wp_mail( $to, $subject, $nettix_contact_viesti, $headers );

		echo 'Viesti lähetetty!';
		wp_die();
	}

	/**
	 * Send vehicle details by email
	 * @since   1.0.0
	 * @return  void
	 */
	public function wb_nettix_sendMailVehicleInfo() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$lang = sanitize_email($dataArr->{'lang'});

		$to = sanitize_email($dataArr->{'nettix_contact_email'});
		$subject = 'Ajoneuvon tiedot';

		if(isset($dataArr->description)) {
			$description = sanitize_text_field($dataArr->description);
			$description = str_replace('\\\\n', '<br>', $description);
		} else {
			$description = '';
		}

		if($description == 'null') {
			$description = '';
		}

		if(isset($dataArr->make)) {
			$make = sanitize_text_field($dataArr->make->name);
		} else {
			$make = '';
		}

		if(isset($dataArr->model)) {
			$model = sanitize_text_field($dataArr->model->name);
		} else {
			$model = '';
		}

		if(isset($dataArr->year)) {
			$year = sanitize_text_field($dataArr->year);
		} else {
			$year = '';
		}

		if(isset($dataArr->price)) {
			$price = sanitize_text_field($dataArr->price);
		} else {
			$price = '';
		}

		if(isset($dataArr->bikeType)) {
			if($lang == 'en') {
				$bikeType = sanitize_text_field($dataArr->bikeType->en);
			} else {
				$bikeType = sanitize_text_field($dataArr->bikeType->fi);
			}
		} else {
			$bikeType = '';
		}

		if(isset($dataArr->bodyType)) {
			if($lang == 'en') {
				$bodyType = sanitize_text_field($dataArr->bodyType->en);
			} else {
				$bodyType = sanitize_text_field($dataArr->bodyType->fi);
			}
		} else {
			$bodyType = '';
		}

		if(isset($dataArr->color)) {
			if($lang == 'en') {
				$color = sanitize_text_field($dataArr->color->en);
			} else {
				$color = sanitize_text_field($dataArr->color->fi);
			}
		} else {
			$color = '';
		}

		if(isset($dataArr->colorType)) {
			if($lang == 'en') {
				$colorType = sanitize_text_field($dataArr->colorType->en);
			} else {
				$colorType = sanitize_text_field($dataArr->colorType->fi);
			}
		} else {
			$colorType = '';
		}

		if(isset($dataArr->fuelType)) {
			if($lang == 'en') {
				$fuelType = sanitize_text_field($dataArr->fuelType->en);
			} else {
				$fuelType = sanitize_text_field($dataArr->fuelType->fi);
			}
		} else {
			$fuelType = '';
		}

		if(isset($dataArr->driveType)) {
			if($lang == 'en') {
				$driveType = sanitize_text_field($dataArr->driveType->en);
			} else {
				$driveType = sanitize_text_field($dataArr->driveType->fi);
			}
		} else {
			$driveType = '';
		}

		if(isset($dataArr->engineSize)) {
			$engineSize = sanitize_text_field($dataArr->engineSize);
		} else {
			$engineSize = '';
		}

		if(isset($dataArr->totalOwners)) {
			$totalOwners = sanitize_text_field($dataArr->totalOwners);
		} else {
			$totalOwners = '';
		}

		if(isset($dataArr->kilometers)) {
			$kilometers = sanitize_text_field($dataArr->kilometers);
		} else {
			$kilometers = '';
		}

		if(isset($dataArr->accessories)) {
			$accessories = $dataArr->accessories;
		} else {
			$accessories = '';
		}

		$nettix_contact_nimi = esc_attr(get_option('nettix_yrityksen_nimi'));
		$nettix_contact_email = esc_attr(get_option('nettix_yrityksen_email'));

		if($lang == 'en') {
			$vehicle_details_str = 'Vehicle information from ' . $nettix_contact_nimi . '<br><br>';
			$vehicle_details_str .= 'Description: ' . $description . '<br>';
			$vehicle_details_str .= 'Make: ' . $make . '<br>';
			$vehicle_details_str .= 'Model: ' . $model . '<br>';
			$vehicle_details_str .= 'Year: ' . $year . '<br>';
			$vehicle_details_str .= 'Price: ' . $price . '<br>';
			
			if($bikeType) {
				$vehicle_details_str .= 'Type: ' . $bikeType . '<br>';
			}
			
			$vehicle_details_str .= 'Type / Additional info: ' . $bodyType . '<br>';
			$vehicle_details_str .= 'Color: ' . $color . '<br>';
			$vehicle_details_str .= 'Color type: ' . $colorType . '<br>';
			$vehicle_details_str .= 'Fuel type: ' . $fuelType . '<br>';
			$vehicle_details_str .= 'Traction: ' . $driveType . '<br>';
			$vehicle_details_str .= 'Engine capacity: ' . $engineSize . '<br>';
			$vehicle_details_str .= 'Total owners: ' . $totalOwners . '<br>';
			$vehicle_details_str .= 'Mileage: ' . $kilometers . '<br>';

			if($accessories) {
				$vehicle_details_str .= '<br>' . 'Accessories: ' . '<br>';

				foreach($accessories as $acc) {
					$vehicle_details_str .= '- ' . $acc->en . '<br>';
				}
			}
			
		} else {
			$vehicle_details_str = 'Ajoneuvon tiedot sivustolta: ' . $nettix_contact_nimi . '<br><br>';
			$vehicle_details_str .= 'Kuvaus: ' . $description . '<br>';
			$vehicle_details_str .= 'Merkki: ' . $make . '<br>';
			$vehicle_details_str .= 'Malli: ' . $model . '<br>';
			$vehicle_details_str .= 'Vuosimalli: ' . $year . '<br>';
			$vehicle_details_str .= 'Hinta: ' . $price . '<br>';
			
			if($bikeType) {
				$vehicle_details_str .= 'Tyyppi: ' . $bikeType . '<br>';
			}
			
			$vehicle_details_str .= 'Tyyppi / Lisätietoja: ' . $bikeType . '<br>';
			$vehicle_details_str .= 'Väri: ' . $color . '<br>';
			$vehicle_details_str .= 'Värin tyyppi: ' . $colorType . '<br>';
			$vehicle_details_str .= 'Polttoaine: ' . $fuelType . '<br>';
			$vehicle_details_str .= 'Vetotapa: ' . $driveType . '<br>';
			$vehicle_details_str .= 'Moottorin tilavuus: ' . $engineSize . '<br>';
			$vehicle_details_str .= 'Omistajien lukumäärä: ' . $totalOwners . '<br>';
			$vehicle_details_str .= 'Mittarilukema: ' . $kilometers . '<br>';

			if($accessories) {
				$vehicle_details_str .= '<br>' . 'Lisävarusteet: ' . '<br>';
				foreach($accessories as $acc) {
					$vehicle_details_str .= '- ' . $acc->fi . '<br>';
				}
			}
		}

		$headers[] = 'From:' . $nettix_contact_nimi . ' <' . $nettix_contact_email . '>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		$sendMail = wp_mail( $to, $subject, $vehicle_details_str, $headers );

		echo 'Viesti lähetetty!';
		wp_die();
	}

	/**
	 * Change emails to html format
	 * @since   1.0.0
	 * @return  void
	 */
	public function wb_nettix_set_html_mail_content_type() {
		return 'text/html';
	}

	/**
	 * Load single vehicle
	 * @since   1.0.0
	 * @return  string
	 */
	public function wb_nettix_loadVehicleSingle() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$id = sanitize_text_field($dataArr->{'id'});

		global $wpdb;
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value= %d", $id ));

		if(!$post_id) {
			WB_Nettix_Functions::nettixCptVehicles();

			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value= %d", $id ));

			echo get_permalink( $post_id );
		} else {
			echo get_permalink( $post_id );
		}

		wp_die();
	}

	/**
	 * Get vehicles
	 * @since   1.0.0
	 * @return  JSON
	 */
	public function wb_nettix_getVehicles() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$ajoneuvotyyppi = sanitize_text_field($dataArr->{'ajoneuvotyyppi'});

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
			WB_Nettix_Functions::nettixGetToken();

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

	/**
	 * Get models
	 * @since   1.0.0
	 * @return  JSON
	 */
	public function wb_nettix_getModels() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$ajoneuvotyyppi = sanitize_text_field($dataArr->{'ajoneuvotyyppi'});
		$ajoneuvolaji = sanitize_text_field($dataArr->{'ajoneuvolaji'});
		$id = sanitize_text_field($dataArr->{'id'});

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

	/**
	 * Get vehicle types
	 * @since   1.0.0
	 * @return  JSON
	 */
	public function wb_nettix_getAjoneuvolajit() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$ajoneuvotyyppi = sanitize_text_field($dataArr->{'ajoneuvotyyppi'});

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

	/**
	 * Get vehicle details
	 * @since   1.0.0
	 * @return  JSON
	 */
	public static function wb_nettix_getVehicleDetails() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$api = esc_attr(get_option('nettix_palvelin'));

		if($api == 'tuotanto') {
			$url_start = 'https://api.nettix.fi/';
		} else {
			$url_start = 'https://api-test.nettix.fi/';
		}

		$ajoneuvotyyppi = sanitize_text_field($dataArr->{'ajoneuvotyyppi'});
		$id = sanitize_text_field($dataArr->{'id'});

		if($ajoneuvotyyppi == 'Autot') {
			$uri = $url_start . 'rest/car/ad/' . $id;
		} else if($ajoneuvotyyppi == 'Motot') {
			$uri = $url_start . 'rest/bike/ad/' . $id;
		}

		$token = file_get_contents(nettix_temp . '/token.txt');

		if(!$token) {
			$token = WB_Nettix_Functions::newToken();
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

	/**
	 * Get settings
	 * @since   1.0.0
	 * @return  JSON
	 */
	public function wb_nettix_getSettings() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		// General settings
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

		// Alternative company details
		$paikkakunta2 = esc_attr(get_option('nettix_paikkakunta2'));
		$maakunta2 = esc_attr(get_option('nettix_maakunta2'));
		$yrityksen_osoite2 = esc_attr(get_option('nettix_yrityksen_osoite2'));
		$yrityksen_postinumero2 = esc_attr(get_option('nettix_yrityksen_postinumero2'));
		$yrityksen_paikkakunta2 = esc_attr(get_option('nettix_yrityksen_paikkakunta2'));

		// Single item
		$kuvan_koko = esc_attr(get_option('nettix_kuvan_koko'));
		$km_otsikossa = esc_attr(get_option('nettix_km_otsikossa'));

		// Search
		$pikahaku = esc_attr(get_option('nettix_pikahaku'));
		$rows = esc_attr(get_option('nettix_tulokset'));
		$vapaa_haku = esc_attr(get_option('nettix_vapaa_haku'));
		$kilometrit = esc_attr(get_option('nettix_kilometrit'));
		$hinta = esc_attr(get_option('nettix_hinta'));
		$tilavuus = esc_attr(get_option('nettix_tilavuus'));
		$vuosimalli = esc_attr(get_option('nettix_vuosimalli'));
		$paikkakunta = esc_attr(get_option('nettix_paikkakunta'));
		$maakunta = esc_attr(get_option('nettix_maakunta'));

		// Widgets
		$mainos = esc_attr(get_option('nettix_mainos'));
		$lisatiedot = esc_attr(get_option('nettix_lisatiedot'));
		$viesti = esc_attr(get_option('nettix_viesti'));
		$tiedot_email = esc_attr(get_option('nettix_tiedot_email'));
		$sijainti = esc_attr(get_option('nettix_sijainti'));
		$mainosteksti = esc_attr(get_option('nettix_mainosteksti'));
		$mainos_linkki = esc_attr(get_option('nettix_mainos_linkki'));
		$googlemaps = esc_attr(get_option('nettix_googlemaps'));
		$googlemaps2 = esc_attr(get_option('nettix_googlemaps2'));
		$jakonapit = esc_attr(get_option('nettix_jakonapit'));
		$laskuri = esc_attr(get_option('nettix_laskuri'));
		$laskurin_korko = esc_attr(get_option('nettix_laskurin_korko'));

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
			'cpt' => $cpt,
			'laskuri' => $laskuri,
			'laskurin_korko' => $laskurin_korko,
			'tiedot_email' => $tiedot_email,
			'km_otsikossa' => $km_otsikossa,
		);

		echo json_encode($arr);
		wp_die();
	}

	/**
	 * Search vehicles
	 * @since   1.0.0
	 * @return  JSON
	 */
	public static function wb_nettix_getVehiclesSearch() {
		if ( ! check_ajax_referer( 'nettix-security-nonce', 'security', false ) ) {	
			wp_send_json_error( 'Invalid security token sent.' );	    
			wp_die();	  
		}

		$data = esc_js($_POST['sendData']);
		$dataArr = json_decode(str_replace('&quot;', '"', $data));

		$ajoneuvotyyppi = sanitize_text_field($dataArr->{'ajoneuvotyyppi'});
		$ajoneuvolaji = sanitize_text_field($dataArr->{'ajoneuvolaji'});
		$merkki = sanitize_text_field($dataArr->{'merkki'});
		$malli = sanitize_text_field($dataArr->{'malli'});
		$vapaa_haku = sanitize_text_field($dataArr->{'vapaa_haku'});

		$kilometrit_alkaen = sanitize_text_field($dataArr->{'kilometrit_alkaen'});
		$kilometrit_paattyen = sanitize_text_field($dataArr->{'kilometrit_paattyen'});
		$hinta_alkaen = sanitize_text_field($dataArr->{'hinta_alkaen'});
		$hinta_paattyen = sanitize_text_field($dataArr->{'hinta_paattyen'});

		$tilavuus_alkaen = sanitize_text_field($dataArr->{'tilavuus_alkaen'});
		$tilavuus_paattyen = sanitize_text_field($dataArr->{'tilavuus_paattyen'});
		$vuosimalli_alkaen = sanitize_text_field($dataArr->{'vuosimalli_alkaen'});
		$vuosimalli_paattyen = sanitize_text_field($dataArr->{'vuosimalli_paattyen'});
		
		$kaupunki = sanitize_text_field($dataArr->{'kaupunki'});
		$maakunta = sanitize_text_field($dataArr->{'maakunta'});

		$pagenumber = sanitize_text_field($dataArr->{'pagenumber'});
		$aloitus = sanitize_text_field($dataArr->{'aloitus'});

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
					$token = WB_Nettix_Functions::newToken();
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

				if(isset($json2->total)) {
					$total += (int)$json2->total;
				}
			}
			
			$mergedOutput = substr($mergedOutput, 0, -1);
			$output_new = '[' . $mergedOutput . ']';
		} else {
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
}

new WB_Nettix_Pro_Admin_Ajax();