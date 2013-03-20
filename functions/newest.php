<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// Browse Newest Files
//-----------------------

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$dropmenu .= '<div class="sort"><h2>Thumbnails:</h2><ul><li><a href="?q=n&page='.$page.'&t=30">30</a></li><li><a href="?q=n&page='.$page.'&t=60">60</a></li><li><a href="?q=n&page='.$page.'&t=90">90</a></li></ul></div>';
$menu_right .= '<a id="sort_slide" href="#">'.$label_sort.'</a> - '; // show sort menu

if(isset($_GET['t'])) {
	setcookie('thumbs_pr_page',intval($_GET['t']) ,2145938400);
	$thumbs_pr_page = intval($_GET['t']);
}
elseif(isset($_COOKIE['thumbs_pr_page']))
	$thumbs_pr_page = $_COOKIE['thumbs_pr_page'];

$from = sqlite_escape_string(($page-1) * $thumbs_pr_page);
$to =  ($thumbs_pr_page*$pages_per_req)+$from;
$result = $db->query("SELECT * FROM pictures WHERE isPicture='1' ORDER BY date DESC LIMIT $from, $to");
$i = 0;
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
	if($i++ < $thumbs_pr_page)
		$output .= "<li><a href='$ppath' rel='lightbox[billeder]' title='<a href=\"?dir=".urlencode($dirnm)."\">$basenm</a> - $img_captions - <a href=\"?q=i&p=$path\">Info</a> - <a href=\"$ppath\">Fullscreen</a>'><img src='$ppath2' title='$basenm / $file' alt='$label_loading' /></a></li>";
	else
		$page_navigation .= "<a href='$ppath' rel='lightbox[billeder]' class='hidden' title='<a href=\"?dir=".urlencode($dirnm)."\">$basenm</a> - $img_captions - <a href=\"?q=i&p=$path\">Info</a> - <a href=\"$ppath\">Fullscreen</a>'></a>";
}
if($i > $thumbs_pr_page) {
	//$page_navigation .= $label_page;
	if($page > 1) $page_navigation .= '<a href="?q=n&page='.($page-1).'"><-----</a>';
	for($k=1; $k <= ceil($i / $thumbs_pr_page); $k++) {
		if ($page == $k)
			$page_navigation .= " | $k";
		else
			$page_navigation .= " | <a href='?q=n&page=$k'>$k</a>";
	}
	if ($page <= ceil($i / $thumbs_pr_page))
		$page_navigation .= ' | <a href="?q=n&page='.($page+1).'">-----></a>';
}
?>