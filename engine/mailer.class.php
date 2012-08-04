<?php

if(!defined('EMAIL_FROM')) 
define('EMAIL_FROM','apache@localhost.org');
if(!defined('EMAIL_CHARSET')) 
define('EMAIL_CHARSET','utf-8');

class Mailer {

	function sendText($srecipient, $ssubject, $sbody, $sfrom=NULL, $scc=NULL, $sbcc=NULL) {
		$headers = '';
		if(!$sfrom) $sfrom = EMAIL_FROM;
		$headers.= "Content-type: text/plain; charset=".EMAIL_CHARSET."\r\n";
		$headers .= "From: $sfrom\r\n";
		if($scc) $headers.= "Cc: $scc\r\n";
		if($sbcc) $headers.= "Bcc: $sbcc\r\n";
		return mail($srecipient, $ssubject, $sbody, $headers);
	}

	function sendHtml($srecipient, $ssubject, $sbody, $sfrom=NULL, $scc=NULL, $sbcc=NULL) {
		$headers = '';
		if(!$sfrom) $sfrom = EMAIL_FROM;
		$headers.= "Content-type: text/html; charset=".EMAIL_CHARSET."\r\n";
		$headers .= "From: $sfrom\r\n";
		if($scc) $headers.= "Cc: $scc\r\n";
		if($sbcc) $headers.= "Bcc: $sbcc\r\n";
		return mail($srecipient, $ssubject, $sbody, $headers);
	}
	
}


