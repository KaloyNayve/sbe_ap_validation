<?php  

	function getParts($var) {
		// get current date to string
		$year = date("Y");
		return strpos($var, $year) !== false && count(explode(" ", $var)) > 2; 
	}	

	function my_substr_function($str, $start, $end){
	  return substr($str, $start, $end - $start);
	}

	function getInvoiceNumber($var) {
		// get current date to string
		$year = date("Y");
		return strpos($var, $year) !== false && count(explode(" ", $var)) == 2; 
	}

	function ProcessInvoice($file) {
		// Include Composer autoloader if not already done.
		include 'vendor/autoload.php';
		// array to be returned
		$data = [];
		
		
		 
		// $text = $pdf->getText();
		// // echo $text;
		// $text = explode("\n", $text);
		// echo "<pre>";
		// print_r($text);
		// echo "</pre>";

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
			// $invoice_number = array_filter($text, "getInvoiceNumber");
			// echo "<pre>";
			// print_r($invoice_number);
			// echo "</pre>";

			

			// $if_samsung = array_filter($text, "ifSamsung");
			// echo "<pre>";
			// print_r($if_samsung);
			// echo "</pre>";
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

		/* Get parts in invoice */

		

		$parts = array_values(array_filter($lines, "getParts"));

		foreach ($parts as $key => $value) {
			$data['PO'][$key]['ORIGINAL'] = str_replace("Samsung Electronics Canada (SECA)", "", $value);
			$data['PO'][$key]['CODE'] = str_replace("Samsung Electronics Canada (SECA)", "", substr($value, strripos($value, "-") - 6, strlen($value)));
			// Remove po number from rest
			$data['PO'][$key]['REST'] = str_replace($data['PO'][$key]['CODE'], "", $data['PO'][$key]['ORIGINAL']);	
			$rest = str_replace($data['PO'][$key]['CODE'], "", $value);	
			// remove invoice date from rest
			$data['PO'][$key]['REST'] = str_replace($data['invoice_date'], "", $data['PO'][$key]['REST']);
			$rest = str_replace($data['invoice_date'], "", $rest);
			// remove triple space from rest
			$data['PO'][$key]['REST'] = str_replace("   ", "", $data['PO'][$key]['REST']);
			// explode the rest into array
			$data['PO'][$key]['REST_ARR'] = explode("  ", $data['PO'][$key]['REST']);

			$po_array = explode("  ", $data['PO'][$key]['REST']);
			// save the rest to rest
			$rest = $data['PO'][$key]['REST']; 
			// get unit price from rest
			$data['PO'][$key]['UNIT_PRICE'] = substr($rest, strpos($rest, " "), strripos($rest, ".") - strlen($rest) + 3);
			// get po total price
			$data['PO'][$key]['TOTAL_UNIT_PRICE'] = substr($rest, 0, strpos($rest, ".") - strlen($rest) + 3);
			// remove unit price from rest

			$data['PO'][$key]['REST2'] = str_replace($data['PO'][$key]['UNIT_PRICE'], "", $rest);
			// remove total unit price from rest
			$data['PO'][$key]['REST2'] = str_replace($data['PO'][$key]['TOTAL_UNIT_PRICE'], "", $data['PO'][$key]['REST2']);
			$rest2 = $data['PO'][$key]['REST2'];
			// get coderef
			$c = explode(" ", $rest2);

			$data['PO'][$key]['CODEREF'] = str_replace($data['invoice_date'], "", $c[0]);

			$data['PO'][$key]['REST3'] = str_replace($data['PO'][$key]['CODEREF'], "", $rest2);

			$rest3 = str_replace($data['PO'][$key]['CODEREF'], "", $rest2);


			// get quantity
			$data['PO'][$key]['QUANTITY'] = preg_replace('/[^0-9,.]/', '', substr($rest3, 0, stripos($rest3, "S") - strlen($rest3)));
			// get description
			$data['PO'][$key]['DESCRIPTION'] = str_replace($data['PO'][$key]['QUANTITY'], "", $rest3);

			// unset($data['PO'][$key]['REST']);
			unset($data['PO'][$key]['REST2']);
			unset($data['PO'][$key]['REST3']);
		}
		

		foreach ($parts as $key => $value) {
			$parts[$key] = explode(" ", trim(str_replace($data['invoice_date'], "", $value)));
		}
		$newParts = [];
		foreach ($parts as $part) {
			array_push($newParts, array_values(array_filter($part)));
		}

		

		

		

		/*  automatically read pre-tax amount, hst/qst, total   */


		$last_page_index = count($pageLines) - 1;

		/* Get Sub Total*/
		$data['subtotal'] = $pageLines[$last_page_index][array_search("N DE FACTURE", $pageLines[$last_page_index]) + 1];		

		/* Get hst/gst*/

		$hst_index = array_search("GRAND", $pageLines[$last_page_index]) + 1;		
		$data['hst_gst'] = trim(str_replace("TOTALGST/HST", "", $pageLines[$last_page_index][$hst_index]));

		$grand_total_index = array_search("N DE FACTURE", $pageLines[$last_page_index]) + 3;
		$data['grand_total'] = trim(str_replace("Sub Total/Sous-Total", "", $pageLines[$last_page_index][$grand_total_index]));

		echo "<pre>";
		print_r($pageLines[$last_page_index]);
		echo "</pre>";

		return $data;

	}
	
	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Samsung/INVOICE_10.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Samsung/INVOICE_11.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Samsung/INVOICE_12.pdf"));
	echo "</pre>";


	