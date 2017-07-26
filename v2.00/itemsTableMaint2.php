<?php
// BLP 2014-04-30 -- rework
// Items table cleanup

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new $_site->className($_site);

// ONLY super users

if(!$S->query("select fname from superuser where password='{$_GET['debug']}'")) {
  echo "<h1>Only For super users</h1>";
  exit();
}

// Delete items selected below. And add items to the 'items' table if requested.

if($_POST['page'] == 'post') {
  if($files = $_POST['add']) {
    $siteId = $_POST['siteid'];
    if(!$S->query("select fname from sites where siteId='$siteId'")) {
      echo "<h1>SiteId not valid try again</h1>";
      exit();
    }
    
    $addfiles = count($files);
    $sql2 = "insert into items (siteId, showTime, creationTime, creatorName, location, status) values";
    foreach($files as $file) {
      $sql2 .= "('$siteId', now(), now(), 'itemsTableMaint', '$file', 'new'),"; 
    }
    $sql2 = rtrim($sql2, ',');
    $S->query($sql2);
    $addfiles = "<p>We added $addfiles items to the 'items' table for site: $siteId.</p>\n";
  }
  
  if($ids = join(",", $_POST['ids'])) {
    $sql1 = "delete from items where itemId in ($ids)";
    $deltable = $S->query($sql1);
    $deltable = "<p>We deleted $deltable items from the 'items' table.</p>\n";
  }
  
  if($files = $_POST['remove']) {
    $deffiles = count($files);
    foreach($files as $file) {
      unlink($file);
    }
    $delfiles = "<p>We deleted $delfiles items form the 'content' direcotry</p>\n";
  }
  
  $ids = $ids ? "<p>The following items were deleted: $ids.</p>" : '';
  $sql1 = $sql1 ? "<p>SQL: $sql1</p>" : '';
  $sql2 = $sql2 ? "<p>SQL: $sql2</p>" : '';
  
  $h->banner = "<h1>Items Posted</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);

  echo <<<EOF
$top
$deltable
$ids
$delfiles
$addfiles
$sql1
$sql2
$footer
EOF;
  exit();
}

// Do database maintenance. Specifically look at the 'items' table and make sure each item has a
// correcponding image file.

$sql = "select itemId, location, siteId from items";
$S->query($sql);

$list = "";

// This array has every entry from the items table

$items = array(); // BLP 2014-04-30 -- instead of reading the database again later

while(list($id, $loc, $siteId) = $S->fetchrow("num")) {
  $items[$loc] = $id; // BLP 2014-04-30 -- 
  
  if(!file_exists($loc)) {
    // Image file does not exists
    $list .= "<li id='$id'>itemId=$id, siteId=$siteId: ".
             "<input type='checkbox' name='ids[]' checked value='$id'>$loc</li>\n";
  }
}

$options = "<option value=''>Select SiteId</option>\n";

$S->query("select siteId from sites");

while(list($siteId) = $S->fetchrow('num')) {
  $options .= "<option>$siteId</option>\n";
}

// Now look for files that have no entries in the database
// Get an array of every image in the content directory

$files = glob("content/*");
$missing = "";

foreach($files as $file) {
  // Now use the new $items array to see if there is an entry for this file.
  //echo("file: $file, items: {$items[$file]}<br>");
  if(!isset($items[$file])) {
    // echo("file: $file<br>");
    // File was not in the database
    $missing .= <<<EOF
<tr><td>Remove: <input type='checkbox' name='remove[]' value='$file' checked>
add to database:<input type='checkbox' name='add[]' value='$file'>
</td><td>
  $file<br>
<img src="$file" width="200">
</td></tr>

EOF;
  }
}

if(!$list && !$missing) {
  $h->banner = "<h1>No Items Table Problems</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  echo $top . $footer;
  exit();
}

// Naked database entry with no photo

if($list) {
  $list = <<<EOF
<p>The following 'items' table entries have no image file associated. To remove/keep these
files check or uncheck the box by the name. By default all are checked.</p>
<ul>
$list
</ul>
EOF;
}

// Naked file with no database entry

if($missing) {
  $list .= <<<EOF
<p>The following files in the 'content' directory have no 'items' table entry. To remove/keep these
items check or uncheck the box by the name. By default all are checked.</p>
<table border="1">
$missing
</table>
<select id="getsiteid" name="siteid">
$options
</select>

EOF;
}

$h->extra = <<<EOF
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script>
jQuery(document).ready(function($) {
  $("#getsiteid").hide();

  // Only remove or keep
  $("table").on("change", "input:checkbox", function(e) {
    $("#getsiteid").show();
    var x = $(this).prop("checked");
    if(x) {
      var s = $(this).siblings();
      $(s[0]).prop("checked", false);
    }
  });
});
</script>

EOF;

$h->banner = "<h1>Items Maint</1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form method="post">
$list
<input type="submit" value="Submit">
<input type="hidden" name="page" value="post">
</form>
$footer
EOF;
