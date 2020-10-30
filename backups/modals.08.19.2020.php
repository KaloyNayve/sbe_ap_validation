      
      <!-- /. receive document modal -->
      <div class="modal fade" id="receive_modal">
        <div class="modal-dialog mw-100 w-25" id="receive_modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title"><strong>Receive Document </strong></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form class="form-horizontal" action="actions.php" method="post" autocomplete="off" onsubmit="loading();">
              <div class="modal-body">
                <input type="hidden" name="document_file" id="document_file">
                <input type="hidden" name="page_url" value="<?php echo basename($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" class="form-control" id="index" name="index" readonly>
                <div class="form-group">
                  <label for="sender">Sender:</label>
                  <input type="text" class="form-control" id="sender" readonly>
                </div>

                <div class="form-group">
                  <label for="subject">Email Subject:</label>
                  <input type="text" class="form-control" id="subject" readonly>
                </div>

                <div class="form-group">
                  <label for="attached_file">Attached file:</label>
                  <input type="text" class="form-control" id="attached_file" readonly>
                </div>

                <div class="form-group">
                  <label for="sender">Date and time received:</label>
                  <input type="text" class="form-control" id="date_and_time" readonly>
                </div>

                <div class="form-group">
                  <label for="id">Invoice Number:</label>
                  <input type="text" class="form-control" id="invoice_number" name="invoice_number" >
                </div>

                <div class="form-group">
                  <label for="company">Company:</label>
                  <select class="form-control" name="company" id="company">
                    <option></option>
                    <option value="SBE (Canada) Ltd">SBE (Canada) Ltd</option>
                    <option value="SBE (USA) Ltd">SBE (USA) Ltd</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="type">Document type:</label>
                  <select class="form-control" name="document_type" id="document_type">                    
                    <option></option>                    
                    <option value="OEM">OEM</option>
                    <option value="Shop Supplies">Shop Supplies</option>
                    <option value="Freight and Courier">Freight and Courier</option>
                    <option value="HR and Admin">HR and Admin</option>
                    <option value="Finance">Finance</option> 
                    <option value="CEO">CEO</option>                                 
                  </select>
                </div>

                <div class="form-group">
                  <label for="id">Supplier:</label>
                  <input type="text" class="form-control" id="supplier" name="supplier" >
                </div>

                <div class="form-group">
                  <label for="id">Invoice date</label>
                  <input type="text" class="form-control datepicker" id="invoice_date" name="invoice_date" >
                </div>

              </div>
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" name="receive_document" class="btn btn-primary">Receive Document</button>
              </div>
            </form>            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->

            <!-- /. receive document modal -->
      <div class="modal fade" id="upload_invoice_modal">
        <div class="modal-dialog mw-100 w-25" id="upload_modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-primary">
              <h4 class="modal-title"><strong>Upload Invoice</strong></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form class="form-horizontal" action="actions.php" method="post" autocomplete="off" onsubmit="loading();" enctype="multipart/form-data">
              <div class="modal-body">
                
                <div class="form-group">
                  <label for="exampleInputFile">Upload Pdf/xls and xlsx files only</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="uploadedInvoice" name="uploadedInvoice" required>
                      <label class="custom-file-label" for="uploadedDocument">Choose file</label>
                    </div>                   
                  </div>
                </div> 
                

                <div class="form-group">
                  <label for="id">Invoice Number:</label>
                  <input type="text" class="form-control"  name="invoice_number" required>
                </div>

                <div class="form-group">
                  <label for="company">Company:</label>
                  <select class="form-control" name="company" required>
                    <option></option>
                    <option value="SBE (Canada) Ltd">SBE (Canada) Ltd</option>
                    <option value="SBE (USA) Ltd">SBE (USA) Ltd</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="type">Document type:</label>
                  <select class="form-control" name="document_type" required>                    
                    <option></option>
                    <!-- <option value="Invoice">Invoice</option> -->
                    <option value="OEM">OEM</option>
                    <option value="Shop Supplies">Shop Supplies</option>
                    <option value="Freight and Courier">Freight and Courier</option>
                    <option value="HR and Admin">HR and Admin</option>
                    <option value="Finance">Finance</option> 
                    <option value="CEO">CEO</option>                                 
                  </select>
                </div>

                <div class="form-group">
                  <label for="id">Supplier:</label>
                  <input type="text" class="form-control" name="supplier" required>
                </div>

                <div class="form-group">
                  <label for="id">Invoice date</label>
                  <input type="text" class="form-control datepicker" name="invoice_date" id="upload_invoice_date" required>
                </div>

                <input type="hidden" name="page_url" value="<?php echo $_SESSION['url']; ?>">

              </div>
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" name="upload_invoice" class="btn btn-primary">Upload Invoice</button> 
              </div>
            </form>            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->



      <!-- History modal -->
      <div class="modal fade"  id="history_modal">
        <div class="modal-dialog modal-lg" id="history_modal-dialog">
          <div class="modal-content">
            <div class="modal-header border-0">
              <h4 class="modal-title">Document History</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <table class="table table-striped table-valign-middle" id="history_table">
                  <thead>
                  <tr>
                    <th>Changes</th>
                    <th>Changes by</th>
                    <th>Date</th>
                    <th>Time</th>
                  </tr>
                  </thead>
                  <tbody>                 
                  </tbody>
                </table>
            </div>
            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->


      <!-- Attached Documents -->
      <div class="modal fade" id="attached_documents_modal" >
        <div class="modal-dialog mw-100 w-80">
          <div class="modal-content">
            <div class="modal-header bg-primary">
              <h4 class="modal-title">Attached Documents for Invoice #<span id="attached_documents_modal-id"></span></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <input type="hidden" id="attachments_counter_id">
              <div class="row" id="attached_documents_div">
                
                <div class="col-lg-6">
                  
                  <!-- TABLE: Attachments  -->
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

                       <button class="btn3d btn btn-outline-primary btn-md" onclick="deleteAttachment();" data-toggle="tooltip" data-placement="top" title="Delete Selected Attached Document"><i class="fas fa-minus-circle"></i> </button>  

                       <button class="btn3d btn btn-outline-info btn-md" onclick="printAttachment();" data-toggle="tooltip" data-placement="top" title="Print Selected Attached Document"><i class="fas fa-print"></i> </button>  

                       <button class="btn3d btn btn-outline-success btn-md" onclick="downloadAttachment();" data-toggle="tooltip" data-placement="top" title="Download Selected Attached Document"><i class="fas fa-download"></i></button>                    

                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0" >
                      <div class="table-responsive">
                        <table class="table table-hover m-0" id="attachmentTable">
                          <thead>
                          <tr>                      
                            <th>Attached Document</th>
                            <th>uploaded by</th>                                                  
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

                <div class="col-lg-6">
                  <div class="card">
                    <div class="card-header bg-primary">
                      <h5 class="m-0">View Attached Document</h5>

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
                      <div id="attachedDocumentViewer" class="pdf-viewer embed-responsive embed-responsive-21by9" style="height: 620px !important;">
                        <div class="instructions">To view document, click on it</div>                  
                      </div>
                     
                    </div>
                  </div>

                  
                  </div>

              </div>

              <div class="row">
                <div class="col-md-12">
                  
                   <!-- general form elements -->
                    <div class="card card-primary">
                      <div class="card-header bg-primary">
                        <h3 class="card-title">Upload only xls/xlsx or pdf documents</h3>
                      </div>
                      <!-- /.card-header -->
                      <!-- form start -->
                      <form id="attach_file" method="post" autocomplete="off">
                        <input type="hidden" name="selected_id" id="selected_id" value="">
                        <div class="card-body">                      
                          <div class="form-group">
                            <label for="document_name">document name</label>
                            <input type="text" class="form-control" id="document_name" name="document_name" required>
                          </div>
                          <div class="form-group">
                            <label for="exampleInputFile">File Upload</label>
                            <div class="input-group">
                              <div class="custom-file">
                                <input type="file" class="custom-file-input" id="uploadedDocument" name="uploadedDocument" required>
                                <label class="custom-file-label" for="uploadedDocument">Choose file</label>
                              </div>
                             
                            </div>
                          </div>                          
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                          
                          <button type="submit" class="btn btn-primary">upload</button>
                        </div>
                      </form>
                    </div>
                    <!-- /.card -->

                </div>
              </div>

            </div>            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->



       <!--                        Document notes                           -->
      <div class="modal fade" id="document_notes">
        <div class="modal-dialog modal-xl" id="document_notes-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Document Notes</span></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="note_counter_id">
              <div class="row" id="document_notes_panel">
                <div class="container-fluid">
                  <div class="card" >
                    <div class="card-header bg-primary">
                      <h3 class="card-title">Notes/Comments</h3>
                    </div>
                    <div class="card-body">
                      
                      <!-- The time line -->
                      <div class="timeline" id="notes_timeline">
                      </div>

                    </div>
                  </div>


                </div>                
              </div>

              <div class="row">
               <div class="col-md-12">
                 <!-- form start -->
                 <div class="card">
                     <div class="card-header bg-primary">
                      <h3 class="card-title">Add Notes to this document</h3>
                    </div>
                    <form id="add_notes_form" method="post" autocomplete="off">
                      <div class="card-body">
                        <input type="hidden" name="notes_id" id="notes_id" value="">
                        <div class="form-group">
                          <label>Note:</label>
                          <textarea class="form-control" rows="3" placeholder="Enter ..." name="note" id="note"></textarea>
                        </div>
                      </div>
                      <div class="card-footer">                      
                        <button type="submit" class="btn btn-primary">Add Note</button>
                      </div>
                    </form>                    
                 </div>

               </div>
              </div>


            </div>
            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->



      <!--                           email modal                              -->

      <div class="modal fade" id="email_modal">
        <div class="modal-dialog modal-xl" id="email_modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title"><i class="fas fa-envelope-open-text"></i> Send Document as attachment</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="col-md-12">
                <div class="card card-primary  ">
                  <div class="card-header bg-primary card-outline">
                    <h3 class="card-title">Forward document in email</h3>
                  </div>
                  <!-- /.card-header -->
                  <form id="send_email_form" method="post" autocomplete = "off">
                    <div class="card-body">
                      <div class="form-group">
                        <label>To:(if sending to multiple emails, separate email address with space)</label>
                        <input class="form-control" placeholder="To:" name="email_address" required>
                      </div>
                      <div class="form-group">
                        <label>Subject:</label>
                        <input class="form-control" placeholder="Subject:" name="email_subject" required>
                      </div>
                      <div class="form-group">
                        <label>Document Attached:</label>
                        <input type="text" name="email_document_attached" id="email_document_attached" class="form-control" readonly>
                      </div>
                      <div class="form-group">
                          <textarea id="compose-textarea" class="form-control" style="height: 600px" name="email_body">
                            
                          </textarea>
                      </div>
                      
                    </div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                      <div class="float-right">                      
                        <button type="submit" class="btn btn-primary"><i class="far fa-envelope"></i> Send</button>
                      </div>                    
                    </div>

                  </form>               
                  
                  <!-- /.card-footer -->
                </div>
                <!-- /.card -->
              </div>
              <!-- /.col -->
            </div>            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->


     