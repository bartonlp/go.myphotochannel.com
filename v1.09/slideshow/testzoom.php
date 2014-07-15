<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Tom;

$h->extra =<<<EOF
<!-- include CSS -->
<link rel="stylesheet" href="css/slideshow.css">
<!-- include jQuery library -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script>

jQuery(document).ready(function($) {
  var i=0;

  x(i);

  function x(a) {
    $("#photoemailaddress").html("<img src='/content/100"+ a +".jpg'/>");
    var w;
    if(i % 2) {
      $("img").css('width', '100%');
      w = "70%";
    } else {
      w = "100%";
    }

    $("img").hide().fadeIn(1000, function() {
      $("img").animate({width: w}, 5000, function() {
        $("img").fadeOut(1000, function() {
          x(i % 10);
        })
      });
    });
    ++i;
  }
});
</script>
EOF;

$top = $S->getPageTop($h);

// Main Page
echo <<<EOF
$top
<div id='show'>
</div>
<div id='photoemailaddress'></div>
</body>
</html>
EOF;



?>
