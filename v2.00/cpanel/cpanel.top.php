<?php
// BLP 2014-11-01 -- select only active sites
// BLP 2014-07-29 -- add $_SESSION['superuser'] see below.
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
define(TOP, $_site->path);
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$contentprefix = TOP . "/";
$site_domain = "http://". $_site->siteDomain . "/";

$S = new $_site->className($_site);

// Is there a query userId= ?
// If so force the $this->id to become the userId rather than using the value of the cookie.

if($_GET['unit']) {
  $S->userId = $_GET['unit'];
}

// Turn cashing on or off via cpanel.php?cache=true
// This lets me turn cache on for debugging and still have it off by default. See cpanel.php

if(!session_id()) session_start();

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

// By the time we get here the required variables like $S->siteId, $S->userId, etc. should be set by
// one of the functions in the above switch.

// BLP 2014-07-29 -- 
// We will use the session to keep track of superuser for files like cpanel.account.php/js that use
// superuser to verify access. This is only a problem when the user does a ctrl-R to refresh the
// page (which should not happen often but I did it and got an error).

if(!$_SESSION['superuser']) {
  $_SESSION['superuser'] = $S->superuser;
}

$siteId = $S->siteId; // All of the cpanel.xxx.php files use $siteId appended to return names.

$cwd = realpath(TOP . $S->self);
$cpanelVersion = preg_replace("~^.*?/(v\d+\.\d+)/.*$~", "$1", $cwd);

$cpanelAr = array("cpanel.*php", "cpanel.ajax.php", "!js/cpanel.admin.js", "js/cpanel.*.js");
$lastMod = $cpanelVersion; //lastMod(array("cpanel.*php", "cpanel.ajax.php", "!js/cpanel.admin.js", "js/cpanel.*.js"),
                   //$cwd) . ", $cpanelVersion";

$h->title = "myphotochannel Control Panel";

$h->extra =<<<EOF
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
  <link rel="stylesheet" href="css/cpanel.photoadmin.css">
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>

  <script>
var siteId="$S->siteId";
var userId="$S->userId";
var superuser="$S->superuser";
var CONTENTPREFIX="$contentprefix";
var SITE_DOMAIN="$site_domain";
var lastMod="$lastMod";
  </script>
  <script src="js/cpanel.js"></script>
  <script src="js/cpanel.tv.js"></script>
  <script src="js/cpanel.account.js"></script>
  <script src="js/cpanel.approve.js"></script>
  <script src="js/cpanel.commercial.js"></script>
  <script src="js/cpanel.category.js"></script>
  <script src="js/cpanel.expunge.js"></script>
  <script src="js/cpanel.newuser.js"></script>
  <script src="js/cpanel.showsettings.js"></script>
  <script src="js/cpanel.managecontent.js"></script>
  <script src="js/cpanel.games.js"></script>
  <script src="js/cpanel.lotto.js"></script>
EOF;

if(!$nopagetop) {
  $top = $S->getPageTop($h);
  if(!$noecho) echo $top;
}

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

      // BLP 2014-11-01 -- 
      $sql = "select siteId from sites where status='active'";
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
      return normal($S);
    }
  }
  echo "<h1>NOT SUPER USER</h1>";
}

// ***********************************************
// ***********************************************

function start($S) {
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
    
    $sql = "select siteId from sites where siteId='$S->siteId' and status='active'";

    if(!$S->query($sql)) {
      $sql = "select siteId from sites where status='active'";
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
