<?php
/*define('TOPFILE', $_SERVER['DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");
*/
// One class for all my sites
// This version has been generalized to not have anything about my sites in it!
/**
 * SiteClass
 *
 * This class HAS Database class.
 * This is like site.class.php except instead of inhereting from Database this class HAS
 * Database (or not)
 * @package SiteClass
 * @author Barton Phillips <barton@bartonphillips.com>
 * @version 1.0
 * @link http://www.bartonphillips.com
 * @copyright Copyright (c) 2010, Barton Phillips
 * @license http://opensource.org/licenses/gpl-3.0.html GPL Version 3
 */

// Note: the $this->memberTable format should have these fields as a minimum!
// id: auto_increment
// fname and lname: member's name
// email: member's email address
// visits: the number of times a member visits or hits the site
// lasttime: timestamp
// visittime: datetime last visit. Set explicitly by logic not a timestamp
//
// We also create logip, logagent, memberpagecnt, and counter tables if they do not exist.
// If you need more extensive table extend this class and overlay the methods you need to change.
// The following methods create or use tables:
//   checkId(), daycount(), counter(), tracker(), getWhosBeenHereToday(), 

/**
 * @package SiteClass
 * This class can be extended to handle special issues and add methods. One of the special cases is the
 * $memberTable. Member tables are not all the same, some tables have a lot of stuff
 * while others have next to nothing (or nothing).
 * NOTE: the 'login' page inserts the member information into the 'memberTable,
 * if the site does not have a 'login' page that calls setIdCookie() and inserts information
 * into the table there will be NO member table created and no SiteId cookie created.
 * If there is no SiteId cookie there is no $this->id and therefore we will never look at
 * the $memberTable table (which doesn't exist more than likely).
 * Extend this class to handle these issues.
 */

// dbAbstract has redirects for all the implemented methods like query, fetchrow etc.
// dbAbstract has $db which is the resource for the various possible db engines like mysqli or
// sqlite etc. By extending the SiteClass via dbAbstract we can say:
//  $S = new SiteClass($s);
//  $S->query(...);
// etc. and not have to do a '$db = $S->getDb(); $db->query(...); etc.

class SiteClass extends dbAbstract {
  // DOCTYPE Constants
  const DOCTYPE_NONE =  0;         // No Doctype header
  const DOCTYPE_4_01_STRICT = 1;   // 4.01 Strict
  const DOCTYPE_4_01_TRANS = 2;    // 4.01 Transitional
  const DOCTYPE_XHTML_STRICT = 3;  // XHTML Strict
  const DOCTYPE_XHTML_TRANS = 4;   // XHTML Transitional
  const DOCTYPE_HTML5 = 5;         // HTML5

  // DOCTYPE text Constants
  const DOCTYPE_NONE_TEXT = '';
  const DOCTYPE_4_01_STRICT_TEXT = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
  const DOCTYPE_4_01_TRANS_TEXT = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
  const DOCTYPE_XHTML_STRICT_TEXT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  const DOCTYPE_XHTML_TRANS_TEXT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
  const DOCTYPE_HTML5_TEXT = '<!DOCTYPE html>';

  // Current Doc Type
  private $doctype;
  
  // Array of Doc Types
  // Fill in with the constants from above.
  // !!! If you add new types then all three areas must be updated: DOCTYPE_xxx numbers,
  //     DOCTYPE_xxx_TEXT strings, and the $doctypes array values!!!
  private $doctypes = array(self::DOCTYPE_NONE_TEXT, self::DOCTYPE_4_01_STRICT_TEXT,
                            self::DOCTYPE_4_01_TRANS_TEXT, self::DOCTYPE_XHTML_STRICT_TEXT,
                            self::DOCTYPE_XHTML_TRANS_TEXT, self::DOCTYPE_HTML5_TEXT);
  private $hitCount = null;
  
  protected $databaseClass = null;
  protected $nodb = null;
  
  protected $count = true;        // if true we do the counters, if false then no counters. Default is true
  protected $countMe = false;     // if true we count me (ie. webmaster). Default is false
  protected $siteDomain = null;   // site's domain name, like granbyrotary.org etc
  protected $subDomain = null;    // this is the 4th parameter to setcookie()
  protected $emailDomain= null;   // where we send webmaster email: webmaster@$emailDomain. Defaults to siteDomain
  protected $memberTable = null;  // the name of the members table
  protected $daycountwhat = null; // the argument to daycount() 
  
