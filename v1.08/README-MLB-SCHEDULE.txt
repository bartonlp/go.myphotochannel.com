BLP 2014-04-25 -- 

The Cardinals Major League Baseball schedule can be found at
http://mlb.mlb.com/search/?query=csv+scheule&c_id=mlb or
http://stlouis.cardinals.mlb.com/schedule/downloadable.jsp?c_id=stl&year=2014. Just change the year
on the second URL to the year you want. From these sites you can download a csv file with the full
seasons schedule. Once you have the csv file run ~/currentVersion/csvToDb.php which will add the
seasons schedule information to the sportsschedule table:

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

There should be an entry in the crontab to run the ~/currentVersion/mkmlbschedule-image.php program
every day (currently at 7am).

0 7 * * *    /kunden/homepages/45/d454707514/htdocs/currentVersion/mkmlbschedule-image.php

The image is placed in the ~/adscontent directory and is named mlb.png

The csvToDb.php program takes the first second forth and fifth fields from the csv file and adds
them to the database. The mkmlbschedule-image.php program gets the next three upcoming games from
the database and creates an new image using the template image at ~/images/mlb.png which has an empty
area in the center. That image is placed in the ~/adscontent directory as mlb.png.

Using the Control Panel (cpanel.php) go to the 'Show Settings' and set the 'Allow Ads' button to
'yes' and post the change. Then use the 'Ads Cpanel' (adsadmin.php) and make sure the mlb.png photo
is 'active'. It should be because the I brute forced it into the 'ads' table using mysql. There
should probably be a better way of adding this image to the 'ads' table but for now there isn't.

Once the allowAds flag is set in the appinfo table and the ~/adscontent/mlb.png is set active in the
ads table the MLB schedule should show during every commercial break.

There is also a mkmlbschedule.php program which does something similar to the
mkmlbschedule-image.php but instead of making a png image it makes mlb.html which uses the template
~/images/mlb.png and puts a <ul> list of the next three games. We DO NOT use this program currently.
I think the mkmlbschedule-image.php program does a better job.

