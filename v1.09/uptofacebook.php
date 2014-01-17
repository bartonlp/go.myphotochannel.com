<?php
// BLP 2014-01-17 -- Upload photos to facebook

define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

// Ajax to send to facebook

if($_GET['page'] == 'upload') {
  require_once 'facebook/facebook.php';

  $config = array(
                  'appId' => '164508790539',
                  'secret' => 'bb56f8200a49ac641fe45870fdb75e37',
                  'fileUpload' => true, // optional
                  'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
                 );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();

  $photo = SITE_ROOT . "/{$_GET['image']}";
  
  $message = 'Photo upload via the PHP SDK!';
  
  if($user_id) {
    // We have a user ID, so probably a logged in user.
    // If not, we'll get an exception, which we handle below.
    try {
      // Upload to a user's profile. The photo will be in the
      // first album in the profile. You can also upload to
      // a specific album by using /ALBUM_ID as the path 
      $ret_obj = $facebook->api('/10203151178488281/photos', 'POST',
                                array('source' => '@' . $photo, 'message' => $message,)
                               );
      $url = $facebook->getLogoutUrl();
      echo <<<EOF
Photo ID: {$ret_obj['id']}<br>
<br /><a href="$url">logout</a>
EOF;
    } catch(FacebookApiException $e) {
      // If the user is logged out, you can have a 
      // user ID even though the access token is invalid.
      // In this case, we'll get an exception, so we'll
      // just ask the user to login again here.
      $login_url = $facebook->getLoginUrl(array('scope' => 'photo_upload')); 
      echo 'Please <a href="' . $login_url . '">login.</a>';
      error_log($e->getType());
      error_log($e->getMessage());
    }   
  } else {
    // No user, print a link for the user to login
    // To upload a photo to a user's wall, we need photo_upload  permission
    // We'll use the current URL as the redirect_uri, so we don't
    // need to specify it here.
    $login_url = $facebook->getLoginUrl( array('scope' => 'photo_upload'));
    echo 'Please <a href="' . $login_url . '">login.</a>';
  }

  exit();
}

$s->bannerFile = SITE_INCLUDES."/myphotochannelbanner.i.php";
$S = new Tom($s);

$h->title = "Up To Facebook";
$h->banner = "<h1>Up To Facebook</h1>";
$h->extra = <<<EOF
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>
jQuery(document).ready(function($) {
  $("img").click(function() {
    var image = $(this).attr("src");
    $.ajax({
      url: '$S->self',
      data: {page: 'upload', image: image},
      type: 'get',
      success: function(data) {
        console.log("DATA", data);
        if(/^Please/.test(data) == true) {
          $("#message").html(data).show();
          return false;
        }
        $("#message").html(data).show();
        return false;
      },
      error: function(err) {
        console.log("ERR", err);
      }
    });
  });
});
</script>
<style>
#message {
  display: none;
  position: absolute;
  top: 10px;
  left: 20px;
  width: 400px;
  border: 3px solid black;
}
</style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

$sql = "select location from items where resized='yes' and siteId='Site-Demo' and status='active' ".
       "and category='photo' and type='image'";

$S->query($sql);

$tbl = "<table>";
$i = 0;
while(list($loc) = $S->fetchrow('num')) {
  if($i == 0) {
    $tbl .= "<tr>";
  }
  $tbl .= "<td><img width='150' src='/$loc'></td>";
  if($i++ > 4) {
    $i = 0;
    $tbl .= "</tr>\n";
  }
}
$j = $i;
while($j++ < 5) {
  $tbl .= "<td></td>";
}
if($i < 5) {
  $tbl .= "</tr>";
}
$tbl .= "</table>";

echo <<<EOF
$top
$tbl
<div id="message"></div>
</body>
</html>
EOF;
?>