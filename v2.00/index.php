<?php
// BLP 2016-01-19 -- removed version changing logic
// BLP 2014-07-17 -- Comment out the cutting edge stuff for now and
// only show 'active' sites and
// add 'Photos last week' to site stuff.
// BLP 2014-03-25 -- added last day photos and total photos to table#sitesinfo
// index for tomsproject
// BLP 2014-01-10 -- Add resize.log by the other two logs
// Javascript for this program is at js/index.js
// 310-452-5409 Heidi store.

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
define(DOC_ROOT, $_site->path);
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$tz = date('T (O \G\M\T)');

// Here is a little bit of trickery
// The function lastMod() returns the filemtine() for the filename in $x.
// We can embed this into a string as follows: first we make a variable with the name of the
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
    $times[] = $mtime;
  }
  // From the $times array of filemtimes return the max value

  $modtime = max($times);
  define(ONEDAY, 86400);
  
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

$currentVersion = getversion("/currentVersion");
$curSlideshowTimes = "";

foreach($slideshowAr as $file) {
  $curSlideshowTimes .= "<tr><td>/slideshow/$file:</td>".
                        "<td>{$lm($slideshowAr, '/currentVersion/slideshow')}</td></tr>\n";
}

function getlinkversion($type) {
  global $lm, $slideshowAr, $cpanelAr, $currentVersion;
  
  switch($type) {
    case "current":
    default:
      $name = "current";
      //$path = "http://bartonlp.com/myphotochannel.com/currentVersion";
      break;
  }

  $link = <<<EOF
<p>Debug links to the $name versions ($currentVersion):</p>
<ul>
<li><a target="_blank" href="currentVersion/slideshow/slideshow.php">SlideShow ($currentVersion) 
with debug info in upper left</a>
<span class='superextra'>*</span> {$lm($slideshowAr,"currentVersion/slideshow")}</li>
<li><a  target="_blank" href="currentVersion/cpanel/cpanel.php">Control Panel ($currentVersion)</a>
<span class='superextra'>*</span> {$lm($cpanelAr,"currentVersion/cpanel")}</li>
<li><a target="_blank" href="currentVersion/cpanel/PC-cpanel.php">PC-cpanel</a>
<span class='superextra'>*</span> {$lm(array("PC-cpanel.php","js/PC-cpanel.js"),"currentVersion/cpanel")}.
This is the full screen PC version not the iPhone version.</li>
<li><a target="_blank" href="currentVersion/cpanel/PC-delete.php">PC-delete</a>
<span class='superextra'>*</span> {$lm(array("PC-delete.php"),"currentVersion/cpanel")}.
This is the a PC version not an iPhone version.</li>
<li><a target="_blank" href="currentVersion/uploadphotos.php">Upload Photos From Client</a>
<span class='superextra'>*</span> 
{$lm(array("uploadphotos.php","js/uploadphotos.js"), "currentVersion")}</li>
<li><a target="_blank" href="currentVersion/itemsInfo.php">Slide Show Info</a>
<span class='superextra'>*</span>  {$lm('itemsInfo.php', "currentVersion")}</li>
<li><a target="_blank" href="currentVersion/siteInfo.php">'sites' Table Info</a>
{$lm('siteInfo.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/userinfo.php">'users' Table Info</a>
{$lm('userinfo.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/appInfo.php">'appinfo' Table Info</a>
{$lm('appInfo.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/webstats.php">Web Stats</a>
<span class='super'>*</span>  {$lm('webstats.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/itemsTableMaint2.php">Check Items Table for Integrity</a> 
<span class='super'>*</span>  {$lm('itemsTableMaint2.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/uploadsforweek.php">Who Emailed Photos This Week</a>
 {$lm('uploadsforweek.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/whoapproved.php">Who Approved Photos</a>
 {$lm('whoapproved.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/track-startup.php">Track Startups</a>
 {$lm('track-startup.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/pushercheck.php">Pusher Status</a>
 {$lm('pushercheck.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/videocontrol.php">Video Control Panel</a> <span class='super'>*</span>
{$lm(array('videocontrol.php', 'js/videocontrol.js'),"currentVersion")}. Control Panel for vidos in the 'ads' and 'items' tables</li>
<li><a target="_blank" href="currentVersion/createNewSite.php">Create A Site</a>
<span class='super'>*</span> {$lm('createNewSite.php',"currentVersion")}</li>
<li><a target="_blank" href="currentVersion/deleteSite.php">Delete A Site</a> <span class='super'>*</span>
{$lm('deleteSite.php',"currentVersion")}. 
<span style="color: white; background: red; padding: 0 4px"><b>BE VERY CAREFUL!</b></span></li>
<li><i>emailphoto.php</i> {$lm('emailphoto.php',"currentVersion")}. 
A CLI run from CRON. Processes photos emailed by
customers every minute.</li>
<li><i>mysitemap.json</i> {$lm('mysitemap.json', "/")}. Configuration file for site.</li>
<li><a target="_blank" href="currentVersion/slideshow/photoloto.php">Photo Lotto (photoloto.php)</a>
 {$lm('photoloto.php', "currentVersion/slideshow")}. CLI or Web program. The CLI is
run from a CRON job.</li>
<li><a target="_blank" href="currentVersion/showlottowinners.php">Show Lotto Winners</a>
 {$lm('showlottowinners.php', "currentVersion")}.</li>
</ul>

<h3>Ads Programs (not in production use)</h3>
<p>These are pretty stable but only work with <i>slideshow-v1.02</i> or higher.</p>
<ul>
<li><a target="_blank" href="currentVersion/cpanel/adsCpanel.admin.php">Ads CPanel</a>
<span class='super'>*</span>
{$lm(array("adsCpanel.admin.php", "adsCpanel.ajax.php", "js/adsCpanel.js"),"currentVersion/cpanel")}</li>
<li><a target="_blank" href="currentVersion/adsadmin.php">Admin adsInfo</a>
{$lm('adsadmin.php',"currentVersion")} <span class='super'>*</span></li>
<li><a target="_blank" href="currentVersion/adsAccountAdmin.php">Admin the Ads Accounts</a>
{$lm('adsAccountAdmin.php',"currentVersion")}. Add or Edit Account Info
<span class='super'>*</span></li>
<li><a target="_blank" href="currentVersion/uploadads.php">Upload Ads from Client</a>
<span class='super'>*</span> {$lm(array('uploadads.php', 'js/uploadads.js'),"currentVersion")}</li>
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

// Ajax filesize

if($_POST['page'] == 'filesize') {
  $file = DOC_ROOT . "{$_POST['file']}";
  $size = filesize($file);
  echo "Size: $size";
  exit();
}

// Ajax gettable

if($_GET['name'] == 'gettable') {
  $S = new Database($_site);

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
<h2>Sites Status OPEN or active today</h2>
<table border='1'>
<tr><th>No Sites Running At This Time.</th></tr></table>
EOF;
  } else {
    $button = '';
    if($c) {
      $openclosed = "Open=$n, Closed=$c: <button></button>";
    }
      
    $tbl = <<<EOF
<h2>Sites Status OPEN or active today
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

$S = new $_site->className($_site);

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
    
    while(list($userId, $siteId) = $S->fetchrow('num')) {
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

  list($userId, $siteId) = $S->fetchrow('num');
  goto UserHomePage;
  exit();
}

// Verify2 PAGE
// Second step verify for user on multiple sites

if($_POST['page'] == "verify2") {
  list($userId, $siteId) = explode(",", $_POST['siteinfo']);
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

  if(!setcookie("superuser", $superuser, $expire)) {
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
    $S->setSiteCookie($userId, 'userId', time()+31536000); // One year
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

$h->title = "MyPhotoChannel";

$h->link =<<<EOF
<link rel="stylesheet" href="css/index.css">
EOF;

$h->banner = <<<EOF
<h1>Development Home Page</h1>
EOF;

$h->extra =<<<EOF
<link rel="stylesheet" type="text/css"
href="http://fonts.googleapis.com/css?family=Lora&subset=latin">
<style>
body {
  font-family: 'Lora', serif;
}
ul {
  line-height: 150%;
}
#logfiles {
  line-height: 150%;
}
</style>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script>
var userId="$userId";
</script>
<script src="js/index.js"></script>
<!-- end extra -->
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Create the sites table with all the siteId, sitCode, emailUsername, and #photos today, total
// photos.
// BLP 2014-07-17 --  add "where status='active'"

$sql = "select siteId, emailUsername, siteCode from sites where status='active'";
$n = $S->query($sql);
$r = $S->getResult();

$sites = <<<EOF
<p>There are now $n sites with a status of 'active':</p>

<style>
#sitesinfo th {
  padding: 3px;
}
</style>

<table id="sitesinfo" border="1">
<thead>
<tr><th>Site Id</th><th>Upload Email Address</th><th>Site Code</th>
<th>Photos<br>Last Day</th><th>Photos<br>Last Week</th><th>Total Photos</th></tr>
</thead>
<tbody>

EOF;

// BLP 2014-07-17 -- add select last week and add 'Photos<br>Last Week' above to thead
// Note: count(exp) counts the NON-null items BUT the expression in the if() returns 0 or 1 (false
// or true) therefore the if() to turn the expression results into 1 or null. Seems a bit too hard
// but that's the way it works.

while(list($siteId, $email, $siteCode) = $S->fetchrow($r, 'num')) {
  $sql = "select count(*), ".
         "count(if(creationTime > date_sub(now(), interval 1 day), 1, null)), ".
         "count(if(creationTime > date_sub(now(), interval 1 week), 1, null)) ".
         "from items where siteId='$siteId' ".
         "and status='active'";
  $S->query($sql);
  list($tot, $d, $w) = $S->fetchrow('num');

  $sites .= "<tr><td>$siteId</td><td>$email</td><td>$siteCode</td>".
            "<td>$d</td><td>$w</td><td>$tot</td></tr>\n";
}
$sites .= "</tbody>\n</table>\n";

// Get the linkversion area for the currentVersion

$currlink = getlinkversion("current");

// Render Page for Superuser

echo <<<EOF
$top
<p>Index Version $currentVersion</p>
$superWelcome
<h2>All Apps now use 'vendor/bartonlp/site-class'</h2>
<div id="startup-table"></div>
<h2 class="demodebug">Run the current production <a target="_blank"
href="currentVersion/slideshow/slideshow.php"
alt='slideshow'>SlideShow ($currentVersion)</a> uses 'SiteCode' cookie if set.</h2>
<p><b>$slideshowRealName</b> is the directory of the current production version pointed to by the symlink <b>slideshow</b>.<br>
<b>$cpanelRealName</b> is the directory of the current production version pointed to by the symlink <b>cpanel</b>.</p>
<table border="1">
$curSlideshowTimes
</table>

<p class='notes'>All <i>Last Modified</i> times are $tz.<br>
Files modified during the last three days have the date in <span style="color: red">RED</span>.<br>
<a target="_blank" href="change.log">Change Log</a> started Oct. 4, 2013.<br>
<a target="_blank" href="currentVersion/gitInfo.php">Git Info</a>
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
  /*line-height: 0px;*/
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
<div class="left"><a class='showlog' target="_blank" data-logname="/emailphoto.log"
href="emailphoto.log">Email Upload Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/emailphoto.log">Clear Log</button></div>
</div>

<div class="row">
<div class="left"><a class='showlog' target="_blank" data-logname="/resize.log"
href="/resize.log">Resize Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/resize.log">Clear Log</button></div>
</div>

<div class="row">
<div class="left"><a class='showlog' target="_blank" data-logname="/photolotto.log"
href="/photolotto.log">Photo Lotto Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/photolotto.log">Clear Log</button></div>
</div>

<div class="row">
<div class="left"><a class='showlog' target="_blank" data-logname="/database.log"
href="/database.log">Error Log</a>
<span class='size'></span></div>
<div class="right"><button class="clearlog" data-logname="/database.log">Clear Log</button></div>
</div>
</div>

<h3>Super user code: <input id="superuser" value='$superuser'>
</h3>
<div id="links">
<div id="linkversion">
$currlink
</div> <!-- End #linkversion -->

</div>
<div>
<h3>This is version $currentVersion with the siteCode added to the URL</h3>
<ul>
<li><a target="_blank" class="demonodebug"
href="currentVersion/slideshow/slideshow.php?siteCode=Felix's&unit=">SlideShow $currentVersion
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
<li><code>unit=nnnn</code> &mdash; <i>If a site has multiple Internet feeds (computer boxes)
a 'unit' number is needed.
The numbers must be unique for each Internet feed (computer box). If a site has only one Internet feed
then this argument can be left off.  That is if there are more than one computer box then
you need a unit number for each box. When <code>debug=nnnn</code> is pressent the 'unit' is set
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
<h2>All apps now use 'vendor/bartonlp/site-class'</h2>
<h2 class="demodebug">Run the current production
<a target="_blank" 
href="currentVersion/slideshow/slideshow.php?unit="
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
<li><a  target="_blank" href="currentVersion/cpanel/cpanel.php?siteId=$siteId">Control Panel ($currentVersion)</a>
{$lm($cpanelAr, '/cpanel')}</li>
<li><a target="_blank" href="currentVersion/cpanel/PC-cpanel.php?siteId=$siteId">PC-cpanel</a>
{$lm(array("PC-cpanel.php","js/PC-cpanel.js"), '/currentVersion/cpanel')}.
This is the full screen PC version not the iPhone version.</li>
<li><a target="_blank" href="currentVersion/uploadphotos.php?siteId=$siteId">Upload Photos From Client</a>
</span> {$lm(array("uploadphotos.php","js/uploadphotos.js"), "currentVersion")}</li>
<li><a target="_blank" href="currentVersion/itemsInfo.php?siteId=$siteId">Slide Show Info</a>
{$lm('itemsInfo.php', "currentVersion")}</li>
<li><a target="_blank" href="currentVersion/track-startup.php">Track Startups</a>
 {$lm('track-startup.php', "currentVersion")}</li>
</ul>
</div>
<hr>
$footer
EOF;

exit();
