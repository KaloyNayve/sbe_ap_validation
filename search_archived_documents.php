<?php 
  include 'header.php';  
  $allowed_access_array = array('admin', 'accounting', 'ap');
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
            <h1 class="m-0 text-dark">Search Archived Documents</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Search Archived Documents</li>
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
                        <input type="text" class="form-control" id="supplier_search" name="supplier">
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

                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Validation date range:</label>

                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">
                              <i class="far fa-calendar-alt"></i>
                            </span>
                          </div>
                          <input type="text" class="form-control float-right" id="validation_date_range" name="validation_date">
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

                 <button class="panel-btn btn3d btn btn-outline-success btn-sm" onclick="printDocument();"><i class="fas fa-print"></i> Print Document</button>

                 <button class="panel-btn btn3d btn btn-outline-info btn-sm" onclick="downloadDocument();"><i class="fas fa-download"></i> Download Document</button>
                 
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover m-0" id="documentTable">
                    <thead>
                    <tr>
                                        
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

    // validation date range
    $('#validation_date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#validation_date_range').on('apply.daterangepicker', function(ev, picker) {
        console.log("apply date ranger picker");
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#validation_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // search submit handler
    function searchSubmit(e) {
      e.preventDefault();
      // get inputs
      const fields = Array.from(this.querySelectorAll("input"));      
      // get all empty fields
      function emptyCheck(element) {
        return element.value === "";
      }
      const emptyFields = fields.filter(emptyCheck);
      // Check if all fields are empty      
      if (emptyFields.length === fields.length) {        
        // Sends error if all is empty
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Enter at least one filter to search'
        }); 
      } else {
        const form_data = new FormData(this); // get form data
        form_data.append("search_archived", "1");
        // ajax call
        $.ajax({
          type: 'post',
            url: 'actions.php',
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
                      >
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

    const searchForm = document.querySelector("#search_backups");
    searchForm.addEventListener('submit', searchSubmit);

    

</script>
</body>
</html>



