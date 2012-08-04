<?php

//paths
define('ROOT_PATH', realpath(dirname(__FILE__).'/'));
define('PUBLIC_PATH', 		ROOT_PATH.'/public/');
define('BUFFER_PATH', 		ROOT_PATH.'/temp/');
define('LOGS_PATH', 			ROOT_PATH.'/logs/');
define('ENGINE_PATH', 		ROOT_PATH.'/engine/');
define('MODELS_PATH', 		ROOT_PATH.'/models/');
define('CAPSULES_PATH', 	ROOT_PATH.'/capsules/');
define('CONTENT_PATH', 		ROOT_PATH.'/content/');
define('TEMP_PATH', 			ROOT_PATH.'/temp/');
define('BACKGROUND_PATH', 	ROOT_PATH.'/background/');

//loading deployment-specific confugiration
if(file_exists(ROOT_PATH.'/location')) 
require_once ROOT_PATH.'/config_'.trim(file_get_contents(ROOT_PATH.'/location')).'.php';

//engine switches
define('ALLOW_AUTO_URL_MAPPING', true);
$allowServingStaticFiles = array('js', 'css', 'png', 'jpg', 'jpeg', 'gif');

//user config
date_default_timezone_set("Europe/Warsaw");
header("Content-type: text/html; charset=utf-8", true);
mb_internal_encoding("UTF-8");

