<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);
$T = new dbTables($S);

$sql = "select * from lottowinners";

$tbl = $T->maketable($sql, array('attr'=>array('id'=>'table', 'border'=>'1')));
$num = $tbl['num'];
$tbl = $tbl['table'];
$sql = "select count(*) from lottowinners where redeemtime != ''";
$S->query($sql);
list($redeem) = $S->fetchrow('num');
$diff = $num - $redeem;
$per = number_format($redeem/$num * 100, 2);

$h->title = "Show Lotto Winners";
$h->banner = "<h1>Show Lotto Winners</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>Total: $num<br>
Redeemed: $redeem<br>
% Redeemed: $per</p>
$tbl
$footer
EOF;
?>