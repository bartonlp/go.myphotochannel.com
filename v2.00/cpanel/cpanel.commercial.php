<?php include("cpanel.top.php");
echo <<<EOF
<!-- Commercial Break Settings -->

<div id="commbreak" data-role="page" data-add-back-btn="true" data-theme="a">
	<div data-role="header">
    <a data-rel="panel" href="#commercial-help" data-inline="true" data-mini="true">Help</a>
		<h1>Commercial Break Settings<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->
	<div data-role="content" id="commbreakcontent">
    <div data-role="fieldcontain">
    <label for="segselect">Select Segment Number</label>
    <select id="segselect" name="segselect">
       <option value="cs1" selected>segment 1</option>
       <option value="cs2">segment 2</option>
       <option value="cs3">segment 3</option>
       <option value="cs4">segment 4</option>
       <option value="cs5">segment 5</option>
    </select>
    </div>

    <ul data-role="listview" data-inset="true" id="catlist">
    <li>
    <div data-role="fieldcontain">
    <label for="commbreakslider1">Announce</label>
    <input type="range" name="announce" id="commbreakslider1" value="5" min="0" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="commbreakslider2">Brand</label>
    <input type="range" name="brand" id="commbreakslider2" value="5" min="0" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="commbreakslider3">Product</label>
    <input type="range" name="product" id="commbreakslider3" value="5" min="0" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="commbreakslider4">Info</label>
    <input type="range" name="info" id="commbreakslider4" value="5" min="0" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <label for="commbreaksliderOK" class="ui-hidden-accessible">Submit</label>
    <button id="commbreaksliderOK" name="commbreaksliderOK" data-inline="true">Submit</button>
    </div>
    </li>
    </ul>
	</div><!-- /content -->

  <div data-role="panel" id="commercial-help" data-theme="b">
    <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
  <!--<script src="js/cpanel.commercail.js"></script>-->
</div><!-- /page -->

</body>
</html>
EOF;
