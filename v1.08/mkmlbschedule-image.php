#! /usr/bin/php6 -q
<?php
// BLP 2014-04-25 --  Make the mlb.png image that show the next scheduled Major Leage Baseball game
// to be played by the Cardinals. See the csvToDb.php program for more details.
// This program is run via cron every day at 7AM.
// There is another program, mkmlbschedule.php that make mlb.html but we are not using it at this
// time.

// Force our TOPFILE for CLI
define('TOPFILE', "/homepages/45/d454707514/htdocs/siteautoload.php");
// Now this looks like all the other files.
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else {
  echo "Can't find siteautoload.php";
  exit();
}

// During debug
Error::setDevelopment(true);
Error::setNoEmailErrs(true);
Error::setNoHtml(true);

$S = new Database($dbinfo);

/*
CREATE TABLE `sportsschedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('mlb','nfl','nbl') DEFAULT 'mlb',
  `image` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `team` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=684 DEFAULT CHARSET=utf8;
*/

$sql = "select concat(date,' ', time), subject, location from sportsschedule ".
       "where date > curdate() limit 3";

$S->query($sql);
$tbl = '';
$cnt = 0;

while(list($date, $subject, $location) = $S->fetchrow('num')) {
  $date = date("l F j", strtotime($date));
  // area to put line can take 51 caracters.
  $line = "$date, $subject $location";

  if(strlen($line) > 51) {
    $x = explode(" ", $line);
    $line = '';
    $l = '';
    foreach($x as $y) {
      if(strlen("$l $y") < 51) {
        $l .= "$y ";
      } else {
        $line .= rtrim($l, " ") . "\n";
        $l = "$y ";
      }
    }

    if($l) {
      $line .= "$l";
      ++$cnt;
    }
  }
  $tbl .= "$line\n\n";
}

$ypos = 300 - ($cnt * 17);

$im = @imagecreatefrompng(DOC_ROOT ."/images/mlb.png");
$text_color = imagecolorallocate($im, 0, 0, 0);
$font = DOC_ROOT .'/fonts/ARIALBD.TTF';

imagettftext($im, 30, 0, 80, $ypos, $text_color, $font, $tbl);
//imagepng($im);
imagepng($im, DOC_ROOT ."/adscontent/mlb.png");
imagedestroy($im);
?>