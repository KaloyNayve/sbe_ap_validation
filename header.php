<?php 
  date_default_timezone_set('US/Eastern');
  require 'db/dbCon.php';
  require 'db/functions.php';
  require 'class/user.php';

  header("Pragma: no-cache");
  header("Cache: no-cache");
  header( "Expires: Mon, 08 Oct 2019 03:00:00 GMT" );
  header( "Cache-Control: no-store,no-cache, must-revalidate" );
  header( "Cache-Control: post-check=0, pre-check=0", FALSE);
  header( "Pragma: no-cache" );
  $_SESSION['url'] = basename($_SERVER['PHP_SELF']);
  // To set session name
  $portal_name = "sbe_ap_validation";

  $portal_title = "SBE Ap Validation Portal";

  if(isset($_SESSION[$portal_name])){   
    $user = unserialize($_SESSION[$portal_name]); 
    $uname = strtolower($user->username);    
    $fname =$user->first_name;
    $lname =$user->last_name;
    $badge = $user->badge;
    $access = $user->access;    
  }
  else{
    header('location: login.php');
  }

  


 ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $portal_title; ?></title>
  <link rel="shortcut icon" type="image/png" href="dist/img/SBE-icon.png"/>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- Date picker -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0-RC3/css/bootstrap-datepicker.min.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <!--  Portal style -->
  <link rel="stylesheet" href="css/style.css">
</head>
 
 <input type="hidden" id="access" value="<?php echo $access; ?>">
 <input type="hidden" id="uname" value="<?php echo $uname; ?>">
 <input type="hidden" id="page_name" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
 <div id="loadingModal"></div> 