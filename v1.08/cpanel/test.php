<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

// Ajax gettable

$S = new Tom;
$siteId = 'Site-Demo';

$h->extra = <<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script>
var siteId='$siteId';

jQuery(document).ready(function($) {
  $("body").on("click", "button", function(e) {
    // If there is userId in then update visits in users

    // loop through the tdapproveinput <td> and collect the radio
    // button info. The radio button values are 'yes' and 'no'

    var emails = new Array;

    $(".tdapproveinput input:checked").each(function(i, v) {
      var sql;
      var vv = $(v);
      var parent = vv.parents("tr");

      var val = vv.val(); // yes or no
      // The values are "approved-$id" so we want what is after the -,
      // the image Id.

      var id = v.name.match(/.*-(.*)/)[1];

      // val can only be yes or no because one of the two radios was
      // checked.

      if(val == 'no') {
        // no means 'inactive'
        sql = "update items set status='inactive' where itemId='"+id+"' and siteId='"+siteId+"'";
      } else if(val == 'yes') {
        // yes means 'active' and set the creation time to now to make
        // it a feature.

        var ext = parent.attr("data-ext");
        var email = parent.attr("data-email");

        if(ext != "no") {
          emails.push({ext: ext, email: email});
        }

        sql = "update items set status='active', creationTime=now() where itemId='"+id+"' and siteId='"+siteId+"'";
      }

      // Send the sql to the Ajax program

      console.log("update", sql);
      // Get the containing row <tr> and remove it.

      var ext = $(v).parents("tr").attr("data-ext");
      var email = $(v).parents("tr").attr("data-email");
      console.log("ext: %s, email: %s", ext, email);
    });
    console.log("emails", emails);
    $(emails).each(function(i, v) {
      if(v.ext != 'no') {
        var ar = v.ext.split(",");
        var order = "showTime";
        if(ar[0] == 'rand') {
          order = "rand()";
        }
        sql = "update items set showTime=now() "+
              " where siteId='"+siteId+
              "' and creatorName like('%"+v.email+
              "%') and showTime > date_sub(now(), interval "+ar[1]+
              " day) order by "+order+
              " limit 3";
        console.log("sql", sql);
      }
    });
  });
});
</script>

EOF;
$h->banner = "<h1>Test</h>";
list($top, $footer) = $S->getPageTopBottom($h);

$sql = "select featureExt from sites where siteId='$siteId'";
$S->query($sql);
list($ext) = $S->fetchrow('num');
  
$sql = "select itemId, location, creatorName from items where status='new' and siteId='$siteId'";
if($S->query($sql) == 0) {
  echo "No New Photos Found";
  exit();
}

$tbl = '';
  
while(list($id, $loc, $name) = $S->fetchrow('num')) {
  $location = SITE_DOMAIN . "/$loc"; // This is http://...

  $email = "";
    
  if(preg_match("/(?:\w*\s+\w*\s+(?:&lt;)?)?(\w+@\w+\.\w+)(?:&gt;)?/", $name, $m)) {
    $email = $m[1];
  }

  $tbl .= <<<EOF
<tr data-ext='$ext' data-email='$email'>
<td><img id='$id' src='$location' width='300'></td>
<td class='tdapproveinput'>
Approve <input type='radio' class="approveyes" name='approve-$id' value='yes'>
Disapprove <input type='radio' class="approveno" name='approve-$id' value='no'>
</td>
</tr>

EOF;
}

$tbl = <<<EOF
<table id="approvephotostable" border="1">
$tbl
</table>
EOF;

echo <<<EOF
$top
<button>Submit</button><br>
<div id="approvephotoshere">
$tbl
</div>
$footer
EOF;
?>