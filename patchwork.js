<?php 
require_once("ui.inc");
require_once("utils.inc");
require_once("dbapi.inc");


// Return an array of frame times for each page.
$gN = getParam('n');
$gL = getParam('l');
if ( ! $gN || ! $gL ) {
	exit();
}


// Ask for twice as many as needed so we can filter out adult sites.
$query = "select pageid, url, wptid, wptrun from $gPagesTable where label='$gL' and rank > 0 and rank <= " . (2*$gN) . " order by rank asc;";
$result = doQuery($query);
if ( 0 == mysql_num_rows($result) ) {
	mysql_free_result($result);
	// Older crawls do NOT have values for "rank". Use today's rank.
	$query = "select u.rank, pageid, url, wptid, wptrun from $gPagesTable, $gUrlsTable as u where label='$gL' and u.rank > 0 and u.rank <= " . (2*$gN) . " and urlOrig=url order by u.rank asc;";
	$result = doQuery($query);
}

$wptServer = wptServer();
$i = 0;
$msMax = 0;
while ($row = mysql_fetch_assoc($result)) {
	$url = $row['url'];
	if ( ! isAdultContent($url) ) {
		$pageid = $row['pageid'];
		$wptid = $row['wptid'];
		$wptrun = $row['wptrun'];

		$xmlurl = "{$wptServer}xmlResult.php?test=$wptid";
		$xmlstr = fetchUrl($xmlurl);
		$xml = new SimpleXMLElement($xmlstr);
		$frames = $xml->data->run[($wptrun - 1)]->firstView->videoFrames;
		if ( $frames->frame ) {
			$sJS = "";
			foreach($frames->frame as $frame) {
				$ms = floatval($frame->time) * 1000;
				$msMax = max($msMax, $ms);
				$sJS .= ( $sJS ? ", " : "" ) . "$ms: 1"; // must NOT end with a comma - poop
			}
			echo "hPages[$pageid] = {" . $sJS . "};\n";
		}
		
		$i++;
		if ( $i >= $gN ) {
			break;
		}
	}
}
mysql_free_result($result);
echo "\nvar msMax = $msMax;\n";
?>
