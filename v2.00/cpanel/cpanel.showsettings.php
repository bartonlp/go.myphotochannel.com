<?php
// BLP 2014-07-23 -- Add allow IFTTT

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
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Allow IFTTT</legend>
    <input type="radio" name="allowIFTTT" class="allows" id="allowifttt1" value="yes" />
    <label for="allowifttt1">yes</label>
    <input type="radio" name="allowIFTTT" class="allows" id="allowifttt2" value="no" />
    <label for="allowifttt2">no</label>
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
    <ul>
      <li>Life of Feature (minutes): A photo is classified as a <b>Feature</b> and shown at the beginning
        of every <i>Show</i> segment for this number of minutes after it is uploaded.</li>
      <li>When Photo Aged (days): The number of days before today that the photo is considered
        <i>Recent</i> and therefore has a higher priority in the photo selection process.</li>
      <li>Program Duration (images): The slide show is broken into two major segments: <i>Show</i>
        and <i>Commercial Break</i>. This field determins how many photos will be displayed during
        the <i>Show</i> segment before the <i>Commercial Break</i> occurs.</li>
      <li>Max Features Per Show (images): The Maximum number of <b>Feature</b> photos that will be
        shown during the <i>Show</i> segment. For example if the <b>Program Duration</b> is set to
        20 and <b>Max Feature Per Show</b> is set to 10 and you have 50 new (<b>Feature</b> photos,
        Then during the <i>Show</i> segment 10 <b>Feature</b> photos would be shown and then
        10 non-feature photos, then the <b>Commercial Break</b> and then the next 10 <b>Feature</b>
        photos etc.</li>
      <li>Server Callback (images): The number of photos to display before the server is contacted
        to get new photos.</li>
      <li>Server Fast Callback (images): The number of photos to display before the server is
        contacted to check on <b>Feature</b> photos and new announcements. This value is only valid
        if the 'Pusher' logic is not active.</li>
      <li>Allow Ads: If yes then 'Ads' are allowed during the <b>Commercial Break</b>.</li>
      <li>Allow Video: If yes then 'Videos' are allowed during the <b>Commercial Break</b>.</li>
      <li>Allow IFTTT: If yes then the emailphoto.php program will process photos sent by the
          IFTTT system from social media sites like <b>Instegram, Facebook etc</b>.</li>
      <li>Photo History: yes or no<br>
        If yes then when someone submits a new photo other photos that the person has submited
        are also made <b>Feature</b> photos.<br>
        When yes is selected then four additional fields are displayed</li>
      <ul>
        <li>Type: The additional photos are selected a) at random b) chronologically</li>
        <li>Limit: The maximum number of additional photos to select</li>
        <li>More Recent (days): The number of days before now that the image was uploaded. That is
          Image can be no older than N days before now.</li>
        <li>Less Recent (days): The number of days before now that the image was uploaded. That is
          the image must be N days before now.</li>
     </ul>
        So <b>Type</b>: 'Random', <b>Limit</b>: '3', <b>More Recent</b> '10',
        <b>Less Recent</b>: '1' means:<br>
        We select at most three photos at random from a pool consisten of photos that were uploaded
        more recently than 10 days ago and yet less recently than one day ago.<br>
        That is the image must be between 'More Recent' and 'Less Recent' days ago.
   </ul>
   <p>Once you have made all of your selection press <b>Submit</b> to post your changes. The screen
     will blink <span style="color: green">GREEN</span> indicating that the post has happened.</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
</div><!-- /page -->

</body>
</html>
EOF;
