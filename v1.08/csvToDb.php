<?php
// BLP 2014-04-25 -- This program takes a csv file and create database entries for the Cardinals
// baseball team schedule. The csv file can be downloaded from
// http://stlouis.cardinals.mlb.com/schedule/downloadable.jsp?c_id=stl&year=2014 . Just change the
// year to get the current information. Or you can go to
// http://mlb.mlb.com/search/?query=csv+scheule&c_id=mlb to get other teams and schedules.

/*
CREATE TABLE `sportsschedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('nbl','nfl','nbl') COLLATE latin1_general_ci DEFAULT 'nbl',
  `image` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `team` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `location` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
*/

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

if($_GET['page'] == 'mkdb') {
  $S = new Database($dbinfo);
  
  $filename = $_GET['filename'];
  
  $fd = fopen($filename, 'r');
  $cnt = 0;
  fgetcsv($fd);
  while(($data = fgetcsv($fd)) !== false) {
    $date = date("Y-m-d", strtotime($data[0]));
    $time = date("H:i", strtotime($data[1]));
    
    $sql = "insert into sportsschedule (type, image, date, time, team, subject, location) ".
           "values('mlb', 'images/mlb.png', '$date', '$time', 'Cardinals', ".
           "'$data[3]', '$data[4]')";
    //echo "$sql<br>";
    $S->query($sql);
    ++$cnt;
  }
  echo "Inserted $cnt records<br>";
  exit();
}

$S = new Tom;

$h->title = "csv to database";
$h->banner = <<<EOF
<div id="myphotochannelheader">
<a href="http://www.myphotochannel.com">
<img src="images/myphotochannel.png"/></a>
<h1>CSV Schedule to Database</h1>
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
#myphotochannelheader img {
  border: none;
}
.clearlog {
  background-color: red;
  color: white;
  border-radius: 15px;
  -webkit-border-radius: 15px;

}
/*.clearlog:nth-child(2) {
  position: absolute;
}*/
#cuttingedge {
  width: 100%;
  background-color: pink;
  color: black;
  padding: 8px;
  margin-left: -8px;
  border-bottom: 2px groove black;
}
#myphotochannelheader {
  text-align: center;
}
td {
  padding: 5px;
}
.super {
  color: red;
}
.superextra {
  color: green;
}
#posted {
  position: fixed;
  top: 100px;
  left: 200px;
  border: 1px solid black;
  background-color: green;
  color: white;
  padding: 40px;
  z-index: 10;
  border-radius: 15px;
  -webkit-border-radius: 15px;
}
</style>
EOF;

$h->extra = <<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script>
jQuery(document).ready(function($) {
  $("#content").append("<form>Filename: <input type='text' id='filename'/><br><input type='submit' "+
                   "id='filesubmit'/></form>");

  $("#filesubmit").click(function(e) {
    var filename = $("#filename").val();
    $.ajax({
             url: 'csvToDb.php',
             data: { page: 'mkdb', filename: filename },
             dataType: 'text',
             type: 'get',
             success: function(data) {
               console.log(data);
               $("#content").html(data);
             },
             error: function(err) {
               console.log(err);
             }
    });
    return false;
  });
});
</script>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

echo <<<EOF
$top
<div id='content'></div>
$footer

EOF;

?>  