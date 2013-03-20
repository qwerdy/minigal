<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// FUNCTIONS
//-----------------------



// Something is changed.... quick fix
//------------------------------------------------------------------------
define('SQLITE_ASSOC', PDO::FETCH_ASSOC);

function sqlite_escape_string($s) {
	return SQLite3::escapeString($s);
}
//------------------------------------------------------------------------


function is_directory($filepath) {
	// $filepath must be the entire system path to the file
	if (!@opendir($filepath)) return FALSE;
	else {
		return TRUE;
		closedir($filepath);
	}
}
function recursive_dirlist($folder) {
	global $output;
	$output .= '<div class="directories" style="display:none;">';
	$directory = opendir($folder);
	while($dir = readdir($directory)) {
		if($dir != "." && $dir != "..") {
			if(is_dir($folder.'/'.$dir)) {
				$path = $folder.'/'.$dir;
				$output .= "<div><b class=\"clickable\" onclick=\"toggleNeighbor(this)\">$dir:</b> <a href='?q=a&job=update&scan=$path'>Scan</a> - <a href='?q=a&job=update&exif=&scan=$path'>Exif scan</a></div>";
				recursive_dirlist($path);
			}
		}
	}
	$output .= '</div>';
}
function changesort() {
	if(isset($_GET['sort'])) {
		switch($_GET['sort']) {
					case 'folder:name:asc':
									setcookie('sort:folder',"nasc" ,2145938400);
									break;
					case 'folder:name:desc':
									setcookie('sort:folder',"ndesc" ,2145938400);
									break;
					case 'folder:date:asc':
									setcookie('sort:folder',"dasc" ,2145938400);
									break;
					case 'folder:date:desc':
									setcookie('sort:folder',"ddesc" ,2145938400);
									break;
					case 'file:name:asc':
									setcookie('sort:file',"nasc" ,2145938400);
									break;
					case 'file:name:desc':
									setcookie('sort:file',"ndesc" ,2145938400);
									break;
					case 'file:date:asc':
									setcookie('sort:file',"dasc" ,2145938400);
									break;
					case 'file:date:desc':
									setcookie('sort:file',"ddesc" ,2145938400);
									break;
					case 'file:size:asc':
									setcookie('sort:file',"sasc" ,2145938400);
									break;
					case 'file:size:desc':
									setcookie('sort:file',"sdesc" ,2145938400);
									break;
				}
		if($_SERVER['HTTP_REFERER'])
			header("Location: {$_SERVER['HTTP_REFERER']}");
	}
}

function padstring($name, $length) {
	global $label_max_length;
	if (!isset($length)) $length = $label_max_length;
	if (strlen($name) > $length) {
      return substr($name,0,$length) . "...";
   } else return $name;
}

function getrandomImage($dirname) {
	$imageName = false;
	$ext = array("jpg", "png", "jpeg", "gif", "JPG", "PNG", "GIF", "JPEG");
	if($handle = opendir($dirname))
	{
		$image_array = array(); 
		$i=0;
		while(false !== ($file = readdir($handle)))
		{
			$lastdot = strrpos($file, '.');
			$extension = substr($file, $lastdot + 1);
			if ($file[0] != '.' && in_array($extension, $ext)) $image_array[] = $file;
			if($i++ > 50) break; // selects a random image from the 50 first
		}
		shuffle($image_array); 
		$imageName = @$image_array[0];
		closedir($handle);
	}
	return($imageName);
}

function sqlite_login($db, $username, $password) {
	if(empty($username) || empty($password))
		return false;
	$_SESSION['username'] = $username = sqlite_escape_string($username);
	$password = sha1($password);
	return $db->query("SELECT count(*) FROM users WHERE username='$username' AND password='$password'")->fetchColumn() > 0;
}

function readEXIF($file) {
		$extension = strtolower(end(explode('.',$file)));
		if($extension != 'jpg' && $extension != 'jpeg')
			return "";
			
		$exif_data = "";
		$exif = @exif_read_data($file, 0, true);
		
        $emodel = @$exif['IFD0']['Model'];
        $efocal = @$exif['IFD0']['FocalLength'];
		if($efocal) {
			list($x,$y) = explode('/', $efocal);
			$efocal = round($x/$y,0);
		}
		//$lat_ref = $exif['GPS']['GPSLatitudeRef']; 
		$lat = @$exif['GPS']['GPSLatitude'];
		if($lat) {
			list($num, $dec) = explode('/', $lat[0]);
			$lat_s = $num / $dec;
			list($num, $dec) = explode('/', $lat[1]);
			$lat_m = $num / $dec;
			list($num, $dec) = explode('/', $lat[2]);
			$lat_v = $num / $dec;
		 
			//$lon_ref = $exif['GPS']['GPSLongitudeRef'];
			$lon = @$exif['GPS']['GPSLongitude'];
			list($num, $dec) = explode('/', $lon[0]);
			$lon_s = $num / $dec;
			list($num, $dec) = explode('/', $lon[1]);
			$lon_m = $num / $dec;
			list($num, $dec) = explode('/', $lon[2]);
			$lon_v = $num / $dec;
		 
			$gps_int = array($lat_s + $lat_m / 60.0 + $lat_v / 3600.0, $lon_s + $lon_m / 60.0 + $lon_v / 3600.0);
		}
       
        $eexposuretime = @$exif['EXIF']['ExposureTime'];
        $efnumber = @$exif['EXIF']['FNumber'];
		if($efnumber) {
			list($x,$y) = explode('/', $efnumber);
			$efnumber = round($x/$y,0);
		}
		
        $eiso = @$exif['EXIF']['ISOSpeedRatings'];
        $edate = @$exif['IFD0']['DateTime'];
		
		if ($emodel OR $efocal OR $eexposuretime OR $efnumber OR $eiso OR $lat) $exif_data .= "::";
        if ($emodel) $exif_data .= "$emodel";
        if ($efocal) $exif_data .= " | $efocal" . "mm";
        if ($eexposuretime) $exif_data .= " | $eexposuretime" . "s";
        if ($efnumber) $exif_data .= " | f$efnumber";
        if ($eiso) $exif_data .= " | ISO $eiso";
		if ($lat) $exif_data .= ' | <a href="http://maps.google.com/?q='.$gps_int[0].','.$gps_int[1].'" target="_blank">Location</a>';
        return($exif_data);
}

function checkpermissions($file) {
	global $messages;
	if (substr(decoct(fileperms($file)), -1, strlen(fileperms($file))) < 4 OR substr(decoct(fileperms($file)), -3,1) < 4) $messages = "At least one file or folder has wrong permissions. Learn how to <a href='http://minigal.dk/faq-reader/items/how-do-i-change-file-permissions-chmod.html' target='_blank'>set file permissions</a>";
}

function clearthumb() {
	$dir = 'cache';
	if (is_dir($dir)) {
    $directory_list = opendir($dir);
		while($file = readdir($directory_list))	{
			if($file != '.' && $file != '..')
				unlink($dir.'/'.$file);
       }
	   closedir($directory_list);
     }
   }
?>