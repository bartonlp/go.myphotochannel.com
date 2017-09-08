<?php
echo "INI: " . ini_get('error_log')."<br>";  
echo "Send error_log<br>";
error_log("BLP Test **********");
echo "Sent<br>";
