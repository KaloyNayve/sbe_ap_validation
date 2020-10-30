<?php 
  	include 'header.php'; 
	require 'PHPExcel/Classes/PHPExcel.php';
	require 'PHPExcel/Classes/PHPExcel/IOFactory.php';
	require 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
	ini_set('precision', '15'); //return: 3.5410705883463E+14 realy value: 357888057644723
	date_default_timezone_set("America/Toronto");

	function processWorksheet($arr) {
		$val = [];	
		foreach ($arr as $key => $value) {					
					
			if (!empty($value[0]) && !isset($data[$value[0]])) {
				$val[$value[0]] = [];						
			}

			if (!empty($value[0])) {
				$invoice_data = []; // create invoice data array
				$invoice_data['invoice_number'] = $value[1];
				$invoice_data['invoice_date'] = $value[3];
				$invoice_data['amount'] = $value[6];
				$invoice_data['currency'] = $value[5];
				array_push($val[$value[0]], $invoice_data);
			}					 
		}
		return $val;	
	}

	if (isset($_POST['excelUpload'])) {
		$filename = $_FILES["excelFile"]["name"];
		$tmp = $_FILES['excelFile']['tmp_name'];
		$ext= substr($filename,strrpos($filename,"."),(strlen($filename)-strrpos($filename,".")));
		if ($ext=='.xlsx' || $ext=='.xls') {
			# read excel file
			$excelReader = PHPExcel_IOFactory::createReaderForFile($tmp);
			$excelObj = $excelReader->load($tmp);
			$sheetCount = $excelObj->getSheetCount();
			if ($sheetCount === 8) {
				$sheets = [];
				foreach ($excelObj->getAllSheets() as $sheet) {
					$sheets[$sheet->getTitle()] = $sheet->toArray(); //get all worksheets
				}
				
				$employee_array = $sheets['Employee']; // get Employee worksheet
				array_shift($employee_array); // removing first index
				$wire_array = $sheets['Wire']; // get Wire worksheet	
				array_shift($wire_array);// remove first index
				$eft_array = $sheets['EFT']; // get EFT worksheet
				array_shift($eft_array); // remove first index				
				// merge arrays
				$total_array = array_merge($employee_array, $wire_array);
				$total_array = array_merge($total_array, $eft_array);
				$data = [];
				foreach ($total_array as $key => $value) {					
					
					if (!empty($value[0]) && !isset($data[$value[0]])) {
						$data[$value[0]] = [];						
					}
		
					if (!empty($value[0])) {
						$invoice_data = []; // create invoice data array
						$invoice_data['invoice_number'] = $value[1];
						$invoice_data['invoice_date'] = $value[3];
						$invoice_data['amount'] = $value[6];
						$invoice_data['currency'] = $value[5];
						array_push($data[$value[0]], $invoice_data);
					}					 
				}

				// Process the data generated
				foreach ($data as $key => $value) {
					echo $key . '<br>';
					$total_amount = 0;
					foreach ($value as $invoice) {
						$total_amount = $total_amount + $invoice['amount'];
					}
					if ($total_amount > 0) {
						echo "send email  total amount: {$total_amount} <br>";
					} else {
						echo "dont send email  total amount: {$total_amount} <br>";
					}
				}
				
				


			} else {
				sendMsg("You uploaded wrong file", "failure", basename($_SERVER['PHP_SELF']));
			}
			
			// $worksheet = $excelObj->getSheet(0);
			// $lastRow = $worksheet->getHighestRow();
			// $submitted =0;
			// date_default_timezone_set('UTC');
			
			// $last_column = $worksheet->getHighestColumn();
			// if ($last_column === 'H') {
			// 	$ctr = 0;
			// 	// Getting the columns name in an array
			// 	$columns = array();
			// 	$range = range('A', $last_column);
			// 	foreach($range as $letter) {
			// 		$columns[$letter] = $worksheet->getCell($letter."1")->getValue();
			// 	}

			// 	for ($i = 2;$i <= $lastRow; $i++) {
			// 		$data = [];
			// 		for ($j=0; $j < count($range); $j++) { 
			// 			$data[$range[$j]] = trim($worksheet->getCell($range[$j] . $i)->getValue());
			// 		}
			// 		// send email
			// 		if (!empty($data['B']) && sendRemittanceEmail($data)) {
			// 			$ctr++;
			// 		}
			// 	}
			// 	sendMsg("Sent {$ctr} Payment Notifications", "success", basename($_SERVER['PHP_SELF']));
			// } else {
			// 	sendMsg("File has missing/extra columns", "failure", basename($_SERVER['PHP_SELF']));
			// }



		} else {
			sendMsg("Only excel(xlsx, xls) files are allowed", "failure", basename($_SERVER['PHP_SELF']));
		}
	}
   
