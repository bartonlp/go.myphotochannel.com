<?php include("cpanel.top.php");
echo <<<EOF
<!-- Add New User  -->

<div id="newuser" data-role="page" data-add-back-btn="true" data-theme="a">
	<div data-role="header">
    <a data-rel="panel" href="#newuser-help" data-inline="true" data-mini="true">Help</a>

		<h1>Add a New User<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->
  <div data-role="content" id="newusercontent">	
  <p>Add information for your new admin.</p>
  <p><input type="text" id="fname" placeholder="First Name" autofocus> 
     <input type="text" id="lname" placeholder="Last Name">
     <input type="text" id="password" placeholder="Password"> 
     <input type="text" id="email" placeholder="Email Address">
     <input type="text" id="smsphone" placeholder="SMS Phone number (only digits)">
     <select id="smsprovider" data-inline="true">
        <option value="">Select SMS Provider</option>
        <option>Verizon</option>
        <option>TMobile</option>
        <option>Sprint</option>
        <option>ATT</option>
        <option>Virgin</option>
     </select>
     <button id="newadminsubmit">Submit</button>
  </p>
  <div id="createOK" data-role="popup" data-theme="e">
     <p>New Admin Created OK</p>
  </div>
	</div><!-- /content -->
    <div data-role="panel" id="newuser-help" data-theme="b">
    <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.newuser.js"></script>-->
</div><!-- /page -->

</body>
</html>
EOF;
?>