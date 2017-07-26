<?php
include("cpanel.top.php");
echo <<<EOF
<!-- Lotto Control. -->

<div id="lotto" data-role="page" data-theme="a">
	<div data-role="header">
    <a data-role="button" href="#lotto-help" data-inline="true" data-mini="true">Help</a>
		<h1>Lotto Control<span></span></h1>
    <a href="cpanel.php?siteId=$siteId" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right"></a>
	</div><!-- /header -->
	<div data-role="content" id="lottocontent">	
    <ul data-role="listview" data-inset="true">
    <li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="checkbox" id="lottoctrl1"/>
    <label for="lottoctrl1">Play Game 1</label>
    </fieldset><br>
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="text" id="lotto1prize" placeholder="Prize Description" />
    </fieldset>
    </div>
    </li>
		<li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="checkbox" id="lottoctrl2"/>
    <label for="lottoctrl2">Play Game 2</label>
    </fieldset><br>
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="text" id="lotto2prize" placeholder="Prize Description" />
    </fieldset>
    </div>
    </li>
		<li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="checkbox" id="lottoctrl3"/>
    <label for="lottoctrl3">Play Game 3</label>
    </fieldset><br>
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="text" id="lotto3prize" placeholder="Prize Description" />
    </fieldset>
    </div>
    </li>
		<li>
    <div data-role="fieldcontain">
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="checkbox" id="lottoctrl4"/>
    <label for="lottoctrl4">Play Game 4</label>
    </fieldset><br>
    <fieldset data-role="controlgroup" data-type="horizontal">
    <input type="text" id="lotto4prize" placeholder="Prize Description" />
    </fieldset>
    </div>
    </li>
    </ul>
    <label for="lottoOK" class="ui-hidden-accessible">Submit</label>
    <button id="lottoOK" data-inline="true">Submit</button>
	</div><!-- /content -->

  <div data-role="panel" id="lotto-help" data-theme="b">
     <p>There are a possible four games per night. The first game is at 5pm.
        The second at 7pm, then 9pm and finally 11pm.
        Select the game or games you wish to play. Then enter the prize description below it.
        Click <b>Submit</b> when you have finished.</p>
  </div>

	<div data-role="footer">
		<h4>&#169 2013 myphotochannel<span class="curtime"></h4>
	</div><!-- /footer -->
</div><!-- /page -->
</body>
</html>
EOF;
