<?php
include("cpanel.top.php");

// ******* Start ******

echo <<<EOF
<div id="home" data-role="page" data-theme="a">
  <div data-role="header">
    <a data-rel="panel" href="#main-help" data-inline="true" data-mini="true">Help</a>

    <h1>Control Panel<span></span></h1>
  </div><!-- /header -->
  <div id="homemainmenu" data-role="content">
    <ul data-role="listview" data-inset="true" id="homelist">
    <li id="approvephotos">
    <a href="cpanel.approve.php?siteId=$siteId">Approve Photos (<span id="numtoapprove"></span>)</a></li>

    <li id="deletephotos">
    <a href="cpanel.expunge.php?siteId=$siteId">Remove Photos Marked Deleted (<span id="numtodelete"></span>)</a></li>

    <li><a href="cpanel.tv.php?siteId=$siteId">Text to Channel</a></li>
    <li><a href="cpanel.managecontent.php?siteId=$siteId">Manage Content</a></li>
    <li><a href="cpanel.showsettings.php?siteId=$siteId">Show Settings</a></li>
    <li><a href="cpanel.commercial.php?siteId=$siteId">Commercial Break Settings</a></li>
    <li><a href="cpanel.category.php?siteId=$siteId">Category Settings</a></li>
    <li><a href="cpanel.account.php?siteId=$siteId">Account Settings</a></li>
    <li><a href="cpanel.games.php?siteId=$siteId">Game Settings</a></li>

    </ul>
  </div><!-- /content -->

  <div data-role="panel" id="main-help" data-theme="b">
    <p>This is help for the main page of the control panel. Click on the item you want to
display or edit.</p>
<ul>
<li>Approve Photos: Photos that have been mailed to <b>myphotochannel</b> must be approved before
they are displayed at your site.</li>
<li>Text to Channel: You can create announcements by entering your text. The text is rendered
as an image.</li>
<li>Manage Content: Manage your photos, announcements, brands, product and information categories.
You can rotate the image, change the status and much more.</li>
<li>Channel Settings: Manage image duration and segments.</li>
<li>Account Maintenance: Change status and add users.</i>
</ul>
  </div>

  <div data-role="footer">
    <h4>&#169 2017 myphotochannel<span class="curtime"></span></h4>
  </div><!-- /footer -->
</div><!-- /page -->
</body>
</html>
EOF;
