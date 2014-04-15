<?php
// items table information
// THIS USES cpanel/cpanel.top.php so we need to do the full banner and style stuff for the
// header!!!

$nopagetop = $noselect = true;
include("cpanel/cpanel.top.php");

$S->query("select now()");  
list($nytime) = $S->fetchrow('num');

$h->title = "Slide Show Info";
/*$h->banner = <<<EOF
<div id="myphotochannelheader">
<a href="http://www.myphotochannel.com">
<img src="images/myphotochannel.png"/></a>
<h1>Slide Show Information</h1>
</div>
<hr>
EOF;
*/
$h->banner =<<<EOF
<h1>Slide Show Information</h1>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>

<script>
jQuery(document).ready(function($) {
  $(".extrainfo").hide();
  $(".siteinfo").hide();

  // Get the last day info and display it
  var last = new Array;

  $(".photonumbers tr td.numlastday").each(function() {
    last.push($(this).text());
  });

  $(".lastday").each(function(i, v) {
    $(v).html("<p>Photos for the last day: " + last[i]+"</p>");
  });
  
  $(".showmore").click(function() {
    var item = $(this).attr('data-item');

    if(!this.item) {
      $("."+item).show();
      $(this).text("Show Less");
    } else {
      $("."+item).hide();
      $(this).text("Show More");
    }
    this.item = !this.item;
  });

  $(".showsite").click(function() {
    var item = $(this).attr('data-item');

    if(!this.item) {
      $(".site-"+item).show();
      $(this).text("Hide");
      $(".site-"+item).prev().hide();
    } else {
      $(".site-"+item).hide();
      $(this).text("Show");
      $(".site-"+item).prev().show();
    }
    this.item = !this.item;
  });
});
  
</script>

<style>
/*
body {
  background-color: #FCF8DC;
}
#myphotochannelheader h1 {
  margin-top: -20px;
}
#myphotochannelheader {
  text-align: center;
}
#myphotochannelheader img {
  border: none;
}
*/
table {
  border-collapse: collapse;
}
tr {
  border-bottom: 1px solid black;
}
tr:last-of-type {
  border: none;
}
.categories, .segments, .users, .playbingo, .playlotto {
  border: 5px groove gray;
  width: 650px;
}
.categories caption, .segments caption, .users caption, .playbingo caption, .playlotto caption {
  font-weight: bold;
}
.categories tr, .segments tr, .users tr, .playbingo tr, .playlotto tr {
  border: none;
}
.categories td, .categories th, .segments td, .segments th, .users td, .users th, 
.playbingo td, .playbingo th, .playlotto td, .playlotto th {
  padding-left: 10px;
  padding-right: 10px;
  border: 1px solid black;
}
.photonumbers th {
  text-align: left;
}
.photonumbers td {
  padding-left: 20px;
  text-align: right;
}
legend {
  font-weight: bold;
}
</style>
EOF;

$S->setBannerFile(SITE_INCLUDES."/myphotochannelbanner.i.php");

list($top, $footer) = $S->getPageTopBottom($h);

if($_GET['debug'] && $_GET['debug'] == $S->superuser) {
  $sql = "select count(*) from items where status='active'";
  $S->query($sql);
  list($cnt) = $S->fetchrow('num');
// BLP 2014-04-14 -- 
  $sql = "select siteId, allowAds, allowVideo, playbingo, playLotto, perRecent, open, close from sites";
  $rows = $S->queryfetch($sql);
  $n = count($rows);
} else {
  $sql = "select count(*) from items where status='active' && siteId='$siteId'";
  $S->query($sql);
  list($cnt) = $S->fetchrow('num');
// BLP 2014-04-14 --   
  $sql = "select siteId, allowAds, allowVideo, playbingo, playLotto, perRecent, open, close ".
         "from sites where siteId='$siteId'";
  $rows = $S->queryfetch($sql);
  $n = 1;
}

$S->query("select concat(fname, ' ',lname), password from superuser");
$superusers =<<<EOF
<table>
<tr><th>Super Users</th></tr>

