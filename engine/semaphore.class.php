<?php

define('SYNC_SEMAPHORE_SWEEP_TIME',(60*60*10)); //10 hours

class Semaphore {

	var $sname;
	var $slock;
	
	//public
	function Semaphore($sname,$scomment='',$bforce=false) {
		$this->sname = $sname;
		$this->slock = time().'/'.rand(1000,9999);
		if(!$this->isFree($sname)) {
			if($bforce) unlink(TEMP_PATH.$sname.'.lock');
			else return false;
		}
		$handle = fopen(TEMP_PATH.$sname.'.lock','w');
		fputs($handle,$this->slock);
		fputs($handle,"\n".$scomment);
		fclose($handle);
	}

	function isFree($sname=false) {
		if(!$sname) $sname = $this->name;
		if(!file_exists(TEMP_PATH.$sname.'.lock')) return true;
		
		$stime = $this->getTime($sname);
		if((int)$stime + (SYNC_SEMAPHORE_SWEEP_TIME) > time()) return false;
		
		//auto clear outdated semaphores
		if(file_exists(TEMP_PATH.$sname.'.lock')) unlink(TEMP_PATH.$sname.'.lock');
		
		return true;
	}

	function isMine() {
		$sname = $this->sname;
		// echo "semaphore me".$this->slock." file".$this->getSlock($sname)."\n";
		if(strcmp($this->getSlock($sname),$this->slock)==0) return true;
		return false;
	}
	
	function clear() {
		$sname = $this->sname;
		
		if(file_exists(TEMP_PATH.$sname.'.lock')) 
		if($this->getSlock($sname)==$this->slock) 
		unlink(TEMP_PATH.$sname.'.lock');
		
		return true;
	}

	//private
	function getSlock($sname) {
		if(!file_exists(TEMP_PATH.$sname.'.lock')) return false;
		$handle = fopen(TEMP_PATH.$sname.'.lock','r');
		$sline = trim(fgets($handle));
		fclose($handle);
		// echo "slock[$sline]\n";
		return $sline;
	}
	
	function getTime($sname) {
		$sline = $this->getSlock($sname);
		$ans = explode('/',$sline);
		return $ans[0];
	}

}

