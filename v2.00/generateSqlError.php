#! /usr/bin/php6 -q
<?php
// This file generates a SQL error to see if it gets logged in the PHP_ERROR.log
// This header section was taken from 'emailphoto.ifttt.php'
if(!getenv("SITELOADNAME")) {
  putenv("SITELOADNAME=/kunden/homepages/45/d454707514/htdocs/vendor/bartonlp/site-class/includes/siteload.php");
}
$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
ErrorClass::setNoEmailErrs(true);
ErrorClass::setNohtml(true);
define(DOC_ROOT, $_site->path);
$S = new Database($_site);
// End of header

// Say we will generate an error  
error_log("generateSqlError.php: Generating an sql error, 'select * from xxx'");
$sql = "select * from xxx";
// This should generate an error
$S->query();

