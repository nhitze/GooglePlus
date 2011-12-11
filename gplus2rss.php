<?php
error_reporting(E_ALL);
$cacheFile = './cache/gplus-cache';
$cacheLoaded = false;
exit;
if(!file_exists($cacheFile)) {
	$handle = fopen($cacheFile, 'a');
	fclose($handle);
	if(!$handle) {
		echo 'cache failed';
	}
} else {
	if((time() - filemtime($cacheFile) < 1800)
	&& filesize($cacheFile)>0) {
		$content = file_get_contents($cacheFile);
		$cacheLoaded = true;
	}
}

if($cacheLoaded == false) {

	$key = 'YOUR-API-KEY';
	$url = 'https://www.googleapis.com/plus/v1/people/113799277735885972934/activities/public?alt=json&pp=1&key=';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url.$key);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$content = curl_exec($ch);
	curl_close($ch);
//	file_put_contents($cacheFile, $content);
}
$gplus = json_decode($content);
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.00">
<channel>
<title>Mein GPlus</title>
<link>http://plus.google.com</link>
<description></description>
<language>de-de</language>
<?php
require_once 'simplepie.inc';

$feed = new SimplePie();
$feed->set_feed_url('http://silberkind.de/gplus.php');
$feed->enable_cache(false);
$feed->init();

foreach($gplus->items as $item) { ?>
	<item>
	<title><?php echo $item->title; ?></title>
	<link><?php echo $item->url; ?></link>
	<description>
		<?php echo $item->object->content ?>
	</description>
	</item>
<?php } ?>

</channel>
</rss>