  // the following files are optional. If they do not exist there will be defaults used
  protected $headFile = null;     // file with the <head> stuff
  protected $bannerFile = null;   // file with the banner
  protected $footerFile = null;   // file with the footer

  // If the site has a memberTable then these are valid
  public $id = null;       // member ID. Index into memberTable
  public $fname = "";      // First name of member
  public $lname = "";      // Last name of member
  public $email = "";      // Email address of member

  // From $_SERVER
  public $self = null;     // $_SERVER['PHP_SELF']
  public $ip = null;       // $_SERVER['REMOTE_ADDR']
  public $agent = null;    // $_SERVER['HTTP_USER_AGENT']

  // If constructor is called with $s->myUri then we do a lookup of the ip. This lets you
  // not count webmaster activity.
  public $myIp = null;     // gethostbyname(your-local-address). Your home or office URI

  /**
   * Constructor
   *
   * @param array|object $s
   *  fields: databaseClass, siteDomain, subDomain, memberTable, headFile,
   *  bannerFile, footerFile, count, daycountwhat, emailDomain, nodb:
   *  these fields are all protected. Note: nodb can also be a member of databaseClass->nodb.
   *  If there are more elements in $s they become public properties. You can add myUri to populate
   *  $this->myIp if you don't want to count webmaster activity.
   *  count is default true and countMe is default false. The rest of the values are 'null' if not
   *  specifically set in $s.
   */
  
  public function __construct($s=null) {
    $arg = array(); // temp array for $s during parsing

    if(!is_null($s)) {
      if(is_array($s)) {
        $arg = $s;
      } elseif(is_object($s)) {
        foreach($s as $k=>$v) {
          $arg[$k] = $v;
        }
      } else {
        throw(new Exception(__CLASS__ . ": Argument to constructor not an array or object"));
      }
    }
    
    // Now make $this objects of the items what were in $s
    
    foreach($arg as $k=>$v) {
      $this->$k = $v; 
    }

    // From here on we don't use $arg any more instead $this

    // If emailDomain is not set force it to siteDomain
    if(!$this->emailDomain) {
      $this->emailDomain = $this->siteDomain;
    }

    if(is_null($this->databaseClass) ||
       $this->databaseClass->nodb === true || $this->nodb === true) {
       // nodb === true so don't do any database stuff
       // could be either no $databaseClass or $databaseClass->nodb===true so be sure and
       // set $this->nodb true also.
      $this->nodb = true;
      $this->count = $this->countMe = false;
    }

    // Populate the dbAbstract class's protected $db. This allows the dbAbstract class's
    // methods to be accessed via SiteClass. Can't use getDb() until $this->db is valid!
    
    $this->db = $this->databaseClass->db; // use property not method getDb()!
    
    // HTML5 default document type

    $this->doctype = !is_null($this->doctype) ? $this->doctype : self::DOCTYPE_HTML5;

    if(isset($this->myUri)) {
      $this->myIp = gethostbyname($this->myUri); // get my home ip address
      //echo "myUri: $this->myUri, myIp: $this->myIp<br>";
    }
    $this->ip = $_SERVER['REMOTE_ADDR'];
    $this->agent = $_SERVER['HTTP_USER_AGENT'];
    $this->self = $_SERVER['PHP_SELF'];

    $this->checkId(); // check database and cookie and set publics

    // If 'count' is false we don't do these counters

    //vardump($this, "SiteClass this");

    if($this->count) {
      // If this is me and $countMe is false (default is false) then don't count.

      if(!(($this->isMe()) && ($this->countMe == false))) {
        $this->counter(); 
        $this->daycount($this->daycountwhat); 
        $this->tracker(); // track visits
      }

      // Now retreive the hit count value after it may have been incremented

      $rows = $this->queryfetch("select count from counter where filename='$this->self'");
      $this->hitCount = ($rows[0]['count']) ? $rows[0]['count'] : 0;
    }
  }

  public function getDatabaseClass() {
    return $this->databaseClass;
  }

  /**
   * isMe()
   * Check if this access is from ME
   * @return bool true if me else false
   */

