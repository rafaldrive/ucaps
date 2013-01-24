<?php

require_once '../config.php';

header('HTTP/1.1 200 OK');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Pragma: no-cache");                          // HTTP/1.0
header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
// header("Content-type: text/html; charset=utf-8");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

require_once ENGINE_PATH.'engine.functions.php';
require_once ENGINE_PATH.'capsule.class.php';

$surl = $_SERVER['REDIRECT_URL'];
$surl = stripslashes($surl);
$surl = htmlspecialchars($surl);
$surl = str_replace('%5C', '', $surl);
$surl = str_replace("\\", "", $surl);
$surl = rtrim($surl, '/');
$acapsule = getCapsule($surl);
// var_dump($acapsule);
$scaps = ucwords($acapsule['controller']);
$capsuleobject = new $scaps($acapsule['capsule'].'/'.$acapsule['view'].'.view');
// $capsuleobject->viewpath = $acapsule['capsule'].'/'.$acapsule['view'].'.view';
$capsuleobject->init($acapsule['params']);
$html = $capsuleobject->getHtml();
echo $html;

// real static file buffering
if(isset($capsuleobject->buffertime))
if($capsuleobject->buffertime>0) {
	$bufferpath = PUBLIC_PATH.$surl;
	if(!file_exists($bufferpath)) { mkdir($bufferpath,0775,true); chmod($bufferpath, 0775);}
	if(is_dir(PUBLIC_PATH.substr($surl,1))) {
		$sfile = substr($surl, 1).'/index.html';
		file_put_contents(PUBLIC_PATH.substr($surl, 1).'/index.html',$html);
		chmod(PUBLIC_PATH.substr($surl, 1).'/index.html', 0775);
 	}
 	else {
		$sfile = substr($surl,1).'.html';
 		file_put_contents(PUBLIC_PATH.substr($surl,1).'.html',$html);
 		chmod(PUBLIC_PATH.substr($surl,1).'.html', 0777);
 	}
	if($capsuleobject->buffertime<24*60*60) {
		$odb = getOdb();
		$odb->runInsert('buf_files_to_remove',array('spath'=>$sfile,'tplanned'=>date('Y-m-d H:i',time()+$capsuleobject->buffertime)));
	}
}

