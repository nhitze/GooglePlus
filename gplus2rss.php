<?php
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
    $content = file_get_contents($url.$key);
    if($content) file_put_contents($cacheFile,$content);
}
if(!$content) die('Failed to load G+ data');

$gplus = json_decode($content);
if(!$gplus) die('Failed to decode G+ data');


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
    <title><?php echo htmlspecialchars($item->title); ?></title>
    <link><?php echo $item->url; ?></link>
    <description>
        <?php echo htmlspecialchars($item->object->content); ?>
    </description>
    </item>
<?php } ?>

</channel>
</rss>
