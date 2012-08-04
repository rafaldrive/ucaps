<?php

class Mailbox {

	//var $headers;
	//var $his;
	var $hconnection;
	var $oimapcheck;
	var $ooverview;
	var $auidsbymsgno;
	var $amsgnobyuid;
	var $aheadersbyuid;
	var $parsedmessageno;

	//CORE
	function Mailbox($string,$login,$pass) {
		$this->resetArrays();
		$this->hconnection = imap_open($string,$login,$pass);
		if(!$this->hconnection) return false;
	}
	
	function isConnected() {
		return $this->hconnection;
	}
	
	function getHandle() {
		return $this->hconnection;
	}

	function getTotal() {
		// $imapcheck = imap_check($this->hconnection);
		// return $imapcheck->Nmsgs;
		return imap_num_msg($this->hconnection);
	}

	function loadJustUids($nmax=9999,$bunseenonly=true) {
		$this->resetArrays();
		$imapcheck = imap_check($this->hconnection);
		//echo "got ".$imapcheck->Nmsgs." Nmsgs\n";
		
		$n = 0;
		$overview = imap_fetch_overview($this->hconnection,"1:{$imapcheck->Nmsgs}",0);
		//echo "overview count is ".count($overview)."\n";
		foreach($overview as $i => $ah) {
			//echo "$i has ".$ah->seen." \n";
			if(!$bunseenonly || ($bunseenonly && !$ah->seen)) {
				$this->auidsbymsgno[$ah->msgno] = $ah->uid;
				$this->amsgnobyuid[$ah->uid] = $ah->msgno;
				$this->aheadersbyuid[$ah->uid] = $ah;
				$n++;
				//echo "$i added\n";
			}
			//echo "n=$n nmax=$nmax\n";
			if($n==$nmax) return $this->auidsbymsgno;
		}
		return $this->auidsbymsgno;
	}

	function loadNaggers() {
		//$this->resetArrays();
		$imapcheck = imap_check($this->hconnection);
		
		$a = array();
		$n = 0;
		$overview = imap_fetch_overview($this->hconnection,"1:{$imapcheck->Nmsgs}",0);
		foreach($overview as $i => $ah) {
			$hi = imap_headerinfo($this->hconnection, $ah->msgno, 80, 80);
			$sfrom = trim(strtolower($hi->from[0]->mailbox . "@" . $hi->from[0]->host));
			if($a[$sfrom]>0) $a[$sfrom] = $a[$sfrom] +1;
			else $a[$sfrom] = 1;
		}
		return $a;
	}
	
	function getHeader($uid) {
		$aheader = array();
		$aheader['uid'] = $uid;
		$aheader['msgno'] = $this->amsgnobyuid[$uid];
		
		$hi = imap_headerinfo($this->hconnection, $this->amsgnobyuid[$uid], 80, 80);
		$aheader['ssubject'] = $this->decodeHeaderPart($hi->subject);
		$aheader['sfrom'] = trim(strtolower($hi->from[0]->mailbox . "@" . $hi->from[0]->host));
		$aheader['sfromname'] = $this->decodeHeaderPart($hi->from[0]->personal);
		
		$aheader['scccoded'] = $hi->ccaddress;
		$a = imap_mime_header_decode($hi->ccaddress);
		$s = '';
		for ($i=0; $i<count($a); $i++) $s.= $a[$i]->text;
		$aheader['sccorg'] = $s;
		
		//$aheader['treceived'] = $this->getRecDate($hi->date);
		$aheader['treceived'] = date('Y-m-d H:i',$hi->udate);
		// echo "hi-to\n";
		// print_r($hi->to);
		// $aheader['atos'] = $this->arrayizeMaillist($hi->to);
		$aheader['accs'] = $this->arrayizeMaillist($hi->cc);
		$aheader['scc'] = implode(',',$aheader['accs']);
			
		$header = $this->aheadersbyuid[$uid];
		
		$aheader['stocoded'] = $header->to;
		$a = imap_mime_header_decode($header->to);
		$s = '';
		for ($i=0; $i<count($a); $i++) $s.= $a[$i]->text;
		$aheader['stoorg'] = $s;
		
		$hrfc = imap_rfc822_parse_headers(imap_fetchheader($this->hconnection, $uid,FT_UID));
		// echo "hrfc\n";
		// print_r($hrfc);
		$aheader['atos'] = $this->arrayizeMaillist($hrfc->to);
		$aheader['sto'] = implode(',',$aheader['atos']);
		
		$aheader['tdate'] = $header->date;
		$aheader['nsize'] = $header->size;
		$aheader['bseen'] = $header->seen;
		$aheader['bunread'] = !$header->seen;
		$aheader['npriority'] = $header->x-priority;

		return $aheader;
	}
	
	function getBodyAsHtml($uid) {
		$msgno = $this->amsgnobyuid[$uid];
		$this->parseMessage($msgno);
		return $this->htmlBody;
	}
	
	function getBodyAsText($uid) {
		$msgno = $this->amsgnobyuid[$uid];
		$this->parseMessage($msgno);
		return $this->plainBody;
	}
	
