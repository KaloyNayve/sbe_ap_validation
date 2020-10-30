<?php
	function printArr($arr) {
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	} 

    function isPoNumber($po) {
        include '../db/dbCon.php';
        $qry = "SELECT * from commandes_entetes where numcommande_four = '$po' and numcommande_four not in ('T')";
        $res = $conn->query($qry)->fetchAll(PDO::FETCH_ASSOC);
        if (empty($res)) {
            return false;
        }
        return true;
	}
	
	
	

	function cleanString($str)
	{       
	    $str = utf8_decode($str);
	    $str = str_replace("&nbsp;", "", $str);
	    $str = preg_replace("/\s+/", "", $str);
	    $str = preg_replace('/[^A-Za-z0-9.\-]/', '', $str); // remove special characters
	    return $str;
	}


	function deleteDir($path) {
		# delete dir and contents
	    if (empty($path)) { 
	        return false;
	    }
	    return is_file($path) ?
	            @unlink($path) :
	            array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
	}

	function ProcessHTML($file) {
		# gets the contents of the html file generated inside the body
		$result = [];
		$html = file_get_contents($file);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$dom->preserveWhiteSpace = false;
		$xpath = new DOMXPath($dom);

		$body = $xpath->query("/html/body/text()");		

		foreach ($body as $element) {
			if ($element->nodeValue !== "") {
				array_push($result, trim($element->nodeValue));
			}
		}

		return $result;
	}

	function getTotalPages($out) {
		#returns total pages from pdfinfo output array
		$index = "";
		foreach ($out as $key => $value) {
			if(stripos($value, "Pages:") !== false) {
				$index = $key;
				break;
			}
		}
		
		return str_replace("Pages: ", "", $out[$index]);
	}

	function getInvoiceDetails($contents) {
		$invoice_details = [];
		$invoiceNumberIndex = array_search("Mississauga ON L5N 0B9", $contents) + 1;
		$invoice_details['INVOICE_NUMBER'] = $contents[$invoiceNumberIndex];
		$invoiceDateIndex = $invoiceNumberIndex + 1;
		$invoice_details['INVOICE_DATE'] = $contents[$invoiceDateIndex];
		$subtotalIndex = array_search("Sub Total/Sous-Total", $contents);
		$string = str_replace("GST/HST", "", $contents[$subtotalIndex - 1]);
		$string = cleanString($string);
		$invoice_details['HST_GST'] = $string;
		$subtotalIndex = array_search("Sub Total/Sous-Total", $contents) + 1;
		$invoice_details['SUBTOTAL'] = cleanString($contents[$subtotalIndex]);
		$grandtotalIndex = array_search("TOTAL", $contents) + 1;
		$invoice_details['GRAND_TOTAL'] = cleanString($contents[$grandtotalIndex]);
		return $invoice_details;
	}

	function processInvoiceItemsB($items_array) {
		$new_items_array = [];
		$i = 0; //element counter
		$j = 0; //array counter
		foreach ($items_array as $key => $value) {            
			$i++;
			if ($i === 1) {
				// instantiate a new item in the new items array when its the i is at 1
				$new_items_array[$j] = [];
			}
			// push value into new items array
			array_push($new_items_array[$j], $value);
			// every 9th element the $i counter resets and adds +1 to h
			if ($i === 9) {
				// reset i counter
				$i = 0;
				$j++;
			}
		}
		return $new_items_array;
	}

    function processInvoiceItems($items) {
        $new_items = [];
        $i = 0; // array element counter
		foreach ($items as $key => $value) {
			if(isPoNumber($value)) {
                $i++;
                $new_items[$i] = [];
                array_push($new_items[$i], $value);
            } else {
                array_push($new_items[$i], $value);
            }
        }
        
        return $new_items;
	}

	function segregateInvoiceItems($items) {
		$result = [];
		foreach ($items as $key => $value) {
			$arr = [];
			$arr['PO_NUMBER'] = $value[0];
			$arr['PART_SHIPPED'] = $value[1];
			$arr['TOTAL_AMOUNT'] = cleanString($value[count($value) - 1]);
			// get part qty and part unit price
			$line_count = count($value);

			if ($line_count === 9) {
				$arr['PART_QTY'] = cleanString($value[3]);
				$arr['UNIT_PRICE'] = cleanString($value[4]);
			}

			if ($line_count === 8) {				
				// checks if 4th value of the array is a unit a price
				$validUnitPrice = strpos($value[3],".");				
				if ($validUnitPrice) {
					$unitPriceArr = explode(" ",$value[2]);
					$unitPriceIndex = count($unitPriceArr) - 1;
					$arr['PART_QTY'] = $unitPriceArr[$unitPriceIndex];
					$arr['UNIT_PRICE'] = cleanString($value[3]);					
				} else {
					$arr['PART_QTY'] = cleanString($value[3]);
					$arr['UNIT_PRICE'] = cleanString($value[4]);
				}
			}

			if ($line_count === 7) {
				$arr['UNIT_PRICE'] = cleanString($value[3]);
				$unitPriceArr = explode(" ",$value[2]);
				$unitPriceIndex = count($unitPriceArr) - 1;
				$arr['PART_QTY'] = $unitPriceArr[$unitPriceIndex];
			}

			array_push($result, $arr);
		}

		return $result;
	}


	function processPDF_Samsung($pdf) {
		// pdf location
		// $pdfDir = "Samsung/";
		// output dir path
		$outputDir = "output/";
		// path to pdfinfo binary
		$pdfinfo = 'C:/poppler-0.68.0/bin/pdfinfo';
		// path to pdftohtml binary
		$pdftohtml = 'C:/poppler-0.68.0/bin/pdftohtml';		
		// shell command
		// $cmd = "{$pdfinfo} {$pdfDir}{$pdf}";
		$cmd = "{$pdfinfo} {$pdf}";
		// convert pdf to html through shell command
		exec($cmd, $out, $ret);	
	  	// get total pages
	  	$total_pages = getTotalPages($out);
	
		//pdf name
		// $pdfName = substr($pdf, 0, strpos($pdf, "."));
		$pdfName = substr($pdf, strripos($pdf, "/") + 1);
		$pdfName = substr($pdfName, 0, strpos($pdfName, "."));
		
		$folderToCreate = $outputDir . $pdfName;

		if (!is_dir($folderToCreate)) {
			// create folder
			mkdir($folderToCreate, 0777, true);
		}	

		if (is_dir($outputDir . $pdfName)) {
			// instantiate invoice items array and invoice details array
			$items_array = [];
			$invoice_details = [];
			for ($i= 1; $i <= $total_pages; $i++) { 
				// loop through the pages to parse through the contents
				$page = $i;
				// created html path and name
				$createdFile = "{$outputDir}{$pdfName}/{$pdfName}-{$page}.html";
				// convert pdf to html for page 1
				// $cmd = "{$pdftohtml} -f {$page} -l {$page} -noframes {$pdfDir}{$pdf} {$createdFile}";				
				$cmd = "{$pdftohtml} -f {$page} -l {$page} -noframes {$pdf} {$createdFile}";
				exec($cmd, $out, $ret);
				if ($ret === 0 && file_exists($createdFile)) {
					$contents = ProcessHTML($createdFile);
					if (in_array("CREDIT MEMO NO.", $contents)) {
						// invoice is a credit memo return null
						return null;
					} else {	
						
                        $startIndex = array_search("Part# Ordered", $contents) + 1;
                        // echo "<br> items Start at " . $startIndex;
                        $endIndex = array_search("* Please remit to:", $contents) - 1;
                        // echo "<br> items end at " . $endIndex;
						//get invoice details in the last page
						if ($i == $total_pages) {
							$invoice_details = getInvoiceDetails($contents);
						}
						
						foreach ($contents as $key => $value) {
							if ($key > $startIndex && $key <= $endIndex) {	
                                						
								array_push($items_array, $value);
							}							
						}
					}					
				}				
            }
            
			$result = [];
			$result['INVOICE_DETAILS'] = $invoice_details;	
			//index the array numerically.
			$items_array = array_values($items_array);
			//sprintArr($items_array);
			// set items array in the results			
			$processedItems = processInvoiceItems($items_array);
			//printArr($processedItems);			
			$result['INVOICE_ITEMS'] = segregateInvoiceItems($processedItems);
			return $result;
		}
		//delete the folder
		deleteDir($folderToCreate);
	}

	$pdf = "Samsung/INVOICE_30.pdf";
	$data = processPDF_Samsung($pdf);
	printArr($data);

	
	
	