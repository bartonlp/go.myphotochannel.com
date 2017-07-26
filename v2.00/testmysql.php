<?php
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);

// dbMysqli extends dbAbstract and Database extends dbAbstract
// Database has dbMysqli in $db

class mymysqli extends dbMysqli {
  public function __construct($info) {
    $arg = (object)$info;
    parent::__construct($arg->host, $arg->user, $arg->password, $arg->database);
  }
  
  public function openDb() {
    echo "openDb<br>\n";
    $this->MYDB = "Barton Phillips";
    return parent::openDb();
  }

  public function __toString() {
    ob_start();
    var_dump($this->db);
    $r = ob_get_clean();
    echo "$r\n";
    if(preg_match("/#(\d+)/", $r, $m)) {
      return "$m[1]";
    } else return "NOT FOUND";
  }
}

function var_debug($variable,$return=false,$strlen=100,$width=25,$depth=10,$i=0,&$objects=array()) {
  $search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
  $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');
 
  $string = '';
 
  switch(gettype($variable)) {
    case 'boolean':      $string.= $variable?'true':'false'; break;
    case 'integer':      $string.= $variable;                break;
    case 'double':       $string.= $variable;                break;
    case 'resource':     $string.= "[$variable]";             break;
    case 'NULL':         $string.= "null";                   break;
    case 'unknown type': $string.= '???';                    break;
    case 'string':
      $len = strlen($variable);
      $variable = str_replace($search,$replace,substr($variable,0,$strlen),$count);
      $variable = substr($variable,0,$strlen);
      if ($len<$strlen) $string.= '"'.$variable.'"';
      else $string.= 'string('.$len.'): "'.$variable.'"...';
      break;
    case 'array':
      $len = count($variable);
      if ($i==$depth) $string.= 'array('.$len.') {...}';
      elseif(!$len) $string.= 'array(0) {}';
      else {
        $keys = array_keys($variable);
        $spaces = str_repeat(' ',$i*2);
        $string.= "array($len)\n".$spaces.'{';
        $count=0;
        foreach($keys as $key) {
          if ($count==$width) {
            $string.= "\n".$spaces."  ...";
            break;
          }
          $string.= "\n".$spaces."  [$key] => ";
          $string.= var_debug($variable[$key],$return,$strlen,$width,$depth,$i+1,$objects);
          $count++;
        }
        $string.="\n".$spaces.'}';
      }
      break;
    case 'object':
      $id = array_search($variable,$objects,true);
      if ($id!==false)
        $string.=get_class($variable).'#'.($id+1).' {...}';
      else if($i==$depth)
        $string.=get_class($variable).' {...}';
      else {
        $id = array_push($objects,$variable);
        $array = (array)$variable;
        $spaces = str_repeat(' ',$i*2);
        $string.= get_class($variable)."#$id\n".$spaces.'{';
        $properties = array_keys($array);
        foreach($properties as $property) {
          $name = str_replace("\0",':',trim($property));
          $string.= "\n".$spaces."  [$name] => ";
          $string.= var_debug($array[$property],$return,$strlen,$width,$depth,$i+1,$objects);
        }
        $string.= "\n".$spaces.'}';
      }
      break;
  }
 
  if ($i>0) return $string;
 
  $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
  do {
    $caller = array_shift($backtrace);
  } while ($caller && !isset($caller['file']));
  
  if ($caller) $string = $caller['file'].':'.$caller['line']."\n".$string;

  if($return) return $string;
  else echo $string . "\n";
}

echo "<h1>\$_site</h1>";
//printit($_site);

$S = new Database($_site);
$S->setDb(new mymysqli($S->dbinfo));

$arg = (object)$S->dbinfo;
$ss = mysqli_connect($arg->host, $arg->user, $arg->password, $arg->database);
echo "<h1>All done</h1>";
echo "mysqli: " .gettype($ss) . "<br>";

echo "<h1>\$ss</h1>";
vardump("ss", $ss);
printit($ss);
echo "\$ss->client_info: $ss->client_info<br>";

d($ss);

class t {
  public $a = 10;
  public $b = "11";
  public $c = 1.7;
  public function __construct() {
    $this->xx = new class{};
  }
}
echo "<h1>t</h1>";
$t = new t;
vardump("t", $t);
echo gettype($t->xx) . "<br>";
printit($t);
echo "DONE";

exit();

function printit($value) {
  foreach($value as $k=>$v) {
    if(is_string($v)) {
      echo "$k: $v<br>";
    } elseif(is_numeric($v)) {
      echo "$k: $v<br>";
    } elseif(is_object($v)) {
      foreach($v as $kk=>$vv) {
        if(gettype($vv) == 'object') {
          echo "object<br>";
          printit($vv);
        } else {
          echo "{$k}->$kk: $vv<br>";
        }
      }
    } elseif(is_array($v)) {
      foreach($v as $kkk=>$vvv) {
        if(gettype($vvv) == 'array') {
          echo "array<br>";
          printit($vvv);
        } else {
          echo "{$k}[$kkk]: $vvv<br>";
        }
      }
    } elseif(is_bool($v)) {
      echo "$k ". ($v ? "true" : "false") . "<br>";
    } elseif(is_null($v)) {
      if(count($value->$k) != 0) {
        echo "$k: " .$value->$k. "<br>";
      } else {
        echo "$k is null<br>";
      }
    } else {
      echo "k: $k. What<br>";
    }    
  }
}
