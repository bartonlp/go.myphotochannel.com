<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);
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
