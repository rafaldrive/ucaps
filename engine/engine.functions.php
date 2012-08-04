<?php

//returns database connection
//creates one when necesary
function getOdb() {
	global $odb;
	if(!is_object($odb)) {
		require_once ENGINE_PATH."db.".DB_ENGINE.".class.php";
		$odb = new Database(DB_HOST,DB_PORT,DB_USER,DB_PASS,DB_NAME,true);
	}
	return $odb;
}

function loadModel($sname) {
	if(file_exists(MODELS_PATH.$sname.'/'.$sname.'.model')) {
		require_once MODELS_PATH.$sname.'/'.$sname.'.model';
		return true;
	}
	if(file_exists(MODELS_PATH.$sname.'.model')) {
		require_once MODELS_PATH.$sname.'.model';
		return true;
	}
	die('uCaps ERROR: model '.$sname.' not installed!');
}

function loadLib($sname) {
	if(file_exists(ENGINE_PATH.$sname.'.class.php')) {
		require_once ENGINE_PATH.$sname.'.class.php';
		return true;
	}
	if(file_exists(ENGINE_PATH.$sname.'.functions.php')) {
		require_once ENGINE_PATH.$sname.'.functions.php';
		return true;
	}
	die('uCaps ERROR: library '.$sname.' not installed!');
}

function getCapsule($surl='') {
	serveStaticFiles($surl);
	$acapsule = getCapsuleFromUrlmap($surl);
	if(ALLOW_AUTO_URL_MAPPING) if(count($acapsule)==0) $acapsule = getCapsuleFromAutomapping($surl);
	if(count($acapsule)==0) $acapsule = array('capsule'=>'notfound','params'=>'surl='.$surl);
			
	if(empty($acapsule['controller'])) {
		if(file_exists(CAPSULES_PATH.$acapsule['capsule'].'/'.$acapsule['capsule'].'.controller')) 
		$acapsule['controller'] = $acapsule['capsule'];
		else
		$acapsule['controller'] = 'capsule';
	}
	
	if(empty($acapsule['view'])) {
		if($acapsule['controller']!='capsule') {
			if(file_exists(CAPSULES_PATH.$acapsule['capsule'].'/'.$acapsule['controller'].'.view')) 
			$acapsule['view'] = $acapsule['controller'];
			else $acapsule['view'] = false;
		}
		else {
			if(file_exists(CAPSULES_PATH.$acapsule['capsule'].'/'.$acapsule['capsule'].'.view'))
			$acapsule['view'] = $acapsule['capsule'];
			else die('404 page not found');
		}
	}
	
	if($acapsule['controller']!='capsule')
	require_once(CAPSULES_PATH.$acapsule['capsule'].'/'.$acapsule['controller'].'.controller');
	
	return $acapsule;
}

function getCapsuleFromUrlmap($surl) {
	$aurlparts = explode("/", $surl);
	$nurlparts = count($aurlparts);
	$nmatchcounter=0;
	$params = '';
	$n = 0;
	require_once '../urlmap.php';
	foreach ($urlmap as $code => $apage) {
		$n++;
		$capsulename = $apage['capsule'];
		if(isset($apage['controller'])) $controller = $apage['controller'];
		else $controller = null;
		if(isset($apage['view'])) $view = $apage['view'];
		else $view = null;
		$params = $apage['params'];		
		if($apage['urlschema']=='/' && $surl=='') break; 
		$aschema = explode("/", $apage['urlschema']);
		$nschema = count($aschema);
		if($nschema == $nurlparts)	{
			// echo " <br /> matching $code ".$apage['urlschema']." <br />";
			$nmatchcounter = 1;
			for($i=1; $i<$nurlparts; $i++) {
				$sparameter = isParam($aschema[$i]);
				// echo " test ".$aschema[$i]." against ".$aurlparts[$i]." or $sparameter ";
				if ($aschema[$i] === $aurlparts[$i] || $sparameter)	{
					// echo " MATCH ";
					$nmatchcounter++;
					if($sparameter) $params .= '&'.$sparameter.'='.$aurlparts[$i];
				}
			}
			// echo " nmatchcounter=$nmatchcounter nurlparts=$nurlparts ";
			if($nmatchcounter == $nurlparts) break; 
		}
	}
	//not found?
	if($n==count($urlmap)) if($nmatchcounter != $nurlparts) return array();
	
	if($params) if($params[0]=='&') $params = substr($params,1);
	if(empty($controller)) $controller = $capsulename;
	if(empty($view)) $view = $controller;
	
	if(!file_exists(CAPSULES_PATH.$capsulename.'/'.$controller.'.controller')) {
		if(!file_exists(CAPSULES_PATH.$capsulename.'/'.$view.'.view')) return array();
		$controller = 'capsule';
	}
	return array('capsule'=>$capsulename,'controller'=>$controller,'view'=>$view,'params'=>$params);
}

