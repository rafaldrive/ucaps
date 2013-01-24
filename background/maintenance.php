<?php
/**
 * This is an example of a script running in background
 * with Semaphore
 */
 
require_once '../config.php';
require_once ENGINE_PATH.'engine.functions.php';

//one instance at a time
loadLib('semaphore');
$semaphore = new Semaphore('maintenance','takeover mode',true);
if(!$semaphore->isMine()) die(0);

//establish db connection
// $odb = getOdb();

//clean cache
loadLib('filesystem');
echo "\n".date('Y-m-d H:i:s')."\n";
Filesystem::removeFromPublic('*',true);
Filesystem::remove(TEMP_PATH.'*',true);

//clear the semaphore
$semaphore->clear();

