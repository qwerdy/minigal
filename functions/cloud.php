<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}
//-----------------------
// Exif Cloud
//-----------------------
$result = $db->query("SELECT count(*) FROM exif");
if($result->fetchColumn() > 0) {
	$result = $db->query("SELECT words FROM exif");
	while($row = $result->fetch(SQLITE_ASSOC)) {
		$exif = $row['words'];
		$exif = explode(' ', $exif);
		foreach($exif as $key) {
			if($key != "")
			@$tags[$key] = $tags[$key]+1;
		}
	}		
	uksort($tags, function() { return rand() > rand(); }); //randomize
	$min_size = 10;
	$max_size = 70;
	$minimum_count = min(array_values($tags));
	$maximum_count = max(array_values($tags));
	$spread = $maximum_count - $minimum_count;
	if($spread == 0) {
		$spread = 1;
	}
	
	$cloud_html = '';
	$cloud_tags = array();
	foreach ($tags as $tag => $count) {
		if($count >= $exif_number) {
			$size = $min_size + ($count - $minimum_count) * ($max_size - $min_size) / $spread;
			$cloud_tags[] = '<a style="font-size: '. floor($size) . 'px'
			. '" class="tag_cloud" href="?q=s&search='.$tag
			.'&exifsearch=true" title="\''.$tag.'\' returned a count of '.$count.'">'
			.$tag.'</a>';
		}
	}
	$cloud_html = join("\n", $cloud_tags) . "\n";
	$output .= '<div id=tag_cloud>'.$cloud_html.'</div>';
}
?>