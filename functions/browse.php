<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// Setting Variables
//-----------------------
$dir = isset($_GET["dir"]) ? $_GET["dir"] : '';
$thumbdir = rtrim('photos' . "/" .$dir,"/");
$thumbdir = str_replace("/..", "", $thumbdir); // Prevent looking at any up-level folders
$currentdir = GALLERY_ROOT . $thumbdir;
$dropmenu .= '<div class="sort"><h2>Folders:</h2><ul><li><a href="?sort=folder:name:asc">Name ASC</a></li><li><a href="?sort=folder:name:desc">Name DESC</a></li><li><a href="?sort=folder:date:asc">Date ASC</a></li><li><a href="?sort=folder:date:desc">Date DESC</a></li></ul></div><div class="sort"><h2>Files:</h2><ul><li><a href="?sort=file:name:asc">Name ASC</a></li><li><a href="?sort=file:name:desc">Name DESC</a></li><li><a href="?sort=file:date:asc">Date ASC</a></li><li><a href="?sort=file:date:desc">Date DESC</a></li><li><a href="?sort=file:size:asc">Size ASC</a></li><li><a href="?sort=file:size:desc">Size DESC</a></li></ul></div>';
//-----------------------
// READ FILES AND FOLDERS
//-----------------------
$files = array();
$dirs = array();
if (is_dir($currentdir) && $handle = opendir($currentdir))
{
	$menu_right .= '<a id="sort_slide" href="#">'.$label_sort.'</a> - '; // show sort menu
	while (false !== ($file = readdir($handle)))
    {			
// 1. LOAD FOLDERS
		if (is_directory($currentdir . "/" . $file))
			{ 
				if ($file != "." && $file != ".." )
				{
					$url_name = urlencode($file);
					$url_dir  = urlencode($dir);
					checkpermissions($currentdir . "/" . $file); // Check for correct file permission
					// Set thumbnail to folder.jpg if found:
					if (file_exists("$currentdir/" . $file . "/folder.jpg"))
					{
						$dirs[] = array(
							"name" => $file,
							"date" => filemtime($currentdir . "/" . $file . "/folder.jpg"),
							"html" => "<li><a href='?dir=" .ltrim($url_dir . "/" . $url_name, "/") . "'><em>" . padstring($file, $label_max_length) . "</em><img src='" . GALLERY_ROOT . "functions/createthumb.php?filename=$currentdir/" . $url_name . "/folder.jpg&amp;size=$thumb_size'  alt='$label_loading' /></a></li>");
					}  else
					{
					// Set thumbnail to a random image (if any):
						unset ($firstimage);
						$firstimage = getrandomImage("$currentdir/" . $file);
						$path = $currentdir."/".$file."/".$firstimage;
						$path2 = (file_exists("cache/".str_replace("/",".",$path))) ? "/cache/".str_replace("/",".",$path) : GALLERY_ROOT."functions/createthumb.php?filename=".urlencode($path)."&amp;size=$thumb_size";

						if ($firstimage != "") {
						$dirs[] = array(
							"name" => $file,
							"date" => filemtime($currentdir . "/" . $file),
							"html" => "<li><a href='?dir=" . ltrim($url_dir . "/" . $url_name, "/") . "'><em>" . padstring($file, $label_max_length) . "</em><img src='$path2'  alt='$label_loading' /></a></li>");
						} else {
						// If no folder.jpg nor image is found, then display default icon:
							$dirs[] = array(
								"name" => $file,
								"date" => filemtime($currentdir . "/" . $file),
								"html" => "<li><a href='?dir=" . ltrim($url_dir . "/" . $url_name, "/") . "'><em>" . padstring($file, $label_max_length) . "</em><img src='" . GALLERY_ROOT . "images/folder_" . strtolower($folder_color) . ".png' width='$thumb_size' height='$thumb_size' alt='$label_loading' /></a></li>");
						}
					}
				}
			}	



// 3. LOAD FILES
			elseif ($file != "." && $file != ".." && $file != "folder.jpg")
			{
				// 2. LOAD CAPTIONS
				if (file_exists($currentdir ."/captions.txt"))
				{
					$file_handle = fopen($currentdir ."/captions.txt", "rb");
					while (!feof($file_handle) ) 
					{	
						$line_of_text = fgets($file_handle);
						$parts = explode('/n', $line_of_text);
						foreach($parts as $img_capts)
						{
							list($img_filename, $img_caption) = explode('|', $img_capts);	
							$img_captions[$img_filename] = $img_caption;
						}
					}
					fclose($file_handle);
				}
				else $img_captions[$file] = $file.' '; // Filename
				
				
				$filetolower = strtolower($file);
				$extension = "";
				$path = $currentdir."/".$file;
				// JPG, GIF and PNG
				if (preg_match("/.jpg$|.gif$|.png$/i", $filetolower))
				{
					//Read EXIF
					if ($display_exif == 1) $img_captions[$file] .= readEXIF($path);

					checkpermissions($path);
					$path_converted = "cache/".preg_replace('/[^.[:alnum:]_-]/','_',trim($path));
					$path2 = file_exists($path_converted) ? $path_converted : GALLERY_ROOT."functions/createthumb.php?filename=".urlencode($path)."&amp;size=$thumb_size";
					$files[] = array (
						"name" => $file,
						"date" => filemtime($path),
						"size" => filesize($path),
						"html" => "<li><a href='".$path."' rel='lightbox[billeder]' title='$img_captions[$file] - <a href=\"$path\">Fullscreen</a>'><img src='$path2' title='$file' alt='$label_loading' /></a></li>");
				}
				// Other filetypes
				elseif     (preg_match("/.pdf$/i", $filetolower)) $extension = "PDF"; // PDF
				elseif (preg_match("/.zip$/i", $filetolower)) $extension = "ZIP"; // ZIP archive
				elseif (preg_match("/.rar$|.r[0-9]{2,}/i", $filetolower)) $extension = "RAR"; // RAR Archive
				elseif (preg_match("/.tar$/i", $filetolower)) $extension = "TAR"; // TARball archive
				elseif (preg_match("/.gz$/i", $filetolower)) $extension = "GZ"; // GZip archive
				elseif (preg_match("/.doc$|.docx$/i", $filetolower)) $extension = "DOCX"; // Word
				elseif (preg_match("/.ppt$|.pptx$/i", $filetolower)) $extension = "PPTX"; //Powerpoint
				elseif (preg_match("/.xls$|.xlsx$/i", $filetolower)) $extension = "XLXS"; // Excel
				elseif (preg_match("/.mp4$/i", $filetolower)) $extension = "MEDIA"; // MEDIA
				//elseif (preg_match("/.avi$|.mov$|.mpeg$|.mpg$|.mp4$|.mp3$|.3gp$/i", $filetolower)) $extension = "MEDIA"; // MEDIA
				//else    $extension = "UNK"; // Unknown
	
				if ($extension != "")
				{
					$files[] = array (
						"name" => $file,
						"date" => filemtime($path),
						"size" => filesize($path),
						"html" => "<li><a href='" . $path . "' rel='lightbox[billeder]' title='$file'><em-pdf>" . padstring($file, 20) . "</em-pdf><img src='" . GALLERY_ROOT . "images/filetype_" . $extension . ".png' width='$thumb_size' height='$thumb_size' alt='$file' /></a></li>");
				}
			}   		
	}
  closedir($handle);
	//-----------------------
	// SORT FILES AND FOLDERS
	//-----------------------
	changesort();
	if(isset($_COOKIE['sort:folder'])) {
		switch($_COOKIE['sort:folder']) {
					case 'nasc':
									$sortdir_folders = "ASC";
									$sorting_folders = "name";
									break;
					case 'ndesc':
									$sortdir_folders = "DESC";
									$sorting_folders = "name";
									break;
					case 'dasc':
									$sortdir_folders = "ASC";
									$sorting_folders = "date";
									break;
					case 'ddesc':
									$sortdir_folders = "DESC";
									$sorting_folders = "date";
									break;
				}
	}
	if(isset($_COOKIE['sort:file'])) {
		switch($_COOKIE['sort:file']) {
					case 'nasc':
									$sortdir_files = "ASC";
									$sorting_files = "name";
									break;
					case 'ndesc':
									$sortdir_files = "DESC";
									$sorting_files = "name";
									break;
					case 'dasc':
									$sortdir_files = "ASC";
									$sorting_files = "date";
									break;
					case 'ddesc':
									$sortdir_files = "DESC";
									$sorting_files = "date";
									break;
					case 'sasc':
									$sortdir_files = "ASC";
									$sorting_files = "size";
									break;
					case 'sdesc':
									$sortdir_files = "DESC";
									$sorting_files = "size";
									break;				
				}
	}
	if (sizeof($dirs) > 0) 
	{
		foreach ($dirs as $key => $row)
		{
			if($row["name"] == '') unset($dirs[$key]); //Delete empty array entries
			$name[$key] = strtolower($row['name']);
			$date[$key] = strtolower($row['date']);
		}	
		if (strtoupper($sortdir_folders) == "DESC") array_multisort($$sorting_folders, SORT_DESC, $name, SORT_DESC, $dirs);
		else array_multisort($$sorting_folders, SORT_ASC, $name, SORT_ASC, $dirs);
	}
	if (sizeof($files) > 0)
	{
		foreach ($files as $key => $row)
		{
			if($row["name"] == "") unset($files[$key]); //Delete empty array entries
			$name[$key] = strtolower($row['name']);
			$date[$key] = strtolower($row['date']);
			$size[$key] = strtolower($row['size']);
		}
		if (strtoupper($sortdir_files) == "DESC") array_multisort($$sorting_files, SORT_DESC, $name, SORT_ASC, $files);
		else array_multisort($$sorting_files, SORT_ASC, $name, SORT_ASC, $files);
	}
} else die("ERROR: Could not open $currentdir for reading!");

