<?php
//BLP 2014-04-29 -- made the table font size bigger and moved the margins a little
// sites table information
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new $_site->className($_site);

if(!$S->query("select fname from superuser where password='{$_GET['debug']}'")) {
  echo "<h1>Only For super users</h1>";
  exit();
}

$S->query("select now()");  
list($nytime) = $S->fetchrow('num');
$nytime = date("F j, Y H:i:s", strtotime($nytime));
$h->title = "sites table info";
$h->banner = <<<EOF
<h1>sites Table Information</h1>
EOF;

$h->link =<<<EOF
<style>
/* BLP 2014-04-29 -- font-size from 6 to 16, width from 95 to 120, margin-left from 100 to 120 */
#siteinfo {
  font-size: 16px;
}
#siteinfo tr {
  border: 1px solid black;
}
#siteinfo th:first-of-type, #siteinfo td:first-of-type {
  position: absolute;
  width: 120px;
  left: 11px;
  top: auto;
}
#siteinfo th, #siteinfo td {
  border: 1px solid black;
  padding: 0 2px 0 2px;
}
#tbldiv {
  overflow-x:scroll;  
  margin-left:120px;
  overflow-y:visible;
}
#bigdiv {
  border: 1px solid black;
}
</style>
EOF;

$h->extra = <<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script>
jQuery(document).ready(function($) {
  $("#siteinfo td:nth-child(2)").each(function(i, v) {
    var h = $(v).prop("offsetHeight");
    $(v).siblings().eq(0).attr("height", h-2);
  });
});
</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);
// BLP 2014-04-14 -- 
$changed = "/(allowAds)|(allowVideo)|(playbingo)|(playLotto)|(featureExt)|(perRecent)/";

$S->query("select * from sites");
$tbl = '<tbody>';
$hdr = '';
while($row = $S->fetchrow('assoc')) {
  if(empty($hdr)) {
    $hdr .= <<<EOF
<thead>
<tr>
EOF;
    foreach(array_keys($row) as $v) {
      if(preg_match($changed, $v)) {
        $hdr .= "<th style='color: red'>$v</th>";
      } else {
        $hdr .= "<th>$v</th>";
      }
    }
    $hdr .= <<<EOF
</tr>
</thead>
EOF;
  }

  $tbl .= "<tr>";
  foreach($row as $v) {
    $tbl .= "<td>$v</td>";
  }
  $tbl .= "</tr>";
}
$tbl = <<<EOF
<table id="siteinfo">
$hdr
$tbl
</tbody>
</table>
EOF;

echo <<<EOF
$top
Server is in California, the time is: $nytime<br>
<div id="bigdiv">
<div id="tbldiv">
$tbl
</div>
</div>
<hr>
$footer
EOF;
