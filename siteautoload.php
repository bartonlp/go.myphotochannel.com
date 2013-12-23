<?php
// For go.myphotochannel.com

// Auto load Classes
// Finds the .sitemap.php for a site. Starts at the lowest level and works up until we reach a
// .sitemap.php or the DOC_ROOT without finding anything.
// If success then reads the .sitemap.php to get the layout of the site and the database and
// site info for Database and SiteClass. This info is usually used by a site specific subclass
// that enharits from SiteClass and instantiates a Database if needed.
// Once the site layout is know the class autoloader uses that to load Classes.

// DOC_ROOT is set in the siteautoload.php and is usually $_SERVER['DOCUMENT_ROOT'] for WEB
// programs.
// DOC_ROOT is set to "/kunden" . realpath(dirname(TOPFILE) for CLI programs.
// TOPFILE is defined in the header in the target program and is the location of this file.
// For a CLI program TOPFILE is the full UNIX path to the this file (siteautoload.php).
// ON THIS SITE (go.myphotochannel.com) $_SERVER['DOCUMENT_ROOT'] is empty for CLI programs.
// ON THIS SITE $_SERVER['PHP_SELF'] is also empty for CLI programs.
// On go.myphotochannel.com (the ISP is 1and1.com) we use "$_SERVER['PWD'].$_SERVER['argv'][0]" for
// $self.

// The site structure is defined in the '.sitemap.php' file for the site or sub-site. For
// go.myphotochannel.com we have ONLY ONE '.sitemap.php'.
// The '.sitemap.php' file defines several paths used to locate class files. See the '.sitemap.php'
// file for the ISP and site for the meaning of the following:
// INCLUDES 
// DATABASE_ENGINES
// SITE_INCLUDES
// The following are defined in this file:
// DOC_ROOT: As described above
// SITE_ROOT: This is the path to the found '.sitemap.php'.
// TARGET_ROOT: This is the path to the target file (self).

// NOTE: conditionally defined functions must be defined BEFORE they are used. This is unlike
// unconditionally defined function which can be defined after being called in the source!

//***************
// recursive function to find the site map file. $n is the depth we should search up from where we
// are.

#$DEBUG=true;

if(!function_exists('findSiteMap')) {
  function findSiteMap($sitemapFile, $n) {
    // Does the file exist here?
    //echo "siteFile: $sitemapFile, n: $n\n".getcwd()."\n";
    if(file_exists($sitemapFile)) {
      //echo "success\n";
      return "$sitemapFile";
    } else {
      // NO. Have we searched all the way up to the doc root yet?
      if($n-- == 0) {
        return null;
      }
      // No we have not so hop up a directory level and try again.

      $sitemapFile = "../$sitemapFile";
      return findSiteMap($sitemapFile, $n);
    }
  }
}

//***********************
// Set up the Auto Loader
// Auto load function for database and SiteClass or includes for sites
// Look in all the possible locations

if(!function_exists('siteAutoLoad')) {
  function siteAutoLoad($class) {
    $clLower = strtolower($class);

    //echo "\nCLASS: $class\n";

    // Look at .siteMap.php for definitions of INCLUDES, DATABASE_ENGINES, and SITE_INCLUDES.

    // First look in my cwd/includes (SITE_INCLUDES) then in my cwd.
    // Then start at the very top with INCLUDES, DATABASE_ENGINES
    // Then look for lower case of the class.
    // Finally look in the DOC_ROOT in includes etc for both class and lowercase of class.
    // NOTE: DATABASE_ENGINES have only one location which is usually in INCLUDE/database_engines
    
    $loadMap = array(
                     SITE_INCLUDES . "/$class",    // /var/www/granbyrotary.org/htdocs/<cwd>/includes
                     SITE_ROOT . "/$class",        // /var/www/granbyrotary.org/htdocs/<cwd>
                     INCLUDES . "/$class",         // /home/bartonlp/includes
                     DATABASE_ENGINES . "/$class", // /home/barton/includes/database-engines
                     SITE_INCLUDES . "/$clLower",  // Look for lowercase in SITE_INCLUDES
                     SITE_ROOT . "/$clLower",      // Look for lowercase in appropriate places
                     INCLUDES . "/$clLower",
                     // these four may be the same as SITE_INCLUDES and SITE_ROOT if the
                     // .sitemap.php file is in the TARGET_ROOT, but if it is we will never get
                     // this far down.
                     TARGET_ROOT . "includes/$class", 
                     TARGET_ROOT . "/$class",
                     TARGET_ROOT . "includes/$clLower", 
                     TARGET_ROOT . "/$clLower",
                     DOC_ROOT . "/includes/$class", // finally look in doc root
                     DOC_ROOT . "/$class",
                     DOC_ROOT . "/includes/$clLower", // and then lowercase of class
                     DOC_ROOT . "/$clLower",
                    );

    // First look for class with the sufix '.class.php'
    
    foreach($loadMap as $file) {
      //echo "FILE: $file.class.php\n";
      if(file_exists("$file.class.php")) {
        //echo "FOUND $file.class.php\n";
        include_once("$file.class.php");
        return;
      }
    }
    // If not found look for class with just the php sufix
    foreach($loadMap as $file) {
      //echo "FILE: $file.php\n";
      if(file_exists("$file.php")) {
        //echo "FOUND $file.php\n";
        include_once("$file.php");
        return;
      }
    }
    // Failed miserably!
    throw new Exception("Class Auto Loader could not fine class $class");
  }
}