//-----------------------
// OFFSET DETERMINATION
//-----------------------
	$offset_start =(isset($_GET["page"])) ? ($_GET["page"] * $thumbs_pr_page) - $thumbs_pr_page : 0;
	$offset_end = $offset_start + $thumbs_pr_page;
	if ($offset_end > sizeof($dirs) + sizeof($files)) $offset_end = sizeof($dirs) + sizeof($files);

	if (isset($_GET["page"]) && $_GET["page"] == "all")
	{
		$offset_start = 0;
		$offset_end = sizeof($dirs) + sizeof($files);
	}
//-----------------------
// PAGE NAVIGATION
//-----------------------
if (!isset($_GET["page"])) $_GET["page"] = 1;
if (sizeof($dirs) + sizeof($files) > $thumbs_pr_page)
{
	$page_navigation .= "$label_page ";
	for ($i=1; $i <= ceil((sizeof($files) + sizeof($dirs)) / $thumbs_pr_page); $i++)
	{
		if ($_GET["page"] == $i)
			$page_navigation .= "$i";
			else
				$page_navigation .= "<a href='?dir=" . $_GET["dir"] . "&amp;page=" . ($i) . "'>" . $i . "</a>";
		if ($i != ceil((sizeof($files) + sizeof($dirs)) / $thumbs_pr_page)) $page_navigation .= " | ";
	}
	//Insert link to view all images
	if ($_GET["page"] == "all") $page_navigation .= " | $label_all";
	else $page_navigation .= " | <a href='?dir=" . $_GET["dir"] . "&amp;page=all'>$label_all</a>";
}

