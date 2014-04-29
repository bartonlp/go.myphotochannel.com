
<?php
// BLP 2014-03-25 -- added last day photos and total photos to table#sitesinfo
// index for tomsproject
// BLP 2014-01-10 -- Add resize.log by the other two logs
// Javascript for this program is at js/index.js
// 310-452-5409 Heidi store.

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

// Here is a little bit of trickery
// The function lastMod() returns the filemtine() for the filename in $x.
// We can embed this into a string as follows: first we make a variable witht the name of the
// function ($lm = 'lastMod';)
// then we use it in the string like this: "this is a string {$lm('filename')}" and it works.

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

  foreach($ar as $file) {
    if(strpos($file, '!') === 0) {
      // don't test flag
      $notfile = ltrim($file, '!');
      continue;
    }
    // If the filename has an '*' in it then do a glob and do all of the files.

    if(strpos($file, '*') !== false) {
      $file = "$path{$file}";
      if(substr($file, 0, 1) == '/') {
        $file = DOC_ROOT . $file;
      }
      
      $files = glob($file);

      foreach($files as $file) {
        // does the notfile match the file? If so then skip that file
        if($notfile && strpos($file, $notfile) !== false) {
          continue;
        }
        $mtime = filemtime($file);
        //cout("file: $file, $mtime");
        $times[] = $mtime;
      }
      continue;
    }
    // No glob
    
    $filename = "$path{$file}";
    if(substr($filename, 0,1) == "/") {
      $filename = DOC_ROOT . "$filename";
    }
    $mtime = filemtime($filename);
    //file_put_contents("debug.txt", "$filename :: ". date("M j, Y H:i", $mtime) . "\n", FILE_APPEND);

    //cout("no glob: $filename, $mtime");
    $times[] = $mtime;
  }
  // From the $times array of filemtimes return the max value

  date_default_timezone_set('US/Central');
  $modtime = max($times);
  define(ONEDAY, 86400);
  //echo "modtime: $modtime, time-3days: " . (time() - (ONEDAY*3)) . "<br>\n";
  
  if($modtime > (time() - (ONEDAY*3))) {
    return "Last Modified: <span style='color: red'>" . date("M j, Y H:i", $modtime) . "</span>";
  } else {
    return "Last Modified: " . date("M j, Y H:i", $modtime);
  }
}

$lm = 'lastMod'; // $lm is the lastMod function

// Get the version given the path

function getversion($path) {
  $name = realpath(DOC_ROOT ."$path");
  $version = preg_replace('/^.*?(v\d+\.\d+).*$/', "$1", $name);
  return $version;
}

// Get the link version information

// List of file for slideshow and cpanel. Used all over.

$slideshowRealName = realpath(DOC_ROOT ."/currentVersion/slideshow");
$cpanelRealName = realpath(DOC_ROOT ."/currentVersion/cpanel");

$slideshowAr = array("slideshow.php", "slideshow.ajax.php", "js/slideshow.js");
$cpanelAr = array("cpanel.*php", "cpanel.ajax.php", "js/cpanel.*.js");

$oldVersion = getversion("/oldVersion");
$currentVersion = getversion("/currentVersion");
$workingVersion = getversion("/workingVersion");

$curSlideshowTimes = "";

foreach($slideshowAr as $file) {
  $curSlideshowTimes .= "<tr><td>/slideshow/$file:</td>".
                        "<td>{$lm($slideshowAr, '/currentVersion/slideshow')}</td></tr>\n";
}

