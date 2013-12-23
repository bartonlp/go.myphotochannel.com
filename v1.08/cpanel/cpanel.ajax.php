<?php
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
$S->contentprefix = TOP . "/";

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

// Ajax to do startup

if($_GET['page'] == 'startup') {
  $siteId = $_GET['siteId'];
  $unit = $_GET['unit'];
  $version = "cpanel: " . $_GET['version'];
  
  $sql = "insert into startup (siteId, unit, version) values('$siteId', '$unit', '$version')";

  $S->query($sql);
  $id = $S->getLastInsertId();
  echo json_encode(array('id'=>$id));

  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';
  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("slideshow", "startup",
                   array('msg'=>"startup $unit",
                         'siteId'=>$siteId,
                         'ip'=>$_SERVER['REMOTE_ADDR'],
                         'agent'=>$_SERVER['HTTP_USER_AGENT']));
  exit();
}

// Ajax startup-update

if($_GET['page'] == 'startup-update') {
  $siteId = $_GET['siteid'];
  $unit = $_GET['unit'];
  $startupId = $_GET['startupId'];

  $sql = "update startup set status='open', lasttime=now() where id='$startupId'";
  $S->query($sql);
  echo $sql;

  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';
  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("slideshow", "startup-update",
                   array('msg'=>"startup-update $unit",
                         'siteId'=>$siteId,
                         'ip'=>$_SERVER['REMOTE_ADDR'],
                         'agent'=>$_SERVER['HTTP_USER_AGENT']));


  exit();
}

// Ajax to do unload and update startup

if($_GET['page'] == 'unload') {
  $siteId = $_GET['siteId'];
  $startupId = $_GET['startupId'];
  $unit = $_GET['unit'];
  
  $sql = "update startup set status='closed', lasttime=now() where id='$startupId'";
  $S->query($sql);
  echo $sql;

  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';
  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("slideshow", "unload",
                   array('msg'=>"unload $unit",
                         'siteId'=>$siteId,
                         'ip'=>$_SERVER['REMOTE_ADDR'],
                         'agent'=>$_SERVER['HTTP_USER_AGENT']));


  exit();
}

// **********************
// Ajax, saveTextAnnounce
// **********************

if($_POST['page'] == 'saveTextAnnounce') {
  $text = $_POST['text'];
  $siteId = $_POST['siteId'];

  $sql = "insert into items (siteId, category, creatorName, description, status, type, location) ".
         "values('$siteId', 'announce', 'saveTextAnnounce', 'Local Message', 'active', 'html', '$text')";

  $S->query($sql);

  echo "OK, DONE";
  exit();
}

// **************************
// Ajax, saveTextFileAnnounce
// **************************

if($_POST['page'] == 'saveTextFileAnnounce') {
  $text = $_POST['text'];
  $siteId = $_POST['siteId'];

  $sql = "insert into items (siteId, category, creatorName, description, status, type) ".
         "values('$siteId', 'announce', 'saveTextFileAnnounce', 'File Message', 'active', 'filehtml')";

  $S->query($sql);

  // now update the 'location' with the name of the new photo which will be 'lastInsertId.<ext>'

  $newid = $S->getLastInsertId();
  $sql = "update items set location='content/$newid.txt' " .
         "where itemId='$newid'";
  
  $S->query($sql);

  // write the new file to the content directory
  file_put_contents("{$S->contentprefix}content/$newid.txt", $text);
}

// **********************************************
// Ajax, saveImageAnnounce
// Insert the Announcement into the 'items' table
// Given a data uri image.
// **********************************************

if($_POST['page'] == 'saveImageAnnounce') {
  $attach = $_POST['image'];
  $siteId = $_POST['siteId'];
  // Strip off the dataUri suffix.
  $attach = preg_replace("~data:image/png;base64,~", '', $attach);
  // Turn the base64 back into binary
  $attach = base64_decode($attach);
  // Add the info to the database

  $sql = "insert into items (siteId, category, creatorName, description, status) ".
         "values('$siteId', 'announce', 'saveImageAnnounce', 'announcement', 'active')";

  $S->query($sql);

  // now update the 'location' with the name of the new photo which will be 'lastInsertId.<ext>'

  $newid = $S->getLastInsertId();
  $sql = "update items set description='$newid.png', location='content/$newid.png' " .
         "where itemId='$newid'";
  
  $S->query($sql);
  
  // 3) move the photo to the content directory
  // We shouldn't have any permission problems as this will be running as our user not as www-data
  // like the browser does.

  // write the new file to the content directory
  file_put_contents("{$S->contentprefix}content/$newid.png", $attach);
  echo "OK, DONE";
  exit();
}

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