  public function isMe() {
    //echo "myIp: $this->myIp, this ip: $this->ip<br>";
    return ($this->myIp == $this->ip);
  }

  /**
   * setIdCookie()
   * Sets the browser Cookie to user ID
   * This is used by login logic of some sites
   */

  public function setIdCookie($id, $cookie=null) {
    $this->id = $id;
    if(!$id) return; // If no ID then don't set any cookies

    $expire = time() + 31536000;  // one year from now

    // Use only www sub domain

    //$ref = ($this->siteDomain == "localhost") ? "localhost" : "www.$this->siteDomain";

    $ref = $_SERVER['HTTP_HOST'];
    
    if($this->subDomain) {
      $sub = $this->subDomain;
    } else {
      $sub = "/";
    }

    $siteid = (is_null($cookie)) ? "SiteId" : $cookie;
    
    if(!setcookie($siteid, "$id", $expire, $sub, $ref)) {
      throw(new Exception("Can't set cookie"));
    }
  }

  /**
   * checkId()
   * Called by the constructor
   * @param $mid defaults to null
   * @param $cookie if pressent then the name of the cookie instead of SiteId.
   * @return the user ID or 0
   * Redifine in an extended class if needed.
   */

  public function checkId($mid=null, $cookie=null) {
    if(!isset($mid) && $this->id) {
      return $this->id;
    }

    if(!$mid && is_null($this->id)) {
      $id = (is_null($cookie)) ? $_COOKIE['SiteId'] : $_COOKIE[$cookie];
    }
    
    if(!$id && !$mid) {
      return 0; // NO ID so don't do any database stuff!
    } elseif(isset($mid)) {
      $id = $mid;
    }

    if(!$this->nodb && $this->memberTable) {
      // If the table does not exist this will throw an exception

      $query = "select fname, lname, email from $this->memberTable where id='$id'";

      $n = $this->query($query);
      $row = $this->fetchrow();
      
      if(!$n) {
      // OPS DIDN'T FIND THE ID IN THE DATABASE?
        $this->id =  0;
        return 0;
      }

      $this->fname = $row['fname'];
      $this->lname = $row['lname'];
      $this->email = $row['email'];
    }

    $this->id = $id;
    return $id;
  }

  /**
   * getId()
   * Get user id
   */

  public function getId() {
    return $this->id;
  }

  /**
   * setId()
   */

  public function setId($id) {
    $this->id = $id;
  }

  /**
   * getIp()
   * Get the ip address
   * @return int ip address
   */

  public function getIp() {
    return $this->ip;
  }

  /**
   * getEmail()
   * Get the user's email address
   */

  public function getEmail() {
    return $this->email;
  }

  /**
   * setEmail()
   * Set the user's email address
   */

  public function setEmail($email) {
    $this->email = $email;
  }

  /**
   * getWhosBeenHereToday()
   * Get Whos Been Here Today message
   * redefine in an extended class if needed!!!
   * NOTE: not called from site.class.php!
   */

  public function getWhosBeenHereToday() {
    if($this->nodb) {
      return;
    }

    $ret = <<<EOF
<table id="todayGuests" style="width: 100%;">
<tbody>
<tr>
<th style="width: 60%">Who's visited our Home Page today?</th>
<th>Last Time</th>
</tr>

EOF;
// NOTE the database visittime (as last) field has the San Diego time not our
// time. So we use the sql ADDTIME to add one hour to the time to get Mountain
// time.

    if($this->memberTable) {
       list($rows, $n) = $this->queryfetch("select concat(fname, ' ', lname) as name, " .
                                           "date_format(addtime(visittime, '1:0'), '%H:%i:%s') as last " .
                                           "from $this->memberTable where visits != 0" .
                                           " and visittime  > current_date() order by visittime desc", true);

       if(!$n) {
         return null;
       }

       foreach($rows as $row) {
         $ret .= "<tr><td>" . stripslashes($row['name']) . "</td><td>{$row['last']}</td></tr>\n";
       }
    }

    $ret .= <<<EOF
</tbody>
</table>

EOF;
    return $ret;
  }

  /**
   * getPageTopBottom()
   * Get Page Top and Footer
   * @param object|array $h top stuff
   * @param object|array $b bottom stuff
   * @return array top, footer
   */