function getlinkversion($type) {
  global $lm, $slideshowAr, $cpanelAr;
  
  switch($type) {
    case "working":
      $name = "working";
      $path = "/workingVersion";
      break;
    case "old":
      $name = "old";
      $path = "/oldVersion";
      if(!file_exists("/oldVersion/index.php")) {
        echo "No oldVersion of files";
        exit();
      }
      break;
    case "current":
    default:
      $name = "current";
      $path = "/currentVersion";
      break;
  }

  $version = getversion($path);

  $link = <<<EOF
<p>Debug links to the $name versions ($version):</p>
<ul>
<li><a target="_blank" href="$path/slideshow/slideshow.php">SlideShow ($version) 
with debug info in upper left</a>
<span class='superextra'>*</span> {$lm($slideshowAr, "$path/slideshow")}</li>
<li><a  target="_blank" href="$path/cpanel/cpanel.php">Control Panel ($version)</a>
<span class='superextra'>*</span> {$lm($cpanelAr, "$path/cpanel")}</li>
<li><a target="_blank" href="$path/cpanel/PC-cpanel.php">PC-cpanel</a>
<span class='superextra'>*</span> {$lm(array("PC-cpanel.php","js/PC-cpanel.js"), "$path/cpanel")}.
This is the full screen PC version not the iPhone version.</li>
<li><a target="_blank" href="$path/uploadphotos.php">Upload Photos From Client</a>
<span class='superextra'>*</span> 
{$lm(array("uploadphotos.php","js/uploadphotos.js"), "/currentVersion")}</li>
<li><a target="_blank" href="$path/itemsInfo.php">Slide Show Info</a>
<span class='superextra'>*</span>  {$lm('itemsInfo.php', "$path")}</li>
<li><a target="_blank" href="$path/siteInfo.php">'sites' Table Info</a>
{$lm('siteInfo.php', "$path")}</li>
<li><a target="_blank" href="$path/userinfo.php">'users' Table Info</a>
{$lm('userinfo.php', "$path")}</li>
<li><a target="_blank" href="$path/appInfo.php">'appinfo' Table Info</a>
{$lm('appInfo.php', "$path")}</li>
<li><a target="_blank" href="$path/webstats.php">Web Stats</a>
<span class='super'>*</span>  {$lm('webstats.php', "$path")}</li>
<li><a target="_blank" href="$path/itemsTableMaint.php">Check Items Table for Integrity</a> 
<span class='super'>*</span>  {$lm('itemsTableMaint.php', "$path")}</li>
<li><a target="_blank" href="$path/uploadsforweek.php">Who Emailed Photos This Week</a>
 {$lm('uploadsforweek.php', "$path")}</li>
<li><a target="_blank" href="$path/whoapproved.php">Who Approved Photos</a>
 {$lm('whoapproved.php', "$path")}</li>
<li><a target="_blank" href="$path/track-startup.php">Track Startups</a>
 {$lm('track-startup.php', "$path")}</li>
<li><a target="_blank" href="$path/pushercheck.php">Pusher Status</a>
 {$lm('pushercheck.php', "$path")}</li>
<li><a target="_blank" href="$path/videocontrol.php">Video Control Panel</a> <span class='super'>*</span>
{$lm(array('videocontrol.php', 'js/videocontrol.js'), "$path")}. Control Panel for vidos in the 'ads' and 'items' tables</li>
<li><a target="_blank" href="$path/createNewSite.php">Create A Site</a>
<span class='super'>*</span> {$lm('createNewSite.php', "$path")}</li>
<li><a target="_blank" href="$path/deleteSite.php">Delete A Site</a> <span class='super'>*</span>
{$lm('deleteSite.php', "$path")}. 
<span style="color: white; background: red; padding: 0 4px"><b>BE VERY CAREFUL!</b></span></li>
<li><i>emailphoto.php</i> {$lm('emailphoto.php', "$path")}. 
A CLI run from CRON. Processes photos emailed by
customers every minute.</li>
<li><i>siteautoload.php</i> {$lm('siteautoload.php', "$path")}. Finds '.sitemap.php' and autoloads classes.</li>
<li><i>.sitemap.php</i> {$lm('.sitemap.php', "/")}. Configuration file for site.</li>

<li><a target="_blank" href="/v1.08/slideshow/photoloto.php">Photo Lotto (photoloto.php)</a>
 {$lm('photoloto.php', "/currentVersion/slideshow")}. CLI or Web program. The CLI is
run from a CRON job.</li>
<li><a target="_blank" href="currentVersion/showlottowinners.php">Show Lotto Winners</a>
 {$lm('showlottowinners.php', "/currentVersion")}.</li>
</ul>

<h3>Ads Programs (not in production use)</h3>
<p>These are pretty stable but only work with <i>slideshow-v1.02</i> or higher.</p>
<ul>
<li><a target="_blank" href="$path/cpanel/adsCpanel.admin.php">Ads CPanel</a>
<span class='super'>*</span>
{$lm(array("adsCpanel.php", "adsCpanel.ajax.php", "js/adsCpanel.js"), "$path/cpanel")}</li>
<li><a target="_blank" href="$path/adsadmin.php">Admin adsInfo</a>
{$lm('adsadmin.php', "$path")} <span class='super'>*</span></li>
<li><a target="_blank" href="$path/adsAccountAdmin.php">Admin the Ads Accounts</a>
{$lm('adsAccountAdmin.php', "$path")}. Add or Edit Account Info
<span class='super'>*</span></li>
<li><a target="_blank" href="$path/uploadads.php">Upload Ads from Client</a>
<span class='super'>*</span> {$lm(array('uploadads.php', 'js/uploadads.js'), "$path")}</li>
</ul>
EOF;

  return $link;
}

