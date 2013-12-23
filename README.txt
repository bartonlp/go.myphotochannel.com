Some notes on the directory structure:
There are two symlinks:
  slideshow
  cpanel

The first two point to the current production code. The slide show has version directories like:
  slideshow-v1.01 etc.

The 'slideshow' symlink points to the current stable production code directory.

The same goes for 'cpanel' symlink. The actual code is in a directory that looks like:
  cpanel-v1.01 etc.

The other code like 'uploadphotos.php' etc. are in the 'root' directory.

The 'working' directory has the other code files that are not stable and being worked on.

The 'working' directory has symlinks for 'cpanel-v1.01' (this must match the version used in the link
to 'cpanel.top.php' used in a couple of the files).

It also has a sumlink to the '/content' directory. These symlinks make it possible to copy the
working code right to the 'root' directory without changing the code.

The '/js' directory has JavaScripts for the 'other' stable code. The 'working' directory has its own
'js' directory and the appropriate JavaScript code for a php file which must be moved to the 'root'
'js' directory when the php file is moved. Likewise the 'css' file if one exists.

Any of the version directories could have a '.sitemap.php' file for use during testing etc. Just
remember you have it.

The 'siteautoload.php' file in the 'root' finds the appropriate '.sitemap.php' file and provides the
class autoload feature.

The php class files are in the 'includes' directory in the 'root' and posibely in subdirectories.
The root 'includes' directory has a subdirectory 'database-engines' which has the database classes.

Every night the database is extracted and zipped into the 'backup' directory.

Every night all of the code is mirrored onto my home computer at www.bartonphillips.dyndns.org into
a directory called 'backup' and that directory is tar backed up to '/extra/myphotochannel-backup'
once a week.  The mirror is done with rsync so only new or changed files are actually moved.