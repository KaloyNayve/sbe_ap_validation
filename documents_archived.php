<?php 
  include 'header.php'; 
  // Get to be received documents
  $documents = getDocuments('archived');
  
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
            <h1 class="m-0 text-dark">Documents Archived</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Documents Archived</li>
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
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                 <br>
                 
                

              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover m-0" id="documentTable">
                    <thead>
                    <tr>                      
                      <th>Invoice number</th>
                      <th>Company</th>
                      <th>Document Type</th>
                      <th>Supplier</th>
                      <th>Archived filename</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      <?php if ($documents): ?>
                        <?php $ctr = "1"; ?>
                        <?php foreach ($documents as $row): ?>
                        <tr 
                          oncontextmenu="return false;" 
                          class="tableBodyRow" 
                          data-filename="<?php echo $row['BACKUP_FOLDER']  . $row['BACKUP_FILENAME']  ?>" 
                          data-folder="documents_backup/" id="<?php echo $row['ID']; ?>" 
                          data-invoicenumber="<?php echo $row['INVOICE_NUMBER']; ?>"
                          id="<?php echo $row['ID']; ?>" 
                          >
                          
                          <td id="index_<?php echo $ctr; ?>">
                            <?php echo $row['INVOICE_NUMBER']; ?>
                          </td>
                          <td id="company_<?php echo $ctr; ?>">
                            <?php echo $row['COMPANY']; ?>
                          </td>
                          <td id="type_<?php echo $ctr; ?>">
                            <?php echo $row['DOCUMENT_TYPE'] ?>
                          </td>
                          <td id="sender_<?php echo $ctr; ?>">
                            <?php echo $row['SUPPLIER']; ?>
                          </td>
                          <td>
                            <?php echo $row['ARCHIVED_FILENAME']; ?>
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
</body>
</html>


