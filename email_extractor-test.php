<?php 
	include 'db/dbCon.php';
	include 'db/functions.php';
	include 'class/queryBuilder.php';
	date_default_timezone_set('US/Eastern'); 
	$mydate = getdate(date("U"));
	// echo "<pre>";
	// print_r($mydate);
	// echo "</pre>";
	if( strlen($mydate['mday'])==1)
	{
	 $Day = str_pad($mydate['mday'],2,0,STR_PAD_LEFT);
	}
	else {
	 $Day  = $mydate['mday'];
	}
	if( strlen($mydate['month'])==1)
	{
	 $Month = str_pad($mydate['month'],2,0,STR_PAD_LEFT);
	}
	else {
	 $Month = $mydate['month'];
	}
	// $date = $Day." ".$Month." ".$mydate['year'];
	$date = "21 April ".$mydate['year'];
	echo '<BR/>';
	echo $date;
	echo '<BR/>';


	$hostname = '{mail.sbe-ltd.co.uk:993/imap/ssl}INBOX';
	$username = 'cnayve@sbe-ltd.ca';
	$password = 'Nayve106433!';
	$inbox =imap_open($hostname,$username,$password) or die ('cannot connect to Mail '.imap_last_error());
	
	$emails = imap_search($inbox,"ON \"$date\"");

	echo "Number of emails: " . count($emails) . "<br>";

	// echo "<pre>";
	// print_r($emails);
	// echo "</pre>";


	// foreach ($emails as $emailNum) {
	// 	$overview = imap_fetch_overview($inbox,$emailNum,0);
	// 	// echo "<pre>";
	// 	// print_r($overview);
	// 	// echo "</pre>";

	// 	$header = imap_headerinfo($inbox, $emailNum);

	// 	$sender = $header->from[0]->mailbox . "@" . $header->from[0]->host;

	// 	echo $sender . "<br>";

	// 	$structure = imap_fetchstructure($inbox, $emailNum);

	// 	echo "<pre>";
	// 	print_r($structure);
	// 	echo "</pre>";

	// }


	// //close the stream
 //     imap_close($inbox);


	/* if any emails found, iterate through each email */
if($emails) {

    $count = 1;

    /* put the newest emails on top */
    rsort($emails);

    /* for every email... */
    foreach($emails as $email_number) 
    {

        /* get information specific to this email */
        $overview = imap_fetch_overview($inbox,$email_number,0);

        $message = imap_fetchbody($inbox,$email_number,2);

        /* get mail structure */
        $structure = imap_fetchstructure($inbox, $email_number);

        $header = imap_headerinfo($inbox, $email_number);

		$sender = $header->from[0]->mailbox . "@" . $header->from[0]->host;

        $attachments = array();

        $attachmentName = array();

        if ($sender === 'otoluakande@sbe-ltd.ca') {
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

	        echo $sender . "<br>";

			// echo "<pre>";
			// print_r($overview);
			// echo "</pre>";

			$data = [];
			
			$data['sender_name'] = $overview[0]->from;
			$data['sender_email'] = $sender;
			$data['domain'] = $header->from[0]->host;
			$data['sent_date'] = date("Y/m/d", strtotime($overview[0]->date));
			$data['sent_time'] = date("h:i:s", strtotime($overview[0]->date));
			$data['email_subject'] =  $overview[0]->subject;


			/* if any attachments found... */
			/* iterate through each attachment and save it */
	        foreach($attachments as $attachment)
	        {
	            if($attachment['is_attachment'] == 1)
	            {
	            	// check if document exist in the database if not download document and save to database
	                $condition = "WHERE email_subject = '{$data['email_subject']}' and attached_document = '{$attachment['name']}'";
	                $databaseCheck = searchIfDataExists("AP_VALIDATION", $condition , '94');

	                if (!$databaseCheck) {
	                	# code...
	                	// get unique identifier as index
			            $data['id'] = str_pad(getNextSeq("AP_VALIDATION_ID"), 9, "0", STR_PAD_LEFT);

	                	$filename = $attachment['name'];

		                if(empty($filename)) $filename = $attachment['filename'];

		                if(empty($filename)) $filename = time() . ".dat";	                

		                // get extension of file
		                $ext = substr($filename,strrpos($filename,"."),(strlen($filename)-strrpos($filename,".")));
		                $ext = strtolower($ext);

		                // if pdf and excel save to archive and save to document and save to database
		                if (in_array($ext, array('.pdf','.xls','.xlsx'))) {
		                	# code...
		                	$archive_folder = "archive";

		                	$archive_path = $archive_folder . "/" . $filename;
		                	// if folder does not exist create folder
			                if(!is_dir($archive_folder))
			                {
			                     mkdir($archive_folder);
			                }

			                $archive_file = fopen($archive_path, "w+");
			                fwrite($archive_file, $attachment['attachment']);
			                fclose($archive_file);

			                $data['attached_document'] = $filename;

			                // save to document with id as username
			                $document_folder = "documents_to_be_received";

			                $document_path = $document_folder . "/" . $data['id'] . $ext;
			                // if folder does not exist create folder
			                if(!is_dir($document_folder))
			                {
			                     mkdir($document_folder);
			                }

			                $document_file = fopen($document_path, "w+");
			                fwrite($document_file, $attachment['attachment']);
			                fclose($document_file);

			                $data['document_path'] = $document_path;
			                $data['RENAMED_ATTACHED_FILE'] = $data['id'] . $ext;

			                $qb = new QueryBuilder('AP_VALIDATION');
							$query = $qb->insertDb($data);

							if (file_exists($document_path)) {
								if ($conn->query($query)) {

									// Data is saved, save into history
									$history_data = [];
									$history_data['id'] = str_pad(getNextSeq("AP_VALIDATION_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
									$history_data['actions'] = "inserted into database";
									$history_data['actions_by'] = 'system';
									$history_data['status'] = 'to be received';
									$history_data['document_type'] = 'Invoice';
									$history_data['ap_VALIDATION_id'] = $data['id'];
									$history_qb = new QueryBuilder('AP_VALIDATION_HISTORY');
									$history_qry = $history_qb->insertDb($history_data);
									if ($conn->query($history_qry)) {
										echo "Invoice saved <br>";
									} else {
										echo "Invoice saved but history error <br>";
										echo $history_qry . "<br>";
									}

									
								} else {
									echo $query . "<br>";
								}
							}

		                }


	                } else {
	                	echo "already saved <br>";
	                }

	                
	                

	                
	            }

	        }

        }   
    }
} 

/* close the connection */
imap_close($inbox);

echo "all attachment Downloaded";