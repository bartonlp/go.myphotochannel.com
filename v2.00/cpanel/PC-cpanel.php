<?php
// This is NOT the cpanel version, this is the PC version!

include("PC-cpanel.top.php");

// **********************
// Render Page
// **********************

echo <<<EOF
<div id="PCadminpanel" data-role="page" data-theme="e">
	<div data-role="header">
    <a data-role="button" href="#admin-help" data-inline="true" data-mini="true">Help</a>
		<h1>Items Table<span></span></h1>
	</div><!-- /header -->

  <div data-role="content" id="adminpanelcontent">	

<p>Click on the Image to rotate it 90 degrees counter clockwise.
To turn the image 270 degrees click three times.</p>
<hr>

    <div  class="allNone">
<div data-role="fieldcontain">
<label for="catselect">Select Category to Diaplay</label>
<select id="catselect" class='category'>
<option>photo</option>
<option>announce</option>
<option>brand</option>
<option>product</option>
<option>info</option>
</select>
</div>
<div data-role="fieldcontain">
<label for="statusselect">Show Status</label>
<select id="statusselect" class='showstatus'>
<option>All</option>
<option selected>active</option>
<option>inactive</option>
<option>delete</option>
<option>new</option>
</select>
</div>
    </div>

<form>
<div id="itemsTableDiv">
<!-- itemsTable goes here via javaScript -->
</div>

<input type="hidden" name="page" value="post"/>
<input type="hidden" name="siteId" value="$S->siteId"/>
</form>

<div id="div2"></div>

</div><!--/content-->

<div data-role="panel" data-position="right" id="admin-help" data-theme="b">
<p>Help goes here. <span class="curtime"></p>
</div>

  <div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->

<script src="js/cpanel.admin.js"></script>
</div>

</body>
</html>
EOF;