// Ajax: Get the link area for the correct version (currentVersion or workingVersin)

if($_GET['page'] == 'getlink') {
  $type = $_GET['linkversion'];
  $link = getlinkversion($type);
  echo $link;
  exit();
}

// Ajax clearlog.

if($_GET['page'] == 'clearlog') {
  $logfile = DOC_ROOT . "{$_GET['logfile']}";
  $old = file_get_contents($logfile);
  file_put_contents("{$logfile}.save", $old);
  file_put_contents($logfile, '');
  //file_put_contents("/tmp/blpdebug.txt", "$logfile\n");
  echo "Cleared $logfile";
  exit();
}

if($_POST['page'] == 'filesize') {
  $file = DOC_ROOT . "{$_POST['file']}";
  $size = filesize($file);
  echo "Size: $size";
  exit();
}

// Ajax gettable

if($_GET['name'] == 'gettable') {
  $S = new Database($dbinfo);

  // Close any site in the 'startup' table that has had no activity for 30 minutes.

  $sql = "update startup set status='closed' where status='open' ".
         "&& (lasttime < (now() - interval 30 minute) || ".
         "lasttime is null && starttime < (now() - interval 30 minute))";

  $S->query($sql);

  // How may closed items do we have today?
  
  $n = $S->query("select id from startup where status='open'");

  $sql = "select id, siteId, unit, version, status,".
         "convert_tz(starttime, '-4:00', '-5:00'), ".
         "convert_tz(lasttime, '-4:00', '-5:00') as last, timediff(lasttime, starttime) from startup ".
         "where status='open' || ".
         "(lasttime > date(now()) ".
         "and id in (select max(id) ".
         "from startup group by siteId, unit, status))".
         "order by last";

  $c = $S->query($sql) - $n;

  while(list($id, $siteId, $unit, $version, $status, $starttime, $lasttime, $run) = $S->fetchrow('num')) {
    $tbl .= "<tr><td>$siteId</td><td>$unit</td><td>$version</td>".
            "<td class='status'>$status</td><td>$starttime</td><td>$lasttime</td><td>$run</td></tr>";
  }

  if(empty($tbl)) {
    $tbl = <<<EOF
<h2>Sites Status OPEN or active today (v1.05 or later)</h2>
<table border='1'>
<tr><th>No Sites Running At This Time.</th></tr></table>
EOF;
  } else {
    $button = '';
    if($c) {
      $openclosed = "Open=$n, Closed=$c: <button></button>";
    }
      
    $tbl = <<<EOF
<h2>Sites Status OPEN or active today (v1.05 or later)
<span style="font-size: 10px">Ordered by 'Last'</span></h2>
$openclosed
<table border='1'>
<thead>
<tr><th>Site</th><th>Unit</th><th>Version</th>
<th>Status</th><th>Start</th><th>Last</th><th>Run Time</th></tr>
</thead>
<tbody>
$tbl
</tbody>
</table>
EOF;
  }
  echo $tbl;
  exit();
}

// End Ajax

// Set bannerFile which will override the value in .sitemap.php

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

// Form calls
  
// Verify PAGE

