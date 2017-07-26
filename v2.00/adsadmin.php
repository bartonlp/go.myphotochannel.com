<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

$S = new $_site->className($_site);

// Update the adsInfo table

if($_POST['page'] == 'updateAdsInfo') {
  $dur = $_POST['dur'];
  $trans = $_POST['trans'];
  $effect = $_POST['effect'];
  
  // Not doing effect at this time.
  $freq = $_POST['freq'];
  $num = $_POST['num'];

  // freq and num are ar[adId][seg#]
  
  foreach($freq as $adId=>$v) {
    // $adId is the adId and $v is the segments 'freq' and 'num'
    $ar = array();
    $d = $dur[$adId];
    $t = $trans[$adId];
    $ef = $effect[$adId];
    
    foreach($v as $seg=>$f) {
      // $seg is the segment number 0-5, $f is the freq for that segment
      $n = $num[$adId][$seg]; 
      $ar[] = array('freq'=>$f, 'num'=>$n);
    }
    $segs = json_encode($ar);

    if($S->query("select adId from adsInfo where adId='$adId'")) {
      $sql = "update adsInfo set dur='$d', trans='$t', effect='$ef', segInfo='$segs' where adId='$adId'";
    } else {
      // New record so insert
      $sql = "insert into adsInfo (adId, dur, trans, effect, segInfo) ".
             "values('$adId', '$d', '$t', '$ef', '$segs')";
    }
    //echo "$sql<br>\n";
    $S->query($sql);
  }
  $banner = "<h1>adsInfo update OK</h1>";
  // Fall into rest of page
}

if(!$_GET['debug']) {
  echo "<h1>Sorry Only for Super Users</h1>";
  exit();
} else {
  $debug = $_GET['debug'];
  if(!$S->query("select * from superuser where password='$debug'")) {
    echo "<h1>Super User Code Not Found</h1>";
    exit();
  }
}
    
$options = "<option value=''>Select Customer</option>\n";

$S->query("select siteId, allowAds, allowVideo from appinfo");
$ar = array();
while(list($siteId, $allowAds, $allowVideo) = $S->fetchrow('num')) {
  $ar[$siteId] = array($allowAds, $allowVideo);
  $options .= "<option>$siteId</option>\n";
}

$al = json_encode($ar);

$h->title = "Edit Ads Segments";

$h->banner = <<<EOF
<h1>Edit Ads Segments for Customers</h1>
EOF;

$h->extra =<<<EOF
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script>
jQuery(document).ready(function($) {
  var ar=$al;
  $("#allowselect").change(function(e) {
    $("#allowdiv").show();
    var site = $(this).val();

    if(ar[site][0] == 'yes') {
      $("#allowadsyes").prop("checked", true);
    } else {
      $("#allowadsno").prop("checked", true);
    }
    if(ar[site][1] == 'yes') {
      $("#allowvideoyes").prop("checked", true);
    } else {
      $("#allowvideono").prop("checked", true);
    }
  });
});
</script>
<style>
table {
  width: 50%;
  margin: auto;
  margin-bottom: 20px;
}
th, td {
  padding: 5px;
}

#allowdiv { display: none; }
#allowdiv table, #allowdiv th {
  width: auto;
  text-align: left;
  margin: 0px;
}
h1 {
  text-align: center;
}
input[type='text'] {
  width: 3em;
}
.durtrans {
  width: 50%;
  border: 1px solid black;
}
#submit {
  width: 50px;
  margin: auto;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h, "<hr>");

$S->query("select adId, adContactName, adCompany from adsAccount");
$result = $S->getResult();

$owner = array();
$page = '';

while(list($adId, $name, $company) = $S->fetchrow($result, 'num')) {
  $owner[$adId] = array('name'=>$name, 'co'=>$company);
  $segs = array();
  $durTrans = array();
  $newMsg = "";
  
  if(!$S->query("select dur, trans, effect, segInfo from adsInfo where adId='$adId'")) {
    // Did NOT find an existing adsInfo record for this customer
    $newMsg = "<tr><th colspan='2'>NEW RECORD</th></tr>";
    $durTrans[$adId] = array(5000, 1000, 'fade');
    $segs[$adId] = array((object)array('freq'=>'0', 'num'=>'0'),
                         (object)array('freq'=>'0', 'num'=>'0'),
                         (object)array('freq'=>'0', 'num'=>'0'),
                         (object)array('freq'=>'0', 'num'=>'0'),
                         (object)array('freq'=>'0', 'num'=>'0'),
                         (object)array('freq'=>'0', 'num'=>'0')
                        );
  } else { 
    while(list($dur, $trans, $effect, $segInfo) = $S->fetchrow('num')) {
      $durTrans[$adId] = array($dur, $trans, $effect);
      $segs[$adId] = json_decode($segInfo);
    }
  }

  foreach($segs as $id=>$seg) {
    $opt = <<<EOF
<option>fade</option>
<option>dissolve</option>
<option>pop</option>
EOF;

    $opt = preg_replace("/>{$durTrans[$id][2]}/", " selected='true'>{$durTrans[$id][2]}", $opt);
    $page .= <<<EOF
<table class="durtrans">
<thead>
$newMsg
<tr><th colspan='2'>AdId: $id, Contact Name: {$owner[$id]['name']}, Company: {$owner[$id]['co']}</th></tr>
</thead>
<tbody>
<tr><td>Dur:</td><td><input type='text' name='dur[$id]' value="{$durTrans[$id][0]}"></td></tr>
<tr><td>Trans:</td><td><input type='text' name='trans[$id]' value="{$durTrans[$id][1]}"></td></tr>
<tr><td>Effect:</td><td><select name='effect[$id]'>
$opt
</select>
<tr><td colspan="2">
  <table border="1">
  <tbody>

EOF;
  

    foreach($seg as $k=>$v) {
      $page .=<<<EOF
<tr><td>$k</td><td>freq: <input class="freq" type='text' name='freq[$id][]' value='$v->freq'></td>
<td>num: <input class="num" type='text' name='num[$id][]' value='$v->num'></td>
</tr>
EOF;
    }
      
    $page .= <<<EOF
  </td>
  </tr>
  </tbody>
  </table>
</tbody>
</table>
EOF;
  }
}

echo <<<EOF
$top
<p><b>freq</b> is a skip count. <b>freq</b>=0 means do not skip any Commercial Breaks (CB).
A <b>freq</b>=2 means skip two CB's between the Ads.<br>
<b>num</b> is the number of ads for a customer that will be played during a CB.<br>
So if the <b>freq</b>=2 and the <b>num</b>=2 the CB's would look like this:</p>
<table border="1">
<tr><th>CB1</th><th>CB2</th><th>CB3</th><th>CB4</th><th>CB5</th><th>CB6</th><th>...</th></tr>
<tr><td>skip</td><td>skip</td><td>ad1, ad2</td><td>skip</td><td>skip</td><td>ad1, ad2</td><td>...</td></tr>
</table>
<p>Currently each Customer has six segments. A segment with a <b>freq</b>=0 and <b>num</b>=0 is
a null segment and displays no ads during the CB. A segment with a <b>freq</b>=3 and a <b>num</b>=0 consumes
four CB's without showing any ads, that is three skips and one CB with no ads.</p>
<hr>
<form id="adsInfoForm" method="post">
$page
<div id="submit">
<input id="adsInfoSubmit" type="submit">
<input type="hidden" name="page" value="updateAdsInfo">
<input type="hidden" name="debug" value="$debug">
</div>
</form>
$footer
EOF;
