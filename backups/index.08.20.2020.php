<?php 
  include 'header.php'; 
    
?>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
<style type="text/css">

body, html {
    height: 100%;
}



  /* The hero image */
  .content {
    background-image: url("dist/img/BG1.jpg");
    height: 700px;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    position: relative;
  }

  .hero-text {
    text-align: right;
    position: absolute;
    top: 50%;
    left: 80%;
    transform: translate(-50%, -50%);
    color: white;
    text-shadow: 2px 2px 4px #000000;
  }

  .hero-h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 60px;
  } 

</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<?php include 'sidebar.php'; ?>	
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    
    <!-- Main content -->
      <div class="content">
      <div class="hero-image">
        <div class="hero-text">
          <h1 class="hero-h1">Welcome to AP Validation Portal</h1>                   
        </div>
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

<script type="text/javascript">
  

</script>
</body>
</html>