if($_POST['page'] == "verify") {
  // Get info from users and sites tables.

  $sql = "select id, siteId from users ".
         "where email='{$_POST['email']}' and password='{$_POST['password']}'";

  if(!($n = $S->query($sql))) {
    echo "Sorry Email or Password not valid try again.";
    exit();
  }

  if($n > 1) {
    // user is in multiple sites with the same email and password so ask which site he wants
    $select = "<select name='siteinfo'>\n";
    
    while(list($userId, $siteId) = $S->fetchrow($result, 'num')) {
      // if the id starts with SU- (superuser) id then skip it.
      if(substr($siteId, 0, 3) == "SU-") {
        continue;
      }
      //cout("siteId: $siteId, userId: $userId, siteCode: $S->siteCode, email: $emailUsername");
      $select .= "<option value='$userId,$siteId'>$siteId</option>\n";
    }
    $select .= "</select>\n";

    $h->banner = "<h1>Select Site</h1>";
    list($top, $footer) = $S->getPageTopBottom($h);
    // $select has the <select id="siteinfo" options for the multiple sites the user is in.
    echo <<<EOF
$top
<p>You are associated with more than one site. Please select the site you wish to use.</p>
<form method="post">
$select
<input type="submit" value="submit"/>
<input type="hidden" name="page" value="verify2"/>
</form>
<hr>
$footer
EOF;
    exit();
  }

  // Only one site with this user
/*
  if($userId != $_COOKIE['userId']) {
    // The userId cookie is not set so set it and loop back
    $S->setIdCookie($userId, 'userId');
  }
*/
  goto UserHomePage;
  exit();
}

// Verify2 PAGE
// Second step verify for user on multiple sites

if($_POST['page'] == "verify2") {
  list($userId, $siteId) = explode(",", $_POST['siteinfo']);
/*
  if($userId != $_COOKIE['userId']) {
    // The userId cookie is not set so set it and loop back
    $S->setIdCookie($userId, 'userId');
  }
*/
  goto UserHomePage;
  exit();
}

if($_GET['dousercheck']) {
  goto DoUserCheck;
}

// Superuser COOKIE or query debug= or DoUserCheck.
// If the superuser COOKIE is set check if it is valid.
// else if URL has 'debug=" and the superuser code check if it is valid.
// else do the user check for the reduced index page.

if($superuser = $_GET['debug']) {
  $sql = "select concat(fname, ' ', lname) from superuser where password='$superuser'";
  if(!$S->query($sql)) {
    echo "Super user password not found in database";
    exit();
  }

  $expire = time() + 31536000;  // one year from now

  if(!setcookie("superuser", $superuser, $expire, '', $_SERVER['HTTP_HOST'])) {
    // If we get an error here it is probably because something has done an OUTPUT before we
    // tried to set the COOKIE. There can be NO output not even a blank line or a space!
    throw(new Exception("Can't set SiteId cookie. Check to make sure NO output before."));
    //echo "Can't set SiteId cookie?";
  }

  list($superusername) = $S->fetchrow('num');
  $superWelcome = "<h2>Welcome superuser $superusername</h2>";

  if(!$S->query("select id from users where siteId='SU-$superuser'")) {
    echo "ERROR (1): Superuser (SU-$superuser) not found in users table";
    exit();
  }

  list($userId) = $S->fetchrow('num');

  // Is the cookie set to this userId? If not set it and loop back to the index page
  
  if($userId != $_COOKIE['userId']) {
    $S->setIdCookie($userId, 'userId');
    header("Location: index.php?debug=$superuser");
    exit();
  }
  
  // Fall into rest of Superuser page
} elseif($superuser = $_COOKIE['superuser']) {
  // Even if the superuser COOKIE is set test it against the database
  
  $sql = "select concat(fname, ' ', lname) from superuser where password='$superuser'";
  if(!$S->query($sql)) {
    // Super user cookie not valid
    setcookie("superuser", "", time()-10000, $siteinfo['subDomain'], $_SERVER['HTTP_HOST']);
    echo "Super user cookie not valid. <a href='$S->self'>Try again.</a>";
    exit();
  }

  list($superusername) = $S->fetchrow('num');
  $superWelcome = "<h2>Welcome superuser $superusername</h2>";

  if($S->query("select id from users where siteId='SU-$superuser'") != 1) {
    echo "ERROR (2): Superuser (SU-$superuser) not found in users table";
    exit();
  }

  list($userId) = $S->fetchrow('num');

  // Is the cookie set to this userId? If not set it and loop back to the index page
  
  if($userId != $_COOKIE['userId']) {
    $S->setIdCookie($userId, 'userId');
    header("Location: index.php?debug=$superuser");
    exit();
  }

  // OK, fall into rest of page Superuser page.
} else {
  // Check 'user' email and password for access to user home page

  DoUserCheck:

  $h->banner = "<h1>Restricted Page</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  echo <<<EOF
$top
<p>Myphotochannel users please enter you email address and password:</p>
<form method="post">
<table>
<tr><th>Email Address</th><td><input type="text" name="email"/></td></tr>
<tr><th>Password</th><td><input type="password" name="password"/></td></tr>
</table>
<input type="submit"/>
<input type="hidden" name="page" value="verify"/>
</form>
$footer
EOF;
exit();
}

