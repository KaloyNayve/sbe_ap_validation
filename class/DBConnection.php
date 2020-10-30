<?php 
    class DBConnection extends PDO {

        private $site;
        private $host = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST=10.194.1.43)(PROTOCOL=tcp)(PORT=1521))(CONNECT_DATA=(SID=CA02)))";
        private $username = "";
        private $password = "artemis";

        function __construct($site = null){
            $this->site = $site;
            // switch statement to determine username depending on site
            switch ($site) {
                case '94':
                    $this->username = "sbe";
                    break;
                case '96':
                    $this->username = "site96";
                    break;
                case '39':
                    $this->username = "site39";
                    break;
                default:
                    $this->username = "sbe";
                    break;
            }
            parent::__construct($this->host, $this->username, $this->password);
            $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } 
    }
