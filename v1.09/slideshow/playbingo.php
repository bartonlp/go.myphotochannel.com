<?php
// Play Bingo Game

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

// Ajax

// Check to see if you won

if($_GET['page'] == "checkwin") {
  $S = new Database($dbinfo);

  // The photo ids are past as five numbers seperated by commas
  
  $numbers = explode(",", rtrim($_GET['numbers'], ','));
  $siteId = $_GET['siteId'];
  $email = $_GET['email'];
  $game = $_GET['game'];
  $inx = $_GET['inx'];
  
  $S->query("select whenWin from playbingo where siteId='$siteId'");
  list($win) = $S->fetchrow('num');

  $num = $inx+1;

  $sql = "select itemId, location from bingo where siteId='$siteId' and game='$game' limit $num";

  $n = $S->query($sql);

  //file_put_contents("stuff.txt", "$sql\n", FILE_APPEND);
  
  $cnt = 0;
  $hits = array();
  
  while(list($itemId, $loc) = $S->fetchrow('num')) {
    $hit = false;
    if(in_array($itemId, $numbers)) {
      ++$cnt;
      $hit = true;
    }
    $hits[] = array('itemId'=>$itemId, 'loc'=>$loc, 'hit'=>$hit);
  }
  //file_put_contents("stuff.txt", "loc=$loc, hits=".print_r($hits,true)."\n", FILE_APPEND);

  if($cnt < $win) {
    // Not found so your are a loser.
    if($gameover == 'yes') {
      echo json_encode(array('msg'=>"Game Over! You did not win but please play the next game.",
                             'hits'=>$hits));
    } else {
      echo json_encode(array('msg'=>"Sorry you have not won yet, but keep playing, ".
                                   "you have $cnt so far. $num photos shown so far.",
                             'hits'=>$hits));
    }
    exit();
  }

  echo json_encode(array('msg'=>"You are a winner. You have been sent an email coupon. ".
                   "Present the coupon to the bartender ".
                   "to redeam your prize. Congratulation, and play again.",
                   'hits'=>$hits));

  $msg = <<<EOF
Show this to the bartender. You are a winner of game #$game. You identified $win out of $num photos.
EOF;

  mail("$email", "Your are a winner", $msg, null, "-fbartonphillips@gmail.com");

  // add winners to bingowinners table
  $sql = "update bingoplayers set points=100 ".
         "where siteId='$siteId' and game='$game' and email='$email'";

  $S->query($sql);
  
  $S->query("update bingogames set gameover='yes', inx='$inx' where gameNumber='$game'");

  // Tell the slideshow

  require('websocket/vendor/pusher/pusher-php-server/lib/Pusher.php');
  $app_id = '52258';
  $key = '2aa0c68479472ef92d2a';
  $secret = '86714601dfa6e13a87f7';
  $pusher = new Pusher($key, $secret, $app_id);
  $pusher->trigger("slideshow", "gameover", array('game'=>$game, 'gameover'=>'yes', 'inx'=>$inx));
  exit();
}

// Ajax
// Get the game card

if($_GET['page'] == 'getgame') {
  $S = new Database($dbinfo);

  $siteId = $_GET['siteId'];
  $game = $_GET['game'];
  $email = $_GET['email'];
  
  $S->query("select whenWin, drawnumber from playbingo where siteId='$siteId'");
  list($win, $draw) = $S->fetchrow('num');
  $pool = (int)($draw * 5/3);

  $rules = <<<EOF
<h2>Rules</h2>
<p>When a <b>Photo Bingo</b> Photo is shown, match the photo to one of the
photos on your <b>Photo Bingo</b> screen. When you have $win images selected Submit your Bingo Card.</p>
<p>Your bingo card photos are selected at random from the $pool photos that will be displayed during
the <b>Photo Bingo</b> game. The game shows $draw photos. If no one matches then the game ends
without a winner.</p>
EOF;
    // Check if the game is over

  if(!$S->query("select gameover from bingogames where gameNumber='$game'")) {
    echo json_encode("Sorry but game $game is not a valid game number!");
    exit();
  } else {
    list($gameover) = $S->fetchrow('num');
    if($gameover == 'yes') {
      echo json_encode("Sorry but game $game is over!");
      exit();
    }
  }

  $S->query("insert ignore bingoplayers (siteId, game, email, points, datetime) ".
            "values('$siteId', '$game', '$email', 0, now())");
  
  $S->query("select itemId, location from bingo ".
            "where siteId='$siteId' and game='$game' order by rand() limit 9");

  $items = array();
  
  while(list($itemId, $location) = $S->fetchrow('num')) {
    $items[] = array($itemId, $location);
  }
  echo json_encode(array('items'=>$items, 'rules'=>$rules));
  exit();
};

