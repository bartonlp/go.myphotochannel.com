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
    </li>

    <li> <!-- NEW Trivia -->
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <legend>Play Trivia:</legend>
    <input type="radio" name="playtrivia" id="trivia1" value="yes" />
    <label for="trivia1">yes</label>
    <input type="radio" name="playtrivia" id="trivia2" value="no" />
    <label for="trivia2">no</label>
    </fieldset>
    </div>
    <div data-role="fieldcontain">
    <label for="trivianum">How Many Trivia Questions:</label>
    <input type="range" id="trivianum" value="10" min="1" max="100" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="triviaqtime">Question Only Duration:</label>
    <input type="range" id="triviaqtime" value="10" min="1" max="30" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="triviaatime">Time Till Answer:</label>
    <input type="range" id="triviaatime" value="5" min="1" max="30" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="triviacat">Category</label>
    <select id="triviacat" name="triviacat">
       <option value="" selected>Any Category</option>
       <option value="21">Sports</option>
       <option value="22">Geography</option>
       <option value="23">History</option>
       <option value="24">Politics</option>
       <option value="26">Celebrities</option>
       <option value="27">Animals</option>
       <option value="28">Vehicles</option>
    </select>
    </div>
    <div data-role="fieldcontain">
    <label for="triviafontsize">Font Size (rem):</label>
    <input type="range" id="triviafontsize" value="1" min="1" max="3" step=".1" data-highlight='true'/>
    </div>
    <div data-role="fieldcontain">
    <label for="triviafontstyle">Font Style:</label>
    <select id="triviafontstyle" name="triviafontstyle">
      <option value="Arial" selected>Arial</option>
      <option value="'Times Roman'">Times Roman</option>
      <option value="serif">Serif</option>
      <option value="sans-serif">Sans-serif</option>
      <option value="cursive">Cursive</option>
      <option value="fantasy">Fantasy</option>
      <option value="monospace">Monospace</option>
    </select>
    </div>
    </li>

    <li>
    <label for="gamesOK" class="ui-hidden-accessible">Submit</label>
    <button id="gamesOK" data-inline="true">Submit</button>
    </li

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
