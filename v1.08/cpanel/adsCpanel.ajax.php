<?php
// Last modified June 8, 2013   
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

// Ajax for the control panel (cpanel.php) and all sub-memues. Each sub-menue is in its own file
// like cpanel.xxx.php or cpanel.xxx.js etc.
// All javaScript file that do Ajax have a variable declaired at the start of the file 'var
// ajaxfile = 'cpanel-php/cpanel.ajax.php'

// During development for debug

Error::setDevelopment(true);
Error::setNoEmailErrs(true);

$S = new Database($dbinfo);
$S->contentprefix = SITE_ROOT . "/";

// Don't let anyone use this from outside our domain

$referer = $_SERVER['HTTP_REFERER'];
$d = preg_replace("~http://www\.~", '', SITE_DOMAIN); // SITE_DOMAIN defined in .sitemap.php

if(!preg_match("~$d~", $referer)) {
  if($referer) echo "referer=$referer<br/>";

  $errorhdr = <<<EOF
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta name="robots" content="noindex">
</head>
EOF;

  echo <<<EOL
$errorhdr
<body>
<h1>This page can only be accessed indirectly from this domain.</h1>
<p>Please return to our <a href='/'>home page</a> link.</p>
</body>
</html>
EOL;

  exit;
}
   
// **************
// Ajax functions
// **************

date_default_timezone_set('America/New_York'); // The 1&1 ISP is in NY

// ************************************************************
// Ajax, doSql
// Do general SQL task
// $_POST['sql'] is a sql statment to execute.
// If the statement is a 'select' we return the result rows.
// If 'insert' or 'update' we return the number of rows affected
// *************************************************************

if($_POST['page'] == 'doSql') {
  $sql = $_POST['sql'];
  if($sql == '') {
    echo "NO SQL"; exit();
  }

  //echo $sql;

  $n = $S->query($sql);
  if(strpos($sql, 'select') !== false) {
    while($row = $S->fetchrow('assoc')) {
      $rows[] = $row;
    }
    echo json_encode(array('num'=>$n, 'rows'=>$rows));
    exit();
  }
  echo json_encode($n);
  exit();
}

// ****************
// Ajax, itemUpdate
// ****************

if($_POST['page'] == 'itemsUpdate') {
  extract($_POST); 

  $touch = ($touch == 'yes') ? $touch = ", creationTime=now()" : $touch = '';
  
  $sql = "update ads set status='$status', description='$desc', ".
         "duration='$dur'$touch where itemId='$id'";

  echo("$sql\n");
  echo "n=" .$S->query($sql);
  exit();
}
      
// *************
// Ajax, expunge
// *************

if($_POST['page'] == 'expunge') {
  $siteId = $_POST['siteId'];
  $id = $_POST['itemId'];

  $sql = "select itemId, location from items where itemId='$id' and siteId='$siteId'";

  if($S->query($sql) == 0) {
    echo "<h2>Nothing to delete</h2>";
    exit();
  }

  $result = $S->getResult(); // get the result because we do another sql statement inside the loop.

  list($id, $loc) = $S->fetchrow($result, 'num');
  echo "unlink: {$S->contentprefix}$log\n";
  unlink("{$S->contentprefix}$loc"); // delete the underlying file.
  $sql = "delete from items where itemId='$id'"; // remove the table entry.
  echo "$sql<br>\n";
  $S->query($sql);
  
  echo "OK item $id expunged";
  exit();
}

// **************************************************
// Ajax: getItems
// Get Items from the 'items' table based on category
// Returns the <table ....> to be displayed.
// **************************************************

if($_POST['page'] == 'getItems') {
  // Option is 'category'

  $S->getStatus = $_POST['status'];
  $S->limit = $_POST['limit'];
  $S->startId = $_POST['startId'];
  
  echo getItemsTable($S);
  exit();
}

// *************
// Ajax, getItem
// Get a single item from the items table and return the table row
// *************

if($_POST['page'] == 'getItem') {
  $S->itemId = $_POST['itemId'];

  $sql = "select itemId, adId, creationTime, description, location, " .
         "status, type, duration, transition, effect from ads where itemId='$S->itemId'";

  echo getItemRow($S, $sql);
  exit();
}

// ************
// Ajax, rotate
// ************

