<?php 
  include 'header.php'; 

  $allowed_access_array = array('admin', 'ap');
  if (!in_array($access, $allowed_access_array)) { 
    redirect("index.php");
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
            <h1 class="m-0 text-dark">Documents in flow</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Documents in flow</li>
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

                 <div class="d-flex flex-wrap">

                   <!-- <button type="button" class="btn3d btn btn btn-outline-success btn-sm" onclick="validate();"><i class="fas fa-check-circle"></i> Validate</button> -->
                   
                   <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
                    <button  type="button"  class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="history();" data-toggle="tooltip" data-placement="top" title="View Invoice History"><i class="fas fa-history"></i> </button> 
                   <?php endif; ?> 
                   

                   <?php if(in_array($access, array('admin', 'ap'))): ?>
                    <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="del();" data-toggle="tooltip" data-placement="top" title="Delete selected"><i class="fas fa-minus-circle"></i></button>
                   <?php endif; ?>

                   <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
                    <button  type="button"  class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="attachments();" data-toggle="tooltip" data-placement="top" title="View/Add Attached Documents To Invoice" ><i class="fas fa-paperclip"></i> </button> 
                   <?php endif; ?>       

                   

                   <button  type="button"  class="panel-btn btn3d btn btn-outline-primary  btn-sm" onclick="documentNotes();" data-toggle="tooltip" data-placement="top" title="View/Add Document Notes to Invoice"><i class="far fa-comment-alt"></i> </button>  

                   <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
                    <button  type="button"  class="panel-btn btn3d btn btn-outline-primary  btn-sm" onclick="openEmailModal();" data-toggle="tooltip" data-placement="top" title="Forward Invoice in Email"><i class="fas fa-envelope-open-text"></i></button> 
                   <?php endif; ?>  
                   
                   
                   <button  type="button"  class="panel-btn btn3d btn btn-outline-primary  btn-sm" onclick="documentInformation();" data-toggle="tooltip" data-placement="top" title="Edit Invoice Information"><i class="fas fa-info-circle"></i></button>

                   <form action="actions.php" method="post">
                     <button type="submit" class="panel-btn btn3d btn btn-outline-primary  btn-sm" name="download_in_flow_report" data-toggle="tooltip" data-placement="top" title="Download In flow Report"><i class="fas fa-download" ></i> </button> 
                   </form>
                   
                   <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="printDocument();" data-toggle="tooltip" data-placement="top" title="Print Selected Invoice"><i class="fas fa-print"></i> </button>

                   <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="downloadDocument();" data-toggle="tooltip" data-placement="top" title="Download selected Invoice"><i class="far fa-save"></i> </button>

                   <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="uploadInvoice();" data-toggle="tooltip" data-placement="top" title="Upload Invoice"><i class="fas fa-file-upload"></i> </button>
                   
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
                      <th>Attached Docs</th>
                      <th>#</th>                    
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

 <?php include 'modals.php'; ?>

  <iframe id="my_iframe" style="display:none;"></iframe>
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
  
<?php include "footer.php"; ?>

<script>
  $(document).ready(function(){
    $('#documentTable').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        "pageLength": 20,
        'ajax': {
            'url':'pagination/in_flow.php',
            "data": {
                        status: 'in flow',
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
          { data: 'grandtotal'},
          { data: 'attached_documents'},
          { data: 'rowindex'},         
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
            $(row).attr('data-attachmentcounterid', `attachments_count_${data.id}`);   
        },
        'columnDefs': [
          {
              'targets': 0,
              'createdCell':  function (td, cellData, rowData, row, col) {
                $(td).attr('id', `note_count_${rowData.id}`); 
              }
          },
          {
              'targets': 9,
              'createdCell':  function (td, cellData, rowData, row, col) {
                $(td).attr('id', `attachments_count_${rowData.id}`); 
              }
          }
        ]
    });    
  });
</script>
<script type="text/javascript" src="js/in_flow.js"></script> 
</body>
</html>



