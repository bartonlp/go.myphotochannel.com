<?php
// Can we put the slideshow in an iframe?
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

if($_GET['userId']) {
  $s->id = $_GET['userId'];
} else {
  $s->id = 0;
}
$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";

$S = new Tom($s);

$h->banner = "<h1>Iframe Test</h1>";
$h->link =<<<EOF
<link rel="stylesheet" href="/css/index.css">
EOF;
list($top, $footer) = $S->getPageTopBottom($h);

echo <<< EOF
$top
<iframe width="700" height="500"
   src="http://go.myphotochannel.com/slideshow-v1.07/slideshow.php?siteCode=Site-Demo&unit=blptest"></iframe>
<iframe width="400" height="500"
   src="http://go.myphotochannel.com/cpanel-v1.06/cpanel.php?siteId=Site-Demo&userId=51"></iframe>
<hr>       
$footer
EOF;


             