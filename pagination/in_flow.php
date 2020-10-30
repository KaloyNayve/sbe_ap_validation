<?php 
    ## Database configuration
    include '../db/dbCon.php';
    include '../db/functions.php';

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value
    
    $last = intval($row) + intval($rowperpage);

    ## Search 
    $searchQuery = " ";
    if($searchValue != ''){
        $searchValue2 = strtolower($searchValue);
        $searchQuery = " and (invoice_number like '%".$searchValue."%' or 
                LOWER(document_type) like '%".$searchValue2."%' or 
                lower(supplier) like'%".$searchValue2."%' or 
                invoice_date like'%".$searchValue."%') ";
    }    

    ## Total number of records without filtering
    $sel = "SELECT count(*) as allcount FROM AP_VALIDATION WHERE status = 'in flow' and deleted is null";
    $records = $conn->query($sel)->fetch(PDO::FETCH_ASSOC);
    $totalRecords = $records['ALLCOUNT'];

    ## Total number of record with filtering
    $sel = "SELECT count(*) as allcount FROM AP_VALIDATION WHERE status = 'in flow' and deleted is null".$searchQuery;
    $records = $conn->query($sel)->fetch(PDO::FETCH_ASSOC);
    $totalRecordwithFilter = $records['ALLCOUNT'];

    

    ## Fetch records
    // $empQuery = "SELECT T.* FROM ( SELECT T.*, rowNum as rowIndex FROM ( SELECT A.*,  case when notes is null then 0 else notes end as notes,
    //     case when attached_documents is null then 0 else attached_documents end as attached_documents FROM AP_VALIDATION a LEFT OUTER JOIN (select ap_validation_id, count(*) as notes from ap_validation_notes group by ap_validation_id)b
    //     on a.id = b.ap_validation_id left outer join (select ap_validation_id, count(*) as attached_documents from ap_validation_attachments group by ap_validation_id)c
    //     on a.id = c.ap_validation_id WHERE a.status = 'in flow' and a.deleted is null {$searchQuery} order by ".$columnName." ".$columnSortOrder." )T)T WHERE rowIndex > ".$row." AND rowIndex <= " .$last;

    $empQuery = "SELECT * FROM (SELECT T.* FROM ( SELECT T.*, rowNum as rowIndex FROM ( SELECT A.*,  case when notes is null then 0 else notes end as notes,
    case when attached_documents is null then 0 else attached_documents end as attached_documents FROM AP_VALIDATION a LEFT OUTER JOIN (select ap_validation_id, count(*) as notes from ap_validation_notes where deleted is null group by ap_validation_id)b
    on a.id = b.ap_validation_id left outer join (select ap_validation_id, count(*) as attached_documents from ap_validation_attachments where deleted is null group by ap_validation_id)c
    on a.id = c.ap_validation_id WHERE a.status = 'in flow' and a.deleted is null {$searchQuery}  )T)T WHERE rowIndex > ".$row." AND rowIndex <= " .$last . ") order by ".$columnName." ".$columnSortOrder;    
    
    $empRecords = $conn->query($empQuery)->fetchAll(PDO::FETCH_ASSOC);
    $data = array();

    foreach ($empRecords as $key => $row) {
        $data[] = array( 
            
            "notes"=>$row['NOTES'],
            "invoice_number"=>$row['INVOICE_NUMBER'],
            "company"=>$row['COMPANY'],
            "document_type"=>$row['DOCUMENT_TYPE'],
            "supplier" => $row['SUPPLIER'],
            "invoice_date"=>$row['INVOICE_DATE'],
            "subtotal"=>$row['SUBTOTAL'],
            "hst_gst"=>$row['HST_GST'],
            "grandtotal"=>$row['GRAND_TOTAL'],
            "attached_documents"=>$row['ATTACHED_DOCUMENTS'],
            "id" => $row['ID'],
            "backup_folder" => $row['BACKUP_FOLDER'],
            "backup_filename" => $row['BACKUP_FILENAME'],
            "rowindex" => $row['ROWINDEX']
         );
    }

   ## Response
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data,
        "query" => $empQuery,
        "searchvalue" => $searchValue
    );
    
    echo json_encode($response);
