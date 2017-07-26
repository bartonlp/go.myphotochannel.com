<?php
   // Database Ajax Program. Get Images for the Slide Show

   // This program gets items from the database. The information is passed as a GET.
   //
   // db.ajax.php?name=<function>&getType=<type>&num=<number-of-items>
   //            &category=<category>&date=<date-filter>&siteId=<site id>
   // Arguments:
   // name: required. 'getItem', 'getInfo'
   //
   //   name=getItem. Get the requested category from the 'items' table.
   //     siteCode: required. The siteid
   //     type: optional. defaults to 'random'. Other types??
   //     category: 'feature', 'photo', 'announce' 'brand', 'product' and 'info'
   //     num: The number of items to return.
   //     startupid: instance startup id see 'startup' below. Only require with cat photo!
   //   Returns: array of item info
   //
   //   name=getInfo
   //     siteCode: required
   //   Returns: array of info form 'appinfo' table and 'segments' table.
   //
   //   name=getAds
   //     siteCode: required
   //   Returns: array of rows
   //
   //   name=getAdsInfo
   //     siteCode: required
   //   Returns: array like getInfo()
   //
   //   name=getBingo
   //     siteCode: required
   //     unit: required
   //   Returns: array like getItem()
   //
   //   name=bingoupdate
   //     siteCode: required
   //     bingoGame: required
   //     inx: required
   //     gameover: required
   //   Returns: 'gameOver'|'bingoupdate OK'
   //
   //   name=startup
   //     siteCode: required
   //     unit: required
   //   Returns: startupId
   //
   //   name=unload
   //     startupId: required
   //   Returns: nothing
   //----------------------------------------------------------------

// New logic for 'vendor' and SITELOADNAME
// The main 'vendor' directory is at /var/www/vendor and the one for myphotochannel.com is at
// /var/www/bartonlp/myphotochannel.com/vendor
// The file /var/www/bartonlp/myphotochannel.com/composer.json has:
/*
{
    "autoload": {
      "classmap": [
        "includes"
      ]
    }
}
*/
// This lets the applications use Pusher.php etc.
// The 'SITELOADNAME' is defined in the .htaccess file at
// /var/www/bartonlp/myphotochannel.com/.htaccess

if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
define(TOP, $_site->path);
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new Database($_site);

// Do we have a siteCode

if(!($siteCode = $_GET['siteCode'])) error($S, "No Site Code");
// Is it valid?
$siteCode = $S->escape($siteCode);
//cout("$siteCode");

if(!$S->query("select * from sites ".
              "where siteCode='$siteCode'")) {
  
  error($S, "Site Code not found: $siteCode");
}

$S->sites = $S->fetchrow('assoc');
$S->sites['siteId'] = $S->escape($S->sites['siteId']);
$S->siteId = $S->sites['siteId'];

$host = $_SERVER['REMOTE_ADDR'];

//file_put_contents("debug.txt", "*******************\n".date("Y-m-d H:i:s") .
//                  " " .$_GET['name']." : $host\n", FILE_APPEND);

switch($_GET['name']) {
  case 'getItem':
    // The server is contacted for the 'photo', 'brand', 'product' and 'info' categories every
    // 'callbackTime' which is defaulted to 50 images which works out to about 6 minutes.
    // For 'feature' and 'announce' the server is contacted
    //'progDurration - (progDuration - frequentCallbackTime). For example if progDuration is 20
    // and frequentCallbackTime is 5 the client will call the server when the show is 15 images in.
    
    // The 'progDuration' (default to 20 images or 2 1/3 minutes) and featuresPer' (the maximum
    // 'number of feature images per show) regulate the show.
    // So we have 50 'photo' images per callback and every 15 images into the slow we callback for
    // 'feature' and 'announce' images. NOTE there may not be any 'feature' or 'announce' images.
    // The client has an arrays of 'photo', 'feature', 'announce' etc images. The 'images' array is
    // indexed by category and each category has an array of images.
    // We check to see what category to use (getCategory()). 

    // Get the call arguments

    $S->type = $_GET['type']; // rand, chron
    $S->num = (int) $_GET['num']; 
    $S->category = $S->retCategory = $_GET['category'];
    $S->startupId = $_GET['startupid'];
    
    if($S->retCategory == 'feature') {
      $S->category = 'photo';
    }
    
    getItem($S);
    break;
  case 'getInfo':
    // Get the info from the addinfo table and return an array
    getInfo($S);
    break;
  case 'getAds':
    getAds($S);
    break;
  case 'getAdsInfo':
    getAdsInfo($S);
    break;
  case 'getBingo':
    $S->unit = $_GET['unit'];
    getBingo($S);
    break;
  case 'bingoupdate':
    $S->bingoGame = $_GET['game'];
    $S->inx = $_GET['inx'];
    $S->gameover = $_GET['gameover'];
    bingoupdate($S);
    break;
  case 'startup':
    $S->unit = $_GET['unit'];
    $S->version = $_GET['version'];
    startup($S);
    break;
  case 'unload':
    $S->startupId = $_GET['startupId'];
    unload($S);
    break;
}
exit();