EOF;

$sname = '';

while(list($supername, $password) = $S->fetchrow('num')) {
  if($password == $superuser) $sname = "<br>Welcome super user $supername";
  $superusers .= "<tr><td>$supername</td></tr>\n";
}
$superusers .= "</table>\n";

$content = <<<EOF
<p>Number of sites shown: $n<br>
Total number of 'active' items in the <b>items</b> table for the shown sites: $cnt<br>
Numbers are for 'active' items</p>
$superusers
$sname
<hr>

EOF;

foreach($rows as $k=>$v) {
  $site = $v['siteId'];
  $siteId = $S->escape($v['siteId']);
  $openClose = "";
  
  if($v['open'] && $v['close']) {
    $openClose = "Open from <b>{$v['open']}</b> to <b>{$v['close']}</b><br>";
  }

  $sql = "select lifeOfFeature, whenPhotoAged, progDuration, CallbackTime, frequentCallbackTime, ".
         "featuresPer from appinfo where siteId='$siteId'";

  $S->query($sql);
  list($life, $aged, $appdur, $slowCall, $fastCall, $featuresPer) = $S->fetchrow('num');

  $life = "$life minute";
  $sql = "select date_sub(now(), interval $life), date_sub(now(), interval $aged)";
  $S->query($sql);
  list($lifedate, $ageddate) = $S->fetchrow('num');

  $sql = "select category, duration, transition, effect from categories where siteId='$siteId'";
  $S->query($sql);

  $catinfo = "<table class='categories'>\n<caption>From <i>categories</i> Table</caption>\n";
  $catinfo .= "<tr><th>Category</th><th>Duration</th><th>Transition</th><th>Effect</th></tr>\n";
  while(list($cat, $dur, $trans, $effect) = $S->fetchrow('num')) {
    $catinfo .= "<tr><td>$cat</td><td>$dur</td><td>$trans</td>".
                "<td>$effect</td></tr>\n";
  }
  $catinfo .= "</table>\n";

  $sql = "select category, cs1, cs2, cs3, cs4, cs5 from segments where siteId='$siteId'";
  $S->query($sql);
  $seginfo = "<table class='segments'>\n<caption>From <i>segments</i> Table</caption>\n";
  $seginfo .= "<tr><th>Category</th><th>CS1</th><th>CS2</th><th>CS3</th><th>CS4</th><th>CS5</th></tr>\n";
  while(list($cat, $cs1, $cs2, $cs3, $cs4, $cs5) = $S->fetchrow('num')) {
    $seginfo .= "<tr><td>$cat</td><td>$cs1</td><td>$cs2</td><td>$cs3</td>".
                "<td>$cs4</td><td>$cs5</td></tr>\n";
  }
  $seginfo .= "</table>\n";

  $sql = "select concat(fname,' ',lname), status, emailNotify, notifyPhone ".
         "from users where siteId='$siteId'";
  $S->query($sql);
  $userinfo = "<table class='users'>\n<caption>From <i>users</i> Table</caption>\n";
  $userinfo .= "<tr><th>Name</th><th>Status</th><th>Notify</th><th>Text</th><tr>\n";
  while(list($name, $status, $notify, $phone, $carrier) = $S->fetchrow('num')) {
    if(is_null($phone) || $phone == '') {
      $text = "no";
    } else {
      $text = "yes";
    }
    $userinfo .= "<tr><td>$name</td><td>$status</td><td>$notify</td><td>$text</td></tr>\n";
  }
  $userinfo .= "</table>\n";

  $sql = "select freq, intervals, drawnumber, whenWin from playbingo where siteId='$siteId'";
  if($S->query($sql)) {
    $playbingo = <<<EOF
<table class='playbingo'>
<caption>From <i>playbingo</i> Table</caption>
<tr><th>Freq</th><th>Intervals</th><th>Draw Number</th><th>When Win</th></tr>

EOF;

    while(list($freq, $intervals, $drawnumber, $whenWin) = $S->fetchrow('num')) {
      $playbingo .= "<tr><td>$freq</td><td>$intervals</td><td>$drawnumber</td><td>$whenWin</td></tr>\n";
    }
    $playbingo .= "</table>\n";
  }
// BLP 2014-04-14 -- 
  $sql = "select data, expires, game, period, canPlay from playlotto where siteId='$siteId'";
  if($S->query($sql)) {
    $playlotto = <<<EOF
<table class='playlotto'>
<caption>From <i>playlotto</i> Table</caption>
<tr><th>Data</th><th>Expires</th><th>Game</th><th>Period</th><th>Can Play</th></tr>

EOF;
  
    while(list($data, $expires, $game, $period, $canPlay) = $S->fetchrow('num')) {
      $ar = json_decode($data);
      $d = "<ul>";
      foreach($ar as $k=>$val) {
        ++$k;
        $g = $val->game == 1 ? 'play' : 'don\'t play';
        $d .= "<li>Game $k: $g<br>Prize: {$val->prize}</li>";
      }
      $d .= '</ul>';
      $playlotto .= "<tr><td>$d</td><td>$expires</td><td>$game</td><td>$period</td><td>$canPlay</td></tr>\n";
    }
    $playlotto .= "</table>\n";
  }

  $content .=<<<EOF
<h1>$site <button class="showsite" data-item="$siteId">Show</button></h1>
<div class="lastday"></div>
<div class="siteinfo site-$siteId">

<button class="showmore" data-item="button-$siteId">Show More</button>

<div class="extrainfo button-$siteId">
<fieldset id="sitesdiv" style="border: 1px solid black">
<legend>From <i>sites</i> Table</legend>
$openClose
AllowAds: <b>{$v['allowAds']}</b><br>
AllowVideo: <b>{$v['allowVideo']}</b><br>
PlayBingo: <b>{$v['playbingo']}</b><br>
PlayLotto: <b>{$v['playLotto']}</b><br>
PerRecent: <b>{$v['perRecent']}</b><br>
</fieldset>
<fieldset>
<legend>From <i>appinfo</i> Table</legend>
FeaturesPer: <b>$featuresPer</b><br>
Program Duration: <b>$appdur</b> photos, Fast Call: <b>$fastCall</b> photos,
Slow Call: <b>$slowCall</b> photos<br>
lifeOfFeature: &quot;<b>$life</b>&quot; (<i>feature</i>: younger than $lifedate)<br>
whenPhotoAged: &quot;<b>$aged</b>&quot; (<i>recent</i>: younger than $ageddate),
(<i>aged</i>: older than $ageddate)
</fieldset>
$catinfo
$seginfo
$userinfo
$playbingo
$playlotto
</p>
</div>

<table class="photonumbers">
EOF;
  
  foreach(array('All photos'=>'',
                'Added last day'=>'1 day',
                'Added last 2 days'=>'2 day',
                'Added last 3 days'=>'3 day',
                'Added last 4 days'=>'4 day',
                'Added last 5 days'=>'5 day',
                'Added last 6 days'=>'6 day',
                'Added last 7 days'=>'7 day',
                'Added last 14 days'=>'14 day',
                'Added last 30 days'=>'30 day',
                'Added last 60 days'=>'60 day',
                'Added last 90 days'=>'90 day',
                'Added last 120 days'=>'120 day',
                "Feature (last {$life}s)"=>$life,
                "Recent (last {$aged}s)"=>$aged,
                "Aged (older than {$aged}s)"=>$aged) as $m=>$int)
  {
    $d = '';
    if($int) {
      $gtlt = ">=";
      // This comparison must match exactaly with the aged line above!!!
      if($m == "Aged (older than {$aged}s)") {
        $gtlt = "<";
      }
      
      $d = " and creationTime $gtlt date_sub(now(), interval $int)";
    }
    $sql = "select itemId from items where siteId='$siteId'$d and status='active'";
    $n = number_format($S->query($sql));
    if($m == "Added last day") {
      $content .= "<tr><th>$m:</th><td class='numlastday'>$n</td></tr>";
    } else {
      $content .= "<tr><th>$m:</th><td>$n</td></tr>";
    }
  }
  $content .= "</table>\n</div>\n<hr>\n";
}

