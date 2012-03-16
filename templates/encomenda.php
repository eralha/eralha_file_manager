<?php
	$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE idGaleria = '".$_GET["idObject"]."'"), ARRAY_A);

	foreach($gallerysDataSet as $data){
		if(isset($_FILES['Filedata']) && isset($_GET["idObject"])){
			$file_name = $_FILES['Filedata']['name'];
			$file_ext  = substr($file_name, strripos($file_name, '.'));
			$finalName = (time().rand(0, 100000)).$file_ext;
			$file_file = $_FILES['Filedata']['tmp_name'];
			
			//INSERT FILE NAME INTO DB
				$rows_affected = $wpdb->insert($table_images, 
														array(
															'iData'=>time(), 
															'iUserId'=>$user_ID, 
															'idGaleria'=>$_GET['idObject'], 
															'vchImageName'=>$finalName
														));
				
			//RESIZE IMAGE AND MOVE TO FOLDER
				$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", $pluginDir);
				$image = new SimpleImage();
					$image->load($_FILES['Filedata']['tmp_name']);
					$image->resizeToWidth(980);
					$image->save("../".$uploadPath."uploads/".$finalName);
		}
	}
?>