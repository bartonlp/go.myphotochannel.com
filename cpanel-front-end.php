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
  $siteid = $S->escape($_POST['siteid']);
  $password = $S->escape($_POST['password']);
  $email = $S->escape($_POST['email']);
  
  $sql = "select siteId from users where email='$email' ".
         "and password='$password' and siteId='$siteid'";

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

  header("Location: http://go.myphotochannel.com/cpanel/cpanel.php?siteId=$siteid");
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