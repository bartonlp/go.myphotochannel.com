<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$h->extra = <<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<!-- A date() function that works like PHP date(). -->
<script src="js/php-date.js"></script>
<script>
function outputMsg(subscribor, event, data) {
  var tbl = "<tr><td>"+subscribor+"</td><td>"+event+"</td><td>";

  var d = new Date().getTime() / 1000;
  
  for(x in data) {
    tbl += "<b>"+x+"</b>: "+data[x]+"<br>";
  }
  // Format date as yyyy-mm-dd hh:mm:ss
  var datetime = date("Y-m-d H:i:s", d);

  tbl += "<b>date-time</b>: "+datetime;
  tbl += "</td></tr>";

  $("#messages thead").after(tbl);
  return false;
}

jQuery(document).ready(function($) {
  // create table with headers in #messages.

  $("#messages").html("<table border='1'>"+
            "<thead>"+
            "<tr><th>Subscriber</th><th>Event</th><th>Data</th></tr>"+
            "</thead>"+
            "</table>");

  // Define Pusher.log

  Pusher.log = function(message) {
    if(window.console && window.console.log) {
      window.console.log(message);
    }
    $("#log pre").append(message + '<br>');
  };

  $("button").click(function(e) {
    if(this.flag) {
      $("#log").hide();
    } else {
      $("#log").show();
    }
    this.flag = !this.flag;
  });

  // Our Pusher key info.
  var key = '2aa0c68479472ef92d2a';
  var pusher = new Pusher(key);
  var slideshow = pusher.subscribe('slideshow');

  slideshow.bind('pusher:subscription_succeeded', function() {
    console.log("subscribed");
  });

  // Bind to all slideshow events.

  slideshow.bind_all(function(event, data) {
    console.log(event, data);
    outputMsg('slideshow', event, data);
  });
});

</script>
<style>
table {
  width: 100%;
}
table td {
  padding: 10px;
}
table td:first-child {
  width: 5em;
}
table td:nth-child(2) {
  width: 15em;
}
#log { display: none; }
</style>
EOF;

$h->banner = "<h1>Pusher Status</h1>";
list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<p>Most recent at top.</p>
<div id="messages">Messages Go Here:</div>
<button>Show/Hide Pusher Status</button>
<div id="log"><pre></pre></div>
<hr>
$footer
EOF;
?>