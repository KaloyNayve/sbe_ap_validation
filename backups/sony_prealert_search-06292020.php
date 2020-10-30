<?php include 'header.php'; 
	$qry = "SELECT * FROM SONY_US_PREALERT where country = 'US'";
	$res_keys = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
	$keys = array_keys($res_keys);
	$res = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);
	
?>
<style type="text/css">
	/*.table-wrapper {
		overflow-x: scroll; 
	}*/
	.table-wrapper {
		overflow-x: scroll; 
	}

	.modal {
	    display:    none;
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
	

</style>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <!-- Left side column. contains the logo and sidebar -->
<?php include "sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Sony US Pre alert Search
        <!--<small>In / Out</small>-->
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">SONY US</li> 
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
			<div class="col-md-8">
				<div class="box box-info">
					<div class="box-header with-border">
						<h3 class="box-title">Select data from any of the fields below</h3>
					</div>
					<form method="post" action="actions.php" autocomplete="off">
						<div class="form-group">
							<div class="box-body">
								<div class="form-group">
									<label>Service job number:</label>
									<select class="form-control select2" name="jobnumber" id="jobnumber">
										<option>Select one</option>
										<?php foreach($res as $row) : ?>
											<option value="<?php echo $row['SERVICE_JOB_NUMBER']; ?>"><?php echo $row['SERVICE_JOB_NUMBER']; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="form-group">
									<label>CRM Number:</label>
									<select class="form-control select2" name="crm" id="crm">
										<option>Select one</option>
										<?php foreach($res as $row) : ?>
											<option value="<?php echo $row['CRM_NUMBER']; ?>"><?php echo $row['CRM_NUMBER']; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="form-group">
									<label>IMEI:</label>
									<select class="form-control select2" name="imei" id="imei">
										<option>Select one</option>
										<?php foreach($res as $row) : ?>
											<option value="<?php echo $row['SERIAL_NUMBER']; ?>"><?php echo $row['SERIAL_NUMBER']; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="box-footer">
								<input type="button" class="btn btn-primary" name="search" id="search" value="Search" />
							</div>						
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 "  id="tableDiv">
				<div class="box">
		            <div class="box-header">
		              <h3 class="box-title">Sony US Prealert Records</h3>
		              <div class="box-tools pull-left">
						  <a href="sony_prealert_search/actions.php?downloadResult=1&jobnumber=All" class="btn btn-app download-btn" id="downloadResult"><i class="fa fa-download"></i>Download Result</a>
					  </div>		              
		            </div>
		            <!-- /.box-header -->
		            <div class="box-body table-wrapper">
		              <table class="table table-hover dataTable search-table"  id="datatable_files" >
		              	<thead><tr>
		                  <?php foreach($keys as $i ): ?>
		                  	<th><?php echo $i; ?></th>
		                  <?php endforeach; ?>
		                </tr></thead>
		               <tbody>
		               	<?php foreach($res as $row): ?>
		               		<tr>
		               			<?php for($i = 0;$i < count($keys);$i++): ?>
		               				<td><?php echo $row[$keys[$i]]; ?></td>
		               			<?php endfor; ?>
		               		</tr>
		               	<?php endforeach; ?>
		              </tbody>
		              </table>
		            </div>
		            <!-- /.box-body -->
		          </div>
			</div>
		</div>
		
	 	  
    </section>
  </div>
  <div class="modal"></div>
  <!-- /.content-wrapper -->
 <?php include "widget_footer.php"; ?>
  <!-- /.control-sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<?php include "footer.php"; ?>
<script type='text/javascript'>
	// Loads loading modal at the start of an ajax call
	$(document).on({
	    ajaxStart: function() { $(".modal").css('display', 'block'); },
	     ajaxStop: function() { $(".modal").css('display', 'none'); }    
	});

	
	
	// Importing table columns from php 
	var columns = <?php echo json_encode($keys); ?>;
	
	$("#datatable_files").DataTable();
	
	$("#search").on('click',function(){

		var jobnumber = $("#jobnumber").val();
		var crm = $("#crm").val();
		var imei = $("#imei").val();
		var href = 'sony_prealert_search/actions.php?downloadResult=1&jobnumber=' + jobnumber;
		$("#downloadResult").attr('href',href);
		$("#resultTable").html("");
		// Generating columns from table from keys
		var tableData = "<thead><tr>";
		for(var a = 0;a < columns.length;a++){
			tableData += "<th>" + columns[a] + "</th>";
		}
		tableData += "</tr></thead><tbody><tr>";		
		$.ajax({
			type: "post",
			url: "sony_prealert_search/actions.php",
			data: {
				"search_prealert" : "1",
				"jobnumber" : jobnumber,
				"crm" : crm,
				"imei" : imei
			},
			dataType: "html",
			success: function(data){				
				var res = JSON.parse(data);
				// console.log(res);
				for(var i = 0;i < res.length;i++){
					tableData += "<tr>";
					for(var j = 0;j < columns.length;j++){
						tableData += "<td>" + res[i][columns[j]] + "</td>";
					}
					tableData += "</tr>";
				}
				tableData += "</tbody>";

				$("#datatable_files").html(tableData);
				$("#jobnumber").val("").trigger('change');
				$("#crm").val("").trigger('change');
				$("#imei").val("").trigger('change');
				// $("#resultTable").dataTable();
				// $(document).ready(function(){
				// 	$('#datatable_files').DataTable( {
				//      dom: 'Bfrtip',
				// 	    buttons: [
				// 	        'copy', 'excel', 'pdf', 'csv'
				// 	    ]
				// 	} );
				// });
				
			}
		});
		
	})

</script>
</html>