<?php
/**
 * This is an example of a script running in background
 * with Semaphore
 */
 
require_once '../config.php';

//one instance at a time
loadLib('semaphore');
$semaphore = new Semaphore('maintenance');
if(!$semaphore->isMine()) die(0);

//establish db connection
require_once ENGINE_PATH.'engine.functions.php';
$odb = getOdb();



//PUT YOUR CODE HERE
//and do something 



//clear the semaphore
$semaphore->clear();

