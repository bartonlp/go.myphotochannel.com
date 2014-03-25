<?php
// Control panel for videos
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

// Uses js/videocontrol.js which does the ajax calls to these Ajax items.

// ************************************************************
// Ajax, doSql
// Do general SQL task
// $_POST['sql'] is a sql statment to execute.
// If the statement is a 'select' we return the result rows.
// If 'insert' or 'update' we return the number of rows affected
// *************************************************************

if($_GET['page'] == 'doSql') {
  // For AJAX all we need is the database class
  
  $S = new Database($dbinfo);
  
  $sql = $_GET['sql'];
  if($sql == '') {
    echo "NO SQL"; exit();
  }

  //file_put_contents("/tmp/debug.txt", print_r($_GET, true) . "\n" . $sql . "\n");

  $n = $S->query($sql);
  if(strpos($sql, 'select') !== false) {
    while($row = $S->fetchrow('assoc')) {
      $rows[] = $row;
    }
    // {num: <number of records>, rows: <each record> }
    // the rows are [ { <field name>: <field value> } ... ] array 0-<number of records -1>
  
    echo json_encode(array('num'=>$n, 'rows'=>$rows));
    exit();
  }

  // If not a select just return the number of rows.
  
  echo json_encode(array('num'=>$n, 'sql'=>$sql)); // return a json object
  exit();
}

// Ajax reload

if($_GET['page'] == 'reload') {
  $S = new Database($dbinfo);

  $which = $_GET['which'];

  switch($which) {
    case 'ads':
      $S->query("select * from ads where type in ('video', 'youtube')");
      $page = makepage($S, 'ads');
      break;
    case 'items':
      $S->query("select * from items where type in ('video', 'youtube')");
      $page = makepage($S, 'items');
      break;
    default:
      $page = "Error";
      break;
  }
  echo $page;
  exit();
}

// If not AJAX we do a full page so instantiate the Tom class

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$S->t = 0;

function notAllowed() {
  echo "<h1>Only 'super users' are allowed. Sorry.</h1>";
  exit();
}

if($superuser = $_GET['debug']) {
  $sql = "select concat(fname, ' ', lname) from superuser where password='$superuser'";
  if(!$S->query($sql)) {
    notAllowed();
    exit();
  }
//  list($name) = $S->fetchrow('num');
//  $superuserName = $name;
} else {
  notAllowed();
  exit();
}

$h->title = "Video Control Panel";
$h->banner = <<<EOF
<h1>Video Control Panel</h1>
EOF;

$h->link =<<<EOF
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
<style>
#posted {
  position: fixed;
  top: 100px;
  left: 200px;
  border: 1px solid black;
  background-color: green;
  color: white;
  padding: 40px;
  z-index: 10;
  border-radius: 15px;
  -webkit-border-radius: 15px;
}
.viddiv {
  border: 1px solid black;
}
.viddiv video {
  margin-left: 20px;
  max-width: 400px;
  max-height: 300px;
}
.viddiv iframe {
  margin-left: 20px;
  max-width: 400px;
  max-height: 300px;
}
.viddiv input {
  border: 0;
}
.durslider, .skipslider {
  width: 80%;
}
/* when we go into full screen the browser automaticaly does:
video:-webkit-full-screen, audio:-webkit-full-screen {
background-color: transparent;
position: static;
margin: 0px;
height: 100%;
width: 100%;
-webkit-flex: 1 1 0px;
display: block;
}

or for iframs:

iframe:-webkit-full-screen {
margin: 0px;
padding: 0px;
border: 0px;
border-image: initial;
position: fixed;
height: 100%;
width: 100%;
left: 0px;
top: 0px;
}

But we have set the max-width and max-height so we have to take care of those ourself!
*/
iframe:-webkit-full-screen, video:-webkit-full-screen {
  max-width: 100%;
  max-height: 100%;
}

@media only screen and (max-width: 500px) {
  .durslider, .skipslider {
    width: 250px;
  }
  .viddiv {
    width: 320px;
  }
  .viddiv video {
    margin-left: 5px;
    width: 300px;
  }
  .viddiv iframe {
    margin-left: 5px;
    width: 300px;
  }
}
@media only screen
and (-webkit-min-device-pixel-ratio: 2)
and (-wibkit-min-device-width: 320px)
and (-wibkit-max-device-width: 480px) {
  .viddiv {
    max-width: 320px;
  }
}
</style>

