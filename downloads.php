<?php 
/*
Copyright 2010 Google Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

require_once("ui.php");
require_once("utils.php");

$gTitle = "Downloads";

$gaNormalFiles = array(
                       array( "httparchive_Jun_15_2011.gz", "June 15 2011" ),
                       array( "httparchive_June_1_2011.gz", "June 1 2011" ),
                       array( "httparchive_May_16_2011.gz", "May 16 2011" ),
                       array( "httparchive_Apr_30_2011.gz", "Apr 30 2011" ),
                       array( "httparchive_Apr_15_2011.gz", "Apr 15 2011" ),
                       array( "httparchive_Mar_29_2011.gz", "Mar 29 2011" ),
                       array( "httparchive_Mar_15_2011.gz", "Mar 15 2011" ),
                       array( "httparchive_Feb_26_2011.gz", "Feb 26 2011" ),
                       array( "httparchive_Feb_11_2011.gz", "Feb 11 2011" ),
                       array( "httparchive_Jan_31_2011.gz", "Jan 31 2011" ),
                       array( "httparchive_Jan_20_2011.gz", "Jan 20 2011" ),
                       array( "httparchive_Dec_28_2010.gz", "Dec 28 2010" ),
                       array( "httparchive_Dec_16_2010.gz", "Dec 16 2010" ),
                       array( "httparchive_Nov_29_2010.gz", "Nov 29 2010" ),
                       array( "httparchive_Nov_15_2010.gz", "Nov 15 2010" ),
                       array( "httparchive_Nov_6_2010.gz", "Nov 6 2010" ),
                       array( "httparchive_Oct_22_2010.gz", "Oct 22 2010" ),
                       array( "httparchive_Oct_2010.gz", "Oct (5) 2010" )
                      );

$gaMobileFiles = array(
                       array( "httparchive_mobile_May_16_2011.gz", "May 16 2011" ), 
                       array( "httparchive_mobile_May_12_2011.gz", "May 12 2011" ), 
                       array( "httparchive_mobile_May_8_2011.gz", "May 8 2011" ), 
                       array( "httparchive_mobile_May_7_2011.gz", "May 7 2011" ), 
                       array( "httparchive_mobile_May_6_2011.gz", "May 6 2011" )
                      );

function listFiles($aFiles) {
	$sHtml = "";
	foreach($aFiles as $fileinfo) {
		list($filename, $label) = $fileinfo;
		$filesize = filesize("./downloads/$filename");
		$size = ( $filesize > 1024*1024 ? round($filesize/(1024*1024)) . " MB" : round($filesize/(1024)) . " kB" );
		$sHtml .= "  <li> <a href='downloads/$filename'>$label</a> ($size)\n";
	}

	return $sHtml;
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $gTitle ?></title>
<meta charset="UTF-8">

<?php echo headfirst() ?>
<link type="text/css" rel="stylesheet" href="style.css" />
</head>

<body>

<?php echo uiHeader($gTitle); ?>
<h1>Downloads</h1>

<p>
There's a download file for each run:
</p>

<p>
HTTP Archive (desktop):
</p>

<ul class=indent>
<?php echo listFiles($gaNormalFiles) ?>
</ul>

HTTP Archive Mobile:
<ul class=indent>
<?php echo listFiles($gaMobileFiles) ?>
</ul>

<p>
The downloaded file was generated by <a href="http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html">mysqldump</a> and then gzipped.
The mysqldump file does <em>not</em> contain the commands to create the MySQL database and tables.
To restore these mysqldump downloads:
</p>

<ol class=indent>
  <li> Install the <a href="http://code.google.com/p/httparchive/source/checkout">HTTP Archive source code</a>.
  <li> Modify <code>settings.inc</code> to have the appropriate MySQL settings.
  <li> Open the <code>admin.php</code> page in your browser and click on the link to create the MySQL tables.
  <li> Ungzip the downloaded mysqldump file.
  <li> Import the mysqldump file using this command:<br><code>mysql -v -u MYSQL_USERNAME -pMYSQL_PASSWORD -h MYSQL_HOSTNAME MYSQL_DB < MYSQLDUMP_FILE</code>
</ol>

<?php echo uiFooter() ?>

</body>

</html>

