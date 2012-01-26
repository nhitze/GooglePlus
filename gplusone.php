<?php
header('Content-Type: text/html; charset=UTF-8');

// your cache file, make sure it's writable
$cacheFile = 'cache/gplusones-cache';

// 30 minutes Cache Time
$cacheTime = 1800;

// a (in this case my) Google Plus ID
$googlePlusID = 'xxx';

// your Google+ API key, obtain your's here https://code.google.com/apis/console/b/0/
$key = 'xxx';
$url = 'https://plus.google.com/u/0/_/plusone/get?ct=' . $key . '&oid=' . $googlePlusID ;

function fetchAPI($url) {
    $content = '';
    if(ini_get('allow_url_fopen', true)) {
        $content = file_get_contents($url);
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);
    }

    return $content;
}


// got cachefile?
$lmod = @filemtime($cacheFile);
if( !isset($_GET['purgeCache']) &&
$lmod &&
filesize($cacheFile) &&
(time() - $lmod < $cacheTime)) {
    $content = file_get_contents($cacheFile);
}else{
    $content = fetchAPI($url);
    if($content) {
        file_put_contents($cacheFile, $content);
    }
}

if(!$content) die('Failed to load G+ data');

$content = substr($content, 5);
$content = str_replace("\n", "", $content);
$content = str_replace(',,', ',"",', $content);
$content = str_replace(',,', ',"",', $content);

$json = json_decode($content, true);

if(!isset($json[0])
|| !isset($json[0][1])
|| !isset($json[0][1][0])) {
    die("json wasn't decoded");
}

$plusones = $json[0][1][0];

// Output RSS feed
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<rss version=\"2.00\" xmlns:geo=\"http://www.w3.org/2003/01/geo/wgs84_pos#\">\n";
echo "<channel>\n";
echo "  <title>My +1</title>\n";
echo "  <link>http://plus.google.com/$googlePlusID</link>\n";
echo "  <description>All my +1ed Webpages</description>\n";
echo "  <lastBuildDate>".date('D, d M Y h:i:s')."</lastBuildDate>\n";

foreach($plusones as $plusone) {
    echo "  <item>\n";
    echo "      <title>".htmlspecialchars($plusone[7][1])."</title>\n";
    echo "      <link>".htmlspecialchars($plusone[7][0])."</link>\n";
    echo "      <guid>".htmlspecialchars($plusone[6])."</guid>\n";
    echo "      <description>".htmlspecialchars($plusone[4])."</description>\n";
    echo "  </item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
