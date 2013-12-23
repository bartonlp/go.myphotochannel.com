<?php
// Special Demo Slideshow that uses different tables and content directory etc.

// Main Slide Show Page

// For debugging app slideshow.php?cache=true to allow caching. The default adds the time stamp to
// prevent caching

if($_GET['cache'] == 'true') {
  $scriptfile = "js/demo-slideshow.js";
} else {
  $nocache = "?nocache=".time();
  $scriptfile = "js/demo-slideshow.js$nocache";
}

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Tom;

$siteCode = "Demo";
$siteId = "Demo";
$unit = $_GET['unit'];

//************************
// Main Page
//************************

// remove any old bingo games from 'bingogames' using the siteId and the unit.
// This gets done in 'getBingo' in slideshow.ajax.php also for each new game.

$S->query("update demo-bingogames set gameover='yes' where siteId='$siteId' and unit='$unit'");

$h->title = "$S->self";

$Version = preg_replace("/slideshow-/", "", basename(getcwd()));
$cwd = getcwd();
$slideshowAr = array("demo-slideshow.php", "demo-slideshow.ajax.php", "js/demo-slideshow.js");
$lastMod = lastMod($slideshowAr, $cwd) .", ".$Version; 

$h->extra =<<<EOF
<!-- include CSS -->
<link rel="stylesheet" href="css/slideshow.css$nocache">
<!-- include jQuery library -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<!-- YouTube Iframe API -->
<script src="https://www.youtube.com/iframe_api"></script>
<script>
var siteCode = "$siteCode";
var siteId = "$siteId";
var unit = "$unit";
var lastMod = "$lastMod";
</script>
<script src="$scriptfile"></script>
  
EOF;

$top = $S->getPageTop($h);

// Main Page
echo <<<EOF
$top
<div id='show'>
</div>
<div id='photoemailaddress'></div>
</body>
</html>
EOF;


// lastMod
// @param $ar string|array if any of the elements have a wild card (*) glob expand it
// @param $path string|null
// @return string "Last Modified ...."

function lastMod($ar, $path=null) {
  if(is_string($ar)) {
    // if $ar is a string make it an array with one element.
    $ar = array($ar);
  }
  
  $times = array();
  $notfile = '';
  
  // If $path is not empty or null append a '/'
  
  if($path) $path .= '/';

  $x = "x: " .print_r($ar, true) . "\n". print_r($path, true) . "\n";
  file_put_contents("/tmp/debug.txt", $x);
  
  foreach($ar as $file) {
    if(strpos($file, '!') === 0) {
      // don't test flag
      $notfile = ltrim($file, '!');
      continue;
    }
    // If the filename has an '*' in it then do a glob and do all of the files.
    if(strpos($file, '*') !== false) {
      $files = glob("$path{$file}");
      foreach($files as $file) {
        // does the notfile match the file? If so then skip that file
        if($notfile && strpos($file, $notfile) !== false) {
          continue;
        }
        $mtime = filemtime($file);
        $times[] = $mtime;
      }
      continue;
    }
    // No glob
    
    $filename = "$path{$file}";
    $mtime = filemtime($filename);
    $times[] = $mtime;
  }
  // From the $times array of filemtimes return the max value
  date_default_timezone_set('US/Central');
  return date("M j, H:i:s", max($times));
}
  
?>