  public function getPageTopBottom($h, $b=null) {
    $top = $this->getPageTop($h);
    $footer = $this->getFooter($b);
    return array($top, $footer);
  }


  /**
   * getPageTop()
   * Get Page Top
   * Gets both the page <head> section and the banner
   * The first argument ($header) is either a string, an array or an object and is required.
   * The array/object version has the 'title', 'description', 'script&styles etc',
   * 'documennt type', 'banner' and 'nonav',
   * (it can also look like $header=>array(head=>array(), banner=>"banner", nonav=>bool),
   * where head can have 'title','desc', 'extra' and 'doctype'
   * and banner has a banner string. This is depreciated).
   * The string version has just the 'title' which is then used for the 'description' also.
   * The second argument is optional and a string with the 'banner'.
   * The banner can either be part of the first argument as 'banner' or the second argument.
   * If the second argument is not present then $header[banner] is used (which could also be null).
   *
   * NOTE: added nonav which can only be used as part of the header array!
   *
   * @param string|array|object $header assoc array [title][desc][extra][doctype][banner][bodytag]
   *   or string title
   * @param string $banner
   * @param string $bodytag a custome body tag, defaults to null
   *   (bodytag can also be a member of the $header array or object)
   * @return string with the <head> section and the banner.
   */

  public function getPageTop($header, $banner=null, $bodytag=null) {
    $arg = array();

    if(is_string($header)) {
      $arg['title'] = $header;
      // $banner and $bodytag are handled below. Therefore we could have
      // an object, string, string in which case $banner string and $bodytag string
      // would override $header->banner etc.
    } elseif(is_object($header)) {
      foreach($header as $k=>$v) {
        $arg[$k] = $v; // turn the object into the $arg array
      }
    } elseif(is_array($header)) {
      $arg = $header; // this is then title, desc, extra, nonav, doctype, banner, maybe bodytag
    } else {
      throw(new Exception("Error: Wrong argument type"));
    }

    // If doctype is not supplied then use the constructor version which may be the default

    if(!$arg['doctype']) {
      $arg['doctype'] = $this->doctype;
    }

    $nonav = $arg['nonav'] ? $arg['nonav'] : false;

    // NOTE: the bodytag and banner strings override the $arg values.
    // So if we have the initial arguments 'object', 'string', 'string' the two string
    // values take presidence!
    $bodytag = $bodytag ? $bodytag : $arg['bodytag'];    
    $banner = $banner ? $banner : $arg['banner']; 

    // Get the page <head> section

    $head = $this->getPageHead($arg);

    // Get the page's banner section

    $banner = $this->getBanner($banner, $nonav, $bodytag);

    return "$head\n$banner";
  }

  /**
   * isxhtml()
   * @param $doctype, if null then default $this->doctype
   * @return true or false
   */

  public function isxhtml($doctype=null) {
    if(!$doctype) $doctype = $this->doctype;
    return ($doctype == self::DOCTYPE_XHTML_STRICT || $doctype == self::DOCTYPE_XHTML_TRANS);
  }

  /**
   * getDoctype()
   * Returns the CURRENT DocType used by this program
   */

  public function getDoctype() {
    return $this->doctype;
  }

  /**
   * getDoctypeString()
   * Return the Document Type String of a) the current document or b) $type
   * @param int $type defaults to null
   * @return string DocType or false if $type is not a doc type. Use === or !== to test for false as DOCTYPE_NONE returns ''.
   */

  public function getDoctypeString($type=null) {
    if(!is_null($type)) {
      if(!in_array($type, array_keys($this->doctypes))) {
        return false;
      }
      return $this->doctypes[$type];
    } else {
      return $this->doctypes[$this->doctype];
    }
  }

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

