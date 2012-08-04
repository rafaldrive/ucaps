<?php

class BufferedCapsule extends Capsule {

	var $buffertime = 0; 				// 0 jesli niebuforowany, albo liczba sekund = czas buforowania (default 24h)
  	
	function init($params='') {
		if($this->buffertime>0) {
			$bufferpath = BUFFER_PATH.get_class($this).'/';
			if(!file_exists($bufferpath)) { mkdir($bufferpath,0775,true); chmod($bufferpath, 0775); }
			
			if ($sparams) $sfilename = $sparams;
			else $sfilename = 'index';

			$bufferfile = $bufferpath.'/'.$sfilename.'.html';
			if(file_exists("$bufferfile")) {
				$tlastchange = filectime($bufferfile);
				if($tlastchange + $this->buffertime > time()) {
					$this->shtml = file_get_contents($bufferfile);
					//return time left
					return $tlastchange + $this->buffertime - time();
				}
			}
		}
		
		parent::init($sparams);
		
		if($this->buffertime>0) file_put_contents($bufferfile,$this->shtml);
		return $this->buffertime;
	}

	function loadSubCapsule($path, $params='', $name=null) {
		$o = parent::loadSubCapsule($path,$params,$name);
		$this->buffertime = min($this->buffertime,$o->buffertime);
		return $o;
	}
	
}

