       
<?php if(in_array($access, array('admin', 'ap'))): ?>
  <div class="container-fluid">
    <!-- Statistics boxes -->
    <div class="row">
      <div class="col">
        <div class="info-box mb-3">            
          <a href="<?php echo in_array($access, array('admin', 'ap')) ? 'documents_to_be_received.php' : '#' ; ?>" class="info-box-icon bg-info elevation-3"><i class="fas fa-file-download"></i></a>
          <div class="info-box-content">
            <a href="<?php echo in_array($access, array('admin', 'ap')) ? 'documents_to_be_received.php' : '#' ; ?>" >
                <span class="info-box-text">Documents received</span>
            </a>                
            <span class="info-box-number">
                <?php echo getDocumentCount("to be received"); ?>
            </span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col">
        <div class="info-box mb-3">              
          <a href="<?php echo in_array($access, array('admin', 'ap')) ? 'documents_in_flow.php' : '#' ; ?>" class="info-box-icon bg-success elevation-3"><i class="fas fa-recycle"></i></a>
          <div class="info-box-content">
            <a href="<?php echo in_array($access, array('admin', 'ap')) ? 'documents_in_flow.php' : '#' ; ?>">
                <span class="info-box-text">Documents in flow</span>
            </a>                
            <span class="info-box-number"><?php echo getDocumentCount("in flow"); ?></span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->

      <!-- fix for small devices only -->
      <div class="clearfix hidden-md-up"></div>

      <div class="col">
        <div class="info-box mb-3">              
          <a href="<?php echo in_array($access, array('admin', 'approver', 'user', 'ap')) ? 'my_documents.php' : '#' ; ?>" class="info-box-icon bg-warning elevation-3"><i class="fas fa-file-signature"></i></a>
          <div class="info-box-content">
            <a href="<?php echo in_array($access, array('admin', 'approver', 'user', 'ap')) ? 'my_documents.php' : '#' ; ?>">
                <span class="info-box-text">My Documents</span>
            </a>                
            <span class="info-box-number"><?php echo getMyDocumentCount($uname); ?></span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col">
        <div class="info-box mb-3">              
          <a href="<?php echo in_array($access, array('admin', 'approver', 'ap')) ? 'documents_on_hold.php' : '#' ; ?>" class="info-box-icon bg-orange elevation-3"><i class="fas fa-pause-circle"></i></a>  
          <div class="info-box-content">
            <a href="<?php echo in_array($access, array('admin', 'approver' , 'ap')) ? 'documents_on_hold.php' : '#' ; ?>">
                <span class="info-box-text">Documents on Hold</span>
            </a>                
            <span class="info-box-number"><?php echo getDocumentCount("on hold"); ?></span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <!-- /.col -->
      <div class="col">
        <div class="info-box mb-3">              
          <a href="<?php echo in_array($access, array('admin', 'ap')) ? 'documents_validated.php' : '#' ; ?>" class="info-box-icon bg-purple elevation-3"><i class="fas fa-check-circle"></i></a>  
          <div class="info-box-content">
            <a href="<?php echo in_array($access, array('admin',  'ap')) ? 'documents_validated.php' : '#' ; ?>">
                <span class="info-box-text">Documents Validated</span>
            </a>                
            <span class="info-box-number"><?php echo getDocumentCount("validated"); ?></span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->

    </div>
    <!-- /.row -->
  </div>

      
<?php endif; ?>

       
       