  public function getPageHead(/*$title, $desc=null, $extra=null, $doctype=self::DOCTYPE_4_01_TRANS, $lang*/) {
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

    // this->doctype can be initialized in the constuctor. If $arg['doctype'] has a value here
    // we want to use it for this page head. Otherwise use the this->doctype which may be the
    // default set by the constructor

    $arg['doctype'] = !is_null($arg['doctype']) ? $arg['doctype'] : $this->doctype;

    if(is_null($arg['desc'])) {
      $arg['desc'] = $arg['title'];
    }

    if(is_null($arg['lang'])) $arg['lang'] = 'en'; // default language is english
    $l = $arg['lang'];

    if($this->isxhtml($arg['doctype'])) {
      $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
      $html = "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"$l\" lang=\"$l\">";
    } else {
      $xml = '';
      $html = "<html lang=\"$l\">";
    }

    $dtype = $this->doctypes[$arg['doctype']];

    // What if headFile is null?
    if(!is_null($this->headFile)) {
      require($this->headFile); // Brings in $pageHeadText
    } else {
    // Make a default <head>
      $pageHeadText =<<<EOF
{$arg['preheadcomment']}<head>
  <title>{$arg['title']}</title>
  <!-- METAs -->
  <meta charset="utf-8"/>
  <meta name="description" content="{$arg['desc']}"/>
  <!-- External link commands -->
{$arg['link']}
  <!-- script -->
{$arg['extra']}
</head>

EOF;
    }

    // Default header has < /> elements. If not XHTML we remove the /> at the end!
    $pageHead = <<<EOF
{$xml}{$dtype}
$html
$pageHeadText

EOF;

    if(!$this->isxhtml($arg['doctype'])) {
    $pageHead = preg_replace("|/>|", ">", $pageHead);
    }

    return $pageHead;
  }

  /**
   * getBanner()
   * Get Page Banner
   * @param string $mainTitle
   * @param bool $nonav if set to true then the navigation bar is NOT displayed (for homepage).
   * @param string $bodytag
   * @return string banner
   */

  public function getBanner($mainTitle, $nonav=false, $bodytag=null) {
    $bodytag = $bodytag ? $bodytag : "<body>";

    if(!is_null($this->bannerFile)) {
      require($this->bannerFile); // brings in $pageBannerText
    } else {
    // a default banner
    // The default banner does not have the IE warnings etc.
      $pageBannerText =<<<EOF
<header>
<div id='pagetitle'>
$mainTitle
</div>
<noscript>
<p style='color: red; background-color: #FFE4E1; padding: 10px'>
Your browser either does not support <b>JavaScripts</b>
or you have JavaScripts disabled, in either case your browsing
experience will be significantly impaired. If your browser supports JavaScripts but you have
it disabled consider enabaling JavaScripts conditionally if your browser supports that.
Sorry for the inconvienence.
</p>
</noscript>
</header>

EOF;
    }

    if(!$this->isxhtml()) {
      $pageBannerText = preg_replace("|/>|", ">", $pageBannerText);
    }

    // Return the Banner

    return <<<EOF
$bodytag
$pageBannerText

EOF;
  }

  /**
   * getFooter()
   * Get Page Footer
   * @param variable number of args.
   *   arguments can be strings (defaults: $msg1='', $msg2='', $ctrmsg=''),
   *   an assoc array, or an object.
   *   for array and object the elements are 'msg1', 'msg2', 'ctrmsg'
   * @return string
   */

  public function getFooter(/* mixed */) {
  // If called from getPageTopBottom($h, $b) then $b
  // will be there even though it may be null. This is not an error.

    $n = func_num_args();
    $args = func_get_args();
    $arg = array();

    if($n == 1) {
      $a = $args[0];

      if(is_string($a)) {
        $arg['msg1'] = $a;
      } elseif(is_object($a)) {
        foreach($a as $k=>$v) {
          $arg[$k] = $v;
        }
      } elseif(is_array($a)) {
        $arg = $a;
      } // elseif(is_null($a)) this is OK because getPageTopBottom($h, $b) will always pass a $b
      //   even if it is null.
    } elseif($n > 1) {
    // String items are being passed and must be in this order.
      $keys = array(msg1, msg2, ctrmsg);
      $ar = array();
      for($i=0; $i < $n; ++$i) {
        $ar[$keys[$i]] = $args[$i];
      }
      $arg = $ar;
    }

    // Make the bottom of the page counter

    $counterWigget = $this->getCounterWigget($arg['ctrmsg']); // ctrmsg may be null which is OK

    if(!is_null($this->footerFile)) {
      require($this->footerFile); // bring in $pageFooterText
    } else {
      $pageFooterText = "<footer>\n";

      if($arg['msg1']) {
        $pageFooterText .= "<div id='footerMsg'>{$arg['msg1']}</div>\n";
      }

      $pageFooterText .= $counterWigget;

      date_default_timezone_set('US/Central');

      $rdate = getlastmod();

      define(ONEDAY, 86400);

      if($rdate > (time() - (ONEDAY*3))) {
        $date = "<span style='color: red'>" . date("M j, Y H:i", $rdate) . "</span>";
      } else {
        $date = date("M j, Y H:i", $rdate);
      }

      $pageFooterText .= <<<EOF
<div style="text-align: center;">
<p id='lastmodified'>Last Modified: $date</p>
<p id='contactUs'><a href='mailto:webmaster@$this->emailDomain'>Contact Us</a></p>
</div>

EOF;

      if(!empty($arg['msg2'])) {
      $pageFooterText .=  $arg['msg2'];
      }

      $pageFooterText .= <<<EOF
</footer>
</body>
</html>

EOF;
    }

    if(!$this->isxhtml()) {
      $pageFooterText = preg_replace("|/>|", ">", $pageFooterText);
    }

    return $pageFooterText;
  }