// ***********************************************************************************************
// Ajax, approvephotos
// Get New Photos to Approve or Disapprove
// Returns the information to render which is the
// <img> two radio buttons with Approve and Disapprove (the radio button name is 'approve-$id' or
// 'disapprove-$id'. $id is the image id in the items table). The 'value' is 'yes' or 'no'.
// The whole thing is wrapped in a table with a submit button.
// ***********************************************************************************************

if($_POST['page'] == 'approvephotos') {
  $siteId = $_POST['siteId'];

  $sql = "select featureExt from appinfo where siteId='$siteId'";
  $S->query($sql);
  list($ext) = $S->fetchrow('num');
  
  $sql = "select itemId, location, creatorName from items where status='new' and siteId='$siteId'";
  if($S->query($sql) == 0) {
    echo "No New Photos Found";
    exit();
  }

  $tbl = '';
  
  while(list($id, $loc, $name) = $S->fetchrow('num')) {
    $location = SITE_DOMAIN . "/$loc"; // This is http://...

    $email = "";
    
    if(preg_match("/(?:\w*\s+\w*\s+(?:&lt;)?)?(\w+@\w+\.\w+)(?:&gt;)?/", $name, $m)) {
      $email = $m[1];
    }

    $tbl .= <<<EOF
<tr data-ext='$ext' data-email='$email'>
<td><img id='$id' src='$location' width='300'></td>
<td class='tdapproveinput'>
Approve <input type='radio' class="approveyes" name='approve-$id' value='yes'>
Disapprove <input type='radio' class="approveno" name='approve-$id' value='no'>
</td>
</tr>

EOF;
  }

$tbl = <<<EOF
<table id="approvephotostable" border="1">
$tbl
</table>
EOF;

  echo $tbl;
  exit();
}

// *******************
// Ajax, getThumbnails
// *******************

if($_POST['page'] == "getThumbnails") {
  $cat = $_POST['category'];
  $status = $_POST['status'];
  $siteId = $_POST['siteId'];
  
  // control panel admin that displays thumbnail images to admin.
  // Get all of the photos into a big array of arrays of 100.

  $sql = "select itemId, location, type from items where category='$cat' and status='$status' ".
         "and siteId='$siteId' ".
         "order by showTime desc";

  $n = $S->query($sql);
  $x = '';
  $page;
  $j = 0;
  $i = 0;
  
  while(list($id, $loc, $type) = $S->fetchrow('num')) {
    $location = SITE_DOMAIN . "/$loc"; // This is http://...
    switch($type) {
      case 'image':
        $x .= "<img name='$id' src='$location'> ";
        break;
      case 'html':
        $x .= "<div class='htmlitem' name='$id'>" . escapeltgt($loc) . "</div>";
        break;
      case 'filehtml':
        $x .= "<div class='htmlitem' name='$id'>".
              escapeltgt(file_get_contents("{$S->contentprefix}$loc")). "</div>";
        break;
    }
    if(++$i >= 100) {
      $page[$j++] = $x;
      $x = '';
      $i = 0;
    }
  }

  if($i) $page[$j] = $x;
  
  // Make it look like javaScript

  $page = json_encode($page);
  echo $page;
  exit();
}

// ****************
// Ajax, itemUpdate
// ****************

if($_POST['page'] == 'itemsUpdate') {
  extract($_POST); // id, siteId, desc, dur, touch, status, cat
  // siteId is passed in in $_POST

  $touch = ($touch == 'yes') ? $touch = ", showTime=now()" : $touch = '';
  
  $sql = "update items set category='$cat', status='$status', description='$desc', ".
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

  $sql = "select itemId, location from items where itemId='$id'";

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

  $S->category = $_POST['category']; // category my be blank in which case all categories are included.
  $S->getStatus = $_POST['status'];
  $S->siteId = $_POST['siteId'];
  $S->limit = $_POST['limit'];
  $S->startId = $_POST['startId'];
  
  echo getItemsTable($S);
  exit();
}

// *************
// Ajax, getItem
// *************

