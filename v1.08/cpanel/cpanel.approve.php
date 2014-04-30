<?php
// BLP 2014-04-30 -- if image file does not exist delete record.
// BLP 2014-04-25 -- Add resize logic here instead of doing it via a cron.

include("cpanel.top.php");

// The first thing we do when someone runs this program is check to see if any files for this site
// need to be resized.

$S->resizelog = DOC_ROOT . "/resize.log";

function getversion($path) {
  $name = realpath("$path");
  $version = preg_replace('/^.*?(v\d+\.\d+).*$/', "$1", $name);
  return $version;
}

$version = getversion(getcwd());

date_default_timezone_set("America/Denver");
file_put_contents($S->resizelog,  "==========================\n".
                  "cpanel.approve: Resize: version $version\n".
                  date("Y-m-d H:i T" . "\n") .
                  "$S->siteId\n",
                  FILE_APPEND);

$starttime = time();

// Check if there are any file that need to be resized for this site

$S->query("select itemId, location from items where resized='no' ".
          "and type='image' and siteId='$S->siteId'");

$r = $S->getResult(); // save because we do other database action within the loop

$itemCnt = 0;

while(list($itemId, $location) = $S->fetchrow($r, 'num')) {
  $ar = pathinfo($location);
  // Build the destination file name. Make sure it is a 'jpg' regardless of what the original was.
  
  $destfile = SITE_ROOT . "/" . $ar['dirname'] . "/" . $ar['filename'] . ".jpg";
  $location = SITE_ROOT. "/$location"; // Add root to the filename

  // Make sure the file exists
  
  if(!file_exists($location)) {
    // If we can't find the photo mark the database item as inactive and press on.
    file_put_contents($S->resizelog,
                      "ERROR: file $location does not exist, DELETE record.\n",
                      FILE_APPEND);
    $S->query("delete from items where itemId=$itemId");
    continue;
  }

  // Resize it
  
  if(resizeImage($location, $destfile, $S) === false) {
    // ERROR
    file_put_contents($S->resizelog, "ResizeImage Error SKIP: $location\n", FILE_APPEND);
    // BLP 2014-04-30 -- just skip for now
    //$S->query("update items set status='inactive' where itemId=$itemId");
    continue;
  }

  // Update the database table

  $destfile = "content/" .basename($destfile);

  $S->query("update items set resized='yes', location='$destfile' where itemId='$itemId'");
  
  file_put_contents($S->resizelog, "Image $itemId $location Resized\n", FILE_APPEND);
  ++$itemCnt;
}

// If we processed anything tell us about it

if($itemCnt) {
  $time = time() - $starttime;

  file_put_contents($S->resizelog,
                    date("Y-m-d H:i T") .
                    "-- processed $itemCnt photos in $time sec. DONE\n",
                    FILE_APPEND);
}

// resize the image file
// @param string, $filename: path+filename of source
// @param string, $destfile: path+filename of destination
// @return bool, true if OK false if failure.

function resizeImage($filename, $destfile, $S) {
  // get an image for the original source file: jpeg, gif, png

  file_put_contents($S->resizelog, "Destination file name: " . basename($destfile) . "\n",
                    FILE_APPEND);
  
  $source = open_image($filename); 

  if($source === false) {
    file_put_contents($S->resizelog, "ERROR: open_image($filename)\n",
                      FILE_APPEND);
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

  file_put_contents($S->resizelog, "Size before: $beforeSize, size after: $afterSize\n",
                    FILE_APPEND);
  
  return true;
}

// Helper for resizeImage();

function open_image($file) {
  //detect type and process accordinally

  $size = getimagesize($file);

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
  return $im;
}

echo <<<EOF
<!-- Approve/Disapprove New Photos -->

<div id="approvephotos-page" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-rel="panel" href="#approve-help" data-inline="true" data-mini="true">Help</a>
		<h1>Photo Approval<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" id="homejames" data-icon="home" data-iconpos="notext"></a>
	</div><!-- /header -->
	<div data-role="content" id="approvephotoscontent">
    <div height="80">&nbsp</div>
    <div id="floatingsubmit" style="position: fixed; left: 50px; top: 40px;">
      <div id="approveallnone" data-role="controlgroup" data-type="horizontal">
         <button id="approveall">Approve All</button><button id="approvenone">Disapprove All</button>
         <button id="approveclear">Clear All</button>
         <button id="approvephotosOK"  data-inline="true">Submit</button>
       </div>
    </div>
    <br>
    <div id="approvephotoshere">
    <!-- Photo <li>s go here -->
    </div>
    <div id="approvePostedOK" data-role="popup" data-theme="e">
     <p>Items Posted OK</p>
    </div>

	</div><!-- /content -->
  <div data-role="panel" id="approve-help" data-theme="b">
     <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.approve.js"></script>-->
</div><!-- /page -->

</body>
</html>
EOF;
?>