<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not premitted');}
/**
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

// EDIT SETTINGS BELOW TO CUSTOMIZE YOUR GALLERY
$thumbs_pr_page 		= "30"; 	// Number of output on a single page
$gallery_width 			= "900px"; 	// Gallery width. Eg: "500px" or "70%"
$backgroundcolor 		= "white"; 	// This provides a quick way to change your gallerys background to suit your website. Use either main colors like "black", "white", "yellow" etc. Or HEX colors, eg. "#AAAAAA"
$templatefile 			= "mano"; 	// Template filename (must be placed in 'templates' folder)
$title 					= "MiniGal Nano Testsite"; // Text to be displayed in browser titlebar
$author 				= "Rybber";
$folder_color 			= "black"; 	// Color of folder icons: blue / black / vista / purple / green / grey
$sorting_folders		= "name"; 	// Sort folders by: [name][date]
$sorting_files			= "name"; 	// Sort files by: [name][date][size]
$sortdir_folders		= "ASC"; 	// Sort direction of folders: [ASC][DESC]
$sortdir_files			= "ASC"; 	// Sort direction of files: [ASC][DESC]


//LANGUAGE STRINGS
$label_home 			= "Home"; 		// Name of home link in breadcrumb navigation
$label_new 				= "New"; 		// Text to display for new images. Use with $display_new variable
$label_page 			= "Page"; 		// Text used for page navigation
$label_all 				= "All"; 		// Text used for link to display all images in one page
$label_noimages 		= "No images"; 	// Empty folder text
$label_loading 			= "Loading..."; // Thumbnail loading text
$label_newest			= "Newest"; 	// Text used for navigation
$label_random			= "Random"; 	// Text used for navigation
$label_search			= "Search"; 	// Text used for navigation
$label_cloud			= "Exif Cloud"; // Text used for navigation
$label_gps		 		= "GPS";  		// Text used for navigation
$label_browse 	 		= "Browse";  	// Text used for navigation
$label_sort		 		= "Sort"; 		// Text used for navigation
$label_username 		= "Username"; 	// Placeholder username field
$label_password 		= "Password"; 	// Placeholder password field

//ADVANCED SETTINGS
$cookie_domain			= ''; // Domain name for site, used for cookies. (eg: 'photos.example.com')
$thumb_size 			= 120;// Thumbnail height/width (square thumbs). Changing this will most likely require manual altering of the template file to make it look properly! 
$label_max_length 		= 30; // Maximum chars of a folder name that will be displayed on the folder thumbnail  
$display_exif			= 1;  // Display exif information, slows down pageload a bit
$pages_per_req			= 3;  // Pages to query on every "Newest" page
$exif_number			= 2;  // The minimum number of a exif(jpg comment) word needed to show in cloud.
?>