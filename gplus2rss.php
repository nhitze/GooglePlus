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

// for debugging:
#print_r($gplus); exit;

// Output RSS feed
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<rss version=\"2.00\">\n";
echo "<channel>\n";
echo "  <title>".htmlspecialchars($gplus->title)."</title>\n";
echo "  <link>http://plus.google.com/$googlePlusID</link>\n";
echo "  <description></description>\n";
echo "  <pubDate>".$gplus->updated."</pubDate>\n";
echo "  <lastBuildDate>".$gplus->updated."</lastBuildDate>\n";
foreach($gplus->items as $item) {
    echo "  <item>\n";
    echo "      <title>".htmlspecialchars($item->title)."</title>\n";
    echo "      <link>".htmlspecialchars($item->url)."</link>\n";
    echo "      <guid>".htmlspecialchars($item->id)."</guid>\n";
    echo "      <pubDate>".$item->updated."</pubDate>\n";

    // we might add a source quote from attachments
    $desc = $item->object->content;

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

    echo "  </item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