/*********** Functions ***************/

function getItem($S) {
  // Here is what additional come in $S
  // $S->sites: from the sites table
  // $S->appInfo: from the appinfo 
  // $S->category = database category. If $S->retCategory == 'feature' then this is 'photo'
  // $S->retCategory = $_GET['category'];
  // $S->type = $_GET['type']; // rand, chron
  // $S->num = $_GET['num']
  // $S->siteId = $_GET['siteid'];

//  file_put_contents("debug.txt",
//                    "cat: $S->category, retCat: $S->retCategory\n".
//                    "type: $S->type, num: $S->num\n", FILE_APPEND);
  
  // Get and translate the lifeOfFeature and whenPhotoAged into an absolute datetime.
  
  $sql = "select lifeOfFeature, whenPhotoAged, perRecent from appinfo where siteId='$S->siteId'";
  $S->query($sql);
  list($life, $aged, $perRecent) = $S->fetchrow('num');
  
  $sql = "select date_sub(now(), interval $life minute) as life, ".
         "date_sub(now(), interval $aged) as aged";
  
  $S->query($sql);
  list($life, $aged) = $S->fetchrow('num');
  
  $where = $order = $limit = '';

  $rows = array();

  switch($S->type) {
    case 'rand':
      $order = ' order by rand()';
      break;
    case 'chron':
    default:
      $order = 'order by showTime desc';
      break;
  }
  
  // Switch on retCategory not category. retCategory has 'feature'
  
  switch($S->retCategory) {
    case 'photo':
      // Keep trying until we have gotten the number requested or as many as we can find.

      $num = $S->num; // Total number requested

      // do loop twice, once for recent and once for other

      $per = $perRecent / 100;
      
      for($j=0; $j<2; ++$j) {
        if($j == 0) {
          // First time so do recent
            
          $where = "showTime > '$aged'"; // recent only
          
          if($per != 0) {
            // Get a percent of the total from recent.
            $i = $num * $per; // percent of total
          } else {
            $i = $num; // all we can get from recent
          }
        } else {
          // Second time so depends on if we have a percentage
          $i = $num; // whatever is left in either case
            
          if($per != 0) {
            $where = ''; // use all photos
          } else {
            // Only aged.
            $where = " showTime < '$aged'";
          }
        }

        // If we have a percent then $i is % of $num when $j=0
        // and when $j=1 it is the number to pull from all.
        // If zero percent then $i = number of photos total when $j=0
        // and when $j=1 it is whatever is left to be pulled from aged.
          
        $S->num = (int)$i;

        list($n, $r) = getItems($S, $where, $order);
        $rows = array_merge($rows, $r);
        
        // $n is the number actually retrived.
          
        if($n < $i) {
          // We were not able to get the number we asked for.
          $num -= $n; // so make num the rest if there is another iteration;
        } else {
          if($per != 0) {
            $num -= $i;
          } else {
            break;
          }
        }
      }
      // Randomize the whole thing. Each getItems had random order but the order of the
      // feature/recent/aged was not random.
      
      shuffle($rows);

      // Update startup

      $sql = "update startup set status='open', lasttime=now() where id='$S->startupId'";
      $S->query($sql);

      $app_id = '52258';
      $key = '2aa0c68479472ef92d2a';
      $secret = '86714601dfa6e13a87f7';

      $pusher = new Pusher($key, $secret, $app_id);
      $pusher->trigger("slideshow", "startup-update",
                       array('msg'=>"startup-update",
                             'siteId'=>$S->siteId,
                             'ip'=>$_SERVER['REMOTE_ADDR'],
                             'agent'=>$_SERVER['HTTP_USER_AGENT']));

      break;
    case 'brand':
    case 'product':
    case 'info':
    case 'video':
      list($n, $rows) = getItems($S, $where, $order);
      break;
      
    case 'feature':
      $where = " showTime > '$life'";
      list($n, $rows) = getItems($S, $where, $order);
      break;
    case 'announce':
      list($n, $rows) = getItems($S, $where, $order);
      break;
  }

  $ret = json_encode(array('num'=>count($rows),    // how may rows are we returning
                           'cat'=>$S->retCategory, // retCategory may be 'feature' instead of 'photo'
                           'error'=>"OK",
                           'rows'=>$rows) // has: time, desc, loc, dur, type (or null).
                    );
  echo $ret;
}

