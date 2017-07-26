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

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
$S = new $_site->className($_site);

$h->title = "PhotoLotto Redemption";
$h->extra = <<<EOF
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
#winnerphoto {
  width: 500px;
}
table {
  margin-left: 12px;
}
#table1 td:first-child {
  width: 100px;
}
@media (max-width: 500px) {
  body {
    font-size: 14px;
  }
  h1 {
    font-size: 20px;
  }
  h2 {
    font-size: 16px;
  }
  #myphotochannelheader img {
    max-width: 80%;
  }
  #winnerphoto {
    max-width: 480px;
    width: 100%;
  }
  table {
    margin-left: 3px;
  }
  #table1 td:first-child {
    50px;
  }
}
</style>
EOF;

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

// Render Already Won Page

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

if($today > $expires) {
  // Already Expired
  echo <<<EOF
$top
<h3>I am sorry but your prize has expired on $expires</h3>
<p>Please play again and remember you must present your redemtion email no later than the
experation date on the email.</p>
<hr>
$footer
EOF;
} else {
  // Your are a winner

  echo <<<EOF
$top
<h2>Your Prize is: $prize</h2>
<form method='post'>
Enter employee ID or Name: <input type='text' name='employeeId'/><br>
<input type='hidden' name='id' value='$winnerId'/>
<input type='submit' name='submit' value='Submit'/>
</form>
<hr>
EOF;
}

  echo <<<EOF
<table id="table1">
<tr><td><li>ID:</li></td><td>$id</td></tr>
<tr><td><li>Name:</li></td><td>$name</td></tr>
<tr><td><li>Email:</li></td><td>$email</td></tr>
<tr><td><li>Prize:</li></td><td>$prize</td><tr>
<tr><td><li>Expires:</li></td><td>$expires.</td></tr>
</table>
<table id="table2">
<tr><td><li>Winning Photo:</li><br>
<img id="winnerphoto" src="/$loc" alt="/$loc"/>
</td></tr>
</table>
<hr>
$footer
EOF;