if($_POST['page'] = 'rotate') {
  // Get input arguments, itemId, and image.

  $id = $_POST['itemId'];
  $image = $_POST['image'];

  // $image will be the full Url "http://host/tomsproject/content/nnn.jpg" for example

  $finfo = pathinfo($image); // get assoc array: dirname, basename, extension, filename
  $ext = $finfo['extension'];
  $base = $finfo['filename'];

  // Make the full path and name

  $image = "content/$base.$ext";

  // look at the incoming file name. Does it have a '.N.jpg". If yes then extract N. Increment N and
  // modulus 4. If modulus 4 is zero then remove the '.N' and return the filename to xxx.jpg. Other
  // wise create the filename xxx.(N+1).jpg.
  // Update the database with the new name. Rename the file on disk to the new name. Then return the
  // new name to the client.

  if(preg_match("~(.*?)\.(\d)\.{$ext}$~", $image, $m)) {
    // $1 will be "content/nnn", $2 will be a number 1-3
    // add one to $1
    $newnum = $m[2] + 1;

    // the numbers are 1, 2, 3. If newnum == 4 then reverts back to just the original name nnn.jpg
    // for example.
    if(($newnum % 4) == 0) {
      // just nnn.ext
      $new = "$m[1].$ext";
    } else {
      // nnn.n.ext
      $new = "$m[1].$newnum.$ext";
    }
  } else {
    // No '.N' so this is ".1"
    $new = "content/$base.1.$ext";
  }

  $sql = "update items set location='$new', description='$new' where itemId='$id'";

  $S->query($sql);

  // Load the image

  $image = "{$S->contentprefix}$image";
  
  list($img, $type) = open_image($image);

  // Rotate
  $rotate = imagerotate($img, 90, 0);

  // Output

  $newloc = "{$S->contentprefix}$new";

  output_image($rotate, $newloc, $type);
  // Return the new name.

  echo $new;
  exit();
}

// ****************** End Ajax ********************

// *********************** Start Functions *************************

// *******************
// getItemRow()
// Get a single row from the items table.
// $q is the query for the row: via ajax GetItem
//      $sql = "select itemId, adId, creationTime, description, location, " .
//             "status, type, duration, transition, effect from ads where itemId='$S->itemId'";
// or from getItemsTable($S):
//       $sql = "select itemId, adId, creationTime, description, location, " .
//             "status, type, duration, transition, effect from ads {$status}{$startId} ".
//             "order by creationTime desc{$limit}";
// ******************

function getItemRow($S, $q) {
  $n = $S->query($q);

  if(!$n) {
    echo "<table border='1'><tr><th style='color: red;'>Table Empty</th></tr></table>";
    exit();
  }

  $result = $S->getResult();
    
  while(list($id, $adId, $time, $desc, $loc, $status, $type, $dur, $trans, $effect) =
    $S->fetchrow($result, 'num')) {

    $S->query("select adContactName, adCompany from adsAccount where adId=$adId");
    list($adContactName, $adCompany) = $S->fetchrow('num');

    // strip off any query
    $l = preg_replace("/?.*/", '', $loc);

    if($type != 'html' && !file_exists("{$S->contentprefix}$l")) {
      $sql = "delete from ads where location='{$S->contentprefix}$l'";
      echo "missing: $l<br>$sql<br>";
      //      $S->query($sql);
      continue;
    }

    $S->startId = "{$time}{$id}";
    
    $dur /= 1000;

    $size = '';
    if($type != 'html') {
      $size = "Size: " .number_format(@filesize("{$S->contentprefix}$loc"));
    }

    $utime = strtotime($time);
    $typeOfTime = "";

    // Status radio buttons
       
    $stat = "<table class='statustbl'>\n";
       
    foreach(array('active', 'inactive', 'delete', 'new') as $v) {
      $checked = ($v == $status) ? " checked" : "";
      $stat .= <<<EOF
<tr>
<td>
<div data-role="fieldcontain">
<label for='$id-$v'>$v</label>
<input id='$id-$v' class='status' data-mini="true" type='radio' value='$v' name='$id'$checked />
</div>
</td>
</tr>

EOF;
    }
    $stat .= "</table>\n";

    // Make each row of the items table.

    $location = SITE_DOMAIN . "/$loc"; // This is http://...

    switch($type) {
      case 'image':  
        $item = "<img class='typeimage' data-id='$id' src='$location'>\n";
        break;
      case 'html':
        $item = "<div class='typehtml' style='border: 1px solid white' data-id='$id'>$loc</div>\n";
        break;
      case 'filehtml':
        $dd = file_get_contents("{$S->contentprefix}$loc");
        $item = "<div class='typefilehtml' data-id='$id'>$dd</div>\n";
        break;
    }
       
    $tbl .= <<<EOF
<tr>
<td>

<!-- The frame table holds all the info for one photo -->
<table class='frame'>
<!-- The head section has the image and the non-editable information side by side. -->
<thead>
<tr>
<td class="itemimage">
$item
</td>
<td>
AdId: $adId<br>
Advertiser: $adCompany<br>
Contact: $adContactName<br>
Image Id: <span id="$id-imageid">&nbsp;$id</span><br>$size

</td>
</tr>
</thead>

<!-- tbody of the frame table has all of the changeable data for the image -->
<tbody>
<tr>
<td class="leftside">

<div data-role="controlgroup" data-type="horizontal">
<label for="$id-touch">Make Current</label>
<input id="$id-touch" type="radio" name="touch" data-mini="true" data-inline="true">
</div>
<hr>
<label for="$id-desc">Description</label>
<input id="$id-desc" type="text" name="desc" value="$desc" data-mini="true"/>

<label for="$id-duration">Duration</label>
<input id="$id-duration" class='dur' type="range" min="0" max="60" name="dur" value="$dur" data-mini="true"/>
</td>

<td class="statusgroup">
$stat
</td>

</tr>

</tbody>
</table>
</td>
</tr>

EOF;
  }
  $S->numRows = $n;
  return $tbl;
}

