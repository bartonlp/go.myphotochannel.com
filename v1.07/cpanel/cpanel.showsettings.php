<?php
include("cpanel.top.php");
echo <<<EOF
<!-- Show Settings -->
<div id="showsettings" data-role="page" data-add-back-btn="true" data-theme="a">
	<div data-role="header">
    <a data-role="button" href="#showsettings-help" data-inline="true" data-mini="true">Help</a>

		<h1>Show Settings<span></span></h1>

		<a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->

  <div data-role="content" id="showsettingscontent">	
    <ul data-role="listview" data-inset="true">
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings1">Life of Feature (minutes)</label>
    <input type="range" name="lifeOfFeature" id="showsettings1" value="60" min="1" max="300"
     data-highlight='true' />
    </div>
    </li>
		<li>
    <div data-role="fieldcontain">
    <label for="showsettings2">When Photo Aged (days)</label>
    <input type="range" name="whenPhotoAged" id="showsettings2" value="90" min="1" max="470"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings3">Program Duration (images)</label>
    <input type="range" name="progDuration" id="showsettings3" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings4">Max Features Per Show (images)</label>
    <input type="range" name="featuresPer" id="showsettings4" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings5">Server Callback (images)</label>
    <input type="range" name="callbackTime" id="showsettings5" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings6">Server Fast Callback (images)</label>
    <input type="range" name="frequentCallbackTime" id="showsettings6" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>

    <li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Allow Ads</legend>
    <input type="radio" name="allowAds" id="allowads1" value="yes" />
    <label for="allowads1">yes</label>
     <input type="radio" name="allowAds" id="allowads2" value="no" />
    <label for="allowads2">no</label>
    </fieldset>
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Allow Video</legend>
    <input type="radio" name="allowVideo" id="allowvid1" value="yes" />
    <label for="allowvid1">yes</label>
     <input type="radio" name="allowVideo" id="allowvid2" value="no" />
    <label for="allowvid2">no</label>
    </fieldset>
    </div>
    </li>
    <li>
    <label for="showsettingsOK" class="ui-hidden-accessible">Submit</label>
    <button id="showsettingsOK" name="showsettingsOK" data-inline="true">Submit</button>
    </li>

  </ul>
	</div><!-- /content -->
    <div data-role="panel" id="showsettings-help" data-theme="b">
    <p>Help goes here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
</div><!-- /page -->

</body>
</html>
EOF;
?>