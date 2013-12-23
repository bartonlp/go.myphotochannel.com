<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Database($dbinfo);
   
// Sql proxy
// GET: sqlproxy.php?msg=$msg
// $msg is: sql:$sql&timestamp&code
// code is: "blp0411+Granby"

define(FIVE, 300);

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

$msg = $_GET['msg'];
//echo "$msg<br>\n";
$msg = base64_decode($msg);
//echo "$msg<br>\n";

$data = "";
if(openssl_private_decrypt($msg, $data, $privkey) === true) {
  $msg = urldecode($data);
  if(preg_match("/^(.*?)::(\d+)::blp0411\+Granby$/", $msg, $m)) {
    $sql = $m[1];
    $time = $m[2];
  } else {
    echo "preg_match failed: $msg";
    exit();
  }
  $d = date("U");
  if($time < $d - FIVE) {
    echo "time too old: time=$time, now=$d";
    exit();
  }
  //echo "sql=$sql, time=$time, now=$d";
  if(strpos($sql, "select") != 0) {
    // insert/delete/update
    $n = $S->query($sql);
  } else {
    $n = $S->query($sql);
    $rows = array();
    while($row = $S->fetchrow('assoc')) {
      $rows[] = $row;
    }
    $ret = json_encode(array('num'=>$n, 'rows'=>$rows));
    echo $ret;
    exit();
  }
} else {
  echo "Decrypt failed";
  exit();
}

?>