<?php

class Filesystem {

	function remove($path) {

		//usuwanie zawartoœci katalogów
		if(strrpos($path,"/*") == strlen($path)-2) {
			$path2 = substr($path,0,-2);
			
			if(file_exists($path2)) {
				if(is_dir($path2)) {
				
					$dirhandle = opendir($path2);
					
					if ($dirhandle) {

					    while (false !== ($file = readdir($dirhandle))) { 
							if ($file == '.' || $file == '..') continue;
							Filesystem::remove($path2.'/'.$file);
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
					Filesystem::remove($path.'/'.$file2);
				}
				closedir($dirhandle2); 
			}
			rmdir($path);
		}
		
		//usuwanie pliku
		else if(is_file($path)) {
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
					Filesystem::remove($dir.'/'.$file2);
				}
				closedir($dirhandle2); 
			}
		}
	}

	//recurent function to remove sfiles from sfile system
	//funkcja przyjmuje sciezke RZECZYWISTA, nie wirtualna!
	function dircopy($srcdir, $dstdir) {
		umask(002);
	  	$num = 0;
	  	//debug("$srcdir");
	  	if(!is_dir($srcdir)) return;
	  	//debug("$srcdir");
	  	if(!is_dir($dstdir)) mkdir($dstdir,0775,true);
	  	//debug("$dstdir");
	  	if($curdir = opendir($srcdir)) {
	  	  //debug("opendir");
		   while($file = readdir($curdir)) {
			   if($file != '.' && $file != '..') {
	       		$srcfile = $srcdir . '/' . $file;
	       		$dstfile = $dstdir . '/' . $file;
	       		//debug("VF:check $srcfile");
	       		if(is_file($srcfile)) {

	           		//debug("VF: Copying '$srcfile' to '$dstfile'...");
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

}

	
