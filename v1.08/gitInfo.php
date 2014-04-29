<?php
// BLP 2014-04-29 -- Do various git functions
if($cmd = $_GET['page']) {
  $out = '';
  exec("git $cmd", $out);
  $out = implode("\n", $out);
  $out = preg_replace(array("/</", "/>/"), array("&lt;","&gt;"), $out);
  echo "<pre>$out</pre>";
  exit();
}

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$h->title = "GIT Info";
$h->banner = "<h1>Show GIT Info</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<ul>
<li><a href="gitInfo.php?page=status">'git status'</a></li>
<li><a href="gitInfo.php?page=log">'git log'</a></li>
<li><a href="gitInfo.php?page=diff -w">'git diff -w'</a></li>
<li><a href="gitInfo.php?page=diff -w HEAD">'git diff -w HEAD'</a></li>
</ul>
<hr>
$footer
EOF;
?>