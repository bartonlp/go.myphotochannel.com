<?php include("cpanel.top.php");
echo <<<EOF
<!-- Category Duration -->

<div id="category" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-role="button" href="#category-help" data-inline="true" data-mini="true">Help</a>

		<h1>Category Settings<span></span></h1>
		<a href="cpanel.php?siteId=$siteId" id="homejames" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->

  <div data-role="content" id="categorycontent">
    <ul data-role="listview" data-inset="true">
    <li>
    <legend>Photo</legend>
    <div data-role="fieldcontain">
    <label for="durationslider1">Duration (seconds)</label>
    <input type="range" name="photo" data-item="duration" id="durationslider1" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="transslider1">Transition (seconds)</label>
    <input type="range" name="photo" data-item='transition' id="transslider1" value="1" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="effectselect1">Effect</label>
    <select id="effectselect1" name="photo" data-item="effect" data-highlight='true'>
    <option value="pop">Pop</option>
    <option value="fade">Fade</option>
    <option value="dissolve">Dissolve</option>
    </select>
    </div>
    </li>

    <li>
    <legend>Announce</legend>
    <div data-role="fieldcontain">
    <label for="durationslider2">Announce (seconds)</label>
    <input type="range" name="announce" data-item="duration" id="durationslider2" value="5" min="1" max="60" data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="transslider2">Transition (seconds)</label>
    <input type="range" name="announce" data-item="transition" id="transslider2" value="1" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="effectselect2">Effect</label>
    <select id="effectselect2" name="announce" data-item="effect" data-highlight='true'>
    <option value="pop">Pop</option>
    <option value="fade">Fade</option>
    <option value="dissolve">Dissolve</option>
    </select>
    </div>
    </li>

    <li>
    <legend>Brand</legend>
    <div data-role="fieldcontain">
    <label for="durationslider3">Brand (seconds)</label>
    <input type="range" name="brand" data-item="duration" id="durationslider3" value="5" min="1" max="60" data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="transslider3">Transition (seconds)</label>
    <input type="range" name="brand" data-item="transition" id="transslider3" value="1" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="effectselect3">Effect</label>
    <select id="effectselect3" name="brand" data-item="effect" data-highlight='true'>
    <option value="pop">Pop</option>
    <option value="fade">Fade</option>
    <option value="dissolve">Dissolve</option>
    </select>
    </div>
    </li>

    <li>
    <legend>Pruduct</legend>
    <div data-role="fieldcontain">
    <label for="durationslider4">Product (seconds)</label>
    <input type="range" name="product" data-item="duration" id="durationslider4" value="5" min="1" max="60" data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="transslider4">Transition (seconds)</label>
    <input type="range" name="product" data-item="transition" id="transslider4" value="1" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="effectselect4">Effect</label>
    <select id="effectselect4" name="product" data-item="effect" data-highlight='true'>
    <option value="pop">Pop</option>
    <option value="fade">Fade</option>
    <option value="dissolve">Dissolve</option>
    </select>
    </div>
    </li>

    <li>
    <legend>Info</legend>
    <div data-role="fieldcontain">
    <label for="durationslider5">Info (seconds)</label>
    <input type="range" name="info" data-item="duration" id="durationslider5" value="5" min="1" max="60" data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="transslider5">Transition (seconds)</label>
    <input type="range" name="info" data-item="transition" id="transslider5" value="1" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="effectselect5">Effect</label>
    <select id="effectselect5" name="info" data-item="effect" data-highlight='true'>
    <option value="pop">Pop</option>
    <option value="fade">Fade</option>
    <option value="dissolve">Dissolve</option>
    </select>
    </div>
    </li>

  
    <li>
    <legend>Video</legend>
    <div data-role="fieldcontain">
    <label for="durationslider6">Info (seconds)</label>
    <input type="range" name="video" data-item="duration" id="durationslider6" value="5" min="1" max="60" data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="transslider6">Transition (seconds)</label>
    <input type="range" name="video" data-item="transition" id="transslider6" value="1" min="1" max="60"
     data-highlight='true' />
    </div>
    <div data-role="fieldcontain">
    <label for="effectselect6">Effect</label>
    <select id="effectselect6" name="video" data-item="effect" data-highlight='true'>
    <option value="pop">Pop</option>
    <option value="fade">Fade</option>
    <option value="dissolve">Dissolve</option>
    </select>
    </div>
    </li>

    <label for="displaysliderOK" class="ui-hidden-accessible">Submit</label>
    <button id="displaysliderOK" name="displaysliderOK" data-inline="true">Submit</button>
    </div>
    </li>
    </ul>
	</div><!-- /content -->
    <div data-role="panel" id="category-help" data-theme="b">
    <p>This setting controls the default display duration each image will display on
     screen during the show and the commercial break. NOTE: Any duration setting defined on an
     individual content image within the Manage Content page, will override this setting on that
     single image.
    </p>
  </div>
	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
</div><!-- /page -->

</body>
</html>
EOF;
