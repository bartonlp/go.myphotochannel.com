<?php
// www.myphotochannel.com has a link to the control panel that link comes HERE so we can let the
// user sign in.

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

if($_POST['page'] == "post") {
  $sql = "select siteId from users where email='{$_POST['email']}' ".
         "and password='{$_POST['password']}' and siteId='{$_POST['siteid']}'";
  if(!$S->query($sql)) {
    $h->title = "bad signin";
    $h->banner = "<h1>Signin Not Valid</h1>";
    list($top, $footer) = $S->getPageTopBottom($h);
    echo <<<EOF
$top
$footer
EOF;
    exit();
  }
  list($siteId) = $S->fetchrow('num');
  header("Location: http://go.myphotochannel.com/cpanel/cpanel.php?siteId=$siteId");
  exit();
}

$h->title = "CPanel Front End";
$h->banner = "<h1>Control Panel Login</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form method="post" action="$S->self">
<table>
<tr><th>SiteId:</th><td><input type="text" id="siteid" name="siteid"/></td></tr>
<tr><th>Email Address:</th><td><input type="text" id="email" name="email"/></td></tr>
<tr><th>Password:</th><td><input type="password" id="password" name="password"/></td></tr>
<tr><th colspan="2"><button id="submit">Submit</button></th></tr>
</table>
<input type="hidden" name="page" value="post"/>
</form>
<hr>
$footer
EOF;
?>