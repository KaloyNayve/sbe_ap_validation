<?php
session_start();
//require_once('db/MailPack/phpmailer/PHPMailerAutoload.php');
require 'dbCon.php';

/*
	$username = username
	$password = password
	$portal_name = name of portal to set the session variables
	$url = url to where to redirect after logging in
*/
function login($username, $password,$portal_name, $url) {
	require 'dbCon.php';
	// set login query
	$qry = "SELECT * FROM UTILISATEURS 
			WHERE upper(login) = upper('$username') and password = '$password'
			and numbadge in ('100622', '106547', '100078', '100630', '104888', '106433')";
	$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	// check result
	if (empty($res)) {
		
		echo "<script type=\"text/javascript\">
				window.location.href='login.php?user_login=false'; 
			</script>";
	} else {
		// get user information object
		$userObject =  getInformationOfUser($username);
		
		if ($userObject) {
		
			$_SESSION[$portal_name] = serialize($userObject);
							
			header("Location: $url");
		} else {
			echo "<script type=\"text/javascript\">
				window.location.href='login.php?user_login=false';
			</script>";
		}
	}


}



// good in working condition
function getInformationOfUser($user){
	require 'dbCon.php';
	require 'class/user.php';
	
	date_default_timezone_set('US/Eastern');	
	$curr_date 	= date("Y/m/d"); 
	$yestr_date = date('Y/m/d',strtotime("-1 days"));
	
	$query = "SELECT distinct * from (select u.*,x.* from utilisateurs u
			left outer join  ( select * from ( select d.* , case			
				when badge in ('100232','100480','100021') then 'claim'			
				
				when badge in ('100502','100078','103864','100826','106434','106433') then 'admin'
				else role end as Permission,
			'' as holiday_access
			from dir_indir d ) where  date_ins =(SELECT MAX(Date_Ins) FROM Dir_Indir)) x on u.numbadge= x.badge
			where x.Permission is not null and upper(u.login) =upper('$user'))";
	
	$res = $conn->query($query)->fetch(PDO::FETCH_ASSOC);
	if(!empty($res)){
        $userObject = new User($res['FIRST_NAME'],$res['LAST_NAME'],$res['ACCOUNT'],$res['PERMISSION'],$res['PROCESS_LINE'],$user,$res['NUMBADGE'],$res['HOLIDAY_ACCESS']);
		
		return $userObject;
	}
}

function sendMsg($msg,$status,$page){
	echo "<form action='".$page."' method='post' id='statusForm'>
				<input type='hidden' name='msg' value='".$msg."' />
				<input type='hidden' name='status' value='".$status."' />
			</form>";
	echo "<script>
			document.getElementById('statusForm').submit();
		</script>";
}

function executeStatement($queryByCusto, $date_from, $date_to, $connect_qry) {
	require 'dbCon.php';
	if($connect_qry =='96'){
		$connection = $conn_96;
	}else{
		$connection = $conn;
	}
	$stmt = $connection->prepare($queryByCusto);
	$stmt->bindValue(":date_from", $date_from, PDO::PARAM_STR);
	$stmt->bindValue(":date_to", $date_to, PDO::PARAM_STR);
	$stmt->execute();
	return $stmt;
} 

function createColumnsArray($end_column, $first_letters = '')
{
  $columns = array();
  $length = strlen($end_column);
  $letters = range('A', 'Z');

  // Iterate over 26 letters.
  foreach ($letters as $letter) {
      // Paste the $first_letters before the next.
      $column = $first_letters . $letter;

      // Add the column to the final array.
      $columns[] = $column;

      // If it was the end column that was added, return the columns.
      if ($column == $end_column)
          return $columns;
  }

  // Add the column children.
  foreach ($columns as $column) {
      // Don't itterate if the $end_column was already set in a previous itteration.
      // Stop iterating if you've reached the maximum character length.
      if (!in_array($end_column, $columns) && strlen($column) < $length) {
          $new_columns = createColumnsArray($end_column, $column);
          // Merge the new columns which were created with the final columns array.
          $columns = array_merge($columns, $new_columns);
      }
  }

  return $columns;
}

