<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

function notAllowed() {
  echo "<h1>Only 'super users' are allowed to delete sites. Sorry.</h1>";
  exit();
}

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

if($superuser = $_GET['debug']) {
  $sql = "select concat(fname, ' ', lname) from superuser where password='$superuser'";
  if(!$S->query($sql)) {
    notAllowed();
    exit();
  }
  list($name) = $S->fetchrow('num');
  $superuserName = $name;
} else {
  notAllowed();
  exit();
}

$h->title = "Delete A Site";
$h->banner = <<<EOF
<h1>Delete A Site</h1>
EOF;

$h->link =<<<EOF
<style>
#warn-p {
  position:relative;
}
#warn {
  text-decoration:underline;
  -webkit-animation:animated_div 5s 1 2s;
  width:180px;
  height:50px;
  background:red;
  color:white;
  position:absolute;
  top: -30px;
  left: 720px;
  font-weight:bold;
  font-size:30px;
  padding:10px;
  padding-top: 20px;
  -webkit-border-radius:15px;
}
@-webkit-keyframes animated_div {
  0%		{-webkit-transform: rotate(0deg);left:720px;}
  25%		{-webkit-transform: rotate(20deg);left:720px;}
  50%		{-webkit-transform: rotate(0deg);left:1000px;}
  55%		{-webkit-transform: rotate(0deg);left:1000px;}
  70%		{-webkit-transform: rotate(0deg);left:1000px;background:green;}
  100%	{-webkit-transform: rotate(-360deg);left:720px;}
}
#delete {
  padding: 20px;
  background-color: red;
  color: white;
  -webkit-border-radius:15px;
}
</style>
EOF;

$h->extra =<<<EOF
  <script src="http://code.jquery.com/jquery-1.8.2.js"></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

if($_POST['page'] == "deletesite") {
  extract($_POST);

  $sql = "delete from sites where siteId='$siteid'";
  //cout("$sql");
  $S->query($sql);

  $sql = "delete from users where siteId='$siteid'";
  //cout("$sql");
  $S->query($sql);

  $sql = "delete from appinfo where siteId='$siteid'";
  //cout("$sql");
  $S->query($sql);

  $sql = "delete from categories where siteId='$siteid'";
  //cout("$sql");
  $S->query($sql);

  $sql = "delete from segments where siteId='$siteid'";
  //cout("$sql");
  $S->query($sql);

  // Finally delete all images from the items table. I am not deleting the images from the content
  // folder.
  
  $sql = "delete from items where siteId='$siteid'";
  //cout("$sql");
  $S->query($sql);
  
  echo <<<EOF
$top
<h1>Site Deleted</h1>
<p>The site information has been deleted from all tables but the images have not been deleted
from the 'content' folder. They will show up as unattached images when you run
<b>Check Items Table for Integrity</b>. If you have made a terrible mistake you can recover by
using the backup data. Email bartonphillips@gmail.com right away and explain
what terrible thing you have done.</p>
<hr>
$footer
EOF;

  exit();
}

// ****************************************
// Start Page
// First Get the Information for a new site
// ****************************************

$S->query("select siteId from sites");
$opt = '';
while(list($siteid) = $S->fetchRow('num')) {
  $opt .= "<option>$siteid</option>\n";
}

echo <<<EOF
$top
<h2>Welcome 'super user' $superuserName</h2>
<div id="warn-p">This will delete the selected site from all tables. This is not easily reversable so be
very <div id="warn">CAREFULL!</div></div>
<form method="post">
Select site to delete: <select name="siteid">
$opt
</select>
<input type="hidden" name="page" value="deletesite"/>
<br><span style="color: red">When you press submit it is all over, so DON'T press submit unless you really
really want to delete the site!</span>
<br><input id="delete" type="submit" value="SUBMIT to DELETE FOREVER" />
</form>
<hr>
$footer
EOF;