EOF;

// JavaScript stuff
$h->extra = <<<EOF
<!-- include jQuery library -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<!-- jQuery UI library -->
<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<!-- Our JavaScript stuff -->
<script src="js/videocontrol.js"></script>

EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
Select <select id="selectstatus">
<option>active</option>
<option>inactive</option>
<option>new</option>
<option>delete</option>
</select>

<h1>Videos in 'ads' Table</h1>
<div id='ads'><!-- from makepage(..,'ads') --></div>
<button id="adssubmit">Submit Ads Changes</button>
<hr>
<h1>Videos in 'items' Table</h1>
<div id='items'><!-- from makepage(..,'items') --></div>
<button id="itemssubmit">Submit Items Changes</button>
<hr>
$footer
EOF;

// ********************

function makepage($S, $class) {
  $page = "";

  while($row = $S->fetchrow('assoc')) {
    switch($class) {
      case 'items':
        $items_ads = "<li>siteId: {$row['siteId']}</li>";
        break;
      case 'ads':
        $items_ads = "<li>adId: {$row['adId']}</li>";
        break;
    }
  
    // Two types of videos: 1) youtube 2) HTML5

    $select = <<<EOF
<select data-item="{$row['itemId']}">
<option value="">select</option>
<option>active</option>
<option>inactive</option>
<option>new</option>
<option>delete</option>
</select>
EOF;

    $select = preg_replace("~>{$row['status']}~", " selected='true'>{$row['status']}", $select);
    $statusval = $row['status'];
    
    if($row['type'] == 'youtube') {
      $page .= <<<EOF
<div class="viddiv $class" id="{$row['itemId']}">
<ul>
<li>itemId: {$row['itemId']}</li>
$items_ads
<li class="desc">Desc: <input type="text" value="{$row['description']}"></li>
<li>Type: {$row['type']}</li>
<li class="status" data-item="$statusval">Status: $select</li>
<li class="dur" data-value="{$row['duration']}"/>
<li class="skip" data-value="{$row['skip']}"/>
<li>Loc: {$row['location']}</li>
</ul>
<div>
<iframe id="video-{$row['itemId']}" title="YouTube video player" width="800" height="600"
webkitAllowFullScreen mozallowfullscreen allowFullScreen 
src="http://www.youtube.com/embed/{$row['location']}?controls=1">
</iframe>
</div>
</div>

EOF;
    } else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "http://go.myphotochannel.com/{$row['location']}");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_NOBODY, 1);
      $out = curl_exec($ch);
      curl_close($ch);
      //cout("{$row['location']}:\n$out\n+++++++++++++++++++++++++++++++");
      if(preg_match("/Content-Length:\s+(\d+)\s+.*?Content-Type:\s+(.+)\s|$/i", $out, $m)) {
        $ctype = "Reported Content-Type: $m[2]";
        $clen = "Reported Content-Length: $m[1]";
      } else $clen = $ctype = "Not found";

      if(preg_match("/\.([^.]*?)$/i", $row['location'], $m)) {
        $ext = $m[1];
      } else {
        echo "ERROR: $m[0], $m[1]<br>";
      }

      switch($ext) {
        case 'ogv':
        case 'ogg':
          $ext = "ogg; codecs=theora,vorbis";
          break;
        case 'mp4':
          $ext = "mp4; codecs=avc1.42E01E,mp4a.40.2";
          break;
        case 'webm':
          $ext = "webm; codecs=vp8,vorbis";
          break;
      }

      $nocache = 't=' . date("U") . '-' . $S->t++;
      
      $page .= <<<EOF
<div class="viddiv $class" id="{$row['itemId']}">
<ul>
<li>itemId: {$row['itemId']}</li>
$items_ads
<li class="desc">Desc: <input type="text" value="{$row['description']}"></li>
<li>Type: {$row['type']}</li>
<li class="status" data-item="$statusval">Status: $select
</li>
<li class="reporteddur"></li>
<li class="dur" data-value="{$row['duration']}"/>
<li class="skip" data-value="{$row['skip']}"/>
<li>Loc: {$row['location']}</li>
<li>$ctype</li>
<li>$clen</li>
</ul>
<video id="video-{$row['itemId']}" controls='1' src="/{$row['location']}?$nocache" type="video/$ext">
Your browser does not support HTML5 video.
</video>
</div>

EOF;
    }
  }
  return $page;
}

?>