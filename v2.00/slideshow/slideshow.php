<?php
// Main Slide Show Page
// New logic for 'vendor' and SITELOADNAME

// Because 1and1 does not allow SetEnv in the .htaccess we must use this workaround
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
define(DOC_ROOT, $_site->path);
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

$scriptfile = 'js/slideshow.js'; // relative from this file.

// Verify siteCode entered. First we ask for the siteCode now we verify it.

if($_POST['page'] == "verify") {
  // set cookie after verifying code.

  $h->title = "$S->self";
  $h->banner = "<h1>Login Verification</h1>";

  list($top, $footer) = $S->getPageTopBottom($h);

  $siteCode = $S->escape($_POST['siteCode']); // From the form.

  $sql = "select siteId from sites where siteCode='$siteCode'";

  $n = $S->query($sql);

  if(!$n) {
    // NOT FOUND
    $h->banner = "<h1>Login Failed</h1>";
    list($top, $footer) = $S->getPageTopBottom($h);
    echo <<<EOF
$top
<p>Note, the site code is case sensitive. <a href="$S->self?login=yes">Try Again</a></p>
$footer
EOF;
    exit();
  }

  // Get the siteId

  list($siteId) = $S->fetchrow('num');

  $expire = time() + 31536000;  // one year from now

  $siteId = $S->escape($siteId);

  if(!setcookie("SiteId", "$siteId", $expire)) { //, $siteinfo['subDomain'], $_SERVER['HTTP_HOST'])) {
    throw(new Exception("Can't set SiteId cookie"));
  }

  if(!setcookie("SiteCode", "$siteCode", $expire)) { //, $siteinfo['subDomain'], $_SERVER['HTTP_HOST'])) {
    throw(new Exception("Can't set SiteCode cookie"));
  }

  if(isset($_POST['debug'])) {
    $debug = "?debug={$_POST['debug']}";
  }
  if(isset($_POST['unit'])) {
    $unit = $_POST['unit'];
    $unitstr = $debug ? "&unit=$unit" : "?unit=$unit";
  }
  header("location: $S->self{$debug}{$unitstr}");
  exit();
}

// The slideshow can be started as 'slideshow.php?debug=nnnn' where 'nnnn' can be 1) empty, 2)
// 'true' or 3) a super user code.

// If 'debug' equaled a super user id we come  here to 'start'

if($_POST['page'] == 'start') {
  // First debug then user selected a site. The siteId and siteCode are passed in 'site' as
  // "$siteId:$siteCode"

  $_GET['unit'] = $_GET['debug']; // set unit before we unset debug
  unset($_GET['debug']);

  if(!preg_match("/(.*?):(.*)/", $_POST['site'], $m)) {
    echo "preg_match error: 'site': {$_POST['site']}";
    exit();
  }
  // Set the siteId and siteCode and then start the slideshow
  // Escape these for mysqsl use.
  
  $siteId = $S->escape($m[1]);
  $siteCode = $S->escape($m[2]);
  // Fall through to the slideshow.
} else {
  // Get the code and Id from cookies
  
  $siteCode = $_COOKIE['SiteCode'];
  $siteId = $_COOKIE['SiteId'];
}

// 'start' unset debug and set unit. 
// Is 'debug' set in the search query?
  
if($_GET['debug']) {
  // Debug mode.
  // Show all of the sites and let user select one
  // debug=nnn can be debug=true or debug=code. The code is in the superuser table.
  // If code then superuser gets to select the site.
  // Else must use the site in the siteCode cookie

  if($_GET['debug'] != 'true') {
    // maybe superuser

    $sql = "select concat(fname, ' ', lname) from superuser where password='{$_GET['debug']}'";

    if($S->query($sql)) {
      $unit = $_GET['debug']; // set unit to the super user number.

      list($name) = $S->fetchrow('num');

      // BLP 2014-11-01 -- 
      $sql = "select siteId, siteCode from sites where status='active'";
      $S->query($sql);

      $opt = '';

      while(list($siteId, $siteCode) = $S->fetchrow('num')) {
        $opt .= "<option>$siteId:$siteCode</option>\n";
      }

      $h->title = "Slide Show";
      $h->banner = "<h1>Select Site</h1>";
      list($top, $footer) = $S->getPageTopBottom($h);
     
      echo <<<EOF
$top
<p>Hello 'super user' $name</p>
<p>Select the site you want to use:</p>
<form method="post">
<select name="site">
$opt
</select>
<input type="hidden" name="page" value="start">
<input type="submit">
</form>
$footer
EOF;
      exit();
    }
    // If here then 'debug=code' but code not found in 'superuser' table so treat this just as
    // debug=true
  }
  // debug but not super user
}

// Is there a search element 'siteCode' on the command line

if($_GET['siteCode']) {
  $siteCode = $S->escape($_GET['siteCode']);

  // Is code valid?
  $sql = "select siteId from sites where siteCode='$siteCode'";
  if($S->query($sql)) {
    // valid so set siteId
    list($siteId) = $S->fetchrow('num');
  }
}

// This is for the games. There could be more than one instance of the slideshow running
// at the bar (or by me in debug mode). So the fiveandten table has both the siteCode and unit.
// when debug=xxx then unit=xxx also.

$unit = $_GET['unit'];

// If we are here then 1) no 'debug' or 2) debug but not a super user.
// Get the siteCode and siteId from the cookies
  
// They must both be set if not do LOGIN

if(!$siteCode || !$siteId) {
  // do login logic and save cookies
  $h->title = "$S->self";
  $h->banner = "<h1>Login</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);

  if(isset($_GET['debug'])) {
    $debug = "<input type='hidden' name='debug' value='{$_GET['debug']}'>";
  }

  echo <<<EOF
$top
<form action="$S->self" method="post">
Enter Site Code: <input type="text" name="siteCode"><br>
<input type="submit">
<input type="hidden" name="page" value="verify">
<input type="hidden" name="unit" value="$unit">
$debug
</form>
$footer
EOF;
  exit();
}

//************************
// Main Page
//************************

// remove any old bingo games from 'bingogames' using the siteId and the unit.
// This gets done in 'getBingo' in slideshow.ajax.php also for each new game.

$S->query("update bingogames set gameover='yes' where siteId='$siteId' and unit='$unit'");

$h->title = "$S->self";

$cwd = realpath(DOC_ROOT . $S->self); //getcwd();
$Version = preg_replace("~^.*?/(v\d+\.\d+)/.*$~", "$1", $cwd);
$slideshowAr = array("slideshow.php", "slideshow.ajax.php", "js/slideshow.js");
$lastMod = lastMod($slideshowAr, "currentVersion/slideshow") .", ".$Version; 

$h->extra =<<<EOF
  <!-- include CSS -->
  <link rel="stylesheet" href="css/slideshow.css">
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

$top = $S->getPageHead($h);

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
  date_default_timezone_set('US/Los_Angeles');
  return date("M j, H:i:s", max($times));
}
