<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
// ------------ lixlpixel recursive PHP functions -------------
// scan_directory_recursively( directory to scan, filter )
// expects path to directory and optional an extension to filter
// ------------------------------------------------------------
function sdrec($directory, $database, $scanExif=FALSE, $filter=FALSE)
{
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}
	if(!file_exists($directory) || !is_dir($directory))
	{
		return FALSE;
	}elseif(is_readable($directory))
	{
		$directory_list = opendir($directory);
		while($file = readdir($directory_list))
		{
			if($file != '.' && $file != '..')
			{
				$path = $directory.'/'.$file;
				if(is_readable($path))
				{
					$subdirectories = explode('/',$path);
					if(is_dir($path))
					{	
						sdrec($path, $database, $scanExif, $filter);
						$name = sqlite_escape_string(end($subdirectories));
						$filemtime = filemtime($path);
						if ($filemtime > time()) $date = 0; // Some folders have absurd dates :\
						else $date = date("Y-m-d H:i:s", filemtime($path));
						$path = sqlite_escape_string(str_replace('photos/', '',$path));
						$stm = "INSERT INTO pictures (path, name, isfile, date) VALUES ( '$path', '$name', '0', '$date')"; 
						$ok = $database->query($stm);
						if(!$ok) die(implode(", ", $database->errorInfo()));
					}elseif(is_file($path))
					{
						$extension = end(explode('.',end($subdirectories)));
						if($filter === FALSE || $filter == $extension)
						{
							$filemtime = filemtime($path);
							if ($filemtime > time()) $date = 0; // Some images have absurd dates :\
							else $date = date("Y-m-d H:i:s", filemtime($path));
							$name = sqlite_escape_string(end($subdirectories));
							$size = filesize($path);
							$extension = sqlite_escape_string(strtolower($extension));
							$ext = array("jpg", "png", "jpeg", "gif");
							$isPicture = in_array($extension, $ext) ? 1 : 0 ;
							$comment = NULL;
							$gps_lat  = 'NULL';
							$gps_lon  = 'NULL';
							if($scanExif && strtolower($extension) == 'jpg') {
								$exif = exif_read_data($path, 0, true);
								$comment = isset($exif['COMMENT']) ? sqlite_escape_string(utf8_encode(implode(', ',$exif['COMMENT']))) : NULL;
								if(isset($exif['GPS']['GPSLatitude'])) {
								    //$lat_ref = $exif['GPS']['GPSLatitudeRef'];
									$lat = $exif['GPS']['GPSLatitude'];
									list($num, $dec) = explode('/', $lat[0]);
									$lat_s = $num / $dec;
									list($num, $dec) = explode('/', $lat[1]);
									$lat_m = $num / $dec;
									list($num, $dec) = explode('/', $lat[2]);
									$lat_v = $num / $dec;

									//$lon_ref = $exif['GPS']['GPSLongitudeRef'];
									$lon = $exif['GPS']['GPSLongitude'];
									list($num, $dec) = explode('/', $lon[0]);
									$lon_s = $num / $dec;
									list($num, $dec) = explode('/', $lon[1]);
									$lon_m = $num / $dec;
									list($num, $dec) = explode('/', $lon[2]);
									$lon_v = $num / $dec;
									$gps_lat = "'".($lat_s + $lat_m / 60.0 + $lat_v / 3600.0)."'";
									$gps_lon = "'".($lon_s + $lon_m / 60.0 + $lon_v / 3600.0)."'";
								}
							}
							$path = sqlite_escape_string(str_replace('photos/', '',$path));
							$stm2 = "INSERT INTO pictures (path, name, isfile, isPicture, extension, date, size, gps_lat, gps_lon) VALUES ( '$path', '$name', '1', '$isPicture', '$extension', '$date', '$size', $gps_lat, $gps_lon)"; 
							if(!$database->query($stm2)) echo "<br>ERROR: \"$path\": ".implode(", ", $database->errorInfo());
							if($comment) {
								$stm3 = "INSERT INTO exif (id, words) VALUES (last_insert_rowid(), '$comment')"; 
								if(!$database->query($stm3)) echo "<br>Exif-ERROR: \"$path\": ".implode(", ", $database->errorInfo());
							}
						}
					}
				}
			}
		}
		closedir($directory_list); 
		return TRUE;
	}else{
		return FALSE;	
	}
}
// ------------------------------------------------------------
?>
