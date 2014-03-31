This is the website for the MyPhotochannel project.
Webmaster and Co-owner:
Barton Phillips
bartonphillips@gmail.com
http://www.bartonphillips.com

This site is hosted at 1and1.com.
The site is: http://go.myphotochannel.com.
The site is registered at godaddy.com.

Co-owner: Tom Galbraith <tom@felixsrestaurant.com>
Organization: KLL Media
Street: 6335 Clayton Avenue
City: St. Louis
State/Province: Missouri
Postal Code: 63139
Country: United States
Phone: +1.3146456565
Support Email: support@myphotochannel.com

Some notes on the directory structure:

There are several symlinks:
  slideshow -> current version of the slideshow
  cpanel -> current version of cpanel
  currentVersion -> points to the current version directory
  workingVersion -> points to the working version directory
  oldVersion -> points to the most recent old version directory

There are currently (BLP 2014-03-31) three version directories:
  v1.07 -- old version
  v1.08 -- current version
  v1.09 -- working version

NOTE: BLP 2014-03-31 -- I am now using get and github.com and the version directories are not as
important as they once were. The repository is at the root. 
Repository: git@github.com:bartonlp/myphotochannel.git
GitHub: https://github.com/bartonlp/myphotochannel
The repository is PRIVATE.

The first two point to the current production code. The slide show has version directories like:
  v1.08/slideshow/.

The 'slideshow' symlink points to the current stable production code directory.

The same goes for 'cpanel' symlink. The actual code is in a directory that looks like:
  v1.08/cpanel/.

The 'working' directory has the other code files that are not stable and being worked on.

Any of the version directories could have a '.sitemap.php' file for use during testing etc. Just
remember you have it.

The 'siteautoload.php' file in the 'root' finds the appropriate '.sitemap.php' file and provides the
class autoload feature.

The php class files are in the 'includes' directory in the 'root' and possibly in subdirectories.
The root 'includes' directory has a subdirectory 'database-engines' which has the database classes.

Every night the database is extracted and zipped into the 'backup' directory.

Every night all of the code is mirrored onto my home computer at www.bartonphillips.dyndns.org into
a directory called 'backup' and that directory is tar backed up to '/extra/myphotochannel-backup'
once a week.  The mirror is done with rsync so only new or changed files are actually moved.
