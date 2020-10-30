<?php include "header.php"; ?> 
<?php 
	$allowed_badge = array('100694','100573', '106433', '103876' , '100502'); 
	$newQuery = new QueryBuilder("tradein_parts_selloff");

	$jobnumbers = $conn->query($newQuery->selectDb("distinct jobnumber"))->fetchAll(PDO::FETCH_ASSOC);
	
 ?>
<style type="text/css">
	.reprint-panel{
		position: absolute;
		
	}

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
        Parts Auction Screening 
        <!--<small>In / Out</small>-->
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Screening</li>
      </ol>
    </section>
	    <!-- Main content -->
   
    <!-- /.content -->
	
		 <?php if(isset($_POST['status'])){ ?>
				<div id="file_updated_box" >
						<div class="alert <?php echo ($_POST['status']=="success") ? " alert-success " : " alert-danger "; ?> alert-dismissible file_updated">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<h4><i class="icon fa <?php echo ($_POST['status']=="success") ? " fa-check" : " fa-ban"; ?>"></i> <?php echo $_POST['msg']; ?></h4>
						</div>
				</div>
	  <?php } ?>

	

    <section class="content">
    	


		<div class="row">
			<div class="col-lg-8">
				
				<div class="box box-primary">		 
		            <div class="box-header with-border">
		              <h3 class="box-title">Scan Part Number</h3>
		              <div class="box-tools pull-right">
						  <a href="actions.php?downloadPartsAuction=1" class="btn btn-app" <?php  echo (in_array($sbegn_badge, $allowed_badge)) ? "style='display:block;width:100%'" : "style='display:none;width:100%;'" ?>><i class="fa fa-download"></i> Download Parts Auction Report</a>
					  </div>
		            </div>
		            <!-- /.box-header -->
		            <!-- form start -->
		            <form method="post" action="actions.php" id="partsAuctionsForm" autocomplete="off" onsubmit="return confirm('Do you confirm that the information is correct?');">
		              <div class="box-body">
		                <div class="form-group" id="partNumber">
		                  <label for="part_number">Part Number:</label>
		                  <input class="form-control" id="part_number" name="part_number" type="text" placeholder="coderef" required>
		                  <input class="form-control" id="type" name="type" type="hidden">
		                  <input class="form-control" id="oem" name="oem" type="hidden">
		                  <input class="form-control" id="model" name="model" type="hidden">                                  
		                </div>

		              <div class="form-group" id="jbnumDiv" style="display:none;">
		              	<label>Jobnumber:</label>	
		              	<input class="form-control" type="text" name="jobnumber" id="jobnumber" placeholder="scan jobnumber">
		              </div>

		            <div class="form-group" id="device_verification" style="display: none;">           	
					  <label class="control-label" id="device_verification_label">Is part actually a ?</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="device_verification_yes" class="radio" name="device_verify" value="yes" type="radio" required>
						      yes</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="device_verification_no" class="radio" name="device_verify" value="no" type="radio" required>
						      no</label>
						    </div>	    
						    
					   </div>
					</div>

					<div class="form-group" id="case_verification" style="display: none;">
					  <label class="control-label" id="case_verification_label">Case condition?</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="case_verification_yes" class="radio" name="case_verify" value="ls" type="radio" >
						      Light Scratches</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="case_verification_no" class="radio" name="case_verify" value="ds/cd" type="radio" >
						      Deep Scratches/Cracked Display</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="iphone7_scratches" style="display: none;">
					  <label class="control-label" id="iphone7_scratches_label">light scratches:</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="iphone7_scratches_a" name="iphone7_scratches_verify" value="less_than_7" type="radio" class="radio" >
						      less than 7 scratches?</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="iphone7_scratches_b" name="iphone7_scratches_verify" value="more_than_7" type="radio" class="radio" >
						      more than 7 scratches?</label>
						    </div>			    
						    
					   </div>
					</div>




					
					

					<div class="form-group" id="broken_verification" style="display: none;">
					  <label class="control-label">Is the part broken?</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="broken_verification_yes" name="broken_verify" value="yes" type="radio" class="radio">
						      yes</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="broken_verification_no" name="broken_verify" value="no" type="radio" class="radio">
						      no</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="ear_piece_flex_verification" style="display: none;">
					  <label class="control-label">Is ear piece flex missing?</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="ear_piece_flex_verification_missing" name="earPiece_verify" value="missing" type="radio" class="radio">
						      missing</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="ear_piece_flex_verification_not_missing" name="earPiece_verify" value="not-missing" type="radio" class="radio">
						      not missing</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="display_flex_verification" style="display: none;">
					  <label class="control-label">Is display flex missing?</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="display_flex_verification_broken" name="display_flex_verify" value="broken" type="radio" class="radio">
						      broken</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="display_flex_verification_not_broken" name="display_flex_verify" value="not-broken" type="radio" class="radio">
						      not broken</label>
						    </div>			    
						    
					   </div>
					</div>



					<div class="form-group" id="functional_verification" style="display: none;">
					  <label class="control-label">NFF/Faulty?</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="functional_verification_yes" name="functional_verify" value="nff" type="radio" class="radio">
						      NFF</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="functional_verification_no" name="functional_verify" value="faulty" type="radio" class="radio">
						      Faulty</label>
						    </div>			    
						    
					   </div>
					</div>

						  

					<div class="form-group" id="not_broken_condition_verification" style="display: none;">
					  <label class="control-label">Select condition of display:</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="condition_verification_ls" name="not_broken_condition_verify" value="ls" type="radio" class="radio">
						      light scratches</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="condition_verification_ds" name="not_broken_condition_verify" value="ds" type="radio" class="radio">
						      deep scratches/cracks</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="condition_verification_fs" name="not_broken_condition_verify" value="fs" type="radio" class="radio">
						      frame scratches</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="condition_verification_fs" name="not_broken_condition_verify" value="sd" type="radio" class="radio"> 
						      shattered display</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="broken_condition_verification" style="display: none;">
					  <label class="control-label">Select condition of display:</label>
					   <div >
					   		<div class="radio">
						     <label>
						      <input id="condition_verification_ds" name="broken_condition_verify" value="ds" type="radio" class="radio">
						      deep scratches/cracks</label>
						    </div>
						    
						    <div class="radio">
						     <label>
						      <input id="condition_verification_fs" name="broken_condition_verify" value="sd" type="radio" class="radio">
						      shattered display</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="apple_ls_questions" style="display: none;">
					  <label class="control-label">light scratches condition:</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="apple_ls_questions_lessthan4" name="apple_ls_questions" value="less_than_4" type="radio" class="radio">
						      less than four scratches</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="apple_ls_questions_morethan4" name="apple_ls_questions" value="more_than_4" type="radio" class="radio">
						      more than four scratches</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="samsung_ls_questions" style="display: none;">
					  <label class="control-label">Samsung light scratches condition:</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="samsung_ls_questions_lessthan7" name="samsung_ls_questions" value="less_than_4" type="radio" class="radio">
						      less than four scratches</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="apple_ls_questions_morethan4" name="samsung_ls_questions" value="more_than_4" type="radio" class="radio">
						      more than four scratches</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="samsung_fs_questions" style="display: none;">
					  <label class="control-label">Samsung frame scratches condition:</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="samsung_ls_questions_lessthan7" name="samsung_fs_questions" value="less_than_7" type="radio" class="radio">
						      less than seven scratches</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="apple_ls_questions_morethan7" name="samsung_fs_questions" value="more_than_7" type="radio" class="radio">
						      more than seven scratches</label>
						    </div>			    
						    
					   </div>
					</div>

					<div class="form-group" id="functional_condition" style="display: none;">
					  <label class="control-label">Select the functional Condition</label>
					   <div >
						    <div class="radio">
						     <label>
						      <input id="functional_condition_1" name="burnt_display" value="yes" type="checkbox">
						      Burnt Display</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="functional_condition_2" name="dead_pixel" value="yes" type="checkbox">
						      Dead Pixel</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="functional_condition_3" name="1d_touch" value="yes" type="checkbox">
						      No 1D Touch</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="functional_condition_4" name="3d_touch" value="yes" type="checkbox">
						      No 3D Touch</label>
						    </div>
						    <div class="radio">
						     <label>
						      <input id="functional_condition_5" name="no_power" value="yes" type="checkbox">
						      No power</label>
						    </div>				    
						    
					   </div>
					</div>

		            </div>
		              <!-- /.box-body -->
		            <div class="box-footer">
		                <input type="submit" class="btn btn-primary" name="partAuctionSubmit" id="partAuctionSubmit" value="Submit" style="display:none;" />
		              </div>
		            </form>
		          </div> 

			</div>

			<div class="col-lg-4" id="info-panel">
				
				<div class="box box-primary">
					<div class="box-header with-border">
		              <h3 class="box-title">Destination Details</h3> 
		            </div>
		            <table class='table table-bordered table-striped' >
		            	<tbody>
		            		<tr>
		            			<?php if($_POST['destination'] === '96'){
		            						$destination = "Site 96 Buff and Polish";
		            					} elseif($_POST['destination'] === 'SO'){
		            						$destination = "Selloff";
		            					} elseif($_POST['destination'] === 'SO-B') {
		            						$destination = "Selloff - Broken Device";	
		            					} elseif($_POST['destination'] === 'recovery') {
		            						$destination = "recovery";
		            					} else {
		            						$destination = "";
		            					}
		            			?>
		            			<td>Destination:</td>
		            			<td><?php echo isset($_POST['destination']) ? $_POST['destination'] : ''; ?></td>	            			
		            		</tr>		            		
		            		<tr>
		            			<td>Box:</td>
								<?php $box = ltrim($_POST['box'], '0'); //trim the leading zeroes in box ?>
		            			<td><?php echo isset($_POST['box']) ? $box : ''; ?></td>
		            		</tr>
		            		<tr>
		            			<td>Position:</td>
		            			<td><?php echo isset($_POST['position']) ? $_POST['position'] : ''; ?></td>		            			
		            		</tr>
		            		<tr>
		            			<td>Oem:</td>
		            			<td><?php echo isset($_POST['oem']) ? $_POST['oem'] : ''; ?></td>		            			
		            		</tr>
		            		<tr>
		            			<td>Model:</td>
		            			<td><?php echo isset($_POST['model']) ? $_POST['model'] : ''; ?></td>		            			
		            		</tr>
		            		<tr>
		            			<td>Jobnumber:</td>
		            			<td><?php echo isset($_POST['jobnumber']) ? $_POST['jobnumber'] : ''; ?></td>		            			
		            		</tr>
		            		<tr>
		            			<td>Type:</td>
		            			<td><?php echo isset($_POST['type']) ? $_POST['type'] : ''; ?></td>	            			
		            		</tr>
		            		<tr>
		            			<td>Part Ref:</td>
		            			<td><?php echo isset($_POST['part_ref']) ? $_POST['part_ref'] : ''; ?></td>	            			
		            		</tr>
		            		<tr>
		            			<td>is device broken?</td>
		            			<td><?php echo isset($_POST['broken']) ? $_POST['broken'] : ''; ?></td>            			
		            		</tr>

		            		<tr>
		            			<td>is device functional?</td>
		            			<td><?php echo isset($_POST['functional']) ? $_POST['functional'] : ''; ?></td>            			
		            		</tr>
		            		<tr>
		            			<td>Ear Piece Flex</td>
		            			<td><?php echo isset($_POST['ear_piece_flex']) ? $_POST['ear_piece_flex'] : ''; ?></td>
		            		</tr>
		            		<tr>
		            			<td>Display Flex</td>
		            			<td><?php echo isset($_POST['display_flex']) ? $_POST['display_flex'] : '';?></td>
		            		</tr>
		            		<tr>
		            			<td>burnt display:</td>
		            			<td><?php echo isset($_POST['burnt_display']) ? $_POST['burnt_display'] : ''; ?></td>		            			
		            		</tr>
		            		<tr>
		            			<td>Dead Pixel:</td>
		            			<td><?php echo isset($_POST['dead_pixel']) ? $_POST['dead_pixel'] : ''; ?></td>	            			
		            		</tr>
		            		<tr>
		            			<td>No 1D Touch:</td>
		            			<td><?php echo isset($_POST['oned_touch']) ? $_POST['oned_touch'] : ''; ?></td>            			
		            		</tr>
		            		<tr>
		            			<td>No 3D Touch:</td>
		            			<td><?php echo isset($_POST['threed_touch']) ? $_POST['threed_touch'] : ''; ?></td>            			
		            		</tr>
		            		<tr>
		            			<td>No Power:</td>
		            			<td><?php echo isset($_POST['power']) ? $_POST['power'] : ''; ?></td>
		            		</tr>					            							            		
		            		
		            	</tbody>
		            </table>
				</div>
			</div>
		</div>

		
		<div class="col-md-4 reprint-label pull-right">
			<div class="box box-success">
				<div class="box-header with-border">
	              <h3 class="box-title">Reprint Label</h3>
	            </div>

	            <div class="box-body">
	            	<div class="form-group">
	            		<label>Enter Jobnumber:</label>
	            		<input type = "text" class="form-control" id="reprint_jobnumber" name="reprint_jobnumber">
	            	</div>

	            	<div class="form-group">
	            		<label>Enter CodeRef:</label>
	            		<input type = "text" class="form-control select2" id="reprint_coderef" name="reprint_coderef">	            		
	            	</div>

	            	<button class="btn btn-primary" name="reprint-button" id="reprint-button">Reprint Label</button>
	            </div>
			</div>
		</div>
		
		
		 
	    <input type="hidden" id="refurb" name="refurb">
		<div id="loadingModal"></div>   
    </section>



  </div>
  <!-- /.content-wrapper -->
 <?php include "widget_footer.php"; ?>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<?php include "footer.php"; ?>
