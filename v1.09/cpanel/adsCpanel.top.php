<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$contentprefix = TOP . "/";
$site_domain = SITE_DOMAIN . "/";

// To keep from doing a $top = $S->getPageTop() set $nopagetop = true. Setting $nopagetop will also
// mean $noecho (for obvious reasons).
// To keep top from echoing $top put $noecho = true; before includeing this file.

session_start();

$S = new Tom;

if($_POST['page'] == "verify") {
  // set cookie after verifying code.

  $h->title = "$S->self";
  $h->banner = "<h1>Login Verification</h1>";

  list($top, $footer) = $S->getPageTopBottom($h);

  $siteId = $S->escape($_POST['siteId']);

  $sql = "select siteId from sites where siteId='$siteId'";

  $n = $S->query($sql);

  if(!$n) {
    // NOT FOUND
    $h->banner = "<h1>Login Failed</h1>";
    list($top, $footer) = $S->getPageTopBottom($h);
    echo <<<EOF
$top
<p>Note, the site ID is case sensitive. <a href="$S->self?login=yes">Try Again</a></p>
$footer
EOF;
    exit();
  }

  $expire = time() + 31536000;  // one year from now

  if(!setcookie("SiteId", "$siteId", $expire, $siteinfo['subDomain'], $_SERVER['HTTP_HOST'])) {
    throw(new Exception("Can't set SiteId cookie"));
  }

  header("location: $S->self");
  exit();
}

// NOTE this is used by the cpanel and also in uploadphotos and uploadads
// We do NOT need siteCode here.

if($_POST['page'] == 'start') {
  // First debug now user selected a site
  
  $siteId = $S->escape($_POST['site']); 
  $superuser = $_POST['superuser'];
  $_SESSION['siteId'] = $siteId;
  $_SESSION['superuser'] = $superuser;
} else {
  // Is 'debug' set in the search query?
  
  if($_GET['debug']) {
    // Debug mode.
    // Show all of the sites and let user chose
    // debug=nnn can be debug=true,
    // debug=code. The code is in the superuser table.
    // If code then superuser gets to select the site.
    // Else must use the site in the siteCode/Id

    if($_GET['debug'] != 'true') {
      // maybe superuser
      $sql = "select concat(fname, ' ', lname) from superuser where password='{$_GET['debug']}'";
      if($S->query($sql)) {
        if($noselect) {
          // $noselect was set by the file that included this file.
          // We do not select a site.
          $superuser = $_GET['debug'];
          $_SESSION['siteId'] = $siteId;
          $_SESSION['superuser'] = $superuser;

          //cout("superuser; NO SELECT goto Start");
          goto Start; // I know goto's are poison but what the hell
        }

        list($name) = $S->fetchrow('num');
        
        $sql = "select siteId from sites";
        $S->query($sql);
        $opt = '';

        while(list($siteId) = $S->fetchrow('num')) {
          $opt .= "<option>$siteId</option>\n";
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
<input type="hidden" name="superuser" value="{$_GET['debug']}">
<input type="submit">
</form>
$footer
EOF;
        exit();
      }
    } else {
      // debug=true
      $_SESSION['siteId'] = '';
      
      if($noselect) {
        // $noselect was set by the file that included this file.
        // We do not select a site.
        $siteId = $_COOKIE['SiteId'];
        $_SESSION['siteId'] = $siteId;
      }
    }
  }

  if(!$_SESSION['siteId']) {
    if($_GET['siteId']) {
      $siteId = $S->escape($_GET['siteId']);
    } else {
      // the cookies should already be escaped
  
      $siteId = $_COOKIE['SiteId'];
    }
  
    //file_put_contents("test", "Before siteId check. siteId=$siteId\n",  FILE_APPEND);

    if(!$siteId) {
      // do login logic and save cookies
      $h->title = "$S->self";
      $h->banner = "<h1>Login</h1>";
      list($top, $footer) = $S->getPageTopBottom($h);

      echo <<<EOF
$top
<form action="$S->self" method="post">
Enter Site ID: <input type="text" name="siteId"><br>
<input type="submit">
<input type="hidden" name="page" value="verify">
</form>
$footer
EOF;
      exit();
    }
  } else {
    $siteId = $_SESSION['siteId'];
    $superuser = $_SESSION['superuser'];
  }
}

Start:

$userId = $_COOKIE['userId'];

$h->title = "myphotochannel Control Panel";
$h->extra =<<<EOF
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.css">
  <link rel="stylesheet" href="css/cpanel.photoadmin.css">
  <script src="http://code.jquery.com/jquery.min.js"></script>
  <script>
var siteId="$siteId";
var userId="$userId";
var superuser="$superuser";
var CONTENTPREFIX="$contentprefix";
var SITE_DOMAIN="$site_domain";
  </script>
  <script src="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.js"></script>
EOF;

if(!$nopagetop) {
  $top = $S->getPageTop($h);
  if(!$noecho) echo $top;
}
?>