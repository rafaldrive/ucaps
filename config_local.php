<?php

define('INSTANCE','local');
define('DEFAULT_BUFFERTIME',0);
define('GOOGLE_ANALYTICS_ID','');
define('SECURITY_SESSION_DEADLINE',time()+(3600*24*30)); //1msc

error_reporting(E_ALL ^ E_NOTICE);

// database connection
define('DB_HOST',		'127.0.0.1');
define('DB_NAME',		'databasename');	
define('DB_PORT',		'3306');
define('DB_USER',		'username');
define('DB_PASS',		'password');
define('DB_ENGINE',	'mysql');

