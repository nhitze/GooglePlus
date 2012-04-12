<?php
// detect fopen and else use curl
function fetchAPI($url) {
    $content = '';
	if(ini_get('allow_url_fopen')==true) {
		$content = file_get_contents($url);
	} else {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url.$key);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		curl_close($ch);
	}

    return $content;
}

// your cache file, make sure it's writable
$cacheFile = '/tmp/gplus-cache';

// 30 minutes Cache Time
$cacheTime = 1800;

// a (in this case my) Google Plus ID
$googlePlusID = '113799277735885972934';

// your Google+ API key, obtain your's here https://code.google.com/apis/console/b/0/
$key = 'XXX';

// your Google+ Activity Stream, get more info: https://developers.google.com/+/api/latest/activities
$url = 'https://www.googleapis.com/plus/v1/people/' . $googlePlusID . '/activities/public?alt=json&pp=1&key=';

// got cachefile?
$lmod = @filemtime($cacheFile);
if( !isset($_GET['purgeCache']) &&
    $lmod &&
    filesize($cacheFile) &&
    (time() - $lmod < $cacheTime)){
    $content = file_get_contents($cacheFile);
}else{
    $content = fetchAPI($url.$key);
    if($content) file_put_contents($cacheFile,$content);
}
if(!$content) die('Failed to load G+ data');

$gplus = json_decode($content);
if(!$gplus) die('Failed to decode G+ data');

// for debugging:
#print_r($gplus); exit;

// Output RSS feed
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<rss version=\"2.00\" xmlns:geo=\"http://www.w3.org/2003/01/geo/wgs84_pos#\">\n";
echo "<channel>\n";
echo "  <title>".htmlspecialchars($gplus->title)."</title>\n";
echo "  <link>http://plus.google.com/$googlePlusID</link>\n";
echo "  <description></description>\n";
echo "  <pubDate>".$gplus->updated."</pubDate>\n";
echo "  <lastBuildDate>".$gplus->updated."</lastBuildDate>\n";
foreach($gplus->items as $item) {
    $object = $item->object;
    echo "  <item>\n";
    echo "      <title>".htmlspecialchars($item->title)."</title>\n";
    echo "      <link>".htmlspecialchars($item->url)."</link>\n";
    echo "      <guid>".htmlspecialchars($item->id)."</guid>\n";
    echo "      <comments>".htmlspecialchars($object->replies->selfLink)."</comments>\n";
    echo "      <pubDate>".$item->updated."</pubDate>\n";

    if ($item->verb == 'share') {
	$source = "<a href={$object->actor->url}><img src={$object->actor->image->url} />{$object->actor->displayName}</a>";
	$desc = "{$item->annotation}<p>&nbsp;<br/>";
	$desc .= "<em>$source:</em></p><blockquote>{$object->content}</blockquote>";
    } else {
	$desc = $item->object->content;
    }

    if(isset($item->object->attachments)) foreach($item->object->attachments as $attach){
        if($attach->objectType == 'article'){
            $desc .= '<blockquote>';
            $desc .= '<b><a href="'.$attach->url.'">'.$attach->displayName.'</a></b><br /><br />';
            $desc .= $attach->content;
            $desc .= '</blockquote>';
        }elseif($attach->objectType == 'photo'){
            echo "        <enclosure url=\"".htmlspecialchars($attach->image->url)."\" type=\"".$attach->image->type."\" />\n";
        }// FIXME what other attachement type need to be supported?
    }

    echo "      <description>".htmlspecialchars($desc)."</description>\n";

    if($item->geocode){
        list($lat,$lon) = explode(" ",$item->geocode);
        echo "    <geo:lat>$lat</geo:lat>\n";
        echo "    <geo:long>$lon</geo:long>\n";
    }

    echo "  </item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