// Define doc_root. Via the web this will be the apache document root but CLI it will be BLANK!
if($_SERVER['DOCUMENT_ROOT'] == '') {
  // This is a CLI program because DOC_ROOT is blank.
  // TOPFILE for a CLI file has the full UNIX path for the siteautoload.php file!
  // Make DOC_ROOT be the real path of the TOPFILE directory. NOTE TOPFILE may be the path via the
  // login path which would be /home/<account name>/<path to site>/siteautoload.php
  // The realpath would be via /var/www/...
  define('DOC_ROOT', "/kunden" . realpath(dirname(TOPFILE)));
  //echo "CLI: topfile: ".TOPFILE. "\n";
  // PWD is the full UNIX path of the current directory. argv[0] is the command 
  $self = $_SERVER['PWD'] . "/" . $_SERVER['argv'][0];
} else {
  // This is a WEB program because DOC_ROOT is the apache DOCUMENT_ROOT as reported by PHP.
  define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
  //echo "WEB: topfile: ".TOPFILE. "\n";
  // $self need to add the DOC_ROOT to PHP_SELF which is relative to the DOC_ROOT, for example
  // http://granbyrotary.org/hits.php whould be /hits.php.
  $self = DOC_ROOT . $_SERVER['PHP_SELF'];
}
if($DEBUG) {
  echo "doc_root: ". DOC_ROOT . "\n";
  echo "self: $self\n";
}
// Change to the directory where the target file lives.
// For CLI this will be PHP_SELF which is the full path.
// For web based this will be the web root, like /kremmling/index.php. So we need to add the
// doc_root to the PHP_SELF.
//echo "dirname of self: ".dirname($self) ."\n";
chdir(dirname($self)); // Change to the directory where the file lives.
if($DEBUG) echo "cwd: ".getcwd()."\n";
// Get the directory path for our file, that is, the directory that included this file
// Use $self and find the absolute path on the server and capture the dirname part.
$targetDir =  "/kunden" .dirname(realpath($self));
if($DEBUG) echo "targetDir: $targetDir\n";
// Now count the number of '/' in the doc root and myDir. This tells us how far our file is down from
// the doc root. We will search up the tree only that far.
$a = substr_count(DOC_ROOT, '/');
$b = substr_count($targetDir, '/');
// n is the depth down from the root
$n = $b - $a;
if($DEBUG) echo "a: $a, b: $b, n:$n\n";
// The file we are looking for in the dir tree
$sitemapFile = ".sitemap.php";
$x = findSiteMap($sitemapFile, $n);
//echo "x: $x\n";
// $x will be something like ../.sitemap.php or as far up as the file .sitemap.php is from the
// directory where the target file lives.

// were we succcessful?

if($x) {
  // TARGET_ROOT is the home directory of the program. The path to self.
  define('TARGET_ROOT', dirname(realpath($self)));
  // SITE_ROOT is the location of the '.sitemap.php' file 
  define('SITE_ROOT', dirname(realpath($x)));

  if($DEBUG) echo "TARGET_ROOT: ".TARGET_ROOT."\nSITE_ROOT: ".SITE_ROOT."\n";
  
  // Finally what we have come here for we include the .sitemap.php file that has the site
  // configuration information.
  include($x);
} else {
  echo "Failed to Load .sitemap.php\n";
  echo "DOC_ROOT: " . DOC_ROOT . "\nsiteautoloader_myDir: $siteautoloader_myDir\n";
  echo "b(myDir)=$b\na(DOC_ROOT)=$a\nn=$n\n";
  echo "x=$x\n" . "realpath of x: " . dirname(realpath($x)) . "\n";
  // Failure
  throw new Exception("Did not find '.sitemap.php' before " . DOC_ROOT);
}

// Grab the helper function as soon as we know where they are. They should always be with the
// database stuff

require_once(DATABASE_ENGINES . "/helper-functions.php");

// Register autoload functon

spl_autoload_register('siteAutoLoad');
?>