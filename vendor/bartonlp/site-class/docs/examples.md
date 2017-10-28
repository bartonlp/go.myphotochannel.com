# Examples

---

The code shown below can be found in the 'examples' directory at http://github.com/bartonlp/site-class or from your project root at 'vendor/bartonlp/site-class/examples'. There is an 'EXAMPLES.md' and EXAMPLES.html in the 'examples' directory.

<p style="color: green">The code in the 'examples' directory has actually been tested and runs. 
The code in this README was originally copied in from the examples code but may have changed for some reason. 
Therefore you should use the examples code rather than doing a copy and past from this README.</p>

If you have Apache or Nginx installed then you should have made your project root somewhere within your DocumentRoot, for example '/var/www/html/myproject'.

If you don't have Apache or Nginx installed on your computer you can use the PHP server. Do the following from your project root:

```bash
php -S localhost:8080
```

Then use your browser by entering `http://localhost:8080/vendor/bartonlp/site-class/README.html` in the browsers location bar.

The code in the 'examples' directory uses the **sqlite3** database engine. There should be a 'test.sdb' database file in the 'examples' directory already.

I have included a 'sqlite.sql' file that can be run from the command line if you want to recreate the 'members' table.

You will need to get sqlite3 and get the PHP sqlite packages along with mysql etc. From the command line in the directory where the SiteClass was downloaded:

```bash
$ cd examples
$ sqlite3 test.sdb
sqlite> drop table members;
sqlite> .read sqlite.sql
sqlite> .table
members
sqlite> select rowid,* from members;
1|Big|Joe
2|Little|Joe
3|Barton|Phillips
4|Someone|Else
sqlite> .quit
$
```

This should create a new 'members' table in the 'test.sdb' database.

<hr>

There are a number of ways to use the framework:

**First** you can just use the SiteClass all by itself.

```php
<?php
// example1.php

require_once("../../../autoload.php");

$siteinfo = array(
  'siteDomain' => "localhost",
  'siteName' => "Example1",
  'copyright' => "2016 Barton L. Phillips",
);

$S = new SiteClass($siteinfo);

list($top, $footer) = $S->getPageTopBottom();
echo <<<EOF
$top
<h1>Example 1</h1>
<p>Hello World</p>
<hr>
$footer
EOF;
```

That is the simplest usage. You get a generic head and a generic footer. No database or other stuff.

<hr>

You can extend this by adding a database either by instantiating the 'Database' class directly or indirectly. 

```php
<?php  
// example2.php

require_once("../../../autoload.php");

$_site = array(
  'siteDomain' => "localhost",
  'siteName' => "Example2",
  'copyright' => "2016 Barton L. Phillips",
  'memberTable' => "members",
  'noTrack' => true, // don't do tracking logic in SiteClass
  'dbinfo' => array(
    'database' => 'test.sdb',
    'engine' => 'sqlite3'
  ),
  'count' => false
);

ErrorClass::setNoEmailErrs(true);
ErrorClass::setDevelopment(true);

$S = new SiteClass($_site);

list($top, $footer) = $S->getPageTopBottom();

// Do some database operations
$S->query("select fname, lname from $S->memberTable");

$names = '';

while(list($fname, $lname) = $S->fetchrow('num')) {
  $names .= "$fname $lname<br>";
}

echo <<<EOF
$top
<h1>Example 2</h1>
<p>$names</p>
<hr>
$footer
EOF;
```

The above example uses the 'query' and 'fetchrow' methods to do some database operations.

<hr>

The database could also be instantiated explicitly as follows:

```php
<?php
// example3.php

require_once("../../../autoload.php");

$_site = array(
  'siteDomain' => "localhost",
  'siteName' => "Example3",
  'copyright' => "2016 Barton L. Phillips",
  'memberTable' => "members",
  'noTrack' => true, // do tracking logic in SiteClass
  'count' => false
);

$dbinfo = array(
  'database' => 'test.sdb',
  'engine' => 'sqlite3'
);

ErrorClass::setNoEmailErrs(true);
ErrorClass::setDevelopment(true);

$db = new Database($dbinfo);

$S = new SiteClass($_site);
$S->setDb($db);

// The rest is like the above example. 

list($top, $footer) = $S->getPageTopBottom();

// Do some database operations
$S->query("select fname, lname from $S->memberTable");

$names = '';

while(list($fname, $lname) = $S->fetchrow('num')) {
  $names .= "$fname $lname<br>";
}

echo <<<EOF
$top
<h1>Example 3</h1>
<p>$names</p>
<hr>
$footer
EOF;
```

---

You can also use the 'siteload.php' file to load the json file 'mysitemap.json' to further automate working with the framework. This file is in the 'includes' directory. There is a 'mysitemap.json.php' file that is well commented. You can uncomment sections of this file or add items as needed.

You can run this file as a CLI program and it will output to 'stdout'. Create your 'mysitemap.json' file as follows:

```bash
./mysitemap.json.php >mysitemap.json
```

Copy the created file to your project directory. 
There is already a 'mysitemap.json' file in the 'examples' directory.

I set the Apache2 environment variable 'SITELOAD' to point to my 'siteload.php' file in 'vendor/bartonlp/site-class/includes'. You can add it to your '/etc/apache2/apache2.conf', to an apache2 virtual host or to your '.htaccess' file. I will assume you have done this in the following examples.

