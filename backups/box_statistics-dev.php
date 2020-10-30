       <!-- Statistics boxes -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?php echo getDocumentCount("to be received"); ?></h3>

                <p>Documents to be received</p>
              </div>
              <div class="icon">
                <i class="fas fa-file-download"></i>
              </div>
              <a href="index.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?php echo getDocumentCount("in flow"); ?></sup></h3>

                <p>Documents in flow</p>
              </div>
              <div class="icon">
                <i class="fas fa-recycle"></i>
              </div>
              <a href="documents_in_flow.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?php echo getMyDocumentCount($uname); ?></h3>

                <p>My Documents</p>
              </div>
              <div class="icon">
                <i class="fas fa-address-book"></i>
              </div>
              <a href="my_documents.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?php echo getDocumentCount("on hold"); ?></h3>

                <p>Documents on Hold</p>
              </div>
              <div class="icon">
                <i class="fas fa-pause-circle"></i>
              </div>
              <a href="documents_on_hold.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
