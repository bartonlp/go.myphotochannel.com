<?php
$pubkey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDSUv3Gmba1PJp22ZF47ki1XcDQ
fPpwrCYCN6J+VXp7UrE6EKHXEiZEfO76xxlON1m038occzynlyLyT8qP7sqz/N1M
36vo4Ia2/36r1OWzA2cs0D1Xwjc/NVNqEJ/n2fjUGfcgDleBUQvjYl/TaHZFyJH3
PHocrrcfBzKegBTstwIDAQAB
-----END PUBLIC KEY-----
";

$sql = "select * from items limit 5";
$d = date("U");
$s = "blp0411+Granby";
$msg = "$sql::$d::$s";
echo "MSG: $msg<br>\n";
$msg = urlencode($msg);
openssl_public_encrypt($msg, $data, $pubkey);
$encoded = urlencode(base64_encode($data));
//$msg = json_encode(array('mymsg'=>$encoded));
echo "$encoded<br>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://go.myphotochannel.com/sqlproxy.php?msg=$encoded");
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

$ret = curl_exec($ch);
curl_close($ch);
$data = json_decode($ret);
echo "<br>\nRET:<br>\n$ret<br>\ndata: " . print_r($data, true) . "<br>\nENDRET<br>\n";

/*

$privkey = "-----BEGIN RSA PRIVATE KEY-----
MIICXwIBAAKBgQDSUv3Gmba1PJp22ZF47ki1XcDQfPpwrCYCN6J+VXp7UrE6EKHX
EiZEfO76xxlON1m038occzynlyLyT8qP7sqz/N1M36vo4Ia2/36r1OWzA2cs0D1X
wjc/NVNqEJ/n2fjUGfcgDleBUQvjYl/TaHZFyJH3PHocrrcfBzKegBTstwIDAQAB
AoGBALyGF2OFNPiPMgWGT5cOP64SM1quK+4C4K7sH4MOK5OPM7zQW8DkS9joA25W
OKCbjJVMY2XNBXlTR8fbLb6GVLoPYBdlcf4uB5vWcHYoHumKGyGXSb/2aQmQGqkk
vp/3gelmC04bLeqgwnfRcuWk+B4OgqC6VWuGsnDpstC+ItbBAkEA/G8zXCPm5YP3
Jn0xQ8JY2G2OxvSZdvpbtyU1ow1/IL7EaEb83Efx7/QnForDxKUKukuzzqjM62V1
ySTB9XYuxQJBANVLhVJDfGKHbiv/BLEwqabA8tkE3jpTKpLn2HYvIhlWFlpntWsL
YtR3rSYHX19bGegqhAI88YKNWTalnC4T5UsCQQC/gMTf48So3cJDirozA19PYV3t
hWZfInMtr6bPOc/10YNC8IenvVTHituUeFUn+2T2C7Qu1VQQSHpgy+fxBWVZAkEA
oCDXUWAK1KmZ43vL2P6QjvkSGC0YbS8cqjdWgbt23RCNLYfoYhmlM585JXCpgBwT
wgGRI2D/aySU0nrYWptjKwJBAONnK6eu1bMSYQWPbvklARqxSqBl6vqe8olU14Gu
t1p83LhoT77fII8kD2m3ZFTDK5uNyFNXnN9+2qMWyeXQU5g=
-----END RSA PRIVATE KEY-----
";

$msg = urldecode($urlencoded);
$data = "";
if(openssl_private_decrypt($msg, $data, $privkey) === false) echo "<br>\nfailed<br>\n";
echo "decripted: $data<br>\n";
*/
