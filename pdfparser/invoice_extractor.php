<?php  
	
	function getInvoiceNumber($var) {
		// get current date to string
		$year = date("Y");
		return strpos($var, $year) !== false && count(explode(" ", $var)) == 2; 
	}

	function ProcessSamsungInvoice($file) {
		// Include Composer autoloader if not already done.
		include 'vendor/autoload.php';
		// array to be returned
		$data = [];
		
		// function ifSamsung($var) {
		// 	// convert to lower case 
		// 	$var = strtolower($var);
		// 	return strpos($var, "lg electronics") !== false; 
		// }
		
		$parser = new \Smalot\PdfParser\Parser();
		$pdf    = $parser->parseFile($file);
		 
		// Retrieve all pages from the pdf file.
		$pages  = $pdf->getPages();
		// Create array to put page lines separated by pages
		$pageLines = [];
		 
		// Loop over each page to extract text.
		foreach ($pages as $page) {
		    
		    $text = $page->getText();
			// echo $text;
			$text = explode("\n", $text);	

			$text = array_map("trim", $text);
			// push text lines into pageLines array	
			array_push($pageLines, $text); 
			
		}

		/* Put all lines into a single array*/
		$lines = [];
		foreach ($pageLines as $page) {
			foreach ($page as $line) {
				array_push($lines, $line);
			}
		}
		

		/* to get invoice number and date would search the first page for string with year and when 
		 * exploded would generate 2 items in array 
		*/

		
		$process_invoice = array_values(array_filter($pageLines[0], "getInvoiceNumber"));
		$data['invoice_date'] = substr($process_invoice[0], 0, 10);
		$process = explode(" ", str_replace($data['invoice_date'], "", $process_invoice[0]));
		$data['invoice_number'] = $process[1];

		$last_page_index = count($pageLines) - 1;

		/* Get Sub Total*/
		$data['subtotal'] = $pageLines[$last_page_index][array_search("N DE FACTURE", $pageLines[$last_page_index]) + 1];		

		/* Get hst/gst*/

		$hst_index = array_search("GRAND", $pageLines[$last_page_index]) + 1;		
		$data['hst_gst'] = trim(str_replace("TOTALGST/HST", "", $pageLines[$last_page_index][$hst_index]));
		$grand_total_index = array_search("N DE FACTURE", $pageLines[$last_page_index]) + 3;
		$data['grand_total'] = trim(str_replace("Sub Total/Sous-Total", "", $pageLines[$last_page_index][$grand_total_index]));
		return $data;
	}
	
	

