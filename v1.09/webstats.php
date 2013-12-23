<?php
// webstats.php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$t = new dbTables($S); // make tables logic

if(!$S->query("select fname from superuser where password='{$_GET['debug']}'")) {
  echo "<h1>Only For super users</h1>";
  exit();
}

$h->extra = <<<EOF
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script>
jQuery(document).ready(function($) {
  // clicked on a td. Is it a ID and if so show the name of the id

  $("td.id").hover(function(e) {
    var id = $(this).text();
    if(id == 0) return false;

    $("#users td.id").each(function(i, v) {
      if($("#username").css("display") != 'none') {
        $("#username").hide();
        return false;
      }
      if($(v).text() == id) {
        var name = $(v).next().text();
        $("#username").css({ top: e.pageY -50, left: e.pageX -30 }).show().html(name);
        return false;
      }
    });
    return false;
  });

  // Add buttons Show/Hide to every <h2>

  $("div h2").append(" <button>Show Table</button>");

  // Selectively show tables

  $("button").click(function(e) {
    // <div id="users">
    // <h2>Table One: from table <i>users</i></h2>
    // <table
    var div = $(this).parent().parent();

    if(this.flag) {
      $("table", div).hide();
      $(this).html("Show Table");
    } else {
      $("table", div).show();
      $(this).html("Hide Table");
    }

    this.flag = !this.flag;
  });
});
  </script>

EOF;

$h->banner = <<<EOF
<h1>Web Stats for go.myphotochannel.com</h1>
EOF;

$h->link =<<<EOF
<style>
#username {
        display: none;
        border: 1px solid black;
        position: absolute;
        padding: 5px;
        background-color: white;
}
div, td {
  padding: 10px;
}
table {
  display: none;
}
</style>

EOF;


$h->title = "Web Statistics";

list($top, $footer) = $S->getPageTopBottom($h, "<p>Return to <a href='index.php'>Home Page</a></p>\n<hr/>");

// Callback function for mktable()

function giveIdClass(&$row, &$rowdesc) {
  if($row['ID']) {
    $rowdesc = preg_replace("~<td>ID</td>~", "<td class='id'>{$row['ID']}</td>", $rowdesc);
  }
}

$query = "select id as ID, concat(fname, ' ', lname) as Name, siteId as SiteId, " .
         "status as Status, emailNotify, email as Email, notifyPhone, notifyCarrier ".
         "from users order by id";

list($tbl) = $t->maketable($query,
  array(callback=>giveIdClass, attr=>array(id=>"users", border=>"1")));

date_default_timezone_set('America/New_York');
$nytime = date("Y-m-d H:i:s");

echo <<<EOF
$top

<p>By placing the cursor over the ID you will see the user name.</p>
<p>All date-times are for the server in New York. Current date-time in New York: $nytime.</p>
<span id='username'></span>

<div id="users">
<h2>Table One: from table <i>users</i></h2>
$tbl
</div>

EOF;

echo <<<EOF
<ul>
   <li><a href="#table2">Goto Table Two: ip, agent</a></li>
   <li><a href="#table3">Goto Table Three: counter</a></li>
   <li><a href="#table4">Goto Table Four: daycounts</a></li>
   <li><a href="#table5">Goto Table Five: memberpagecnt</a></li>
</ul>   

EOF;

$num = $S->query("select sum(count) as visits from logip");
list($visits) = $S->fetchrow('num');
$ftr = "<tr><th colspan='3'>Total Records: $num Total Visits: $visits</th></tr>\n";

$query = "select ip as IP, id as ID, sum(count) as Count, lasttime as Last from logip ".
         "group by ip,id order by lasttime desc";

list($tbl) = $t->maketable($query,
  array(attr=>array(id=>"logip", border=>"1"), footer=>$ftr));

echo <<<EOF
<div id="table1">
<h2>Table One: from table <i>logip</i></h2>
$tbl
</div>

EOF;

$query = "select ip as IP, id as ID, agent as Agent, sum(count) as Count, ".
         "lasttime as Last from logagent ".
         "group by agent, ip, id order by lasttime desc";

/*$query = "select ip as IP, id as ID, agent as Agent, " .
         "lasttime as Last from logagent ". //as agent left join logip as ip on agent.ip=ip.ip ".
         "group by agent order by lasttime desc";
*/
list($tbl) = $t->maketable($query,
                           array(callback=>giveIdClass, attr=>array(id=>"logagent", border=>"1")));

echo <<<EOF
<br>
<div id="table2">
<a name="table2"></a>
<a href="#table3">Goto Table3</a>
<h2>Table Two: from tables <i>logagent</i></h2>
$tbl
</div>

EOF;

$query = "select filename as Page, count as Count, lasttime as Last from counter order by lasttime desc";

list($tbl) = $t->maketable($query, array(attr=>array(id=>"counter", border=>"1")));
echo <<<EOF
<br>
<div id="table3">
<a name="table3"></a>
<a href="#table4">Goto Table4</a>
<h2>Table Three: from <i>counter</i></h2>
$tbl
</div>

EOF;
$query = "select count(*) as Visitors, sum(count) as Count, sum(visits) as Visits ".
         "from daycounts ".
         "order by lasttime desc";

$S->query($query);
list($Visitors, $Count, $Visits) = $S->fetchrow('num');
$S->query("select date from daycounts order by date limit 1");
list($start) = $S->fetchrow('num');

$ftr = "<tr><th>Totals</th><th>$Visitors</th><th>$Count</th><th>$Visits</th></tr>";

$query = "select date as Date, count(*) as Visitors, sum(count) as Count, sum(visits) as Visits
from daycounts group by date order by date desc";

list($tbl) = $t->maketable($query, array(footer=>$ftr, attr=>array(border=>"1", id=>"daycount")));

echo <<<EOF
<br>
<div id="table4">
<a name="table4"></a>
<a href="#table5">Goto Table5</a>
<h2>Table Four: from <i>daycounts</i></h2>
$tbl
</div>

EOF;

$query = "select page as Page, ip as IP, agent as Agent, id as ID, count as Count, lasttime as Last ".
         "from memberpagecnt ".
         "order by lasttime desc";

list($tbl) =  $t->maketable($query, array(callback=>giveIdClass, attr=>array(border=>"1", id=>"memberpagecnt")));

echo <<<EOF
<br>
<div id="table5">
<a name="table5"></a>
<h2>Table Five: from <i>memberpagecnt</i></h2>
$tbl
</div>
$footer
EOF;

?>