<script type="text/javascript" src="js/parts_auction.js"></script>
<?php 
	if(isset($_POST['destination'])){ 
		$box = ltrim($_POST['box'], '0');
		echo '<script type="text/javascript">',
			 '$(document).ready(function(){',
			 	'var w = 300;',
			 	'var h = 500;',
			 	'var l = (window.screen.availWidth - w) / 3;',
			 	'var t = (window.screen.availHeight - h) / 2;',
			 	'var myTable = "<table>";',
			 	'myTable +="<tr><td><strong>Destination:</strong></td><td>' . $_POST['destination'] . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>Box:</strong></td><td>' . $box . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>Position:</strong></td><td>' . $_POST['position'] . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>OEM:</strong></td><td>' . $_POST['oem'] . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>Model:</strong></td><td>' . $_POST['model'] . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>Jobnumber:</strong></td><td>' . $_POST['jobnumber'] . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>Part Reference:</strong></td><td>' . $_POST['part_ref'] . '</td></tr>"; ',
			 	'myTable +="<tr><td><strong>Functional:</strong></td><td>' . $_POST['functional'] . '</td></tr>"; ',
			 	'myTable += "</table>";',
			 	'var sOption = "toolbar=no, location=no, directories=no, menubar=no, header=no, footer=no, scrollbars=no, width=" + w + ",height=" + h + ",left=" + l + ",top=" + t;',
			 	'var objWindow = window.open("LabelPrint.aspx", "Print", sOption);',
			 	'objWindow.document.write(myTable);',
		     	'objWindow.document.close();',
		     	'objWindow.print();',
		     	'objWindow.close();',
		     '});',	
		     '</script>'
		;
	}

 ?>
</html>