date_default_timezone_set("America/Denver");
$clienttime = date("Y-m-d H:i:s");

echo <<<EOF
$top
<h4>Times are server time which is EST. The current time in NY is: $nytime<br>
Client time is: $clienttime</h4>
$content
<hr>

<h3>What it all means</h3>

<p>The slide show is made up of two segments: a) The <b>show</b> b) The <b>commercial break</b>.
The <b>show</b> is made of the photos you uploaded and the photos email in by your customers.  The
<b>commercial break</b> is your opertunity to present information you think is important or of
interest to your customers. For example, upcoming events, food or drink specials, hours of
operations, how to send photos to your email address.</p>

<p>The slide show works by requesting information and photos from a server on the Internet. Our
server is at <b>http://go.myphotochannel.com</b>. We ask for information and <b>feature</b> and
<b>announce</b> categories during what we call a <b>fastCall</b>. The information consists of the
<i>Control Panel</i> values discussed below.</p>

<p>Less frequently we ask the server for <b>photo</b>, <b>brand</b>, <b>product</b> and <b>info</b>
categories. We call this a <b>slowCall</b>. The <b>photo</b> category is the <b>show</b> and the
<b>brand</b>, <b>product</b> and <b>info</b> categories are the <b>commercial break</b>.</p>

<p>The frequency of the <b>fastCall</b> and <b>slowCall</b> can be controlled via the <i>Control
Panel</i> under <i>Channel Settings|Segment Settings</i>. The values are in number of photos between
server calls. Therefore the time between server calls is a function of the photo duration and
transition which can also be set via the <i>Control Panel</i>.</p>

