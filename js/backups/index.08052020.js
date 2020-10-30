// Load loading animation
function loading() {
  const loadingModal = document.querySelector('#loading');
  loadingModal.style.display = 'block';
}

// creates an iframe with the pdf url then prints it
printPdf = function (url) {
  var iframe = this._printIframe;
  if (!this._printIframe) {
    iframe = this._printIframe = document.createElement('iframe');
    document.body.appendChild(iframe);

    iframe.style.display = 'none';
    iframe.onload = function() {
      setTimeout(function() {
        iframe.focus();
        iframe.contentWindow.print();
      }, 1);
    };
  }

  iframe.src = url;
}

firefoxPrint = function (url) {
  var myWindow = window.open(url, '_blank', 'width=800,height=600');   
  // myWindow.onload = function() {
  //   setTimeout(function() {
  //       myWindow.focus(); 
  //       myWindow.print(); 
  //       myWindow.close();
  //     }, 1);
  // };
}

function download(download_url, msg = "Document downloaded") {
      console.log(download_url.substr(download_url.lastIndexOf("/"),download_url.length).replace("/",""));
      fetch(download_url)
      .then(resp => resp.blob())
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        // the filename you want
        const fileName = download_url.substr(download_url.lastIndexOf("/"),download_url.length).replace("/","");
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        Swal.fire(msg); // or you know, something with better UX...
      })
      .catch(() => alert('oh no!'));
  };

  function processExcelFiles(url, element) {  
    
    function getExcelData(url) {    
      return new Promise(function(resolve, reject) {
        /* set up async GET request */
        var req = new XMLHttpRequest();
        req.open("GET", url, true);
        // req.setRequestHeader('Access-Control-Allow-Headers', '*');
       //    req.setRequestHeader('Content-type', 'application/ecmascript');
       //    req.setRequestHeader('Access-Control-Allow-Origin', '*');
        req.responseType = "arraybuffer";
        req.onload = function(e) {
          let data = new Uint8Array(req.response);
          let workbook = XLSX.read(data, {type:"array"});

          /* DO SOMETHING WITH workbook HERE */
          //console.log(workbook);
          resolve(workbook);
        }
        req.send();
      })    
    }   

    const workbookPromise =  getExcelData(url);

    var to_html = function to_html(workbook) {
      let html = `<div class="sheetjsTable" style = "overflow: auto;height: 600px !important;">`;
      workbook.SheetNames.forEach(function(sheetName) {
        const worksheet = workbook.Sheets[sheetName];
        if (worksheet['!ref'] !== undefined) {
          html += XLSX.utils.sheet_to_html(worksheet);
        }
        
      });
      html += "</div>";
      return html;
    };

    workbookPromise.then(workbook => {      
      const html = to_html(workbook)
      element.innerHTML = html;
    });
  }
  

  function showPdf(filename, div) {
    const container = document.querySelector(`#${div}`);
    if (filename !== "") {
      container.innerHTML = `<iframe id="documentViewer" src = "pdfviewer/ViewerJS/#../../${filename}" allowfullscreen webkitallowfullscreen class="embed-responsive-item"  height="600"></iframe>`;
    } else {
      container.innerHTML = `<div class="instructions">To view document, click on it</div>`;
    }
    
  }  

  function showPdf2(filename, div) {
    const container = document.querySelector(`#${div}`);
    if (filename !== "") {
      container.innerHTML = `<object data="${filename}" type="application/pdf">
                      <embed src="${filename}" type="application/pdf" />
                  </object>`;
    } else {
      container.innerHTML = `<div class="instructions">To view document, click on it</div>`;
    }
  }

  function clicked(e) {

      const row = e.currentTarget;
      // get filename
      const folder = row.dataset.folder;
      const filename = folder + row.dataset.filename;      
      // get file ext
      const ext = filename.substring(filename.lastIndexOf("."), filename.length).toLowerCase();
      const tableBodyRows = document.querySelectorAll(".tableBodyRow");
      
      switch (e.button) {
          case 0:            

            // Remove all selected class on all rows
            tableBodyRows.forEach(r => r.classList.remove("selected-row")); 
            // add selected class on clicked row            
            if (row.classList.contains("selected-row")) {
              console.log("selected");
              row.classList.remove("selected-row");
            } else {
              row.classList.add("selected-row");
              // if file is pdf show document., if xls or xlsx then download file
              if (ext === ".pdf") {                
                showPdf2(filename, 'viewerContainer');
              } else if (ext === ".xls" || ext === ".xlsx") {

                const url = `https://portal-ca.sbe-ltd.ca/sbe_ap_validation/${filename}`;                
                processExcelFiles(url, document.querySelector("#viewerContainer"));
              }   
            }                  
            
            break;
          case 1:
            console.log("middle click detected");
            break;
          default:            
            e.preventDefault();          
      }
  }
  const tableBodyRows = document.querySelectorAll(".tableBodyRow");
  tableBodyRows.forEach(function(row) {
    row.addEventListener('mousedown', clicked, false);
  });

  // Data table initialization
  $(function () {
    
    $('#documentTable').DataTable({
      "pageLength": 50,
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
    });

    $('#receive_modal').on('shown.bs.modal', function () {
      console.log("modal on");
   })

    
  });

  // Process selected document
  function receive() {
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      // ajax call to get 
      $.ajax({
        type: 'post',
        url: 'actions.php',
        data: {
          "getDocument_information" : "1",
          "document_id" : selected.id
        },
        success: function(data){
          const res = JSON.parse(data);
          console.log(res);     
          const invoice_number = res.INVOICE_NUMBER == null ? res.ID : res.INVOICE_NUMBER;
          
          document.querySelector("#sender").value = res['SENDER_NAME'];
          document.querySelector("#subject").value = res['EMAIL_SUBJECT'];
          document.querySelector("#attached_file").value = res['ATTACHED_DOCUMENT'];
          document.querySelector("#date_and_time").value = `${res['SENT_DATE']} ${res['SENT_TIME']}`;
          document.querySelector("#index").value = res['ID'];
          document.querySelector("#invoice_number").value = invoice_number;
          document.querySelector("#company").value = res['COMPANY'];
          // document.querySelector("#document_type").value = res['DOCUMENT_TYPE'];
          document.querySelector("#supplier").value = res['SUPPLIER'];
          document.querySelector("#invoice_date").value = res['INVOICE_DATE'];
          document.querySelector("#document_file").value= selected.dataset.folder + selected.dataset.filename;
          $("#receive_modal").modal();
        }
      });   
    }
  }

  // Put selected document on hold
  function hold() {
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to put this document on hold?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#33cc33',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes put it on hold'
      }).then((result) => {
        if (result.value) {
            $.ajax({
              type: 'post',
              url: 'actions.php',
              data: {
                "put_document_on_hold" : "1",
                "document_id" : selected.id
              },
              success: function(data){
                console.log(data);
                if (data === 'success') {
                  Swal.fire(
                    'Success!',
                    'Document put on hold',
                    'success'
                  );
                  // remove row 
                  selected.remove();
                  // reset the document viewer
                  showPdf2("","viewerContainer");
                } else {
                  // Sends error if response is fail
                  Swal.fire({
                      icon: 'error',
                      title: 'Oops...',
                      text: 'something went wrong contact data department!'
                  }); 
                }
              }
            });       
        }
      }) 


      
    }
  }

  // delete selected
  function del(){
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      Swal.fire({
        title: 'Are you sure?',
        // text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.value) {
          $.ajax({
            type: 'post',
            url: 'actions.php',
            data: {
              "delete_document" : "1",
              "document_id" : selected.id
            },
            success: function(data){
              if (data === 'success') {
                Swal.fire(
                  'Deleted!',
                  'Document has been deleted',
                  'success'
                );
                // remove row 
                selected.remove();
                // reset the document viewer
                showPdf("","viewerContainer");
              } else {
                // Sends error if response is fail
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'something went wrong contact data department!'
                }); 
              }
            }
          });         
        }
      })      
    }
  }

  // validate selected
  function validate() {    
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      Swal.fire({
        title: 'Are you sure?',
        text: "Confirm that you validate this document",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#33cc33',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, its validated!'
      }).then((result) => {
        if (result.value) {
          $.ajax({
            type: 'post',
            url: 'actions.php',
            data: {
              "validate_document" : "1",
              "document_id" : selected.id
            },
            success: function(data){
              if (data === 'success') {
                Swal.fire(
                  'Validated!',
                  'Document has been validated',
                  'success'
                );
                // remove row 
                selected.remove();
                // reset the document viewer
                showPdf("","viewerContainer");
              } else {
                // Sends error if response is fail
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'something went wrong contact data department!'
                }); 
              }
            }
          });         
        }
      })      
    }
  }


    // validate selected
  function archive() {    
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      Swal.fire({
        title: 'Are you sure?',
        text: "Confirm that you want to archive this document",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#33cc33',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it'
      }).then((result) => {
        if (result.value) {
          $.ajax({
            type: 'post',
            url: 'actions.php',
            data: {
              "archive_document" : "1",
              "document_id" : selected.id
            },
            success: function(data){
              // console.log(data);
              if (data === 'success') {
                Swal.fire(
                  'Archived!',
                  'Document has been archived',
                  'success'
                );
                // remove row 
                selected.remove();
                // reset the document viewer
                showPdf("","viewerContainer");
              } else if(data === 'fail') {
                // Sends error if response is fail
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'something went wrong contact data department!'
                }); 
              }
            }
          });         
        }
      })      
    }
  }


  // process history data and show history modal
  function history() {
     const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Nothing selected!'
      }); 
    }else {
      $.ajax({
        type: 'post',
        url: 'actions.php',
        data: {
          "get_history" : "1",
          "document_id" : selected.id
        },
        success: function(data){
          const res = JSON.parse(data);   
          const html = res.
            map(row => `<tr>
              <td>${row.ACTIONS}</td>
                <td>${row.ACTIONS_BY}</td>
                <td>${row.HISTORY_DATE}</td>
                <td>${row.HISTORY_TIME}</td>
              </tr>`
            )
            .join('');
          // set history table data
          const tbody = document.querySelector("#history_table tbody");
          tbody.innerHTML = html;
          // show modal
          $("#history_modal").modal();
        }
      });
    }
  }

  // attach files to the documents
  function attachments() {
     const selected = document.querySelector('#documentTable .selected-row');
     
      if (selected === null) {
        // Sends error if nothing is selected
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Nothing selected!'
        }); 
      } else {
        // populate attachments table
        showAttachmentsTable(selected.id);
        // Populate id in the modal
        document.querySelector("#attachments_counter_id").value = selected.dataset.attachmentcounterid;
        document.querySelector("#attached_documents_modal-id").textContent = selected.dataset.invoicenumber;
        document.querySelector("#selected_id").value = selected.id;
        // show attachment modal
        $("#attached_documents_modal").modal();
      }
  }

  // attach documents submit handler 
  function uploadAttachments(e) {
    e.preventDefault(); 
    // loading animation    
    loadingModal.style.display = 'block';
    // get uploaded file   
    const fileName = document.querySelector('#uploadedDocument').files[0].name;
    const id = document.querySelector('#selected_id').value;
    // check if file type is excel/pdf if not do not save
    const ext = fileName.substring(fileName.lastIndexOf('.'),fileName.length).toLowerCase();    
    if (ext === '.xls' || ext === '.xlsx' || ext === '.pdf') {
      const file = document.getElementById('uploadedDocument').files[0];
      const form_data = new FormData(this);
      form_data.append("file",file);
      form_data.append("upload_attachment", "1");
      form_data.append("ext", ext);
      $.ajax({
          url: 'actions.php', // point to server-side PHP script 
          dataType: 'text',  // what to expect back from the PHP script, if anything
          cache: false,
          contentType: false,
          processData: false,
          data:form_data,                         
          type: 'post',
          success: function(data){
              console.log(data) // display response from the PHP script, if any
              if (data === "success") {
                Swal.fire(
                  'Success!',
                  'Upload Successful',
                  'success'
                );
                // Reset form
                document.querySelector("#attach_file").reset();
                // show changes in table
                // populate attachments table
                showAttachmentsTable(id);
                // get counter id
                const counterId = document.querySelector("#attachments_counter_id").value;
                // get counter element
                const counter = document.querySelector(`#${counterId}`);
                // get current attachment count
                const attachmentCount = parseInt(counter.textContent.trim());
                // add 1 to get new count
                const newCount = attachmentCount + 1;                
                // update attachments counter
                counter.textContent = `${newCount}`;
              } else {
                // If error occurs throw msg
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Something went wrong, contact data department'
                });
              }              
          }
       }); 
    } else {
      console.log("file type is not allowed");
      loadingModal.style.display = 'none';
      // Sends error if file type is not allowed
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Uploaded file type is not allowed!'
      });
      document.getElementById('uploadedDocument').value =''; // clear upload
    }

  }

  // attach submit handler 
  const uploadAttachmentsForm = document.querySelector("#attach_file");
  uploadAttachmentsForm.addEventListener('submit', uploadAttachments);
  const attachmentTable_tbody = document.querySelector("#attachmentTable tbody");
  

  // function to show attachments table
  function showAttachmentsTable(id) {
    const ad = document.querySelector("#attached_documents_div");
    $.ajax({
      type: 'post',
      url: 'actions.php',
      data: {
        "get_attachments" : "1",
        "document_id" : id
      },
      success: function(data){
        // console.log(data);
        if (data !== "no attachments") {
          const res = JSON.parse(data);          
          const html = res.map(function(row) {
            return `<tr class="attachmentTableRow" 
                id="${row.ID}"
                data-filename = "${row.FILENAME}"
                onclick="attachmentClicked(event)"
                data-folder="documents_attached/"
              >
              <td>${row.DOCUMENT_NAME}</td>
              <td>${row.UPLOAD_BY}</td>
              </tr>
            `;

          }).join('');
          attachmentTable_tbody.innerHTML = html;
          showPdf("", 'attachedDocumentViewer');
          ad.classList.remove('hide');  
        } else {
          console.log("empty");
          ad.classList.add('hide'); 
        }
      }
    });
  }

  function attachmentClicked(event) {
    console.log("Clicked on attachments");
    const row = event.currentTarget;    
    const id = row.id;
    const folder = row.dataset.folder;
    const filename = folder + row.dataset.filename;
    // get file ext
    const ext = filename.substring(filename.lastIndexOf("."), filename.length).toLowerCase();
    const rows = document.querySelectorAll(".attachmentTableRow");
    // remove all selected row
    rows.forEach(r => r.classList.remove("selected-row")); 
    if (row.classList.contains("selected-row")) {
      console.log("selected");
      row.classList.remove("selected-row");
    } else {
      row.classList.add("selected-row");
      // show document
      if (ext === ".pdf") {
        // show pdf                
        showPdf2(filename, 'attachedDocumentViewer');
      } else if (ext === ".xls" || ext === ".xlsx") {
        // download excel
        const download_url =  filename;
        const url = `https://portal-ca.sbe-ltd.ca/sbe_ap_validation/${filename}`;                
        processExcelFiles(url, document.querySelector("#attachedDocumentViewer"));      
      }   

    }
  }

  // function to print attachments
  function printAttachment() {
    const selected = document.querySelector('#attachmentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      const file = selected.dataset.folder + selected.dataset.filename;
      const ext = file.substr(file.lastIndexOf('.'), file.length).toLowerCase(); // get file extension
      if (ext === ".pdf") {
        firefoxPrint(file); // opens iframe with pdf to print
      } else {
        download(file, "excel file downloaded"); // download excel files to print
      }
     
    }
  }

  // function to download attachments
  function downloadAttachment() {
    const selected = document.querySelector('#attachmentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {
      const file = selected.dataset.folder + selected.dataset.filename;
      download(file, "attached document downloaded"); // download attached documents     
    }
  }


  // function to delete attachments
  function deleteAttachment() {
    const selected = document.querySelector('#attachmentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
              title: 'Oops...',
          text: 'Nothing selected!'
      }); 
    } else {

      Swal.fire({
        title: 'Are you sure?',
        // text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.value) {
          
          $.ajax({
            type: 'post',
            url: 'actions.php',
            data: {
              "delete_attachments" : "1",
              "id" : selected.id
            },
            success: function(data){
              console.log(data);
              if (data === 'success') {
                Swal.fire(
                  'Success!',
                  'Document deleted',
                  'success'
                );
                // remove row 
                selected.remove();
                // reset the document viewer
                showPdf("",'attachedDocumentViewer');
                // get counter id
                const counterId = document.querySelector("#attachments_counter_id").value;
                // get counter element
                const counter = document.querySelector(`#${counterId}`);
                // get current attachment count
                const attachmentCount = parseInt(counter.textContent.trim());
                // add 1 to get new count
                const newCount = attachmentCount - 1;                
                // update attachments counter
                counter.textContent = `${newCount}`;


              } else {
                // Sends error if response is fail
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'something went wrong contact data department!'
                }); 
              }
            }
          });

        }
      });
      
    }
  }

  // send to flow return hold documents back to flow
  function sendToFlow() {
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Nothing selected!'
      }); 
    } else {
      Swal.fire({
        title: 'Are you sure?',
        text: "Confirm that you want to send this document back to flow",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#33cc33',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes send back to flow!'
      }).then((result) => {
        if (result.value) {
          $.ajax({
            type: 'post',
            url: 'actions.php',
            data: {
              "send_back_to_flow" : "1",
              "document_id" : selected.id
            },
            success: function(data){
              if (data === 'success') {
                Swal.fire(
                  'Success!',
                  'Document is sent back flow',
                  'success'
                );
                // remove row 
                selected.remove();
                // reset the document viewer
                showPdf("","viewerContainer");
              } else {
                // Sends error if response is fail
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'something went wrong contact data department!'
                }); 
              }
            }
          });         
        }
      }) 
    }
  }

  // show notes/comments 
  function showNotes(id) {
    const nd = document.querySelector("#document_notes_panel");
    $.ajax({
      type: 'post',
      url: 'actions.php',
      data: {
        "get_notes" : "1",
        "id" : id
      },
      success: function(data){
        if (data === 'none found') {
          // hide document notes panel
          nd.classList.add('hide');
        } else {
          // process data
          const res = JSON.parse(data);
          console.table(res);
          const html = res.map(row => {
            // format date
            const d = new Date(row.ADDED_DATE);
            const dtf = new Intl.DateTimeFormat('en', { year: 'numeric', month: 'short', day: '2-digit' }) 
            const [{ value: mo },,{ value: da },,{ value: ye }] = dtf.formatToParts(d);
            const date = `${da}-${mo}-${ye}`; 

            return `<div class="time-label">
                <span class="bg-red">${date}</span>
              </div>
              <div>
                <i class="fas fa-comment-alt bg-blue"></i>
                <div class="timeline-item">
                  <span class="time text-white"><i class="fas fa-clock"></i> ${row.ADDED_TIME}</span>
                  <h3 class="timeline-header bg-info">added by <strong>${row.ADDED_BY}</strong></h3>

                  <div class="timeline-body">
                    ${row.NOTE}
                  </div>
                  <div class="timeline-footer">                             
                    <a class="btn btn-danger btn-sm" 
                      onclick="deleteNote(event)" 
                      id = "${row.ID}"
                      data-apid = "${row.AP_VALIDATION_ID}"
                    >Delete</a>
                  </div>
                </div>
              </div>
            `
          });
          const timeline = document.querySelector("#notes_timeline");
          timeline.innerHTML = "";
          timeline.innerHTML = html;
          nd.classList.remove('hide');
        }
      }
    });
  }

  // show document notes modal
  function documentNotes() {
    const selected = document.querySelector('#documentTable .selected-row');
    if (selected === null) {
      // Sends error if nothing is selected
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Nothing selected!'
      }); 
    } else {
      // Populate information for modal
      document.querySelector("#note_counter_id").value = selected.dataset.notecounterid;
      // document.querySelector("#document_notes-invoice_number").textContent = selected.dataset.invoicenumber;
      document.querySelector('#notes_id').value = selected.id; 
      // show notes for the document
      showNotes(selected.id);
      // show notes modal
      $('#document_notes').modal();
    }
  }

  // Add Note to a document
  function addNote(e) {
    e.preventDefault();     
    // get form data
    const id = document.querySelector('#notes_id').value;
    const form_data = new FormData(this);
    form_data.append("add_note_to_document", "1"); 
    // ajax call
    $.ajax({
      type: 'post',
      url: 'actions.php',
      data: form_data,
      processData: false,
      contentType: false,
      success: function(data){
        if (data === 'success') {
          Swal.fire(
            'Note Added!',            
            'success'
          );
          // const loading = document.querySelector('#loadingModal');
          // loading.style.display = 'none';
          // Reset form
          document.querySelector("#add_notes_form").reset();
          // show updated notes/comments
          showNotes(id);
          // get counter id
          const counterId = document.querySelector("#note_counter_id").value;
          // get counter element
          const counter = document.querySelector(`#${counterId}`);
          // get current attachment count
          const attachmentCount = parseInt(counter.textContent.trim());
          // add 1 to get new count
          const newCount = attachmentCount + 1;                
          // update attachments counter
          counter.textContent = `${newCount}`;

        } else {
          // Sends error if response is fail
          Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'something went wrong contact data department!'
          }); 
        }
      }      
    });
    
  }

  // delete note
  function deleteNote(event) {
    const target = event.currentTarget;
    Swal.fire({
        title: 'Are you sure?',
        Text: "Do you want to delete this note/comment!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.value) {
          
          $.ajax({
            type: 'post',
            url: 'actions.php',
            data: {
              "delete_note" : "1",
              'ap_validation_id': target.dataset.apid,
              "id" : target.id
            },
            success: function(data){
              console.log(data);
              if (data === 'success') {
                Swal.fire(
                  'Success!',
                  'Note deleted',
                  'success'
                );
                // show updated notes/comments
                showNotes(target.dataset.apid);
                // get counter id
                const counterId = document.querySelector("#note_counter_id").value;
                // get counter element
                const counter = document.querySelector(`#${counterId}`);
                // get current attachment count
                const attachmentCount = parseInt(counter.textContent.trim());
                // add 1 to get new count
                const newCount = attachmentCount - 1;                
                // update attachments counter
                counter.textContent = `${newCount}`;
                
              } else if (data === 'fail'){
                // Sends error if response is fail
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'something went wrong contact data department!'
                }); 
              }
            }
          });

        }
      });
  }
  

  // add event listener to add note form
  const addNoteForm = document.querySelector("#add_notes_form");
  addNoteForm.addEventListener('submit', addNote);


