<?php
  require 'db/functions.php';
  // To set session name
  $portal_name = "sbe_ap_validation";

  // to determine where to redirect after login
  $url = '';
  // echo $_SESSION['url'];
  if(isset($_SESSION['url'])){
    $url = $_SESSION['url'];
    unset($_SESSION['url']);

  } else {
    $url = 'index.php';
  }

  

  if(isset($_POST['login'])){    

    if(!empty($_POST['login'])){
      $uname = $_POST['uname'];
      $pass = $_POST['pass'];
      $redirectUrl = $_POST['url'];
      login($uname,$pass, $portal_name, $redirectUrl);
    } 
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SBE | Log in</title>
  <link rel="shortcut icon" type="image/png" href="dist/img/SBE-icon.png"/>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <style>
    .login-page{
      width: 100%;
      background-image: url('dist/img/BG1.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: top;
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to start your session</p>
      
        <?php if (isset($_GET['user_login']) && $_GET['user_login'] =='false'): ?>
          <div class="alert alert-danger alert-dismissible">
          <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4 style="margin-bottom: 0;font-size: 17px;font-weight: 500;"><i class="icon fa fa-ban"></i>Wrong username or password !</h4>
          </div>
        <?php endif ?>

      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return validate()">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="uname" id="uname" placeholder="Username">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" name="pass" id="pass">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          
          <!-- /.col -->
          <div class="col-4">
            <input type="hidden" class="form-control" name="url" id="url" value="<?php echo $url; ?>">
            <button type="submit" class="btn btn-primary btn-block" value="Login" name="login">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>     
      
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
      function validate(){
        var empty="";
        var uname = document.getElementById("uname").value;
        var pass = document.getElementById("pass").value;
        console.log(uname);
        if((uname.trim()) == empty){
          alert("user name can't be empty");
          return false;
        }
        else if(pass == empty){
          alert("passord can't be empty");
          return false;
        }
        else{
          return true;
        }       
      }

  </script>

</body>
</html>
