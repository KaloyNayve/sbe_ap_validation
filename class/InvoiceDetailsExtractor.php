<?php  
    /**     
     *          This file is part of the PdfParser library.
     *
     * @author  Carlo Nayve 
     * @date    2020-10-28    
     *
     *  Class that extracts invoice details that are mapped via Supplier
     *  It uses Biniries from poppler windows installer which can be found here http://blog.alivate.com.au/poppler-windows/
     *  Or in https://portal-ca.sbe-ltd.ca/sbe_applications  which should be installed in C:/
     *      
     *
     */



    class InvoiceDetailsExtractor {
        // pdo connection to database (depedency injection)
        private $connection;
        // output dir of temp files created
        private $outputDir = "output/";
        // path to pdfinfo binary
        private $PDFINFO = 'C:/poppler-0.68.0/bin/pdfinfo';
        // path to pdftohtml
        private $PDFTOHTML = 'C:/poppler-0.68.0/bin/pdftohtml';
        // Array with invoice details and invoice items to be returned
        private $result;
        // pdf file to be extracted
        private $pdfFile;
        // oem of pdf
        private $oem;

        //constructor
        function __construct($connection, $pdfFile, $oem) {
            $this->connection = $connection;
            $this->pdfFile = $pdfFile;
            $this->oem = $oem;            
        }

        private function isPoNumber($po) {            
            $qry = "SELECT * from commandes_entetes where numcommande_four = '$po' and numcommande_four not in ('T')";
            $res = $this->connection->query($qry)->fetchAll(PDO::FETCH_ASSOC);
            if (empty($res)) {
                return false;
            }
            return true;
        }
        
        
        
    
        private function cleanString($str)
        {  
            #cleans the string from unseen characters      
            $str = utf8_decode($str);
            $str = str_replace("&nbsp;", "", $str);
            $str = preg_replace("/\s+/", "", $str);
            $str = preg_replace('/[^A-Za-z0-9.\-]/', '', $str); // remove special characters
            $str = str_replace(chr(194), '', $str);
            return $str;
        }
    
    
        private function deleteDir($path) {
            # delete dir and contents
            if (empty($path)) { 
                return false;
            }

            
            return is_file($path) ?
                    @unlink($path) :
                    array_map(array($this, __FUNCTION__), glob($path.'/*')) == @rmdir($path);
        }

        private function getTotalPages() {
            // execute cmd command to get total pages using pdfinfo
            $cmd = "{$this->PDFINFO} {$this->pdfFile}";
            // execute command
            exec($cmd, $out, $ret);	
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

        private function convert2Html($file , $page) {
            $cmd = "{$this->PDFTOHTML} -f {$page} -l {$page} -noframes {$this->pdfFile} {$file}";
            // execute command
            exec($cmd, $out, $ret);
            return $ret;	
        }

        private function printArr($arr) {
            echo "<pre>";
            print_r($arr);
            echo "</pre>";
        }

        private function processHTML($file) {
            # get contents of converted html file
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

        public function extractData() {
            // Manages the methods by OEM to get Invoice Details and Items
            if ($this->connection === null || $this->connection === "") {
                throw new Exception("PDO connection error");
            }

            if ($this->pdfFile === null || $this->pdfFile === "" || !file_exists($this->pdfFile)) {
                throw new Exception("Pdf file is empty/File does not exists");
            }

            if ($this->oem === null || $this->oem === "") {
                throw new Exception("OEM is empty");
            }

            switch (strtolower($this->oem)) {
                case 'samsung':
                    $this->extractDataSamsung();
                    break;
                default:
                    $this->result = null;
                    break;                
            }

            return $this->result;
        }

        private function getInvoiceDetails_Samsung($contents) {
            # Maps the text contents of converted pdf for samsung details
            $invoice_details = [];
            $invoiceNumberIndex = array_search("Mississauga ON L5N 0B9", $contents) + 1;
            $invoice_details['INVOICE_NUMBER'] = $contents[$invoiceNumberIndex];
            $invoiceDateIndex = $invoiceNumberIndex + 1;
            $invoice_details['INVOICE_DATE'] = $contents[$invoiceDateIndex];
            $subtotalIndex = array_search("Sub Total/Sous-Total", $contents);
            $string = str_replace("GST/HST", "", $contents[$subtotalIndex - 1]);
            $string = $this->cleanString($string);
            $invoice_details['HST_GST'] = $string;
            $subtotalIndex = array_search("Sub Total/Sous-Total", $contents) + 1;
            $invoice_details['SUBTOTAL'] = $this->cleanString($contents[$subtotalIndex]);
            $grandtotalIndex = array_search("TOTAL", $contents) + 1;
            $invoice_details['GRAND_TOTAL'] = $this->cleanString($contents[$grandtotalIndex]);
            return $invoice_details;
        }

        private function processInvoiceItems_Samsung($items) {
            # arranges the invoice items to its own array per invoice line
            $new_items = [];
            $i = 0; // array element counter
            foreach ($items as $key => $value) {
                if($this->isPoNumber($value)) {
                    $i++;
                    $new_items[$i] = [];
                    array_push($new_items[$i], $value);
                } else {
                    array_push($new_items[$i], $value);
                }
            }
            
            return $new_items;
        }

        private function segregateInvoiceItems_Samsung($items) {
            # Maps the Samsung invoice items array so it can be labelled 
            # Returns associative array
            $result = [];
            foreach ($items as $key => $value) {
                $arr = [];
                $arr['PO_NUMBER'] = $value[0];
                $arr['PART_SHIPPED'] = $value[1];
                $arr['TOTAL_AMOUNT'] = $this->cleanString($value[count($value) - 1]);
                // get part qty and part unit price
                $line_count = count($value);
                // Mapping Rules
                if ($line_count === 9) {
                    $arr['PART_QTY'] = $this->cleanString($value[3]);
                    $arr['UNIT_PRICE'] = $this->cleanString($value[4]);
                }
    
                if ($line_count === 8) {				
                    // checks if 4th value of the array is a unit a price
                    $validUnitPrice = strpos($value[3],".");				
                    if ($validUnitPrice) {
                        $unitPriceArr = explode(" ",$value[2]);
                        $unitPriceIndex = count($unitPriceArr) - 1;
                        $arr['PART_QTY'] = $this->cleanString($unitPriceArr[$unitPriceIndex]);
                        $arr['UNIT_PRICE'] = $this->cleanString($value[3]);					
                    } else {
                        $arr['PART_QTY'] = $this->cleanString($value[3]);
                        $arr['UNIT_PRICE'] = $this->cleanString($value[4]);
                    }
                }
    
                if ($line_count === 7) {
                    $arr['UNIT_PRICE'] = $this->cleanString($value[3]);
                    $unitPriceArr = explode(" ",$value[2]);
                    $unitPriceIndex = count($unitPriceArr) - 1;
                    $arr['PART_QTY'] = $this->cleanString($unitPriceArr[$unitPriceIndex]);
                }
    
                array_push($result, $arr);
            }
    
            return $result;
        }
        
        private function getPdfName() {
           // get pdf name (not absolute path)
        //    $pdfName = substr($this->pdfFile, strripos($this->pdfFile, "/") + 1);
        //    $pdfName = substr($pdfName, 0, strpos($pdfName, "."));
           // get pdf name (absolute path)
           $pdfName = substr($this->pdfFile, strripos($this->pdfFile, "\\") + 1);
           $pdfName = substr($pdfName, 0, strpos($pdfName, ".")); 
           return $pdfName;
        }

        private function createDir($tempDir) {
            if (!is_dir($tempDir)) {
                // create folder
                mkdir($tempDir, 0777, true);
            }
        }
    
        private function extractDataSamsung() {
            // get total pages of pdf
           $totalPages = $this->getTotalPages(); 
           // Get pdf name
           $pdfName = $this->getPdfName();
           // create a temp dir to house converted files (to be deleted after)
           $tempDir = $this->outputDir . $pdfName;
           $this->createDir($tempDir);
            // if temp dir is created
            if (is_dir($tempDir)) {
                // instantiate invoice items array and invoice details array
                $items_array = [];
                $invoice_details = [];
                for ($i= 1; $i <= $totalPages; $i++) { 
                    // convert pdf to html to get text contents with wrapping
                    $convertedFile = "{$this->outputDir}{$pdfName}/{$pdfName}-{$i}.html";
                    $convert = $this->convert2Html($convertedFile , $i);
                    if ($convert === 0 && file_exists($convertedFile)) {
                        $contents = $this->processHTML($convertedFile);
                        if (in_array("CREDIT MEMO NO.", $contents)) {
                            // invoice is a credit memo return null
                            $this->result = null;
                            return null;
                        } else {	
                            
                            $startIndex = array_search("Part# Ordered", $contents) + 1;                            
                            $endIndex = array_search("* Please remit to:", $contents) - 1;                            
                            //get invoice details in the last page
                            if ($i == $totalPages) {
                                $invoice_details = $this->getInvoiceDetails_Samsung($contents);
                            }
                            
                            foreach ($contents as $key => $value) {
                                if ($key > $startIndex && $key <= $endIndex) {	
                                                            
                                    array_push($items_array, $value);
                                }							
                            }
                        }	
                    }
                }
                //instantiate return array
                $result['INVOICE_DETAILS'] = $invoice_details;
                //index the array numerically.
                $items_array = array_values($items_array);			
                // set items array in the results			
                $processedItems = $this->processInvoiceItems_Samsung($items_array);
                $result['INVOICE_ITEMS'] = $this->segregateInvoiceItems_Samsung($processedItems);
                $this->result = $result;
            }
            //delete the folder
            $this->deleteDir($tempDir);
            return null;  
        }

    }
    
