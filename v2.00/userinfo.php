<?php
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

$t = new dbTables($S); // make tables logic

$h->title = "MyPhotoChannel";
$h->banner = <<<EOF
<h1>Users Table</h1>
EOF;

$h->link = <<<EOF
<style>
td, th {
  padding: 5px;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// Callback function for mktable()

function giveIdClass(&$row, &$rowdesc) {
  if($row['ID']) {
    $rowdesc = preg_replace("~<td>ID</td>~", "<td class='id'>{$row['ID']}</td>", $rowdesc);
  }
}

$query = "select id as ID, concat(fname, ' ', lname) as Name, siteId as SiteId, " .
         "status as Status, emailNotify, email as Email, password as Password, ".
         "notifyPhone, notifyCarrier, emailNotify, textNotify ".
         "from users order by id";

list($tbl) = $t->maketable($query,
  array(callback=>giveIdClass, attr=>array(id=>"users", border=>"1")));

echo <<<EOF
$top

<div id="users">
$tbl
</div>
<hr>
$footer
EOF;
