<?php
	// set execution time to unlimited
	set_time_limit(0);
	
	require_once 'db/functions.php';
	require_once 'class/queryBuilder.php';
	require_once 'class/DBConnection.php';
	require_once 'class/InvoiceDetailsExtractor.php';

	// create connection
	$conn = new DBConnection();
	
	date_default_timezone_set('US/Eastern'); 
	function imap_utf8_fix($string) {
		#decodes an UTF-8 encoded string.
		return iconv_mime_decode($string,0,"UTF-8");
	}
	$time = date("G:i:s");
	echo "<b>{$time}</b> \n";
	// Get today's date
	$date_today = date("j F Y");
	// $date_today = "4 June 2020";
	// Email Credentials
	$hostname = '{mail.sbe-ltd.co.uk:993/imap/ssl}INBOX';
	$username = 'ap_validation@sbe-ltd.ca';
	$password = '2hBPqb3DwnYR';
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
			echo "<b>================================================</b><br><br><br>";
			echo $sender . "<br>";			
			# Start of email uploads
			if ( isset($overview[0]->subject) && !empty($overview[0]->subject) ) {
				echo imap_utf8_fix($overview[0]->subject) . "<br><br>";
				$subject = imap_utf8_fix($overview[0]->subject);
				$subject = strtolower($subject);
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
					// instantiate initial invoice data
					$data = [];						
					$data['sender_name'] = $overview[0]->from;
					$data['sender_email'] = $sender;
					$data['domain'] = $header->from[0]->host;
					$data['sent_date'] = date("Y/m/d", strtotime($overview[0]->date));
					$data['sent_time'] = date("h:i:s", strtotime($overview[0]->date));
					$data['email_subject'] =  str_replace("'", "''", $subject);
					if (!empty($attachmentName)) {
						echo "<br>Attachments: <br>";
						printArr($attachmentName);
					};					
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
								$check_qry = "SELECT * FROM AP_VALIDATION WHERE attached_document = '{$filename}'";
								$check_res = $conn->query($check_qry)->fetchAll(PDO::FETCH_ASSOC);
								
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
										// Instantiate insert data array
										$insert_data = [];
										// extract invoice information
										$extraction = new InvoiceDetailsExtractor($conn, $actualFile, $data['supplier']);
										$extracted_data = $extraction->extractData();										
										// if extracted data not null, merge with insert data
										if ($extracted_data !== null)  $insert_data = array_merge($extracted_data['INVOICE_DETAILS'], $insert_data);
										//merge inserted data with initial data
										$insert_data = array_merge($data, $insert_data);
										$qb = new QueryBuilder('AP_VALIDATION');
										$query = $qb->insertDb($insert_data);										
										// printArr($attachmentName);
										if ($conn->query($query)) {
											$ctr++;
											saveInvoiceItems($data['supplier'], $extracted_data['INVOICE_ITEMS'], $data['id']);
											echo "<br>Inserted data:<br>";
											printArr($insert_data); 
											echo "<b>Saved: {$data['attached_document']}</b> <br>";
										} else {
											echo "<b>Invoice not saved, consult error logs: {$data['attached_document']}</b> <br>";
											// $logfile = "logs/error_logs.txt";
											$logfile = "D:\Portals\sbe_ap_validation\logs\\error_logs.txt";
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
									echo "<br><b>Data already saved:</b><br>";
									printArr($data);									
									echo "<b>already saved</b>" . "<br><br>";
								}
							}
						}
					}
				}
			} #end of email upload
	        // ignore emails from sbe-ltd.ca domain
	        if ($header->from[0]->host != 'sbe-ltd.ca') {
				$attachments = array();
	       		$attachmentName = array();

	        	if ($sender != 'productionreport@sbe-ltd.co.uk') {
	        		# code...
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
					$subject = imap_utf8_fix($overview[0]->subject);

					if ( isset($overview[0]->subject) && !empty($overview[0]->subject) ) {
						$data['email_subject'] =  trim(str_replace("'", "''", $subject));
					} else {
						$data['email_subject'] = ""; 
					}
					if (!empty($attachmentName)) {
						echo "<br>Attachments: <br>";
						printArr($attachmentName);
					};
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
								// printArr($attachmentName);
								$check_qry = "SELECT * FROM AP_VALIDATION WHERE email_subject = '{$data['email_subject']}' and attached_document = '{$filename}' and sender_email = '{$data['sender_email']}' and sent_date = '{$data['sent_date']}' and sent_time = '{$data['sent_time']}'";
								$check_res = $conn->query($check_qry)->fetchAll(PDO::FETCH_ASSOC);							
								if (empty($check_res)) {
									echo "<b>Invoice to be saved: {$filename}</b> <br>";
									// get unique identifier as index
									$data['id'] = str_pad(getNextSeq("AP_VALIDATION_ID"), 9, "0", STR_PAD_LEFT);
									$data['attached_document'] = $filename;
									// save to document with id as username
									$document_folder = "documents_to_be_received";
									$document_path = $document_folder . "/" . $data['id'] . $extension;
									$data['document_path'] = $document_path;
									$data['RENAMED_ATTACHED_FILE'] = $data['id'] . $extension;
									$data['supplier'] = getSupplier($data['sender_email'], $data['domain']);
									$data['document_type'] = determineDocuType($data['sender_email'], $data['domain']);
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
										// Instantiate insert data array
										$insert_data = [];
										// extract invoice information
										$extraction = new InvoiceDetailsExtractor($conn, $actualFile, $data['supplier']);
										$extracted_data = $extraction->extractData();
										// if extracted data not null, merge with insert data
										if ($extracted_data !== null)  $insert_data = array_merge($extracted_data['INVOICE_DETAILS'], $insert_data);
										//merge inserted data with initial data
										$insert_data = array_merge($data, $insert_data);
										// create insert query
										$qb = new QueryBuilder('AP_VALIDATION');
										$query = $qb->insertDb($insert_data);										
										// printArr($attachmentName);
										if ($conn->query($query)) {
											$ctr++;
											saveInvoiceItems($data['supplier'], $extracted_data['INVOICE_ITEMS'], $data['id']);
											echo "<br>Inserted data:<br>";
											printArr($insert_data);
											echo "<b>Saved: {$data['attached_document']}</b> <br>";
										} else {
											echo "<b>Invoice not saved, consult error logs: {$data['attached_document']}</b> <br>";
											$logfile = "D:\Portals\sbe_ap_validation\logs\\error_logs.txt";											
											$errorData = [];
											$errorData['portal_name'] = "ap_validation - email extraction";
											$errorData['uname'] = "system";
											$errorData['date'] = date("Y/m/d");
											$errorData['time'] = date("G:i:s");
											$errorData['query'] = $query;
											writeErrorLogs($logfile, $errorData);
											// send error notification email to developer
											errorNotification("cnayve@sbe-ltd.ca", "Email extractor - automated mode", $query);
										}
									}									
								} else {	
									echo "<br><b>Data already saved:</b><br>";
									printArr($data);								
									echo "<b>already saved</b>" . "<br>";
								}

							}

						}
					}

	        	} 
			} else {
				// echo "status: email excluded from portal <br>";
			}
		}
		echo "No. of Invoices Saved: {$ctr}";
	}
	/* close the connection */
	imap_close($inbox);


