<?php
// Expunge deleted items from the items table.
// These are items that have a status of 'delete'

include("cpanel.top.php");
echo <<<EOF
<div id="expungephotos" data-add-back-btn="true" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-rel="panel" href="#expunge-help" data-inline="true" data-mini="true">Help</a>
		<h1>Expunge Photos<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->
	<div data-role="content" id="expungecontent">
     <!--
     // Show images of photos to be deleted
     // This is the same logic as used by the approve
     -->
    <div height="80">&nbsp</div>
    <div id="expungefloatingsubmit" style="position: fixed; left: 50px; top: 40px;">
      <div id="expungeallnone" data-role="controlgroup" data-type="horizontal">
         <button id="expungeall">Remove All</button>
         <button id="expungeclear">Clear All</button>
         <button id="expungeOK"  data-inline="true">Submit</button>
       </div>
    </div>
    <ul id="expungephotoshere" data-role="listview" data-inset="true">
    <!-- Photo <li>s go here -->
    </ul>
    <div id="expungePostedOK" data-role="popup" data-theme="e">
     <p>Items Removed OK</p>
    </div>

	</div><!-- /content -->
  <div data-role="panel" id="expunge-help" data-theme="b">
     <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.expunge.js"></script>-->
</div><!-- /page -->

</body>
</html>
EOF;
