<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Home</a>
      </li>
      
    </ul>    

    <ul class="navbar-nav ml-auto">

      <?php if(strtolower($access) === 'admin'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Admin Settings</a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="add_user.php">Add User</a>
            <!-- <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a> -->
            <!-- <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Add user</a> -->
          </div>
        </li>
      <?php endif; ?>  
      <li class="nav-item d-none d-sm-inline-block">        
        <a href="logout.php" class="nav-link">Logout</a>
      </li>
    </ul>  
    
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <!-- <a href="index3.html" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a> -->

    <a href="index.php" class="brand-link logo-switch" >
      <span class="brand-image-xs logo-xs" style="margin-top: 10px;font-size: 25px;"><b>S</b>BE</span>
      <!-- <img src="/docs/3.0/assets/img/logo-xs.png" alt="AdminLTE Docs Logo Small" class="brand-image-xl logo-xs"> -->
      <img src="dist/img/sbe_logo.png" alt="Sbe Logo" class="brand-image-xs logo-xl" style="left: 30px;max-height: 45px" >
    </a>
    <style type="text/css">
      .sidebar-welcome {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 0;
      }
    </style>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <!-- <div class="image">
          <img src="dist/img/avatar04.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"> Carlo Nayve</a>
        </div> -->
        <div class="pull-left info">
          <p class="sidebar-welcome">Welcome <?php echo ucwords(strtolower($fname)) . " " . ucwords(strtolower($lname))?></p>
          <a href="#" style="font-size: 11px;"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

 <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <?php if(in_array($access, array('admin', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'documents_to_be_received.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-inbox"></i>
                <p>
                  Documents received 
                </p>
              </a>
            </li>
          <?php endif; ?>

          <?php if(in_array($access, array('admin', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'documents_in_flow.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-inbox"></i>
                <p>
                  Documents in flow
                </p>
              </a>
            </li>
          <?php endif; ?>

          <?php if(in_array($access, array('admin', 'ap', 'user'))): ?>
            
          <?php endif; ?>

          <?php if(in_array($access, array('admin', 'approver', 'user', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'my_documents.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-inbox"></i>
                <p>
                  My Documents
                </p>
              </a>
            </li>
          <?php endif; ?>

          <?php if(in_array($access, array('admin', 'ap', 'accounting'))): ?>
            <li class="nav-item">
              <?php $url = 'documents_validated.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-inbox"></i>
                <p>
                  Documents Validated
                </p>
              </a>
            </li>
          <?php endif; ?>

          

          <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'documents_on_hold.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-inbox"></i>
                <p>
                  Documents on hold
                </p>
              </a>
            </li>
          <?php endif; ?>          

          <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'search_backups.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-search"></i>
                <p>
                  Search Document Backups
                </p>
              </a>
            </li>
          <?php endif; ?>     

          <?php if(in_array($access, array('admin', 'approver', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'search_archived_documents.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-archive"></i>
                <p>
                  Search Archived Documents
                </p>
              </a>
            </li>
          <?php endif; ?>      

          <?php if(in_array($access, array('admin', 'ap'))): ?>
            <li class="nav-item">
              <?php $url = 'automated_remittance_emailer.php' ?>
              <a href="<?php echo $url ?>" class="nav-link <?php echo ($_SESSION['url'] === $url) ? 'active' : ''; ?>">
                <i class="nav-icon fas fa-envelope-open-text"></i>
                <p>
                  Remittance Notification Sender
                </p>
              </a>
            </li>
          <?php endif; ?> 
          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>