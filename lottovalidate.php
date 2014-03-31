<?php
/*
CREATE TABLE `lottowinners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteId` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `name` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `itemId` int(11) DEFAULT NULL,
  `prize` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `expires` date DEFAULT NULL,
  `wintime` datetime DEFAULT NULL,
  `redeemtime` datetime DEFAULT NULL,
  `employeeId` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
*/

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$h->title = "PhotoLotto Redeemption";

// Submit form

if($_POST['submit']) {
  $S->query("update lottowinners set employeeId='{$_POST['employeeId']}', redeemtime=now() ".
            "where id={$_POST['id']}");

  $h->banner = "<h1>Redemption Posted</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  echo "$top$footer";
  exit();
}

// If get and no winnerid then error

if(!($winnerId = $_GET['winnerid'])) {
  $h->banner = "<h1>Wrong Place</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  echo "$top$footer";
  exit();
}

// If winnerId not in lottowinners table then error

if(!$S->query("select id, siteId, name, email, itemId, prize, expires, wintime, ".
              "employeeId, redeemtime, date(now()) as today ".
              "from lottowinners where id='$winnerId'")) {
  
  $h->banner = "<h1>Your Id is not valid</h1>";
  list($top, $footer) = $S->getPageTopBottom($h);
  echo "$top$footer";

  exit();
}

$row = $S->fetchrow('assoc');
extract($row); // id,siteId,name,email,itemId,prize,expires,wintime,employeeId,redeemtime,today

$h->banner = "<h1>PhotoLotto Winner Redeemption</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

// Render Alread Won Page

if($redeemtime) {
  echo <<<EOF
$top
<h3>Your Prize has already been redeemed on $redeemtime.</h3>
<style>
table {
  margin-left: 12px;
}
td:first-child {
  width: 140px;
}
</style>
<table>
<tr><td><li>Name:</li></td><td>$name</td></tr>
<tr><td><li>Email:</li></td><td>$email</td></tr>
<tr><td><li>Prize:</li></td><td>$prize</td><tr>
<tr><td><li>Expires:</li></td><td>$expires.</td></tr>
<tr><td><li>Win Time:</li></td><td>$wintime</td></tr>
<tr><td><li>Employee:</li></td><td>$employeeId</td></tr>
<tr><td><li>Redeem Time:</li></td><td>$redeemtime</td></tr>
</table>
<hr>
$footer;
EOF;
  exit();
}

$S->query("select location from items where itemId=$itemId");
list($loc) = $S->fetchrow('num');

// Render Winner Page

echo <<<EOF
$top
<style>
table {
  margin-left: 12px;
}
td:first-child {
  width: 100px;
}
</style>
<table>
<tr><td><li>ID:</li></td><td>$id</td></tr>
<tr><td><li>Name:</li></td><td>$name</td></tr>
<tr><td><li>Email:</li></td><td>$email</td></tr>
<tr><td><li>Prize:</li></td><td>$prize</td><tr>
<tr><td><li>Expires:</li></td><td>$expires.</td></tr>
</table>
<table>
<tr><td><li>Winning Photo:</li><br>
<img width="400" src="/$loc" alt="/$loc"/>
</td></tr>
</table>
<hr>
EOF;

if($today > $expires) {
  // Already Expired
  echo <<<EOF
<h3>I am sorry but your prize has expired on $expires</h3>
<p>Please play again and remember you must present your redemtion email no later than the
experation date on the email.</p>
<hr>
$footer
EOF;
} else {
  // Your are a winner

  echo <<<EOF
<form method='post'>
Enter employee ID or Name: <input type='text' name='employeeId'/><br>
<input type='hidden' name='id' value='$winnerId'/>
<input type='submit' name='submit' value='Submit'/>
</form>
<hr>
$footer
EOF;
}
?>