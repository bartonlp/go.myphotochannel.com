# Get Major League Baseball Schedule
## Download CSV File

The Cardinals Major League Baseball schedule can be found at
http://mlb.mlb.com/search/?query=csv+scheule&c_id=mlb or
http://stlouis.cardinals.mlb.com/schedule/downloadable.jsp?c_id=stl&year=2014. Just change the year
on the second URL to the year you want. From these sites you can download a csv file with the full
seasons schedule. 

## Make _sportsschedule_ table

Once you have the csv file run _~/currentVersion/csvToDb.php_ which will add the
seasons schedule information to the _sportsschedule_ table:

<pre>
CREATE TABLE `sportsschedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('mlb','nfl','nbl') DEFAULT 'mlb',
  `image` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `team` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=684 DEFAULT CHARSET=utf8;
</pre>

The _csvToDb.php_ program takes the first second forth and fifth fields from the csv file and adds
them to the database. The _mkmlbschedule-image.php_ program gets the next three upcoming games from
the database and creates an new image using the template image at *~/images/mlb.png* which has an empty
area in the center. That image is placed in the *~/adscontent* directory as _mlb.png_.

## CRON PROGRAM

There should be an entry in the crontab to run the _~/currentVersion/mkmlbschedule-image.php_ program
every day (currently at 7am).

    0 7 * * *    /kunden/homepages/45/d454707514/htdocs/currentVersion/mkmlbschedule-image.php

The image is placed in the *~/adscontent* directory and is named _mlb.png_

## Using _cpanel.php_ and _adsCpanel.admin.php_

Using the Control Panel go to the *Show Settings* and set the *Allow Ads* button to
_yes_ and post the change. Then use the _Ads Cpanel (adsCpanel.admin.php)_ and make sure the _mlb.png_
photo is 'active'. It should be because I brute forced it into the _ads_ table using mysql. There
should probably be a better way of adding this image to the _ads_ table but for now there isn't.

Once the _allowAds_ flag is set in the _appinfo_ table and the _~/adscontent/mlb.png_ is set active in the
_ads_ table the MLB schedule should show during every commercial break.

## _mkmlbschedule.php_ and _mkmlbschedule-image.php_

There is also a _mkmlbschedule.php_ program which does something similar to the
_mkmlbschedule-image.php_ but instead of making a _png_ image it makes _mlb.html_ which uses the template
*~/images/mlb.png* and puts a &lt;ul&gt; list of the next three games.
We __DO NOT__ use _mkmlbschedult.php_ program currently.
I think the _mkmlbschedule-image.php_ program does a better job.