// Helper. get all the rows given 'where' and 'order'
  
function getItems($S, $where, $order) {
  $debug = "In getItems\n";
  
  $rows = array();
  
  if($where) $where = " and $where";

  $sql = "select * from items where siteId='$S->siteId' ".
         "and status='active' and category='$S->category'{$where}{$order} limit $S->num";

  $n = $S->query($sql);

  $debug .= "sql=$sql\nn=$n\n";
  
  while($row = $S->fetchrow('assoc')) {
    // Does the image exist on the server?

    if(($row['type'] == 'image' || $row['type'] == 'filehtml' || $row['type'] == 'video') && !file_exists(TOP . "/" . $row['location'])) {
      continue;
    }

    $x = array('itemId'=>$row['itemId'],
               'time'=>$row['showTime'],
               'desc'=>$row['description'],
               'loc'=>$row['location'], // This ends up as <img src='loc'...
               'dur'=>$row['duration'], // image duration. If null then use categories.duration
               'trans'=>$row['transition'],
               'effect'=>$row['effect'],
               'skip'=>$row['skip'],
               'type'=>$row['type']    // 'image', 'html', 'filehtml', 'video'
              );
    $rows[] = $x;
    $debug .= $row['itemId'] . "\n";
  }
//  file_put_contents("debug.txt", $debug, FILE_APPEND);
  return array($n, $rows);
}

// getInfo()
// get the info from the appinfo table and the category table

function getInfo($S) {
  $sql = "select category, cs1, cs2, cs3, cs4, cs5 from segments where siteId='$S->siteId'";
  if(!$S->query($sql)) error($S, "No segments info for site $S->siteId");

  $seg = array('announce'=>array(), 'brand'=>array(), 'product'=>array(), 'info'=>array());
  
  while(list($segCat, $cs1, $cs2, $cs3, $cs4, $cs5) = $S->fetchrow('num')) {
    $seg[$segCat] = array($cs1, $cs2, $cs3, $cs4, $cs5);
  }

  // Get the appinfo data

  $sql = "select lifeOfFeature, whenPhotoAged, ".
         "callbackTime, frequentCallbackTime, progDuration, featuresPer, ".
         "allowAds, allowVideo, playbingo, playLotto, playtrivia, featureExt ".
         "from appinfo where siteId='$S->siteId'";
  
  if(!$S->query($sql)) {
    error($S, "No appinfo for siteId $S->siteId");
  }

  list($life, $aged, $cbTime, $fastCallback, $progDur, $features,
       $allowAds, $allowVideo, $playbingo, $playLotto, $playtrivia, $featureExt) = $S->fetchrow('num');

  if($playbingo) {
    $S->query("select freq, intervals, drawnumber, whenWin ".
              "from playbingo where siteId='{$S->sites['siteId']}'");

    list($S->sites['bingoFreq'], $S->sites['bingoInterval'],
         $S->sites['drawnumber'], $S->sites['bingoWhenWin']) = $S->fetchrow('num');
  }

  if($playtrivia) {
    $S->query("select trivianum, triviaqtime, triviaatime, triviacat, triviafontsize, triviafontstyle ".
              "from playtrivia where siteId='{$S->sites['siteId']}'");

    list($S->sites['trivianum'],
         $S->sites['triviaqtime'],
         $S->sites['triviaatime'],
         $S->sites['triviacat'],
         $S->sites['triviafontsize'],
         $S->sites['triviafontstyle']) = $S->fetchrow('num');
  }

  $sql = "select date_sub(now(), interval $life minute) as life, ".
         "date_sub(now(), interval $aged) as aged";
  
  if(!$S->query($sql)) error($S, "date_sub failed");

  list($life, $aged) = $S->fetchrow('num');

  // Determin if the bar is open or closed.
  // get the values from the sites table
  
  //$S->query("select open, close, emailUserName from sites where siteId='$S->siteId'");
  //list($open, $close, $emailPhotoAddress) = $S->fetchrow('num');

  $appinfo = array('life'=>$life,
                   'aged'=>$aged,
                   'callbackTime'=>$cbTime,
                   'progDur'=>$progDur,
                   'features'=>$features,
                   'fastCallback'=>$fastCallback,
                   'allowAds'=>$allowAds,
                   'allowVideo'=>$allowVideo,
                   'playbingo'=>$playbingo,
                   'playLotto'=>$playLotto,
                   'playtrivia'=>$playtrivia,
                   'featureExt'=>$featureExt
                  );
  
  $sql = "select category, duration, transition, effect from categories where siteId='$S->siteId'";

  if(($n = $S->query($sql)) == 0) {
    error($S, "No recs for siteId=$S->siteId");
  }

  $categories = array();
  
  while(list($cat, $dur, $trans, $effect) = $S->fetchrow('num')) {
    $categories[$cat]= array('dur'=>$dur, 'trans'=>$trans, 'effect'=>$effect);
  }

  $ret = json_encode(array('error'=>'OK',
                           'sites'=>$S->sites,
                           'appInfo'=>$appinfo,
                           'segments'=>$seg,
                           'categories'=>$categories));
  echo $ret;
}