// Ajax

if($_GET['page'] == 'doSql') {
  $S = new Database($dbinfo);
  
  $sql = $_GET['sql'];
  if($sql == '') {
    echo "NO SQL"; exit();
  }

  //echo $sql;

  $n = $S->query($sql);
  if(strpos($sql, 'select') !== false) {
    while($row = $S->fetchrow('assoc')) {
      $rows[] = $row;
    }
    echo json_encode(array('num'=>$n, 'rows'=>$rows));
    exit();
  }
  echo json_encode($n);
  exit();
}

// Main Page

$S = new Tom;

$sql = "select siteId from sites where playbingo='yes'";
if(!$S->query($sql)) {
  echo "NO SITES ARE PLAYING BINGO";
  exit();
}

$options = "";

while(list($siteId) = $S->fetchrow('num')) {
  $options .= "<option>$siteId</option>\n";
}

$h->title = "Photo Bingo Game";
$h->banner = <<<EOF
<div id="myphotochannelheader">
<a href="http://www.myphotochannel.com">
<img src="/images/myphotochannel.png"/></a>
<h1>Photo Bingo Game</h1>
</div>
<hr>
EOF;

$h->link =<<<EOF
<style>
body {
  background-color: #FCF8DC;
}
.my {
  font: italic bold 25px arial, sans-serif;
}
.photochannel {
  font: bold 35px Arial, Verdana, sans-serif;
  letter-spacing: -2px;
}
#myphotochannelheader h1 {
  margin-top: -20px;
}
#myphotochannelheader {
  text-align: center;
}
.row {
  display: -webkit-flex;
  -webkit-flex-flow: row wrap;
}
.box {
  width: 110px;
  height: 110px;
  border: 1px solid black;
  background-color: tomato;
  padding: 2px;
}
#email {
  width: 200px;
}
#submit {
  display: none;
  margin: auto;
  margin-top: 10px;
  margin-left: 5px;
  background-color: green;
  color: white;
  -webkit-border-radius: 15px;
  width: 200px;
  height: 100px;
}
img {
  margin-top: 5px;
  margin-left: 5px;
  width: 100px;
  max-height: 100px;
}
#card {
  position: relative;
}
#messages {
  display: none;
  position: fixed;
  top: 5px;
  left: 8px;
  width: 298px;
  padding: 5px;
  background-color: white;
  border: 4px solid black;
}
#hits {
  display: none;
  margin-top: 10px;
  width: 350px;
}
#showrules {
  display: none;
}
</style>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script src="js/playbingo.js"></script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<div id="firstup">
<hr>
<div id="email-code">
<form>
<table>
<tr>
<td>Email Address:</td><td><input type="text" id="email" autofocus/></td></tr>
<td>Site:</td><td><select id='siteId'>
$options
</select></td></tr>
<tr><td>Game Number:</td><td><input type="text" id='game'/></td></tr>
<tr><td>Auto Play:</td><td><input id="autoplay" type='checkbox' checked/></td></tr>
</table>

<button type="submit" id='ok'>GO</button><hr>
</form>
</div>
</div>

<div id="card">
<button id="showrules">Show Rules</button>
<div id="rules"></div>

<form>
<div id="row1" class="row">
<div class="box"></div>
<div class="box"></div>
<div class="box"></div>
</div>
<div id="row2" class="row">
<div class="box"></div>
<div class="box"></div>
<div class="box"></div>
</div>
<div id="row3" class="row">
<div class="box"></div>
<div class="box"></div>
<div class="box"></div>
</div>
<br>
<button type='submit' id='submit'>Submit Card</button>
</form>
<div id="hits"></div>
<div id="messages"></div>
</div>
<hr>
$footer
EOF;
?>