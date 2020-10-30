<?php 
  include 'header.php'; 
  // Get to be received documents
  $documents = getDocuments("to be received");
  
?>
<style type="text/css">
  #documentViewer {
    height: 600px !important;
  }

  .selected-row {
    color: #212529;
    background-color: rgba(0,0,0,.075);
  }

  .modal-backdrop {
     background-color: transparent !important;
  }

  .modal-dialog {
    position: absolute;
    top: 0;
    /*right: 0;*/
    bottom: 0;
    left: 100px;
    z-index: 10040;
    
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
            <h1 class="m-0 text-dark">Documents to be received</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Documents to be recieved</li>
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
          <div class="col-lg-6">            

            <!-- Document table -->
            <div class="card">
              <div class="card-header border-transparent">
                <!-- <h3 class="card-title">RECEIVE DOCUMENTS</h3> -->
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                 <br>
                 <button class="btn3d btn btn btn-outline-primary btn-sm" onclick="hold();"><i class="fas fa-pause-circle"></i> Hold Selected</button>

                 <button class="btn3d btn btn-outline-primary btn-sm" onclick="del();"><i class="fas fa-minus-circle"></i> Delete Selected</button>

                 <button class="btn3d btn btn-outline-success btn-sm" onclick="receive();"><i class="fas fa-file-download"></i> Receive Selected Document</button>

              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover m-0" id="documentTable">
                    <thead>
                    <tr>                      
                      <th>Index</th>
                      <th>Company</th>
                      <th>Document Type</th>
                      <th>Sender</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      <?php if ($documents): ?>
                        <?php $ctr = "1"; ?>
                        <?php foreach ($documents as $row): ?>
                        <tr oncontextmenu="return false;" class="tableBodyRow" data-filename="<?php echo $row['RENAMED_ATTACHED_FILE'] ?>" id="<?php echo $row['ID']; ?>">
                          
                          <td id="index_<?php echo $ctr; ?>">
                            <?php echo $row['ID']; ?>
                          </td>
                          <td id="company_<?php echo $ctr; ?>">
                            <?php echo $row['COMPANY']; ?>
                          </td>
                          <td id="type_<?php echo $ctr; ?>">
                            <?php echo $row['DOCUMENT_TYPE'] ?>
                          </td>
                          <td id="sender_<?php echo $ctr; ?>">
                            <?php echo $row['SENDER_EMAIL']; ?>
                          </td>
                        </tr>
                        <?php $ctr++; ?>
                      <?php endforeach ?>
                      <?php endif ?>
                    </tbody>
                  </table>
                </div>
                <!-- /.table-responsive -->
              </div>
              <!-- /.card-body -->
              
              <!-- /.card-footer -->
            </div>
            <!-- /.card -->


          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                <h5 class="m-0">Document Viewer</h5>
              </div>
              <div class="card-body">
                <div id="viewerContainer" class="embed-responsive embed-responsive-21by9" style="height: 620px !important;">
                  <div id="instructions">To view document, click on it</div>
                  <!-- <iframe id="documentViewer" src = "pdfviewer/ViewerJS/#../pdf/demo.pdf" allowfullscreen webkitallowfullscreen class="embed-responsive-item"  height="600"></iframe> -->
                </div>
               
              </div>
            </div>

            
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php include 'modals.php'; ?>

  <iframe id="my_iframe" style="display:none;"></iframe>
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

<?php include "footer.php"; ?>
<script type="text/javascript" src="js/index.js"></script>
<script type="text/javascript">
  //Date picker
    $('#invoice_date').datepicker({
      useCurrent:true,
      autoclose: true,
    });

</script>
</body>
</html>



