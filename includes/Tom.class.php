<?php
// BLP 2014-04-27 -- changed Error:setDevelopment() to false as we are kinda out of development and
// I don't want users to see detailed error stuff.
// sites class for go.myphotochannel.com
// This should be changed to MyPhotochannel. It was originaly named after Tom Galbrith when I first
// started the project and we didn't really have a name yet.

class Tom extends SiteClass {

  public function __construct($x=null) {
    global $dbinfo, $siteinfo; // from .sitemap.php

    Error::setNoEmailErrs(true); // For debugging
    Error::setDevelopment(false); // during development
    Error::setErrorType(E_ALL & ~(E_WARNING | E_NOTICE | E_STRICT));
    //Error::setNoOutput(true); // no output to user

    $s = $siteinfo;
    $s['databaseClass'] = new Database($dbinfo);

    // If $x has values then add/modify the $s array

    if(!is_null($x)) foreach($x as $k=>$v) {
      $s[$k] = $v;
    }

    parent::__construct($s);
  }

  // Overload checkId to use cookie 'userId'
    
  public function checkId() {
    return parent::checkId(null, 'userId');
  }

  // Overload getPageHead()
  // Get the version 

  /**
   * getPageHead()
   * Get the page <head></head> stuff including the doctype etc.
   * This can take either 5 args or an array or object
   * @param string $title
   * @param string $desc or null
   * @param string $extra or null
   * @param int $doctype default to DOCTYPE_4_01_TRANS
   * @param string $lang or null
   * or 
   * @param array array[title=>"title", ...]
   * or
   * @param object object->title = "title" etc.
   * NOTE: the array or object can have 'link' or 'preheadcomment'. These are added to the head
   *   section if they exist in the headFile or if the default is used.
   */

  public function getPageHead() {
    $n = func_num_args();
    $args = func_get_args();
    $arg = array();

    if($n == 1) {
      $a = $args[0];
      if(is_string($a)) {
        $arg['title'] = $a;
      } elseif(is_object($a)) {
        foreach($a as $k=>$v) {
        //echo "$k=$v<br>\n";
          $arg[$k] = $v;
        }
      } elseif(is_array($a)) {
        $arg = $a;
      } else {
        throw(new Exception("Error: getPageHead() argument no valid: ". var_export($a, true)));
      }
    } elseif($n > 1) {
      $keys = array(title, desc, extra, doctype, lang);
      $ar = array();
      for($i=0; $i < $n; ++$i) {
        $ar[$keys[$i]] = $args[$i];
      }
      $arg = $ar;
    }

    $version = preg_replace("/^.*?(v\d+\.\d+).*$/", "$1", realpath(DOC_ROOT . "$this->self"));
    $arg['title'] .= "::$version";
    $this->version = $version;  
    return parent::getPageHead($arg);
  }

  // Most of the file add the banner file to $s->bannerFile but some use cpanel/cpanel.top.php and
  // have to add the file via this method: itemsInfo.php, uploadphotos.php and uploadads.php

  public function setBannerFile($file) {
    $this->bannerFile = $file;
  }
}

// Callback to get the user id for db.class.php SqlError

if(!function_exists('ErrorGetId')) {
  function ErrorGetId() {
    $id = "IP=$_SERVER[REMOTE_ADDR], AGENT=$_SERVER[HTTP_USER_AGENT]";
    
    return $id;
  }
}

// WARNING THERE MUST BE NOTHING AFTER THE CLOSING PHP TAG.
// Really nothing not even a space!!!!

?>