// open send email modal
function openEmailModal() {
  const selected = document.querySelector('#documentTable .selected-row');
  if (selected === null) {
    // Sends error if nothing is selected
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Nothing selected!'
    }); 
  } else  {
    //populate modal information
    const file = selected.dataset.folder + selected.dataset.filename;
    document.querySelector("#email_document_attached").value = file;
    // open send email modal
    $('#email_modal').modal();
  }
}

function sendEmail(e) {
  e.preventDefault();
  const form_data = new FormData(this);
  form_data.append("send_email", "1"); 
  // ajax call
  $.ajax({
    type: 'post',
      url: 'actions.php',
      data: form_data,
      processData: false,
      contentType: false,
      success: function(data){
        if (data === 'success') {
          Swal.fire(
            'Success!',
            'Email Sent',
            'success'
          );

          // reset form
          document.querySelector("#send_email_form").reset();
          $('#compose-textarea').summernote('reset'); // reset summernote
          // close modal
          $('#email_modal').modal('hide');
        } else if (data === 'fail') {
          // Sends error if nothing is selected
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong contact data department'
          }); 
        }
      }
  });
}

// send email event handler

const emailForm = document.querySelector("#send_email_form");
emailForm.addEventListener('submit', sendEmail);


// edit document information in flow
function documentInformation() {
  const selected = document.querySelector('#documentTable .selected-row');
  // get page name
  const pageName = document.querySelector("#page_name").value;
  if (selected === null) {
    // Sends error if nothing is selected
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Nothing selected!'
    }); 
  } else  {
    // ajax call to get document information
    $.ajax({
      type: 'post',
      url: 'actions.php',
      data: {
        "getDocument_information" : "1",
        "document_id" : selected.id
      },
      success: function(data){
        const res = JSON.parse(data);
        console.log(res);
        const form = document.querySelector("#receive_modal form");
        form.innerHTML += `<input type="hidden" name="original_type" id="original_type">`;
        form.innerHTML += `<input type="hidden" name="page" id="page">`;
        document.querySelector("#page").value = pageName;
        document.querySelector("#sender").value = res['SENDER_NAME'];
        document.querySelector("#subject").value = res['EMAIL_SUBJECT'];
        document.querySelector("#attached_file").value = res['ATTACHED_DOCUMENT'];
        document.querySelector("#date_and_time").value = `${res['SENT_DATE']} ${res['SENT_TIME']}`;
        document.querySelector("#index").value = res['ID'];
        document.querySelector("#invoice_number").value = res['INVOICE_NUMBER'];
        document.querySelector("#company").value = res['COMPANY'];
        document.querySelector("#document_type").value = res['DOCUMENT_TYPE'];
        document.querySelector("#original_type").value = res['DOCUMENT_TYPE'];
        document.querySelector("#supplier").value = res['SUPPLIER'];
        document.querySelector("#invoice_date").value = res['INVOICE_DATE'];
        document.querySelector("#document_file").value = selected.dataset.folder + selected.dataset.filename;
        // get receive modal form
        document.querySelector("#receive_modal .modal-title").innerHTML = `<strong>Edit Document Information </strong>`;
        const submit = form.querySelector('button[type="submit"]');
        // change submit name to editDocumentInfo
        submit.name = "editDocumentInfo";
        // change submit value to edit info
        submit.textContent = "Save Document Info";
        // launch modal
        $("#receive_modal").modal();
      }
    });
  }
}

// print document
function printDocument() {
  const selected = document.querySelector('#documentTable .selected-row');
  if (selected === null) {
    // Sends error if nothing is selected
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Nothing selected!'
    }); 
  } else {
    const file = selected.dataset.filename; // get file
    const ext = file.substr(file.lastIndexOf('.'), file.length).toLowerCase(); // get file extension
    if (ext === ".pdf") {
      if (selected.dataset.section) {
        firefoxPrint(selected.dataset.folder + selected.dataset.filename);
      } else {
        firefoxPrint(selected.dataset.folder + file); // opens iframe with pdf to print
      }
      
    } else {
      if (selected.dataset.section) {
        download(selected.dataset.folder + selected.dataset.filename, "document downloaded");
      } else {
        download(selected.dataset.folder + file, "excel file downloaded"); // download excel files to print
      }
      
    }

  }
}

function downloadDocument() {
  const selected = document.querySelector('#documentTable .selected-row');
  if (selected === null) {
    // Sends error if nothing is selected
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Nothing selected!'
    }); 
  } else {
    download(selected.dataset.folder + selected.dataset.filename, "document downloaded");
    
    
  }
}

function uploadInvoice() {
  $("#upload_invoice_modal").modal();
}


 