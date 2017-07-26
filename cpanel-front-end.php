<?php
// www.myphotochannel.com has a link to the control panel that link comes HERE so we can let the
// user sign in.

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

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

  header("Location: http://go.myphotochannel.com/currentVersion/cpanel/cpanel.php?siteId=$siteid");
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