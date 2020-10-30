<?php 
	/*
		Description:
		Scans AP Validation Portal Invoices for Invoices awaiting approval and sends daily summary to approvers at 9 am
	*/
    date_default_timezone_set('US/Eastern'); 
	// Get today's date
    $date = date("j F Y");
    // $date = "19 July 2020";    
    $day = date('D', strtotime($date));
    echo $day;
	require 'db/dbCon.php';
	// require 'db/functions.php';	
    
    function sendDailySummary($data) {
		require_once  'db/MailPack/PHPMailer/PHPMailerAutoload.php';
        require 'db/MailPack/phpCredential.php';
        $date_today = date("j F Y");
        $invoices_count = count($data['invoices']);
        $on_hold_count = count($data['on_hold']);
        $total_count = $invoices_count + $on_hold_count;
        $invoice_indicator = "";
        $keyword = "";
        $on_hold_indicator = "";
        $invoice_html = "";
        $on_hold_html = "";
        // generate invoice table data        
        if (isset($data['invoices']) && !empty($data['invoices'])) {
            $invoice_indicator = "invoices awaiting your validation ({$invoices_count})";
            $keyword = "and";
            $invoice_html = "<p>This is a daily summary of Invoices Awaiting for your validation:</p>
            <table cellspacing='0' cellpadding='0'>
                <thead style='background: rgb(175, 236, 255);'>
                    <tr>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Company</th>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Supplier</th>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Invoice Number</th>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Invoice Date</th>							
                    </tr>
                </thead>
                <tbody>";
            foreach ($data['invoices'] as $key => $value) {
                $invoice_html .= "<tr>";
                $invoice_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['COMPANY']}</td>";
                $invoice_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['SUPPLIER']}</td>";
                $invoice_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['INVOICE_NUMBER']}</td>";
                $invoice_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['INVOICE_DATE']}</td>";			
                $invoice_html .= "</tr>";
            }
            $invoice_html .= "</tbody></table>";
        }

        if (isset($data['on_hold']) && !empty($data['on_hold'])) {
            $on_hold_indicator = "{$keyword} invoices on hold ({$invoices_count})";            
            $on_hold_html = "<br><p>This is daily summary of Invoices On hold requiring resolution:</p>
            <table cellspacing='0' cellpadding='0'>
                <thead style='background: rgb(175, 236, 255);'>
                    <tr>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Company</th>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Supplier</th>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Invoice Number</th>
                        <th style='border: 1px solid #868686; padding: 5px 10px;'>Invoice Date</th>							
                    </tr>
                </thead>
                <tbody>";
            foreach ($data['on_hold'] as $key => $value) {
                $on_hold_html .= "<tr>";
                $on_hold_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['COMPANY']}</td>";
                $on_hold_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['SUPPLIER']}</td>";
                $on_hold_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['INVOICE_NUMBER']}</td>";
                $on_hold_html .= "<td style='border: 1px solid #ccc; padding: 3px 10px;'>{$value['INVOICE_DATE']}</td>";			
                $on_hold_html .= "</tr>";
            }
            $on_hold_html .= "</tbody></table>";
        }
		
        $mail->addAddress($data['email']);
        // $mail->AddCC('cnayve@sbe-ltd.ca');	
        $mail->setFrom('no-reply@sbe-ltd.ca', 'Ap Validation Portal');
        $mail->Subject = "Daily Awaiting Validation Summary";			
        $mail->Body = "<p>{$date_today}</p>
        <p>You have {$total_count} items requiring action: {$invoice_indicator} {$on_hold_indicator}. </p>
            <p>Click <a href='https://portal-ca.sbe-ltd.ca/sbe_ap_validation/my_documents.php'>here</a> to view all documents awaiting your validation</p>
            <br>      
            {$invoice_html}
            {$on_hold_html}    
            ";
        $mail->send();
		return true;

	};

	if (!in_array($day, array("Sat", "Sun"))) {
        // Approvers array
        $Approvers_array = [];
        $Approvers_array["CEO"] = 'dt@sbe-ltd.ca';
        $Approvers_array['Finance'] = 'mpoon@sbe-ltd.ca';
        $Approvers_array['HR and Admin'] = 'cconnolly@sbe-ltd.ca';

        foreach ($Approvers_array as $key => $value) {
            // Query to get invoices awaiting  validation 
            $qry = "SELECT * FROM AP_VALIDATION 
                        WHERE STATUS = 'in flow'
                        AND DOCUMENT_TYPE = '{$key}'
                        AND DELETED is null";
            $res = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);       
            

            // on hold qry
            $oh_qry = "SELECT * FROM AP_VALIDATION 
                        WHERE STATUS = 'on hold'
                        AND DOCUMENT_TYPE = '{$key}'
                        AND DELETED is null";

            $oh_res = $conn->query($oh_qry)->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($res) || !empty($oh_res)) {
                    $data = [];
                    $data['invoices'] = $res;
                    $data['on_hold'] = $oh_res;
                    $data['email'] = $value;
                    // printArr($data);
                    if (sendDailySummary($data)) {
                        echo "Summary sent <br>";
                    }
            }      
        
        }
    }