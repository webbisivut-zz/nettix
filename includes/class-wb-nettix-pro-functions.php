<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WB_Nettix_Functions {
    
    /**
	 * The constructor
	 * @since 	1.0.0
	 */
    public function __construct() {
        // Actions
        add_action( 'init', array($this, 'wb_nettix_get_token_on_save'));
        add_action( 'deleteOldVehicles', array($this, 'deleteOldVehicles'));
        add_action( 'nettixCptVehicles', array($this, 'nettixCptVehicles'));
        add_action( 'nettixGetToken', array($this, 'nettixGetToken'));
        add_action( 'upgrader_process_complete', array($this, 'wb_nettix_upgrate_function'), 10, 2);

        // Cron
        add_filter( 'cron_schedules',array($this, 'wb_nettix_cron_schedules'));
        add_action( 'wp', array($this, 'wb_nettix_cron_activation'));

        // Shortcodes
        add_shortcode( 'deleteOldVehicles', array($this, 'deleteOldVehicles'));
    }

    /**
	 * Clear them, get token and create cache on settings update
	 * @since 	1.0.0
     * @return void
	 */
    public function wb_nettix_get_token_on_save() {
        if(is_admin() && isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && isset($_GET['page']) && $_GET['page']=='wb_nettix_pro_settings') {
            $this->nettixClearTemp();
            WB_Nettix_Functions::nettixGetToken();

            $nettix_cpt = esc_attr(get_option('nettix_cpt'));

            if($nettix_cpt == 'kylla') {
                WB_Nettix_Functions::nettixCptVehicles();
            }
        }
    }

    /**
	 * Add new cron schedules
	 * @since 	1.0.0
     * @return array
	 */
    public function wb_nettix_cron_schedules($schedules){
        $schedules["20min"] = array(
            'interval' => 1200,
            'display' => __('Once every 20 minutes')
        );

        return $schedules;
    }

    /**
	 * Run cron schedules
	 * @since 	1.0.0
     * @return void
	 */
    public function wb_nettix_cron_activation() {
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

    /**
	 * Get new Token from the API
	 * @since 	1.0.0
     * @return string
	 */
    public static function newToken() {
        $api = esc_attr(get_option('nettix_palvelin'));
        $client_id = esc_attr(get_option('nettix_tunnus'));
        $client_secret = esc_attr(get_option('nettix_salasana'));

        if($api == 'tuotanto') {
            $uri = 'https://auth.nettix.fi/oauth2/token';
        } else {
            $uri = 'https://auth-api.test.nettix-aws.com/oauth2/token';
        }

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

    /**
	 * Create token and vehicles in temp file
     * https://api.nettix.fi/rest/car/search-count?isMyFavorite=false&sortOrder=price&userId=12345&adType=forsale&includeMakeModel=true&accessoriesCondition=and&undrivenVehicle=false&coSeater=false&isPriced=true&taxFree=false&vatDeduct=true
	 * @since 	1.0.0
     * @return void
	 */
    public static function nettixGetToken() {
        $api = esc_attr(get_option('nettix_palvelin'));
        $token = WB_Nettix_Functions::newToken();

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

                    // Generate XML if selected
                    if($xml == 'kylla') {
                        $xmlContent = WB_Nettix_Functions::generateXML('nettiauto');

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

                    // Generate XML if selected
                    if($xml == 'kylla') {
                        $xmlContent = WB_Nettix_Functions::generateXML('nettimoto');

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

    /**
	 * Generate XML file
	 * @since 	1.0.0
     * @return XML
	 */
    public static function generateXML($type = 'nettiauto') {
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

    /**
	 * Clear temp
	 * @since 	1.0.0
     * @return void
	 */
    public function nettixClearTemp() {
        if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/nettimoto/vehicles_full_list.txt')) {
            unlink(plugin_dir_path( __FILE__ ) . 'temp/nettimoto/vehicles_full_list.txt');
        }

        if(file_exists(plugin_dir_path( __FILE__ ) . 'temp/nettiauto/vehicles_full_list.txt')) {
            unlink(plugin_dir_path( __FILE__ ) . 'temp/nettiauto/vehicles_full_list.txt');
        }
    }

    /**
	 * Get token and create temp files when plugin is updated
	 * @since 	1.0.0
     * @return void
	 */
    public function wb_nettix_upgrate_function( $upgrader_object, $options ) {
        $our_plugin = plugin_basename( __FILE__ );

        if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
            foreach($options['plugins'] as $plugin) {
                if( $plugin == $our_plugin ) {
                    $this->nettixClearTemp();
                    WB_Nettix_Functions::nettixGetToken();
                }
            }
        }
    }

    /**
	 * Generate vehicle as custom post type
	 * @since 	1.0.0
     * @return void
	 */
    public static function nettixCptVehicles() {
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
                WB_Nettix_Functions::generateNettixVehicle($vehicle, 'Autot');
            }
        }

        if(isset($vehicles_nettimoto) && is_array($vehicles_nettimoto) ) {
            foreach($vehicles_nettimoto as $vehicle) {
                WB_Nettix_Functions::generateNettixVehicle($vehicle, 'Motot');
            }
        }
        
    }

    /**
	 * Delete old vehicles from temp and custom post type
	 * @since 	1.0.0
     * @return void
	 */
    public function deleteOldVehicles() {
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

        $this->deleteVehicles($mergedArray);
    }

    /**
	 * Create new vehicles
	 * @since 	1.0.0
     * @return void
	 */
    public static function generateNettixVehicle($vehicle, $category) {
        global $wpdb;
        $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value= %d", $vehicle->id ));

        if($post_id == '' OR $post_id == null) {
            
            $post_id = -1;

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

            // If page doesn't exist, let's create it
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

                WB_Nettix_Functions::generateFeaturedImage( $vehicle->images[0]->large->url, $post_id );

                $generated_post = get_post( $post_id );
                wp_update_post( $generated_post );
            } else {
                // -2 yhtä kuin sivu on jo olemassa, joten keskeytetään.
                $post_id = -2;
            }
        }
    }

    /**
	 * Delete unnecessary vehicles
	 * @since 	1.0.0
     * @return void
	 */
    public function deleteVehicles($vehicles) {
        global $wpdb;
        $post_ids = array();
        $vehicle_ids = array();

        foreach($vehicles as $vehicle) {
            $vehicle_id = $vehicle->id;
            $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %d", $vehicle_id ));

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

    /**
	 * Create featured image for vehicle
	 * @since 	1.0.0
     * @return void
	 */
    public static function generateFeaturedImage( $image_url, $post_id  ) {
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
        
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $post_id, $attach_id );
    }

}

new WB_Nettix_Functions();