<?php 
  include 'header.php';  
  $allowed_access_array = array('admin', 'approver',  'ap');
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
            <h1 class="m-0 text-dark">Search Document Backups</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Search Document Backups</li>
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
            <!-- Document Search -->
            <!-- general form elements disabled -->
            <div class="card card-warning">
              <div class="card-header bg-primary">
                <h3 class="card-title">Search Filters</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <form role="form" id="search_backups" autocomplete="off">
                  <div class="row">
                    <div class="col-sm-6">
                      <!-- text input -->
                      <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" class="form-control" id="invoice_number_search" name="invoice_number">
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Supplier</label>                        
                        <select name="supplier_search" id="supplier_search" class="form-control select2">
                            <option></option>                            
                            <?php $qry = "select distinct supplier from ap_validation where supplier not in ('A','Test', 'test', 'testSupplier', 'supplier') order by supplier"; ?>
                            <?php foreach($conn->query($qry) as $row): ?>
                                <option value="<?php echo $row['SUPPLIER']; ?>"><?php echo $row['SUPPLIER']; ?></option>
                            <?php endforeach; ?>
                                                     
                            
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Invoice date range:</label>

                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">
                              <i class="far fa-calendar-alt"></i>
                            </span>
                          </div>
                          <input type="text" class="form-control float-right" id="invoice_date_range" name="invoice_date">
                        </div>
                        <!-- /.input group -->
                      </div>
                    </div>
                  </div>                 
                   
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                
                <button type="submit" class="btn btn-primary float-right">Search Document</button>
              </div>
                <!-- /.card-footer -->
              </form>              
            </div>

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
                 <div class="d-flex flex-wrap" id="buttonsPanel">
                    <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
                      <button  type="button"  class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="history();" data-toggle="tooltip" data-placement="top" title="View Invoice History"><i class="fas fa-history"></i> </button> 
                    <?php endif; ?>
                    <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
                    <button  type="button"  class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="attachments();" data-toggle="tooltip" data-placement="top" title="View/Add Attached Documents To Invoice" ><i class="fas fa-paperclip"></i> </button> 
                   <?php endif; ?>
                    <button  type="button"  class="panel-btn btn3d btn btn-outline-primary  btn-sm" onclick="documentNotes();" data-toggle="tooltip" data-placement="top" title="View/Add Document Notes to Invoice"><i class="far fa-comment-alt"></i> </button>            
                    <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="printDocument();" data-toggle="tooltip" data-placement="top" title="Print Selected Invoice"><i class="fas fa-print"></i> </button>

                    <button class="panel-btn btn3d btn btn-outline-primary btn-sm" onclick="downloadDocument();"  data-toggle="tooltip" data-placement="top" title="Download selected Invoice"><i class="fas fa-download"></i> </button>
                            
                 </div>               
                 
                 
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover m-0" id="documentTable">
                    <thead>
                    <tr>
                      <th>Notes</th>
                      <th>Attachments</th>                  
                      <th>Invoice Number</th>
                      <th>Supplier</th>
                      <th>Invoice Date</th>
                      <th>status</th>                      
                    </tr>
                    </thead>
                    <tbody>
                      
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
    const searchForm = document.querySelector("#search_backups");
    //Initialize Select2 Elements
    // $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2').select2({
      theme: 'bootstrap4'
    })
  //Date picker
    $('#invoice_date').datepicker({
      useCurrent:true,
      autoclose: true,
    });

    //Date range picker
    $('#invoice_date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#invoice_date_range').on('apply.daterangepicker', function(ev, picker) {
        console.log("apply date ranger picker");
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#invoice_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // search submit handler
    function searchSubmit(e) {
      e.preventDefault();
      // get inputs
       const supplier = document.querySelector("#supplier_search").value;
       const invoice_date_range = document.querySelector("#invoice_date_range").value;
       const invoice_number = document.querySelector("#invoice_number_search").value;
       if (supplier == "" && invoice_date_range == "" && invoice_number == "") {
           // Sends error if all is empty
            Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Enter at least one filter to search'
            }); 
       } else {
            const form_data = new FormData(this); // get form data
            form_data.append("search_backups", "1");
            // ajax call
            $.ajax({
            type: 'post',
                url: 'actions-dev.php',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){                  
                  if (data === 'no data') {
                      Swal.fire({
                          icon: 'error',
                          title: 'Oops...',
                          text: 'No result found'
                      }); 
                  } else {
                      const res = JSON.parse(data);
                                      
                      const html = res.map(function(row) {
                          return `<tr class="tableBodyRow" 
                              id="${row.ID}"
                              data-filename = "${row.BACKUP_FOLDER + row.BACKUP_FILENAME}"
                              onclick="clicked(event)"                        
                              data-folder="documents_backup/"
                              data-section="documentSearch"
                              data-invoicenumber="${row.INVOICE_NUMBER}"
                              data-notecounterid = "note_count_${row.ID}"
                              data-attachmentcounterid = "attachments_count_${row.ID}"                        
                          >
                          <td id="note_count_${row.ID}">${row.NOTES}</td>
                          <td id="attachments_count_${row.ID}">${row.ATTACHED_DOCUMENTS}</td>
                          <td>${row.INVOICE_NUMBER}</td>
                          <td>${row.SUPPLIER}</td>
                          <td>${row.INVOICE_DATE}</td>
                          <td>${row.STATUS}
                          </tr>
                          `;

                      }).join('');
                      const tbody = document.querySelector("#documentTable tbody");
                      tbody.innerHTML = ''; //clear tbody
                      tbody.innerHTML = html; //populate result in table
                  }
                }
            });  
       }
    }

    
    searchForm.addEventListener('submit', searchSubmit);

    

</script>
</body>
</html>



