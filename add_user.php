<?php 
  include 'header.php'; 
	
  if (strtolower($access) !== 'admin') {
	//You need to redirect	
	redirect("index.php");
  } 
?>
<style type="text/css">
	tr.tabledit-deleted-row.text-muted {
		display: none;
	}

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
            <h1 class="m-0 text-dark">Add User Access</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Settings</li>
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
									<div class="card card-default">
											<div class="card-header bg-primary">
													<h3 class="card-title">Select User to add</h3>													
											</div>
											<!-- /.card-header -->
											<form action="actions.php" method="post">
													<div class="card-body">
															<div class="row">
																	<div class="container-fluid">
																			<div class="form-group">
																					<label>User: </label>
																					<select class="form-control select2bs4" style="width: 100%;" id="user" name="user" required>
																							<option></option>
																							<?php $qry = "SELECT Distinct d.Badge,d.First_Name,d.Last_Name, u.login 
																																			from Dir_Indir d
																																			LEFT OUTER JOIN utilisateurs u
																																			on d.badge = u.numbadge
																																			where d.Date_Ins=(select max(Date_Ins) from Dir_Indir) 
																																			order by d.First_Name ASC"; ?>
																							<?php foreach($conn->query($qry) as $row): ?>
																									<?php $employee = $row['BADGE'] . "-" . $row['FIRST_NAME'] . "-" . $row['LAST_NAME']; ?>
																									<option value="<?php echo $employee . "-" . $row['LOGIN']; ?>"><?php echo $employee; ?></option>
																							<?php endforeach; ?>												
																					</select>
																			</div>

																			<div class="form-group">
																					<label>Access level:</label>
																					<select name="access_level" id="access_level" class="form-control select2bs4" required>
																									<option></option>
																									<option value="admin">Admin</option>
																									<option value="ap">AP</option>
																									<option value="approver">Approver</option>																									
																									<option value="accounting">Accounting</option>
																									<option value="user">User</option>
																					</select>
																			</div>
																	</div>
																	
															</div>
															<!-- /.row -->
													</div>
													<!-- /.card-body -->
													<div class="card-footer">
															<input type="hidden" name="page_url" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
															
															<button type="submit" class="btn btn-primary pull-right" name="addUser">Submit</button>
													</div>
											</form>
											
									</div>	
							</div> 
							<div class="col-md-6">
								<div class="card">
										<div class="card-header bg-primary">
											<h3 class="card-title">Ap Validation Portal Users</h3>														
										</div>
										<div class="card-body">
											<table id="ap_validation_users_table" class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>Badge</th>
															<th>Username</th>
															<th>Name</th>
															<th>Profile</th>	
														</tr>
													</thead>
													<tbody>
														<?php $qry = "SELECT * FROM AP_VALIDATION_USERS WHERE DELETED is null"; ?>
														<?php foreach($conn->query($qry) as $row): ?>
															<tr id="<?php echo $row['BADGE']; ?>" data-access = "<?php echo $row['ACCESS_LEVEL']; ?>">
																<td><?php echo $row['BADGE']; ?></td>
																<td><?php echo strtolower(ucwords($row['UNAME'])); ?></td>
																<td><?php echo ucwords($row['FIRST_NAME']) . ' ' . ucwords($row['LAST_NAME']); ?></td>
																<td><?php echo ucwords($row['ACCESS_LEVEL']); ?></td>
															</tr>
														<?php endforeach; ?>	
													</tbody>											
											</table>
										</div>                                     
								</div>                                 
							</div> 
					</div>
			<!-- SELECT2 EXAMPLE -->
            
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
<script src="plugins/jquery-tabledit/jquery.tabledit.custom.js"></script>
<script type="text/javascript">
  const form = document.querySelector('form');
	form.addEventListener('submit', e => {
		const loadingModal = document.querySelector('#loadingModal');
	loadingModal.style.display = 'block';
	});

	//Initialize Select2 Elements
	$('.select2bs4').select2({
		theme: 'bootstrap4'
	});

	// datable initialization
	$("#ap_validation_users_table").DataTable();


	$('#ap_validation_users_table').Tabledit({
	    url: 'actions.php',	    
	    editButton: true,
	    restoreButton: false,
	    columns: {
	        identifier: [0, 'badge'],
	        editable: [ [3, 'access_level', '{"admin": "Admin", "ap":"AP", "approver": "Approver", "accounting": "Accounting", "user": "User"}']]
			}
	});

	

</script>
</body>
</html>