if($_POST['page'] == 'getItem') {
  $S->itemId = $_POST['itemId'];
  $S->siteId = $_POST['siteId'];
  $sql = "select itemId, category, showTime, creationTime, creatorName, description, location, " .
         "status, type, duration from items where itemId='$S->itemId'";

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

function getItemRow($S, $q) {
  $sql = "select lifeOfFeature, whenPhotoAged from appinfo where siteId='$S->siteId'";
  $S->query($sql);

  list($life, $aged) = $S->fetchrow('num');

  $sql = "select date_sub(now(), interval $life minute), ".
         "date_sub(now(), interval $aged)";

  $S->query($sql);
  list($life, $aged) = $S->fetchrow('num');

  $life = strtotime($life);
  $aged = strtotime($aged);

  $n = $S->query($q);

  if(!$n) {
    echo "<table border='1'><tr><th style='color: red;'>Table Empty</th></tr></table>";
    exit();
  }

  $result = $S->getResult();

  // $time is showTime
  
  while(list($id, $cat, $time, $creationTime, $creator, $desc, $loc, $status, $type, $dur) =
    $S->fetchrow($result, 'num')) {

    // Just use the email address from creator
    if(preg_match("/ &lt;(.*?)&gt;/", $creator, $m)) {
      $creator = $m[1];
    }

    if($type != 'html' && !file_exists("{$S->contentprefix}$loc")) {
      $sql = "delete from items where location='{$S->contentprefix}$loc'";
      echo "missing: $loc<br>$sql<br>";
      //      $S->query($sql);
      continue;
    }

    // showTime and id
    
    $S->startId = "{$time}{$id}";
    
    $dur /= 1000;

    if($type != 'html') {
      $size = number_format(@filesize("{$S->contentprefix}$loc"));
      //$fileType = getimagesize("{$S->contentprefix}$loc");
      //$fileType = $fileType['mime'];
    }

    $utime = strtotime($time); // showTime
    $typeOfTime = "";

    // Only photos have a 'Type'

    if($cat == 'photo') {
      if($utime > $life) {
        $typeOfTime = "feature";
      } elseif($utime < $aged) {
        $typeOfTime = "aged";
      } else {
        $typeOfTime = 'recent';
      }
      $typeOfTime = "<label for='$id-mode'>Type: </label><span id='$id-mode'>$typeOfTime</span>";
    }

    // Options for select.
       
    $options = "<option value=''>Select</option>\n";

    foreach(array('photo','announce','brand','product','info') as $v) {
      $optcat = ($cat == $v) ? ' selected' : '';
      $options .= "<option value='$v'$optcat>$v</option>\n";
    }

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
Image Id <span id="$id-imageid">&nbsp;$id</span><br>Size: $size

</td>
</tr>
<tr>
<td colspan="2">
From: $creator<br>
</td>
</tr>
</thead>

<!-- tbody of the frame table has all of the changeable data for the image -->
<tbody>
<tr>
<td class="leftside">


<div data-role="controlgroup" data-type="horizontal">
<label for="$id-touch">Make Feature</label>
<input id="$id-touch" type="radio" name="touch" data-mini="true" data-inline="true">
</div>

<label for="$id-time-type">STime: </label>
<span id="$id-time-type">$time<br>CTime: $creationTime<br>$typeOfTime</span>
<hr>
<label for="$id-category">Category</label>
<select id="$id-category" name="category" data-mini="true">
$options
</select>

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
  $category = '';
  $status = '';
  $limit = '';
  $startId = '';
  $tbl = '';
  
  if($S->category) {
    $category = " category='$S->category' &&";
  }
  if($S->getStatus) {
    $status = " status='$S->getStatus' &&";
  }
  if($S->limit) {
    $limit = " limit $S->limit";
  }
  if($S->startId) {
    $startId = " concat(showTime,itemId) < '$S->startId' &&";
  }

  $sql = "select count(category) from items where{$category}{$status}{$startId} siteId='$S->siteId'" .
         "order by showTime desc";

  $S->query($sql);
  
  list($maxCount) = $S->fetchrow('num');
  
  $sql = "select itemId, category, showTime, creationTime, creatorName, description, location, " .
         "status, type, duration from items where{$category}{$status}{$startId} siteId='$S->siteId' ".
         "order by showTime desc{$limit}";

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