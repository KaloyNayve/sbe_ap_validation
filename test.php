<?php 
	//include 'db/dbCon.php';
	include 'db/functions.php';

	// $uname = 'cnayve1';
	// $qry = "SELECT document_type from ap_validation_users where lower(uname) = lower('{$uname}')";
	// $res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);

	// $document_type = $res['DOCUMENT_TYPE'];

	// echo $document_type;


	// $qry = "SELECT * FROM AP_VALIDATION where deleted is null and status = 'in flow' and document_type in ({$document_type})";
	// $res = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);

	// printArr($res);

	// function validateUser($username, $password) {
	// 	include 'db/dbCon.php';
	// 	$qry = "SELECT * FROM ap_validation_users ap
	// 		left outer join utilisateurs u
	// 		on ap.badge = u.numbadge
	// 		where upper(u.login) = upper(:username) and u.password = :password and ap.deleted is null";
	// 	$stmt = $conn->prepare($qry);
	// 	$stmt->execute([':username' => $username, ':password' => $password]);
	// 	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	// 	if (empty($result)) return false;
	// 	return true;
	// }
	// echo "hello world";
	// printArr(validateUser("cnayve", "Cjsn106433!1"));

	// function getSupplier2($sender, $domain) {
	// 	if ($sender === null ) return null;
	// 	$domain = strtolower($domain);
	// 	$sender = strtolower($sender);
	// 	include 'db/dbCon.php';
	// 	$qry = "SELECT * from ap_validation_suppliers where lower(email_address) like lower('%{$sender}%')";
	// 	$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	// 	if (empty($res)) {
	// 		#search for domain instead
	// 		if ($domain === null ) return null;
	// 		$qry = "SELECT SHORT_NAME FROM AP_VALIDATION_SUPPLIERS WHERE lower(DOMAIN) = '{$domain}'";
	// 		$res2 = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	// 		if (empty($res2)) return "";
	// 		return $res2['SHORT_NAME'];
	// 	} else {
	// 		return $res['SHORT_NAME'];
	// 	}
	// }

	// function determineDocuType($sender, $domain ) {
	// 	if ($sender === null ) return null;
	// 	$domain = strtolower($domain);
	// 	$sender = strtolower($sender);
	// 	include 'db/dbCon.php';
	// 	$qry = "SELECT * from ap_validation_suppliers where lower(email_address) like lower('%{$sender}%')";
	// 	$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	// 	if (empty($res)) {
	// 		#search for domain instead
	// 		if ($domain === null ) return null;
	// 		$qry = "SELECT * FROM AP_VALIDATION_SUPPLIERS WHERE lower(DOMAIN) = '{$domain}'";
	// 		$res2 = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	// 		if (empty($res2)) return "";
	// 		return $res2['DOCUMENT_TYPE'];
	// 	} else {
	// 		return $res['DOCUMENT_TYPE'];
	// 	}
	// }

	// $qry = "SELECT * FROM AP_VALIDATION WHERE STATUS = 'in flow'";
	// $data = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);

	// foreach ($data as $key => $value) {
	// 	$docuType = determineDocuType($value['SENDER_EMAIL'], $value['DOMAIN']);
	// 	echo "<br> <b>Document Type</b> is: {$docuType} <b>{$value['DOCUMENT_TYPE']}</b> {$value['SUPPLIER']} <b>Sender email</b>: {$value['SENDER_EMAIL']} <b>Domain</b>: {$value['DOMAIN']} <b>ID: </b> {$value['ID']}";
	// }
	// set_time_limit(0);
	// phpinfo();

	// function errorNotification($email, $location) {
	// 	require_once  'db/MailPack/PHPMailer/PHPMailerAutoload.php';
	// 	require 'db/MailPack/phpCredential.php';
	// 	$datein = date("Y-m-d"); 
	// 	$timein	= date("G:i:s");
	// 	$mail->addAddress($email);
	// 	$mail->setFrom("notification@sbe-ltd.ca", "AP Validation");
	// 	$mail->Subject = "Error Notification: {$datein} {$timein}";
	// 	$mail->Body = "<h1>SQL error in AP Validation portal has occured consult error logs</h1><p>Location: {$location}</p>";
	// 	$mail->send();
	// }

	// errorNotification("cnayve@sbe-ltd.ca");

	// $supplier = "";

	// echo $supplier == null ? "Supplier is empty" : "Supplier not empty";
	// function factorial( $n ) {

	// 	// Base case
	// 	if ( $n == 0 ) {
	// 	  echo "Base case: $n = 0. Returning 1...<br>";
	// 	  return 1;
	// 	}
	  
	// 	// Recursion
	// 	echo "$n = $n: Computing $n * factorial( " . ($n-1) . " )...<br>";
	// 	$result = ( $n * factorial( $n-1 ) );
	// 	echo "Result of $n * factorial( " . ($n-1) . " ) = $result. Returning $result...<br>";
	// 	return $result;
	//   }
	  
	//   echo "The factorial of 5 is: " . factorial( 5 );

	//   $file = "documents_to_be_received/test.pdf";

	//   if (file_exists($file)) {
	// 	 unlink($file);
	//   } else {
	// 	  echo "file does not exists";
	//   }

	// require_once 'class/DBConnection.php';

	// $conn = new DBConnection();

	// $qry = "SELECT * FROM AP_VALIDATION";
	// $data = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);
	
	// printArr($data);

	// class BaseClass {
	// 	function __construct() {
	// 		print "In BaseClass constructor\n";
	// 	}
	// }
	
	// class SubClass extends BaseClass {
	// 	function __construct() {
	// 		// parent::__construct();
	// 		print "In SubClass constructor\n";
	// 	}
	// }
	
	// class OtherSubClass extends BaseClass {
	// 	// inherits BaseClass's constructor
	// }
	
	// // In BaseClass constructor
	// $obj = new BaseClass();
	
	// // In BaseClass constructor
	// // In SubClass constructor
	// $obj = new SubClass();
	
	// // In BaseClass constructor
	// $obj = new OtherSubClass();
	class foo {
		const test = 'foobar!';
	}
	
	echo foo::test;