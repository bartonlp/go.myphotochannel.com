<?php
include("cpanel.top.php");
echo <<<EOF
<!-- Game Control. -->

<div id="games" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-role="button" href="#games-help" data-inline="true" data-mini="true">Help</a>
		<h1>Game Control<span></span></h1>
    <a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->
	<div data-role="content" id="gamescontent">	
    <ul data-role="listview" data-inset="true">
    <li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Play Bingo:</legend>
    <input type="radio" name="playbingo" id="bingo1" value="yes" />
    <label for="bingo1">yes</label>
     <input type="radio" name="playbingo" id="bingo2" value="no" />
    <label for="bingo2">no</label>
    </fieldset>
    </div>
    <div data-role="fieldcontain">
    <label for="bingofreq">Bingo Frequency (images):</label>
    <input type="range" id="bingofreq" value="5" min="0" max="60" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="bingointerval">Bingo Interval (minutes):</label>
    <input type="range" id="bingointerval" value="5" min="1" max="30" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="bingodraw">Bingo Draws (photos):</label>
    <input type="range" id="bingodraw" value="30" min="10" max="100" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="bingowhenwin">Number of Matches to Win:</label>
    <input type="range" id="bingowhenwin" value="6" min="2" max="9" data-highlight='true'/>
    </div>
    </li>
		<li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Play Lotto:</legend>
    <input type="radio" name="playlotto" id="lotto1" value="yes" />
    <label for="lotto1">yes</label>
     <input type="radio" name="playlotto" id="lotto2" value="no" />
    <label for="lotto2">no</label>
    </fieldset>
    </div>
    <div data-role="fieldcontain">
    <label for="lottoexpires">When Lotto Win Expires (days):</label>
    <input type="range" id="lottoexpires" value="30" min="1" max="120" data-highlight='true'/>
    </div>
    <label for="gamesOK" class="ui-hidden-accessible">Submit</label>
    <button id="gamesOK" data-inline="true">Submit</button>
    </li>
    <li>
    <a href='cpanel.lotto.php?siteId=$siteId'>Lotto Data</a>
    </li>
    </ul>
	</div><!-- /content -->

  <div data-role="panel" id="games-help" data-theme="b">
     <p>Help Goes Here</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
</div><!-- /page -->
</body>
</html>
EOF;
?>