BLP 2017-09-22 -- This directory is ONLY used by version v1.08. Version v2.00 uses the 
/var/www/vendor/bartonlp/site-class instead of this directory. The Pusher.php and 
myphotochannelbanner.i.php files have been copied to currentVersion/includes and have been modified
for the v2.00 version. The /var/www/composer.json file has
  "autoload": {
    "classmap": [
      "v2.00/includes"
    ]
  }
which adds that path to the composer path (see vendor/composer/autoload_static.php).
BLP 2017-09-22 -- End Comment
