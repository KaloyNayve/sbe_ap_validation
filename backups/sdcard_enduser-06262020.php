<?php 
	include 'header.php';	
	$parts_array = array(		
		'SF-M64/T2',
		'SF-M128/T2',
		'SF-M256/T2',
		'SF-M64T/T1',
		'SF-M128T/T1',
		'SF-M256T/T1',
		'SF-G32T/T1',
		'SF-G64T/T1',
		'SF-G128T/T1'
	); 

	
?>
<style type="text/css">
	#loadingModal {
	    display:    block;
	    position:   fixed;
	    z-index:    1000;
	    top:        0;
	    left:       0;
	    height:     100%;
	    width:      100%;
	    background: rgba( 255, 255, 255, .8 ) 
	                url('https://i.stack.imgur.com/FhHRx.gif') 
	                50% 50% 
	                no-repeat;
	}

	.input {
		width: 200px;
	}

</style>
<script src='dist/js/jquery-barcode.js'></script>

<script>
	var btype = 'code128';
	var renderer = 'css';
	// console.log(IMEI);
	var settings = {
	  output:renderer,
	  bgColor: '#FFFFFF',
	  color: '#000000',
	  barWidth: 1,
	  barHeight: 22,
	  moduleSize: '#000000',
	  posX: 15,
	  posY: 30,
	  addQuietZone: 1  
	}; 	

	function printLabel(data) {
		const w = 300;
		const h = 800;
		const l = (window.screen.availWidth - w) / 3;
		const t = (window.screen.availHeight - h) / 2;
		$('#dnumber_barcode').show().barcode(data['DISPATCH_NUMBER_1'], btype, settings);
		const barcode = document.getElementById('dnumber_barcode').innerHTML;		
		$('#dnumber_barcode').css('display','none');
		let myTable = `<table style='font-size: 12px;'>`;
		myTable += `<tr><td>${barcode}</td></tr>`;
		myTable += `<tr><td>${data['DISPATCH_NUMBER_1']}</td></tr>`;
		myTable += `<tr><td>Date: ${data['DATE']}</td></tr>`;
		myTable += `<tr><td>Model: ${data['RECEIVED_MODEL_1']}</td></tr>`;
		myTable += `<tr><td>QTY: ${data['TOTAL_QTY_RECEIVED']}</td></tr>`;
		myTable += `<tr><td>WAYBILL: ${data['WAYBILL']}</td></tr>`;
		myTable += `</table>`;
		var sOption = "toolbar=no, location=no, directories=no, menubar=no, header=no, footer=no, scrollbars=no, width=" + w + ",height=" + h + ",left=" + l + ",top=" + t;
		var objWindow = window.open("LabelPrint.aspx", "Print", sOption);			
		objWindow.document.write(myTable);
		objWindow.document.close();
		objWindow.print();
		objWindow.close();
	}
</script>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
  <!-- Left side column. contains the logo and sidebar -->
