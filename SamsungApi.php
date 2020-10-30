<?php 
    
    include 'db/dbCon.php';
      
    function getCodeRef($imei) {
        /* Curl To samsung Api to get code ref */
        $soapUrl = "https://www.samsungasc.com/nawss/soap/sGSPN?wsdl"; // asmx URL of WSDL
        $soapUser = "SBECA";  //  username
        $soapPassword = "acuv1978sbe"; // password

        // xml post structure

        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sam="www.samsungasc.com">
             <soapenv:Header/>
             <soapenv:Body>
                <sam:GetIMEIInfo>
                    <sam:strXMLin>
                    <![CDATA[<?xml version="1.0" encoding="utf-8" ?>
                     <rootdoc>
                        <WSUserID>SBECA</WSUserID>
                        <WSPassword>acuv1978sbe</WSPassword>
                        <Company>C310</Company>                        
                        <IMEI>' . $imei . '</IMEI>
                                               
                      </rootdoc>
                      ]]>
                  </sam:strXMLin>
                </sam:GetIMEIInfo>
             </soapenv:Body>
          </soapenv:Envelope>';   // data from the form, e.g. some ID number

           $headers = array(              
              "Host: https://www.samsungasc.com",
              "Content-Type: application/soap+xml; charset=utf-8",
              "Content-Length: ".strlen($xml_post_string)
              ); 

            $url = $soapUrl;

            $proxyIP = "proxy.sbe-ltd.ca";
            $proxyPort = 3128;

            // PHP cURL  for https connection with auth
            $ch = curl_init();
            //Set the proxy IP.
            curl_setopt($ch, CURLOPT_PROXY, $proxyIP);
             
            //Set the port.
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch);
            //echo $response; 
            curl_close($ch);            
            // converting
            
            $response1 = str_replace("<soap:Body>","",$response);
            $response2 = str_replace("</soap:Body>","",$response1); 
            // formating response to get coderef
            $string =  substr($response, strpos($response, "ModelCode"));       
            $coderef = str_replace(substr($string, strripos($string, "/ModelCode")), "", $string);
            $coderef = str_replace("ModelCode", "", $coderef);
            $coderef = str_replace($coderef[0], "", $coderef);
            $coderef = str_replace("gt;", "", $coderef);
            $coderef = str_replace("lt;", "", $coderef);

            return $coderef;
          }


            
          if (isset($_POST['getCodeRef'])) {
            $input = filter_input_array(INPUT_POST);            
            $imei = trim($_POST['imei']);
            $coderef = getCodeRef($imei);
            $qry = "SELECT p.codepro from references R
                    LEFT OUTER JOIN PRODUITS P
                    ON p.numpro_int = r.numpro_int
                    WHERE R.CODEREF = '{$coderef}'";
            $res = $conn->query($qry)->fetch(PDO::FETCH_ASSOC);
            if (!empty($res)) {
              echo $res['CODEPRO'];
            } else {
              echo "not found";
            }
          }

          // $imei = trim($_POST['imei']); 

          // echo getCodeRef($imei);