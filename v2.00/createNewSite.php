<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new $_site->className($_site);

function notAllowed() {
  echo "<h1>Only 'super users' are allowed to create new sites. Sorry.</h1>";
  exit();
}

if($superuser = $_GET['debug']) {
  $sql = "select concat(fname, ' ', lname) from superuser where password='$superuser'";
  if(!$S->query($sql)) {
    notAllowed();
    exit();
  }
  list($name) = $S->fetchrow('num');
  $superuserName = $name;
} else {
  notAllowed();
  exit();
}

$h->title = "Create New Site";
$h->banner = <<<EOF
<h1>Create New Site</h1>
EOF;

$h->extra =<<<EOF
  <script src="http://code.jquery.com/jquery-1.8.2.js"></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// *****************************************************
// Create a new site with the information gathered below
// *****************************************************

if($_POST['page'] == "createsite") {
  foreach($_POST as $k=>$v) {
    $$k = $S->escape($v);
  }

  // Add new site to sites table
  
  $sql = "INSERT IGNORE INTO `sites` (siteId, siteCode, fname, lname, password, company, address, email, phone, emailServer, ".
         "emailUsername, emailPassword, emailPort) ".
         "VALUES ('$siteid','$siteid','$fname','$lname',".
         "'$ownerpassword','$company','$address','$email','$phone','$server','$username',".
         "'$emailpassword','$port')";
  //cout("$sql");
  $S->query($sql);

  // Add owner to the users table
  
  $sql = "INSERT IGNORE INTO `users` (fname, lname, password, email, status, emailNotify, siteId) ".
         "VALUES ('$fname','$lname','$ownerpassword','$email','member','yes','$siteid')";
  //cout("$sql");
  $S->query($sql);

  // Add new site to appinfo table

  $sql = "INSERT IGNORE INTO `appinfo` (siteId) value('$siteid')";
  //cout("$sql");
  $S->query($sql);

  // Add new site to the categories table
  
  $sql = "INSERT IGNORE INTO `categories` (siteId, category) VALUES ('$siteid', 'photo'),".
         "('$siteid','announce'),('$siteid','brand'),('$siteid','product'),".
         "('$siteid','info'),('$siteid','feature'),('$siteid','video')";
  
  //cout("$sql");
  $S->query($sql);

  // Add new site to segments table
  
  $sql = "INSERT IGNORE INTO `segments` (siteId, category) ".
         "VALUES ('$siteid','announce'),('$siteid','brand'),('$siteid','product'),".
         "('$siteid','info'),('$siteid','video')";
  //cout("$sql");
  $S->query($sql);

  // Add new site to modified table

  $sql = "insert ignore into `modified` (siteId, xchange) values('$siteid', 0)";
  //cout("$sql");
  $S->query($sql);

  // Add to playbingo and playlotto
  $sql = "insert ignore into `playbingo` (siteId) values('$siteid')";
  $S->query($sql);
  $sql = "insert ignore into `playlotto` (siteId) values('$siteid')";
  $S->query($sql);
  $sql = "insertt ignore into `playtrivia` (siteId) value('$siteid')";
  $S->query($sql);
  
  $siteid = stripslashes($siteid);
  $name = stripslashes($fname) . " " .stripslashes($lname);
  echo <<<EOF
$top
<h1>Site Created</h1>
<ul>
<li>Site: $siteid</li>
<li>Owner: $name</li>
</ul>
<p>Use the control panel to edit site parameters. All parameters have been set to the defaults.</p>
<hr>
$footer
EOF;

  exit();
}

// ****************************************
// Start Page
// First Get the Information for a new site
// ****************************************

echo <<<EOF
$top
<h2>Welcome 'super user' $superuserName</h2>
<form method="post">
<table>
<tr>
<td>Site Name</td>
<td><input type="text" name="siteid"></td>
</tr>
<tr>
<td>Owner First Name</td>
<td><input type="text" name="fname"></td>
</tr>
<tr>
<td>Owner Last Name</td>
<td><input type="text" name="lname"></td>
</tr>
<tr>
<td>Owner Password</td>
<td><input type="text" name="ownerpassword">
</tr>
<tr>
<td>Owner Email</td>
<td><input type="email" name="email"></td>
</tr>
<tr>
<td>Company Name</td>
<td><input type="text" name="company"</td>
</tr>
<tr>
<td>Company Address</td>
<td><input type="text" name="address"></td>
</tr>
<tr>
<td>Company Phone</td>
<td><input type="text" name="phone"></td>
</tr>
<tr>
<td>Email Server</td>
<td><input type="text" name="server"></td>
</tr>
<tr>
<td>Email Username</td>
<td><input type="text" name="username"></td>
</tr>
<tr>
<td>Email Password</td>
<td><input type="text" name="emailpassword"></td>
</tr>
<tr>
<td>Email Port</td>
<td><input type="text" name="port"></td>
</tr>
</table>
<input type="submit" value="Submit">
<input type="hidden" name="page" value="createsite">
</form>
<hr>
$footer
EOF;
