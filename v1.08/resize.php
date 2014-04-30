#! /usr/bin/php6 -q
<?php
// BLP 2014-01-21 -- We had two photos that were not found. It looks like they had be rotated but
// the photo was never produced (xxxx.1.jpg). If we have unfound photos we mark them inactive and
// continue. We also now only process resized=no if it is also active.
// BLP 2014-01-15 -- add database lost connection retries.   
// BLP 2014-01-11 -- Add item counter. We didn't ask for only images (type='image'). Added
// SITE_ROOT to filenames. Used pathinfo() to construct the $destfile and force '.jpg' as extension.
// BLP 2014-01-10 -- New CRON program to run occasinally (like once an hour etc) to resize any
// image that have the resized field in the items table set to no.

#$debug = true;

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

function getversion($path) {
  $name = realpath("$path");
  $version = preg_replace('/^.*?(v\d+\.\d+).*$/', "$1", $name);
  return $version;
}

$version = getversion(getcwd());

date_default_timezone_set("America/Denver");

echo "==========================\n";
echo "Resize: version $version\n";
echo date("Y-m-d H:i T") . "\n--------------------------\n";
$starttime = time();

try {
  // BLP 2014-01-21 -- add status='active'
  $S->query("select itemId, location, siteId from items where resized='no' ".
            "and type='image'");
  
} catch(Exception $e) {
  unset($S);
  $S = new Database($GLOBALS['dbinfo']);
  echo "RETRY: $sql\n";
  try {
    $S->query($sql); // try same sql again
  } catch(Exception $e) {
    echo "Tried retry unset and new Database but still got error. Error: ".$e->getCode()."\n";
    exit();
  }
}

$r = $S->getResult();

$itemCnt = 0;

while(list($itemId, $location, $siteId) = $S->fetchrow($r, 'num')) {
  $ar = pathinfo($location);
  // Build the destination file name. Make sure it is a 'jpg' regardless of what the original was.
  
  $destfile = SITE_ROOT . "/" . $ar['dirname'] . "/" . $ar['filename'] . ".jpg";
  $location = SITE_ROOT. "/$location"; // Add root to the filename

  // Make sure the file exists
  
  if(!file_exists($location)) {
    // BLP 2014-01-21 -- If we can't find the photo mark the database item as inactive and press on.
    echo "ERROR ($siteId): file $location does not exist, DELETE.\n";
    $S->query("delete from items where itemId=$itemId");
    continue;
  }

  // Resize it
  
  if(resizeImage($location, $destfile) === false) {
    // ERROR
    echo "ResizeImage Error ($siteId): $location\n";
    // BLP 2014-01-21 -- If we got an error mark the item inactive and press on.
    $S->query("update items set status='inactive' where itemId=$itemId");
    continue;
  }

  // Update the database table

  $destfile = "content/" .basename($destfile);

  try {
    $S->query("update items set resized='yes', location='$destfile' where itemId='$itemId'");
  } catch(Exception $e) {
    unset($S);
    $S = new Database($GLOBALS['dbinfo']);
    echo "RETRY: $sql\n";
    try {
      $S->query($sql); // try same sql again
    } catch(Exception $e) {
      echo "Tried retry unset and new Database but still got error. Error: ".$e->getCode()."\n";
      exit();
    }
  }

  echo "Image $siteId, $itemId, $location Resized\n";
  ++$itemCnt;
}

// If we processed anything tell us about it

if($itemCnt) {
  $time = time() - $starttime;

  echo date("Y-m-d H:i T") . "-- processed $itemCnt photos in $time sec. DONE\n--------------------------\n";
}

exit();

// resize the image file
// @param string, $filename: path+filename of source
// @param string, $destfile: path+filename of destination
// @return bool, true if OK false if failure.

function resizeImage($filename, $destfile) {
  // get an image for the original source file: jpeg, gif, png

  echo "Destination file name: " . basename($destfile) . "\n";
  
  $source = open_image($filename); 

  if($source === false) {
    echo "ERROR: open_image($filename) FALSE\n";
    return false;
  }

  // The original width and height of the image
  // returns an array 0=width, 1=height, 2=IMAGETYPE_XXX, 3=string 'height="yyy" width="xxx"',
  // mime=the-mime-type like 'image/jpg' etc.
  
  list($width, $height) = getimagesize($filename);
  $beforeSize = $width * $height;

  if($GLOBALS['debug']) echo "size: $beforeSize\n";
  
  // Check to see how big the image is. If it is more than 1/2 meg then scale it down.
  
  if(($width * $height) > 500000) {
    $w = 600/($height/$width);
    $h = $w*$height/$width;

    // create a new image to use for scaling
    
    $thumb = imagecreatetruecolor($w, $h);

    if($GLOBALS['debug']) echo "after imagecreatetruecolor\n";
    
    // Resize

    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $w, $h, $width, $height);

    // regardless of what type the original image was (gif, png or jpeg) we always output a jpg
    // image. This prevents animated gif's which could contain inappropriate material that might be
    // hard to detect.

    if($GLOBALS['debug']) echo "before imagejpeg thumb\n";
        
    imagejpeg($thumb, $destfile);

    imagedestroy($thumb);
    imagedestroy($source);
  } else {
    if($GLOBALS['debug']) echo "before imagespeg source\n";
    
    imagejpeg($source, $destfile);
    imagedestroy($source);
  }

  list($width, $height) = getimagesize($destfile);
  $afterSize = $width * $height;

  echo "Size before: $beforeSize, size after: $afterSize\n";
  
  if($GLOBALS['debug']) echo "end of resizeimage\n";
  
  return true;
}

// Helper for resizeImage();

function open_image($file) {
  //detect type and process accordinally

  $size = getimagesize($file);

  echo "open_image: $file: $size[0]x$size[1], mime:{$size['mime']}\n";
  
  switch($size["mime"]){
    case "image/jpeg":
      $im = imagecreatefromjpeg($file); //jpeg file
      break;
    case "image/gif":
      $im = imagecreatefromgif($file); //gif file
      break;
    case "image/png":
      $im = imagecreatefrompng($file); //png file
      break;
    default:
      $im = false;
      break;
  }
  if($GLOBALS['debug']) echo "end open_image: $im\n";
  return $im;
}

?>
