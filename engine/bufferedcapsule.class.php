<?php

class Bufferedcapsule extends Capsule {

	var $buffertime = DEFAULT_BUFFERTIME; 				// 0 jesli niebuforowany, albo liczba sekund = czas buforowania (default in config)
  	
	function init($sparams='') {
		// echo ' '.get_class($this).' buffertime is '.$this->buffertime.' ';
		if($this->buffertime>0) {
			$bufferpath = BUFFER_PATH.get_class($this).'/';
			if(!file_exists($bufferpath)) { mkdir($bufferpath,0775,true); chmod($bufferpath, 0775); }
			
			if ($sparams) $sfilename = $sparams;
			else $sfilename = 'index';

			$bufferfile = $bufferpath.'/'.$sfilename.'.html';
			if(file_exists("$bufferfile")) {
				$tlastchange = filectime($bufferfile);
				if($tlastchange + $this->buffertime > time()) {
					$this->html = file_get_contents($bufferfile);
					// echo ' '.get_class($this).' is using buffered content dated '.$tlastchange.' ';
					//return time left
					return $tlastchange + $this->buffertime - time();
				}
			}
		}
		
		parent::init($sparams);
		
		if($this->buffertime>0) file_put_contents($bufferfile,$this->html);
		return $this->buffertime;
	}

	function loadSubCapsule($path, $params='', $name=null) {
		$o = parent::loadSubCapsule($path,$params,$name);
		if(isset($o->buffertime)) $this->buffertime = min($this->buffertime,$o->buffertime);
		else $this->buffertime = 0;
		return $o;
	}
	
}

