
<?php 

    class User {

        public $username;
        public $first_name;
        public $last_name;
        public $badge;
        public $access;

        public function __construct($data) {
            $this->username = $data['UNAME'];
            $this->first_name = $data['FIRST_NAME'];
            $this->last_name = $data['LAST_NAME'];
            $this->badge = $data['BADGE'];
            $this->access = $data['ACCESS_LEVEL'];
        }       

    }