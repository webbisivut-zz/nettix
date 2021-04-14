<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WB_Nettix_Pro_Settings {

	/**
	 * The single instance of WB_Nettix_Pro_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'nettix_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_menu_page( __( 'Nettix', 'wb-nettix-pro' ) , __( 'Nettix', 'wb-nettix-pro' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ), plugin_dir_url( __FILE__ ) . '../dist/img/nettix.png' );

		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wb-nettix-pro' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['standard'] = array(
			'title'					=> __( 'Yleiset', 'wb-nettix-pro' ),
			'description'			=> __( 'Yleiset asetukset', 'wb-nettix-pro' ),
			'fields'				=> array(
				array(
					'id' 			=> 'rajapinnat',
					'label'			=> __( 'Käytettävät rajapinnat', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse mitä rajapintoja haluat ottaa käyttöön. Vähintään yksi rajapinta tulee olla valittuna.', 'wb-nettix-pro' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'Autot' => 'Nettiauto', 'Motot' => 'Nettimoto' ),
					'default'		=> array( 'Autot' )
				),
				array(
					'id' 			=> 'tunnus',
					'label'			=> __( 'Käyttäjätunnus' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna rajapinnan käyttäjätunnus tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'salasana',
					'label'			=> __( 'Salasana' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna rajapinnan salasana tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'id',
					'label'			=> __( 'ID' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna Nettix käyttäjä ID tähän kenttään. (Sama kuin Nettiautossa/Nettimotossa, jne)', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'id2',
					'label'			=> __( 'Vaihtoehtoinen ID' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna vaihtoehtoinen Nettix käyttäjä ID tähän kenttään.', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'paikkakunta2',
					'label'			=> __( 'Vaihtoehtoinen paikkakunta ID' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna vaihtoehtoinen paikkunta ID, johon käyttäjä ID liitetään.', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'maakunta2',
					'label'			=> __( 'Vaihtoehtoinen maakunta ID' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna vaihtoehtoinen maakunta ID, johon käyttäjä ID liitetään.', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_nimi',
					'label'			=> __( 'Yrityksen nimi' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen nimi tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_osoite',
					'label'			=> __( 'Yrityksen osoite' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen osoite tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_postinumero',
					'label'			=> __( 'Yrityksen postinumero' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen postinumero tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_paikkakunta',
					'label'			=> __( 'Yrityksen paikkakunta' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen paikkakunta tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_osoite2',
					'label'			=> __( 'Yrityksen toinen osoite' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen toinen osoite tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_postinumero2',
					'label'			=> __( 'Yrityksen toinen postinumero' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen toinen postinumero tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_paikkakunta2',
					'label'			=> __( 'Yrityksen toinen paikkakunta' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen toinen paikkakunta tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_puhelin',
					'label'			=> __( 'Yrityksen puhelin' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen puhelin tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'yrityksen_email',
					'label'			=> __( 'Yrityksen sähköposti' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna yrityksen sähköposti tähän kenttään', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'palvelin',
					'label'			=> __( 'Palvelin' , 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse käytetäänkö tuotanto-, vaiko testipalvelinta', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('tuotanto' => 'Tuotantopalvelin', 'test' => 'Testipalvelin'),
					'default'		=> 'tuotanto'
				),
				array(
					'id' 			=> 'xml',
					'label'			=> __( 'Ajoneuvotiedot XML formaattiin' , 'wb-nettix-pro' ),
					'description'	=> __( 'Voidaan generoida rajapinnan palauttamat ajoneuvot XML formaattiin tarvittaessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'ga',
					'label'			=> __( 'Google Analytics Events' , 'wb-nettix-pro' ),
					'description'	=> __( 'Lisätäänkö Google Analytics Events tuki?', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'jarjestys',
					'label'			=> __( 'Järjestys' , 'wb-nettix-pro' ),
					'description'	=> __( 'Kohteiden järjestys', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('asc' => 'Nouseva', 'desc' => 'Laskeva'),
					'default'		=> 'asc'
				),
				array(
					'id' 			=> 'lajittelu',
					'label'			=> __( 'Lajittelu' , 'wb-nettix-pro' ),
					'description'	=> __( 'Kohteiden lajittelu', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('price' => 'Hinta', 'dateCreated' => 'Julkaisupäivä', 'lastModified' => 'Muokattu', 'year' => 'Vuosimalli'),
					'default'		=> 'price'
				),
			)
		);

		$settings['ulkoasu'] = array(
			'title'					=> __( 'Ulkoasu', 'wb-nettix-pro' ),
			'description'			=> __( 'Ulkoasuun liittyvät asetukset.', 'wb-nettix-pro' ),
			'fields'				=> array(
				array(
					'id' 			=> 'tausta',
					'label'			=> __( 'Valitse taustaväri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään ulkoasussa. Väriä muuttamalla saat lisäosan ulkoasun sopimaan sivuston ulkoasun kanssa. Oletus #ffffff.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#ffffff',
					'placeholder'	=> '#ffffff',
				),
				array(
					'id' 			=> 'paavari1',
					'label'			=> __( 'Valitse pääväri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään ulkoasussa. Väriä muuttamalla saat lisäosan ulkoasun sopimaan sivuston ulkoasun kanssa. Oletus #ba0000.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#ba0000',
					'placeholder'		=> '#ba0000',
				),
				array(
					'id' 			=> 'paavari2',
					'label'			=> __( 'Valitse toissijainen väri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään ulkoasussa. Väriä muuttamalla saat lisäosan ulkoasun sopimaan sivuston ulkoasun kanssa. Oletus #eeeeee.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#eeeeee',
					'placeholder'		=> '#eeeeee',
				),
				array(
					'id' 			=> 'painikkeet1',
					'label'			=> __( 'Valitse painikkeiden pääväri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään ulkoasussa. Väriä muuttamalla saat lisäosan ulkoasun sopimaan sivuston ulkoasun kanssa. Oletus #218900.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#218900',
					'placeholder'		=> '#218900',
				),
				array(
					'id' 			=> 'painikkeet2',
					'label'			=> __( 'Valitse painikkeiden toissijainen väri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään ulkoasussa. Väriä muuttamalla saat lisäosan ulkoasun sopimaan sivuston ulkoasun kanssa. Oletus #ff5a00.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#ff5a00',
					'placeholder'		=> '#ff5a00',
				),
				array(
					'id' 			=> 'painikkeet3',
					'label'			=> __( 'Valitse painikkeiden tekstin väri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään ulkoasussa. Väriä muuttamalla saat lisäosan ulkoasun sopimaan sivuston ulkoasun kanssa. Oletus #ffffff.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#ffffff',
					'placeholder'		=> '#ffffff',
				),
				array(
					'id' 			=> 'kohteen_tausta',
					'label'			=> __( 'Valitse kohteiden taustan väri', 'wb-nettix-pro' ),
					'description'	=> __( 'Tätä väriä käytetään kohteen taustassa. Oletus #f2f2f2.', 'wb-nettix-pro' ),
					'type'			=> 'color',
					'default'		=> '#f2f2f2',
					'placeholder'		=> '#f2f2f2',
				),
				array(
					'id' 			=> 'kohteen_varjostus',
					'label'			=> __( 'Kohteen varjostus', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko kohteelle varjostuksen.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'teema',
					'label'			=> __( 'Teema', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse teema', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('teema1' => 'Teema1', 'teema2' => 'Teema2'),
					'default'		=> 'teema2'
				),
				
			)
		);

		$settings['kohdesivu'] = array(
			'title'					=> __( 'Kohdesivu', 'wb-nettix-pro' ),
			'description'			=> __( 'Yksittäiseen kohteeseen liittyvät asetukset.', 'wb-nettix-pro' ),
			'fields'				=> array(
				array(
					'id' 			=> 'kuvan_koko',
					'label'			=> __( 'Pääkuvan koko', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse kohdesivun pääkuvan koko', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('medium' => 'Medium', 'large' => 'Large'),
					'default'		=> 'medium'
				),				
				array(
					'id' 			=> 'km_otsikossa',
					'label'			=> __( 'Kilometrit otsikossa', 'wb-nettix-pro' ),
					'description'	=> __( 'Näytetäänkö kilometrilukema kohteen otsikossa?', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
			)
		);

		$settings['hakukone'] = array(
			'title'					=> __( 'Hakukone', 'wb-nettix-pro' ),
			'description'			=> __( 'Valitse mitä kenttiä haluat hakukoneessa näytettävän.', 'wb-nettix-pro' ),
			'fields'				=> array(
				array(
					'id' 			=> 'pikahaku',
					'label'			=> __( 'Pikahaku', 'wb-nettix-pro' ),
					'description'	=> __( 'Jos käytössä, haetaan ajoneuvot samalla kun käyttäjä valitsee ajoneuvotyypin/merkin/mallin', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'kylla'
				),
				array(
					'id' 			=> 'tulokset',
					'label'			=> __( 'Hakutulosten määrä', 'wb-nettix-pro' ),
					'description'	=> __( 'Montako hakutulosta per sivu. Oletus 30kpl', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '30',
					'placeholder'	=> '30'
				),
				array(
					'id' 			=> 'kilometrit',
					'label'			=> __( 'Kilometrit alkaen/päättyen', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän kentän näkyvän hakukoneessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'kylla'
				),
				array(
					'id' 			=> 'hinta',
					'label'			=> __( 'Hinta alkaen/päättyen', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän kentän näkyvän hakukoneessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'kylla'
				),
				array(
					'id' 			=> 'tilavuus',
					'label'			=> __( 'Tilavuus alkaen/päättyen', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän kentän näkyvän hakukoneessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'kylla'
				),
				array(
					'id' 			=> 'vuosimalli',
					'label'			=> __( 'Vuosimalli alkaen/päättyen', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän kentän näkyvän hakukoneessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'kylla'
				),
				array(
					'id' 			=> 'paikkakunta',
					'label'			=> __( 'Paikkakunta', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse näytetäänkö paikkakuntavalitsin hakukoneessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'maakunta',
					'label'			=> __( 'Maakunta', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse näytetäänkö maakuntavalitsin hakukoneessa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
			)
		);

		$settings['vimpaimet'] = array(
			'title'					=> __( 'Vimpaimet', 'wb-nettix-pro' ),
			'description'			=> __( 'Valitse mitä vimpaimia näytetään kohteen sivupalkissa.', 'wb-nettix-pro' ),
			'fields'				=> array(
				array(
					'id' 			=> 'mainos',
					'label'			=> __( 'Mainosteksti', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),				
				array(
					'id' 			=> 'laskuri',
					'label'			=> __( 'Laskuri', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'lisatiedot',
					'label'			=> __( 'Lisätiedot', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'tiedot_email',
					'label'			=> __( 'Ajoneuvon tiedot sähköpostiin', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),				
				array(
					'id' 			=> 'viesti',
					'label'			=> __( 'Viesti', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'sijainti',
					'label'			=> __( 'Sijainti', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'jakonapit',
					'label'			=> __( 'Jakopainikkeet', 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse haluatko tämän vimpaimen näkyvän kohteen tiedoissa.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'laskurin_korko',
					'label'			=> __( 'Laskurin korko' , 'wb-nettix-pro' ),
					'description'	=> __( 'Valitse millä korolla kk-laskuri laskee', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '3.9',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'mainosteksti',
					'label'			=> __( 'Mainosteksti' , 'wb-nettix-pro' ),
					'description'	=> __( 'Aseta mainosteksti "mainos" vimpaimelle', 'wb-nettix-pro' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'mainos_linkki',
					'label'			=> __( 'Mainoksen painike url' , 'wb-nettix-pro' ),
					'description'	=> __( 'Jos haluat näyttää painikkeen mainoksen yhteydessä, aseta tähän url osoite jonne painikkeen painaminen ohjaa. Voidaan asettaa esim. yhteystiedot sivu. Jos tyhjä, painiketta ei näytetä.', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'viesti_email',
					'label'			=> __( 'Vastaanottajan sähköposti' , 'wb-nettix-pro' ),
					'description'	=> __( 'Anna sähköpostiosoite, jonne asiakkaan lähettämä viesti lähetetään.', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'googlemaps',
					'label'			=> __( 'Google maps upotuskoodi' , 'wb-nettix-pro' ),
					'description'	=> __( 'Aseta yrityksen Google Maps upotuskoodi tähän. Upotuskoodin voi hakea osoitteesta <a href="https://maps.google.fi/" target="_blank">https://maps.google.fi/</a>', 'wb-nettix-pro' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d254499.81410724166!2d24.738506515349638!3d60.10986783680985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46920bc796210691%3A0xcd4ebd843be2f763!2sHelsinki%2C+Suomi!5e0!3m2!1sfi!2sth!4v1544434220025'
				),
				array(
					'id' 			=> 'sijainti1',
					'label'			=> __( 'Sijainti' , 'wb-nettix-pro' ),
					'description'	=> __( 'Jos annettu, voidaan yhdistää yllä annettu Google Maps sijainti, ajoneuvon sijainnin mukaan', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'googlemaps2',
					'label'			=> __( 'Vaihtoehtoinen Google maps upotuskoodi' , 'wb-nettix-pro' ),
					'description'	=> __( 'Aseta yrityksen Vaihtoehtoinen Google Maps upotuskoodi tähän. Upotuskoodin voi hakea osoitteesta <a href="https://maps.google.fi/" target="_blank">https://maps.google.fi/</a>', 'wb-nettix-pro' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d254499.81410724166!2d24.738506515349638!3d60.10986783680985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46920bc796210691%3A0xcd4ebd843be2f763!2sHelsinki%2C+Suomi!5e0!3m2!1sfi!2sth!4v1544434220025'
				),
				array(
					'id' 			=> 'sijainti2',
					'label'			=> __( 'Vaihtoehtoinen sijainti' , 'wb-nettix-pro' ),
					'description'	=> __( 'Jos annettu, voidaan yhdistää yllä annettu Google Maps sijainti, ajoneuvon sijainnin mukaan', 'wb-nettix-pro' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
			)
		);

		$settings['cpt'] = array(
			'title'					=> __( 'Custom Post Type', 'wb-nettix-pro' ),
			'description'			=> __( 'Custom Post Type asetukset. Custom Post Typen kautta jos halutaan näyttää ajoneuvot, tulee lyhytkoodiin asettaa cpt="true" valitsin. Lisäosan /templates/ hakemistosta löytyy template, jota voidaan käyttää teeman juuressa näyttämään kohteen tiedot.', 'wb-nettix-pro' ),
			'fields'				=> array(
				array(
					'id' 			=> 'cpt',
					'label'			=> __( 'Custom Post Type', 'wb-nettix-pro' ),
					'description'	=> __( 'Otetaanko käyttöön Custom Post Type?', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
				array(
					'id' 			=> 'cpt_generointi',
					'label'			=> __( 'Kohteiden automaattinen generointi', 'wb-nettix-pro' ),
					'description'	=> __( 'Jos käytössä, generoidaan kohteet aina kerran vuorokaudessa. Jos ajoneuvoja on runsaasti, yli 30kpl, kannattaa generointi suorittaa esim. WP All Export Pro lisäosalla.', 'wb-nettix-pro' ),
					'type'			=> 'select',
					'options'		=> array('ei' => 'Ei', 'kylla' => 'Kyllä'),
					'default'		=> 'ei'
				),
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Nettix Asetukset' , 'wb-nettix-pro' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Tallenna Asetukset' , 'wb-nettix-pro' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main WB_Nettix_Pro_Settings Instance
	 *
	 * Ensures only one instance of WB_Nettix_Pro_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WB_Nettix_Pro()
	 * @return Main WB_Nettix_Pro_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
