#! /usr/bin/php6 -q
<?php
// BLP 2014-04-14 -- fix playlotto. This is in two tables, the appinfo and sites tables. It was
// originally in the sites table but I moved playbingo and playlotto from the sites table to the
// appinfo table but HAVE NOT yet removed them form the sites table. In cpanel.games I set these
// values in the appinfo table but here I was reading playlotto from the sites table.
// I have also fixed createNewSite.php to initialize the playbingo and playlotto tables.
// BLP 2014-02-28 --
// BLP 2014-02-25 -- $debug=true; This disables blacklist and outputs additional info to stdout
// Play Lotto Game
// Can run as CLI or web program
/*
The playlotto table controls the game.
'expires' is the number of days from the lottowinners creationTime before the prize offer expires.
'period' is the number of days prior to today that we gather photos from the 'items' table.
We do not gather photos from the entire history.
'canPlay' is the number of days prior to today since someone has won. Any winner that has won more
recently than canPlay days is blacklisted.
'data' contains json information about the games. There are 4 games per day and each game has a
prize. The json data is: game, prize four times. 'game' is true or false, that is play or not.
'prize' is the description of the prize like '$1,000,000' or '2 beers'.
Currently (BLP 2014-04-05) the skipdays and skipdaysleft fields are NOT USED. I think my idea was that
someone could not play again until N number of games had passed. Currently there is no logic to make
this happen in the playloto.php program.

CREATE TABLE `playlotto` (
  `siteId` varchar(255) NOT NULL,
  `data` mediumtext comment 'this is for json data',
  `expires` varchar(20) DEFAULT '+30 day' comment 'When the prize expires',
  `game` int(2) DEFAULT '0' comment 'there are 4 games per day this tells which one',
  `period` int(11) DEFAULT '30' comment 'number of days we gather photos from the items table for',
  `skipdays` int(11) DEFAULT '0' comment 'Not Used',
  `skipdaysleft` int(11) DEFAULT '0' comment 'Not Used',
  `canPlay` int(11) DEFAULT '30' comment 'number of days before someone can play again',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`siteId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

The lottowinners table has the history of everyone who has won the game along with the redemption
information.

CREATE TABLE `lottowinners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteId` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `name` varchar(255) COLLATE latin1_general_ci DEFAULT NULL comment 'Winners name',
  `email` varchar(255) COLLATE latin1_general_ci DEFAULT NULL comment 'Winners email',
  `itemId` int(11) DEFAULT NULL comment 'itemId in the items table',
  `prize` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `expires` date DEFAULT NULL,
  `wintime` datetime DEFAULT NULL comment 'when the prize was won',
  `redeemtime` datetime DEFAULT NULL comment 'when the prize was redeemed',
  `employeeId` varchar(255) COLLATE latin1_general_ci DEFAULT NULL comment 'employee who did the redemption',
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

// Little function the either display of CLI or Web.
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

$S->query("select s.siteId, data, expires, game, period, canPlay from sites as s ".
          "left join playlotto as p on s.siteId=p.siteId ".
          "left join appinfo as a on s.siteId=a.siteId ".
          "where a.playLotto='yes'");

while(list($site, $lottoData, $lottoExpires, $game, $period, $canPlay) = $S->fetchrow('num')) {
  array_push($sites, array('siteId'=>$site, 'lottoData'=>$lottoData,
                           'lottoExpires'=>$lottoExpires, 'game'=>$game,
                           'period'=>$period, 'canPlay'=>$canPlay));
}

// Main loop do it for each site

foreach($sites as $site) {
  $lottoData = json_decode($site['lottoData']); // game, prize X 4
  $siteId = $site['siteId'];
  $expireDate = date("Y-m-d", strtotime($site['lottoExpires'])); // standard date format
  $expires = date("F j, Y", strtotime($site['lottoExpires'])); // display date format
  $game = ($site['game'] + 1) % 4; // next game mod 4
  $period = $site['period']; // Period to look for photos in 'items' table
  $canPlay = $site['canPlay']; // When winner can play again. Blacklist

  putit("\nSiteId: $siteId");
  
  if($cli === true) {
    // update game
    $S->query("update playlotto set game='$game' where siteId='$siteId'");
  }

  // Skip (continue) if game is false ie. Don't play.

  if($lottoData[$site['game']]->game === false) {
    if($debug) putit("SiteId: $siteId game $game is false");
    continue;
  }

  // $game is the next game $site['game'] is this game
  
  $prize = $lottoData[$site['game']]->prize;

  // Look to see who has already won in the past $canPlay days. Put them into the balcklist.
  // If $canPlay is zero can play every game, otherwise can't play until $canPlay days have passed.

  $blacklist = array();

  if($canPlay != 0) {
    if($n = $S->query("select email, winTime from lottowinners where siteId='$siteId' and ".
                      "winTime > date_sub(now(), interval $canPlay day)")) {

      putit("$n customers have already won in the past $canPlay days");

      while(list($bemail, $bwinTime) = $S->fetchrow('num')) {
        $blacklist[] = $bemail;
        if($debug) putit("$bemail : $bwinTime");
      }
    }
  }

  // Company members can't play so they also go into the blacklist!

  $S->query("select email from users where siteId='$siteId'");

  while(list($blacklist[]) = $S->fetchrow('num'));
  
  array_pop($blacklist); // pop off the endoffile

  // We colect images only from the last 'period' days.
  
  $sql = "select itemId, creatorName, location, creationTime from items ".
         "where siteId='$siteId' and status='active' ".
         "and creationTime > date_sub(now(), interval $period day)";

  if(!$S->query($sql)) {
    putit("No Images within $period days for $siteId");
    continue;
  }
  
  $ar = array();

  // Look at the photos
  
  while(list($itemId, $name, $loc, $time) = $S->fetchrow('num')) {
    // If the $name is blank continue to next photo.
    if(preg_match("/^\s*$/", $name)) {
      if($debug) putit("name blank");
      continue;
    }

    // The name field in the 'items' table can (should) have an email address. If there isn't an
    // email address then continue to next photo.
    
    if(strpos($name, '@') === false) {
      putit("$name: Name has No Email Address");
      continue; // no email address
    }

    $e = $name; // By default set email address equal name.

    // Now take the name apart to get the email address. We are looking for <email@host.ext> format
    // after the user's name at the start.
    
    if(preg_match('/^(.*?)\s*&lt;(.*?)&gt;/', $name, $m)) {
      // Ther may be no user name and only the email address. Look at the first captured piece to
      // see if there is an @ sign indicating an email address.
      
      if(strpos($m[1], '@') === false) {
        $name = $m[1];
      } else {
        $name = '';
      }
      $e = $m[2]; // email is second captured piece
    } else {
      $name = ''; // we preset $e=$name above.
    }

    // Is the email address in the blacklist?
    
    if(in_array($e, $blacklist)) {
      // While debugging and testing let blacklist members play.
      if($debug) {
        putit("$e: In Blacklist");
      } else {
        continue;
      }
    }
    
    $ar[] = array($name, $e, $loc, $time, $itemId);
  } // End of while

  // Did we find any candidates?
  
  if(count($ar) == 0) {
    putit("No one found for $siteId");
    continue;
  } 

  putit(count($ar) . " customers are elagable to play at $siteId");

  // Randomize the list a little.
  
  shuffle($ar);

  // Get the winner by randomizing a little more
  
  list($name, $email, $loc, $date, $itemId) = $ar[rand(0, count($ar)-1)];

  $date = date("F j, Y", strtotime($date)); // Photo date

  if($debug) putit("name: $name, email: $email, date: $date, itemId: $itemId");
  
  // Log info in the lottowinners table.

  $mysqlprize = $S->escape($prize); // BLP 2014-04-14 -- add escape
  
  $S->query("insert into lottowinners (siteId, name, email, itemId, prize, expires, winTime) ".
            "values('$siteId', '$name', '$email', '$itemId', '$mysqlprize', '$expireDate', now())");

  $lottowinnerId = $S->getLastInsertId();
  
  // Create the image and save it. This is the image displayed during the slideshow.
    
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

  imagepng($im, DOC_ROOT ."/content/lottowinner$siteId.png");
  imagedestroy($im);

  // Make the image that goes with the email to the winner.
  
  $im = imagecreatetruecolor(800, 650);

  $white = imagecolorallocate($im, 255, 255, 255); // white
  $black = imagecolorallocate($im, 0, 0, 0); // black
  $font = DOC_ROOT .'/fonts/ARIALBD.TTF';
  $winner = "by: $name\non: $date";
  if(empty($name)) {
    $winner = "on: $date";
  }

  $newH = $height/$width*800;
  $newW = 800;
  
  if($newH > 579) {
    $newW = $width/$height*579;
    $newH = 579;
  }
  if($debug) putit("$height, $width, $newW, $newH");

  $x = ((800 - $newW) /2);
    
  // imagettftext(img, size, angle, w, h, color, font, text);
  imagettftext($im, 30, 0, 50, 50, $white, $font, "The winning photo of the PhotoLotto:");
  imagecopyresampled($im, $dest, $x, 70, 0, 0, $newW, $newH, $width, $height);

  imagejpeg($im, DOC_ROOT ."/content/lottowinner-email$siteId.jpg");
  imagedestroy($im);
  
  // Send the email. We are using an embedded image (cid:xxx) rather than a link because most email
  // clients don't show links by default.
  
  $msg = <<<EOF
<p><b>$name</b>:<br>
<img width="300" src='cid:myattach'/>
<br><br>
<a href='http://go.myphotochannel.com/lottovalidate.php?winnerid=$lottowinnerId'
style="background-color:#FFFFCC;padding: 3px 70px;border:5px solid green;border-radius:25px;">
REDEEM ON VISIT
</a>
<br><br>
Prize: <b>$prize</b><br><br>
You submitted a photo to $siteId that was randomly drawn for a special offer.
Present this email to a server or bartender to redeem your offer ($prize).
Hurry this offer expires on $expires!!!  Thank you for sharing your photos.</p>
<p>REDEEM ON VISIT at http://go.myphotochannel.com/lottovalidate.php?winnerid=$lottowinnerId
</p>
EOF;
  
  if($debug) putit($msg);
  
  // DOCUMENTATION for PHPMailer.
  // http://phpmailer.github.io/PHPMailer/classes/PHPMailer.html
  
  require DOC_ROOT .'/PHPMailer/PHPMailerAutoload.php';
  
  $mail = new PHPMailer();
  $mail->IsSendmail(); // telling the class to use SendMail transport
  $mail->AddReplyTo("bartonphillips@gmail.com","Barton Phillips");
  $mail->SetFrom('info@myphotochannel.com');
  $mail->AddAddress($email, $name);
  $mail->addBCC("bartonphillips@gmail.com", "Barton Phillips");
  $mail->Subject = "$siteId PhotoLotto Winner. Expires $expires";
  $mail->MsgHTML($msg); // Creates HTML and TEXT message
  $mail->AddEmbeddedImage(DOC_ROOT ."/content/lottowinner-email$siteId.jpg",
                          "myattach", "Winner.jpg");

  if(!$mail->Send()) {
    putit("Mailer Error: " . $mail->ErrorInfo);
  } else {
    putit("Message sent!");
  }

  // If this is NOT CLI we show the images on the web page.
  
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
<img src="http://go.myphotochannel.com/content/lottowinner-email$siteId.jpg"/>
</div>
EOF;
    putit($climsg);
  }
}
?>