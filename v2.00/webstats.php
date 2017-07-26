<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new $_site->className($_site);
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
<h1>Web Stats for myphotochannel.com</h1>
EOF;

$h->css =<<<EOF
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
$footer
EOF;
