<?php

class User{
	
	var $fname;
	var $uname;
	var $lname;
	var $account;
	var $role;
	var $processLine;	
	var $badge;
	var $access;
	
	function __construct($fname2,$lname2,$account2,$role2,$processLine2,$user_name2,$badge2,$access2){	
		$this->fname	=		$fname2;
		$this->lname	=		$lname2;
		$this->account	=		$account2;
		$this->role		=		$role2;
		$this->processLine	=	$processLine2;
		$this->uname 	= 		$user_name2;
		$this->badge 	= 		$badge2;
		$this->access 	= 		$access2;
	}
}

?>