// ******************************************************************
// Main flow


// Set up title, link and extra for all pages.

$h->title = "MyPhotoChannel";

$h->link =<<<EOF
<link rel="stylesheet" href="css/index.css">
EOF;

$h->banner = <<<EOF
<h1>Development Home Page</h1>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script>
var userId="$userId";
</script>
<script src="js/index.js"></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Create the sites table with all the siteId, sitCode, emailUsername, and #photos today, total
// photos.

$sql = "select siteId, emailUsername, siteCode from sites";
$n = $S->query($sql);
$r = $S->getResult();

$sites = <<<EOF
<p>There are now $n sites:</p>

<style>
#sitesinfo th {
  padding: 3px;
}
</style>

<table id="sitesinfo" border="1">
<thead>
<tr><th>Site Id</th><th>Upload Email Address</th><th>Site Code</th>
<th>Photos<br>Last Day</th><th>Total Photos</th></tr>
</thead>
<tbody>

EOF;

while(list($siteId, $email, $siteCode) = $S->fetchrow($r, 'num')) {
  $sql = "select itemId from items where siteId='$siteId' ".
         "and creationTime > date_sub(now(), interval 1 day) and status='active'";

  $n = number_format($S->query($sql));
  $sql = "select itemId from items where siteId='$siteId' and status='active'";
  $tot = number_format($S->query($sql));
  $sites .= "<tr><td>$siteId</td><td>$email</td><td>$siteCode</td><td>$n</td><td>$tot</td></tr>\n";
}
$sites .= "</tbody>\n</table>\n";

date_default_timezone_set('US/Central');
$tz = date('T (O \G\M\T)');

// Get the linkversion area for the currentVersion

$currlink = getlinkversion("current");
$workingVersion = getversion("/workingVersion");

// Render Page for Superuser

echo <<<EOF
$top
<p>Index Version $S->version</p>
$superWelcome
<div id="startup-table"></div>

<h2 class="demodebug">Run the current production <a target="_blank"
href="/currentVersion/slideshow/slideshow.php"
alt='slideshow'>SlideShow ($currentVersion)</a></h2>
<p><b>$slideshowRealName</b> is the directory of the current production version pointed to by the symlink <b>slideshow</b>.<br>
<b>$cpanelRealName</b> is the directory of the current production version pointed to by the symlink <b>cpanel</b>.</p>
<table border="1">
$curSlideshowTimes
</table>

<p class='notes'>All <i>Last Modified</i> times are $tz.<br>
Files modified during the last three days have the date in <span style="color: red">RED</span>.<br>
<a target="_blank" href="/change.log">Change Log</a> started Oct. 4, 2013.<br>
<a target="_blank" href="/currentVersion/gitInfo.php">Git Info</a> Run various 'git' commands
</p>

<p>The first time you run the <i>slideshow</i> you will need to enter the 'SiteCode' from the
table below. Copy and past the code into the form.
</p>
<p>The email upload program (<i>emailphoto.php</i>) is a CLI CRON job running with an interval
 of one minute.
</p>
<p>Use the email address below to send photo to a site.</p>
$sites
<p>To run any site use the <b>Site Code</b> from the above table.
You will need to clear your cookies for <b>go.myphotochannel.com</b> and then run the slide show.
You should be prompted for your <b>Site Code</b>.
</p>
<p>To clear your cookie go to the <i>Settings</i> in Chrome. Page down to the
bottom where it will say <i>Show Advanced Settings</i>, click on that.
Then under <i>Privacy</i> select
<i>Contents Settings</i> and then <i>All cookies and site data</i>, type in
<b>go.myphotochannel.com</b> and click on
the cookies <b>SiteId</b>, <b>SiteCode</b> and remove each one.
That's it. Now start the slideshow.
</p>
<p>To run the following in debug mode as 'super user' enter your secret code below. If you do
not have a secret code you can still run the slide show in debug mode for the site for which
you have certified.</p>

