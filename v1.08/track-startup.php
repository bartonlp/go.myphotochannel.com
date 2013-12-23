<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

// Ajax

if($_GET['name'] == 'gettable') {
  $S = new Database($dbinfo);
  $sql = "select id, siteId, unit, version, status,".
         "convert_tz(starttime, '-4:00', '-5:00'), ".
         "convert_tz(lasttime, '-4:00', '-5:00') as last, ".
         "timediff(lasttime, starttime) from startup ".
         "order by last";
  
  $S->query($sql);
  while(list($id, $siteId, $unit, $version, $status, $starttime, $lasttime, $run) = $S->fetchrow('num')) {
    $tbl .= "<tr><td>$siteId</td><td>$unit</td><td>$version</td>".
            "<td class='status'>$status</td><td>$starttime</td><td>$lasttime</td><td>$run</td></tr>";
  }

  if(empty($tbl)) {
    $tbl = <<<EOF
<h2>Sites Status OPEN or active today (v1.05 or later)</h2>
<table border='1'>
<tr><th>No Sites Running At This Time.</th></tr></table>
EOF;
  } else {
    $tbl = <<<EOF
<h2>Sites Status OPEN or active last two days (v1.05 or later)
<span style="font-size: 10px">Ordered by 'Last'</span></h2>

<table border='1'>
<thead>
<tr><th>Site</th><th>Unit</th><th>Version</th>
<th>Status</th><th>Start</th><th>Last</th><th>Run Time</th></tr>
</thead>
<tbody>
$tbl
</tbody>
</table>
EOF;
  }

  echo $tbl;
  exit();
}

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

// update the table by looking at the lasttime field and if it isn't today and the status is 'open'
// then mark the field closed

$sql = "update startup set status='closed' where status='open' ".
       "&& (lasttime < (now() - interval 30 minute) || ".
       "lasttime is null && starttime < (now() - interval 30 minute))";

$S->query($sql);

$h->title = "Track Startup";
$h->banner = <<<EOF
<h1>Track Startup</h1>
EOF;

$h->link =<<<EOF
<style>
table {
  width 100%;
  margin: auto;
}
td, th {
  padding: 5px;
}
/* the "Run Time" field */
td:nth-of-type(7) {
  text-align: center;
}
</style>
EOF;

$h->extra =<<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script>
var showclosed;

function getTable(callback) {
  $.ajax({
    url: 'track-startup.php',
    type: 'get',
    dataType: 'html',
    data: { name: 'gettable' },
    success: function(data) {
      console.log(data);
      $("#table").html(data);

      if(showclosed === false) {
        $(".status:contains('closed')").parent().hide();
      }
      if(typeof callback === 'function') return callback();
    }
  });
}

jQuery(document).ready(function($) {
  getTable(function() {
    $(".status:contains('closed')").parent().hide();
    showclosed = false;
  });

  Pusher.log = function(message) {
    if (window.console && window.console.log) {
      window.console.log(message);
    }
  };

  $("button").click(function() {
    if(showclosed) {
      // hide
      $(".status:contains('closed')").parent().hide();
      $("button").html("Show All");
      showclosed = false;
    } else {
      $(".status:contains('closed')").parent().show();
      $("button").html("Show Open Only");
      showclosed = true;
    }
  });

  // Our key
  var key = '2aa0c68479472ef92d2a';
  var pusher = new Pusher(key);
  var slideshow = pusher.subscribe('slideshow');

  slideshow.bind('startup', function(data) {
    console.log('startup', data);
    getTable();
  });

  slideshow.bind('startup-update', function(data) {
    console.log('startup-update', data);
    getTable();
  });

  slideshow.bind('unload', function(data) {
    console.log('unload', data);
    getTable()
  });
});
</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);
date_default_timezone_set('US/Central');
$tz = date('T (O \G\M\T)');
echo <<<EOF
$top
<p>Note: versions before 1.04 do not show.<br>
Times are $tz.</p>
<button>Show All</button>
<div id="table"></div>
<hr>
$footer
EOF;
?>