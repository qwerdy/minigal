<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// Admin Page
//-----------------------
$signedin =  isset($_SESSION['signedin']) ? $_SESSION['signedin'] : 'false';
$username =  isset($_POST['username'])    ? $_POST['username']    : '';
$password =  isset($_POST['password'])    ? $_POST['password']    : '';

if($signedin == 'true' || sqlite_login($db, $username, $password))
{
	$_SESSION['signedin'] = 'true';
	include_once("functions/sdrec.php");
	$output .= "<div id='adminpage'>";
	if(isset($_GET['job']))
		switch($_GET['job']) {
			case 'clearthumb':
							clearthumb();
							$messages .= 'Thumbnail cache cleared! ';
							break;
			case 'update':
							if(!empty($_GET['scan'])) {
								$start_time = microtime(true);
								$path = sqlite_escape_string($_GET['scan']);
								$album = str_replace('photos/', '',$path);
								$query = 'DELETE FROM pictures';
								echo $album;
								$query .= empty($album) ? '' : " WHERE path like'$album%'";
								$db->exec("BEGIN"); // Begin transaction
								$db->exec($query);
								set_time_limit(120);
								if(sdrec($path, $db, isset($_GET['exif']))){
									$end_time = microtime(true);
									$messages .= "Scan completed in ".round(($end_time - $start_time),5)." seconds";
									$db->exec("COMMIT"); // Committ transaction
								} else {
									$db->exec("ROLLBACK"); // Rollback transaction
									$messages .= "Scan FAILED!";
								}
							}
							break;
			case 'changepassword':
							$username = $_SESSION['username'];
							$passwordOLD =  isset($_POST['oldpwd'])    ? sha1($_POST['oldpwd'])    : '';
							$password1   =  isset($_POST['password'])  ? sha1($_POST['password'])  : '';
							$password2   =  isset($_POST['password2']) ? sha1($_POST['password2']) : '';
							
							if ($password1 != $password2){
								$messages .= 'The passwords did not match! ';
								break;
							}
							if($db->query("SELECT id FROM users WHERE username='$username' AND password='$passwordOLD'")) {
								$db->exec("UPDATE users SET password='$password2' WHERE username='$username'");
								$output .=  "<h2>Password changed!</h2>";
								session_regenerate_id();
							} else 
								$messages .= 'The old password was wrong! ';
							break;
			case 'signout':
							session_destroy();
							header("Location: ?");
							break;
		}	
	$output .= "<h2>Albums:</h2>";
	$output .= '<div><b class="clickable" onclick="toggleNeighbor(this)">Photos:</b> <a href="?q=a&job=update&scan=photos/">Scan</a> - <a href="?q=a&job=update&exif=&scan=photos/">Exif scan</a></div>';
	recursive_dirlist('photos'); 
	$output .= '<h2>Clear thumbnail cache:</h2><a href="?q=a&job=clearthumb">Clear cache!</a><h2>Change password:</h2><form action="?q=a&job=changepassword" method="post"><input type="password" placeholder="Old password" name="oldpwd" size="15" maxlength="30">'
.'<br /><input type="password" placeholder="New password" NAME="password" size="15" maxlength="30">'
.'<br /><input type="password" placeholder="New password" NAME="password2" size="15" maxlength="30">'
.'<br /><input type="submit" name="change" value="Change"></form>';	
	$output .= "<br /><br /><p><a href='?q=a&job=signout'>Sign out</a><p>";
	$output .= "</div>";
} 
else {
$output .= "<form class='form' action='?q=a' method='post'><input type='text' name='username' placeholder='$label_username'><br />"
			."<input type='password' name='password' placeholder='$label_password'><br />"
			."<input type='submit' name='login' value='Login'></form>";
}
?>