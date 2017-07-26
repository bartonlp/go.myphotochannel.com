<?php
$lastmod = date("M j, Y H:i", getlastmod());

return <<<EOF
<footer style="text-align: center">
<div id="address">
<address>
  Copyright &copy; $this->copyright<br>
<a href='mailto:bartonphillips@gmail.com'>bartonphillips@gmail.com</a>
</address>
</div>
{$arg['msg']}
{$arg['msg1']}
<br>
Last Modified: $lastmod
{$arg['msg2']}
</footer>
</body>
</html>
EOF;
