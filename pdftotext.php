

<?php
	include ( 'PdfToText/PdfToText.phpclass' ) ;

	function  output ( $message )
	   {
		if  ( php_sapi_name ( )  ==  'cli' )
			echo ( $message ) ;
		else
			echo ( nl2br ( $message ) ) ;
	    }

	$file	=  'documents/lg_invoice.PDF' ;
	$pdf	=  new PdfToText ( $filename = $file, $options = 'PDFOPT_BASIC_LAYOUT') ;

	// output ( "Original file contents :\n" ) ;
	// output ( file_get_contents ( "$file.txt" ) ) ;
	// output ( "-----------------------------------------------------------\n" ) ;

	output ( "Extracted file contents :\n" ) ;
	output ( $pdf -> Text ) ;