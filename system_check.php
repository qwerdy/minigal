<?php
ini_set("memory_limit","256M");

$exif = "No";
$gd = "No";
$update = "No";
$sqlite = "No";
$database = "No";
$cache_dir = "No";
$db_table_result = "successfully created the database and tables!";
if (function_exists('exif_read_data')) $exif = "Yes";
if (extension_loaded('sqlite')) $sqlite = "Yes";
if (extension_loaded('gd') && function_exists('gd_info')) $gd = "Yes";
if (ini_get("allow_url_fopen") == 1) $update = "Yes";

if (!is_dir('cache') && is_writable(".")) {
	mkdir('cache',0700);
	$cache_dir = "Yes";
} elseif (is_writable("cache")){
	$cache_dir = "Yes";
}
if($sqlite == "Yes") {
	if(file_exists("minigal.sqlite")) {
		if(is_writable("minigal.sqlite")) {
			$db_table_result = "Database already set up!";
			$database = "Yes";
		} else 
			$db_table_result = "Database is not writeable!";
	} else {
		$db = NULL;
		try {
			$db = new PDO('sqlite:minigal.sqlite');
		} catch (PDOException $e) {}
		
		if (!$db) {
			$create_error = TRUE;
		} else {
			$database = "Yes";
			$query = $db->query("CREATE TABLE pictures(id INTEGER PRIMARY KEY, path LONGTEXT, name CHAR(256), isfile BOOLEAN, isPicture BOOLEAN, extension VARCHAR(5), size MEDIUMINT(9), date DATETIME, gps_lat NUMERIC, gps_lon NUMERIC)");
			$query2 = $db->query("CREATE VIRTUAL TABLE exif USING FTS3(id INTEGER, words MEDIUMTEXT, FOREIGN KEY(id) REFERENCES pictures(id))");
			$query3 = $db->query("CREATE TABLE users(id INTEGER PRIMARY KEY, username CHAR(256), password VARCHAR(40))");
			$sql = <<<EOT
CREATE TRIGGER rec_delete
BEFORE DELETE ON pictures
FOR EACH ROW BEGIN
	delete from exif where id = OLD.id;
END;
EOT;
			
			$query5 = $db->query($sql);
			$username = "admin";
			$password = sha1("admin");
			$query4 = $db->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");

			
			if (!$query || !$query2 || !$query4 || !$query3 || !$query5) {
				$db_table_result = "ERROR: Failed during database query! (syntax error?). ".implode(", ", $db->errorInfo());
				$database = "No";
				unlink("minigal.sqlite");
			}
		}
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow">
<title>MiniGal Nano system check</title>
<style type="text/css">
body {
	background-color: #daddd8;
	font: 12px Arial, Tahoma, "Times New Roman", serif;
}
h1 {
	font-size: 30px;
	margin: 20px 0 5px 0;
	letter-spacing: -2px;
}
div {
	line-height: 20px;
}
.left {
	width: 300px;
	display: inline-table;
	background-color: #fdffbe;
	padding: 2px;
}
.middle-neutral {
	font-weight: bold;
	text-align: center;
	width: 100px;
	display: inline-table;
	background-color: #fdffbe;
	padding: 2px;
}
.middle-no {
	font-weight: bold;
	text-align: center;
	width: 100px;
	display: inline-table;
	background-color: #ff8181;
	padding: 2px;
}
.middle-yes {
	font-weight: bold;
	text-align: center;
	width: 100px;
	display: inline-table;
	background-color: #98ffad;
	padding: 2px;
}
.right {
	width: 600px;
	display: inline-table;
	background-color: #eaf1ea;
	padding: 2px;
}
</style>
<body>
<h1>MiniGal Nano system check</h1>
<div class="left">
	PHP Version
</div>
<div class="<?php if(version_compare(phpversion(), "4.0", '>')) echo 'middle-yes'; else echo 'middle-no' ?>">
	<?php echo phpversion(); ?>
</div>
<div class="right">
	<a href="http://www.php.net/" target="_blank">PHP</a> scripting language version 4.0 or greater is needed
</div>
<br />

<div class="left">
	GD library support
</div>
<div class="<?php if($gd == "Yes") echo 'middle-yes'; else echo 'middle-no' ?>">
	<?php echo $gd; ?>
</div>
<div class="right">
	<a href="http://www.boutell.com/gd/" target="_blank">GD image manipulation</a> library is used to create output. Bundled since PHP 4.3
</div>
<br />

<div class="left">
	EXIF support
</div>
<div  class="<?php if($exif == "Yes") echo 'middle-yes'; else echo 'middle-neutral' ?>">
	<?php echo $exif; ?>
</div>
<div class="right">
	Ability to extract and display <a href="http://en.wikipedia.org/wiki/Exif" target="_blank">EXIF information</a>. The script will work without it, but not display image information
</div>
<br />

<div class="left">
	SQLite support
</div>
<div  class="<?php if($sqlite == "Yes") echo 'middle-yes'; else echo 'middle-no' ?>">
	<?php echo $sqlite; ?>
</div>
<div class="right">
	SQLite database support
</div>
<br />

<div class="left">
	SQLite table created
</div>
<div  class="<?php if($database == "Yes") echo 'middle-yes'; else echo 'middle-no' ?>">
	<?php echo $database; ?>
</div>
<div class="right">
	<?php 
		if(isset($create_error))
			echo (file_exists("minigal.sqlite")) ? "Impossible to open, check permissions" : "Impossible to create, check permissions";
		else
			echo $db_table_result; 
	?>
</div>
<br />

<div class="left">
	Thumbnail Cache directory
</div>
<div  class="<?php if($cache_dir == "Yes") echo 'middle-yes'; else echo 'middle-no' ?>">
	<?php echo $cache_dir; ?>
</div>
<div class="right">
	Cache directory for storing output. Must be writeable! ( ./cache )
</div>
<br />

<div class="left">
	PHP memory limit
</div>
<div class="middle-neutral">
	<?php echo ini_get("memory_limit"); ?>
</div>
<div class="right">
	Memory is needed to create output. Bigger images uses more memory	
</div>
<br />

<div class="left">
	New version check
</div>
<div class="middle-neutral">
	<?php echo $update ?>
</div>
<div class="right">
	The ability to check for new version and display this automatically. The script will work without it	
</div>
<br /><br />
<a href="http://www.minigal.dk/minigal-nano.html" target="_blank">Support website</a>
| <a href="http://www.minigal.dk/forum" target="_blank">Support forum</a>
</body>
</html>
