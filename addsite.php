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

require_once("ui.inc");
require_once("urls.inc");

$gTitle = "Add a Site";
$gRurl = ( array_key_exists('rurl', $_GET) ? $_GET['rurl'] : '' );
$is_valid_url = false;
if ( $gRurl ) {
	// Do some basic validation
	$is_valid_url = preg_match("/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i", $gRurl);
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

<h1><?php echo $gTitle ?></h1>

<?php
if ( $gRurl ) {
	if ( ! $is_valid_url ) {
		echo "<p class=warning>The URL entered is invalid: $gRurl</p>\n";
	}
	else {
		addSite($gRurl);  // queue it for adding
		echo "<p class=warning>$gRurl will be added within five business days.</p>\n";
	}
}
?>

<script type="text/javascript">
function confirmAdd() {
	var url = document.getElementById("rurl").value;
	if ( ! url ) {
		alert("Please select a URL.");
	}
	else if ( confirm("This will add " + url + " to the HTTP Archive crawls and record data about the site. Do you want to continue?") ) {
		return true;
	}

	return false;
}
</script>

<p>
Enter the URL you want to add to the HTTP Archive.
</p>

<form name=addsite action="<? echo $_SERVER['PHP_SELF'] ;?>" onsubmit="return confirmAdd()">
URL:
<span class="ui-widget" style="font-size: 1em;"> <input id="rurl" name="rurl" style="margin: 0;" size=35 /> </span>
<input type="submit" value="Add" name="submit" placeholder="http://www.example.com" style="margin: 0; margin-left: 1em;" />
</form>


<?php echo uiFooter() ?>

</body>
</html>
