# Readme for v2.00

This is the 2016, 2017 version of the Slideshow and Cpanel along with other programs.
This version uses the $HOME/vendor/bartonlp/site-class programs.
The SiteClass is repositoied on http://github.com/bartonlp/site-class.

Version 2.00 is mirrored on my DigitalOcean server at http://www.bartonphillips.org as
well as on the main site at 1and1.com http://go.myphotochannel.com.

I have set up the .gitignore to not track any of the file that are server specific
(except .htaccess which should work on both sites).
The mysitemap.json, $HOME/vendor, the adscontent/, content/ and other server specific file
and directories are not tracked.

I have put Pusher.php in the v2.00/includes directory and added to the composer.json
file so is in the autoload.php file in $HOME/vendor.

<style>
pre {
	margin:1em 0;
	font-size:1rem;
	background-color:#FCFFFF; /*#eee;*/
	border:1px solid #ddd;
	padding: .5rem;
	line-height:1.5em;
	color: black; /*#444;*/
  max-height: 20rem;
	overflow:auto;
	box-shadow:rgba(0,0,0,0.07) 0 1px 2px inset;
	border-radius:3px;
	-moz-border-radius:3px;border-radius:3px;
}
figure {
  width: 50%;
  font-style: italic;
  font-size: smaller;
  text-indent: 0;
  border: thin silver solid;
  margin: 0.5em;
  padding: 0.5em;
}
</style>

<figure>
<figcaption>composer.json</figcaption>
<pre>
"autoload": {
  "classmap": [
    "v2.00/includes"
  ]
}
</pre>
</figure>

