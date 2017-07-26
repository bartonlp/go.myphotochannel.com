#! /usr/bin/php6 -q
<?php
set_error_handler('my_errorhandler');  

$x = 5/0;

ini_set('memory_limit', 90*1048576);
$file = "bigphoto.jpg";

if(file_exists($file)) {
  echo "memory start: " . memory_get_usage(true) . "\n";
  $part = base64_encode(file_get_contents($file));
  echo "memory part: " . memory_get_usage(true) . "\n";
  $image = base64_decode($part);
  echo "memory image: " . memory_get_usage(true) . "\n";
  echo "sizeof image: " . strlen($image) . "\n";
  $other = base64_encode(file_get_contents($file));
  echo "memory other: " . memory_get_usage(true) . "\n";
  //unset($other, $part);
  
  if(setMemoryForImage($file) === false) echo "Don't need more memory\n";

  echo "Peak: " . memory_get_peak_usage(true). "\n";
  
  $im = imagecreatefromjpeg($file); //jpeg file
  imagejpeg($im, "testimage.jpg");
  echo "memory usage after imagejpeg: " . memory_get_usage() . "\n";
  echo "memory usage after imagejpeg (true): " . memory_get_usage(true) . "\n";

  imagedestroy($im);
} else {
  echo "file does not exist\n";
}

function setMemoryForImage($filename) {
  echo "$filename\n";

  $imageInfo = getimagesize($filename);
  var_dump($imageInfo);

  $MB = 1048576;  // number of bytes in 1M
  $K64 = 65536;    // number of bytes in 64K
  $TWEAKFACTOR = 1; //.5;  // Or whatever works for you

  $memoryNeeded = round(($imageInfo[0] * $imageInfo[1]
                         * $imageInfo['bits']
                         * $imageInfo['channels'] / 8
                         + $K64
                        ) * $TWEAKFACTOR
                       );
  echo "memory needed: $memoryNeeded\n";
  echo "memory_limit: ". ini_get('memory_limit'). "\n";
  //ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
  //Default memory limit is 8MB so well stick with that. 
  //To find out what yours is, view your php.ini file.

  $memoryLimit = 90 * $MB;

//  $m = memory_get_usage(true);
  $m = memory_get_peak_usage(true);
  echo "memory usage: $m\nLimit: " .($m+$memoryNeeded)."\n";

  if(function_exists('memory_get_usage') && 
     $m + $memoryNeeded > $memoryLimit) 
  { 
    $newLimit = $memoryLimit + ceil(memory_get_usage(true)+ $memoryNeeded - $memoryLimit);
    echo "newLimit: $newLimit\n";
    
    ini_set('memory_limit', $newLimit);
    return true;
  } else {
    return false;
  }
}

function my_errorhandler($errno, $errstr, $errfile, $errline, array $errcontext) {
  $errortype = array (
                      //E_ERROR              => 'Error',
                      E_WARNING            => 'Warning',
                      //E_PARSE              => 'Parsing Error',
                      E_NOTICE             => 'Notice',
                      //E_CORE_ERROR         => 'Core Error',
                      //E_CORE_WARNING       => 'Core Warning',
                      //E_COMPILE_ERROR      => 'Compile Error',
                      //E_COMPILE_WARNING    => 'Compile Warning',
                      E_USER_ERROR         => 'User Error',
                      E_USER_WARNING       => 'User Warning',
                      E_USER_NOTICE        => 'User Notice',
                      E_STRICT             => 'Runtime Notice',
                      E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                     );

  $errmsg = "my_errorhandler: File=$errfile, Line=$errline, Message=$errstr ";

  /*
  $backtrace = debug_backtrace();

  array_shift($backtrace); // get rid of first trace which is this function.

  $btrace = '';
  
  foreach($backtrace as $val) {
    if(isset($val['function'])) {
      $btrace .= "function: {$val['function']} in {$val['file']} on line {$val['line']}\n";
      if(isset($val['args'])) {
        foreach($val['args'] as $arg) {        
          $arg = ($arg === false) ? 'false' : $arg;
          $arg = ($arg === true) ? 'true' : $arg;
          $x = escapeltgt(var_export($arg, true));            
          $btrace .= "          arg: $x\n";
//          }
        }
      }
    }
  }

  if($btrace) {
    $btrace = "\nBacktrace:\n$btrace";
  } else {
    $btrace = "\n";
  }
  
  $errmsg .= "$btrace";
  // This may be defined by sites that have members

  finalOutput($errmsg, "{$errortype[$errno]}");
  */

  echo "$errmsg\n";
  return true;
}
