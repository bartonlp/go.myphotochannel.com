<?php
define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");
include("../kint/Kint.class.php");

// During debug
Error::setDevelopment(true);
Error::setNoEmailErrs(true);
Error::setNoHtml(true);

// dbMysqli extends dbAbstract and Database extends dbAbstract
// Database has dbMysqli in $db

class mymysqli extends dbMysqli {
  public function __construct($info) {
    $arg = (object)$info;
    parent::__construct($arg->host, $arg->user, $arg->password, $arg->database);
  }
  
  public function openDb() {
    echo "openDb\n";
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

//$S = new Database($dbinfo);

//$S->setDb(new mymysqli($dbinfo));

$f = fopen("testmysql.php", "r");

Kint::dump($f);

$arg = (object)$dbinfo;
$S = mysql_connect($arg->host, $arg->user, $arg->password, $arg->database);
echo "Type: ".gettype($S)."\n";
Kint::dump($S);
?>