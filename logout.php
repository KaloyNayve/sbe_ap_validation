<?php 
	session_start();
	// To set session name
	$portal_name = "sbe_ap_validation";

	if (isset($_SESSION[$portal_name])) {
		unset($_SESSION[$portal_name]);
		header("location:login.php");
	}
	
 ?>