<?php include "sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Sony SD Card enduser Portal
        <!--<small>In / Out</small>-->
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Sony US</li> 
      </ol>
    </section>
    <!-- Main content -->
    	<?php if(isset($_POST['status'])){ ?>
				<div id="file_updated_box">
						<div class="alert <?php echo ($_POST['status']=="success") ? " alert-success " : " alert-danger "; ?> alert-dismissible file_updated">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<h4><i class="icon fa <?php echo ($_POST['status']=="success") ? " fa-check" : " fa-ban"; ?>"></i> <?php echo $_POST['msg']; ?></h4>
						</div>
				</div>
		 <?php } ?>
   
    <!-- /.content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12">
				<form action="actions.php" method="post" autocomplete="off">
					<input type="hidden" name="page_url" value="<?php echo basename($_SERVER['PHP_SELF']); ?>"> 
					
					<div class="box box-success">
							<div class="box-header with-border">
									<h3 class="box-title">Scan Dispatch Number</h3>
							</div>					
							<div class="box-body">
									<div class="col-lg-12" style="overflow:scroll;">
											<table class="table table-hover table-bordered">
													<thead>
															<tr>
																<th>#</th>
																<th>Dispatch Number</th>
																<th>CRM Number/Alt Ref</th>
																<th>First Name</th>
																<th>Last Name</th>
																<th>Model Number</th>
																<th>S/N</th>
																<th>Model Received</th>
																<th>Exchanged?</th>
																<th>Qty Received</th>
																<th>Qty Shipped</th>
																<th>Qty upgraded</th>
																<th>Upgraded Part</th>
															</tr>
													</thead>
													<tbody>
														<tr>
															<td>1.</td>
															<td>
																<input type="text" data-scope='d1' class="input form-control " data-id = '1' name="dispatch_number_1" id="dispatch_number_1" >
															</td>
															<td>
																<input type="text" class="input form-control d1" data-id = '1' name="alt_ref_1" id="alt_ref_1"  readonly>
															</td>
															<td>
																<input type="text" class="input form-control d1" data-id = '1' name="cust_first_name_1" id="cust_first_name_1"  readonly>
															</td>
															<td>
																<input type="text" class="input form-control d1" data-id = '1' name="cust_last_name_1" id="cust_last_name_1"  readonly>
															</td>
															<td>
																<input type="text" class="input form-control d1" data-id = '1' name="model_1" id="model_1" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d1" data-id = '1' name="serial_number_1" id="serial_number_1"  >
															</td>
															<td>
																<select name="received_model_1" id="received_model_1" class="input form-control d1 received_model" data-id="1">
																		<option></option>
																		<?php foreach($parts_array as $part): ?>
																			<option value="<?php echo $part; ?>"><?php echo $part; ?></option>
																		<?php endforeach; ?>																	
																	</select>
															</td>
															<td>																
																<select name="exchanged_1" id="exchanged_1 " class="input form-control d1 exchanged" data-id='1' >
																	<option></option>
																	<option value="nff">nff</option>
																	<option value="exchanged">exchanged</option>
																</select>
															</td>
															<td>
																<input type="number" class="input form-control d1 received" min="0" step="1"  name="qty_received_1" id="qty_received_1" >
															</td>
															<td>
																<input type="number" class="input form-control d1" min="0" step="1" max="1"  name="qty_shipped_1" id="qty_shipped_1" >
															</td>
															<td>
																<input type="number" class="input form-control d1 upgrade" data-id="1" min="0" step="1" max="1"   name="qty_upgraded_1" id="qty_upgraded_1" >
															</td>
															<td>
																<select name="upgraded_part_1" id="upgraded_part_1" class="input form-control d1">
																	<option></option>																																		
																</select>
															</td>
														</tr>
														<tr>
															<td>2.</td>
															<td>
																<input type="text"  data-scope='d2' class="input form-control" data-id = '2' name="dispatch_number_2" id="dispatch_number_2" >
															</td>
															<td>
																<input type="text" class="input form-control d2" data-id = '2' name="alt_ref_2" id="alt_ref_2" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d2" data-id = '2' name="cust_first_name_2" id="cust_first_name_2" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d2" data-id = '2' name="cust_last_name_2" id="cust_last_name_2" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d2" data-id = '2' name="model_2" id="model_2" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d2" data-id = '2' name="serial_number_2" id="serial_number_2" >
															</td>
															<td>
																<select name="received_model_2" id="received_model_2" class="input form-control d2 received_model" data-id="2">
																		<option></option>
																		<?php foreach($parts_array as $part): ?>
																			<option value="<?php echo $part; ?>"><?php echo $part; ?></option>
																		<?php endforeach; ?>																	
																	</select>
															</td>
															<td>
															<select name="exchanged_2" id="exchanged_2 " class="input form-control d2 exchanged" data-id='2' >
																	<option></option>
																	<option value="nff">nff</option>
																	<option value="exchanged">exchanged</option>
																</select>
															</td>
															<td>
																<input type="number" class="input form-control d2 received" min="0" step="1"  name="qty_received_2" id="qty_received_2" >
															</td>
															<td>
																<input type="number" class="input form-control d2" min="0" step="1" max="1"   name="qty_shipped_2" id="qty_shipped_2" >
															</td>
															<td>
																<input type="number" class="input form-control d2 upgrade" min="0" step="1" max="1"   name="qty_upgraded_2" id="qty_upgraded_2" data-id="2">
															</td>
															<td>
																<select name="upgraded_part_2" id="upgraded_part_2" class="input form-control d2" >
																	<option></option>																	
																</select>
															</td>
														</tr>
														<tr>
															<td>3.</td>
															<td>
																<input type="text"  data-scope='d3' class="input form-control" data-id = '3' name="dispatch_number_3" id="dispatch_number_3" >
															</td>
															<td>
																<input type="text" class="input form-control d3" data-id = '3' name="alt_ref_3" id="alt_ref_3" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d3" data-id = '3' name="cust_first_name_3" id="cust_first_name_3" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d3" data-id = '3' name="cust_last_name_3" id="cust_last_name_3" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d3" data-id = '3' name="model_3" id="model_3" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d3" data-id = '3' name="serial_number_3" id="serial_number_3" >
															</td>
															<td>
																<select name="received_model_3" id="received_model_3" class="input form-control d3 received_model" data-id="3">
																		<option></option>
																		<?php foreach($parts_array as $part): ?>
																			<option value="<?php echo $part; ?>"><?php echo $part; ?></option>
																		<?php endforeach; ?>																	
																	</select>
															</td>
															<td>
																<select name="exchanged_3" id="exchanged_3 " class="input form-control d3 exchanged" data-id='3' >
																	<option></option>
																	<option value="nff">nff</option>
																	<option value="exchanged">exchanged</option>
																</select>
															</td>
															<td>
																<input type="number" class="input form-control d3 received" min="0" step="1"  name="qty_received_3" id="qty_received_3" >
															</td>
															<td>
																<input type="number" class="input form-control d3" min="0" step="1" max="1"   name="qty_shipped_3" id="qty_shipped_3" >
															</td>
															<td>
																<input type="number" class="input form-control d3 upgrade" data-id="3" min="0" max="1"  step="1"  name="qty_upgraded_3" id="qty_upgraded_3" >
															</td>
															<td>
																<select name="upgraded_part_3" id="upgraded_part_3" class="form-control d3">
																	<option></option>																	
																</select>
															</td>
														</tr>
														<tr>
															<td>4.</td>
															<td>
																<input type="text"  data-scope='d4' class="input form-control" data-id = '4' name="dispatch_number_4" id="dispatch_number_4" >
															</td>
															<td>
																<input type="text" class="input form-control d4" data-id = '4' name="alt_ref_4" id="alt_ref_4" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d4" data-id = '4' name="cust_first_name_4" id="cust_first_name_4" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d4" data-id = '4' name="cust_last_name_4" id="cust_last_name_4" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d4" data-id = '4' name="model_4" id="model_4" readonly>
															</td>
															<td>
																<input type="text" class="input form-control d4" data-id = '4' name="serial_number_4" id="serial_number_4" >
															</td>
															<td>
																<select name="received_model_4" id="received_model_4" class="input form-control d4" data-id="4">
																		<option></option>
																		<?php foreach($parts_array as $part): ?>
																			<option value="<?php echo $part; ?>"><?php echo $part; ?></option>
																		<?php endforeach; ?>																	
																	</select>
															</td>
															<td>
																<select name="exchanged_4" id="exchanged_4 " class="input form-control d4 exchanged received_model" data-id='4'>
																	<option></option>
																	<option value="nff">nff</option>
																	<option value="exchanged">exchanged</option>
																</select>
															</td>
															<td>
																<input type="number" class="input form-control d4 received" min="0" step="1"  name="qty_received_4" id="qty_received_4" >
															</td>
															<td>
																<input type="number" class="input form-control d4" min="0" step="1" max="1"   name="qty_shipped_4" id="qty_shipped_4" >
															</td>
															<td>
																<input type="number" class="input form-control d4 upgrade" data-id="4" max="1"  min="0" step="1"  name="qty_upgraded_4" id="qty_upgraded_4" >
															</td>
															<td>
																<select name="upgraded_part_4" id="upgraded_part_4" class="input form-control d4">
																	<option></option>
																	
																</select>
															</td>
														</tr>
													</tbody>
											</table>
									</div> 
									<div class="col-md-6">
											<div class="form-group">
													<label>Waybill:</label>
													<input type="text" class="form-control" name="waybill" id="waybill"  required>
											</div>
											<div class="form-group">
												<label>Comments:</label>
												<textarea class="form-control" name="comments" id="comments"></textarea>
											</div>
											
									</div>       
							</div>
							<div class="box-footer">
									<button type="submit" class="btn btn-primary pull-right" name="sdcard_submit">Submit</button>
							</div>				
					</div>
			</form>
			</div>
		</div>
		
		<div id="dnumber_barcode" style="visibility: hidden"> </div>
		<div id="loadingModal"></div>  
    </section>
  </div>
  <!-- /.content-wrapper -->
 <?php include "widget_footer.php"; ?>
  <!-- /.control-sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<?php include "footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script type='text/javascript'>
	// async function get() {
	// 	const result = await $.ajax({
	// 		type: "post",
	// 		url: "actions.php",
	// 		data: {
	// 			'example': '1'				
	// 		}			
	// 	});

	// 	return result;
	// }

	// get().then( (data) => {
	// 	const res = JSON.parse(data);
	// 	printLabel(res);
	// });


	const dispatchArr = [];

	// Loads loading modal at the start of an ajax call
	const loadingModal = document.querySelector('#loadingModal');

	$(document).on({		
	    ajaxStart: function() { loadingModal.style.display = 'block'; },
	     ajaxStop: function() { loadingModal.style.display = 'none'; }    
	});

	// Removes loading modal when page is ready
    document.onreadystatechange = function() {
      if (document.readyState == "complete") {
        const loadingModal = document.querySelector('#loadingModal');
        loadingModal.style.display = 'none';
      }
    }

	// 	Prevent Submit on Enter
	const form = document.querySelector('form');
	form.addEventListener('keydown', e => {
		if (e.key === 'Enter') {
			e.preventDefault();			
		}
	});	

	// add on submit handler to form
	function submitHandler(event) {
		// check if qty quantity is submitted with 0
		const qty_received = document.querySelectorAll('.received');
		// array filter to check if input is enabled		
		const inputs_enabled = Array.from(qty_received).filter(q => q.disabled === false);
		// use reduce to see if active qty received has a value of < 0
		const validator = inputs_enabled.reduce(function(acc, i) {
			if (i.value < 1) {
				return acc + 1;
			}
			return acc;
		}, 0);
		if (validator > 0) {
			alert("Quantity Received cannot be 0, please check again");
			event.preventDefault();
		} else {
			const loadingModal = document.querySelector('#loadingModal');
			loadingModal.style.display = 'block';
		}

		
	}

	form.addEventListener('submit', submitHandler);

	 // get all inputs	
   const readonly = document.querySelectorAll(".d1, .d2, .d3, .d4");
	 // make inputs readonly and disable
	 readonly.forEach((r) =>  r.disabled = true);

	 function getData(event) {
		 const input = event.currentTarget;
		 const dispatch_number = input.value;
		 const id = input.dataset.id; // get id		
		 const scope = input.dataset.scope;
		 const inputs = document.querySelectorAll(`.${scope}`); // get all in scope 
		 if (dispatch_number.length === 11 && !dispatchArr.includes(dispatch_number)) {
			 // ajax call to get information on scan
			 $.ajax({
					type: "post",
					url: "actions.php",
					data: {
						'getPreAlertData': '1',
						'dnumber' : dispatch_number
					},
					success: function(data){			
						// if return is not found show error
						if (data === "not found") {
							alert("dispatch number not found, please check again");
						} else {
							const result = JSON.parse(data);
							console.log(result);
							// enable the inputs		
							inputs.forEach((i) => {
								if (i.name !== `upgraded_part_${id}` && i.name !== `qty_received_${id}` && i.name !== `qty_shipped_${id}` && i.name !== `qty_upgraded_${id}`) {
									i.disabled = false;
									i.required = true;
								}	
							});
							// populate data							
							document.querySelector(`#alt_ref_${id}`).value = result.CRM_NUMBER; // populate alt ref
							document.querySelector(`#cust_first_name_${id}`).value = result.CONSUMER_FIRST_NAME; // populate cust first name
							document.querySelector(`#cust_last_name_${id}`).value = result.CONSUMER_LAST_NAME; // populate cust last name
							document.querySelector(`#model_${id}`).value = result.MODEL_NUMBER; // populate cust last name
							document.querySelector(`#serial_number_${id}`).value = result.SERIAL_NUMBER; // populate cust last name
							//set qty as value 0
							const qtys = document.querySelectorAll(`#qty_received_${id}, #qty_shipped_${id}, #qty_upgraded_${id}`); // capture qtys
							qtys.forEach((qty) => qty.value = 0); //set as value
							dispatchArr.push(dispatch_number);
						}
					}
				});
		 } else {
			 console.log('already scanned');
			 input.value = "";
		 }		
	 } 

	 // add change listener to exchanged select statement
	 const exchanged = document.querySelectorAll('.exchanged');
	 exchanged.forEach(ex => ex.addEventListener('change', e => {
		const id = e.currentTarget.dataset.id; // get id number
		const needToBeDisabled = document.querySelectorAll(`#qty_received_${id}, #qty_shipped_${id}, #qty_upgraded_${id}`);
		const upgradeParts = document.querySelector(`#upgraded_part_${id}`);
		 if (e.currentTarget.value === 'nff') {	
			 // disable and set as not required	 
			 needToBeDisabled.forEach(n => {
				if (n.disabled === false) {
					n.disabled = true;
					n.required = false;
				}
			 });
			 // check if upgraded part is disabled if so disabled it
			 if (upgradeParts.disabled === false) {
					upgradeParts.disabled = true;
					upgradeParts.required = false;
			 }
		 } else {
			 // enable and set as required
			 needToBeDisabled.forEach(n => {
				if (n.disabled === true) {
					n.disabled = false;
					n.required = true;
				}
			 });
			 // check if upgraded part is enabled if so disabled it
			 if (upgradeParts.disabled === false) {
					upgradeParts.disabled = false;
					upgradeParts.required = true;
			 }
		 }
	 }));

	 // add change listener to upgrade quantity to enable the upgraded parts select
	 const upgrades = document.querySelectorAll(".upgrade");
	 upgrades.forEach(up => up.addEventListener('change', e => {
		 const id = e.currentTarget.dataset.id;
		 const value = e.currentTarget.value;
		 const el = document.querySelector(`#upgraded_part_${id}`);		 
		 if (value > 0) {
				el.disabled = false;
				el.required = true;
		 } else {
			 el.disabled = true;
			 el.required = false;
		 }
	 }));

	 // add change listener to received model to get parts eligeble for upgrade
	 const received_model = document.querySelectorAll('.received_model');
	 received_model.forEach(rm => rm.addEventListener('change', e => {
		 const id = e.currentTarget.dataset.id;
		 const value = e.currentTarget.value;
		 const el = document.querySelector(`#upgraded_part_${id}`);	
		 $.ajax({
			type: "post",
			url: "actions.php",
			data: {
				'getUpgradedParts': '1',
				'base_model' : value
			},
			success: function(data){
				const res = JSON.parse(data); //json parse the data
				let html = `<option></option>`;
				for(let i = 0;i < res.length;i++) {
					
					html += `<option value="${res[i].UPGRADE_MODEL}">${res[i].UPGRADE_MODEL}</option>`;
				}
				el.innerHTML = html;
			}	
		 })
	 }));

	 

	 const dispatchNumberInputs = document.querySelectorAll('[data-scope]');
	 dispatchNumberInputs.forEach(d => {
		 // adding event listeners
		d.addEventListener('change', getData);
		// d.addEventListener('keyup', e => {
		// 	if (e.key === 'Enter') {
		// 		// e.preventDefault();
		// 		getData(e);
		// 	}
		// });
	 });

	 

</script>
</html>