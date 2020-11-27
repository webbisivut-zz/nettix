<?php
/*
 * Plugin Name: WordPress Nettix Pro
 * Version: 1.3.20
 * Plugin URI: https://webbisivut.org/
 * Description: WordPress Nettix Pro lisäosa
 * Author: Webbisivut.org
 * Author URI: https://webbisivut.org/
 * Requires at least: 4.0
 * Tested up to: 5.5.4
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

// Tyhjennetään cache ja haetaan token kun asetukset tallennetaan
add_action('init', 'wb_nettix_get_token_on_save');

function wb_nettix_get_token_on_save() {
	if(is_admin() && isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && isset($_GET['page']) && $_GET['page']=='wb_nettix_pro_settings'){
		nettixClearTemp();
		nettixGetToken();

		$nettix_cpt = esc_attr(get_option('nettix_cpt'));

		if($nettix_cpt == 'kylla') {
			nettixCptVehicles();
		}
   }
}

// Haetaan token automaattisesti tunnin välein ja samalla päivitetään sitemap
function wb_nettix_cron_schedules($schedules){
	$schedules["20min"] = array(
		'interval' => 1200,
		'display' => __('Once every 20 minutes')
	);

    return $schedules;
}

add_filter('cron_schedules','wb_nettix_cron_schedules');

add_action('wp', 'wb_nettix_cron_activation');

function wb_nettix_cron_activation() {
	$nettix_cpt = esc_attr(get_option('nettix_cpt'));

	if (!wp_next_scheduled('nettixGetToken')) {
		wp_schedule_event(time(), '20min', 'nettixGetToken');
	}

	if (!wp_next_scheduled('nettixCptVehicles') && $nettix_cpt == 'kylla') {
		wp_schedule_event(time(), 'hourly', 'nettixCptVehicles');
	}

	if (!wp_next_scheduled('deleteOldVehicles') && $nettix_cpt == 'kylla') {
		wp_schedule_event(time(), 'daily', 'deleteOldVehicles');
	}
}

function newToken() {
	$api = esc_attr(get_option('nettix_palvelin'));
	$client_id = esc_attr(get_option('nettix_tunnus'));
	$client_secret = esc_attr(get_option('nettix_salasana'));

	if($api == 'tuotanto') {
		$uri = 'https://auth.nettix.fi/oauth2/token';
	} else {
		$uri = 'https://auth-api.test.nettix-aws.com/oauth2/token';
	}

	// Haetaan token
	$data = [
		'grant_type' => 'client_credentials',
		'client_id' => $client_id,
		'client_secret' => $client_secret
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $uri);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	$output_1 = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($output_1);

	$token = $json->access_token;

	return $token;
}

// Ajoneuvot temppiin
// https://api.nettix.fi/rest/car/search-count?isMyFavorite=false&sortOrder=price&userId=12345&adType=forsale&includeMakeModel=true&accessoriesCondition=and&undrivenVehicle=false&coSeater=false&isPriced=true&taxFree=false&vatDeduct=true
function nettixGetToken() {
	$api = esc_attr(get_option('nettix_palvelin'));
	$token = newToken();

	$token_temp_file = fopen(nettix_temp . '/token.txt', 'w');
	fwrite($token_temp_file, $token);
	fclose($token_temp_file);

	$rajapinnat = get_option('nettix_rajapinnat');
	$xml = esc_attr(get_option('nettix_xml'));
	$jarjestys = esc_attr(get_option('nettix_jarjestys'));
	$lajittelu = esc_attr(get_option('nettix_lajittelu'));

	$id = esc_attr(get_option('nettix_id'));
	$id2 = esc_attr(get_option('nettix_id2'));

	if($id2 != '' && $id2 != null) {
		$kerroin = 2;
	} else {
		$kerroin = 1;
	}

	if(is_array($rajapinnat)) {
		foreach($rajapinnat as $rajapinta) {
			if(stristr($rajapinta, 'Autot')) {
				$mergedOutput = '';

				$total = 0;

				if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/nettiauto/vehicles_full_list.txt')) {
					unlink(plugin_dir_path( __FILE__ ) . 'temp/nettiauto/vehicles_full_list.txt');
				}

				for($z=0; $z < $kerroin; $z++) {
					$id = esc_attr(get_option('nettix_id'));
					$id2 = esc_attr(get_option('nettix_id2'));

					if($z==1 && $id2 != '' && $id2 != null) {
						$id = $id2;
					}

					if($api == 'tuotanto') {
						$uri3_1 = 'https://api.nettix.fi/rest/car/search-count?isMyFavorite=false&sortOrder=' . $jarjestys . '&userId=' . $id . '&adType=forsale';
					} else {
						$uri3_1 = 'https://api-test.nettix.fi/rest/car/search-count?isMyFavorite=false&sortOrder=' . $jarjestys . '&userId=' . $id . '&adType=forsale';
					}

					$ch3_1 = curl_init();

					$header = array();
					$header[] = 'accept: application/json';
					$header[] = 'X-Access-Token: ' . $token;

					// Katsotaan montako ajoneuvoa
					curl_setopt($ch3_1, CURLOPT_URL, $uri3_1);
					curl_setopt($ch3_1, CURLOPT_HTTPHEADER, $header);
					curl_setopt($ch3_1, CURLOPT_RETURNTRANSFER, 1);

					$output_3_1 = curl_exec($ch3_1);
					curl_close($ch3_1);

					$totalcheck = json_decode($output_3_1, true);

					if(isset($totalcheck['error'])) {
						error_log('Virhe haettaessa ajoneuvojen määrää: ' . $total['error']);
						$total = 100;
					} else {
						$total += (int) $totalcheck['total'];
					}

					$pages = $total / 100;
					$pages = ceil($pages) + 1;

					for($x=1; $x < $pages; $x++) {
						if($api == 'tuotanto') {
							$uri3 = 'https://api.nettix.fi/rest/car/search?page=' . $x . '&sortOrder=' . $jarjestys . '&userId=' . $id . '&rows=100&sortBy=' . $lajittelu . '&includeMakeModel=true&accessoriesCondition=and';
						} else {
							$uri3 = 'https://api-test.nettix.fi/rest/car/search?page=' . $x . '&sortOrder=' . $jarjestys . '&userId=' . $id . '&rows=100&sortBy=' . $lajittelu . '&includeMakeModel=true&accessoriesCondition=and';
						}

						$ch3 = curl_init();
						curl_setopt($ch3, CURLOPT_URL, $uri3);
						curl_setopt($ch3, CURLOPT_HTTPHEADER, $header);
						curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);

						$output_3 = curl_exec($ch3);
						curl_close($ch3);

						$output_3 = substr($output_3, 0, -1);
						$output_3 = substr($output_3, 1);

						$mergedOutput .= $output_3 . ',';

						sleep(2);
					}
				}

				$mergedOutput = substr($mergedOutput, 0, -1);
				$mergedOutput = '[' . $mergedOutput . ']';

				$vehicles_temp_file = fopen(nettix_temp . '/nettiauto/vehicles_full_list.txt', 'w');
				fwrite($vehicles_temp_file, $mergedOutput);
				fclose($vehicles_temp_file);

				// Generoidaan XML tarvittaessa
				if($xml == 'kylla') {
					$xmlContent = generateXML('nettiauto');

					if(file_exists(nettix_temp . '/temp/nettiauto/nettiauto.xml')) {
						unlink(nettix_temp . '/temp/nettiauto/nettiauto.xml');
					}

					$xml_content_xml = fopen(nettix_temp . '/nettiauto/nettiauto.xml', 'w');
					fwrite($xml_content_xml, $xmlContent);
					fclose($xml_content_xml);
				}
			}

			if(stristr($rajapinta, 'Motot')) {
				$mergedOutput = '';
				$total = 0;

				if(file_exists(nettix_temp . '/temp/nettimoto/vehicles_full_list.txt')) {
					unlink(nettix_temp . '/temp/nettimoto/vehicles_full_list.txtl');
				}

				for($z=0; $z < $kerroin; $z++) {
					$id = esc_attr(get_option('nettix_id'));
					$id2 = esc_attr(get_option('nettix_id2'));

					if($z==1 && $id2 != '' && $id2 != null) {
						$id = $id2;
					}

					if($api == 'tuotanto') {
						$uri3_1 = 'https://api.nettix.fi/rest/bike/search-count?isMyFavorite=false&sortOrder=' . $jarjestys . '&userId=' . $id . '&adType=forsale';
					} else {
						$uri3_1 = 'https://api-test.nettix.fi/rest/bike/search-count?isMyFavorite=false&sortOrder=' . $jarjestys . '&userId=' . $id . '&adType=forsale';
					}

					$ch3_1 = curl_init();

					$header = array();
					$header[] = 'accept: application/json';
					$header[] = 'X-Access-Token: ' . $token;

					// Katsotaan montako ajoneuvoa
					curl_setopt($ch3_1, CURLOPT_URL, $uri3_1);
					curl_setopt($ch3_1, CURLOPT_HTTPHEADER, $header);
					curl_setopt($ch3_1, CURLOPT_RETURNTRANSFER, 1);

					$output_3_1 = curl_exec($ch3_1);
					curl_close($ch3_1);

					$totalcheck = json_decode($output_3_1, true);

					if(isset($totalcheck['error'])) {
						error_log('Virhe haettaessa ajoneuvojen määrää: ' . $total['error']);
						$total = 100;
					} else {
						$total += (int) $totalcheck['total'];
					}

					$pages = $total / 100;
					$pages = ceil($pages) + 1;

					for($x=1; $x < $pages; $x++) {
						if($api == 'tuotanto') {
							$uri3 = 'https://api.nettix.fi/rest/bike/search?page=' . $x . '&sortOrder=' . $jarjestys . '&userId=' . $id . '&rows=100&sortBy=' . $lajittelu . '&includeMakeModel=true&accessoriesCondition=and';
						} else {
							$uri3 = 'https://api-test.nettix.fi/rest/bike/search?page=' . $x . '&sortOrder=' . $jarjestys . '&userId=' . $id . '&rows=100&sortBy=' . $lajittelu . '&includeMakeModel=true&accessoriesCondition=and';
						}

						$ch3 = curl_init();
						curl_setopt($ch3, CURLOPT_URL, $uri3);
						curl_setopt($ch3, CURLOPT_HTTPHEADER, $header);
						curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);

						$output_3 = curl_exec($ch3);
						curl_close($ch3);

						$output_3 = substr($output_3, 0, -1);
						$output_3 = substr($output_3, 1);

						$mergedOutput .= $output_3 . ',';

						sleep(2);
					}
				}

				$mergedOutput = substr($mergedOutput, 0, -1);
				$mergedOutput = '[' . $mergedOutput . ']';

				$vehicles_temp_file = fopen(nettix_temp . '/nettimoto/vehicles_full_list.txt', 'w');
				fwrite($vehicles_temp_file, $mergedOutput);
				fclose($vehicles_temp_file);

				// Generoidaan XML tarvittaessa
				if($xml == 'kylla') {
					$xmlContent = generateXML('nettimoto');

					if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/nettimoto/nettimoto.xml')) {
						unlink(plugin_dir_path( __FILE__ ) . 'temp/nettimoto/nettimoto.xml');
					}

					$xml_content_xml = fopen(nettix_temp . '/nettimoto/nettimoto.xml', 'w');
					fwrite($xml_content_xml, $xmlContent);
					fclose($xml_content_xml);
				}
			}

		}
	} else {
		error_log('Nettix virhe! Rajapintoja ei valittuna asetuksissa!');
	}

}

add_action('nettixGetToken', 'nettixGetToken');

// Luodaan XML
function generateXML($type = 'nettiauto') {
	$xmlContent = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$xmlContent .= '<document>'."\n";

	if($type == 'nettiauto') {
		$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
	} else if($type == 'nettimoto') {
		$vehicles = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
	} else {
		$vehicles = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
	}

	if($vehicles) {
		$vehicles = json_decode($vehicles);
	}

	if(is_array($vehicles)) {
		foreach($vehicles as $vehicle) {
			$desc = str_replace('&', '&amp;', $vehicle->description);

			$acsArr = [];
			if($vehicle->accessories != null) {
				foreach($vehicle->accessories as $accessory) {
					array_push($acsArr, $accessory->fi);
				}
			}

			$xmlContent .= "<product>"."\n";
			$xmlContent .= "<id>" . $vehicle->id . "</id>"."\n";
			$xmlContent .= "<make>" . $vehicle->make->name . "</make>"."\n";
			$xmlContent .= "<model>" . $vehicle->model->name . "</model>"."\n";
			
			if($vehicle->modelType != null && $vehicle->modelType->name != '') {
				$xmlContent .= "<modelType>" . $vehicle->modelType->name . "</modelType>"."\n";
			} else {
				$xmlContent .= "<modelType>" . '' . "</modelType>"."\n";
			}

			if($vehicle->modelTypeName != null && $vehicle->modelTypeName != '') {
				$xmlContent .= "<modelTypeName>" . $vehicle->modelTypeName . "</modelTypeName>"."\n";
			} else {
				$xmlContent .= "<modelTypeName>" . '' . "</modelTypeName>"."\n";
			}
			
			$xmlContent .= "<year>" . $vehicle->year . "</year>"."\n";
			$xmlContent .= "<mileage>" . $vehicle->kilometers . "</mileage>"."\n";
			$xmlContent .= "<accessories>" . implode(', ', $acsArr) . "</accessories>"."\n";
			$xmlContent .= "<price>" . $vehicle->price . "</price>"."\n";

			if($type == 'nettiauto') {
				$xmlContent .= "<vehicleType>" . $vehicle->vehicleType->fi . "</vehicleType>"."\n";
				$xmlContent .= "<osasto>" . $vehicle->make->name . ' ' . $vehicle->vehicleType->fi . "</osasto>"."\n";
			}

			if($type == 'nettimoto') {
				$xmlContent .= "<bikeType>" . $vehicle->bikeType->fi . "</bikeType>"."\n";
				$xmlContent .= "<osasto>" . $vehicle->make->name . ' ' . $vehicle->bikeType->fi . "</osasto>"."\n";
			}

			$xmlContent .= "<description>" . $desc . "</description>"."\n";

			$x=0;
			foreach($vehicle->images as $img) {
				$xmlContent .= "<img" . $x . ">" . $img->large->url . "</img" . $x . ">"."\n";
				$x++;
			}

			$xmlContent .= "</product>"."\n";
		}
	}

	$xmlContent .= '</document>';

	return $xmlContent;
}

// Päivitetään sitemap
function nettixUpdateSiteMap() {
	$shortcodeUrl = esc_attr(get_option('nettix_yoast_nettix_url'));

	$sitemapContent = '';

	$autot = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
	if($autot) {
		$autot = json_decode($autot);
	}

	if(is_array($autot)) {
		foreach($autot as $auto) {
			$sitemapContent .= "<sitemap>"."\n";
			$sitemapContent .= "<loc>" . $shortcodeUrl . '?id=' . $auto->id . '&amp;tyyppi=Autot' . "</loc>"."\n";
			$sitemapContent .= "<lastmod>" . date(DATE_ATOM, time()) . "</lastmod>"."\n";
			/*$sitemapContent .= "<changefreq>daily</changefreq>"."\n";*/
			$sitemapContent .= "</sitemap>"."\n";
		}
	}

	$moottoripyorat = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
	if($moottoripyorat) {
		$moottoripyorat = json_decode($moottoripyorat);
	}

	if(is_array($moottoripyorat)) {
		foreach($moottoripyorat as $moottoripyora) {
			$sitemapContent .= "<sitemap>"."\n";
			$sitemapContent .= "<loc>" . $shortcodeUrl . '?id=' . $moottoripyora->id . '&amp;tyyppi=Motot' . "</loc>"."\n";
			$sitemapContent .= "<lastmod>" . date(DATE_ATOM, time()) . "</lastmod>"."\n";
			/*$sitemapContent .= "<changefreq>daily</changefreq>"."\n";*/
			$sitemapContent .= "</sitemap>"."\n";
		}
	}

	/*
	$sitemapContent .= "<urlset>";
	*/

	if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/sitemap.xml')) {
		unlink(plugin_dir_path( __FILE__ ) . 'temp/sitemap.xml');
	}

	$sitemap_xml = fopen(nettix_temp . '/sitemap.xml', 'w');
	fwrite($sitemap_xml, $sitemapContent);
	fclose($sitemap_xml);
}

