<?php  
	
	// Include Composer autoloader if not already done.
	include 'vendor/autoload.php';
	 
	// Parse pdf file and build necessary objects.
	// $parser = new \Smalot\PdfParser\Parser();
	// $pdf    = $parser->parseFile('invoice1.pdf');
	function getInvoiceNumber($var) {
		return strpos($var, "2020") !== false; 
	}
	
	function ifSamsung($var) {
		// convert to lower case 
		$var = strtolower($var);
		return strpos($var, "lg electronics") !== false; 
	}
	 
	// $text = $pdf->getText();
	// // echo $text;
	// $text = explode("\n", $text);
	// echo "<pre>";
	// print_r($text);
	// echo "</pre>";

	$parser = new \Smalot\PdfParser\Parser();
	$pdf    = $parser->parseFile('../documents/000000017.pdf');
	 
	// Retrieve all pages from the pdf file.
	$pages  = $pdf->getPages();

	// echo count($pages);

	 
	// Loop over each page to extract text.
	foreach ($pages as $page) {
	    
	    $text = $page->getText();
		// echo $text;
		$text = explode("\n", $text);
		echo "<pre>";
		print_r($text);
		echo "</pre>";

		

		// $invoice_number = array_filter($text, "getInvoiceNumber");
		// echo "<pre>";
		// print_r($invoice_number);
		// echo "</pre>";

		

		// $if_samsung = array_filter($text, "ifSamsung");
		// echo "<pre>";
		// print_r($if_samsung);
		// echo "</pre>";


	}
	