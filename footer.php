  <footer class="main-footer">
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="https://sbeglobalservice.com/canada/">SBE Canada Limited</a>.</strong> All rights
    reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>

  
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<!-- <script src="plugins/chart.js/Chart.min.js"></script> -->
<!-- Sparkline -->
<!-- <script src="plugins/sparklines/sparkline.js"></script> -->
<!-- JQVMap -->
<!-- <script src="plugins/jqvmap/jquery.vmap.min.js"></script> -->
<!-- <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script> -->
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- DataTables -->
<!-- Select2 -->
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0-RC3/js/bootstrap-datepicker.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<!-- bs-custom-file-input -->
<script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- sheet js -->
<script lang="javascript" src="plugins/sheetjs/dist/xlsx.full.min.js"></script>
<script type="text/javascript">
	
	// Removes loading modal when page is ready
    document.onreadystatechange = function() {
      if (document.readyState == "complete") {
        const loadingModal = document.querySelector('#loadingModal');
        loadingModal.style.display = 'none';

        bsCustomFileInput.init();
      }
    }

     // To use select2 class in a modals
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    // Loads loading animation on ajax call
    $(document).on({
        
        ajaxStart: function() {
          const loadingModal = document.querySelector('#loadingModal');
          loadingModal.style.display = 'block';
        },
        ajaxStop: function() { 
          const loadingModal = document.querySelector('#loadingModal');
          loadingModal.style.display = 'none'; 
        } 

    });

    // close call out
    $(document).on('click', '.closeCallout', function(){
	      $(".callout").fadeOut();
	  });


    const loadingModal = document.querySelector('#loading');

    //Add text editor
    $('#compose-textarea').summernote({height: 300 });

    // make modal draggable
    $(".modal-header").on("mousedown", function(mousedownEvt) {
        var $draggable = $(this);
        var x = mousedownEvt.pageX - $draggable.offset().left,
            y = mousedownEvt.pageY - $draggable.offset().top;
        $("body").on("mousemove.draggable", function(mousemoveEvt) {
            $draggable.closest(".modal-dialog").offset({
                "left": mousemoveEvt.pageX - x,
                "top": mousemoveEvt.pageY - y
            });
        });
        $("body").one("mouseup", function() {
            $("body").off("mousemove.draggable");
        });
        $draggable.closest(".modal").one("bs.modal.hide", function() {
            $("body").off("mousemove.draggable");
        });
    });

    //Date picker
    $('#invoice_date').datepicker({
      useCurrent:true,
      autoclose: true,
      format: 'yyyy/mm/dd',
    });

    //Date picker
    $('#upload_invoice_date').datepicker({
      useCurrent:true,
      autoclose: true,
      format: 'yyyy/mm/dd',
    });

</script>