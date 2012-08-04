<?php

require_once ENGINE_PATH.'db.class.php';
class Database extends DatabaseInterface
{
	
	function Database($host, $port, $user, $password, $name)
	{
		$this->handle = mysql_connect($host,$user,$password,true); 
		parent::helper($host, $port, $user, $password, $name);
		
		$out = mysql_select_db($name);
		if(!$out) {
			error_log(date("H:i")." Cannot access base $name!\n\n",3,LOGS_PATH.date("Y-m-d")."-odb.log");
	  		die();
		}
		
		if(strlen(DB_CLIENT_ENCODING)>0 && DB_CLIENT_ENCODING!="DB_CLIENT_ENCODING") {
			mysql_set_charset(DB_CLIENT_ENCODING,$this->handle);
			//$this->query("SET client_encoding = '".DB_CLIENT_ENCODING."'");
		}
	}

	//a4ps - array for prep statements
	function query($query,$a4ps=null)
	{
		parent::queryBefore($query);
		if(is_array($a4ps)) $query = $this->prepare($query,$a4ps);
		$this->res = mysql_query ($query,$this->handle);
		parent::queryAfter($query);
		
		if (!$this->res) {
	  		$this->notify('error',"SQL error","$query \n\nLast error:\n".mysql_error($this->handle) );
			die();
		}
		return $this->res;
	}
	
	function row($res) {
		if($res) return mysql_fetch_assoc($res);
		return false;
	}
	
	function insertId($table,$key) {
		return mysql_insert_id($this->handle);
	}

	function escapeString($text) {
		return mysql_real_escape_string($text);
	}

}


