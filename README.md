# This is the website for the MyPhotochannel project.
## Webmaster and Co-owner:  
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
* slideshow -> current version of the slideshow
* cpanel -> current version of cpanel
* currentVersion -> points to the current version directory

The current directory is v2.00 -- current version

Repository: git@github.com:bartonlp/myphotochannel.git
GitHub: https://github.com/bartonlp/myphotochannel

The 'currentVersion' symlink points to the 'v2.00' directory.

The 'slideshow' symlink points to the current stable production code, 'v2.00' directory.

The same goes for 'cpanel' symlink. The actual code is in a directory that looks like:
  v2.00/cpanel/.

The 'v2.00' code uses the new 'SiteClass' which is at $HOME/vendor/bartonlp/site-class. 
The 'mysitemap.json' replaces the the old '.sitemap.php' and the new $HOME/vendor/bartonlp/site-class/includes/siteload.php 
replaces the old 'siteautoload.php'.

The php class files are in the 'includes' directory in the 'root' and possibly in subdirectories.

Every night the database is extracted and zipped into the 'other' directory.

Every night all of the code is mirrored onto my home computer at www.bartonphillips.dyndns.org into
a directory called '/extra/myphotochannel' and that directory is tar backed up to '/extra/myphotochannel-backup'
once a week.  The mirror is done with rsync so only new or changed files are actually moved.

## GITHUB

This site is backed up on github.com. The .gitignore looks like this:

content/
Archive/
fonts/
logs/
kint/
myphotochannel.git/
pusher/
websocket/
adscontent/
composer/
other/
vendor/
mysitemap.json
PHP_ERROR.log
.gitignore
.gitnew
.composer
*.pdf
*.doc
*.jpg
*.png
*.gif
database.log
database.log.save
emailphoto.log
emailphoto.log.save
resize.log
resize.log.save
photolotto.log
photolotto.log.save
composer.json
composer.lock

This keeps us from backing up site specific stuff. The main site is at 1and1 but there is a duplicate site at www.bartonphillips.org.

