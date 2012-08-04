<?php

define('CURL_TIMEOUT_FOR_REQUESTS',25);
define('CURL_RETRIES',2);
define('CURL_FILENAME_MAX_LENGTH',40);

class Http {

	// example:
	// $sremoteurl = http://website.pl/banery/dzien_kobiet.jpg
	// $slocalpath = public/banery
	function copy($sremoteurl,$slocalpath) {
		$sremoteurl = str_replace(' ','%20',$sremoteurl);
		if(strlen($sremoteurl)>0) {
			// if(substr($sremoteurl,0,4)!="http") $sremoteurl = "http://$sremoteurl";
			// echo "HTTP copy $sremoteurl $slocalpath\n";
			$dfile = Http::get($sremoteurl);
			if(strlen($dfile)>0) {
				$path_parts = pathinfo($sremoteurl);
				$sfilename = $path_parts['basename'];
				$sfilename = str_replace(' ','_',$sfilename);
				$sfilename = str_replace('%20','_',$sfilename);
				$ext = strtolower($path_parts['extension']);

				if(strlen($sfilename)>CURL_FILENAME_MAX_LENGTH) $sfilename = substr($sfilename,-CURL_FILENAME_MAX_LENGTH);
				$slocalphoto = $slocalpath.'/'.$sfilename;

				$i=1;
				while(file_exists($slocalphoto)) {
					$i++;
					$slocalphoto = $slocalpath.'/'.$i.$sfilename;
				}

				$slocalphoto = $slocalpath.'/'.$sfilename;
				if(!file_exists($slocalpath)) mkdir($slocalpath,0777,true);
				if(file_put_contents($slocalphoto,$dfile)>0) return $slocalphoto;

				error_log(date('H:i.s').'ERROR: Cannot save file to '.$slocalphoto.' '."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
				return '';
			}
			error_log(date('H:i.s').'FATAL: cUrl returned no data from '.$sremoteurl.' '."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
			return '';
		}
		error_log(date('H:i.s').' ERROR: Empty remoteurl '."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
		return '';
	}


	
	function get($surl) {
		if(!function_exists('curl_init')) {
			echo 'FATAL: cUrl is not installed!';
			error_log(date('H:i.s').' FATAL: cUrl is not installed! '."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
			return '';
		}
		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
		for($i=0;$i<CURL_RETRIES;$i++) {
			$cUrl = curl_init();

			//anti connection problems
			$headers[] = 'Connection: keep-alive';
			//anti hangup
			//$headers[] = 'Expect:';
			curl_setopt($cUrl,CURLOPT_HTTPHEADER, $headers);

			curl_setopt($cUrl,CURLOPT_URL, $surl);
			curl_setopt($cUrl,CURLOPT_HEADER, 0);
			curl_setopt($cUrl,CURLOPT_USERAGENT, $user_agent);
			curl_setopt($cUrl,CURLOPT_FOLLOWLOCATION, 1);// allow redirects
//			if(CURL_IS_USING_PROXY===true)
			curl_setopt($cUrl,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cUrl,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($cUrl,CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($cUrl, CURLOPT_TIMEOUT, CURL_TIMEOUT_FOR_REQUESTS); // liczba sekund ile czekamy na polaczenie ze zdalna strona
			$PageContent = curl_exec($cUrl);

			if (curl_errno($cUrl)) {
				echo 'ERROR: cURL error #'.curl_errno($cUrl).' '.$surl.' '.curl_error($cUrl);
				error_log(date('H:i.s').' ERROR: cURL error #'.curl_errno($cUrl).' '.$surl.' '.curl_error($cUrl)."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
			}
			curl_close($cUrl);
			if(strlen($PageContent)>0) return $PageContent;

			sleep(2);
		}
		return $PageContent;
	}



	function post($sget,$apost) {
		if(!function_exists('curl_init')) {
			echo 'FATAL: cUrl is not installed!';
			error_log(date('H:i.s').' FATAL: cUrl is not installed! '."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
			return '';
		}
		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
		for($i=0;$i<CURL_RETRIES;$i++) {
			//ob_start();
			$cUrl = curl_init();

			//anti connection problems
			$headers[] = 'Connection: keep-alive';
			//anti hangup
			//$headers[] = 'Expect:';
			curl_setopt($cUrl,CURLOPT_HTTPHEADER, $headers);

			curl_setopt($cUrl,CURLOPT_URL, $sget);
			curl_setopt($cUrl,CURLOPT_HEADER, 0);
			curl_setopt($cUrl,CURLOPT_USERAGENT, $user_agent);
			curl_setopt($cUrl,CURLOPT_FOLLOWLOCATION, 1);// allow redirects
			//if(CURL_IS_USING_PROXY)
			curl_setopt($cUrl,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cUrl,CURLOPT_POST, true);

			//this shit makes hell from encoding with iso
			//curl_setopt($cUrl, CURLOPT_POSTFIELDS, http_build_query($apost));

			curl_setopt($cUrl, CURLOPT_POSTFIELDS, $apost);
			curl_setopt($cUrl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($cUrl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($cUrl, CURLOPT_TIMEOUT, CURL_TIMEOUT_FOR_REQUESTS); // liczba sekund ile czekamy na polaczenie ze zdalna strona
			$PageContent = curl_exec($cUrl);
			//$PageContent = ob_get_contents();
			//ob_end_clean();

			if (curl_errno($cUrl)) {
				echo 'ERROR: cURL error #'.curl_errno($cUrl).' '.$surl.' '.curl_error($cUrl);
				error_log(date('H:i.s').' ERROR: cURL error #'.curl_errno($cUrl).' '.$surl.' '.curl_error($cUrl)."\n", 3, LOGS_PATH.'http-'.date('Y-m-d').".log");
			}
			curl_close($cUrl);
			if(strlen($PageContent)>0) return $PageContent;

			sleep(2);
		}
		return $PageContent;
	}

}