<style>
#logfiles {
  display: table;
  margin-left: 20px;
}
.row  {
  display: table-row;
  line-height: 0px;
}
.left, .right {
  display: table-cell;
  padding-left: 10px;
}
#logfilesh3 {
  margin-bottom: 0px;
}
</style>

<h3 id="logfilesh3">Log Files</h3>

<div id="logfiles">
<div class="row">
<div class="left"><a class='showlog' target="_blank" href="/emailphoto.log">Email Upload Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/emailphoto.log">Clear Log</button></div>
</div>

<div class="row">
<div class="left"><a class='showlog' target="_blank" href="/resize.log">Resize Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/resize.log">Clear Log</button></div>
</div>

<div class="row">
<div class="left"><a class='showlog' target="_blank" href="/photolotto.log">Photo Lotto Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/photolotto.log">Clear Log</button></div>
</div>

<div class="row">
<div class="left"><a class='showlog' target="_blank" href="/database.log">Error Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/database.log">Clear Log</button></div>
</div>
</div>

<!--
<table id="logfiles">
<tr>
<td>
<a class='showlog' target="_blank" href="/emailphoto.log">Email Upload Log</a>
<span class='size'></span>
</td>
<td>
<button class="clearlog" data-logname="/emailphoto.log">Clear Log</button>
</td>
</tr>
<td>
<a class='showlog' target="_blank" href="/resize.log">Resize Log</a>
<span class='size'></span>
</td>
<td>
<button class="clearlog" data-logname="/resize.log">Clear Log</button>
</td>
</tr>
<tr>
<td>
<a class='showlog' target="_blank" href="/photolotto.log">Photo Lotto Log</a>
<span class='size'></span>
</td>
<td>
<button class="clearlog" data-logname="/photolotto.log">Clear Log</button>
</td>
</tr>
<tr>
<td>
<a class='showlog' target="_blank" href="/database.log">Error Log</a>
<span class='size'></span>
</td>
<td>
<button class="clearlog" data-logname="/database.log">Clear Log</button>
</td>
</tr>
</table>
-->

<h3>Super user code: <input id="superuser" value='$superuser'>
 Cache control <input id="cache" type="checkbox" checked/> <span style="font-size: 12px">
For debugging this should usaully be set.</span></h3>
<p class="notes">Note on Cache Control: If the cache control checkbox is unchecked then none of the JavaScript file
are cached. This is the default for production where we want the most recent version of everything
without having to do any manual intervention. However, for debugging you would usually want the
browser to cache the JavaScript which makes debugging easier. If the scripts are not cached
breakpoints and other debug information is lost every time the code is reexecuted (via CTRL-R),
which makes debugging very difficult.</p>

<div id="links">
<div id="linkversion">
$currlink
</div> <!-- End #linkversion -->

<div id="cuttingedge">
<div id="cutting-slideshow">
<h3>SlideShow Cutting Edge Development ($workingVersion)</h3>
<p>Working on new feature: When a photo is emailed three previous photos are also marked 'feature'</p>
<ul>
<li><a target="_blank" href="/workingVersion/slideshow/slideshow.php">SlideShow</a>
<span class='superextra'>*</span> {$lm($slideshowAr, '/workingVersion/slideshow')}
</li>
<li><a target="_blank" href="/v1.09/slideshow/playbingo.php">Play Bingo</a>
 {$lm('playbingo.php', "/workingVersion/slideshow")}</li>
</ul>
</div>

