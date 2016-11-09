<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . " not found");

$S = new Tom;

if(!($winnerId = $_GET['winnerid'])) {
  echo "Wrong Place";
  exit();
}

if(!$S->query("select * from lottowinners where id='$winnerId'")) {
  echo "Your Id is not valid";
  exit();
}

list($id, $siteId, $name, $email, $itemId, $prize, $expires) = $S->fetchrow('num');
$S->query("select location from items where itemId=$itemId");
list($loc) = $S->fetchrow('num');

$h->title = "PhotoLotto Redeemption";
$h->banner = "<h1>PhotoLotto Winner Redeemption</h1>";

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>id: $id, name: $name &lt;$email&gt;</p>
<p>Prize: $prize, expires: $expires.</p>
<p>Winning Photo:<br>
<img width="400" src="/$loc" alt="/$loc"/>
</p>

$footer
EOF;
