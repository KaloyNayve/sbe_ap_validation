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
<html>
	<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge;" />  
	<title>SBE AP Validation</title>
  <link rel="shortcut icon" type="image/png" href="dist/img/SBE-icon.png"/>
	 <link rel="stylesheet" href="dist/css/login.css">
	 <link rel="stylesheet" href="dist/css/adminlte.min.css">
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
	</head>
		<body>
			<center><h1></h1></center>
			<div class="body"></div>
      <div class="grad"></div>
      <div class="header">
        <div><img height="60px" src="dist/img/sbe_logo.png"></div>
      </div>
      <br>
      <?php if (isset($_POST['login_error'])): ?>
        <div class="container">
          <div class="alert alert-danger alert-dismissible">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4 style="margin-bottom: 0;font-size: 17px;font-weight: 500;"><i class="icon fa fa-ban"></i>User not allowed/wrong password</h4>
          </div>
        </div>
           
      <?php endif ?>
      <div class="loginForm">
          <form method="post" onsubmit="return validate()">	
          		
            <fieldset class="loginfield">
                <input type="text" class="login_field" placeholder="username" name="uname" id="uname" autofocus >
                <input type="password" class="login_field" placeholder="password" name="pass" id="pass">
                <input type="submit" class="login_btn" value="Login" name="login"></td></tr>						
                <input type="hidden" class="form-control" name="url" id="url" value="<?php echo $url; ?>">
            </fieldset>			
          </form>
      </div>
      
      <!-- jQuery -->
      <script src="plugins/jquery/jquery.min.js"></script>
      <!-- Bootstrap 4 -->
      <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
      <!-- AdminLTE App -->
      <script src="dist/js/adminlte.min.js"></script>
		</body>
		
</html>