function isParam($sargument) {
	if(strlen($sargument)>0)
	if($sargument[0] == '[' && $sargument[strlen($sargument)-1] == ']')
	return str_replace("[", "", str_replace("]", "", $sargument));
	return false;
}

function getCapsuleFromAutomapping($surl) {
	if($surl[0]=='/') $surl = substr($surl,1);
	
	//accept only existing Capsules
	$aurlparts = explode("/", $surl);
	$capsulename = $aurlparts[0];
	if(!is_dir(CAPSULES_PATH.$capsulename)) return array();
	
	$nurlparts = count($aurlparts);
	if($nurlparts==2) {
	
		//url like /capsulename/view
		$path_parts = pathinfo($surl);
		$filename = $path_parts['basename'];
		$filename = str_replace(' ','_',$filename);
		$filename = str_replace('%20','_',$filename);
		$viewpath = CAPSULES_PATH.$capsulename.'/'.$filename.'.view';
		$capspath = CAPSULES_PATH.$capsulename.'/'.$filename.'.controller';
		if(file_exists($capspath)) return array('capsule'=>$capsulename,'controller'=>$filename,'view'=>$filename,'params'=>array());
		if(file_exists($viewpath)) return array('capsule'=>$capsulename,'controller'=>'capsule','view'=>$filename,'params'=>array());
		
	}
	if($nurlparts==1) {
	
		//url like /capsulename
		$viewpath = CAPSULES_PATH.$capsulename.'/'.$capsulename.'.view';
		if(file_exists(CAPSULES_PATH.$capsulename.'/'.$capsulename.'.controller')) $controller = $capsulename;
		else $controller = 'capsule';
		if(file_exists($viewpath)) return array('capsule'=>$capsulename,'controller'=>$controller,'view'=>$capsulename,'params'=>array());
		
	}
	return array();
}

function serveStaticFiles($surl) {
	if(strlen($surl)<4) return;
	if($surl[0]=='/') $surl = substr($surl,1);
	// echo " serveStaticFiles($surl) ";
	
	//accept only urls like: /capsulename/file
	$aurlparts = explode("/", $surl);
	$nurlparts = count($aurlparts);
	if($nurlparts!=2) return;
	
	//accept only designated file extensions
	$path_parts = pathinfo($surl);
	if(!isset($path_parts['extension'])) return;
	// var_dump($path_parts);
	$filename = $path_parts['basename'];
	$filename = str_replace(' ','_',$filename);
	$filename = str_replace('%20','_',$filename);
	$ext = strtolower($path_parts['extension']);
	global $allowServingStaticFiles;
	if(!in_array($ext,$allowServingStaticFiles)) return;
	
	//only existing files
	if(!file_exists(CAPSULES_PATH.$surl)) return;
	
	//serve
	switch($ext) {
		case 'css':
			header('Content-Type: text/css');
			break;
		
		case 'js':
			header('Content-Type: application/javascript');
			break;
		
		default:
			header('Content-Type: image/'.$ext);
			break;
	}
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: ' . filesize(CAPSULES_PATH.$surl));
	readfile(CAPSULES_PATH.$surl);
	die();
}

//uzupelnia schemat wartosciami z tablicy asocjacyjnej
function processedAssocValue($coded,$assoc) {
	$tekst = $coded;
	$advque = "";
	$founded = false;
	for($i=0;$i<strlen($tekst);$i++) {
		if (!$founded) {
			if($tekst[$i]=='[') {
				$founded = true;
				$varname = "";
			}
			else $advque .= $tekst[$i];
		} else {
			if($tekst[$i]==']') {
				$founded = false;
				$advque .= $assoc[$varname];
			}
			else $varname .= $tekst[$i];
		}
	}
	return $advque;
}

function name2url($sname) {
	$sText = html_entity_decode($sname);
	$aSzukaj = array('ć','Ć','ś','Ś','ą','Ą','ż','Ż','ó','Ó','ł','Ł','ś','Ś','ź','Ź','ń','Ń','ę','Ę');
	$aZamien = array('c','C','s','S','a','A','z','Z','o','O','l','L','s','S','z','Z','n','N','e','E');
	$sOK = "abcdefghijklmnopqrstuvwxyz";
	$sOK .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$sOK .= "0123456789";
	$sOK .= "-_";

	$sText = str_replace($aSzukaj, $aZamien, $sText);
	$sTextN = "";

	for ( $i = 0; $i < strlen($sText); $i++ )
	{
		if ( strpos($sOK,$sText[$i]) === false )
			$sTextN .= "_";
		else
			$sTextN .= $sText[$i];
	}

	return $sTextN;
}







