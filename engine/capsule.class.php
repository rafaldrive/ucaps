<?php

class Capsule {

	var $viewpath = '';			// default is [className].view
  	var $html = ''; 		

	function Capsule($view=false) {
		if($view) $this->viewpath = $view;
		else $this->viewpath = strtolower(get_class($this).'/'.get_class($this).'.view');
		$this->params = array();
	}

	function init($params='') {	
		$this->prepare($params);
		$this->html = $this->renderHtml($params);
		return 0;
	}

	function renderHtml() {
		ob_start();
		if(is_array($this->params)) foreach($this->params as $k => $v) $$k = $v;
		require CAPSULES_PATH.$this->viewpath;
		return ob_get_clean();
	}
	
	function getHtml() {
      return $this->html;
  	}

	function loadSubCapsule($path, $params='', $sname=null)
	{
		// echo " [loading subCapsule $path with $params] ";
		$name = strtolower($path);
		
		if(!file_exists(CAPSULES_PATH.$path.'.view')) 
		if(file_exists(CAPSULES_PATH.$path.'/'.$path.'.view')) 
		$path = $path.'/'.$path;
		
		if(strstr($path,'/')) {
			$a = explode('/',$path);
			$controller = ucwords($a[count($a)-1]);
			$name = strtolower($controller);
		}
		else $controller = ucwords($path);
		
		if(file_exists(CAPSULES_PATH.$path.'.controller')) require_once CAPSULES_PATH.$path.'.controller';
		else {
			if(DEFAULT_BUFFERTIME>0) {
				loadLib('bufferedcapsule');
				$controller = 'Bufferedcapsule';
				$params = name2url($path);
			}
			else $controller = 'Capsule';
		}
		
		$view = $path.'.view';
		
		$capsuleobject = new $controller($view);
		$capsuleobject->init($params);
				
		if(!is_null($sname)) $name = $sname;
		
		// echo " [name=$name] ";
		$this->params[$name] = $capsuleobject->getHtml();
		
		return $capsuleobject;
	}

  	// interface
  	function prepare($params) {
  		parse_str($params, $this->params);
  	}

}

