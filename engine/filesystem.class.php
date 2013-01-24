<?php

class Filesystem {

	function remove($path,$bverbose=false) {
		//usuwanie zawartoœci katalogów
		if(strrpos($path,"/*") == strlen($path)-2) {
			$path2 = substr($path,0,-2);
			if(file_exists($path2)) {
				if(is_dir($path2)) {
					$dirhandle = opendir($path2);
					if ($dirhandle) {
					    while (false !== ($file = readdir($dirhandle))) { 
							if ($file == '.' || $file == '..') continue;
							Filesystem::remove($path2.'/'.$file,$bverbose);
						}
						closedir($dirhandle); 
					}
				}
			}
		}
					
		//usuwanie katalogów
		else if(is_dir($path)) {
			$dirhandle2 = opendir($path);
			if ($dirhandle2) {
				while (false !== ($file2 = readdir($dirhandle2))) { 
					if ($file2 == '.' || $file2 == '..') continue;
					Filesystem::remove($path.'/'.$file2,$bverbose);
				}
				closedir($dirhandle2); 
			}
			if($bverbose) echo $path."\n";
			rmdir($path);
		}
		
		//usuwanie pliku
		else if(is_file($path)) {
			if($bverbose) echo $path."\n";
			unlink($path);
		}
		
		//usuwanie plików z wildcardem
		else if ($path[strlen($path)-1] == '*') {
			$dir = substr($path, 0, strrpos($path,'/'));
			$nam = rtrim(substr($path, strrpos($path,'/')+1),'*');
			// echo(" dir=$dir nam=$nam \n");
			$dirhandle2 = opendir($dir);
			if ($dirhandle2) {
				
				while (false !== ($file2 = readdir($dirhandle2))) { 
					if ($file2 == '.' || $file2 == '..') continue;
					// echo " file2=$file2 compare to ".substr($file2,0,strlen($nam))." \n";
					if (substr($file2,0,strlen($nam))==$nam) 
					// echo " kill $dir/$file2 \n";
					Filesystem::remove($dir.'/'.$file2,$bverbose);
				}
				closedir($dirhandle2); 
			}
		}
	}

	//recurent function to remove sfiles from sfile system
	//funkcja przyjmuje sciezke RZECZYWISTA, nie wirtualna!
	function dircopy($srcdir, $dstdir) {
		// umask(002);
	  	$num = 0;
	  	// echo("$srcdir");
	  	if(!is_dir($srcdir)) return;
	  	// echo("$srcdir");
	  	if(!is_dir($dstdir)) mkdir($dstdir,0775,true);
	  	// echo("$dstdir");
	  	if($curdir = opendir($srcdir)) {
	  	  // echo("opendir");
		   while($file = readdir($curdir)) {
			   if($file != '.' && $file != '..') {
	       		$srcfile = $srcdir . '/' . $file;
	       		$dstfile = $dstdir . '/' . $file;
	       		// echo("VF:check $srcfile");
	       		if(is_file($srcfile)) {

	           		// echo("VF: Copying '$srcfile' to '$dstfile'...\n");
	           		if(is_file($dstfile)) Filesystem::remove($dstfile);
	         		if(copy($srcfile, $dstfile)) {
	             		touch($dstfile, filemtime($srcfile)); $num++;
	             		//if($verbose) echo "OK\n";
	           		}
	           		else {
		           		//addLog("Error: File '$srcfile' could not be copied!\n","vfolder.log");
		           		return false;
	           		}

	       		}
	       		elseif(is_dir($srcfile)) {
	         		$res = Filesystem::dircopy($srcfile, $dstfile);
	         		if(!$res) return false;
	       		}
	     		}
	   	}
	   	closedir($curdir);
	  	}
	  	else return false;
	  	//return $num;
	  	return true;
	}

	
	
	function removeFromPublic($path,$bverbose=false) {
		if($path[0]!='/') $path = '/'.$path;
		// echo "removeFromPublic($path)\n";
		$public_path = substr(PUBLIC_PATH,0,-1);
		// die(" public_path=$public_path ");
		
		//usuwanie zawartoœci katalogów
		if(strrpos($path,"/*") == strlen($path)-2) {
			$path2 = substr($path,0,-2);
			if(file_exists($public_path.$path2)) {
				if(is_dir($public_path.$path2)) {
					$dirhandle = opendir($public_path.$path2);
					if ($dirhandle) {
					    while (false !== ($file = readdir($dirhandle))) { 
							if ($file == '.' || $file == '..') continue;
							Filesystem::removeFromPublic($path2.'/'.$file,$bverbose);
						}
						closedir($dirhandle); 
					}
				}
			}
		}
					
		//usuwanie katalogów
		else if(is_dir($public_path.$path)) {
			// echo " isdir $public_path"."$path ";
			if(!Filesystem::isProtected($path)) {
				$dirhandle2 = opendir($public_path.$path);
				if ($dirhandle2) {
					while (false !== ($file2 = readdir($dirhandle2))) { 
						if ($file2 == '.' || $file2 == '..') continue;
						Filesystem::removeFromPublic($path.'/'.$file2,$bverbose);
					}
					closedir($dirhandle2); 
				}
				if($bverbose) echo $path."\n";
				rmdir($public_path.$path);
			}
		}
		
		//usuwanie pliku
		else if(is_file($public_path.$path)) {
			// echo " isfile $public_path.$path ";
			if(!Filesystem::isProtected($path)) {
				if($bverbose) echo $path."\n";
				unlink($public_path.$path);
			}
		}
		
		//usuwanie plików z wildcardem
		else if ($path[strlen($path)-1] == '*') {
			$dir = substr($path, 0, strrpos($path,'/'));
			$nam = rtrim(substr($path, strrpos($path,'/')+1),'*');
			// echo(" dir=$dir nam=$nam \n");
			$dirhandle2 = opendir($public_path.$dir);
			if ($dirhandle2) {
				while (false !== ($file2 = readdir($dirhandle2))) { 
					if ($file2 == '.' || $file2 == '..') continue;
					// echo " file2=$file2 compare to ".substr($file2,0,strlen($nam))." \n";
					if (substr($file2,0,strlen($nam))==$nam) 
					// echo " kill $dir/$file2 \n";
					Filesystem::removeFromPublic($dir.'/'.$file2,$bverbose);
				}
				closedir($dirhandle2); 
			}
		}
	}
	
	function isProtected($sin) {
		if($sin[0]=='/') $sin = substr($sin,1);
		global $protectedFilesInPublic;
		// echo " check protection of $sin in ".print_r($protectedFilesInPublic,true)." ";
		foreach($protectedFilesInPublic as $sp)
		if(substr($sin,0,strlen($sp))==$sp) return true;
		// echo " unprotected $sin ";
		return false;
	}
	
	function sizeForHumans($bytes, $precision = 2) {
		$unit = array('B','KB','MB');
		return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).''.$unit[$i];
	}

}

	
