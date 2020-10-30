<?php 
	include 'db/dbCon.php';
	include 'db/functions.php';
	include 'class/queryBuilder.php';
	include 'pdfparser/invoice_extractor.php';
	date_default_timezone_set('US/Eastern'); 
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

			echo $sender . "<br>";
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
							$check_qry = "SELECT * FROM AP_VALIDATION WHERE attached_document = '{$filename}'";
							$check_res = $conn->query($check_qry)->fetchAll(PDO::FETCH_ASSOC);
							
							// printArr($data);

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
				                $data['supplier'] = getSupplier($data['domain']);
				                
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

									printArr($data);

									printArr($attachmentName);

									if ($conn->query($query)) {
										$ctr++;
										
										echo "saved <br>";
									} else {
										$logfile = "logs/error_logs.txt";
										$errorData = [];
										$errorData['portal_name'] = "ap_validation - email extraction";
										$errorData['uname'] = "system";
										$errorData['date'] = date("Y/m/d");
										$errorData['time'] = date("G:i:s");
										$errorData['query'] = $query;
										writeErrorLogs($logfile, $errorData);
									}

				                }
				                
	                		} else {
								printArr($data);

								printArr($attachmentName);
	                			echo "already saved" . "<br>";
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


					// printArr($data);

				// printArr($attachmentsName);
				

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

							$check_qry = "SELECT * FROM AP_VALIDATION WHERE email_subject = '{$data['email_subject']}' and attached_document = '{$filename}' and sender_email = '{$data['sender_email']}' and sent_date = '{$data['sent_date']}' and sent_time = '{$data['sent_time']}'";
							$check_res = $conn->query($check_qry)->fetchAll(PDO::FETCH_ASSOC);
							
							// printArr($data);

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
				                $data['supplier'] = getSupplier($data['domain']);
				                
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

									printArr($data);

									printArr($attachmentName);

									if ($conn->query($query)) {
										$ctr++;
										
										echo "saved <br>";
									} else {
										$logfile = "logs/error_logs.txt";
										$errorData = [];
										$errorData['portal_name'] = "ap_validation - email extraction";
										$errorData['uname'] = "system";
										$errorData['date'] = date("Y/m/d");
										$errorData['time'] = date("G:i:s");
										$errorData['query'] = $query;
										writeErrorLogs($logfile, $errorData);
									}

				                }
				                
	                		} else {
								printArr($data);

								printArr($attachmentName);
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