<p>When we ask the server for photos the servers selects photos randomly from the pool of photos
(categories <b>feature</b> and <b>photo</b>) based on two criteria: a) <b>Life of Feature</b> b)
<b>When Photo Aged</b>. These values are in minutes and days respectivly and can be set via the
<i>Control Panel</i>. For example, if a customer takes a photo now and sends it to the email address
for your site then that photo is considered a <b>feature</b> for the number of minutes set in
<b>Life of Feature</b>. <b>Feature</b> photos get presidence and are retrieved from the server
during the <b>fastCall</b>. <b>Feature</b> photos are shown before older photos. <b>When Photo
Aged</b> determins when a photo is <b>recent</b> versus <b>aged</b>. When we ask for photos during
the <b>slowCall</b> we ask for a specified number of photos based on the <b>Program Duration</b>. We
first get <b>Recent</b> photos and only if there are not sufficent photos newer than <b>When Photo
Aged</b> do we get older photos.  After all <b>feature</b> photos have been displayed we start
displaying the randomly selected <b>recent</b> and posiblely <b>aged</b> photos. You can use the
<i>Control Panel</i> to control the mix of <b>recent</b> to <b>aged</b> photos.</p>

<p>To tune your sites slide show you can use the information above to determin how to set the
various <i>Control Panel</i> parameters. First, every time we do a <b>slowCall</b> to the server we
get <b>Program Duration</b> number of photos from category <b>photo</b>. <b>Program Duration</b> can
be set via the <i>Control Panel</i>. The number of photos is made up of <b>recent</b> and
<b>aged</b> photos. For example, if you have 48 photos added during the last week and your
<b>Program Duration</b> is set to 50 you would, your <b>When Photo Aged</b> to 15 days, and you see
the &quot; Recent Photos&quot; above is 300, then to get a good mix of <b>recent</b> and <b>aged</b>
photos you could set <b>When Photo Aged</b> to say two or three days. That would give you something
like 20 to 25 <b>recent</b> photos and about 20 to 25 <b>aged</b> photos. You can vary the mix by
changing the value of <b>When Photo Aged</b> and then refreshing this page and looking at the
numbers.</p>

<hr>
$footer
EOF;
  
?>