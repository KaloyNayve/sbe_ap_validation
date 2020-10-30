<?php 
	
	require_once 'db/functions.php';
	require_once 'class/queryBuilder.php';
	require_once 'class/DBConnection.php';
	require_once 'class/InvoiceDetailsExtractor.php';
	date_default_timezone_set('US/Eastern'); 
	// create connection
	$conn = new DBConnection();
	

	function imap_utf8_fix($string) {
		#Returns an UTF-8 encoded string.
		return iconv_mime_decode($string,0,"UTF-8");
	}

	function saveInvoiceItems2($supplier, $items, $id) {
		# manages supplier and save functions 
		// 1. check if supplier or items array is empty
		if($supplier == null || empty($items)) return false;
		// switch statement to manage the save functions per supplier
		switch (strtolower($supplier)) {
			case 'samsung':
				saveInvoiceItems_Samsung2($items, $id);
				return true;
				break;
			
			default:
				return false;
				break;
		}
	}

	function saveInvoiceItems_Samsung2($items, $id) {
		# loops through invoice items, adds the ap validation id then saves invoice items to db
		include 'db/dbCon.php';		
		if (empty($items) || $id === null) return null;

		foreach ($items as $key => $value) {
			$value['AP_VALIDATION_ID'] = $id;
			$value['ID'] = str_pad(getNextSeq("AP_VALIDATION_INVOICE_ITEMS_ID"), 9, "0", STR_PAD_LEFT);
			$qb2 = new QueryBuilder('CARLO_TEST2');
			$query = $qb2->insertDb($value);
			// echo $query;
			if(!$conn->query($query)) {
				# record query error on error logs
				$logfile = "D:\Portals\sbe_ap_validation\logs\\error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation - " . __FUNCTION__;
				$errorData['uname'] = "system";
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $query;
				writeErrorLogs($logfile, $errorData);
				// send error notification email to developer
				errorNotification("cnayve@sbe.ltd.ca", __FUNCTION__ , $query);
			}
			// $conn->query($query);
		} 
		
		return true;
	}

	// Get today's date
	// $date_today = date("j F Y");
    
    

	$date_today = "29 October 2020";
	// Email Credentials
	$hostname = '{mail.sbe-ltd.co.uk:993/imap/ssl}INBOX';
	$username = 'cnayve@sbe-ltd.ca';
	$password = 'Cjsn106433!';
	// $username = 'cnayve@sbe-ltd.ca';
	// $password = 'Carlo106433!';
	$inbox =imap_open($hostname,$username,$password) or die ('cannot connect to Mail '.imap_last_error());
	// Grab todays email
	$emails = imap_search($inbox,"ON \"$date_today\"");
	echo "Number of emails: " . count($emails) . "<br>";

	if ($emails) {
		$ctr = 0;
		# code...
		rsort($emails);
		foreach ($emails as $email_number) {
			/* get information specific to this email */
	        $overview = imap_fetch_overview($inbox,$email_number,0);

	        $message = imap_fetchbody($inbox,$email_number,2);

	        /* get mail structure */
	        $structure = imap_fetchstructure($inbox, $email_number);
			
			
	        $header = imap_headerinfo($inbox, $email_number);

			$sender = $header->from[0]->mailbox . "@" . $header->from[0]->host;

			$header = imap_header($inbox, $email_number);

			$from = $header->from;
			
			$print_sender = $from[0]->personal;
			// echo mb_detect_encoding($print_sender, 'auto') . "<br>";
			$print_check = mb_detect_encoding($print_sender, 'auto');

			$bodymsg = imap_qprint(imap_fetchbody($inbox, $email_number, 1.2));

			if (empty($bodymsg)) {
				$bodymsg = imap_qprint(imap_fetchbody($inbox, $email_number, 1));
			}

			if($print_check == "ISO-8859-9" ) {
				$print_sender = mb_convert_encoding($print_sender, "UTF-8", "ISO-8859-9");//Encoding process
				$bodymsg = mb_convert_encoding($bodymsg, "UTF-8", "ISO-8859-9");//Encoding process
			}
			echo "=========================================<br><br>";
			echo $sender . "<br>";
			$subject = imap_utf8_fix($overview[0]->subject);
			echo $subject . "<br>";
			echo $bodymsg . "<br><br>";
			
			if ( isset($overview[0]->subject) && !empty($overview[0]->subject) ) {
				echo $overview[0]->subject . "<br>";
				$subject = strtolower($overview[0]->subject);			

				// if subject of email si upload to ap validation portal, it means it should be extracted regardless of the domain
				if ($subject === "upload to ap validation portal" && $header->from[0]->host === 'sbe-ltd.ca') {
					$attachments = array();
					$attachmentName = array();
					
					// get attachments
					if(isset($structure->parts) && count($structure->parts)) 
					{
						for($i = 0; $i < count($structure->parts); $i++) 
						{
							$attachments[$i] = array(
								'is_attachment' => false,
								'filename' => '',
								'name' => '',
								'attachment' => ''
							);

							$attachmentName[$i] = array(
								'is_attachment' => false,
								'filename' => ''                    
							);

							if($structure->parts[$i]->ifdparameters) 
							{
								foreach($structure->parts[$i]->dparameters as $object) 
								{
									if(strtolower($object->attribute) == 'filename') 
									{
										$attachments[$i]['is_attachment'] = true;
										$attachments[$i]['filename'] = $object->value;

										$attachmentName[$i]['is_attachment'] = true;
										$attachmentName[$i]['filename'] = $object->value;
									}
								}
							}

							if($structure->parts[$i]->ifparameters) 
							{
								foreach($structure->parts[$i]->parameters as $object) 
								{
									if(strtolower($object->attribute) == 'name') 
									{
										$attachments[$i]['is_attachment'] = true;
										$attachments[$i]['name'] = $object->value;

										$attachmentName[$i]['is_attachment'] = true;
										$attachmentName[$i]['filename'] = $object->value;
									}
								}
							}

							if($attachments[$i]['is_attachment']) 
							{
								$attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

								/* 3 = BASE64 encoding */
								if($structure->parts[$i]->encoding == 3) 
								{ 
									$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
								}
								/* 4 = QUOTED-PRINTABLE encoding */
								elseif($structure->parts[$i]->encoding == 4) 
								{ 
									$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
								}
							}
						}
					}

					$data = [];
						
					$data['sender_name'] = $overview[0]->from;
					$data['sender_email'] = $sender;
					$data['domain'] = $header->from[0]->host;
					$data['sent_date'] = date("Y/m/d", strtotime($overview[0]->date));
					$data['sent_time'] = date("h:i:s", strtotime($overview[0]->date));
					$data['email_subject'] =  str_replace("'", "''", $overview[0]->subject);

					foreach ($attachments as $attachment) {
						
						if ($attachment['is_attachment'] == 1) {
							
							# get filename
							$filename = $attachment['filename'];
							// Get file ext
							$extension = strtolower(substr($filename,strrpos($filename,"."),(strlen($filename)-strrpos($filename,"."))));

							// $allowed_types = array('.pdf','.xls','.xlsx','.PDF', '.XLS','.XLSX');
							$allowed_types = array('.pdf','.PDF');
							// Only upload pdf/xls/xlsx files
							if (in_array($extension, $allowed_types)) {
								# check if file is already been saved
								$data = array_map("trim", $data);
								// check if filename has already been uploaded
                                // $check_qry = "SELECT * FROM AP_VALIDATION WHERE attached_document = '{$filename}'";
                                $check_qry = "SELECT * FROM CARLO_TEST WHERE attached_document = '{$filename}'";
								$check_res = $conn->query($check_qry)->fetchAll(PDO::FETCH_ASSOC);
								// printArr($attachmentName);
								if (empty($check_res)) {
									// get unique identifier as index
									$data['id'] = str_pad(getNextSeq("AP_VALIDATION_ID"), 9, "0", STR_PAD_LEFT);

									$data['attached_document'] = $filename;

									// save to document with id as username
									$document_folder = "documents_to_be_received";
									$document_path = $document_folder . "/" . $data['id'] . $extension;
									$data['document_path'] = $document_path;
									$data['RENAMED_ATTACHED_FILE'] = $data['id'] . $extension;
									$data['supplier'] = getSupplier($data['sender_email'], $data['domain']);                                    
                                    if ($data['sender_email'] === "cnayve@sbe-ltd.ca") {
                                        $data['supplier'] = "Samsung";
                                    }                                    
									$data = array_map("trim", $data);
									//actual path folder
									$actualpath = "D:\Portals\sbe_ap_validation\\";
									//if folder does not exist create folder
									if(!is_dir($actualpath . $document_folder))
									{
										mkdir($actualpath . $document_folder, 0777, true);
									}
									$actualFile = $actualpath .  $document_folder . "\\" . $data['id'] . $extension;									
									$document_file = fopen($actualFile, "w+");
									fwrite($document_file, $attachment['attachment']);
									fclose($document_file);
									if (file_exists($actualFile)) {	
										$insert_data = [];									
										
										// extract invoice information
										$extraction = new InvoiceDetailsExtractor($conn, $actualFile, $data['supplier']);
										$extracted_data = $extraction->extractData();
										// if extracted data not null, merge with insert data
										if ($extracted_data !== null)  $insert_data = array_merge($extracted_data['INVOICE_DETAILS'], $insert_data);
										

										// merge data and insert data
										$insert_data = array_merge($data, $insert_data);
										$qb = new QueryBuilder('CARLO_TEST');
										$query = $qb->insertDb($insert_data);
										echo "<br> data:";
										printArr($data);
										echo "<br> insert data:";
										printArr($insert_data);

										// printArr($attachmentName);

										echo "<b>invoice will be saved</b> <br/>";
										if ($conn->query($query)) {
											$ctr++;
											// save invoice items
											// if ($data['supplier'] === "Samsung" && !empty($extracted_data['INVOICE_ITEMS']) ) {
											// 	saveInvoiceItems_Samsung($extracted_data['INVOICE_ITEMS'], $data['id']);   
											// } 
											saveInvoiceItems2($data['supplier'], $extracted_data['INVOICE_ITEMS'], $data['id']);												                                               
											echo "<br> <b>Invoice: {$data['attached_document']}  is  Saved</b>";
										} else {
											echo "<b>Invoice not saved, consult error logs: {$data['attached_document']}</b> <br>";
											$logfile = "logs/error_logs.txt";
											$errorData = [];
											$errorData['portal_name'] = "ap_validation - email extraction";
											$errorData['uname'] = "system";
											$errorData['date'] = date("Y/m/d");
											$errorData['time'] = date("G:i:s");
											$errorData['query'] = $query;
											writeErrorLogs($logfile, $errorData);
											// send error notification email to developer
											errorNotification("cnayve@sbe-ltd.ca", "Email extractor - Manual Upload mode", $query);
										}

									}
									
								} else {
									// printArr($data);

									// printArr($attachmentName);
									echo "<b>already saved</b>" . "<br><br><br>";
								}

							}

						}
					}

				}
			}
	        // ignore emails from sbe-ltd.ca domain
	        if ($header->from[0]->host != 'sbe-ltd.ca') {
				$attachments = array();
	       		$attachmentName = array();

	        	if ($sender != 'productionreport@sbe-ltd.co.uk') {
					# code...
					echo $sender . "<br>";
					
					// echo $message . "<br>";
	        		if(isset($structure->parts) && count($structure->parts)) 
			        {
			            for($i = 0; $i < count($structure->parts); $i++) 
			            {
			                $attachments[$i] = array(
			                    'is_attachment' => false,
			                    'filename' => '',
			                    'name' => '',
			                    'attachment' => ''
			                );

			                $attachmentName[$i] = array(
			                    'is_attachment' => false,
			                    'filename' => ''                    
			                );

			                if($structure->parts[$i]->ifdparameters) 
			                {
			                    foreach($structure->parts[$i]->dparameters as $object) 
			                    {
			                        if(strtolower($object->attribute) == 'filename') 
			                        {
			                            $attachments[$i]['is_attachment'] = true;
			                            $attachments[$i]['filename'] = imap_utf8_fix($object->value);

			                            $attachmentName[$i]['is_attachment'] = true;
			                            $attachmentName[$i]['filename'] = imap_utf8_fix($object->value);
			                        }
			                    }
			                }

			                if($structure->parts[$i]->ifparameters) 
			                {
			                    foreach($structure->parts[$i]->parameters as $object) 
			                    {
			                        if(strtolower($object->attribute) == 'name') 
			                        {
			                            $attachments[$i]['is_attachment'] = true;
			                            $attachments[$i]['name'] = imap_utf8_fix($object->value);

			                            $attachmentName[$i]['is_attachment'] = true;
			                            $attachmentName[$i]['filename'] = imap_utf8_fix($object->value);
			                        }
			                    }
			                }

			                if($attachments[$i]['is_attachment']) 
			                {
			                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

			                    /* 3 = BASE64 encoding */
			                    if($structure->parts[$i]->encoding == 3) 
			                    { 
			                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
			                    }
			                    /* 4 = QUOTED-PRINTABLE encoding */
			                    elseif($structure->parts[$i]->encoding == 4) 
			                    { 
			                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
			                    }
			                }
			            }
			        }

			        $data = [];
					
					$data['sender_name'] = trim($overview[0]->from);
					$data['sender_email'] = trim($sender);
					$data['domain'] = trim($header->from[0]->host);
					$data['sent_date'] = date("Y/m/d", strtotime($overview[0]->date));
					$data['sent_time'] = date("h:i:s", strtotime($overview[0]->date));

					if ( isset($overview[0]->subject) && !empty($overview[0]->subject) ) {
						$data['email_subject'] = imap_utf8_fix(trim(str_replace("'", "''", $overview[0]->subject)));
					} else {
						$data['email_subject'] = ""; 
					}

					


					printArr($data);

				printArr($attachmentName);
				

				foreach ($attachments as $attachment) {
					
					if ($attachment['is_attachment'] == 1) {
						
						# get filename
						$filename = trim($attachment['filename']);
						// Get file ext
						$extension = strtolower(substr($filename,strrpos($filename,"."),(strlen($filename)-strrpos($filename,"."))));

                        // $allowed_types = array('.pdf','.xls','.xlsx','.PDF', '.XLS','.XLSX');
                        $allowed_types = array('.pdf','.PDF');
						// Only upload pdf/xls/xlsx files
						if (in_array($extension, $allowed_types)) {
							# check if file is already been saved

							$data = array_map("trim", $data);

							printArr($data);

							printArr($attachmentName);

							$check_qry = "SELECT * FROM AP_VALIDATION WHERE email_subject = '{$data['email_subject']}' and attached_document = '{$filename}' and sender_email = '{$data['sender_email']}' and sent_date = '{$data['sent_date']}' and sent_time = '{$data['sent_time']}'";
							$check_res = $conn->query($check_qry)->fetchAll(PDO::FETCH_ASSOC);
							
							// echo $check_qry . "<br>";
							// printArr($data);

							// printArr($attachmentName);

	                		if (empty($check_res)) {

								echo "Invoice is going to be saved <br>";
	                			// get unique identifier as index
					            $data['id'] = str_pad(getNextSeq("AP_VALIDATION_ID"), 9, "0", STR_PAD_LEFT);

					            $data['attached_document'] = $filename;

				                // save to document with id as username
				                $document_folder = "documents_to_be_received";

				                $document_path = $document_folder . "/" . $data['id'] . $extension;
				                

				                

				                $data['document_path'] = $document_path;
				                $data['RENAMED_ATTACHED_FILE'] = $data['id'] . $extension;
				                $data['supplier'] = getSupplier($data['sender_email'], $data['domain']);
				                
				                $data = array_map("trim", $data);

				                //actual path folder
				                $actualpath = "D:\Portals\sbe_ap_validation\\";
				                //if folder does not exist create folder
				                if(!is_dir($actualpath . $document_folder))
				                {
				                     mkdir($actualpath . $document_folder, 0777, true);
				                }

				                $actualFile = $actualpath .  $document_folder . "\\" . $data['id'] . $extension;
				                
				                $document_file = fopen($actualFile, "w+");
				                fwrite($document_file, $attachment['attachment']);
				                fclose($document_file);

				                if (file_exists($actualFile)) {	
				                	/* Extract Information on invoice*/
				                	if (strtolower($data['supplier']) === 'samsung') {				                		
				                		$extracted_data = ProcessSamsungInvoice($actualFile);
				                		$data = array_merge($extracted_data, $data);
				                	}

				                	$qb = new QueryBuilder('AP_VALIDATION');
									$query = $qb->insertDb($data);

									echo "invoice will be saved <br>";

									// printArr($data);

									// printArr($attachmentName);

									// if ($conn->query($query)) {
									// 	$ctr++;
										
									// 	echo "saved <br>";
									// } else {
									// 	$logfile = "logs/error_logs.txt";
									// 	$errorData = [];
									// 	$errorData['portal_name'] = "ap_validation - email extraction";
									// 	$errorData['uname'] = "system";
									// 	$errorData['date'] = date("Y/m/d");
									// 	$errorData['time'] = date("G:i:s");
									// 	$errorData['query'] = $query;
									// 	writeErrorLogs($logfile, $errorData);
									// }

				                }
				                
	                		} else {
								// printArr($data);

								// printArr($attachmentName);
	                			echo "already saved" . "<br>";
	                		}

						}

					}
				}

	        	} 
	        }        

			
		}

		echo "insert {$ctr}";
	}

	/* close the connection */
	imap_close($inbox);


