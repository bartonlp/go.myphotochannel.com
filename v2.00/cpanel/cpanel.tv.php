<?php
include("cpanel.top.php");
 
echo <<<EOF
<!-- Text To TV. Create an announcement and send it to the server -->

<div id="texttotv" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-rel="panel" href="#tv-help" data-inline="true" data-mini="true">Help</a>

		<h1>Text to Channel<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->

  <div data-role="content" id="texttotvcontent">
     <form name="texttotvform">
        <div data-role="fieldcontain" id="textotvinput">
           <label for="messagetextentry">Enter Message (max 130 characters):</label>
           <textarea name="messagetext" id="messagetextentry" autofocus></textarea>
        </div>

        Remaining characters = <span id="remainingchar">130</span>

        <div data-role="controlgroup" data-type="horizontal" data-mini="true">
           <button type="radio" id="saveasimage" name="saveas" value="image">Save As Image</button>
           <button type="radio" id="saveastext" name="saveas" value="text">Save As Text</button>
        </div>
        <div data-role="fieldcontain">
        <lable for="messagefile">Or use a html or text file: </label>
        <input id="messagefile" type="file" data-clear-btn="true"  data-theme="e"/>
        </div>
     </form>

     <div id="imagepreview"></div>
     
     <div id='sendimagediv' style="display: none">
     <button id='sendimage' data-inline='true'>Send Image Now</button>
     </div>
     
     <div id='sendtextdiv' style="display: none">
     <button id='sendtext' data-inline='true'>Send Text Now</button>
     </div>
  <div id="alldone" data-role="popup" data-theme="e">
     <p>Image Posted</p>
  </div>

	</div><!-- /content -->
    <div data-role="panel" id="tv-help" data-theme="b">
    <p>Help Goes Here</p>
    </div>

  <div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.tv.js"></script>-->
</div><!-- /page -->

</body>
</html>
EOF;