?>
<style type="text/css">


</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<?php include 'sidebar.php'; ?>	
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <?php if (isset($_POST['status'])): ?>
          <div class="container-fluid">
            <div class="callout <?php echo ($_POST['status']=="success") ? " callout-info bg-info" : " callout-warning bg-warning"; ?>" >
              <button aria-hidden="true" data-dismiss="alert" class="close closeCallout" type="button">×</button>
              <h4><?php echo ($_POST['status']=="success") ? "Success!" : "Uh-oh!"; ?> </h4>
              <p>
                <?php echo $_POST['msg']; ?>              
              </p>
            </div> 
          </div>
        <?php endif ?>

        <?php include 'box_statistics.php'; ?>

        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Automated Remittance Emailer</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Automated Remittance Emailer</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
					<div class="row">
							<div class="col-md-6">
								<div class="card card-primary">
									<div class="card-header bg-primary">
										<h3 class="card-title">Upload Excel File to send emails</h3>
									</div>
									<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
										<div class="card-body">
											<div class="form-group">
												<label for="exampleInputFile">Choose excel file</label>
												<div class="input-group">
													<div class="custom-file">
														<input type="file" class="custom-file-input" id="excelFile" name="excelFile">
														<label class="custom-file-label" for="exampleInputFile">Choose file</label>
													</div>
												</div>
											</div>
										</div>
										<div class="card-footer">
											<button type="submit" class="btn btn-primary" name="excelUpload">Submit</button>
										</div>
									</form>
								</div>
						</div>

						<!-- <div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h3 class="card-title">
										<i class="fas fa-text-width"></i>
										Proper Format to Upload
									</h3>
								</div>
								<div class="card-body">
									<strong>Please Make sure to follow format before uploading file</strong>
									<ul>
											<li>Please arrange columns in excel as per below <br/>
												<table class="table table-bordered table-striped">												
													<tr><th>A</th><th>PO NUMBER</th></tr>	
													<tr><th>B</th><th>EMAIL ADDRESS</th></tr>	
													<tr><th>C</th><th>INVOICE NUMBER</th></tr>	
													<tr><th>D</th><th>INVOICE DATE</th></tr>
													<tr><th>E</th><th>SHIPPING AMOUNT</th></tr>	
													<tr><th>F</th><th>TAX AMOUNT</th></tr>	
													<tr><th>G</th><th>INVOICE TOTAL</th></tr>	
													<tr><th>H</th><th>CURRENCY</th></tr>
												</table>
											</li>
											<li>The number of rows equals to the number of email that will be sent to their corresponding email address in the email column (ie 5 rows will equals 5 emails being sent)</li>
											<li>If an email has multiple invoice number and multiple invoice date please separate them with pipe and space  (ie. "| "   2463635635| 4252654624562  2020/06/01| 2020/07/01 )</li>
									</ul>

								</div>
							</div>
						</div> -->

					</div>
            

						

        </div>        
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

<?php include "footer.php"; ?>

<script type="text/javascript">
  const form = document.querySelector('form');
	form.addEventListener('submit', e => {
		const loadingModal = document.querySelector('#loadingModal');
    loadingModal.style.display = 'block';
	});

</script>
</body>
</html>