  public function __toString() {
    return __CLASS__;
  }


  // ********************************************************************************
  // Private and protected methods
  // protected methods can be overridden in child classes so most things that would be private
  // should be protected in this base class

  /**
   * getCounterWigget()
   */

  protected function getCounterWigget($msg="Page Hits") {
    if($this->nodb) return null;

    // Counter at bottom of page
    $hits = number_format($this->hitCount);

    // #F5DEB3==rgb(245,222,179) is 'wheat' for the background
    // rgb(123, 16, 66) is a burgundy for the number
    // We place the counter in the center of the page in a div, in a table
    return <<<EOF
<div id="hitCounter" style="margin-left: auto; margin-right: auto; width: 50%; text-align: center;">
$msg
<table id="hitCountertbl" style="width: 0; border: 8px ridge yellow; margin-left: auto;
margin-right: auto; background-color: #F5DEB3">
<tr id='hitCountertr'>
<th id='hitCounterth' style="color: rgb(123, 16, 66);">
$hits
</th>
</tr>
</table>
</div>

EOF;

  }

  /**
   * daycount()
   * Day Counts
   * @param string|array $inc. String is the name of the file to count. Array is multiple files to count.
   *   An array should look like array('/index', '/antherpage', 'etc'). We will in_array($this->self, $inc) === true
   *   then we count it.
   *   If $inc == 'all' or 'All' etc. then $check=$what="all";
   * May need to redefine in an extended class
   */

  protected function daycount($inc) {
    if($this->nodb) {
      return;
    }

    //date_default_timezone_set('America/New_York');
    $curdate = date("Y-m-d");

    $what = basename($this->self);

    $ip = $this->ip;

    if($inc) {
      if(is_array($inc)) {
        if(in_array($what, $inc)) {
          $check = $what;
        } else {
          $check = null;
        }
      } elseif(strtolower($inc) == "all") {
      // Not an array and ALL
        $what = $check = "all";
      } else {
        $check = $inc;
      }
    } else {
      $check = "index.php";
    }

    if($what == $check) {
      $q1 = "update daycounts set count=count+1, id='$this->id' where date='$curdate' and ip='$this->ip'";
      $q2 = "insert into daycounts (date, count, visits, ip, id) " .
            "values('$curdate', 1, 0, '$this->ip', '$this->id') ";

      $this->tableUpdate($q1, $q2);

      // This is the 10 minute time delay for visitors vs hits

      if(!($cookietime = $_COOKIE['mytime'])) {
        $cookietime = time();
        // set cookie to expire in 10 minutes
        setcookie("mytime", $cookietime, $cookietime + (60*10), "/", $this->siteDomain, false, true);
        $query = "update daycounts set visits=visits+1, id='$this->id' where ip='$this->ip' and date='$curdate'";
        //echo "$query<br>";
        $this->query($query);
      }
    }
  }

  /**
   * counter()
   * This is the page counter feature at in the footer
   * NOTE: override this in your sites class if you need more features.
   * By default this uses/creates a table 'counter' with 'filename', 'count', and 'lasttime'.
   *  'filename' is the primary key.
   */

  protected function counter() {
    if($this->nodb) {
      return;
    }

    $filename = $this->self; // get the name of the file

    $q1 = "update counter set count=count+1 where filename='$filename'";
    $q2 = "insert into counter (filename, count) values('$filename', '1')";

    $this->tableUpdate($q1, $q2);
  }