// *********************
// Get the 'items' table
// @param object $S. Has all the stuff we need
//   $S->category is the category requested (can be ALL)
//   $S->status the status (active, inactive etc)
//   $S->limit the number of items to get
//   $S->startId is the datetime+itemId. Used to get the next batch of items
//   All of the above can in fact be empty.
// *********************

function getItemsTable($S) {
  $status = '';
  $limit = '';
  $startId = '';
  $tbl = '';
  
  if($S->getStatus) {
    $status = " where status='$S->getStatus' && type not in('video','youtube')";
  }
  if($S->limit) {
    $limit = " limit $S->limit";
  }
  if($S->startId) {
    $startId = " && concat(creationTime,itemId) < '$S->startId'";
  }

  $sql = "select count(*) from ads {$status}{$startId} " .
         "order by creationTime desc";

  $S->query($sql);
  
  list($maxCount) = $S->fetchrow('num');
  
  $sql = "select itemId, adId, creationTime, description, location, " .
         "status, type, duration, transition, effect from ads {$status}{$startId} ".
         "order by creationTime desc{$limit}";

  $tbl = getItemRow($S, $sql);
  
  $nextSet = "<p>Number of items retrieved: $S->numRows of $maxCount</p>\n";

  if($n < $maxCount) {
    $nextSet .= "<button id='getNextSet' value='$S->startId'>Get Next $S->limit Items</button>";
  }

  // Finally make the finished table with nextSet header.
  
  $tbl = <<<EOF
$nextSet
<table id="itemsTable" name='items' border="1">
<tbody>
$tbl
</tbody>
</table>

EOF;

  return $tbl;
}

function output_image($rotate, $image, $type) {
  //detect type and process accordinally

  //$type = exif_imagetype($image);
  /*
  1 	IMAGETYPE_GIF
  2 	IMAGETYPE_JPEG
  3 	IMAGETYPE_PNG
  4 	IMAGETYPE_SWF
  5 	IMAGETYPE_PSD
  6 	IMAGETYPE_BMP
  7 	IMAGETYPE_TIFF_II (intel byte order)
  8 	IMAGETYPE_TIFF_MM (motorola byte order)
  9 	IMAGETYPE_JPC
  10 	IMAGETYPE_JP2
  11 	IMAGETYPE_JPX
  12 	IMAGETYPE_JB2
  13 	IMAGETYPE_SWC
  14 	IMAGETYPE_IFF
  15 	IMAGETYPE_WBMP
  16 	IMAGETYPE_XBM
  17 	IMAGETYPE_ICO
  */

  switch($type) {
    case IMAGETYPE_JPEG:
      imagejpeg($rotate, $image);
      break;
    case IMAGETYPE_GIF:
      imagegif($rotate, $image);
      break;
    case IMAGETYPE_PNG:
      imagepng($rotate, $image);
      break;
  }
}  

function open_image($file) {
  //detect type and process accordinally

  $type = exif_imagetype($file);

  switch($type){
    case IMAGETYPE_JPEG:
      $im = imagecreatefromjpeg($file); //jpeg file
      break;
    case IMAGETYPE_GIF:
      $im = imagecreatefromgif($file); //gif file
      break;
    case IMAGETYPE_PNG:
      $im = imagecreatefrompng($file); //png file
      break;
  }
  return array($im, $type);
}  

?>