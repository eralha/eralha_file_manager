<?php
	$dataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$this->table_files." ORDER BY idFile DESC"), ARRAY_A);
?>

<div class=wrap>

	<h2>File Manager</h2>
	<h3>Upload Files that you want to display in your posts!</h3>

	<form enctype='multipart/form-data' action="admin.php?page=file-manager" method="POST">
		<fieldset>
			<label for="file"> 
				<input type="file" value="" name="Filedata" id="Filedata" />
				<input type="submit" id="file" value="Send File" />
			</label>
		</fieldset>
	</form>

	<div class="eralhaFMListagemContainer">
		<div class="eflcTop clearfix">
			<div class="eflctName">File Name</div>
			<div class="eflctEditLink">Actions</div>
		</div>
		<?php foreach($dataSet as $data){?>
			<div class="eralhaFMListItem clearfix">
				<div class="efliName">
					<?php echo $data["vchFileName"];?><br />
					<b>Tag:</b> [file-manager id:<?php echo $data["idFile"];?>]
				</div>
				<div class="efliEditLink">
					<a href="admin.php?page=file-manager&id=<?php echo $data["idFile"];?>&handler=delete-file">delete file</a>
				</div>
			</div>
		<?php }?>
	</div>

</div>