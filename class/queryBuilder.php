<?php 
	class QueryBuilder {

		protected $query;
		protected $tableName;
		protected $where = "";
		protected $order = "";
		protected $set = "";

		function __construct($tableName){
			$this->tableName = $tableName;
		}

		public function insertDb($data){
			$keys = array_keys($data);
			$column = "";
			$values = "";
			for($i = 0;$i < count($keys);$i++){
				$column .= $keys[$i];
				$values .= "'" . $data[$keys[$i]] . "'";
				if($i < count($keys)-1){
					$column .= ', ';
					$values .= ', ';
				}
			}
			$this->query = "INSERT INTO " . $this->tableName . "(" . $column . ") values (" . $values .")";
			return $this->query;
		}

		// function to insertDb with sequence(nextval function in oracle) for private key
		public function insertDb_with_sequence($data, $privateKey_column ,$name_of_sequence){
			$keys = array_keys($data);
			$column = "";
			$values = "";
			for($i = 0;$i < count($keys);$i++){
				$column .= $keys[$i];
				if($keys[$i] == $privateKey_column){
					$values .= $name_of_sequence . ".nextval";
				} else {
					$values .= "'" . $data[$keys[$i]] . "'";
				}				
				if($i < count($keys)-1){
					$column .= ', ';
					$values .= ', ';
				}
			}
			$this->query = "INSERT INTO " . $this->tableName . "(" . $column . ") values (" . $values .")";
			return $this->query;
		}

		public function insertDb_with_sequence2($data, $privateKey_column ,$name_of_sequence){
			$keys = array_keys($data);
			$column = "{$privateKey_column} ,";
			$values = "{$name_of_sequence}.nextval, ";
			for($i = 0;$i < count($keys);$i++){
				$column .= $keys[$i];
				$values .= "'" . $data[$keys[$i]] . "'";			
				if($i < count($keys)-1){
					$column .= ', ';
					$values .= ', ';
				}
			}
			$this->query = "INSERT INTO " . $this->tableName . "(" . $column . ") values (" . $values .")";
			return $this->query;
		}


		public function selectDb($column = "*"){			

			$selection = "";
			if(! is_array($column)){
				$selection .= $column;
			} else {
				for($i = 0;$i < count($column); $i++){
					$selection .= $column[$i];
					if($i < count($column) - 1){
						$selection .= ', ';
					}
				}
			}

			$this->query = "SELECT " . $selection . " FROM " . $this->tableName . " " . $this->where . " " . $this->order;
			return $this->query; 
		}

		public function where($column, $value){
			$this->where .= "WHERE " . $column . " = '" . $value . "'";
		}

		public function where_is($column, $value){
			$this->where .= "WHERE " . $column . " is '" . $value . "'";
		}

		public function where_and($column, $value){
			$this->where .= " AND " . $column . " = '" . $value . "'";
		}

		public function where_in($column, $value){
			$selection = "";
			if(! is_array($value)){
				$selection = "'" . $value . "'";
			} else {
				for($i = 0;$i < count($value); $i++){
					$selection .= "'" . $value[$i] . "'";
					if($i < count($value) - 1){
						$selection .= ", ";
					}
				}
			}
			$this->where .= "WHERE " . $column . " in (" . $selection . ")";
		}

		public function where_is_null($column){
			$this->where .= "WHERE " . $column . " is null";
		}

		public function and_is_null($column){
			$this->where .= " AND " . $column . " is null";
		}

		public function where_not($column, $value){
			$this->where .= "WHERE " . $column . " not '" . $value . "'";
		}

		public function order_by($column , $type = "desc"){
			$this->order .= "ORDER BY " . $column . " " . $type;
		}

		public function set($arr){
			$this->set = "SET "; 
			$column = array_keys($arr);
			for($i = 0; $i < count($arr);$i++){
				$this->set .= $column[$i] . " = '" . $arr[$column[$i]] . "'";
				if($i < count($arr) - 1){
					$this->set .= ", ";
				} 
			}
		}

		public function set_timestamp($column) {
			// updates timestamp column in oracle db
			// adds timestamp column to set string
			$this->set .= " , {$column} = sysdate";
		}


		public function updateDb(){
			$this->query = "UPDATE " . $this->tableName . " " . $this->set . " " . $this->where;
			return $this->query;
		}


	}