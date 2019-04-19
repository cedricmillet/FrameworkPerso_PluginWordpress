<?php
/*
Plugin Name: ABYXO SECURITY
Version: 1.0
Plugin URI: http://abyxo.agency
description: Ajout de sécurités au CMS Wordpress
Author: Cédric MILLET
Author URI: http://www.cedricmillet.fr
*/

if(!defined('ABSPATH')) exit('ACCES INTERDIT.');


//====================================================================
//						MAIN PLUGIN CLASS
//====================================================================

class Abyxo_Security
{
	function __construct( $args = array() )
	{
		$this->init_constants();
		$this->init_config();
		$this->init_includes();
		$this->init_hooks();
		$this->run();
	}

	//	Display Errors
	private function init_config() {
		if(  $this->define('IS_DEV_MODE') === true ) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
	}

	//	Definition des constantes
	private function init_constants() {
		$this->define( 'IS_DEV_MODE', false );
		$this->define( 'PLUGIN_VERSION', 1.0 );
		$this->define( 'PLUGIN_DIR', __DIR__ );
		//	Définir ici plus de constantes...
	}

	//	Ajout des fonctions core
	private function init_includes() {
		//	Classes natives au framework
		$native_files = array(	'class.event.on_plugin_activated.php',
								'class.event.on_plugin_deactivated.php'	);
		foreach ($native_files as $file) {
			include_once( __DIR__.'/includes/'.$file );
		}
		unset($file);

		//	Classes customs dans le dossier /includes/
		$files = scandir(__DIR__.'/includes/');
		foreach ( $files as $k => $file ) {
			if( $file=='.'||$file=='..'||$file=='.htaccess' ) continue;
		    if( strpos($file, '.old' ) !== false) continue;
		    if( in_array( $file , $native_files) ) continue;
			require_once( __DIR__.'/includes/'.$file );
		}

	}

	//	Hooks & filters
	private function init_hooks() {
		//	CSS & JS
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_styles') );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_styles') );
		//	Admin menu pages
		add_action( 'admin_menu', array($this, 'register_admin_menu_pages') );
		//	Activate / Deactivate
		register_activation_hook( __FILE__ , array($this, 'on_plugin_activated') );
		register_deactivation_hook( __FILE__, array($this, 'on_plugin_deactivated') );
	}

	//	Instances des classes enfants
	private function run() {
		new Abyxo_Security_exemple;
	}

	//	CSS
	public function enqueue_styles($hook) {
		if(is_admin()) {
			//	Header CSS
			wp_enqueue_style( strtolower(__CLASS__).'__style-backend', plugin_dir_url(__FILE__) . 'assets/css/style.backend.css' );
			//	Header script
			wp_enqueue_script( strtolower(__CLASS__).'__script-backend-head', plugin_dir_url(__FILE__) . 'assets/js/script.backend.head.js' );
			//	Footer script
			wp_register_script( strtolower(__CLASS__).'__script-backend-footer', plugin_dir_url(__FILE__) . 'assets/js/script.backend.footer.js','',false,true );
    		wp_enqueue_script( strtolower(__CLASS__).'__script-backend-footer' );
		} else {
			//	Header CSS
			wp_enqueue_style( strtolower(__CLASS__).'__style-front', plugin_dir_url(__FILE__) . 'assets/css/style.front.css' );
			//	Header script
			wp_enqueue_script( strtolower(__CLASS__).'__script-front', plugin_dir_url(__FILE__) . 'assets/js/script.front.js' );
			//	Footer script
			wp_register_script( strtolower(__CLASS__).'__script-front-footer', plugin_dir_url(__FILE__) . 'assets/js/script.backend.footer.js','',false,true );
    		wp_enqueue_script( strtolower(__CLASS__).'__script-front-footer' );
		}

		//	jQuery
		if (!wp_script_is( 'jquery', 'enqueued' ))
            wp_enqueue_script( 'jquery' ); 
	}





	//	Definir une constante dans la classe
	public function define($def, $val = 'x') {
		if($val == 'x') {
			$v = $this->{ '___'.$def };
			return $v;
		} else {
			$this->{ '___'.$def } = $val;
			//echo "Affectation variable effectuée. {$def} = {$val}";
		}
	}
	

	public function on_plugin_activated() {
		//	Actions On plugin Activated

	}


	public function on_plugin_deactivated() {
		//	Actions On plugin deactivated
	}

	//	Déclaration des pages Admin
	public function register_admin_menu_pages() {
		
		add_menu_page( 'Aide et Services', 'Aide & Services', 'manage_options', 'contacter-abyxo', array($this, 'main_admin_page'),  plugin_dir_url(__FILE__) . 'assets/img/logo_pluin.png', 90 );
	}

	//	Afficher le contenu de la page d'administration du plugin
	public function main_admin_page() {
		include_once( __DIR__.'/admin/index.php' );
	}
	
}



//====================================================================
//						INSTANCE DU PLUGIN
//====================================================================
if (class_exists('Abyxo_Security'))
	new Abyxo_Security;