<?php

	$param = $_POST;
	$db = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST=10.194.1.43)(PROTOCOL=tcp)(PORT=1521))(CONNECT_DATA=(SID=CA02)))";
	if(isset($_POST['plant'])){
		if($_POST['plant'] == '94'){
			$db_username = "sbe";
		}else if($_POST['plant'] == '96'){
			$db_username = "site96";
		}
	}else{
		$db_username = "sbe";
	}
	$db_password = "artemis";
	
	$conn = new PDO($db,$db_username,$db_password);	
	if (!$conn) {
	   echo "Did not Connect";   
	}
	$db_96 = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST=10.194.1.43)(PROTOCOL=tcp)(PORT=1521))(CONNECT_DATA=(SID=CA02)))";
	$db_username_96 = "site96";
	$db_password_96 = "artemis";
	$conn_96 = new PDO($db_96,$db_username_96,$db_password_96);	
	if (!$conn_96) {
	   echo "Did not Connect";   
	}
	
	$db_39 = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST=10.194.1.43)(PROTOCOL=tcp)(PORT=1521))(CONNECT_DATA=(SID=CA02)))";
	$db_username_39 = "site39";
	$db_password_39 = "artemis";
	$conn_39 = new PDO($db_39,$db_username_39,$db_password_39);	
	if (!$conn_39) {
	   echo "Did not Connect";   
	}

?>