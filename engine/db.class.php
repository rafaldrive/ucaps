<?php

define('DB_CHECK_MEM_USAGE',false);
define('DB_CLIENT_ENCODING','UTF8');
if(!defined('DB_QUERY_LOG')) define('DB_QUERY_LOG',false);
define('DB_WARNING_TIMELIMIT',28);
define('DB_WARNING_QUERYLIMIT',100);

class DatabaseInterface
{
	var $name;
	var $handle;
	var $res;
	var $sip;
	var $querylog;
	var $nquerries;
		
	//methods required to be implemented
	function Database($host, $port, $user, $password, $name) {}
	function query($query,$a4ps=null) {}
	function row() {}
	function insertId($table,$key) {}
	function escapeString($text) {}
	
	//parent methods
	function helper($host, $port, $user, $password, $name)
	{
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->name = $name;
		$this->querylog = array();
		$this->nquerries = 0;

		if(!$this->handle) {
			error_log(date("H:i")." Cannot connect to database $name !\n\n",3,LOGS_PATH.date("Y-m-d")."-odb-error.log");
	  		die();
		}
		
		if(DB_QUERY_LOG==true)
		error_log("\n".date("H:i")." Connected\n\n",3,LOGS_PATH.date("Y-m-d-H")."-odb-querries.log");
	}
	
	function queryBefore($query) {
		if(DB_QUERY_LOG==true) 
		error_log($query."\n",3,LOGS_PATH.date("Y-m-d-H")."-odb-querries.log");
		
		$this->nquerries = $this->nquerries+1;
		$this->time_before = time()+microtime();
	}
	
	function queryAfter($query)
	{
		$this->time_after = time()+microtime();
		$ttt = $this->time_after - $this->time_before;
		$ttt = substr($ttt,0,strpos($ttt,'.')+6);
		
		if($ttt>DB_WARNING_TIMELIMIT)
		$this->notify('warning',"slow query ".substr($ttt,0,strpos($ttt,'.'))."s","$query \n\ntook ".substr($ttt,0,strpos($ttt,'.')+3).'seconds');

		$mem = '';
		if(DB_CHECK_MEM_USAGE) 
		$mem = ', memory usage is '.memory_get_usage().' bytes';		
		
		if(DB_QUERY_LOG==true)
		error_log("ok, took $ttt seconds $mem \n\n",3,LOGS_PATH.date("Y-m-d-H")."-odb-querries.log");
	}
	
	
	
	//common methods
	function isConnected() {
		if($this->handle) return true;
		return false;
	}
	
	function getList($query,$sidfield='',$a4ps=null) {
		$res = $this->query($query,$a4ps);
		$out = array();
		$nr = 0;
		while($row = $this->row($res)) {
			$nr++;
			if(strlen($sidfield)>0) $out[$row[$sidfield]]=$row;
			else $out[$nr] = $row;
		}
		return $out;
	}

	function getRelation($query,$sidfield1,$sidfield2,$a4ps=null) {
		$res = $this->query($query,$a4ps);
		$out = array();
		while($row = $this->row($res)) $out[$row[$sidfield1]]=$row[$sidfield2];
		return $out;
	}

	function getOneRow($query,$a4ps=null) {
		$res = $this->query($query,$a4ps);
		if($res) if($row = $this->row($res)) return $row;
		return array();
	}

	function close() {
		if($this->nquerries>DB_WARNING_QUERYLIMIT)
		$this->notify('warning',"too many querries x".floor($this->nquerries / DB_WARNING_QUERYLIMIT)," \n\n");
	}
	
