<?php

// *************
// Ajax, upload.
// *************

if($_POST['page'] == 'upload') {
  define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
  if(file_exists(TOPFILE)) {
    include(TOPFILE);
  } else throw new Exception(TOPFILE . "not found");

  $S = new Database($dbinfo);

  $attach = $_POST['data'];
  $desc = $_POST['desc'];
  $siteId = $_POST['siteId'];

  //file_put_contents("/tmp/debugblp", "UPLOAD: $siteId, " .strlen($attach)."\n");

  // Strip off the dataUri suffix.
  $attach = preg_replace("~data:image/png;base64,~", '', $attach);
  // Turn the base64 back into binary
  $attach = base64_decode($attach);
  // Add the info to the database

  $im = imagecreatefromstring($attach);
  unset($attach);
  
  $sql = "insert into items (siteId, category, showTime, creatorName, description, status) ".
         "values('$siteId', 'photo', now(), 'Upload', '$desc', 'new')";
  //file_put_contents("test", "SQL: $sql\n", FILE_APPEND);
  $S->query($sql);

  // now update the 'location' with the name of the new photo which will be 'lastInsertId.<ext>'

  $newid = $S->getLastInsertId();
  $sql = "update items set description='$newid.jpg', location='content/$newid.jpg' " .
         "where itemId='$newid'";
  //file_put_contents("test", "SQL: $sql\n", FILE_APPEND);
  $S->query($sql);
  
  // 3) move the photo to the content directory
  // We shouldn't have any permission problems as this will be running as our user not as www-data
  // like the browser does.
  
  // write the new file to the content directory

  imagejpeg($im, SITE_ROOT ."/content/$newid.jpg");
  imagedestroy($im);
  
  echo "OK, $desc";
  exit();
} // End of Ajax

// *****************
// Render Start Page  
// *****************

$nopagetop = true;   
include("cpanel/cpanel.top.php");

$h->title = "Upload Photo from Client";
$h->banner = <<<EOF
<h1>Upload Photos for $siteId</h1>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
var siteId = "$siteId";
var userId = "$userId";
</script>
<script src="js/uploadphotos.js"></script>

EOF;

$S->setBannerFile(SITE_INCLUDES."/myphotochannelbanner.i.php");

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<form name="uploadForm">
<table>
<tbody>
<tr>
<td><img id="uploadPreview" style="width: 100px; height: 100px;"
src="data:image/svg+xml,%3C%3Fxml%20version%3D%221.0%22%3F%3E%0A%3Csvg%20width%3D%22153%22%20height%3D%22153%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%0A%20%3Cg%3E%0A%20%20%3Ctitle%3ENo%20image%3C/title%3E%0A%20%20%3Crect%20id%3D%22externRect%22%20height%3D%22150%22%20width%3D%22150%22%20y%3D%221.5%22%20x%3D%221.500024%22%20stroke-width%3D%223%22%20stroke%3D%22%23666666%22%20fill%3D%22%23e1e1e1%22/%3E%0A%20%20%3Ctext%20transform%3D%22matrix%286.66667%2C%200%2C%200%2C%206.66667%2C%20-960.5%2C%20-1099.33%29%22%20xml%3Aspace%3D%22preserve%22%20text-anchor%3D%22middle%22%20font-family%3D%22Fantasy%22%20font-size%3D%2214%22%20id%3D%22questionMark%22%20y%3D%22181.249569%22%20x%3D%22155.549819%22%20stroke-width%3D%220%22%20stroke%3D%22%23666666%22%20fill%3D%22%23000000%22%3E%3F%3C/text%3E%0A%20%3C/g%3E%0A%3C/svg%3E" alt="Image preview" />
<br><span id='filename'></span></td>
<td><input id="uploadImage" type="file" multiple name="myPhoto" /></td>
</tr>
</tbody>
</table>
</form>
<div id="status"></div>
<hr>
$footer
EOF;
?>