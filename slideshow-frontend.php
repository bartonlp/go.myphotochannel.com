<?php
// Slideshow front end asks for siteCode and user information
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

if($_POST['page'] == "post") {
  $siteCode = $_POST['sitecode'];
  $sql = "select siteId from sites where siteCode='$siteCode'";
  
  if($S->query($sql)) {
    list($siteId) = $S->fetchrow('num');
    
    $sql = "select siteId from users where email='{$_POST['email']}' ".
           "and password='{$_POST['password']}' and siteId='$siteId'";
    if($S->query($sql)) {
      header("Location: http://go.myphotochannel.com/currentVersion/slideshow/slideshow.php?siteCode=$siteCode");
    }
  }
  $h->title = "bad signin";
  $h->banner = "<h1>Signin Not Valid</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  echo <<<EOF
$top
$footer
EOF;
  exit();
}

$h->title = "Slideshow Front End";
$h->banner = "<h1>Slideshow Login</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form method="post" action="$S->self">
<table>
<tr><th>SiteCode:</th><td><input type="text" id="siteid" name="sitecode"/></td></tr>
<tr><th>Email Address:</th><td><input type="text" id="email" name="email"/></td></tr>
<tr><th>Password:</th><td><input type="password" id="password" name="password"/></td></tr>
<tr><th colspan="2"><button id="submit">Submit</button></th></tr>
</table>
<input type="hidden" name="page" value="post"/>
</form>
<hr>
$footer
EOF;
