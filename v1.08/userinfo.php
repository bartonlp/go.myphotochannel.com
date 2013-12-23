<?php

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
?>