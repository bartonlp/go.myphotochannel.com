<?php
// From the 'items' table see who has emailed the most photos for a week/all-time
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

$h->title = "email photos sent";

$h->banner = <<<EOF
<h1>Uploads for the Week</h1>
EOF;

$h->link =<<<EOF
<style>
td {
  padding: 5px;
}
td:nth-child(1) {
  text-align: right;
}
th:nth-child(1) {
  text-align: right;
  padding: 5px;
}
</style>
EOF;


list($top, $footer) = $S->getPageTopBottom($h);

$sql = "select date_sub(now(), interval 7 day)";
$S->query($sql);
list($sDate) = $S->fetchrow('num');
$sDate = preg_replace("/ .*/", '', $sDate);
$startDate = date("F j, Y", strtotime($sDate)); 
$sql = "select siteId from sites";
$S->query($sql);
while(list($id) = $S->fetchrow('num')) {
  $siteId[] = $id;
}

foreach($siteId as $site) {
  $sql = "select count(*) as cnt, creatorName from items where siteId='$site' ".
         "and showTime > '$sDate' group by creatorName order by cnt";
  $n = $S->query($sql);
  $tbl = "";
  $sum = 0;
  while(list($cnt, $name) = $S->fetchrow('num')) {
    if($name == "Admin" || $name == "Upload") continue;
    $sum += $cnt;
    $tbl .= "<tr><td>$cnt</td><td>$name</td></tr>\n";
  }
  $tbl =<<<EOF
<h2>Info for $site</h2>
<table border="1">
<thead><th>Count</th><th>Name</th></tr></thead>
<tbody>
$tbl
</tbody>
<tfoot>
<tr><th>$sum</th><th>Total</th></tr>
</tfoot>
</table>

EOF;
  $body .= $tbl;
}
echo <<<EOF
$top
<h1>Weeks Start Date: $startDate</h1>
$body
<hr>
$footer
EOF;
