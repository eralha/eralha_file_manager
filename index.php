<?php
	/*
		Plugin Name: Eralha File Manager
		Plugin URI: 
		Description: You can upload files to your webserver, and use it within posts in order to make links for download.
		Version: 0.0.0.1
		Author: Emanuel Ralha
		Author URI: 
	*/

// No direct access to this file
defined('ABSPATH') or die('Restricted access');

if (!class_exists("eralha_fm")){
	class eralha_fm{

		var $optionsName = "eralha_fm";
		var $table_files = "";
		var $dbVersion = "0.1";

		function eralha_fm(){
			global $wpdb;

			$this->table_files = $wpdb->prefix.$this->optionsName."_files";
		}

		function init(){
			$installed_ver = get_option($this->optionsName."_db_version");
			if($installed_ver != $this->dbVersion){
				$this->activationHandler();
				update_option($this->optionsName."_db_version", $this->dbVersion);
			}
		}
		function activationHandler(){
			global $wpdb;

			$sqlTblFiles = "CREATE TABLE ".$this->table_files." 
			(
				`idFile` int(6) NOT NULL auto_increment, 
				`iData` int(32) NOT NULL, 
				`iUserId` int(32) NOT NULL, 
				`vchFileName` varchar(255) NOT NULL, 
				PRIMARY KEY  (`idFile`)
			);";

			require_once(ABSPATH . 'wp-admin/upgrade.php');
			dbDelta($sqlTblFiles);

			add_option($this->optionsName."_db_version", $this->dbVersion);
		}
		function deactivationHandler(){
			global $wpdb;

			//$wpdb->query("DROP TABLE IF EXISTS ". $this->table_files);
		}

		function printAdminPage(){
			global $wpdb;
			global $user_ID;

			//$pluginDir = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			if(isset($_GET["page"]) && $_GET["page"] == "file-manager"){
				//GET TEMPLATE GALLERY LIST
				if(isset($_GET["handler"])){
					if($_GET["handler"] == "delete-file"){
						$this->deleteImage($_GET["id"]);
					}
				}
				$this->checkFilePost($pluginDir);
				include "templates/list_page.php";
			}
		}

		function deleteImage($id){
			global $wpdb;
			global $user_ID;

			$file = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$this->table_files." WHERE idFile = '%d'", $id), ARRAY_A);
			//DELETE IMAGE FROM UPLOAD FOLDER
				$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
				unlink("..".$uploadPath."uploads/".$file[0]["vchFileName"]);

			//DELETE FILE FROM DATA BASE
				$wpdb->query($wpdb->prepare("DELETE FROM ".$this->table_files." WHERE idFile = '%d' ", $id));
		}

		function checkFilePost($pluginDir){
			global $wpdb;
			global $user_ID;

			if(isset($_FILES['Filedata'])){
				$file_name = $_FILES['Filedata']['name'];
				$file_ext  = substr($file_name, strripos($file_name, '.'));
				$finalName = $_FILES['Filedata']['name'];
				$file_file = $_FILES['Filedata']['tmp_name'];
					
				//RESIZE IMAGE AND MOVE TO FOLDER
					$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", $pluginDir);

					$up = move_uploaded_file($file_file, "../".$uploadPath."uploads/".$finalName);

					if($up){
						//INSERT FILE NAME INTO DB
						$rows_affected = $wpdb->insert($this->table_files, 
											array(
												'iData'=>time(), 
												'iUserId'=>$user_ID, 
												'vchFileName'=>$finalName
											));
					}
			}
		}

		function addContent($content=''){
			global $wpdb;

			$table_images = $wpdb->prefix.$this->optionsName."_images";

			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			preg_match_all('(\[file-manager id:([0-9]*) name:([a-zA-Z0-9[\sãõçáàíìùú\-_. ]*]*)\])', $content, $matches, PREG_PATTERN_ORDER);
			
			for($i=0; $i < count($matches[0]); $i++){
				$id = str_replace("[file-manager id:", "", $matches[0][$i]);
				$id = str_replace("]", "", $id);

				$id = $matches[1][$i];
				$name = $matches[2][$i];

				$fileData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$this->table_files." WHERE idFile = '%d' ", $id), ARRAY_A);
				
				//OUTPUT ALL IMAGES FOR GIVEN ID.
				$template = "";
				foreach($fileData as $data){
					$template = "<div class='ficheiro'><a href='".$pluginDir."uploads/".$data["vchFileName"]."' target='_blank'>".$name."</a></div>";
				}

				$content = str_replace($matches[0][$i], $template, $content);
			}

			return $content;
		}
	}
}
if (class_exists("eralha_fm")) {
	$eralha_fm_obj = new eralha_fm();
}

//Actions and Filters
if (isset($eralha_fm_obj)) {
	//Actions
		register_activation_hook(__FILE__, array($eralha_fm_obj, 'activationHandler'));
		register_deactivation_hook(__FILE__, array($eralha_fm_obj, 'deactivationHandler'));
		add_action('admin_menu', 'eralha_fm_admin_initialize');
		add_action('plugins_loaded', array($eralha_fm_obj, 'init'));

	//Filters
		//Search the content for galery matches
		add_filter('the_content', array($eralha_fm_obj, 'addContent'));
	
	//ADD SCRIPTS ACTIONS
		add_action('wp_enqueue_scripts', 'eralha_fm_enqueue_script');
		add_action('admin_enqueue_scripts', 'eralha_fm_enqueue_script');

}

//ENQUE SCRIPTS
if (!function_exists("eralha_fm_enqueue_script")) {
	function eralha_fm_enqueue_script(){
		$plugindir = plugin_dir_url( __FILE__ );

	   //ADD STYLES
	   		wp_register_style('eralha_fm_styles', $plugindir."css/styles.css");
	    	wp_enqueue_style( 'eralha_fm_styles');
	}
}

//Initialize the admin panel
if (!function_exists("eralha_fm_admin_initialize")) {
	function eralha_fm_admin_initialize() {
		global $eralha_fm_obj;
		if (!isset($eralha_fm_obj)) {
			return;
		}
		if ( function_exists('add_submenu_page') ){
			//ADDS A LINK TO TO A SPECIFIC ADMIN PAGE
			add_menu_page('Ficheiros', 'Ficheiros', 'manage_options', 'file-manager', array($eralha_fm_obj, 'printAdminPage'));
				add_submenu_page('file-manager', 'Enviar ficheiro', 'Enviar ficheiro', 'manage_options', 'file-manager', array($eralha_fm_obj, 'printAdminPage'));
		}
	}
}
?>