//add_action('nettixUpdateSiteMap', 'nettixUpdateSiteMap');

// Tyhjennetään temp
function nettixClearTemp() {
	if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/nettimoto/vehicles_full_list.txt')) {
		unlink(plugin_dir_path( __FILE__ ) . 'temp/nettimoto/vehicles_full_list.txt');
	}

	if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/nettiauto/vehicles_full_list.txt')) {
		unlink(plugin_dir_path( __FILE__ ) . 'temp/nettiauto/vehicles_full_list.txt');
	}
}

add_action( 'upgrader_process_complete', 'wb_nettix_upgrate_function',10, 2);

// Haetaan Token ja ajoneuvot temppiin kun lisäosa päivitetään
function wb_nettix_upgrate_function( $upgrader_object, $options ) {
	$our_plugin = plugin_basename( __FILE__ );

    if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
       	foreach($options['plugins'] as $plugin) {
			if( $plugin == $our_plugin ) {
				nettixClearTemp();
				nettixGetToken();
		   	}
       	}
    }
}

function nettixCptVehicles() {
	if(file_exists(nettix_temp . '/nettiauto/vehicles_full_list.txt')) {
		$vehicles_nettiauto = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
		$vehicles_nettiauto = json_decode($vehicles_nettiauto);
	}

	if(file_exists(nettix_temp . '/nettimoto/vehicles_full_list.txt')) {
		$vehicles_nettimoto = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
		$vehicles_nettimoto = json_decode($vehicles_nettimoto);
	}

	if(isset($vehicles_nettiauto) && is_array($vehicles_nettiauto)) {
		foreach($vehicles_nettiauto as $vehicle) {
			generateNettixVehicle($vehicle, 'Autot');
		}
	}

	if(isset($vehicles_nettimoto) && is_array($vehicles_nettimoto) ) {
		foreach($vehicles_nettimoto as $vehicle) {
			generateNettixVehicle($vehicle, 'Motot');
		}
	}
	
}

