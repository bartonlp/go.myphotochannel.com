Version v2.00 uses these file here in currentVersion/includes:
Pusher.php
footer.i.php
myphotochannelbanner.i.php

The previous version, v1.08, uses the /var/www/bartonphillips.org/includes for the SiteClass files
and the Pusher.php, myphotochannelbanner.i.php and Toms.class.php. Version v2.00 does NOT use 
the root includes directory at all.

The /var/www/bartonphillips.org/composer.json file has 
  "autoload": {
    "classmap": [
      "v2.00/includes"
    ]
  }
which adds that path to the composer path (see vendor/composer/autoload_static.php).
