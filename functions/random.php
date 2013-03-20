<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// Browse Random Files
//-----------------------
$result = $db->query("SELECT * FROM pictures WHERE isPicture='1' ORDER BY RANDOM() LIMIT $thumbs_pr_page");
while($row = $result->fetch(SQLITE_ASSOC)) {
	$extension = strtoupper($row['extension']);
	$file = $row['name'];
	$path = $row['path'];
	$ppath = 'photos/'.$path;
	$img_captions = $file.' '; // Filename
	if ($display_exif == 1) $img_captions .= readEXIF($ppath);
	$dirnm = dirname($path);
	$basenm = basename($dirnm);
	$ppath_converted = "cache/".preg_replace('/[^.[:alnum:]_-]/','_',trim($ppath));
	$ppath2 = (file_exists($ppath_converted)) ? $ppath_converted : GALLERY_ROOT."functions/createthumb.php?filename=".urlencode($ppath)."&amp;size=$thumb_size";
	
	$output .= "<li><a href='$ppath' rel='lightbox[billeder]' title='<a href=\"?dir=".urlencode($dirnm)."\">$basenm</a> - $img_captions - <a href=\"$ppath\">Fullscreen</a>'><span></span><img src='$ppath2' title='$basenm / $file' alt='$label_loading' /></a></li>";
}
?>