function deleteOldVehicles() {
	$vehicles_nettiauto = array();
	$vehicles_nettimoto = array();

	if(file_exists(nettix_temp . '/nettiauto/vehicles_full_list.txt')) {
		$vehicles_nettiauto = file_get_contents(nettix_temp . '/nettiauto/vehicles_full_list.txt');
		$vehicles_nettiauto = json_decode($vehicles_nettiauto);
	}

	if(file_exists(nettix_temp . '/nettimoto/vehicles_full_list.txt')) {
		$vehicles_nettimoto = file_get_contents(nettix_temp . '/nettimoto/vehicles_full_list.txt');
		$vehicles_nettimoto = json_decode($vehicles_nettimoto);
	}

	$mergedArray = array_merge($vehicles_nettiauto, $vehicles_nettimoto); 

	deleteVehicles($mergedArray);
}

add_shortcode('deleteOldVehicles', 'deleteOldVehicles');

function generateNettixVehicle($vehicle, $category) {
	global $wpdb;
	$post_id = $wpdb->get_var( "select post_id from $wpdb->postmeta where meta_value = $vehicle->id");

	// Luodaan uusi
	if($post_id == '' OR $post_id == null) {
		
		$post_id = -1;

		//Check if category already exists
		/*
		$cat_ID = get_cat_ID( $category );

		//If it doesn't exist create new category
		if($cat_ID == 0) {
			require_once(ABSPATH . 'wp-config.php'); 
			require_once(ABSPATH . 'wp-includes/wp-db.php'); 
			require_once(ABSPATH . 'wp-admin/includes/taxonomy.php'); 

			$cat_name = array('cat_name' => $category);
			wp_insert_category($cat_name);
		}

		//Get ID of category again incase a new one has been created
		$new_cat_ID = get_cat_ID($category);
		*/

		// Asetetaan tarvittavat arvot tilaukselle
		if(isset($vehicle->make->name)) {
			$vehicleMakeName = $vehicle->make->name;
		} else {
			$vehicleMakeName = '';
		}
		
		if(isset($vehicle->model->name)) {
			$vehicleModelName = $vehicle->model->name;
		} else {
			$vehicleModelName = '';
		}

		if(isset($vehicle->modelType->name)) {
			$vehicleModeltypeName = $vehicle->modelType->name;
		} else {
			$vehicleModeltypeName = '';
		}

		if(isset($vehicle->year)) {
			$vehicleYear = $vehicle->year;
		} else {
			$vehicleYear = '';
		}

		if(isset($vehicle->price)) {
			$vehiclePrice = $vehicle->price;
		} else {
			$vehiclePrice = '';
		}

		$author_id = 1;
		$slug = $vehicleMakeName . '-' . $vehicleModelName . '-' . $vehicleModeltypeName . '-' . $vehicleYear . '-' . $vehiclePrice;
		$title = $vehicleMakeName . ' ' . $vehicleModelName . ' ' . $vehicleModeltypeName . ' vm. ' . $vehicleYear;

		if($vehicle->description != null) {
			$content = $vehicle->description;
		} else {
			$content = '';
		}

		// Jos sivua ei vielä ole, niin luodaan se
		if( null == get_page_by_title( $title ) ) {
			$post_arr = array(
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	$author_id,
				'post_name'			=>	$slug,
				'post_title'		=>	$title,
				'post_status'		=>	'publish',
				'post_content' 		=> 	$content, 
				//'post_category' 	=> 	array($new_cat_ID),
				'post_type'			=>	'nettix_ajoneuvot'
			);

			$post_id = wp_insert_post($post_arr);

			add_post_meta($post_id, 'nettix_ajoneuvo_id', $vehicle->id, true);
			add_post_meta($post_id, 'nettix_ajoneuvo_tyyppi', $category, true);

			Generate_Featured_Image( $vehicle->images[0]->large->url, $post_id );

			$generated_post = get_post( $post_id );
			wp_update_post( $generated_post );
		} else {
			// -2 yhtä kuin sivu on jo olemassa, joten keskeytetään.
			$post_id = -2;
		}
	}
}