```bash
SetEnv SITELOAD /var/www/vendor/bartonlp/site-class/includes/siteload.php
```

This example uses the 'mysitemap.json' explicitly and converts it into an object. The next example uses 'SITELOAD'.

```php
<?php
// Example4.php

require_once("../../../autoload.php");
$_site = json_decode(file_get_contents("mysitemap.json"));

ErrorClass::setNoEmailErrs(true);
ErrorClass::setDevelopment(true);

$S = new SiteClass($_site);

list($top, $footer) = $S->getPageTopBottom();

// Do some database operations
$S->query("select fname, lname from $S->memberTable");

$names = '';
while(list($fname, $lname) = $S->fetchrow('num')) {
  $names .= "$fname $lname<br>";
}

echo <<<EOF
$top
<h1>Example 4</h1>
<p>$names</p>
<hr>
$footer
EOF;
```

---

In addition to the SiteClass and Database classes there are several others classes in the 'database-engins' directory:

* ErrorClass
* SqlException
* dbTables
* and a file with helper functions ('helper-functions.php').

---

The dbTables class uses the Database class to make creating tables simple.

```php
<?php
// example5.php

$_site = require_once(getenv("SITELOAD")."/siteload.php");

ErrorClass::setNoEmailErrs(true);
ErrorClass::setDevelopment(true);

$S = new $_site->className($_site);
$T = new dbTables($S);

// Pass some info to getPageTopBottom method
$h->title = "Example 5"; // Goes in the <title></title>
$h->banner = "<h1>Example 5</h1>"; // becomes the <header> section
// Add some local css to but a border and padding on the table 
$h->css = <<<EOF
  <style>
main table * {
  padding: .5em;
  border: 1px solid black;
}
  </style>
EOF;

list($top, $footer) = $S->getPageTopBottom($h);

// create a table from the memberTable
$sql = "select * from $S->memberTable";
list($tbl) = $T->maketable($sql);

echo <<<EOF
$top
<main>
<h3>Create a table from the members database table</h3>
<p>The members table follows:</p>
$tbl
</main>
<hr>
$footer
EOF;
```

The 'maketable' method takes several optional arguments to help setup the table. Using the options you can give your table an id or class or set any other attributes. You can also pass a 'callback' function which can modify the rows as they are selected (see the 'example-insert-update.php' file in the 'examples' directory for more information). Also take a look at [dbTables Documentation](dbTables.html).

---

## Using Other Libraries

### Twig

You can use other frameworks or templeting engines. Here we will use *Twig* a popular templet engine. 
*Twig* is a super powerful templet engine with looping and conditional statements and much more. 
Here we do just about the minimum just as an example.

There is no 'vendor' directory in the examples directory. You will need to run 'composer' to get *Twig* and *Altorouter* installed. 

```bash
composer install
```

The check out the 'example-with-twig.php' in the repositories 'examples' directory.

If you get an error check out 'EXAMPLES.html' in the 'examples' directory.

### ReST Routing with Altorouter

If you need ReST routing (or pretty routing or SEO friendly routing as it is sometime called) 
you could use one of the popular routing engines available with *Meteor*, *Laravel*, *Synfony2* or *Silex* 
but then again by that point you might as well just bite the bullet and spend the hours or 
days trying to figure out those frameworks.

There is a pretty simple router called *Altorouter* which can be used without too much work. 

The file 'example-route.php' would normally be your 'index.php' in a production environment. 
When using a server with Apache2 you would need a '.htaccess' file in the directory where the 'index.php' lives. 
The '.htaccess' file would look like this:

```bash
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```

This says that if the requested filename does not exist go to the 'index.php' file instead.

To test this without renaming the 'example-route.php' you can use the PHP server like this from the 'examples' directory on your server:

```bash
HOME=/var/www php -S localhost:8080 example-route.php
```

The PHP server uses the 'example-route.php' file and you don't need a '.htaccess' file.

Prefixing the command with 'HOME=...' sets the 'HOME' environment variable. Have it point to your 'vendor/autoload.php'
file. 

Now you can run the program. It will display a table and a form you can use to insert new records. 
Also a button lets you reset the database table to its original state. 
If you click on a number in the 'ID' column of the table you get an edit page where you can change the names.

You can also get to the edit page by entering the URI '/edit/3' for example. 
That will take you to the edit page for 'ID' three. 
From that page you and enter the URI '/home' which will take you back to the home page.

The advantage of ReST is you do not actually need a '/edit/3' or a '/home' directory on your system. 
These are just syntactical links to control logic and as a result are easily modified. 
Also some people think that '/edit/3' somehow looks cooler then '?edit=3', I am not sure I agree. 
ReST is syntactical sugar that takes more code and obfuscates what is really going on.  
But that is just my opinion.

---

[Examples](examples.html)
[dbTables](dbTables.html)
[SiteClass Methods](siteclass.html)
[Additional Files](files.html)
[Analysis and Tracking](analysis.html)
[Testing](testing.html)
[Index](index.html)

## Contact Me

Barton Phillips : [bartonphillips@gmail.com](mailto://bartonphillips@gmail.com)  
Copyright &copy; 2015 Barton Phillips  
Project maintained by [bartonlp](https://github.com/bartonlp)
