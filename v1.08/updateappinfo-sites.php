<?php
/*
CREATE TABLE `appinfo` (
  `siteId` varchar(50) NOT NULL,
  `lifeOfFeature` int(11) DEFAULT '5',
  `whenPhotoAged` varchar(50) DEFAULT '30 day',
  `callbackTime` int(11) DEFAULT '50',
  `frequentCallbackTime` int(11) DEFAULT '9',
  `progDuration` int(11) DEFAULT '20',
  `featuresPer` int(11) DEFAULT '20',
  PRIMARY KEY (`siteId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sites` (
  `siteId` varchar(50) NOT NULL,
  `siteCode` varchar(50) NOT NULL,
  `allowAds` enum('no','yes') DEFAULT 'no',
  `allowVideo` enum('no','yes') DEFAULT 'no',
  `playbingo` enum('no','yes') DEFAULT 'no',
  `playLotto` enum('no','yes') DEFAULT 'no',
  `perRecent` int(11) DEFAULT '0',
  `featureExt` varchar(50) DEFAULT 'no',
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `company` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `emailServer` varchar(255) DEFAULT NULL,
  `emailUsername` varchar(50) DEFAULT NULL,
  `emailPassword` varchar(50) DEFAULT NULL,
  `emailPort` int(11) DEFAULT NULL,
  `open` time DEFAULT NULL,
  `close` time DEFAULT NULL,
  PRIMARY KEY (`siteId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table appinfo add allowAds enum('no','yes') default 'no';
alter table appinfo add allowVideo enum('no','yes') default 'no';
alter table appinfo add playbingo enum('no','yes') default 'no';
alter table appinfo add playLotto enum('no','yes') default 'no';
alter table appinfo add perRecent int(11) default '0';
alter table appinfo add featureExt varchar(50) default 'no';

CREATE TABLE `appinfo` (
  `siteId` varchar(50) NOT NULL,
  `lifeOfFeature` int(11) DEFAULT '5',
  `whenPhotoAged` varchar(50) DEFAULT '30 day',
  `callbackTime` int(11) DEFAULT '50',
  `frequentCallbackTime` int(11) DEFAULT '9',
  `progDuration` int(11) DEFAULT '20',
  `featuresPer` int(11) DEFAULT '20',
  `allowAds` enum('no','yes') DEFAULT 'no',
  `allowVideo` enum('no','yes') DEFAULT 'no',
  `playbingo` enum('no','yes') DEFAULT 'no',
  `playLotto` enum('no','yes') DEFAULT 'no',
  `perRecent` int(11) DEFAULT '0',
  `featureExt` varchar(50) DEFAULT 'no',
  PRIMARY KEY (`siteId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

Error::setDevelopment(true);
Error::setNoEmailErrs(true);

$S = new Database($dbinfo);

$sql = "select siteId from sites";
$S->query($sql);
$result = $S->getResult();

while(list($siteId) = $S->fetchrow($result, 'num')) {
  $sql = "select allowAds, allowVideo, playbingo, playLotto, perRecent, featureExt ".
         "from sites where siteId='$siteId'";

  $S->query($sql);
  
  list($allowAds, $allowVideo, $playbingo, $playLotto, $perRecent, $featureExt) = $S->fetchrow('num');

  $sql = "update appinfo set allowAds='$allowAds', allowVideo='$allowVideo', ".
         "playbingo='$playbingo', playLotto='$playLotto', ".
         "perRecent='$perRecent', featureExt='$featureExt' where siteId='$siteId'";

  //echo "$sql<br>";
  $S->query($sql);
}

echo "DONE<br>";
?>

