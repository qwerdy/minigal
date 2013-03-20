<?php
/*
MINIGAL NANO
- A PHP/HTML/CSS based image gallery script

This script and included files are subject to licensing from Creative Commons (http://creativecommons.org/licenses/by-sa/2.5/)
You may use, edit and redistribute this script, as long as you pay tribute to the original author by NOT removing the linkback to www.minigal.dk ("Powered by MiniGal Nano x.x.x")

MiniGal Nano is created by Thomas Rybak

Copyright 2010 by Thomas Rybak
Support: www.minigal.dk
Community: www.minigal.dk/forum

Please enjoy this free script!
*/

// Do not edit below this section unless you know what you are doing!


//-----------------------
// Debug stuff
//-----------------------
	error_reporting(E_ALL);
	//ini_set("display_errors", 1);


// $start_time = microtime(true);

//-----------------------
// Initiate stuff
//-----------------------
define('IncludeOnly', TRUE);
require_once("config_default.php");
require_once("config.php");

if(!empty($cookie_domain))
	session_set_cookie_params(2592000, '/', $cookie_domain); //30 days
session_start();

$version = "0.3.5";
ini_set("memory_limit","256M");


//-----------------------
// DATABASE SETUP
//-----------------------
if(!file_exists("minigal.sqlite"))
	die ("<html>Database not found! <a href='system_check.php'>Run system_check!</a></html>");
$db = null;
try {
	$db = new PDO('sqlite:minigal.sqlite', 'charset=UTF-8');
	} 
catch (PDOException $e) { die($e); }



//-----------------------
// DEFINE VARIABLES
//-----------------------
$page_navigation = "";
$breadcrumb_navigation = "";
$output = "";
$images = "";
$messages = "";
$menu_right = "";
$dropmenu = "";
if (!defined("GALLERY_ROOT")) define("GALLERY_ROOT", "");

//-----------------------
// PHP ENVIRONMENT CHECK
//-----------------------
if (!function_exists('exif_read_data') && $display_exif == 1) {
	$display_exif = 0;
    $messages = "Error: PHP EXIF is not available. Set &#36;display_exif = 0; in config.php to remove this message";
}

//-----------------------
// FUNCTIONS
//-----------------------
include_once('functions/functions.php');

//-----------------------
// Main functions
//-----------------------
$query = isset($_GET['q']) ? $_GET['q'] : 'default';
switch($query) {
			case 'b':
							include_once('functions/browse.php');
							break;
			case 'n':
							include_once('functions/newest.php');
							break;
			case 'r':
							include_once('functions/random.php');
							break;
			case 'c':
							include_once('functions/cloud.php');
							break;			
			case 's':
							include_once('functions/search.php');
							break;
			case 'a':
							include_once('functions/admin.php');
							break;
			case 'i':
							include_once('functions/info.php');
							break;
			case 'g':
							include_once('functions/gps.php');
							break;
			default:
							include_once('functions/browse.php');
}

//-----------------------
// OUTPUT MESSAGES
//-----------------------
if ($messages != "") {
$messages = "<div id=\"topbar\">" . $messages . " <a href=\"#\" onclick=\"document.getElementById('topbar').style.display = 'none';\";><img src=\"images/close.png\" /></a></div>";
}

//PROCESS TEMPLATE FILE
	if(GALLERY_ROOT != "") $templatefile = GALLERY_ROOT . "templates/integrate.html";
	else $templatefile = "templates/" . $templatefile . ".html";
	if(!$fd = fopen($templatefile, "r"))
	{
		echo "Template $templatefile not found!";
		exit();
	}
	else
	{
		$template = fread ($fd, filesize ($templatefile));
		fclose ($fd);
		$template = stripslashes($template);
		$template = preg_replace("/<% title %>/", $title, $template);
		$template = preg_replace("/<% messages %>/", $messages, $template);
		$template = preg_replace("/<% author %>/", $author, $template);
		$template = preg_replace("/<% dropmenu %>/", $dropmenu, $template);
		$template = preg_replace("/<% navigation %>/", "<a href='?q=b'>$label_browse</a> - <a href='?q=n'>$label_newest</a> - <a href='?q=r'>$label_random</a> - <a href='?q=c'>$label_cloud</a> - <a href='?q=g'>$label_gps</a> - <a href='?q=s'>$label_search</a>", $template);
		$template = preg_replace("/<% admin %>/", $menu_right.'<a href="?q=a">Admin</a>', $template);
		$template = preg_replace("/<% gallery_root %>/", GALLERY_ROOT, $template);
		$template = preg_replace("/<% images %>/", "$images", $template);
		$template = preg_replace("/<% thumbnails %>/", "$output", $template);
		$template = preg_replace("/<% breadcrumb_navigation %>/", "$breadcrumb_navigation", $template);
		$template = preg_replace("/<% page_navigation %>/", "$page_navigation", $template);
		$template = preg_replace("/<% bgcolor %>/", "$backgroundcolor", $template);
		$template = preg_replace("/<% gallery_width %>/", "$gallery_width", $template);
		$template = preg_replace("/<% version %>/", "$version", $template);
		echo "$template";
	}

//-----------------------
//Debug stuff
//-----------------------
//$end_time = microtime(true); echo "<p id='generated'>Page generated in ".round(($end_time - $start_time),5)." seconds</p>";
?>
