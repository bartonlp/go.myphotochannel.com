<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

$h = array('title'=>'Slideshow Redirect Page', 'banner'=>'<h1>Slide Show Redirect</h1>');

list($top, $footer) = $S->getPageTopBottom($h);
echo <<<EOF
$top
<p>Follow the link on the <a href="/">Home Page</a></p>
<p>Link to the current version of the <a href="/slideshow-front-end.php">Slide Show</a></p>
$footer
EOF;
?>