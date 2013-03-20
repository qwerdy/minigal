<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// Search Files
//-----------------------
if(isset($_REQUEST['search'])) {
	$i = 0;
	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$from = ($page == 'all') ? 0 : sqlite_escape_string(($page-1) * $thumbs_pr_page);
	
	$searchfor = sqlite_escape_string($_REQUEST['search']);
	if(isset($_REQUEST['location'])) {
		$distance = isset($_REQUEST['distance']) ? floatval($_REQUEST['distance']/1000) : 1;
		$gps = explode(',', $searchfor);
		$gps_lat = isset($gps[0]) ? $gps[0] : 0;
		$gps_lon = isset($gps[1]) ? $gps[1] : 0;
		//finding close pictures using haversine
		$db->sqliteCreateFunction('ACOS', 'acos', 1);
		$db->sqliteCreateFunction('COS', 'cos', 1);
		$db->sqliteCreateFunction('RADIANS', 'deg2rad', 1);
		$db->sqliteCreateFunction('SIN', 'sin', 1);
		$query = "SELECT name, path, ".
				 "(6371 * ACOS(COS(RADIANS($gps_lat)) * COS(RADIANS(gps_lat)) * COS(RADIANS(gps_lon) - RADIANS($gps_lon)) + SIN(RADIANS($gps_lat)) * SIN(RADIANS(gps_lat)))) AS distance ".
				 "FROM pictures ".
				 "WHERE gps_lat IS NOT NULL ".
				 "AND distance < $distance ".
				 "ORDER BY distance ASC";
	} else {
		$query = "SELECT exif.words, pictures.name, pictures.extension, pictures.path FROM exif ".
				 "INNER JOIN pictures ON pictures.id = exif.id ".
				 "WHERE exif.words MATCH '$searchfor' AND pictures.isPicture = 1";
		if(isset($_REQUEST['jpgonly'])) $query .= " AND pictures.extension LIKE 'jp%'";
	}
	$query .= " LIMIT $from, -1";
	$result = $db->query($query);
	while($row = $result->fetch(SQLITE_ASSOC)){
		$file = $row['name'];
		$path = $row['path'];
		$ppath = 'photos/'.$path;
		$img_captions = $file.' '; // Filename
		if ($display_exif == 1) $img_captions .= readEXIF($ppath);
		$dirnm = dirname($path);
		$basenm = basename($dirnm);
		$ppath_converted = "cache/".preg_replace('/[^.[:alnum:]_-]/','_',trim($ppath));
		$ppath2 = (file_exists($ppath_converted)) ? $ppath_converted : GALLERY_ROOT."functions/createthumb.php?filename=".urlencode($ppath)."&amp;size=$thumb_size";
		
		if($i++ < $thumbs_pr_page || $page == 'all')
			$output .= "<li><a href='".$ppath."' rel='lightbox[billeder]' title='<a href=\"?dir=$dirnm\">$basenm</a> - $img_captions - <a href=\"$ppath\">Fullscreen</a>'><img src='$ppath2' title='$basenm  / $file' alt='$label_loading' /></a></li>";
		else //Create links for all results, so lightbox can browse them.
			$page_navigation .= "<a href='".$ppath."' rel='lightbox[billeder]' title='<a href=\"?dir=$dirnm\">$basenm</a> - $img_captions - <a href=\"$ppath\">Fullscreen</a>'></a>";

	}
	$i += $from;
	if($i > $thumbs_pr_page) {
		$page_navigation .= $label_page;
		for($k=1; $k <= ceil($i / $thumbs_pr_page); $k++) {
			if ($page == $k)
				$page_navigation .= " | $k";
			else
				$page_navigation .=' | <a href="?q=s'.((isset($_REQUEST['jpgonly'])) ? '&jpgonly' : '').((isset($_REQUEST['location'])) ? '&location' : '').'&search='.$_REQUEST['search']."&page=$k\">$k</a>";
		}
		//Insert link to view all images
		if ($page == 'all') $page_navigation .= " | $label_all";
		else $page_navigation .= ' | <a href="?q=s'.((isset($_REQUEST['jpgonly'])) ? '&jpgonly' : '').((isset($_REQUEST['location'])) ? '&location' : '').'&search='.$_REQUEST['search']."&page=all\">$label_all</a>";
	}
}
else {
	$output .= "<form class='form' action='?q=s' method='post'>"
				."<input type='text' name='search' placeholder='$label_search'><br />"
				."<input type='checkbox' name='jpgonly' value='true' checked/> jpeg only<br />"
				."<input type='checkbox' name='location' /> Lat,long<br />"
				."<p><input type='submit' name='submit' value='$label_search'></p></form>";
}
?>