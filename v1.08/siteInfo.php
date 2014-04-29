<?php
//BLP 2014-04-29 -- made the table font size bigger and moved the margins a little
// sites table information
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

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
      if(preg_match($changed, $v, $m)) {
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
Server is in New York, the time is: $nytime<br>
<p>As of November 5, 2013 (v1.08 or cpanel and slideshow) allowAds, allowVideo, playbingo, playLotto,
perRecent, and featureExt are in the 'appinfo' table also.
If these fields are changed via cpanel v1.08 they will not be reflected in the 'sites' table
and visa versa.</p>
<p>Once we update to v1.08 across the board the fields will be removed from the 'sites' table, which
will <span style="color: red">break</span> earlier version of cpanel and slideshow!</p>

<div id="bigdiv">
<div id="tbldiv">
$tbl
</div>
</div>
<hr>
$footer
EOF;

?>