<div id="cutting-cpanel">
<h3>CPanel Cutting Edge Development (v1.09)</h3>
<p>Renamed several modules to more appropriatly represent the function. The names are
less the extension which is 'php' or 'js':</p>
<button>Show Rename Table</button>
<table border="1">
<thead>
<tr><th>Was</th><th>Current</th><th>Text in Cpanel</th></tr>
</thead>
<tbody>
<tr><td>cpanel</td><td>same</td><td><i>Main Page</i></tr>
<tr><td>cpanel.approve</td><td>same</td><td>Approve Photos</td></tr>
<tr><td>cpanel.expunge</td><td>same</td><td>Remove Photos Marked Deleted</tr>
<tr><td>cpanel.tv</td><td>same</td><td>Text to Channel</td></tr>
<tr><td>cpanel.photoadmin</td><td>cpanel.managecontent</td><td>Manage Content</td></tr>
<tr><td>cpanel.segment</td><td>cpanel.showsettings</td><td>Show Settings</td></tr>
<tr><td>cpanel.commercial</td><td>same</td><td>Commercial Break Settings</td></tr>
<tr><td>cpanel.display</td><td>cpanel.category</td><td>Category Settings</td></tr>
<tr><td>cpanel.account</td><td>same</td><td>Account Settings</td></tr>
<tr><td>cpanel.newuser</td><td>same</td><td><i>Under Account Settings</i>: Add New User</td></tr>
<tr><td>cpanel.games</td><td>same</td><td>Game Settings</td></tr>
<tr><td>cpanel.lotto</td><td>same</td><td><i>Under Games Settings</i>: Lotto Data</td></tr>
<tr><td>cpanel.login</td><td>same</td><td><i>Login Screen</i></td></tr>
<tr><td>cpanel.admin</td><td> PC-cpanel</td><td><i>PC version of cpanel.managecontent</i></td></tr>
</tbody>
</table>

<ul>
<li><a target="_blank" href="/workingVersion/cpanel/cpanel.php">Control Panel (cpanel)</a>
<span class='superextra'>*</span>  {$lm($cpanelAr, "/workingVersion/cpanel")}</li>
</ul>
</div>
</div>
<p><span class='super'>* Must be super user.</span><br>
<span class='superextra'>* Super user can select site.</span>
</p>
<hr>

<div id="all-links">
<h3>List of all SlideShow Versions</h3>
<ul>
<li><a target="_blank" href="/oldVersion/slideshow/slideshow.php">SlideShow version $oldVersion</a>
{$lm($slideshowAr, '/oldVersion/slideshow')} Old Stable</li>
<li><a target=_blank" href="/oldVersion/slideshow/playbingo.php">Play Bingo for $oldVersion</a>
 {$lm('playbingo.php', "/oldVersion/slideshow")} Old Stable</li>
<li><a target="_blank" href="/currentVersion/slideshow/slideshow.php">SlideShow version $currentVersion</a>
{$lm($slideshowAr, '/currentVersion/slideshow')} Stable Current</li>
<li><a target=_blank" href="/currentVersion/slideshow/playbingo.php">Play Bingo for $currentVersion</a>
 {$lm('playbingo.php', "/currentVersion")} Stable Current</li>
<li><a target="_blank" href="/workingVersion/slideshow/slideshow.php">SlideShow version $workingVersion</a>
{$lm($slideshowAr, '/workingVersion/slideshow')} Working</li>
<li><a target=_blank" href="/workingVersion/slideshow/playbingo.php">Play Bingo for $workingVersion</a>
 {$lm('playbingo.php', '/workingVersion/slideshow')} Working</li>
</ul>

<h3>List of all Control Panel (cpanel) Versions</h3>
<ul>
<li><a target="_blank" href="/oldVersion/cpanel/cpanel.php">Control Panel version $oldVersion</a>
 {$lm($cpanelAr, '/oldVersion/cpanel')} Old Stable</li>
<li><a target="_blank" href="/currentVersion/cpanel/cpanel.php">Control Panel version $currentVersion</a>
 {$lm($cpanelAr, '/currentVersion/cpanel')} Stable Current</li>
<li><a target="_blank" href="workingVersion/cpanel/cpanel.php">Control Panel version $workingVersion</a>
 {$lm($cpanelAr, '/workingVersion/cpanel')} Working</li>
</ul>
</div>
</div>
<div>
<h3>This is version $currentVersion with the siteCode added to the URL</h3>
<ul>
<li><a target="_blank" class="demonodebug"
href="/currentVersion/slideshow/slideshow.php?siteCode=Felix's&unit=">SlideShow $currentVersion
direct to Felix's</a>
This has the <code>?siteCode=Felix's</code> attached so it goes right to Felix's in non-debug mode</li>
</ul>
<div class="notes">
<p>Note on Slideshow invocation: There are several arguments that can be attached
to the program invocation via query strings, that is the URL followed by '?' or '&'.</p>
<p>For example <code>go.myphotochannel.com/slideshow/slideshow.php?siteCode=Felix's&unit=10</code><br>
URL: go.myphotochannel.com/slideshow/slideshowphp<br>
First query: ?siteCode=Felix's<br>
Second query: &unit=10</p>

