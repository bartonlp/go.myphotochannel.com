<?php
// *************
// Ajax, upload.
// *************

if($_POST['page'] == 'upload') {
  if(!getenv("SITELOADNAME")) {
    putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
  }
  $_site = require_once(getenv("SITELOADNAME"));
  define(DOC_ROOT, $_site->path);
  ErrorClass::setDevelopment(true);
  ErrorClass::setNoEmailErrs(true);

  $S = new Database($_site);

  $attach = $_POST['data'];
  $desc = $_POST['desc'];
  $adId = $_POST['adId'];

  // Strip off the dataUri suffix.
  $attach = preg_replace("~data:image/png;base64,~", '', $attach);
  // Turn the base64 back into binary
  $attach = base64_decode($attach);
  // Add the info to the database

  $im = imagecreatefromstring($attach);

  if(!$S->query("select adContactName, adCompany from adsAccount where adId='$adId'")) {
    echo "Can't find the adId in adsAccount";
    exit();
  }
  list($name, $company) = $S->fetchrow('num');

  // type defaults to 'image'
  $sql = "insert into ads (adId, description, status) ".
         "values('$adId', '$company', 'active')";

  //echo "$sql\n";
  $S->query($sql);

  // now update the 'location' with the name of the new photo which will be 'lastInsertId.<ext>'

  $newid = $S->getLastInsertId();
  $sql = "update ads set location='adscontent/$newid.jpg', creationTime=now() " .
         "where itemId='$newid'";

  //echo "$sql\n";  
  $S->query($sql);
  
  // 3) move the photo to the content directory
  // We shouldn't have any permission problems as this will be running as our user not as www-data
  // like the browser does.
  
  // write the new file to the content directory
  // file_put_contents("/var/www/tomsproject/content/$newid.png", $attach);

  imagejpeg($im, DOC_ROOT ."/adscontent/$newid.jpg");
  imagedestroy($im);
  
  echo "OK, $desc";
  exit();
} // End of Ajax

// *****************
// Render Start Page  
// *****************

$nopagetop = true;   
include("cpanel/cpanel.top.php");

if(!$S->superuser) {
  echo "<h1>Sorry Only for Super Users</h1>";
  exit();
}

$h->title = "Ads Upload";
$h->banner = <<<EOF
<h1>Upload Ads</h1>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>
var siteId = "$siteId";
</script>
<script src="js/uploadads.js"></script>

EOF;

list($top, $footer) = $S->getPageTopBottom($h);

$S->query("select adId, adContactName, adCompany from adsAccount");
$sel = "";
while(list($adId, $name, $co) = $S->fetchrow('num')) {
  $sel .= "<option value='$adId'>$name : $co</option>\n";
}

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
Select Advertiser: <select id="adId">
$sel
</select>
</form>
<div id="status"></div>
<hr>
$footer
EOF;
