<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

if($_POST['page'] == 'delete') {
  unset($_POST['page']);
  
  $list = '';  
  foreach($_POST as $key=>$v) {
    $S->query("update items set status='delete' where itemId=$key");
    $list .= "<li>$key</li>";
  }
  list($top, $footer) = $S->getPageTopBottom();
  
  echo <<<EOF
$top
<p>The following images have been mark 'delete'. To remove these images please use the control panel
'Expunge Photos'.</p>
<ul>
$list
</ul>
$footer
EOF;

  exit();
}

if($_POST['page'] == 'show') {
  $siteId = $_POST['siteId'];
  $d1 = $_POST['startDate'];
  $d2 = $_POST['endDate'];

  $n = $S->query("select itemId, location, creationTime ".
                 "from items ".
                 "where siteId='$siteId' and ".
                 "category='photo' and ".
                 "creationTime between '$d1' and '$d2' and ".
                 "status != 'delete' order by creationTime");

  if(!$n) {
    echo <<<EOF
<h1>Nothing within range</h1>
EOF;
    exit();
  }

  $list = "<form method='post'><table>";

  while(list($id, $image, $time) = $S->fetchrow('num')) {
    $file = "../" . $image;
    $list .= "<tr><td class='imagetd'>$time<br>Delete <input type='radio' name='$id'> <img class='image' src='$file'></td></tr>";
  }
  $list .= <<<EOF
</table>
<input type='submit'>
<input type='hidden' name='page' value='delete'>
</form>
EOF;

  $h->css = <<<EOF
  <style>
.image {
  width: 200px;
  vertical-align: middle;
  margin-bottom: .5rem;
}
.imagetd {
  border: 1px solid black;
  padding: .2rem;
}
  </style>
EOF;

  list($top, $footer) = $S->getPageTopBottom($h);

  echo <<<EOF
$top
<h1>Images To Delete</h1>
<h3>Range $d1 to $d2</h3>
$list
$footer
EOF;

  exit();
}

// Start Page

$S->query("select siteId from sites where status = 'active'");
$opt = '';

while(list($siteId) = $S->fetchrow('num')) {
  $opt .= "<option>$siteId</option>";
}

$h->title = "Start";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<h1>Delete Images</h1>
<form method='post'>
SiteId <select name='siteId'>
$opt
</select>
<table>
<tr>
<td>Start Date (yyyy-mm-dd)</td><td><input type='text' name='startDate'></td>
</tr>
<tr>
<td>End Date (yyyy-mm-dd)</td><td><input type='text' name='endDate'></td>
</tr>
</table>
<input type='submit'>
<input type='hidden' name='page' value='show'>
</form>
$footer
EOF;
