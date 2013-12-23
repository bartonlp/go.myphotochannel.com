<?php
// Add or Edit an Ads Account
// Last update June 8, 2013
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

$h->title = "Ads Account Admin";

if($_POST['page'] == 'post') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $co = $_POST['company'];
  $adId = $_POST['adId'];
  if($adId) {
    // Update
    $sql = "update adsAccount set adContactName='$name', adContactEmail='$email', adCompany='$co' ".
           "where adId='$adId'";
  } else {
    $sql = "insert into adsAccount (adContactName, adContactEmail, adCompany) " .
           "values('$name', '$email', '$co')";
  }
  $S->query($sql);
  $h->banner = "$banner\n<h1>Posted</h1></div><hr>\n";
  list($top, $footer) = $S->getPageTopBottom($h, "<hr>");
  echo "$top $footer";
  exit();
}

$S->h = $h;

if($_POST['page'] == 'edit') {
  doForm('edit', $S);
  exit();
}

// Must be after posts because the post does not clear the $_GET???

if($_GET['page'] == 'new') {
  doForm('new', $S);
  exit();  
}

function doForm($type, $S) {
  if($type == 'edit') {
    $S->h->banner = "$S->banner<h1>Edit Account</h1>";
    $S->query("select * from adsAccount where adId='{$_POST['account']}'");
    list($adId, $name, $email, $company) = $S->fetchrow('num');
  } else {
    $S->h->banner = "$S->banner<h1>Add New Account</h1>";
  }

  list($top, $footer) = $S->getPageTopBottom($S->h, "<hr>");

  echo <<<EOF
$top
<form action="" method="post">
Contact Name: <input type="text" name="name" value="$name"/><br>
Email: <input type="text" name="email" value="$email"/><br>
Company: <input type="text" name="company" value="$company"/><br>
<input type="submit"/>
<input type="hidden" name="page" value="post"/>
<input type="hidden" name="adId" value="$adId"/>
</form>
$footer
EOF;
}

// Initial page
// Select an ads account to edit or create a new account

$h->banner = "$S->banner<h1>Ads Account Admin</h1>";
list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

if($S->query("select * from adsAccount")) {
  $opt = '';
  while(list($adId, $name, $email, $company) = $S->fetchrow('num')) {
    $opt .= "<option value='$adId'>$name: $company</option>\n";
  }
}

echo <<<EOF
$top
<form action="" method="post">
Select an account to edit: <select name="account">
$opt
</select>
<br>
<input type="submit"/>
<input type="hidden" name="page" value="edit"/>
</form>
<a href="$S->self?page=new&debug={$_GET['debug']}">Create New Account</a>
$footer
EOF;

?>