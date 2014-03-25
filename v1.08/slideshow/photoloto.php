#! /usr/bin/php6 -q
<?php
// BLP 2014-02-28 --
// BLP 2014-02-25 -- $debug=true; This disables blacklist and outputs additional info to stdout
// Play Lotto Game
// Can run as CLI or web program

if(!$_SERVER['DOCUMENT_ROOT']) {
  $cli = true;
  $siteautoload = "/homepages/45/d454707514/htdocs/siteautoload.php";
} else {
  $siteautoload = $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php";
}

define('TOPFILE', $siteautoload);
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Tom;

// Debug disables blacklist and outputs additional info to stdout.
//$debug = true;

// Which bars are playing

$sites = array();

$S->query("select s.siteId, data, expires, game, period, canPlay from sites as s left join playlotto as p ".
          "on s.siteId=p.siteId where playLotto='yes'");

while(list($site, $lottoData, $lottoExpires, $game, $period, $canPlay) = $S->fetchrow('num')) {
  array_push($sites, array('siteId'=>$site, 'lottoData'=>$lottoData,
                           'lottoExpires'=>$lottoExpires, 'game'=>$game,
                           'period'=>$period, 'canPlay'=>$canPlay));
}

// Main loop do it for each site

foreach($sites as $site) {
  $lottoData = json_decode($site['lottoData']);
  $siteId = $site['siteId'];
  $expires = date("F j, Y", strtotime($site['lottoExpires']));
  $game = ($site['game'] + 1) % 4;
  $period = $site['period'];
  $canPlay = $site['canPlay'];

  echo "\nSiteId: $siteId\n";
  
  // Skip if game is false

  if($cli === true) {
    // update game
    $S->query("update playlotto set game='$game' where siteId='$siteId'");
  }

  if($lottoData[$site['game']]->game == false) {
    if($debug) echo "SiteId: $siteId game $game is false\n";
    continue;
  }

  $prize = $lottoData[$site['game']]->prize;

  // Look to see who has already won in the past period. Put them into the balcklist also.
  // If $canPlay is zero can play every game, otherwise can't play until $canPlay days have passed.

  $blacklist = array();

  if($canPlay != 0) {
    if($S->query("select email from lottowinners where siteId='$siteId' and ".
                 "datetime > date_sub(now(), interval $canPlay day)")) {
      echo "Customers who have already won in the past $canPlay days\n";
      while(list($bemail) = $S->fetchrow('num')) {
        $blacklist[] = $bemail;
        echo "$bemail\n";
      }
    }
  }

  // Company members can't play!

  $S->query("select email from users where siteId='$siteId'");

  while(list($blacklist[]) = $S->fetchrow('num'));
  
  array_pop($blacklist); // pop off the endoffile

  $sql = "select itemId, creatorName, location, creationTime from items ".
         "where siteId='$siteId' and status='active' ".
         "and creationTime > date_sub(now(), interval $period day)";

  if(!$S->query($sql)) {
    if($debug) echo "No Images within $period days for $siteId\n";
    continue;
  }
  
  $ar = array();

  while(list($itemId, $name, $loc, $time) = $S->fetchrow('num')) {
    if(preg_match("/^\s*$/", $name)) {
      if($debug) echo "name blank\n";
      continue;
    }
    
    if(strpos($name, '@') === false) {
      if($debug) echo "$name: Name has No Email\n";
      continue; // no email address
    }

    $e = $name;
    if(preg_match('/^(.*?)\s*&lt;(.*?)&gt;/', $name, $m)) {
      if(strpos($m[1], '@') === false) {
        $name = $m[1];
      } else {
        $name = '';
      }
      $e = $m[2];
    } else {
      $name = '';
    }

    if(in_array($e, $blacklist)) {
      // While debugging and testing let blacklist members play.
      if($debug) {
        echo "$e: In Blacklist\n";
      } else {
        continue;
      }
    }
    
    $ar[] = array($name, $e, $loc, $time, $itemId);
  }

  if(count($ar) == 0) {
    echo "No one found for $siteId\n";
    continue;
  } 

  echo count($ar) . " customers are elagable to play at $siteId\n";

  shuffle($ar);

  list($name, $email, $loc, $date, $itemId) = $ar[rand(0, count($ar)-1)];

  $date = date("F j", strtotime($date));

  if($debug) echo "name: $name, email: $email, date: $date, itemId: $itemId\n";
  
  if(!$cli) {  
    echo <<<EOF
<h1>$siteId</h1>
<style>
img {
  max-height: 430px;
}
</style>
<div>
<img src='photolotowinner.php?siteId=$siteId&name=$name&date=$date&loc=$loc'/>
</div>
EOF;
  } else {
    // CLI program
    // Log info

    $S->query("insert into lottowinners (siteId, name, email, itemId, datetime) ".
              "values('$siteId', '$name', '$email', '$itemId', now())");
  
    // Create the image and save it.
    
    $im = imagecreatetruecolor(800, 600);
    $dest = imagecreatefromjpeg(DOC_ROOT ."/$loc");
    list($width, $height) = getimagesize(DOC_ROOT ."/$loc");

    $newH = $height/$width*200;
    $white = imagecolorallocate($im, 255, 255, 255); // white
    $black = imagecolorallocate($im, 0, 0, 0); // black
    $font = DOC_ROOT .'/fonts/ARIALBD.TTF';
    $msg = <<<EOF
You too can win the PhotoLoto,
all you have to do is send in a photo.
Use your phone and take a picture.
Then send it to
felixs@myphotochannel.com.
It is that simple.
EOF;
    $winner = "by: $name\non: $date";
    if(empty($name)) $winner = "on: $date";

    imagettftext($im, 30, 0, 50, 130, $white, $font, "The winning photo of the PhotoLotto:");
    imagettftext($im, 30, 0, 260, 220, $white, $font, $winner);
    imagettftext($im, 20, 0, 260, 320, $white, $font, "$msg");

    imagecopyresampled($im, $dest, 50, 170, 0, 0, 200, $newH, $width, $height);

    header('Content-Type: image/png');
    imagepng($im, DOC_ROOT ."/content/lottowinner$siteId.png");
    imagedestroy($im);

    // Send the email
    $msg = <<<EOF
($name, $email):
You submitted a photo to $siteId that was randomly drawn for a special offer.
Come in to redeem your prize ($prize) by presenting this email to a bartender or server.
Hurry, offer expires on $expires!!!”.  Thank you for sharing your photos!
EOF;

    mail($email, "$siteId Photo Lotto Winner. Expires $expires", $msg,
                 "From: info@myphotochannel.com\r\nCC: bartonphillips@gmail.com\r\n",
                 "-fbartonphillips@gmail.com");
  }
}
?>
