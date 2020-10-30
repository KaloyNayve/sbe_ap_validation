<?php 
	include 'db/dbCon.php';
	include 'db/functions.php';

	/*
		Extracts invoices from portal archive folder to the private ahone folder
	*/
	
	$portal_archive_dir = 'D:\Portals\sbe_ap_validation\documents_archived\\';
	$archive_directory = '\\\\ahone\Accounts_Payable\Invoices - For Progidoc\AP Validation Portal\\';

	foreach (new DirectoryIterator($portal_archive_dir) as $fileInfo) {
	    if($fileInfo->isDot()) continue;
	    $dir = $fileInfo->getFilename(); // get directory
	    echo $dir . "<br/>";
	    // get files in the directory
	    $allFiles = scandir($portal_archive_dir . $dir);
	    $files = array_diff($allFiles, array('.', '..')); // Remove . and ..
	    
	    if (count($files) > 0) {
	    	// if there are files in the directory, check if directory exists if not create directory
	    	if (!is_dir($archive_directory . $dir)) {
	    		mkdir($archive_directory . $dir , 0777, true);
	    	} 
	    	// loop over files array
	    	foreach ($files as $key => $value) {
	    		# if file does not exists in archive dir copy file
	    		$archiveFile = $archive_directory . $dir . "\\" . $value;
	    		if (!file_exists($archiveFile)) {
	    			$sourceFile = $portal_archive_dir . $dir . "\\" . $value;
	    			copy($sourceFile, $archiveFile);
	    			echo "Transferred: $value \n";
	    			// if (file_exists($archiveFile)) {
	    			// 	unlink($sourceFile); // Delete source file
	    			// }
	    		}
	    	}

	    }

	}


 ?>
 