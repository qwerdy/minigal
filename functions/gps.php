<?php
if(!defined('IncludeOnly')){die('ERROR: Direct access not permitted');}

$output .= <<<END
<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
<script src="../js/markercluster.js" type="text/javascript"></script>
<div id="map"></div>

<script type="text/javascript">
	var myOptions = {
	  maxZoom: 16,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
	var map = new google.maps.Map(document.getElementById("map"), myOptions);
	var lat=[];
	var lon=[];
	var photo=[];
	var width=[];
	var text=[];
	var dirnm=[];
	var markers=[];
	var infowindow = new google.maps.InfoWindow({});
	\n
END;

$count = $db->query("SELECT count(*) FROM pictures WHERE gps_lat IS NOT NULL");
$count = $count->fetchColumn();
$jcode = '';
if($count > 0) {
	$result = $db->query("SELECT gps_lat, gps_lon, path, name FROM pictures WHERE gps_lat IS NOT NULL");
	$i = 0;
	while($row = $result->fetch(SQLITE_ASSOC)) {
		$path = 'photos/'.$row['path'];
		$dirnm = dirname($row['path']);
		$path_converted = "cache/".preg_replace('/[^.[:alnum:]_-]/','_',trim($path));
		$path = (file_exists($path_converted)) ? $path_converted : GALLERY_ROOT."functions/createthumb.php?filename=".$path."&amp;size=$thumb_size";
		$name = $row['name'];
		$gps_lat = $row['gps_lat'];
		$gps_lon = $row['gps_lon'];
		
		$lat[$i]=$gps_lat;
		$jcode .="lat[$i]=$lat[$i];";
		$lon[$i]=$gps_lon;
		$jcode .="lon[$i]=$lon[$i]; ";
		$jcode .="photo[$i]=\"$path\";";
		$jcode .= "width[$i]=\"200\";";
		$jcode .= "text[$i] = \"$name\";";
		$jcode .= "dirnm[$i] = \"$dirnm\";";
		$jcode .= "\n";
		$i++;
	}
} else {
	$output .= '</script><h2>No Pictures with GPS informationa availiable</h2>';
	return;
}

$jcode .= "\nn = $count;";
$output .= $jcode;
$output .= <<<END
function createMarker(point, url, text, w, path, cords)
  {
  var marker = new google.maps.Marker({
    position: point,
    map: map,
    title: "<div id=\"content\"><img style=\"margin-left:10px;\" src=\"" + url + "\" width=\"" + w + "\" align=\"right\"><a href=\"photos/"+path+"/"+text+"\" rel=\"lightbox[billeder]\">Fullscreen</a><br /><br /><a href=\"?q=s&location&search="+cords+"\">Nearby pictures</a><br /><br /><a href=\"?dir=/"+path+"\">Folder</a></div>"
  });
  google.maps.event.addListener(marker, 'click', function() {
    if(infowindow) infowindow.close();
    infowindow.setContent(marker.getTitle())
    infowindow.open(map,marker);
  });
  markers.push(marker);
  }

var bounds = new google.maps.LatLngBounds();
  
for (i=0;i<n;i++)
  {
  var latitude = lat[i];
  var longitude = lon[i];
  var url = photo[i];
  var w = width[i];
  var path = dirnm[i];
  var thistext = text[i];
  var point = new google.maps.LatLng(latitude,longitude);
  var cords = String(latitude+','+longitude);
  bounds.extend(point);
  createMarker(point,url,thistext,w, path, cords);
  }
  map.fitBounds(bounds);
  var mcOptions = {maxZoom: 16};
  var markerCluster = new MarkerClusterer(map, markers, mcOptions);
  markerCluster.onClick = function(thiscluster) { 
	var mcMarkers = thiscluster.cluster_.getMarkers();
	var randm = Math.floor((mcMarkers.length)*Math.random())
	infowindow.setContent(mcMarkers[randm].getTitle())
	infowindow.open(map, mcMarkers[randm]);
  }
</script>
END;
?>