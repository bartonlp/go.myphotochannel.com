<?php
include("cpanel.top.php");
   
// Login if not userId.

echo <<<EOF
<div id="login" data-role="page" data-theme="b">
  <div data-role="header">
    <h1>User Login</h1>
  </div><!-- /header -->
  <div data-role="content"> 
  <h2>Email Address: <input type="email" id="loginemailaddress" name="loginemailaddress" autofocus><br>
  Password: <input type="password" id="loginpassword" name="loginpassword"></h2>
  <button id="loginsubmit">Submit</button>

  <div id="error"></div>

  </div><!-- /content -->
  <div data-role="footer">
    <h4>&#169 2013 myphotochannel</h4>
  </div><!-- /footer -->
</div><!-- /page -->
</body>
</html>
EOF;
?>