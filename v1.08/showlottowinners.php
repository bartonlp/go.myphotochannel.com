<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);
$T = new dbTables($S);

$sql = "select * from lottowinners";
list($tbl) = $T->maketable($sql, array('attr'=>array('id'=>'table', 'border'=>'1')));

$h->title = "Show Lotto Winners";
$h->banner = "<h1>Show Lotto Winners</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
$tbl
$footer
EOF;
?>