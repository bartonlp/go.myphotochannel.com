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
    <input type="range" name="lifeOfFeature" class="appinfo" id="showsettings1" value="60" min="1" max="300"
     data-highlight='true' />
    </div>
    </li>
		<li>
    <div data-role="fieldcontain">
    <label for="showsettings2">When Photo Aged (days)</label>
    <input type="range" name="whenPhotoAged" class="appinfo" id="showsettings2" value="90" min="1" max="470"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings3">Program Duration (images)</label>
    <input type="range" name="progDuration" class="appinfo" id="showsettings3" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings4">Max Features Per Show (images)</label>
    <input type="range" name="featuresPer" class="appinfo" id="showsettings4" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings5">Server Callback (images)</label>
    <input type="range" name="callbackTime" class="appinfo" id="showsettings5" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <label for="showsettings6">Server Fast Callback (images)</label>
    <input type="range" name="frequentCallbackTime" class="appinfo" id="showsettings6" value="5" min="1" max="60"
     data-highlight='true' />
    </div>
    </li>

    <li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Allow Ads</legend>
    <input type="radio" name="allowAds" class="allows" id="allowads1" value="yes" />
    <label for="allowads1">yes</label>
     <input type="radio" name="allowAds" class="allows" id="allowads2" value="no" />
    <label for="allowads2">no</label>
    </fieldset>
    </div>
    </li>
    <li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Allow Video</legend>
    <input type="radio" name="allowVideo" class="allows" id="allowvid1" value="yes" />
    <label for="allowvid1">yes</label>
     <input type="radio" name="allowVideo" class="allows" id="allowvid2" value="no" />
    <label for="allowvid2">no</label>
    </fieldset>
    </div>
    </li>

    <li>
    <div data-role="fieldcontain">

    <div  id="featureext">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Photo History</legend>
    <input type="radio" name="featureExt" id="featureext1" value="yes" />
    <label for="featureext1">yes</label>
     <input type="radio" name="featureExt" id="featureext2" value="no" />
    <label for="featureext2">no</label>
    </fieldset>
    </div>

    <div id="featureExtYes">
    <br>

    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Type</legend>
    <input type="radio" name="exttype" id="exttype1" checked value="rand" />
    <label for="exttype1">Random</label>
     <input type="radio" name="exttype" id="exttype2" value="chron" />
    <label for="exttype2">Chronological</label>
    </fieldset>

    <fieldset data-role="controlgroup" data-type="horizontal">
    <div data-role="fieldcontain">
    <label for="extlimit">Limit</label>
    <input type="range" name="extlimit" id="extlimit" value="3" min="1" max="20"
     data-highlight='true' />
    </div>  

    <div data-role="fieldcontain">
    <label for="extmorerecent">More Recent (days)</label>
    <input type="range" name="extmorerecent" id="extmorerecent" value="7" min="1" max="90"
     data-highlight='true' />
    </div>
  
    <div data-role="fieldcontain">
    <label for="extlessrecent">Less Recent (days)</label>
    <input type="range" name="extlessrecent" id="extlessrecent" value="1" min="1" max="20"
     data-highlight='true' />
    </div>  
    </fieldset>
    </div>

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