	function listAttachments($uid) {
		$msgno = $this->amsgnobyuid[$uid];
		$this->parseMessage($msgno);
		return $this->attachments;
	}
	
	function saveAllAttachments($uid,$sdirto,$iduser=0) {
		$msgno = $this->amsgnobyuid[$uid];
		$this->parseMessage($msgno);
		$attachments = $this->attachments;
		if($attachments) {
			if(!file_exists($sdirto)) mkdir($sdirto,0777,true);
			foreach($attachments as $sfn => $data) {
				$spath = $sdirto.'/'.$this->decodeFilename($sfn);
				$fh = fopen($spath, 'wb');
				fwrite($fh, $data);
				fclose($fh);
			}
		}
	}

	//FUNCTIONAL
	function expunge() {
		imap_expunge($this->hconnection);
	}
	
	function del($uid) {
		return imap_delete($this->hconnection,$uid,FT_UID);
	}
	function delete($uid) { return $this->del($uid); }
	
	function markAsDeleted($uid) {
		return imap_setflag_full($this->hconnection, $uid, "\\Deleted",ST_UID);
	}
	
	function markAsFlagged($uid) {
		return imap_setflag_full($this->hconnection, $uid, "\\Flagged",ST_UID);
	}

	function markAsSeen($uid) {
		return imap_setflag_full($this->hconnection, $uid, "\\Seen",ST_UID);
	}

	function markAsUnseen($uid) {
		return imap_clearflag_full($this->hconnection, $uid, "\\Seen",ST_UID);
	}
	
	public function moveToImapFolder($uid,$folder) {
		return imap_mail_move($this->hconnection, $uid, $folder, CP_UID);
   }

	//PRIVATE
	function resetArrays() {
		$this->auidsbymsgno = array();
		$this->amsgnobyuid = array();
		$this->aheadersbyuid = array();
		$this->parsedmessageno = (-1);
	}
	
	function decodeFilename($in) {
		// echo "in $in \n\n";
		if(strpos(' '.$in,'=?iso-8859-2?Q?')>0)
		$in = quoted_printable_decode($in);
		if(strpos(' '.$in,'=?utf-8?Q?')>0) {
			// echo "uf8 qp detected\n";
			
			//this returns fucked up hdd-incompatible letters
			// $a = imap_mime_header_decode($in);
			// $in = '';
			// foreach($a as $x) $in.=$x->text;
			
			// $in = utf8_decode($in);
			// echo "utf8_decode $in \n";
			
			//all caps
			// $in = imap_utf8($in);
			// echo "imap_utf8 $in \n";
			
			$in = iconv_mime_decode($in,0,"UTF-8"); 
			// echo "iconv_mime_decode $in \n\n";
			
			// $in = iconv("UTF-8", "ISO-8859-1", $in);
			// echo "iconv $in \n\n";
			
			$in = preg_replace("/[^a-zA-Z0-9 .\s]/", '', $in);
			
		}
		if(strpos(' '.$in,'=?iso-8859-2?B?')>0)
		$in = base64_decode($in);
		// echo "should be good $in \n";
		if(strpos(' '.$in,'=?iso-8859-2?')>0)
		$in = substr($in,15);
		$in = mb_convert_encoding($in,'ASCII');
		$in = str_replace(' ','_',$in);
		return $in;
	}
	
	function decodeHeaderPart($in) {
		$s = $in;
		if(strpos(' '.$s,'=?')>0 || strpos(' '.$s,'?Q?')>0) {
			$s = mb_decode_mimeheader($s);
			$s = str_replace('_',' ',$s);
				//$s = quoted_printable_decode($s);
				//$s = substr($s,strpos($s,'?Q?')+3);
				//$s = substr($s,0,strpos($s,'?='));
			$s = trim($s);
			
			$scharset = substr($in,strpos($in,'=?')+2);
			$scharset = substr($scharset,0,strpos($scharset,'?Q?'));
			//echo "Subject charset detected:".$scharset."\n";
			//$s = mb_convert_encoding($s, "utf-8", $scharset);

			//echo('Decoded subject:'.$s."\n");
		}
		return $s;
	}
	
	function arrayizeMaillist($ar) {
		if(is_array($ar)) {
			$atos = array();
			foreach($ar as $ah) $atos[] = $ah->mailbox.'@'.$ah->host;
		}
		else $atos = array(0=>$ar);
		return $atos;
	}
	
	function getRecDate($date) {
		$date = substr($date, 5, 20);
		$timestamp = strtotime($date);
		return date('m-d-Y', $timestamp);
	}
	
	function parseMessage($msgno) {
		if($this->parsedmessageno == $msgno) return;
		$structure = imap_fetchstructure($this->hconnection, $msgno);
		if (!$structure->parts) {
			// If message is not multipart
			// echo " <br />nonMP ";
			$this->getMessagePart($msgno, $structure);
		}
		else {
			// echo " <br />MULTIPART ";
			// <pre>";
			// var_dump($structure->parts);
			// echo " </pre><hr /> ";
			foreach ($structure->parts as $partno => $part) {
				$this->getMessagePart($msgno, $part, $partno+1);
			}
			// foreach ($structure->parts as $part) {
				// $this->getMessagePart($msgno, $part);
			// }
		}
		$this->parsedmessageno = $msgno;
	}
	
