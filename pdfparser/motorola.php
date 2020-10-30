<?php  

	function ProcessInvoice($file) {
		// Include Composer autoloader if not already done.
		include 'vendor/autoload.php';
		// array to be returned
		$data = [];
		
		
		 
		

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

		echo "<pre>";
		print_r($lines);
		echo "</pre>";

		// Get invoice number
		$invoice_number_index = array_search("Transaction Number", $lines) + 1;
		$data['invoice_number'] = trim(str_replace("Transaction Date", "", $lines[$invoice_number_index]));

		return $data;

	}
	
	
	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_1.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_2.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_3.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_4.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_5.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_6.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_7.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_8.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_9.pdf"));
	echo "</pre>";

	echo "<pre>";
	print_r(ProcessInvoice("../sample_invoice/Motorola/MOTOROLA_10.pdf"));
	echo "</pre>";