function error($S, $msg) {
  $ret = json_encode(array('error'=>$msg));

  echo $ret;
  exit();
}

// getAds()

function getAds($S) {
  // Global Ad Logic

  $sql = "select adId from adsAccount";
  $nCust = $S->query($sql);

  $ads = array();

  while(list($adId) = $S->fetchrow('num')) {
    $ads[] = $adId;
  }
  
  $num = " limit 100"; // Really this should get everything.

  $adsList = array();
  
  foreach($ads as $ad) {
    $sql = "select itemId, description, duration, location, creationTime, type, ".
           "transition, effect, skip from ads ".
           "where status='active' and adId='$ad' order by creationTime$num"; // order by rand()$num";

    $n = $S->query($sql);

    $result = $S->getResult(); // save result because we will check the blacklist in loop

    $adrows = array();
    
    while(list($itemId, $desc, $dur, $loc, $time, $type, $trans, $effect, $skip) =
      $S->fetchrow($result, 'num')) {

      // Individual Ad ItemId's can be blacklisted from a site. This lets the bar owner select ads
      // that can NOT be displayed at his bar!
      
      $sql = "select * from blacklist where siteId='$S->siteId' and itemId='$itemId'";
      if($S->query($sql)) {
        // Item is blacklisted by this site so skip it
        continue;
      }
      $adrows[] = array('itemId'=>$itemId,
                        'adId'=>$ad,
                        'time'=>$time,
                        'desc'=>$desc,
                        'dur'=>$dur,
                        'trans'=>$trans,
                        'effect'=>$effect,
                        'skip'=>$skip,
                        'type'=>$type,
                        'loc'=>$loc);
    }
    $adsList[] = array('num'=>$n, 'rows'=>$adrows);
  }

  echo json_encode(array('nCust'=>$nCust, 'adsList'=>$adsList));
}

// getAdsInfo()

function getAdsInfo($S) {
  $S->query("select adId from adsAccount order by adId");
  $result = $S->getResult();
  $adsInfo = array();
  $i = 0;
  while(list($id) = $S->fetchrow($result, 'num')) {
    $sql = "select adId, dur, trans, effect, segInfo from adsInfo where adId='$id'";
    $n = $S->query($sql);
    $row = $S->fetchrow('assoc');
    // Now remove the segInfo from row into $x which is still an associative array on one element
    // 'segInfo'
    $x = array_splice($row, 4, 1);
    $x = $x['segInfo'];
    // $x is now the json array of segments. Turn it into a real PHP array.
    $x = json_decode($x);
    // Now put the array into a new element in the $row array called segs
    $row['segs'] = $x;
    // Add the row to $adsInfo
    $adsInfo[$i++] = $row;
  }

  // Turn the whole thing into a json array and return it.
  echo json_encode($adsInfo);
}

// Get Bingo info
// $S->numItems
// Returns array like getItem.

