<?php 
  include 'header.php'; 

  $allowed_access_array = array('admin', 'approver', 'ap');
  if (!in_array($access, $allowed_access_array)) {
    redirect("index.php");
  }
  

  if (in_array($access, array('admin', 'ap'))) {
    $documents = getDocuments('on hold'); 
  } else if ($access == "approver") {
    // get your documents on hold
     $document_type = getDocumentType($uname);    
     $qry = "SELECT * FROM AP_VALIDATION 
          WHERE STATUS = 'on hold'
          AND DOCUMENT_TYPE in ({$document_type})
          AND DELETED is null";
    
     $documents = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC); 
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
            <h1 class="m-0 text-dark">Documents on hold</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Documents on hold</li>
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

            <!-- TABLE: LATEST ORDERS -->
            <div class="card">
              <div class="card-header border-transparent">
                <!-- <h3 class="card-title">RECEIVE DOCUMENTS</h3> -->
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="maximize" data-toggle="tooltip" data-placement="top" title="Maximize window"><i class="fas fa-expand"></i></button>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" data-placement="top" title="Minimize window">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" data-placement="top" title="Close window">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                 <br>
                 <div class="d-flex flex-wrap" id="buttonsPanel">
                    <button class="panel-btn btn3d btn btn-outline-primary  btn-sm" onclick="documentNotes();" data-toggle="tooltip" data-placement="top" title="View/Add Document Notes To Invoice"><i class="far fa-comment-alt"></i></button> 
                    <button class="panel-btn btn3d btn btn btn-outline-primary btn-sm" onclick="validate();" data-toggle="tooltip" data-placement="top" title="Validate Invoice"><i class="fas fa-check-circle"></i> </button>

                    <!-- <button class="btn3d btn btn btn-outline-primary btn-sm" onclick="sendToFlow();" data-toggle="tooltip" data-placement="top" title="Send Invoice Back To Flow"><i class="fas fa-recycle"></i> </button> -->
                    
                    <?php if(in_array($access, array('admin', 'ap'))): ?>
                        <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="onhold_delete();" data-toggle="tooltip" data-placement="top" title="Delete selected"><i class="fas fa-minus-circle"></i></button>
                    <?php endif; ?>     
                    
                    <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
                      <button  type="button"  class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="history();" data-toggle="tooltip" data-placement="top" title="View Invoice History"><i class="fas fa-history"></i> </button> 
                    <?php endif; ?> 
                 </div>                 

              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover m-0" id="documentTable">
                    <thead>
                    <tr>
                      <th>notes</th>                       
                      <th>Invoice Number</th>
                      <th>Company</th>
                      <th>Document Type</th>
                      <th>Supplier</th>
                      <th>Invoice Date</th>
                      <th>Subtotal</th> 
                      <th>GST/HST</th>
                      <th>Grandtotal</th>                       
                    </tr>
                    </thead>                    
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
                <h3 class="card-title">Document Viewer</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="maximize" data-toggle="tooltip" data-placement="top" title="Maximize window"><i class="fas fa-expand"></i></button>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" data-placement="top" title="Minimize window">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" data-placement="top" title="Close window">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div id="viewerContainer" class="pdf-viewer embed-responsive embed-responsive-21by9" style="height: 620px !important;">
                  <div class="instructions">To view document, click on it</div>                  
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

    <?php include 'modals-dev.php'; ?>

  <iframe id="my_iframe" style="display:none;"></iframe>
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

<?php include "footer.php"; ?>
<script>
   const uname = document.querySelector("#uname").value; 
   const access = document.querySelector("#access").value;                   
   $(document).ready(function(){
    $('#documentTable').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        "pageLength": 20,
        'ajax': {
            'url':'pagination/on_hold-pagination.php',
            "data": {
                uname,
                access
            }            
        },
        'columns': [          
          { data: 'notes' },
          { data: 'invoice_number' },
          { data: 'company' },
          { data: 'document_type' },
          { data: 'supplier' },
          { data: 'invoice_date' },          
          { data: 'subtotal'},
          { data: 'hst_gst'},
          { data: 'grandtotal'}                  
        ],
        'createdRow': function( row, data, dataIndex ) {
            $(row).attr('oncontextmenu', 'return false;');
            $(row).attr('class', "tableBodyRow"); 
            $(row).attr('onclick', "clicked(event);"); 
            $(row).attr('id', data.id);
            $(row).attr('data-filename', data.backup_folder + data.backup_filename);
            $(row).attr('data-folder', 'documents_backup/');
            $(row).attr('data-invoicenumber', data.invoice_number);
            $(row).attr('data-notecounterid', `note_count_${data.id}`);            
        },
        'columnDefs': [
          {
              'targets': 0,
              'createdCell':  function (td, cellData, rowData, row, col) {
                $(td).attr('id', `note_count_${rowData.id}`); 
              }
          }
        ]
    });    
  });
</script>
<script type="text/javascript" src="js/app-dev.js"></script>

</body>
</html>



