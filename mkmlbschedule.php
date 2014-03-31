#! /usr/bin/php6 -q
<?php
// Also force our TOPFILE
define('TOPFILE', "/homepages/45/d454707514/htdocs/siteautoload.php");
// Now this looks like all the other files.
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else {
  echo "Can't find siteautoload.php";
  exit();
}

// During debug
Error::setDevelopment(true);
Error::setNoEmailErrs(true);
Error::setNoHtml(true);

$S = new Database($dbinfo);

$sql = "select concat(date,' ', time), subject, location from sportsschedule ".
       "where date > curdate() limit 3";

$S->query($sql);
$tbl = '';
while(list($date, $subject, $location) = $S->fetchrow('num')) {
  $date = date("l F j", strtotime($date));
  $tbl .= "<li>$date, $subject $location</li>\n";
}

$file = <<<EOF
<style>
#cardinalsschedule {
  position: relative;
  font-size: 45px;
  color: black;
  text-align: left;
  z-index: 100;
  font-weight: bold;
  list-style-type: none;
}
#cardinalsschedule li {
  margin-top: 40px;
}
#cardinalsimg {
  width: 100%;
}
#cardinalstbl {
  position: absolute;
  left: 70px;
  top : 200px;
  width: 1200px;
}
</style>
<div id="cardinalstbl">
<ul id="cardinalsschedule">
$tbl
</ul>
</div>
<img id="cardinalsimg" src="/images/mlb.png"/>
EOF;
file_put_contents("adscontent/mlb.html", $file);
echo <<<EOF
mlb.html created\n
EOF;
?>