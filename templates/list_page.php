<?php
	$dataSet = $wpdb->get_results("SELECT * FROM ".$this->table_files." ORDER BY idFile DESC", ARRAY_A);
?>

<div class=wrap>

	<h2>Gestor de ficheiros</h2>
	<h3>Envie ficheiros para o gestor de forma a poder inserir em paginas ou posts.</h3>

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
			<div class="eflctName">Nome do ficheiro</div>
			<div class="eflctEditLink">Opções</div>
		</div>
		<?php foreach($dataSet as $data){?>
			<div class="eralhaFMListItem clearfix">
				<div class="efliName">
					<?php echo $data["vchFileName"];?><br />
					<b>Código do ficheiro:</b> [file-manager id:<?php echo $data["idFile"];?> name:Whateva]
				</div>
				<div class="efliEditLink">
					<a href="admin.php?page=file-manager&id=<?php echo $data["idFile"];?>&handler=delete-file">apagar ficheiro</a>
				</div>
			</div>
		<?php }?>
	</div>

</div>