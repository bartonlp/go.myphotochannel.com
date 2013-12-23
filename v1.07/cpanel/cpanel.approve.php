<?php
include("cpanel.top.php");
echo <<<EOF
<!-- Approve/Disapprove New Photos -->

<div id="approvephotos-page" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-rel="panel" href="#approve-help" data-inline="true" data-mini="true">Help</a>
		<h1>Photo Approval<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" id="homejames" data-icon="home" data-iconpos="notext"></a>
	</div><!-- /header -->
	<div data-role="content" id="approvephotoscontent">
    <div height="80">&nbsp</div>
    <div id="floatingsubmit" style="position: fixed; left: 50px; top: 40px;">
      <div id="approveallnone" data-role="controlgroup" data-type="horizontal">
         <button id="approveall">Approve All</button><button id="approvenone">Disapprove All</button>
         <button id="approveclear">Clear All</button>
         <button id="approvephotosOK"  data-inline="true">Submit</button>
       </div>
    </div>
    <ul id="approvephotoshere" data-role="listview" data-inset="true">
    <!-- Photo <li>s go here -->
    </ul>
    <div id="approvePostedOK" data-role="popup" data-theme="e">
     <p>Items Posted OK</p>
    </div>

	</div><!-- /content -->
  <div data-role="panel" id="approve-help" data-theme="b">
     <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.approve.js"></script>-->
</div><!-- /page -->

</body>
</html>
EOF;
?>