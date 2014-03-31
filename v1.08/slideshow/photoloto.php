#! /usr/bin/php6 -q
<?php
// BLP 2014-02-28 --
// BLP 2014-02-25 -- $debug=true; This disables blacklist and outputs additional info to stdout
// Play Lotto Game
// Can run as CLI or web program
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

function putit($msg) {
  global $cli;
  echo $msg;
  echo ($cli) ? "\n" : "<br>";
}

// Debug disables blacklist and outputs additional info to stdout.
$debug = true;

if($debug) {
  putit("*********************");
  putit("DEBUG MODE ACTIVE");
  putit("*********************");
}

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
  $expireDate = date("Y-m-d", strtotime($site['lottoExpires']));
  $expires = date("F j, Y", strtotime($site['lottoExpires']));
  $game = ($site['game'] + 1) % 4;
  $period = $site['period'];
  $canPlay = $site['canPlay'];

  putit("\nSiteId: $siteId");
  
  // Skip if game is false

  if($cli === true) {
    // update game
    $S->query("update playlotto set game='$game' where siteId='$siteId'");
  }

  if($lottoData[$site['game']]->game == false) {
    if($debug) putit("SiteId: $siteId game $game is false");
    continue;
  }

  $prize = $lottoData[$site['game']]->prize;

  // Look to see who has already won in the past period. Put them into the balcklist also.
  // If $canPlay is zero can play every game, otherwise can't play until $canPlay days have passed.

  $blacklist = array();

  if($canPlay != 0) {
    if($S->query("select email, winTime from lottowinners where siteId='$siteId' and ".
                 "winTime > date_sub(now(), interval $canPlay day)")) {

      putit("Customers who have already won in the past $canPlay days");

      while(list($bemail, $bwinTime) = $S->fetchrow('num')) {
        $blacklist[] = $bemail;
        putit("$bemail : $bwinTime");
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
    if($debug) putit("No Images within $period days for $siteId");
    continue;
  }
  
  $ar = array();

  while(list($itemId, $name, $loc, $time) = $S->fetchrow('num')) {
    if(preg_match("/^\s*$/", $name)) {
      if($debug) putit("name blank");
      continue;
    }
    
    if(strpos($name, '@') === false) {
      if($debug) putit("$name: Name has No Email");
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
        putit("$e: In Blacklist");
      } else {
        continue;
      }
    }
    
    $ar[] = array($name, $e, $loc, $time, $itemId);
  }

  if(count($ar) == 0) {
    putit("No one found for $siteId");
    continue;
  } 

  putit(count($ar) . " customers are elagable to play at $siteId");

  shuffle($ar);

  list($name, $email, $loc, $date, $itemId) = $ar[rand(0, count($ar)-1)];

  $date = date("F j, Y", strtotime($date));

  if($debug) putit("name: $name, email: $email, date: $date, itemId: $itemId");
  
  // CLI or Web program 
  // Log info

  $S->query("insert into lottowinners (siteId, name, email, itemId, prize, expires, winTime) ".
            "values('$siteId', '$name', '$email', '$itemId', '$prize', '$expireDate', now())");

  $lottowinnerId = $S->getLastInsertId();
  
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
  if(empty($name)) {
    $winner = "on: $date";
  }

  imagettftext($im, 30, 0, 50, 130, $white, $font, "The winning photo of the PhotoLotto:");
  imagettftext($im, 30, 0, 260, 220, $white, $font, $winner);
  imagettftext($im, 20, 0, 260, 320, $white, $font, "$msg");

  imagecopyresampled($im, $dest, 50, 170, 0, 0, 200, $newH, $width, $height);

  //header('Content-Type: image/png');
  imagepng($im, DOC_ROOT ."/content/lottowinner$siteId.png");
  imagedestroy($im);

  // Now make the photo that goes with the email
  
  $im = imagecreatetruecolor(800, 650);

  $white = imagecolorallocate($im, 255, 255, 255); // white
  $black = imagecolorallocate($im, 0, 0, 0); // black
  $font = DOC_ROOT .'/fonts/ARIALBD.TTF';
  $winner = "by: $name\non: $date";
  if(empty($name)) {
    $winner = "on: $date";
  }

  $newH = $height/$width*800;
  if($debug) putit("$height, $width, $newH");
  
  imagettftext($im, 30, 0, 50, 50, $white, $font, "The winning photo of the PhotoLotto:");
  imagecopyresampled($im, $dest, 0, 70, 0, 0, 800, $newH, $width, $height);

  //header('Content-Type: image/png');
  imagepng($im, DOC_ROOT ."/content/lottowinner-email$siteId.png");
  imagedestroy($im);
  
  // Send the email
  $msg = <<<EOF
<p>($name, $email):<br>
<img width="600" src="cid:myattach"/><br>
You submitted a photo to $siteId that was randomly drawn for a special offer.
Come in to redeem your prize ($prize) by presenting this email to a bartender or server.
Hurry, offer expires on $expires!!! Thank you for sharing your photos.</p>
<p>The <b>bartender or server</b> must follow this
<a href="http://go.myphotochannel.com/lottovalidate.php?winnerid=$lottowinnerId">
link</a> (http://go.myphotochannel.com/lottovalidate.php?winnerid=$lottowinnerId)
to validate your prize.</p>
EOF;


  //if($debug) putit($msg);
  
  // DOCUMENTATION
  // http://phpmailer.github.io/PHPMailer/classes/PHPMailer.html
  
  require DOC_ROOT .'/PHPMailer/PHPMailerAutoload.php';
  
  $mail = new PHPMailer();
  $mail->IsSendmail(); // telling the class to use SendMail transport
  $mail->AddReplyTo("bartonphillips@gmail.com","Barton Phillips");
  $mail->SetFrom('info@myphotochannel.com');
  $mail->AddAddress($email, $name);
  $mail->addBCC("bartonphillips@gmail.com", "Barton Phillips");
  $mail->Subject = "$siteId Photo Lotto Winner. Expires $expires";
  $mail->MsgHTML($msg);
  $mail->AddEmbeddedImage(DOC_ROOT ."/content/lottowinner-email$siteId.png", "myattach", "Winner.png");

  if(!$mail->Send()) {
    putit("Mailer Error: " . $mail->ErrorInfo);
  } else {
    putit("Message sent!");
  }

  //putit(getSentMIMEMessage());
  
  if(!$cli) {  
    $climsg =  <<<EOF
<h1>$siteId</h1>
<style>
img {
  max-width: 600px;
}
</style>
<div>
<img src="http://go.myphotochannel.com/content/lottowinner$siteId.png"/><br><br><br>
<img src="http://go.myphotochannel.com/content/lottowinner-email$siteId.png"/>
</div>
EOF;
    putit($climsg);
  }
}
?>
