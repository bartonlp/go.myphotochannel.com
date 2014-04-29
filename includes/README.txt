database-engines/
  Directory with all of the new database classes and the helper file.
    Database.class.php
      Main database class. It extends dbAbstract.
      This gets instantiated by most classes that extend SiteClass like this:
<code>
      public function __construct($x=null) {
        global $dbinfo, $siteinfo; // from .sitemap.php
        $s = $siteinfo;
        $s['databaseClass'] = new Database($dbinfo);
        ...
      }
</code>
      
    dbAbstract.class.php
      Abatract database class.
    dbMysqli.class.php
      Implements the MySqli interface.
    SqlException.class.php
      Exception class.
    Error.class.php
      Error Class.
    dbTables.class.php
      Implements table creation via the database.
    helper-functions.php
      This is NOT a class but just a bunch of useful functions used by everything.
Pusher.php
  This is a copy of the Pusher.com program that implements the websockets in our application.
  This copy is now used as of v1.08 (cpanel and slideshow). Previous versions used a copy of this
  program in their directories under 'websocket//vendor/pusher/pusher-php-server/lib/Pusher.php'.
  By living in include the siteautoload.php can now find this program and we don't need to have a
  require statement for it in other code.
SiteClass.class.php
  This is the main site class which Tom.class.php extends.
Tom.class.php
  This is the site specific class that extends SiteClass.
myphotochannelbanner.i.php
  This is the banner for the index.php file.

