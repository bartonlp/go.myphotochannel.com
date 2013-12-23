<?php
// From the 'items' table see who has emailed the most photos for a week/all-time

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$h->title = "email photos approved";

$h->banner = <<<EOF
<h1>Who Approved Emailed Photos</h1>
EOF;

$h->link =<<<EOF
<style>
td {
  padding: 5px;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

$sql = "select siteId from sites";
$S->query($sql);
while(list($id) = $S->fetchrow('num')) {
  $siteId[] = $id;
}

foreach($siteId as $site) {
  $sql = "select approved, approvedtime, concat(fname, ' ', lname) from users where siteId='$site' ".
         " order by visits";
  $n = $S->query($sql);
  $tbl = "";
  while(list($cnt, $time, $name) = $S->fetchrow('num')) {
    if($name == "Admin") continue;
    $tbl .= "<tr><td>$cnt</td><td>$time</td><td>$name</td></tr>\n";
  }
  $tbl =<<<EOF
<h2>Info for $site</h2>
<table border="1">
<thead><th>Count</th><th>Last</th><th>Name</th></tr></thead>
<tbody>
$tbl
</tbody>
</table>

EOF;
  $body .= $tbl;
}
echo <<<EOF
$top
<p>Since Oct. 9, 2013.</p>
$body
<hr>
$footer
EOF;
?>