function deleteVehicles($vehicles) {
	global $wpdb;
	$post_ids = array();
	$vehicle_ids = array();

	foreach($vehicles as $vehicle) {
		$vehicle_id = $vehicle->id;
		$post_id = $wpdb->get_var( "select post_id from $wpdb->postmeta where meta_value = $vehicle_id");

		array_push($vehicle_ids, (int)$post_id);
	}

	$args = array( 'post_type' => 'nettix_ajoneuvot', 'posts_per_page' => -1 );

	$loop = new WP_Query( $args );
	while ( $loop->have_posts() ) : $loop->the_post();
		$post_id_new = get_the_ID();

		array_push($post_ids, (int)$post_id_new);
	endwhile;

	foreach($post_ids as $post_id) {
		
		if(!empty($vehicle_ids) && !in_array($post_id, $vehicle_ids)) {
			delete_post_thumbnail( $post_id );
			wp_delete_post( $post_id, true );
		}
	}
}

function Generate_Featured_Image( $image_url, $post_id  ) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
	
	if(wp_mkdir_p($upload_dir['path'])) {
		$file = $upload_dir['path'] . '/' . $filename; 
	} else {
		$file = $upload_dir['basedir'] . '/' . $filename;
	}                              

    file_put_contents($file, $image_data);

	$wp_filetype = wp_check_filetype($filename, null );
	
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
	
	$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
	
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	
	$res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
}