	public function getMessagePart($msgno, $partObj, $partno=0) {
		echo "<br /><hr /><br />getMessagePart($msgno,part,$partno)<br />";
		// If partno is 0 then fetch body as a single part message
		//echo " imap_fetchbody($this->hconnection, $msgno, $partno, FT_PEEK); ";
		// echo "<hr /> partno=";//.(int)$partno." ";
		// var_dump($partno);
		// echo "<br /> msgno=".(int)$msgno." ";
		// var_dump($msgno);
		if($partno) $data = imap_fetchbody($this->hconnection, $msgno, $partno, FT_PEEK);	
		else $data = imap_body($this->hconnection, $msgno, FT_PEEK);
	
		// $data = ($partno) ? imap_fetchbody($conn, $messageId, $partno) : imap_body($conn, $messageId);

		// Any part may be encoded, even plain text messages, so decoding it
		echo " encoding ".$partObj->encoding."\n";
		if ($partObj->encoding == 4) {
			$data = quoted_printable_decode($data);
		}
		elseif ($partObj->encoding == 3) {
			$data = base64_decode($data);
		}

		// Collection all parameters, like name, filenames of attachments, etc.
		$params = array();
		if ($partObj->parameters) {
			foreach ((array) $partObj->parameters as $x) {
				echo " attribute ".$x->attribute."\n";
				$params[strtolower($x->attribute)] = $x->value;
				if ($x->attribute == 'charset' || $x->attribute == 'CHARSET') {
					
					echo " charset ".$x->value."\n";
					
					if (in_array(strtolower($x->value), array('windows-1250', 'iso-8859-2'))) {
						echo '[BODY : ENCODING] presented: ' . $x->value . ' => encoding to utf-8' . "\n";
						$data = iconv($x->value, 'UTF-8', $data);
					}
					
					if (in_array(strtolower($x->value), array('us-ascii', 'default'))) {
						require_once LIB_PATH.'mail/Mail_Encoding.php';
						$detected = Mail_Encoding::detect($data);
						if ($detected != 'utf-8') {
							echo '[BODY : ENCODING] presented: ' . $x->value . ' , detected: ' . $detected . ' => encoding to utf-8' . "\n";
							$data = iconv($detected, 'UTF-8', $data);
						}
					}
				}
			}
		}
		if ($partObj->dparameters) {
			foreach ((array) $partObj->dparameters as $x) {
				$params[strtolower($x->attribute)] = $x->value;
			}
		}

		if ($partObj->id) {
			if ($partObj->type == 5) { // IMAGE
				$extension = strtolower($partObj->subtype);
				$params['filename'] = md5($partObj->id) . '.' . $extension;
			}
		}


		// Any part with a filename is an attachment,
		if ($params['filename'] || $params['name']) {
			// Filename may be given as 'Filename' or 'Name' or both
			$filename = ($params['filename'])? $params['filename'] : $params['name'];

			if (empty($this->attachments[$filename])) {
				$this->attachments[$filename] = $data;
			}
		}

		// Processing plain text message
		if ($partObj->type == 0 && $data) {
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($partObj->subtype) == 'plain' || strtolower($partObj->subtype) == 'text/plain') {
				$this->plainBody = trim($data);
				// echo "<br /><br /><br /><hr /><br />PLAINBODY<br />$data<br />ENDPLAINBODY<hr /><br />";
			}
			else {
				$this->htmlBody = $data;
				// echo "<br /><br /><br /><hr /><br />HTMLBODY<br />$data<br />ENDHTMLBODY<hr /><br />";
			}
		}

		// Some times it happens that one message embeded in another.
		// This is used to appends the raw source to the main message.
		elseif ($partObj->type == 2 && $data) {
			$this->plainBody .= $data;
		}

		// Here is recursive call for subpart of the message
		if ($partObj->parts) {
			foreach ((array) $partObj->parts as $partno2 => $part2) {
				$this->getMessagePart($msgno, $part2, $partno.'.'.($partno2+1));
			}

			if ($partObj->subtype == 'RFC822') {
				echo '[RFC PART] detected: ' . $partno . "\n";

				for($i = (sizeof($partObj->parts)+1); ; $i++) {
					$partObj2 = imap_bodystruct($this->hconnection, $msgno, $partno . '.' . $i);
					if ( ! empty($partObj2)) {
						$this->getMessagePart($msgno, $partObj2, $partno . '.' . $i);
					} else {
						break;
					}
				}
			}
		}
	}
	
	function __destruct() {
		imap_close($this->hconnection);
	}

	function html2txt($document){
		$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
							'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
							'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
							'@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
		);
		$text = preg_replace($search, '', $document);
		return $text;
	}
	
}
