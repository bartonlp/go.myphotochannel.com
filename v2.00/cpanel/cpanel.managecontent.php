<?php
include("cpanel.top.php");

echo <<<EOF
<div id="photoadminpanel" data-role="page" data-theme="a">

<div id="prevnexthelp" class="ui-btn-left"> 
  <div data-role="controlgroup" data-type='horizontal' data-mini="true">
    <button id="prev100">Prev 100</button>
    <button id="next100">Next 100</button>
    <a data-rel="panel" id="photoadmin-help" href="#photoadmin-help-panel"
       data-role="button">Help</a>
  </div>
</div>

<div data-role="header">
  <h1>Admin Photos<span></span></h1>
	<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
</div><!-- /header -->

<div data-role="panel" id="photoadmin-help-panel" data-theme="b" data-position="right"
data-display="overlay">
<p>Use the 'Select Category' and 'Select Status' buttons to see photos of a particular
category and status.</p>

<p>Use the 'Prev 100' and 'Next 100' buttons to scroll through the photos.</p>

<p>Click on any thumbnail photo to see a bigger image. Click on the bigger image to bring up the
control panel for the photo. Move the cursor out of the bigger image to dismiss it or select another
thumbnail by clicking on it.</p>

<p>In the control panel for a photo you can change the status (active, inactive, delete, new),
touch the photo (that is make it a feature photo), change the category of the photo, change the
description or give the photo a duration different for the normal duration of the category. A
duration of zero means the photo's duration will be that of the photo's category.</p>

<p>By clicking on the control panel's thumbnail of the photo you can rotate the image by 90 degrees
counter clockwise. Each click rotates the image another 90 degrees. The rotations happend
immediatly and do not requrie you to 'Post'.</p>

<p>You can 'Post' your changes or dismiss the control panel with out making any changes by clicking
'Do Not Post'.</p>

</div>

<div data-role="content" id="content">

<div id="pageselectctrl" data-role="controlgroup" data-type="horizontal">
<select id="catselect">
<option selected='true'>photo</option>
<option>announce</option>
<option>brand</option>
<option>product</option>
<option>info</option>
</select>
<select id="statusselect">
<option selected='true'>active</option>
<option>inactive</option>
<option>new</option>
<option>delete</option>
</select>
</div>

<div id="photos"><!-- 100 photos per time --></div>
<div id="popup"><!-- Enlarged selected photo --></div>

<!-- The selected photo's control panel -->
<div id="cpanel">
</div><!--/cpanel-->
</div><!--/content-->

<div data-role="popup" id="startend" data-theme="b">
</div>

<div data-role="footer">
<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
</div><!-- /footer -->
<!--<script src="js/cpanel.photoadmin.js"></script>-->
</div><!--/page-->

</body>
</html>
EOF;
