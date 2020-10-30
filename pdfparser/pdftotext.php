

<?php
	include ( 'PdfToText/PdfToText.phpclass' ) ;

	function  output ( $message )
	   {
		if  ( php_sapi_name ( )  ==  'cli' )
			echo ( $message ) ;
		else
			echo ( nl2br ( $message ) ) ;
	    }

	$file	=  '../documents/000000020' ;
	$pdf	=  new PdfToText ( "$file.pdf" ) ;

	// output ( "Original file contents :\n" ) ;
	// output ( file_get_contents ( "$file.txt" ) ) ;
	// output ( "-----------------------------------------------------------\n" ) ;

	output ( "Extracted file contents :\n" ) ;
	output ( $pdf -> Text ) ;