function generateMultipleExcel($data,$reportname){
require 'dbCon.php';	

$alpha_arr = createColumnsArray('DZ');
	$index = 0;
	foreach($data as $d){

	$qry =$d['qry'];
	$sheetname =$d['sheetname'];
	$dataArray = $conn->query($qry)->fetchall(PDO::FETCH_NUM);
		// $dataArray1 = $conn->query($sqlreport1)->fetchall(PDO::FETCH_NUM);
		// print_r($dataArray);
		// exit();
		// $dataArray = $conn->query($sqlqry)->fetchAll(PDO::FETCH_NUM);
	$styleArray = array(
					'font'  => array(
									'bold'  => true,
								),
					'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'ccebff')
								),
							);


		if(isset($dataArray) && count($dataArray) > 0){
			$columns = array_keys($conn->query($qry)->fetch(PDO::FETCH_ASSOC));
		
			// print_r($dataArray);
		
			if($index == 0){
			$objPHPExcel = new PHPExcel();
		
			}else{
			$objPHPExcel->createSheet();
			}
			$objPHPExcel->setActiveSheetIndex($index);
			$objPHPExcel->getActiveSheet()->setTitle($sheetname);
			
			$styleArray = array(
					'font'  => array(
									'bold'  => true,
								),
					'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'ccebff')
								)
			);
			
			for($i=0;$i<sizeof($columns);$i++){
				//$a = $i+1;
			//	echo $alpha_arr[$i]."1"."----".$columns[$i]."<br>";
				$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[$i]."1", $columns[$i]);
				$objPHPExcel->getActiveSheet()->getStyle($alpha_arr[$i]."1")->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_arr[$i])->setAutoSize(true);
			}
			$a= 2;
			for($i=0;$i<sizeof($dataArray);$i++){
				for($j=0;$j<sizeof($dataArray[$i]);$j++){
					//$formattedString = mb_strtolower($dataArray[$i][$j]);
					$formatted_value = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($dataArray[$i][$j]));
				//	$formatted_value = iconv('ISO-8859-1', 'UTF-8//IGNORE', $formattedString);
				//	echo $alpha_arr[$j].$a."------".$formatted_value."<br/>";
					$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[$j].$a, $formatted_value);
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alpha_arr[$j].$a, $formatted_value,PHPExcel_Cell_DataType::TYPE_STRING);
				}
				$a = $a +1;
			}
			
		}else{
			if($index == 0){
			$objPHPExcel = new PHPExcel();
		
			}else{
			$objPHPExcel->createSheet();
			}
			$objPHPExcel->setActiveSheetIndex($index);
			$objPHPExcel->getActiveSheet()->setTitle($sheetname);
			$formatted_value = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode('No data in the report'));
			$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[0]."1",$formatted_value );
			$objPHPExcel->getActiveSheet()->getStyle($alpha_arr[0]."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_arr[0])->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($alpha_arr[0].'1', $formatted_value,PHPExcel_Cell_DataType::TYPE_STRING);
			
		}
		
		$index++;
		
	}			ob_end_clean();
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$reportname.'.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				
}

