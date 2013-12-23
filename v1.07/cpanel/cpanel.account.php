<?php
include("cpanel.top.php");

echo <<<EOF
<div id="account" data-role="page" data-add-back-btn="true" data-theme="a">
	<div data-role="header">
		<h1>Account Maintenance<span></span></h1>
    <a data-rel="panel" href="#account-help" data-inline="true" data-mini="true">Help</a>
		<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->
	<div data-role="content" id="accountcontent">
    <label for="catselect">Select User</label>
    <select id="userselect" name="catselect">
    </select>
    
    <ul data-role="listview" data-inset="true" id="userlist">
    <li>
       <label for="chpass">Change Password <span class="username"></span></label>
       <div id="chpass" data-role="fieldcontain">
       <input data-type='horizontal' type='text' id='newpassword' placeholder='new password'>
       <input data-type='horizontal' type='text' id='confirm' placeholder='re-enter password'>
       </div>
    </li>
    <li>
       <div data-role="fieldcontain">
          <label for="emailaddress">Email address <span class="username"></span></label>
          <input type='text' id='emailaddress'>
       </div>
    </li>
    <li>
       <div data-role="fieldcontain">
          <label for="smsphone">SMS Phone Number (digits only)</label>
          <input type='text' id='smsphone' autocomplete>
       </div>
       <select data-inline="true" id='smsprovider'>
          <option value="">Select SMS Provider</option>
          <option>Verizon</option>
          <option>TMobile</option>
          <option>Sprint</option>
          <option>ATT</option>
          <option>Virgin</option>
       </select>
    </li>
    <li>
       <label for="changestatusgroup">Change User Status <span class="username"></span></label>
       <div id="changestatusgroup" data-role="controlgroup" data-type="horizontal">
       <label for='radiocustomer'>Customer</label>
       <input id='radiocustomer' name='radio' type='radio' value='customer'> 
       <label for='radioadmin'>Admin</label>
       <input id='radioadmin' name='radio' type='radio' value='admin'> 
       <label for='radiomember'>Member/Owner</label>
       <input id='radiomember' name='radio' type='radio' value='member'>
       </div>
    </li>
    <li>
       <label for="changeemailnotify">Change Email Notification</label>
       <div id="changeemailnotify" data-role="controlgroup" data-type="horizontal">
       <label for='radioemailno'>No</label>
       <input id='radioemailno' name='radio1' type='radio' value='no'> 
       <label for='radioemailyes'>Yes</label>
       <input id='radioemailyes' name='radio1' type='radio' value='yes'> 
       </div>
       <label for="changetextnotify">Change Text Notification</label>
       <div id="changetextnotify" data-role="controlgroup" data-type="horizontal">
       <label for='radiotextno'>No</label>
       <input id='radiotextno' name='radio2' type='radio' value='no'>
       <label for='radiotextyes'>Yes</label>
       <input id='radiotextyes' name='radio2' type='radio' value='yes'>
       </div>
    </li>

    <li><button id='accountOK'>Submit</button></li>

    <li><a href="cpanel.newuser.php?siteId=$siteId">Add New User</a></li>
    </ul>
     
    <div id="notowner" data-role="popup" data-theme="e">
       <p>Only the Owner of this site can use this page</p>
    </div>
	</div><!-- /content -->
  <div data-role="popup" id="badpassword" data-theme="b">
     <p>Your password did not match please try again"</p>
  </div>
  
  <div data-role="panel" id="account-help" data-theme="b">
     <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.account.js"></script>-->
</div><!-- /page -->
</body>
</html>
EOF;
?>
