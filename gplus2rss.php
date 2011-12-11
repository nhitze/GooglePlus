<?php
// your cache file, make sure it's writable
$cacheFile = './cache/gplus-cache';
// initialise cached flag
$cacheLoaded = false;
// 30 minutes Cache Time
$cacheTime = 1800;
// a (in this case my) Google Plus ID
$googlePlusID = '113799277735885972934';

// no cachefile? generate it
if(!file_exists($cacheFile)) {
	$handle = fopen($cacheFile, 'a');
	fclose($handle);
	if(!$handle) {
		die('cache creation failed');
	}
// cache file? use it	
} else {
	// cache validation
	if((time() - filemtime($cacheFile) < $cacheTime)
	&& filesize($cacheFile)>0) {
		$content = file_get_contents($cacheFile);
		$cacheLoaded = true;
	}
}

// no cache, reload
if($cacheLoaded == false) {

	$key = 'YOUR-API-KEY';
	$url = 'https://www.googleapis.com/plus/v1/people/' . $googlePlusID . '/activities/public?alt=json&pp=1&key=';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url.$key);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$content = curl_exec($ch);
	curl_close($ch);
	file_put_contents($cacheFile, $content);
}
$gplus = json_decode($content);
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.00">
<channel>
<title>Google+ Feed:</title>
<link>http://plus.google.com/<?php echo $googlePlusID ?></link>
<description></description>
<language>de-de</language>
<?php
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