function printArr($arr){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

function getNextSeq($seqName){
	require 'dbCon.php';
	$qry = "SELECT {$seqName}.NEXTVAL from dual";
	$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	return $res['NEXTVAL'];
}	

function searchIfDataExists($tableName, $conditions, $connection){
    /* Function that accepts two parameters tableName and conditions
    which checks if conditions exist in a given table */
    require 'dbCon.php';
    // Check which connection to use
    $connect = "";
    switch($connection) {
        case '94':  
            $connect = $conn;
        break;
        case '96':
            $connect = $conn_96;
        break;
        case '39':
            $connect = $conn_39;
        break;
    }
    $qry = "SELECT * FROM {$tableName} {$conditions}";   
      
    $res = $connect->query($qry)->fetchAll(PDO::FETCH_ASSOC);
    
    if(empty($res)){
        return false;
    } else {
        return $res;
    }
}


function generateMultipleExcel2($data,$reportname){
	require 'dbCon.php';
	$alpha_arr = createColumnsArray('DZ');
	$index = 0;
	foreach($data as $d){
	// Check which connection to use
    $connection = "";
    switch($d['connection']) {
        case '94':  
            $connection = $conn;
        break;
        case '96':
            $connection = $conn_96;
        break;
        case '39':
            $connection = $conn_39;
        break;
    }	
	$qry =$d['qry'];
	$sheetname =$d['sheetname'];
	$dataArray = $connection->query($qry)->fetchall(PDO::FETCH_NUM);		
	$styleArray = array(
					'font'  => array(
									'bold'  => true,
								),
					'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'ccebff')
								),
							);


		if(isset($dataArray) && count($dataArray) > 0){
			$columns = array_keys($connection->query($qry)->fetch(PDO::FETCH_ASSOC));
		
			// print_r($dataArray);
		
			if($index == 0){
			$objPHPExcel = new PHPExcel();
		
			}else{
			$objPHPExcel->createSheet();
			}
			$objPHPExcel->setActiveSheetIndex($index);
			$objPHPExcel->getActiveSheet()->setTitle($sheetname);
			
			$styleArray = array(
					'font'  => array(
									'bold'  => true,
								),
					'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'ccebff')
								)
			);
			
			for($i=0;$i<sizeof($columns);$i++){
				//$a = $i+1;
			//	echo $alpha_arr[$i]."1"."----".$columns[$i]."<br>";
				$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[$i]."1", $columns[$i]);
				$objPHPExcel->getActiveSheet()->getStyle($alpha_arr[$i]."1")->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_arr[$i])->setAutoSize(true);
			}
			$a= 2;
			for($i=0;$i<sizeof($dataArray);$i++){
				for($j=0;$j<sizeof($dataArray[$i]);$j++){
					//$formattedString = mb_strtolower($dataArray[$i][$j]);
					$formatted_value = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($dataArray[$i][$j]));
				//	$formatted_value = iconv('ISO-8859-1', 'UTF-8//IGNORE', $formattedString);
				//	echo $alpha_arr[$j].$a."------".$formatted_value."<br/>";
					$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[$j].$a, $formatted_value);
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($alpha_arr[$j].$a, $formatted_value,PHPExcel_Cell_DataType::TYPE_STRING);
				}
				$a = $a +1;
			}
			
		}else{
			if($index == 0){
			$objPHPExcel = new PHPExcel();
		
			}else{
			$objPHPExcel->createSheet();
			}
			$objPHPExcel->setActiveSheetIndex($index);
			$objPHPExcel->getActiveSheet()->setTitle($sheetname);
			$formatted_value = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode('No data in the report'));
			$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[0]."1",$formatted_value );
			$objPHPExcel->getActiveSheet()->getStyle($alpha_arr[0]."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_arr[0])->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($alpha_arr[0].'1', $formatted_value,PHPExcel_Cell_DataType::TYPE_STRING);
			
		}
		
		$index++;
		
	}			ob_end_clean();
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$reportname.'.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				
}

	function generateExcelForReports($data,$reportName, $savePath){
    	/*
    	  generates excel file for reports by saving it 
    	  in the savePath argument
		  it takes in $data Array where you can specify how many tabs
		  are required
		  as well as query, sheetname and connection in which the data
		  lives in.
		  and Save path where you want to save your excel report 

    	 */

		require 'dbCon.php';
		$today_date = date("Y-m-d");
		$alpha_arr = createColumnsArray('DZ');
		$index = 0;
		foreach($data as $d){
		// Check which connection to use
	    $connection = "";
	    switch($d['connection']) {
	        case '94':  
	            $connection = $conn;
	        break;
	        case '96':
	            $connection = $conn_96;
	        break;
	        case '39':
	            $connection = $conn_39;
	        break;
	    }	
		$qry =$d['qry'];
		$sheetname =$d['sheetname'];
		$dataArray = $connection->query($qry)->fetchall(PDO::FETCH_NUM);		
		$styleArray = array(
						'font'  => array(
										'bold'  => true,
									),
						'fill' => array(
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => 'ccebff')
									),
								);


			if(isset($dataArray) && count($dataArray) > 0){
				$columns = array_keys($connection->query($qry)->fetch(PDO::FETCH_ASSOC));
			
				// print_r($dataArray);
			
				if($index == 0){
				$objPHPExcel = new PHPExcel();
			
				}else{
				$objPHPExcel->createSheet();
				}
				$objPHPExcel->setActiveSheetIndex($index);
				$objPHPExcel->getActiveSheet()->setTitle($sheetname);
				
				$styleArray = array(
						'font'  => array(
										'bold'  => true,
									),
						'fill' => array(
										'type' => PHPExcel_Style_Fill::FILL_SOLID,
										'color' => array('rgb' => 'ccebff')
									)
				);
				
				for($i=0;$i<sizeof($columns);$i++){
					//$a = $i+1;
				//	echo $alpha_arr[$i]."1"."----".$columns[$i]."<br>";
					$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[$i]."1", $columns[$i]);
					$objPHPExcel->getActiveSheet()->getStyle($alpha_arr[$i]."1")->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_arr[$i])->setAutoSize(true);
				}
				$a= 2;
				for($i=0;$i<sizeof($dataArray);$i++){
					for($j=0;$j<sizeof($dataArray[$i]);$j++){
						//$formattedString = mb_strtolower($dataArray[$i][$j]);
						$formatted_value = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($dataArray[$i][$j]));
					//	$formatted_value = iconv('ISO-8859-1', 'UTF-8//IGNORE', $formattedString);
					//	echo $alpha_arr[$j].$a."------".$formatted_value."<br/>";
						$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[$j].$a, $formatted_value);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit($alpha_arr[$j].$a, $formatted_value,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$a = $a +1;
				}
				
			}else{
				if($index == 0){
				$objPHPExcel = new PHPExcel();
			
				}else{
				$objPHPExcel->createSheet();
				}
				$objPHPExcel->setActiveSheetIndex($index);
				$objPHPExcel->getActiveSheet()->setTitle($sheetname);
				$formatted_value = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode('No data in the report'));
				$objPHPExcel->getActiveSheet()->setCellValue($alpha_arr[0]."1",$formatted_value );
				$objPHPExcel->getActiveSheet()->getStyle($alpha_arr[0]."1")->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getColumnDimension($alpha_arr[0])->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit($alpha_arr[0].'1', $formatted_value,PHPExcel_Cell_DataType::TYPE_STRING);
				
			}
			
			$index++;
			
		}			ob_end_clean();
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');	
					$path = $savePath . $reportName . '-' . $today_date . '.xls';
					$objWriter->save($path);
					
	}


	function sendFileThroughEmail($file, $sendeeArray, $ccArray ,$emailArray) {
		/*
    	  function that uses PhpMailer to send a file through email
    	  It takes in 4 parameters
    	  $file - Absolute path of file to send
		  $sendeeArray - Array of email address that you want to send
		  the email to.
		  $ccArray - Array of email that you want to cc to.
		  $emailArray - needs 4 things
		  	$emailArray['sentFromAddress'] = Email of sender
		  	$emailArray['sentFromName']	= Name of sender
		  	$emailArray['emailSubject'] = Subject of email
		  	$emailArray['emailBody'] = Body of email
		  Returns true (could not find a way to confirm if email is sent)
    	 */
		require_once  'MailPack/PHPMailer/PHPMailerAutoload.php';
		require 'MailPack/phpCredential.php';
		$mail->AddAttachment($file);
		foreach ($sendeeArray as $email) {
			$mail->addAddress($email);
		}			
		foreach ($ccArray as $cc){
			$mail->AddCC($cc);	
		}		
		$mail->setFrom($emailArray['sentFromAddress'], $emailArray['sentFromName']);
		$mail->Subject = $emailArray['emailSubject'];
		$mail->Body    = $emailArray['emailBody'];
		if ($mail->send()) {
			return true;
		} else {
			return false;
		}

	}


	function sendEmail($sendeeArray, $ccArray ,$emailArray) {
		/*
    	  function that uses PhpMailer to send a file through email
    	  It takes in 4 parameters
    	  $file - Absolute path of file to send
		  $sendeeArray - Array of email address that you want to send
		  the email to.
		  $ccArray - Array of email that you want to cc to.
		  $emailArray - needs 4 things
		  	$emailArray['sentFromAddress'] = Email of sender
		  	$emailArray['sentFromName']	= Name of sender
		  	$emailArray['emailSubject'] = Subject of email
		  	$emailArray['emailBody'] = Body of email
		  Returns true (could not find a way to confirm if email is sent)
    	 */
		require_once  'MailPack/PHPMailer/PHPMailerAutoload.php';
		require 'MailPack/phpCredential.php';
		
		foreach ($sendeeArray as $email) {
			$mail->addAddress($email);
		}			
		if (!empty($ccArray)) {
			foreach ($ccArray as $cc){
				$mail->AddCC($cc);	
			}
		}		
		$mail->setFrom($emailArray['sentFromAddress'], $emailArray['sentFromName']);
		$mail->Subject = $emailArray['emailSubject'];
		$mail->Body    = $emailArray['emailBody'];
		if ($mail->send()) {
			return true;
		} else {
			return false;
		}

	}

	function dayCheck($date, $dayInteger) {
		/* Function that takes in $date = the date you wanna check and $dayInteger, Numeric representation of the day of the week (1 for monday through 7 for sundays) */

	    return (date('N', strtotime($date)) == $dayInteger);
	}

	/*
		getDocuments 
		description: function that gets documents from ap_automation table by
		status
	*/
    function getDocuments($status) {
    	include 'dbCon.php';
    	$qry = "SELECT *
				FROM AP_VALIDATION 
				WHERE DELETED is null
				AND STATUS = '{$status}'
				order by date_creation desc";

    	$res = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);
    	if (!empty($res)) {
    		return $res;
    	} else {
    		return false;
    	}    	
    }


    // Write to error logs
	// $logfile = location of the file
	// $data = array of error
	// keys = portal_name, uname, date, time, query(failed query)
	function writeErrorLogs($logfile, $data) {
		$myfile = fopen($logfile, "a") or die("Unable to open file!");
		$txt = "-----------------------------------";
		$txt .= "\n portal: {$data['portal_name']}";
		$txt .= "\n username: {$data['uname']}";
		$txt .= "\n date: {$data['date']} time: {$data['time']}";
		$txt .= "\n query: {$data['query']}";
		$txt .= "\n -----------------------------------";
		fwrite($myfile, "\n". $txt);
		fclose($myfile);
	}


	/* Getting document count */

	function getDocumentCount($status) {
		include 'dbCon.php';
    	$qry = "SELECT count(*) as count
				FROM AP_VALIDATION 
				WHERE DELETED is null
				AND STATUS = '{$status}'";
		$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
		if (empty($res)) {
			return "error";
		} else {
			return $res['COUNT'];
		}
	}

	function getDocumentType($uname) {
		$uname = strtoupper($uname);
		// switch statement to determine document type scope by username
		switch ($uname) {
			case 'DTONDEREAU':
				$invoice_type = ["Expense Reimbursement"];
				break;

			case 'OTOLUAKANDE':
				$invoice_type = ["OEM", "Shop Supplies", "Freight and Courier"];
				break;

			case 'AJANDYAL':
				$invoice_type = ["OEM", "Shop Supplies", "Freight and Courier"];
				break;

			case 'MDEVABATTINI':
				$invoice_type = ["OEM", "Shop Supplies", "Freight and Courier"];
				break;	

			case 'MDEVABATTINI':
				$invoice_type = ["OEM", "Shop Supplies", "Freight and Courier"];
				break;	

			case 'CCONNOLLY2':
				$invoice_type = ["Payroll and Benefits"];
				break;	

			case 'MPOON':
				$invoice_type = ["Benefits"];
				break;	

			case 'CNAYVE':
				$invoice_type = [
					"Expense Reimbursement",
					"Benefits", 
					"OEM", 
					"Shop Supplies", 
					"Freight and Courier",
					"Payroll and Benefits" 
				];
				break;	
			
			default:
				$invoice_type = ["Invalid user"];
				break;
		}


		$type = "";
		// turn array into a string
		$ctr = 0;
		foreach ($invoice_type as $value) {
			if ($ctr === 0) {
				$type .= "'{$value}'";
			} else {
				$type .= ",'{$value}'";
			}
			$ctr++;
		}
		return $type;
	}

	/* Getting  my document count */

	function getMyDocumentCount($uname) {
		include 'dbCon.php';
		$type = getDocumentType($uname);

    	$qry = "SELECT count(*) as count
				FROM AP_VALIDATION 
				WHERE DELETED is null
				AND STATUS = 'in flow'
				AND DOCUMENT_TYPE in ({$type})";
		$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
		if (empty($res)) {
			return "error";
		} else {
			return $res['COUNT'];
		}
		
	}

	/* Getting  my documents */
	function getMyDocuments($uname) {
		include 'dbCon.php';
		// Get document type scope by username
		$type = getDocumentType($uname);
		$qry = "SELECT * FROM AP_VALIDATION
				WHERE DELETED IS null
				AND STATUS = 'in flow'
				AND DOCUMENT_TYPE in ({$type})";
		$res = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);
		if (empty($res)) {
			return false;
		} else {
			return $res;
		}
	} 

	// Determine email to send notifications by document type
	function getEmailFromDocumentType($document_type) {
		switch ($document_type) {
			case 'Payroll and Benefits':
				return 'cconnolly@sbe-ltd.ca';
				break;
			
			case 'Benefits':
				return 'mpoon@sbe-ltd.ca';
				break;

			case 'Expense Reimbursement':
				return 'dt@sbe-ltd.ca';
				break;
		}
	}

	// Send email notification to corresponding person when receiving document that there is a invoice waiting to be validated.

	function sendEmailNotification($data) {
		require_once  'MailPack/PHPMailer/PHPMailerAutoload.php';
		require 'MailPack/phpCredential.php';
		// Determine email address to send to by document type
		$email_address = getEmailFromDocumentType($data['DOCUMENT_TYPE']);
		$mail->addAddress($email_address);
		$mail->AddCC('cnayve@sbe-ltd.ca');
		$mail->setFrom('no-reply@sbe-ltd.ca', 'Ap Validation Portal');
		$mail->Subject = "Invoice awaiting validation";
		$mail->Body = "
			<h1>Hi, the following invoice is awaiting validation</h1>
			<p><b>Company: </b>{$data['COMPANY']}</p>
			<p><b>Document type: </b>{$data['DOCUMENT_TYPE']}</p>
			<p><b>Supplier: </b>{$data['SUPPLIER']}</p>		
			<p><b>Invoice Number: </b>{$data['INVOICE_NUMBER']}</p>
			<p><b>Invoice Date: </b>{$data['INVOICE_DATE']}</p>
			<p>Click <a href='https://portal-ca.sbe-ltd.ca/sbe_ap_validation/my_documents.php'>here</a> to view all documents awaiting your validation</p>
			<br/><br/><br/>
				** Do not reply to this email as it is a system generated email";
		$mail->send();
		return true;

	}
	// Get document by id
	function getDocumentById($id) {
		include 'dbCon.php';
		$query = "SELECT * FROM AP_VALIDATION WHERE id = '{$id}'";
		return $conn->query($query)->fetch(PDO::FETCH_ASSOC);
	}

	function forwardDocumentThroughEmail($data) {
		
		require_once  'MailPack/PHPMailer/PHPMailerAutoload.php';
		require 'MailPack/phpCredential.php';
		$mail->AddAttachment($data["email_document_attached"]);

		$to = explode(" ", $data["email_address"]);
		foreach ($to as $email) {
			$mail->addAddress($email);
		}			
				
		$mail->setFrom('no-reply@sbe-ltd.ca', 'Ap Validation Portal');
		$mail->Subject = $data['email_subject'];
		$mail->Body = $data['email_body'];
		if ($mail->send()) {
			return true;
		} else {
			return false;
		}

	}

	// get notes/comment count

	function getNotesCount($id) {
		if (!$id) {
			return "0";
		}

		include 'dbCon.php';
		$qry = "SELECT count(*) as count
				from ap_validation_notes
				where ap_validation_id = '{$id}'
				AND DELETED is null";
		$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
		return $res['COUNT'];
	}

	// get attached docs count
	function getAttachedDocumentsCount($id) {
		if (!$id) {
			return "0";
		}

		include 'dbCon.php';
		$qry = "SELECT count(*) as count
				from ap_validation_attachments
				where ap_validation_id = '{$id}'
				AND DELETED IS NULL";
		$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
		return $res['COUNT'];
	}

	// get supplier name from domain
	function getSupplier($domain) {
		$domain = strtolower($domain);
		include 'dbCon.php';
		$qry = "SELECT SHORT_NAME FROM AP_VALIDATION_SUPPLIERS WHERE DOMAIN = '{$domain}'";
		$res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
		if (empty($res)) {
			return "";
		} else {
			return $res['SHORT_NAME'];
		}
	}


	function sendRemittanceEmail($data) {
		require_once  'MailPack/PHPMailer/PHPMailerAutoload.php';
		require 'MailPack/phpCredential.php';
		// Determine email address to send to by document type

		$invoice_number_arr = explode("| ", $data['C']);
		$invoice_number_table = "<div style='margin-left:5px;'></div><table >";
		foreach ($invoice_number_arr as $key => $value) {
			$invoice_number_table .= "<tr><td>{$value}</td></tr>";
		}
		$invoice_number_table .= "</table></div>";

		$invoice_date_arr = explode("| ", $data['D']);
		$invoice_date_table = "<table >";
		foreach ($invoice_date_arr as $key => $value) {
			$invoice_date_table .= "<tr><td>{$value}</td></tr>";
		}
		$invoice_date_table .= "</table>";

		$email_address = $data['B'];
		$mail->addAddress($email_address);
		// $mail->AddCC('cnayve@sbe-ltd.ca');
		$mail->setFrom('ap@sbe-ltd.ca', 'SBE ltd CA');
		$mail->Subject = "Payment Notification";
		$mail->Body = "
			<p>This is an automatically generated email; please do not reply to this email directly.</p>
			<p>Thank you for submitting your invoice to SBE Canada Ltd/SBE USA </p>	
			<p>The invoice(s) below have been paid. This is an advice of funds transfer only, not a notification of deposit which is typically reflected in your bank account within 2-3 business days. </p>			
			<br/>
			<ul style='padding-left:none;list-style:none;'>
				<li><strong>Po Number: </strong> {$data['A']}</li>
				<li><strong>Email Address: </strong> {$data['B']}</li>
				<li>
					<strong>Invoice Number: </strong><br/>
					{$invoice_number_table}
				</li>
				<li>
					<strong>Invoice Date: </strong><br/>
					{$invoice_date_table}
				</li>
				<li><strong>Shipping Amount: </strong> {$data['E']}</li>
				<li><strong>Tax Amount: </strong> {$data['F']}</li>
				<li><strong>Invoice Total: </strong> {$data['G']}</li>
				<li><strong>Currency: </strong> {$data['H']}</li>
			</ul>
			
			<br/>
			<p>If you have any further questions or concerns, please feel free to contact accounts payable at ap@sbe-ltd.ca.</p>
			<br/>
			<p>Best Regards, <br/>
			The SBE Payments Team</p>
			";
		$mail->send();
		return true;

	}

	