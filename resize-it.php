#! /usr/bin/php6 -q
<?php

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new Database($_site);

$S->query("select itemId, type, location from items where resized='no' and status='active'");
while(list($itemId, $type, $loc) = $S->fetchrow('num')) {
  //echo "$loc\n";
  if($type != 'image') {
    echo "$filename is not an IMAGE\n";
    exit();
  }
  $size = number_format(filesize($loc));
  list($width, $height) = getimagesize($loc);
  $beforeSize = number_format($width * $height);

  // Build the destination file name. Make sure it is a 'jpg' regardless of what the original was.

  $destfile = $loc;
  echo "filename: $loc\n";
  echo "size: $size, w*h: $beforeSize\n";

  if(resizeImage("$loc", $loc, $S) === false) {
    // ERROR
    echo "ResizeImage Error SKIP: $loc\n";
    // BLP 2014-06-03 -- mark ResizeImage error as inactive which will keep us from running into
    // these problem photos every time we do an approve pass. In most cases these are photos that
    // have a form of xxxxx. with no jpg etc. They for some reason did not get processed correctly.
    $S->query("update items set status='inactive' where itemId=$itemId");
    continue;
    //exit();
  }


  // Update the database table

  $S->query("update items set resized='yes' where itemId='$itemId'");
}
exit();

// resize the image file
// @param string, $filename: path+filename of source
// @param string, $destfile: path+filename of destination
// @return bool, true if OK false if failure.

function resizeImage($filename, $destfile, $S) {
  // get an image for the original source file: jpeg, gif, png

  $source = open_image($filename); 

  if($source === false) {
    echo "ERROR: open_image($filename)\n";
    return false;
  }  

  // The original width and height of the image
  // returns an array 0=width, 1=height, 2=IMAGETYPE_XXX, 3=string 'height="yyy" width="xxx"',
  // mime=the-mime-type like 'image/jpg' etc.
  
  list($width, $height) = getimagesize($filename);
  $beforeSize = $width * $height;

  // Check to see how big the image is. If it is more than 1/2 meg then scale it down.
  
  if(($width * $height) > 500000) {
    $w = 600/($height/$width);
    $h = $w*$height/$width;

    // create a new image to use for scaling
    
    $thumb = imagecreatetruecolor($w, $h);

    // Resize

    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $w, $h, $width, $height);

    // regardless of what type the original image was (gif, png or jpeg) we always output a jpg
    // image. This prevents animated gif's which could contain inappropriate material that might be
    // hard to detect.

    imagejpeg($thumb, $destfile);

    imagedestroy($thumb);
    imagedestroy($source);
  } else {
    imagejpeg($source, $destfile);
    imagedestroy($source);
  }

  list($width, $height) = getimagesize($destfile);
  $afterSize = $width * $height;

  echo "Size before: $beforeSize, size after: $afterSize\n";
  
  return true;
}

// Helper for resizeImage();

function open_image($file) {
  //detect type and process accordinally

  $size = getimagesize($file);

  switch($size["mime"]) {
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

  return $im;
}
