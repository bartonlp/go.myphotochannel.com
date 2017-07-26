<?php
//$_site = require_once(getenv("SITELOADNAME"));
//$S = new $_site->className($_site);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://opentdb.com/api.php?amount=10&category=21&difficulty=easy&type=multiple');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$test = curl_exec($ch);
$ar = json_decode($test);

foreach($ar->results as $i) {
  echo "cat: $i->category<br>";
  echo "question: $i->question<br>";
  $x = array_merge($i->incorrect_answers, [$i->correct_answer]);
  asort($x);
  $inx = 1;
  foreach($x as $v) {
    echo "$inx: $v<br>";
    ++$inx;
  }
  
  echo "<br>";
  echo "correct: $i->correct_answer<br><br>";
}