<ul>
<li><code>debug=nnnn</code> &mdash; <i>nnnn is your super user number</i></li>
<li><code>siteCode=ssss&hellip;</code> &mdash; <i>ssss is the Site Code like <code>siteCode=Felix's</code></i></li>
<li><code>cache=true</code> &mdash; <i>turns caching on. By default caching is off for production</i></li>
<li><code>unit=nnnn</code> &mdash; <i>If a site has multiple Internet feeds (computer boxes)
a 'unit' number is needed.
The numbers must be unique for each Internet feed (computer box). If a site has only one Internet feed
then this argument can be left off.  That is if there are more than one computer box then
you need a unit number for each box. When <code>debug=nnnn</code> is pressent the 'unit'is set
to 'nnnn'. If you run more than one debug instance you should manually change the 'unit number'.</i>
</li>
</ul>
<p>Note on Cpanel invocation: The are a couple of arguments that can be attached to the Control
Panel invocation:</p>
<ul>
<li><code>debug=nnnn</code> &mdash; <i>same as for slideshow</i></li>
<li><code>siteId=ssss&hellip;</code> &mdash; <i>ssss is the Site Id (not the Site Code)
<code>siteId=Felixs</code></i></li>
<li><code>userId=nnnn</code> &mdash; <i>nnnnn is the User Id. This is used by <b>emailphoto.php</b></i>
</li>
</ul>
</div>
<hr>
$footer
EOF;

exit();

// *************************
// Render Home page for a user

UserHomePage:

$h->banner = <<<EOF
<h1>$siteId Home Page</h1>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script>
var userId="$userId";
</script>
<script src="js/index.js"></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

$S->query("select siteCode from sites where siteId='$siteId'");
list($siteCode) = $S->fetchrow('num');

echo <<<EOF
$top
<h2>Run the current production
<a target="_blank" class="demonodebug"
href="/currentVersion/slideshow/slideshow.php?siteCode=$siteCode&unit=user$userId+"
alt='slideshow'>SlideShow ($currentVersion)</a></h2>
<p><b>$slideshowRealName</b> is the directory of the current production version pointed to by the symlink <b>slideshow</b>.<br>
<b>$cpanelRealName</b> is the directory of the current production version pointed to by the symlink <b>cpanel</b>.</p>

<table border="1">
$curSlideshowTimes
</table>
<p class='notes'>All <i>Last Modified</i> times are $tz.<br>
Files modified during the last three days have the date in <span style="color: red">RED</span>.<br>
<a target="_blank" href="/change.log">Change Log<a> started Oct. 4, 2013.
</p>
<p>The email upload program (<i>emailphoto.php</i>) is a CLI CRON job running
with an interval of one minute.<br>
Use the email address <i>$emailUsername</i> to send photo to a site.</p>

<h3>Current Programs</h3>
<div id="normaluser">
<ul>
<li><a  target="_blank" href="/currentVersion/cpanel/cpanel.php?siteId=$siteId">Control Panel ($currentVersion)</a>
{$lm($cpanelAr, '/currentVersion/cpanel')}</li>
<li><a target="_blank" href="/currentVersion/cpanel/PC-cpanel.php?siteId=$siteId">PC-cpanel</a>
{$lm(array("PC-cpanel.php","js/PC-cpanel.js"), '/currentVersion/cpanel')}.
This is the full screen PC version not the iPhone version.</li>
<li><a target="_blank" href="/currentVersion/uploadphotos.php?siteId=$siteId">Upload Photos From Client</a>
</span> {$lm(array("uploadphotos.php","js/uploadphotos.js"), "/currentVersion")}</li>
<li><a target="_blank" href="/currentVersion/itemsInfo.php?siteId=$siteId">Slide Show Info</a>
{$lm('itemsInfo.php', "/currentVersion")}</li>
<li><a target="_blank" href="/currentVersion/track-startup.php">Track Startups</a>
 {$lm('track-startup.php', "/currentVersion")}</li>
</ul>
</div>
<hr>
$footer
EOF;

exit();
?>
