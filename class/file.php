<?php


class File{
	
	var $fname;
	var $fpath;
	var $ftype;
	var $fuse;
	var $man;
	var $model;
	var $dept;
	var $cat;
	var $id;
	var $visibleto;
	var $extn;
	

	function __construct($fname,$fpath,$ftype,$fuse,$man,$model,$dept,$cat,$id,$visibleto,$extn){	
		$this->fname	=	$fname;
		$this->fpath	=	$fpath;
		$this->ftype	=   $ftype;
		$this->fuse		=	$fuse;
		$this->man		=	$man;
		$this->model	=	$model;
		$this->dept		=	$dept;
		$this->cat		=	$cat;
		$this->id		= 	$id;
		$this->visibleto= 	$visibleto;
		$this->extn 	= 	$extn;
		
	}
}


?>