  /**
   * tracker()
   * Track activity on site
   * NOTE: override this in your sites class if you need more features.
   * By default this uses/creates the 'logip', 'logagent' and 'memberpagecnt' tables.
   */

  protected function tracker() {
    if($this->nodb) {
      return;
    }

    $agent = $this->escape($this->agent);

    // If there is a member 'id' then update the memberTable

    if($this->id && $this->memberTable) {
      $q1 = "update $this->memberTable set visits=visits+1, visittime=now() where id='$this->id'";
      $q2 = "insert into $this->memberTable (fname, lname, email, visits, visittime) ".
            "values('$this->fname', '$this->lname', '$this->email', '1', now())";

      $this->tableUpdate($q1, $q2);

      $q1 = "update memberpagecnt set count=count+1, ip='$this->ip', agent='$agent' ".
            "where page='$this->self' and id='$this->id'";
      $q2 = "insert into memberpagecnt (page, id, ip, agent, count) " .
            "values('$this->self', '$this->id', '$this->ip', '$agent', '1')";

      $this->tableUpdate($q1, $q2);
    }

    // insert|update the logip table, or create it if it does not exist.

    $q1 = "update logip set count=count+1 where ip='$this->ip' && id='$this->id'";
    $q2 = "insert into logip (ip, count, id) values('$this->ip', '1', '$this->id')";
    $this->tableUpdate($q1, $q2);

    $q1 = "update logagent set count=count+1 ".
          "where ip='$this->ip' and id='$this->id' and agent='$agent'";
    $q2 = "insert into logagent (ip, agent, count, id) " .
                 "values('$this->ip', '$agent', '1', '$this->id')";
        
    $this->tableUpdate($q1, $q2);
  }

  /**
   * Protected Method
   * tableUpdate()
   * update/insert values in a table. If update fails try insert, if insert gets a dup key error
   * try the update again. I think this must be a race condition where two clients (probably
   * robots) are accessing our site at almost the same time. I use to do an insert..on duplicate updat
   * but changed it so other database engines would work. The insert..on dup was probably an atomic
   * action while the seperate update/insert are not and therefore we get race conditions. 
   * @param string update query
   * @param string insert query
   */

  protected function tableUpdate($q1, $q2) {
    try {
      // Try the update
      $n = $this->query($q1);
    } catch(Exception $e) {
      $err = $e->getCode();
    }

    // If update returned 0 or NULL then we need to do an insert.

    if(!$n) {
      try {
        // Try an insert

        $this->query($q2);
      } catch(Exception $e) {
        if($e->getCode() == 1062) {
          // Duplicate key error. Try update again
          $n = $this->query($q1);
          if($n) {
            // Success, send me an email
            mail(EMAILADDRESS, "tableUpdate $this->self",
                 "First update failed ($err), insert got dup key error:\n" .
                 "second update OK. q1=$q1, q2=$q2\n" .
                 "No error displayed\n".
                 "ip=$this->ip, agent=$this->agent\n", EMAILFROM, "-f ".EMAILRETURN);
          } else {
            // Failed again
            mail(EMAILADDRESS, "tableUpdate $this->self",
                 "First update failed ($err), insert got dup key error:\n" .
                 "Second update FAILED. q1=$q1, q2=$q2\n" .
                 "No error displayed\n".
                 "ip=$this->ip, agent=$this->agent\n", EMAILFROM, "-f ".EMAILRETURN);
          }
        } else {
          // Was an error other than dup key
          throw($e);
        }
      }
    }
  }
  
} // End of Class

//-----------------
// Helper Functions
//-----------------

// Callback to get the user id for db.class.php SqlError
// NOTE: sites that have members will overload this in their class file. This is a generic version
// that does not understand users so it grabs the ip address and agent only.

if(!function_exists('ErrorGetId')) {
  function ErrorGetId() {
    $id = $_COOKIE['SiteId'];
    if(empty($id)) {
      $id = "IP={$_SERVER['REMOTE_ADDR']}, AGENT={$_SERVER['HTTP_USER_AGENT']}";
    }
    return $id;
  }
}

// WARNING THERE MUST BE NOTHING AFTER THE CLOSING PHP TAG.
// Really nothing not even a space!!!!

?>