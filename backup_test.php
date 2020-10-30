<?php 
	include 'db/dbCon.php';
	include 'db/functions.php';

	$fileToBeBackedUp = '000000001.pdf';
	$data = [];
	$data['backup_folder'] = date('m') . "_" . date('M') . "_" . date('Y') . "/";

	$data['backup_filename']  = '04162020-Gertex-3054104.pdf';

	if (!is_dir("document_backups/" . $data['backup_folder'])) {
		mkdir("document_backups/" . $data['backup_folder'], 0777, true);
	}
	
	//generated backup file path
	$backup_path = "document_backups/" . $data['backup_folder'] .  $data['backup_filename'] ;
	//copy $fileToBeBackedUp to folder with formatted filename
	
	if (!file_exists($backup_path)) {		
		if (copy('documents/' . $fileToBeBackedUp, $backup_path)) {
			echo "file backed up";
		}
	} else {
		return "file already backed up";
	}

 ?>
 