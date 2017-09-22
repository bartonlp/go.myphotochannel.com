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

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
define(DOC_ROOT, $_site->path);
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new $_site->className($_site);

$h->title = "GIT Info";
$h->banner = "<h1>Show GIT Info</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<ul>
<li><a href="gitInfo.php?page=status">'git status'</a></li>
<li><a href="gitInfo.php?page=log --abbrev-commit">'git log'</a></li>
<li><a href="gitInfo.php?page=diff -w">'git diff -w'</a> diff between uncommited and HEAD</li>
<li><a href="gitInfo.php?page=diff -w HEAD^">'git diff -w HEAD^'</a>
diff between HEAD and previous HEAD</li>
</ul>
<hr>
$footer
EOF;
