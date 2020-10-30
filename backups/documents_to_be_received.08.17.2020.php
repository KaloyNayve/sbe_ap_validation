<?php 
  include 'header.php'; 

  $allowed_access_array = array('admin', 'ap'); 
  if (!in_array($access, $allowed_access_array)) {
    redirect("index.php");
  }

  // Get to be received documents
  $documents = getDocuments("to be received");
  
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
            <h1 class="m-0 text-dark">Documents received</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Documents recieved</li>
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
                    <button class="panel-btn btn3d btn btn-outline-primary  btn-sm" onclick="documentNotes();" data-toggle="tooltip" data-placement="top" title="View/Add Document Notes To Invoice"><i class="far fa-comment-alt"></i></button> 

                  <?php if(in_array($access, array('admin', 'ap'))): ?>
                    <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="del();" data-toggle="tooltip" data-placement="top" title="Delete selected"><i class="fas fa-minus-circle"></i></button>
                  <?php endif; ?>                 

                   <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="receive();"  data-toggle="tooltip" data-placement="top" title="Receive selected invoice"><i class="fas fa-file-download"></i></button>

                   <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="printDocument();" data-toggle="tooltip" data-placement="top" title="Print selected invoice"><i class="fas fa-print"></i></button>
                   
                   <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="downloadDocument();" data-toggle="tooltip" data-placement="top" title="Download selected invoice"><i class="fas fa-download"></i></button>
                 </div>                

                 


              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="table-responsive" >
                  <table class="table table-hover m-0" id="documentTable" >
                    <thead>
                    <tr>
                      <th>notes</th>                  
                      <th>Invoice Number</th>
                      <th>Supplier</th>
                      <th>Company</th>
                      <th>Document Type</th>
                      <th>Sender</th> 
                      <th>Invoice Date</th>
                      <th>Subtotal</th> 
                      <th>GST/HST</th> 
                      <th>Grandtotal</th>                    
                    </tr>
                    </thead>
                    <tbody>
                      <?php if ($documents): ?>
                        <?php $ctr = "1"; ?>
                        <?php foreach ($documents as $row): ?>
                        <tr oncontextmenu="return false;" 
                            class="tableBodyRow" 
                            data-filename="<?php echo $row['RENAMED_ATTACHED_FILE'] ?>"
                            id="<?php echo $row['ID']; ?>" 
                            data-notecounterid = "note_count_<?php echo $row['ID'];  ?>"
                            data-invoicenumber="<?php echo $row['INVOICE_NUMBER']; ?>" data-folder="documents_to_be_received/"
                          > 
                          <td id="note_count_<?php echo $row['ID'];  ?>">
                            <?php echo getNotesCount($row['ID']); ?>
                          </td>                         
                          <td id="index_<?php echo $ctr; ?>">
                            <?php echo (!empty($row['INVOICE_NUMBER'])) ? $row['INVOICE_NUMBER'] : $row['ID']; ?>
                          </td>
                          <td>
                            <?php echo $row['SUPPLIER'] ?>
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
                          
                          <td>
                            <?php echo $row['INVOICE_DATE']; ?>
                          </td>
                          <td>
                            <?php echo $row['SUBTOTAL']; ?>
                          </td>
                          <td>
                            <?php echo $row['HST_GST']; ?>
                          </td>
                          <td>
                            <?php echo $row['GRAND_TOTAL']; ?>
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
<script type="text/javascript" src="js/index.js"></script>
<script type="text/javascript">
  

</script>
</body>
</html>



