<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$contentprefix = SITE_ROOT . "/";
$site_domain = SITE_DOMAIN . "/";

$S = new Tom;

// Turn cashing on or off via cpanel.php?cache=true
// This lets me turn cache on for debugging and still have it off by default. See cpanel.php

if(!session_id()) session_start();
$nocache = '';
if(!$_SESSION['cache']) {
  $nocache = "?nocache=".time();
}

switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
  case "POST":
    switch(strtolower($_POST['page'])) {
      case 'verify':
        verify($S);
        break;
      case 'start':
        start($S);
        break;
      default:
        break;
    }
    break;
  case "GET":
    if($_GET['debug']) {
      debug($S, $_GET['debug'], $noselect);
      normal($S);
    } else { 
      normal($S);
    }
    break;
  case "HEAD":
  case "PUT":
    throw(new Exception("HEAD or PUT not allowed"));
        
  default:
    throw(new Exception("Nothing"));
}

$cpanelVersion = basename(getcwd());

$cwd = getcwd();
$cpanelAr = array("cpanel.*php", "cpanel.ajax.php", "!js/cpanel.admin.js", "js/cpanel.*.js");
$lastMod = lastMod($cpanelAr, $cwd) . ", " . preg_replace("/cpanel-/", "", $cpanelVersion);

$h->title = "PC myphotochannel Control Panel";
$h->extra =<<<EOF
<!-- The 'base' must change when the cpanel-vn.nn changes!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! -->
  <base href="http://go.myphotochannel.com/$cpanelVersion/cpanel.php?siteId=$S->siteId">
<!-- ********************************************************************************** -->

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
  <link rel="stylesheet" href="css/cpanel.photoadmin.css$nocache">
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>

  <script>
var siteId="$S->siteId";
var userId="$S->userId";
var superuser="$S->superuser";
var CONTENTPREFIX="$contentprefix";
var SITE_DOMAIN="$site_domain";
var lastMod="$lastMod";
  </script>
  <script src="js/cpanel.js$nocache"></script>
  <script src="js/PC-cpanel.js$nocache"></script>
  <script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
EOF;

if(!$nopagetop) {
  $top = $S->getPageTop($h);
  if(!$noecho) echo $top;
}

/*
vardump($S->siteId, "SiteId");
vardump($S->superuser, "superuser");
vardump($S->userId, "userId");
*/

$siteId = $S->siteId;
$userId = $S->userId;
$superuser = $S->superuser;

// ***********************************************
// ***********************************************

function debug($S, $d, $noselect) {
  if($d == 'true') {
    $S->siteId = $_COOKIE['SiteId'];
    $S->userId = $_COOKIE['userId'];
    return;
  }
    
  // maybe superuser
  
  $sql = "select concat(fname, ' ', lname) from superuser where password='$d'";

  if($S->query($sql)) {
    $expire = time() + 31536000;  // one year from now

    if(!setcookie("superuser", $d, $expire, '', $_SERVER['HTTP_HOST'])) {
      throw(new Exception("Can't set superuser cookie"));
    }

    $S->userId = $_COOKIE['userId'];

    if(!$noselect) {
      // $noselect was not set by the file that included this file.
      // We do not select a site.

      list($name) = $S->fetchrow('num');
        
      $sql = "select siteId from sites";
      $S->query($sql);
      $opt = '';

      while(list($siteId) = $S->fetchrow('num')) {
        $opt .= "<option>$siteId</option>\n";
      }

      $h->title = "Slide Show Control Panel";
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
<input type="hidden" name="superuser" value="$d">
<input type="submit">
</form>
$footer
EOF;
      exit();
    } else {
      $S->superuser = $d;
      return start($S);
    }
  }
  echo "<h1>NOT SUPER USER</h1>";
}

// ***********************************************
// ***********************************************

function start($S) {
  if($S->superuser)
    return;
  
  // First debug now user selected a site
  $S->siteId = $S->escape($_POST['site']); 
  $S->superuser = $_POST['superuser'];
  normal($S);
}

// ***********************************************
// ***********************************************

function verify($S) {
  // set cookie after verifying code.
  
  $h->title = "$S->self";
  $h->banner = "<h1>Login Verification</h1>";

  list($top, $footer) = $S->getPageTopBottom($h);

  $siteId = $S->escape($_POST['site']);

  $sql = "select siteId from sites where siteId='$siteId'";
  
  $n = $S->query($sql);

  if(!$n) {
    // NOT FOUND
    $h->banner = "<h1>Login Failed</h1>";
    list($top, $footer) = $S->getPageTopBottom($h);
    echo <<<EOF
$top
<p>Note, the site ID is case sensitive. <a href="$S->self?siteId=$siteId">Try Again</a></p>
$footer
EOF;
    exit();
  }

  $expire = time() + 31536000;  // one year from now

  if(!setcookie("SiteId", "$siteId", $expire, '', $_SERVER['HTTP_HOST'])) {
    throw(new Exception("Can't set SiteId cookie"));
  }

  header("location: $S->self?siteId=$siteId");
  exit();
}

// ***********************************************
// ***********************************************

function normal($S) {
/*
  if(!$S->superuser && $_GET['debug'] != "true") {
    $S->superuser = $_COOKIE['superuser'];
  }
*/
  if(!$S->userId) {
    $S->userId = $S->escape($_GET['userId']);
  }

  if(!$S->userId) {
    $S->userId = $_COOKIE['userId'];
  }

  if($S->superuser) {
    return;
  }
  
  // Is siteId set in the query?

  if($S->siteId || $_GET['siteId']) {
    
    if(!$S->siteId) {
      $S->siteId = $S->escape($_GET['siteId']);
    }
    
    $sql = "select siteId from sites where siteId='$S->siteId'";

    if(!$S->query($sql)) {
      $sql = "select siteId from sites";
      $S->query($sql);
      $opt = '';

      while(list($siteId) = $S->fetchrow('num')) {
        $opt .= "<option>$siteId</option>\n";
      }

      $h->title = "Bad Query String Passed for siteId: {$_GET['siteId']}";
      $h->banner = "<h1>Bad Query String Passed for siteId: {$_GET['siteId']}</h1>";
      list($top, $footer) = $S->getPageTopBottom($h);

      echo <<<EOF
$top
<p>Select the site you want to use:</p>
<form method="post">
<select name="site">
$opt
</select>
<input type="hidden" name="page" value="verify">
<input type="submit">
</form>
$footer
EOF;
      exit();
    }
  } else {
    $h->title = "$S->self";
    $h->banner = "<h1>Login</h1>";
    list($top, $footer) = $S->getPageTopBottom($h);

    echo <<<EOF
$top
<form action="$S->self" method="post">
Enter Site ID: <input type="text" name="site"><br>
<input type="submit">
<input type="hidden" name="page" value="verify">
</form>
$footer
EOF;
    exit();
  }
}

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

  //$x = "x: " .print_r($ar, true) . "\n". print_r($path, true) . "\n";
  //file_put_contents("/tmp/debug.txt", $x);
  
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