	function makeInsert($stabname, $avalues) {
		if(!is_array($avalues)) return "";
		if(count($avalues)==0) return "";
		$out = "INSERT INTO $stabname (";
		$anames = "";
		foreach($avalues as $k => $v) $anames.=",$k ";
		$out.= substr($anames,1);
		$out.= ") VALUES (";
		$a2 = "";
		foreach($avalues as $k => $v) {
			if(is_null($v) || $v=='null') {
				if($k[0]=='n' || $k[0]=='i' || $k[0]=='b') $a2.=",0 ";
				else $a2.=",null ";
			}
			else
			if($k[0]!="s" && (is_numeric($v) || is_float($v) || is_int($v))) {
				if($v==0) $a2.=",0 ";
				else $a2.=",$v ";
			}
			else
			if($k[0]=="s" || !is_numeric($v)) {
				if(strpos(' '.$v,"'")>0) if((2*substr_count($v,"'"))!=substr_count($v,"''")) $v = $this->escapeString($v);
				if(is_null($v)) $a2.=",null ";
				else $a2.=",'$v' ";
			}
			else {
				if(strlen($v)==0 || $v='null') $vv = "null";
				else $vv = $this->escapeString($v);
				$a2.=",$vv ";
			}
		}
		$out.= substr($a2,1);
		$out.= ")";
		// echo " makeInsert $out ";
		return $out;
	}

	function runInsert($stabname, $avalues) {
		return $this->query($this->makeInsert($stabname, $avalues));
	}

	function makeUpdate($stabname, $avalues, $scondition='') {
		if(!is_array($avalues)) return '';
		if(count($avalues)==0) return '';

		$out = "UPDATE $stabname SET ";
		$anames = "";
		foreach($avalues as $k => $v) {
			if(is_null($v) || $v=='null') {
				if($k[0]=='n' || $k[0]=='i' || $k[0]=='b') $out.=$k."=0,";
				else $out.=$k."=null,";
			}
			else
			if($k[0]!="s" && (is_numeric($v) || is_float($v) || is_int($v))) {
				if($v==0 && !is_null($v) && $v!='null') $out.=$k."=0,";
				else $out.=$k."=$v,";
			}
			else 
			if($k[0]=="s" || !is_numeric($v)) {
				if(strpos(' '.$v,"'")>0) if((2*substr_count($v,"'"))!=substr_count($v,"''")) $v = $this->escapeString($v);
				if(is_null($v)) $out.=$k."=null,";
				else $out.=$k."='".$v."',";
			}
			else {
				if(strlen($v)==0 || $v='null') $vv = "null";
				else $vv = $this->escapeString($v);
				$out.=$k."=$vv,";
			}
		}
		$out= substr($out,0, -1);
		if($scondition!='') $out.=" where ".$scondition;
		return $out;
	}

	function runUpdate($stabname, $avalues, $scondition='') {
		return $this->query($this->makeUpdate($stabname, $avalues, $scondition));
	}

	

	// PRIVATE
	function notify($stype,$stitle,$sbody) {
		$sip = '';
		if(isset($_SERVER['REMOTE_ADDR']))
		$sip = $_SERVER['REMOTE_ADDR'];
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$sip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		
		$sreferer = '';
		if(isset($_SERVER['HTTP_REFERER']))
		$sreferer = $_SERVER['HTTP_REFERER'];
		
		$suri = '';
		if(isset($_SERVER['HTTP_HOST']))
		if(isset($_SERVER['REQUEST_URI']))
		$suri = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		
		error_log(date("H:i")." from $sip \n URI $suri \n REF $sreferer \n $sbody \n\n",3,LOGS_PATH.date("Y-m-d")."-odb-".$stype.".log");
	}

	function processFormForQuery($aform,$stimestamp='text') {
		$out = array();
		foreach($aform as $k => $v) {
			if(strlen($v)==0) $out[$k] = "null";
			else {
				if($k[0]=="s") $out[$k] = "'".$this->escapeString((stripslashes($v)))."'::text";
				elseif($k[0]=="f") {
					$out[$k] = str_replace(",",".",$v);
					if(!is_numeric($out[$k])) $out[$k] = "null";
				}
				elseif($k[0]=="t") {
					if(strtolower($v)=="now()") $out[$k] = "now()";
					else $out[$k] = "'".$this->escapeString($v)."'::".$stimestamp;
				}
				else $out[$k] = $v;
			}
		}
		return $out;
	}
	
	function prepare($query,$a4ps=null) {
		if(!is_array($a4ps)) return $query;
		$sout = '';
		$n = 0;
		for($i=0;$i<strlen($query);$i++) {
			$v = $query[$i];
			if($v=='?') {
				$v = $a4ps[$n];
				if(!is_numeric($v)) $v = "'".$this->escapeString($v)."'";
				$sout.= $v;
				$n++;
			}
			else $sout.= $v;
		}
		return $sout;
	}

}