function getBingo($S) {
  // Starting a new game so first set all other games for this siteId & unit to Over.
  // This gets done in slideshow.php at the start also.
  
  $S->query("update bingogames set gameover='yes' where siteId='$S->siteId' and unit='$S->unit'");
  
  $items = array();

  // Calculate the number of photos in the pool as the number of draw * 5/3
  // So draw 30 from a pool of 50 (3/5)

  $numItems = round($S->sites['drawnumber'] * 5/3);
  
  $S->query("insert into bingogames (siteId, unit) values('$S->siteId', '$S->unit')");
  $gameNumber = $S->getLastInsertId();

  $files = glob("bingo*.txt");
  
  foreach($files as $f) {
    $x = json_decode(file_get_contents($f));
    if($x->gameover == 'yes') {
      unlink($f);
    }
  }
  
  $sql = "select itemId, location from items where siteId='$S->siteId' ".
         "and status='active' and category='photo' and type='image' ".
         "order by rand() limit $numItems";

  $S->query($sql);

  $cnt = 0;
  
  while(list($itemId, $location) = $S->fetchrow('num')) {
    // Does the image exist on the server?

    if(!file_exists(TOP . "/" . $location)) {
      continue;
    }

    $items[] = array($itemId, $location);
    ++$cnt;
  }

  foreach($items as $item) {
    $S->query("insert into bingo (itemId, siteId, game, location) ".
              "values('$item[0]', '$S->siteId', '$gameNumber', '$item[1]')");
  }

  array_unshift($items, array(0, "bingostart.php?game=$gameNumber"));
  array_push($items, array(0, "bingoend.php?game=$gameNumber"));

  // if the drawnumber is less the 0.6 of the number of images then force drawnumber to 0.6 rounded
  // up.
  
  if($S->drawnumber < $cnt*3/5) {
    $S->sites['drawnumber'] = round($cnt*3/5);
  }
  
  $ret = json_encode(array($gameNumber, $S->sites['bingoFreq'], $S->sites['drawnumber'], $items));
  echo $ret;
}

// Update the bingogames table and check for winners
// Also update file bingo<gamenumber>.txt

function bingoupdate($S) {
  // send to all playbingo clients. Channel='playbingo' event=newinx
  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';

  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("playbingo", "newinx", array('msg'=>$S->gameover,
                                                'inx'=>$S->inx,
                                                'game'=>$S->bingoGame,
                                                'ip'=>$_SERVER['REMOTE_ADDR'],
                                                'agent'=>$_SERVER['HTTP_USER_AGENT']));

  // Now if the game is over update the database
  
  if($S->gameover == 'yes') {
    $sql = "update bingogames set gameover='yes', inx='$S->inx' where gameNumber='$S->bingoGame'";
    $S->query($sql);
  } 
  exit();
}

// When a site start up
////  CREATE TABLE `startup` (
//   `id` int(11) NOT NULL AUTO_INCREMENT,
//   `siteId` varchar(255) NOT NULL,
//   `unit` int(11) DEFAULT '0',
//   `version` varchar(30) DEFAULT NULL,
//   `status` enum('closed','open') DEFAULT 'open',
//   `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
//   `lasttime` datetime DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=utf8
 
function startup($S) {
  // Defaults are: status=open, starttime=CURRENT_TIMESTAMP, rest are default NULL.
  
  $sql = "insert into startup (siteId, unit, version) values('$S->siteId', '$S->unit', '$S->version')";
  $S->query($sql);
  $id = $S->getLastInsertId();
  echo json_encode(array('id'=>$id));

  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';

  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("slideshow", "startup",
                   array('msg'=>"startup",
                         'siteId'=>$S->siteId,
                         'ip'=>$_SERVER['REMOTE_ADDR'],
                         'agent'=>$_SERVER['HTTP_USER_AGENT']));
    exit();
}

// On slideshow unload.

function unload($S) {
  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';

  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("slideshow", "unload",
                   array('msg'=>"unload",
                         'siteId'=>$S->siteId,
                         'ip'=>$_SERVER['REMOTE_ADDR'],
                         'agent'=>$_SERVER['HTTP_USER_AGENT']));

  //file_put_contents("/tmp/debug.txt", "unload: ". date("Y-m-d H:i:s"));
  $S->query("update startup set status='closed' where id='$S->startupId'");
  exit();
}
