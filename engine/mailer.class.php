<?php

if(!defined('EMAIL_FROM')) 
define('EMAIL_FROM','apache@localhost.org');
if(!defined('EMAIL_CHARSET')) 
define('EMAIL_CHARSET','utf-8');
if(!defined('EMAIL_OUTBOX_PATH')) 
define('EMAIL_OUTBOX_PATH',TEMP_PATH.'outbox/');

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
	
	function findFreeDir() {
		if(!file_exists(EMAIL_OUTBOX_PATH))	mkdir(EMAIL_OUTBOX_PATH,0775,true);
		$n=1;
		while(file_exists(EMAIL_OUTBOX_PATH.$n.'/details.mail')) $n++;
		mkdir(EMAIL_OUTBOX_PATH.$n,0775,true);
		return EMAIL_OUTBOX_PATH.$n.'/';
	}
	
	function queueHtml($srecipient, $ssubject, $sbody, $sfroms=false, $scc=false, $sbcc=false, $sattachmentspath=false) {
		// echo "queueHtml($srecipient, $ssubject, $sbody, $sfroms, $scc, $sbcc, $sattachmentspath) \n";
		if(!$sfroms) $sfroms = EMAIL_FROM;
		loadLib('string');
		$sfrom = strip_to_bare_email($sfroms);	
		$sfromname = strip_email_to_bare_name($sfroms);	
	
		$sdir = Mailer::findFreeDir();
		// echo " sdir=$sdir ";
		file_put_contents($sdir.'body.mail',$sbody);
		
		$details = "subject=$ssubject\n";
		$details.= "to=$srecipient\n";
		$details.= "cc=$scc\n";
		$details.= "bcc=$sbcc\n";
		$details.= "mode=text/html\n";		
		$details.= "from=$sfrom\n";
		$details.= "fromname=$sfromname\n";
		file_put_contents($sdir.'details.mail',$details);
		
		$headers = '';
		$headers.= "Content-type: text/html; charset=".EMAIL_CHARSET."\r\n";
		$headers .= "From: $sfrom\r\n";
		if($scc) $headers.= "Cc: $scc\r\n";
		if($sbcc) $headers.= "Bcc: $sbcc\r\n";
		file_put_contents($sdir.'headers.mail',$headers);
		
		// echo "aqq [$sattachmentspath] ";
		if($sattachmentspath) {
			loadLib('filesystem');
			// echo "dircopy ";
			Filesystem::dircopy($sattachmentspath,$sdir);
		}
		
		return file_put_contents(EMAIL_OUTBOX_PATH.'jobs.queue',$sdir);
	}
	
	function fillTemplate($stemplate,$adata) {
		ob_start();
		if(is_array($adata)) foreach($adata as $k => $v) $$k = $v;
		require TEMPLATES_PATH.$stemplate;
		return ob_get_clean();
	}
	
}