//-----------------------
// BREADCRUMB NAVIGATION
//-----------------------
if (isset($_GET['dir']))
{
	$breadcrumb_navigation .= "<a href='?dir='>" . $label_home . "</a> > ";
	$navitems = explode("/", $_REQUEST['dir']);
	for($i = 0; $i < sizeof($navitems); $i++)
	{
		if ($i == sizeof($navitems)-1) $breadcrumb_navigation .= $navitems[$i];
		else
		{
			$breadcrumb_navigation .= "<a href='?dir=";
			for ($x = 0; $x <= $i; $x++)
			{
				$breadcrumb_navigation .= urlencode($navitems[$x]);
				if ($x < $i) $breadcrumb_navigation .= "/";
			}
			$breadcrumb_navigation .= "'>" . $navitems[$i] . "</a> > ";
		}
	}
} else $breadcrumb_navigation .= $label_home;

//Include hidden links for all images BEFORE current page so lightbox is able to browse images on different pages
for ($y = 0; $y < $offset_start - sizeof($dirs); $y++)
{	
	$breadcrumb_navigation .= "<a href='" . $currentdir . "/" . $files[$y]["name"] . "' rel='lightbox[billeder]' class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>";
}

//-----------------------
// DISPLAY FOLDERS
//-----------------------
if (count($dirs) + count($files) == 0) {
	$output .= "<li>$label_noimages</li>"; //Display 'no images' text
}
$offset_current = $offset_start;
for ($x = $offset_start; $x < sizeof($dirs) && $x < $offset_end; $x++)
{
	$offset_current++;
	$output .= $dirs[$x]["html"];
}

//-----------------------
// DISPLAY FILES
//-----------------------
for ($i = $offset_start - sizeof($dirs); $i < $offset_end && $offset_current < $offset_end; $i++)
{
	if ($i >= 0)
	{
		$offset_current++;
		$output .= $files[$i]["html"];
	}
}

//Include hidden links for all images AFTER current page so lightbox is able to browse images on different pages
if($files)
for ($y = $i; $y < sizeof($files); $y++)
{	
	$page_navigation .= "<a href='" . $currentdir . "/" . @$files[$y]["name"] . "' rel='lightbox[billeder]'  class='hidden' title='" . @$img_captions[$files[$y]["name"]] . "'></a>";
}
?>