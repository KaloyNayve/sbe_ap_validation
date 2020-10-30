<?php 
	require 'db/functions.php';
	require 'class/user.php';
	require 'class/queryBuilder.php';
	require 'PHPExcel/Classes/PHPExcel.php';
	require 'PHPExcel/Classes/PHPExcel/IOFactory.php';
	require 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
	date_default_timezone_set('US/Eastern');
	$datein = date("Y-m-d"); 
	$timein	= date("G:i:s");
	$portal_name = "sbe_ap_validation";
	$user = unserialize($_SESSION[$portal_name]);
	$uname = $user->username;

	function createArchiveFile($data) {		
		// Check if month folder exists, if not make dir		
		$invoice_date = strtotime($data['INVOICE_DATE']);
		$month_folder = date('m', $invoice_date) . " " . date('M', $invoice_date) . " " . date('Y', $invoice_date) . "/";
		if (!is_dir("documents_archived/" . $month_folder)) {
			@mkdir("documents_archived/" . $month_folder, 0777, true);
		}

		// generate archived file path
		$archived_file = $month_folder . $data['BACKUP_FILENAME'];
		//copy backup file to archive folder
		copy("documents_backup/" . $data['BACKUP_FOLDER'] . $data['BACKUP_FILENAME'], "documents_archived/" . $archived_file);
		// check if archive file exists
		if (file_exists("documents_archived/" . $archived_file)) {
			return $archived_file;
		} else {
			return false;
		}
	}

	/* 
		function that takes in $data array and $fileToBeBackedup to be backed up 
		in $data array you need two keys backup_folder and backup_filename

		backup_folder = month folder format = Month count underscore Month Name underscore Year ei 05_Month_2020
		backup_filename = invoicedate - supplier - invoice number ie 05052020-supplier-0918390438.pdf

		$fileToBeBackedup path of file to be backed up

	*/		
	function createDocumentBackups($data, $fileToBeBackedup) {
		// check if month folder exists, if not create month folder
		if (!is_dir("documents_backup/" . $data['backup_folder'])) {
			mkdir("documents_backup/" . $data['backup_folder'], 0777, true);
		}
		
		//generated backup file path
		$backup_path = "documents_backup/" . $data['backup_folder'] .  $data['backup_filename'] ;
		//copy $fileToBeBackedUp to folder with formatted filename
		//check if backup file already exists
		if (file_exists($backup_path)) {
			return true;
		} else {
			// if backup does not exist copy file
			if (copy($fileToBeBackedup, $backup_path)) {
				// if copy successful delete old file
				if (unlink($fileToBeBackedup)) {
					return true;
				} else {
					return false;
				}

			} else {
				return false;
			}
		}


	}


	if (isset($_POST['getDocument_information'])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		$query = "SELECT * FROM AP_VALIDATION WHERE ID = '{$id}'";
		$res = $conn->query($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($res)) {
			echo "Not found";
		} else {
			$json = json_encode($res);
			echo $json;
		}	

	}

	if (isset($_POST['receive_document'])) {
		
        $input = filter_input_array(INPUT_POST);
        $page = $input['page_url'];
		unset($input['page_url']);
		$input = array_map("trim", $input); // apply trim to $input

		//notification array
		// $notifArray = Array("Payroll and Benefits", "Benefits" , "Expense Reimbursement");
		$notifArray = Array("None");
		// $notifArray = Array("Benefits");

		// determine if an email notification is needed
	  	$sendNotification  = (in_array($input['document_type'], $notifArray)) ? true : false;

		// generate data array for saving data
		$data = array_diff_key($input, array_flip(['receive_document', 'index','document_file']));
		$id = $input['index'];		
		$data['status'] = 'in flow';
		$data['updated_by'] = $uname;
		// save into backup folder
		$invoice_date = strtotime($data['invoice_date']);
		$data['backup_folder'] = date('m', $invoice_date) . "_" . date('M', $invoice_date) . "_" . date('Y', $invoice_date) . "/";
		$document_file = $input['document_file'];
		// get file extension
		$ext = strtolower(substr($document_file,strrpos($document_file,"."),(strlen($document_file)-strrpos($document_file,"."))));
		// generate backup filename
		$data['backup_filename'] = str_replace("/", "", $data['invoice_date']) . "-" . $data['supplier'] . "-" . $data['invoice_number'] . $ext;	
		
		// preparing query
		$queryBuilder = new QueryBuilder("AP_VALIDATION");
		$queryBuilder->set($data);
		$queryBuilder->set_timestamp("update_date");
		$queryBuilder->where('id', $id);
		$qry = $queryBuilder->updateDb();

		if ($conn->query($qry)) {
			// Save in history
			// Data is saved, save into history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_VALIDATION_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "received document";
			$history_data['actions_by'] = $uname;
			$history_data['status'] = $data['status'];
			$history_data['document_type'] = $input['document_type'];
			$history_data['ap_validation_id'] = $id;
			$history_qb = new QueryBuilder('AP_VALIDATION_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				
				if ($sendNotification) {
					// Send notification email to who's handling that document type
					$notification_qry = "SELECT * FROM AP_validation WHERE id = '{$id}'";
					$notification_data = $conn->query($notification_qry)->fetch(PDO::FETCH_ASSOC);
					if (sendEmailNotification($notification_data) && createDocumentBackups($data, $document_file)) {
						sendMsg("Document Received", "success", $page);
					}
				} else {
					if (createDocumentBackups($data, $document_file)) {
						sendMsg("Document Received", "success", $page);
					}					
				}
				
				
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				sendMsg("Something went wrong, contact data department", "failure", $page);
			}			


			// sendMsg("Document Received", "success", $page);
		} else {
			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);

			sendMsg("Something went wrong, contact data department", "failure", $page);
		}
	}


	if (isset($_POST["put_document_on_hold"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		$query = "UPDATE AP_validation set
				  STATUS = 'on hold',
				  UPDATED_BY = '{$uname}',
				  UPDATE_DATE = sysdate 
				  WHERE id = '{$id}'";
		
		if ($conn->query($query)) {
			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "put document on hold";
			$history_data['actions_by'] = $uname;
			$history_data['status'] = 'on hold';			
			$history_data['ap_validation_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}			
			
		} else {

			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
		
	}	

	if (isset($_POST["delete_document"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		$new_invoice_number = $input['invoice_number'] . '-deleted-' . rand(0,100);
		$query = "UPDATE AP_validation set
				  INVOICE_NUMBER = '{$new_invoice_number}',
				  DELETED = 'yes',
				  DELETED_BY = '{$uname}',
				  DELETION_DATE = sysdate,
				  STATUS = 'deleted' 
				  WHERE id = '{$id}'";
		if ($conn->query($query)) {

			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "deleted document";
			$history_data['actions_by'] = $uname;			
			$history_data['ap_validation_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}
			
		} else {

			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
		
	}

	if (isset($_POST["validate_document"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		$query = "UPDATE AP_validation set
				  STATUS = 'validated',
				  UPDATED_BY = '{$uname}',
				  UPDATE_DATE = sysdate,
				  VALIDATION_DATE = sysdate 
				  WHERE id = '{$id}'";
		if ($conn->query($query)) {

			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "validated document";
			$history_data['actions_by'] = $uname;
			$history_data['status'] = 'validated';			
			$history_data['ap_validation_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);

			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}
			
		} else {

			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
		
	}

	if (isset($_POST["archive_document"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		// get information for archiving the file
		$qry = "SELECT * FROM AP_VALIDATION WHERE ID = '{$id}'";		
		$data = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
		// Create archive file
		$archived_file = createArchiveFile($data);
		if ($archived_file) {
			# saved data
			$update_qry = "UPDATE AP_validation set
					  STATUS = 'archived',
					  UPDATED_BY = '{$uname}',
					  UPDATE_DATE = sysdate,
					  ARCHIVED_FILENAME = '{$archived_file}' 
					  WHERE id = '{$id}'";
			if ($conn->query($update_qry)) {
			  	# save history
			  	// save in history
				$history_data = [];
				$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
				$history_data['actions'] = "archived document";
				$history_data['actions_by'] = $uname;
				$history_data['status'] = 'archived';			
				$history_data['ap_validation_id'] = $id;
				$history_qb = new QueryBuilder('AP_validation_HISTORY');
				$history_qry = $history_qb->insertDb($history_data);
				if ($conn->query($history_qry)) {
					
					echo "success";
				} else {
					// if error occurs on query save to error logs
					$logfile = "logs/error_logs.txt";
					$errorData = [];
					$errorData['portal_name'] = "ap_validation";
					$errorData['uname'] = $uname;
					$errorData['date'] = date("Y/m/d");
					$errorData['time'] = date("G:i:s");
					$errorData['query'] = $history_qry;
					writeErrorLogs($logfile, $errorData);
					echo "fail";
				}

			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";				
			}		  
					  
		} else {
			echo "fail";
		}
	}


	if (isset($_POST["get_history"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		$query = "SELECT 
					  actions,
					  actions_by,
					  to_char(action_timestamp, 'YYYY/MM/DD') as history_date,
					  to_char(action_timestamp, 'HH:ii:ss') as history_time,
					  status,
					  document_type
				  FROM AP_validation_HISTORY
				  WHERE AP_validation_ID = '{$id}'";
		$res = $conn->query($query)->fetchAll(PDO::FETCH_OBJ);
		
		if (empty($res)) {
			echo "not found";
		} else {
			$json = json_encode($res);
			echo $json;
		}		
		
	}

	if (isset($_POST["upload_attachment"])) {
		$input = filter_input_array(INPUT_POST);
		$data = [];  // prepare data for saving in database
		$data['document_name'] = $input['document_name'];
		$data['ap_validation_id'] = $input['selected_id'];
		if ( 0 < $_FILES['file']['error'] ) {
			// Error occured
	        echo "error";
	    }
	    else {
	    	
	    	$data['filename'] = time() . $input['ext'];
	    	$attachments_folder = "documents_attached/"; // folder where to save attached documents
	    	$data['file_path'] = $attachments_folder . $data['filename']; // attached documents path
	        move_uploaded_file($_FILES['file']['tmp_name'], $data['file_path']);
	        if (file_exists($data['file_path'])) {
	        	// save to database
	        	$data['id'] = str_pad(getNextSeq("AP_validation_Attachments_ID"), 9, "0", STR_PAD_LEFT);
	        	$data['upload_by'] = $uname;

	        	$qb = new QueryBuilder('AP_VALIDATION_ATTACHMENTS');
				$query = $qb->insertDb($data);

	        	if ($conn->query($query)) {
	        		# save to history
	        		// save in history
					$history_data = [];
					$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
					$history_data['actions'] = "uploaded attached documents";
					$history_data['actions_by'] = $uname;								
					$history_data['ap_validation_id'] = $data['ap_validation_id'];
					$history_data['ap_validation_attachments_id'] = $data['id'];
					$history_qb = new QueryBuilder('AP_validation_HISTORY');
					$history_qry = $history_qb->insertDb($history_data);

					if ($conn->query($history_qry)) {
						echo "success";
					}

	        	} else {
	        		// if error occurs on query save to error logs
					$logfile = "logs/error_logs.txt";
					$errorData = [];
					$errorData['portal_name'] = "ap_validation";
					$errorData['uname'] = $uname;
					$errorData['date'] = date("Y/m/d");
					$errorData['time'] = date("G:i:s");
					$errorData['query'] = $qry;
					writeErrorLogs($logfile, $errorData);

					echo "error";	
	        	}

	        } else {
	        	echo "error";
	        }

	    }	
		
		
	}

	// get attachments
	if (isset($_POST['get_attachments'])) {
		$id = $_POST['document_id'];
		$query = "SELECT * FROM AP_VALIDATION_ATTACHMENTS
				  WHERE DELETED is null AND AP_VALIDATION_ID = '{$id}'";
		$res = $conn->query($query)->fetchAll(PDO::FETCH_OBJ);
		if (empty($res)) {
			echo "no attachments";
		} else {
			echo json_encode($res);
		}
		
	}



	if (isset($_POST["delete_attachments"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['id'];
		$query = "UPDATE AP_validation_attachments set
				  DELETED = 'yes',
				  DELETE_BY = '{$uname}',
				  DELETE_DATE = sysdate 
				  WHERE id = '{$id}'";
		if ($conn->query($query)) {

			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "deleted document";
			$history_data['actions_by'] = $uname;			
			$history_data['ap_validation_id'] = $id;
			$history_data['ap_validation_attachments_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				// delete file

				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}
			
		} else {

			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $query;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
		
	}


	//send back to flow  handler
	if (isset($_POST["send_back_to_flow"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['document_id'];
		$query = "UPDATE AP_validation set
				  STATUS = 'in flow',
				  UPDATED_BY = '{$uname}',
				  UPDATE_DATE = sysdate 
				  WHERE id = '{$id}'";
		if ($conn->query($query)) {
			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "sent document back to flow";
			$history_data['actions_by'] = $uname;
			$history_data['status'] = 'archived';			
			$history_data['ap_validation_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			

			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}
			
		} else {

			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $query;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
		
	}



	// add note handler
	if (isset($_POST["add_note_to_document"])) {
		$input = filter_input_array(INPUT_POST);
		// printArr($input);
		$data = []; // prepare insert data
		$data['ap_validation_id'] = $input['notes_id'];
		$data['id'] = str_pad(getNextSeq("AP_VALIDATION_NOTES_ID"), 6, "0", STR_PAD_LEFT);
		$data['note'] = str_replace("'", "''", trim($input['note']));
		$data['added_by'] = $uname;
		$qb = new QueryBuilder("AP_VALIDATION_NOTES");
		$qry = $qb->insertDb($data);
		if ($conn->query($qry)) {
			# insert success, save in history
			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "added a note/comment to document";
			$history_data['actions_by'] = $uname;						
			$history_data['ap_validation_id'] = $data['ap_validation_id'];
			$history_data['ap_validation_notes_id'] = $data['id'];
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);

			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}

		} else {
			# insert failed
			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);

			echo "fail";

		}
	}

	// get notes handler
	if (isset($_POST["get_notes"])) {
		$id = $_POST['id'];
		$qry = "SELECT
					NOTE,
					ID,
					added_by,
					to_char(added_date, 'YYYY/MM/DD') as added_date,
					to_char(added_date, 'HH:MI:SS AM') as added_time,
					ap_validation_id
				FROM AP_VALIDATION_NOTES
				WHERE DELETED is NULL and ap_validation_id = '{$id}'";
		$res = $conn->query($qry)->fetchAll(PDO::FETCH_OBJ);
		if (empty($res)) {
			echo "none found";
		} else {
			echo json_encode($res);
		}
	}


	if (isset($_POST["delete_note"])) {
		$input = filter_input_array(INPUT_POST);
		$id = $input['id'];
		$ap_validation_id = $input['ap_validation_id'];
		$query = "UPDATE AP_validation_notes set
				  DELETED = 'yes',
				  DELETED_BY = '{$uname}',
				  DELETED_DATE = sysdate 
				  WHERE id = '{$id}'";
		if ($conn->query($query)) {

			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "deleted note";
			$history_data['actions_by'] = $uname;			
			$history_data['ap_validation_id'] = $ap_validation_id;
			$history_data['ap_validation_notes_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}
			
		} else {

			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $query;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
		
	}

	//send email handler
	if (isset($_POST['send_email'])) {
		$input = filter_input_array(INPUT_POST);
		if (forwardDocumentThroughEmail($input)) {
			echo "success";
		} else {
			echo "fail";
		}
	}

	// download in flow report handler
	if (isset($_POST['download_in_flow_report'])) {
	    // Generate report
	    
	    $data = [];
	    $data[0]['qry'] = "SELECT 
	    					invoice_number,
	    					supplier,
	    					invoice_date,
	    					invoice_date as upload_date,	    					
	    					document_type,  
	    					company
	    				  FROM AP_VALIDATION 
	    				  WHERE DELETED is null and status = 'in flow'
	    				  order by document_type";
	    $data[0]['sheetname'] = "Main";
	    $data[0]['connection'] = '94';
	    $report_name = "Ap_validation_in_flow";    
	    generatemultipleexcel2($data, $report_name);
	  }


	  // My document reports handler
	  if (isset($_POST['download_my_report'])) {
	  	$uname = trim($_POST['uname']);
	  	$type = getDocumentType($uname);

	  	$data = [];
	    $data[0]['qry'] = "SELECT 
	    					invoice_number,
	    					supplier,
	    					invoice_date,
	    					invoice_date as upload_date,	    					
	    					document_type,  
	    					company
	    				  FROM AP_VALIDATION 
	    				  WHERE DELETED is null and status = 'in flow'
	    				  and document_type in ({$type})
	    				  order by document_type";
	    $data[0]['sheetname'] = "Main";
	    $data[0]['connection'] = '94';
	    $report_name = "Ap_validation_my_documents";    
	    generatemultipleexcel2($data, $report_name);
	  }



	  if (isset($_POST['editDocumentInfo'])) {
	  	$input = filter_input_array(INPUT_POST);
	  	$input = array_map("trim", $input); // apply trim to $input
	  	// printArr($input);

	  	// determine if an email notification is needed
		  // $sendNotification  = (($input['original_type'] !== $input['document_type']) && (in_array($input['document_type'], Array("Payroll and Benefits", "Benefits" , "Expense Reimbursement")))) ? true : false;
		$sendNotification  = (($input['original_type'] !== $input['document_type']) && (in_array($input['document_type'], Array("None")))) ? true : false;
	  	// generate data array for saving data
		$data = array_diff_key($input, array_flip(['index','document_file', 'editDocumentInfo', 'original_type', 'page_url' , 'page']));
		$data['updated_by'] = $uname;
		// generate backup information
		$invoice_date = strtotime($data['invoice_date']);
		$data['backup_folder'] = date('m', $invoice_date) . "_" . date('M', $invoice_date) . "_" . date('Y', $invoice_date) . "/";
		// get file extension
		$ext = strtolower(substr($input['document_file'],strrpos($input['document_file'],"."),(strlen($input['document_file'])-strrpos($input['document_file'],"."))));
		// generate backup filename
		$data['backup_filename'] = str_replace("/", "", $data['invoice_date']) . "-" . $data['supplier'] . "-" . $data['invoice_number'] . $ext;	

		
		// preparing query
		$queryBuilder = new QueryBuilder("AP_VALIDATION");
		$queryBuilder->set($data);
		$queryBuilder->set_timestamp("update_date");
		$queryBuilder->where('id', $input['index']);
		$qry = $queryBuilder->updateDb();
		
		if ($conn->query($qry)) {
			# save history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_VALIDATION_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "updated document information";
			$history_data['actions_by'] = $uname;			
			$history_data['document_type'] = $input['document_type'];
			$history_data['ap_validation_id'] = $input['index'];
			$history_qb = new QueryBuilder('AP_VALIDATION_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {

				# if sendNotification is true send notification and create new backup

				if ($sendNotification) {
					// Send notification email to who's handling that document type
					$notification_qry = "SELECT * FROM AP_validation WHERE id = '{$input['index']}'";
					$notification_data = $conn->query($notification_qry)->fetch(PDO::FETCH_ASSOC);
					if (sendEmailNotification($notification_data) && createDocumentBackups($data, $input['document_file'])) {
						sendMsg("Updated Document Information", "success", $input['page']);
					} else {						
						sendMsg("Something went wrong!", "fail", $input['page']);
					}
				} else {
					#only create backup
					if (createDocumentBackups($data, $input['document_file'])) {
						# code...
						sendMsg("Updated Document Information", "success", $input['page']);
					} else {
						sendMsg("Something went wrong!", "fail", $input['page']);
					}
				}

			}
		} else {
			# if error write log
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation -- edit document information";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);
			sendMsg("Something went wrong, contact data department", "failure", $input['page']);
		}

	  }

	  if (isset($_POST['search_backups'])) {
	  	$input = filter_input_array(INPUT_POST);	  	
	  	$qry = "SELECT * FROM AP_VALIDATION 
	  			WHERE status not in ('to be received') and deleted is null "; // initial qry
	  	$prefix = "AND";
	  	//generate search conditions in query
	  	if (isset($input['invoice_number']) && !empty($input['invoice_number'])) {
	  		$qry .= $prefix . " invoice_number = '{$input['invoice_number']}' ";
	  		$prefix = "AND";
	  	}

	  	if (isset($input['supplier_search']) && !empty($input['supplier_search'])) {
	  		$qry .= $prefix . " supplier = '{$input['supplier_search']}' ";
	  		$prefix = "AND";
	  	}

	  	if (isset($input['invoice_date']) && !empty($input['invoice_date'])) {
			$invoiceDate = explode(" - ", $input['invoice_date']);
			// reformat the date string from dd/mm/yyyy to yyyy/mm/dd
			$date_from = date("Y/m/d", strtotime($invoiceDate[0]) );  
			$date_to = date("Y/m/d", strtotime($invoiceDate[1]) );  
	  		$qry .= $prefix . " invoice_date between '{$date_from}' and '{$date_to}' ";	  		
	  		$prefix = "AND";
	  	}
		  $res = $conn->query($qry)->fetchAll(PDO::FETCH_OBJ);
		  
	  	if (empty($res)) {	  		
	  		echo "no data";
	  	} else {
	  		echo json_encode($res); // return json encoded result
		}	
	  }


	  if (isset($_POST['upload_invoice'])) {	  	
	  	$input = filter_input_array(INPUT_POST);	  	
	  	// Get file upload information
	  	$file_type = $_FILES['uploadedInvoice']['type'];
	  	// check if file is pdf/excel file
	  	$allowed_types = [
	  		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	  		'application/vnd.ms-excel',
	  		'application/pdf'
	  	];
	  	if (in_array($file_type, $allowed_types)) {
		  #process upload
		  $input['attached_document'] = $_FILES['uploadedInvoice']['name'];
		  // get file extension
          $ext = substr($input['attached_document'],strrpos($input['attached_document'],"."),(strlen($input['attached_document'])-strrpos($input['attached_document'],".")));
          // generate month folder
          $invoice_date = strtotime($input['invoice_date']);
          $input['backup_folder'] = date('m', $invoice_date) . "_" . date('M', $invoice_date) . "_" . date('Y', $invoice_date) . "/";
          // check if month folder exists, if not create folder
          if (!is_dir("documents_backup/" . $input['backup_folder'])) {
			mkdir("documents_backup/" . $input['backup_folder'], 0777, true);
		  }
		  // generate backup file name
		  $input['backup_filename'] = str_replace("/", "", $input['invoice_date']) . "-" . $input['supplier'] . "-" . $input['invoice_number'] . $ext;
		  move_uploaded_file($_FILES['uploadedInvoice']['tmp_name'], "documents_backup/" . $input['backup_folder'] . $input['backup_filename']);
		  if (file_exists("documents_backup/" . $input['backup_folder'] . $input['backup_filename'])) {
		  	// echo "document saved <br>";
		  	// generate data array for saving data
			$data = array_diff_key($input, array_flip(['page_url', 'upload_invoice']));
			$data['id'] = str_pad(getNextSeq("AP_VALIDATION_ID"), 9, "0", STR_PAD_LEFT);
			$data['status'] = 'in flow';
			$data['uploaded_by'] = $uname;

			$qb = new QueryBuilder('AP_VALIDATION');
			$query = $qb->insertDb($data);
		  	
		  	if ($conn->query($query)) {
		  		# save history
				$history_data = [];
				$history_data['id'] = str_pad(getNextSeq("AP_VALIDATION_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
				$history_data['actions'] = "uploaded invoice";
				$history_data['actions_by'] = $uname;			
				$history_data['document_type'] = $data['document_type'];
				$history_data['ap_validation_id'] = $data['id'];
				$history_qb = new QueryBuilder('AP_VALIDATION_HISTORY');
				$history_qry = $history_qb->insertDb($history_data);

				if ($conn->query($history_qry)) {
					# save successful		  			
		  			// determine if an email notification is needed
					  // $sendNotification  = ((in_array($data['document_type'], Array("Payroll and Benefits", "Benefits" , "Expense Reimbursement")))) ? true : false;
					  $sendNotification  = ((in_array($data['document_type'], Array("None")))) ? true : false;
				  	if ($sendNotification) {
				  		$notification_qry = "SELECT * FROM AP_validation WHERE id = '{$data['id']}'";
						$notification_data = $conn->query($notification_qry)->fetch(PDO::FETCH_ASSOC);
						if (sendEmailNotification($notification_data)) {
							sendMsg("Invoice uploaded", "success", $input['page_url']);
						} else {
							sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
						}


				  	} else {
				  		sendMsg("Invoice uploaded", "success", $input['page_url']);
				  	}
				} 
	  		} else {
	  			# if error write log
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $query;
				writeErrorLogs($logfile, $errorData);
				sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
	  		}	

		  } else {
		  	sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
		  }		  


	  	} else {
	  		sendMsg("File type not allowed", "failure", $input['page_url']);
	  	}
	  	
	  }

	  if (isset($_POST['search_archived'])) {
		$input = filter_input_array(INPUT_POST);	  	
		$qry = "SELECT * FROM AP_VALIDATION 
				WHERE status in ('archived') "; // initial qry
		$prefix = "AND";
		//generate search conditions in query
		if (isset($input['invoice_number']) && !empty($input['invoice_number'])) {
			$qry .= $prefix . " invoice_number = '{$input['invoice_number']}' ";
			$prefix = "AND";
		}

		if (isset($input['supplier']) && !empty($input['supplier'])) {
			$qry .= $prefix . " regexp_like(supplier, '{$input['supplier']}', 'i') ";
			$prefix = "AND";
		}

		if (isset($input['invoice_date']) && !empty($input['invoice_date'])) {
			$invoiceDate = explode(" - ", $input['invoice_date']);
			$qry .= $prefix . " invoice_date between '{$invoiceDate[0]}' and '{$invoiceDate[1]}' ";	  		
			$prefix = "AND";
		}

		if (isset($input['validation_date']) && !empty($input['validation_date'])) {
			$validationDate = explode(" - ", $input['validation_date']);
			$qry .= $prefix . " to_char(validation_date, 'MM/DD/YYYY') between '{$validationDate[0]}' and '{$validationDate[1]}' ";	  		
			$prefix = "AND";
		}

		$res = $conn->query($qry)->fetchAll(PDO::FETCH_OBJ);
		
		if (empty($res)) {	  		
			echo "no data";
		} else {
			echo json_encode($res); // return json encoded result
		}

	}

	if (isset($_POST['addUser'])) {
		$input = filter_input_array(INPUT_POST);
		$page = $input['page_url'];
		unset($input['page_url']);
		unset($input['addUser']);
		$user = explode("-", $input['user']);
		unset($input['user']);
		$input['uname'] = $user[3];
		$input['badge'] = $user[0];
		$input['first_name'] = strtolower(ucwords($user[1]));
		$input['last_name'] = strtolower(ucwords($user[2]));
		$input['created_by'] = $uname;
		//check if badge already in the db
		$check_qry = "SELECT * FROM ap_validation_users where badge = '{$input['badge']}'";
		$check_res = $conn->query($check_qry)->fetch(PDO::FETCH_ASSOC);
		if (empty($check_res)) {
			$qb = new QueryBuilder("AP_VALIDATION_USERS");	
			$qry = $qb->insertDb($input);
			if ($conn->query($qry)) {
				sendMsg("User granted access", "success", $page);
			} else {
				# if error write log
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $qry;
				writeErrorLogs($logfile, $errorData);
				sendMsg("Something went wrong, contact data department", "failure", $page);
			}
		} else {
			if (!empty($check_res['DELETED'])) {
                $qry = "UPDATE AP_VALIDATION_USERS SET DELETED = '',
                        DELETED_BY = '',
                        DELETED_DATE = '',
                        ACCESS_LEVEL = '{$input['access_level']}',
                        CREATED_BY = '{$uname}',
                        CREATED_ON = sysdate
                        WHERE badge = '{$input['badge']}'";
                if ($conn->query($qry)) {
                    sendMsg("User granted access", "success", $page);
                }
            } else {
                sendMsg("User already has access", "failure", $page);
                
            }
		}		
		
	}

	if (isset($_POST['action']) && $_POST['action'] === 'edit') {
		$input = filter_input_array(INPUT_POST);
		$badge = trim($_POST['badge']);
		$access_level = trim($_POST['access_level']);
        $qry = "UPDATE AP_VALIDATION_USERS 
                SET ACCESS_LEVEL = '{$access_level}',
                UPDATED_BY = '{$uname}',
                UPDATED_DATE = sysdate
                WHERE badge = '{$badge}'";
        if ($conn->query($qry)) {
            echo json_encode($input);
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
		$input = filter_input_array(INPUT_POST);
		$badge = trim($_POST['badge']);		
        $qry = "UPDATE AP_VALIDATION_USERS SET DELETED = '1',
                DELETED_BY = '{$uname}',
                DELETED_DATE = sysdate
                WHERE badge = '{$badge}'";
        if ($conn->query($qry)) {
            echo json_encode($input);
        }
	}

	if (isset($_POST['change_invoice_number_submit'])) {
		$input = filter_input_array(INPUT_POST);
		$qry = "UPDATE ap_validation set invoice_number = '{$input['change_invoice_number']}' where ID = '{$input['change_id']}'";
		if ($conn->query($qry)) {
			# save history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_VALIDATION_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "changed invoice number from {$input['old_invoice_number']} to {$input['change_invoice_number']}";
			$history_data['actions_by'] = $uname;
			$history_data['ap_validation_id'] = $input['change_id'];
			$history_qb = new QueryBuilder('AP_VALIDATION_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				sendMsg("Invoice number successfully changed", "success", $input['page_url']);
			} else {
				# if error write log
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $query;
				writeErrorLogs($logfile, $errorData);
				sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
			}			
		} else {
			# if error write log
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation-change_invoice_number_submit";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);
			sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
		}
	}

	if (isset($_POST['reassign_document_type-submit'])) {
		# reassign document type
		# save comment as document note attached to that ap validation id
		$input = filter_input_array(INPUT_POST);
		//query to reassign the document type
		$qry = "UPDATE AP_VALIDATION SET DOCUMENT_TYPE = '{$input['reassign_document_type-document_type']}' WHERE ID = '{$input['reassign_document_type-id']}'";
		if ($conn->query($qry)) {
			# save in history and saves the comment as document notes			
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_VALIDATION_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "Invoice reassigned document type";
			$history_data['actions_by'] = $uname;
			$history_data['ap_validation_id'] = $input['reassign_document_type-id'];
			$history_qb = new QueryBuilder('AP_VALIDATION_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				# save comment into document notes attached to id
				$notes_data = []; // prepare insert notes_data
				$notes_data['ap_validation_id'] = $input['reassign_document_type-id'];
				$notes_data['id'] = str_pad(getNextSeq("AP_VALIDATION_NOTES_ID"), 6, "0", STR_PAD_LEFT);
				$notes_data['note'] = str_replace("'", "''", trim($input['reassign_document_type-comment']));
				$notes_data['added_by'] = $uname;
				$qb = new QueryBuilder("AP_VALIDATION_NOTES");
				$qry = $qb->insertDb($notes_data);
				if ($conn->query($qry)) {
					# send to page with success message
					sendMsg("Document type successfully reassigned", "success", $input['page_url']);
				} else {
					#if error write in error logs
					$logfile = "logs/error_logs.txt";
					$errorData = [];
					$errorData['portal_name'] = "ap_validation-reassign_document_type-submit -- saving comment to document notes";
					$errorData['uname'] = $uname;
					$errorData['date'] = date("Y/m/d");
					$errorData['time'] = date("G:i:s");
					$errorData['query'] = $qry;
					writeErrorLogs($logfile, $errorData);
					sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
				}
			} else {
				#if error write in error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation-reassign_document_type-submit -- add history record";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $qry;
				writeErrorLogs($logfile, $errorData);
				sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
			}
		} else {
			# if error write in error logs			
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation-reassign_document_type-submit -- update document type";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);
			sendMsg("Something went wrong, contact data department", "failure", $input['page_url']);
		}
    }
    
   if (isset($_POST['delete_on-hold_documents-submit'])) {
		$input = filter_input_array(INPUT_POST);		
		$id = $input['delete_on-hold_documents-id'];
		$new_invoice_number = $input['delete_on-hold_documents-invoice_number'] . '-deleted-' . rand(0,100);
		$qry = "UPDATE AP_validation set
				  INVOICE_NUMBER = '{$new_invoice_number}',
				  DELETED = 'yes',
				  DELETED_BY = '{$uname}',
				  DELETION_DATE = sysdate,
				  STATUS = 'deleted',
				  DELETED_REASON = '{$input['delete_on-hold_documents-comment']}' 
				  WHERE id = '{$id}'";
		if ($conn->query($qry)) {
			// save in history
			$history_data = [];
			$history_data['id'] = str_pad(getNextSeq("AP_validation_HISTORY_ID"), 9, "0", STR_PAD_LEFT);
			$history_data['actions'] = "deleted document";
			$history_data['actions_by'] = $uname;			
			$history_data['ap_validation_id'] = $id;
			$history_qb = new QueryBuilder('AP_validation_HISTORY');
			$history_qry = $history_qb->insertDb($history_data);
			if ($conn->query($history_qry)) {
				echo "success";
			} else {
				// if error occurs on query save to error logs
				$logfile = "logs/error_logs.txt";
				$errorData = [];
				$errorData['portal_name'] = "ap_validation";
				$errorData['uname'] = $uname;
				$errorData['date'] = date("Y/m/d");
				$errorData['time'] = date("G:i:s");
				$errorData['query'] = $history_qry;
				writeErrorLogs($logfile, $errorData);

				echo "fail";
			}
		} else {
			// if error occurs on query save to error logs
			$logfile = "logs/error_logs.txt";
			$errorData = [];
			$errorData['portal_name'] = "ap_validation";
			$errorData['uname'] = $uname;
			$errorData['date'] = date("Y/m/d");
			$errorData['time'] = date("G:i:s");
			$errorData['query'] = $qry;
			writeErrorLogs($logfile, $errorData);

			echo "fail";
		}
   }

