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

require_once("../settings.inc");
require_once("../utils.inc");

// $gParamLabel is a hack to allow for require(copy.php)
$gLabel = ( isset($gParamLabel) ? $gParamLabel : $argv[1] );
if ( !$gLabel ) {
	lprint("You must specify a label.");
	exit();
}

// find min & max pageid of the specified run
$query = "select min(pageid) as minid, max(pageid) as maxid from $gPagesTable where label='$gLabel';";
$row = doRowQuery($query);
$minid = $row['minid'];
$maxid = $row['maxid'];
lprint("Run \"$gLabel\": min pageid = $minid, max pageid = $maxid");



// copy the rows to production
if ( ! $gbMobile && ( $gPagesTableDesktop != $gPagesTableDev ) ) {
	$count = doSimpleQuery("select count(*) from $gPagesTableDesktop where pageid >= $minid and pageid <= $maxid;");
	if ( $count ) {
		lprint("Rows already copied.");
	}
	else {
		lprint("Copy 'requests' rows to production...");
		doSimpleCommand("insert into $gRequestsTableDesktop select * from $gRequestsTableDev where pageid >= $minid and pageid <= $maxid;");

		lprint("Copy 'pages' rows to production...");
		doSimpleCommand("insert into $gPagesTableDesktop select * from $gPagesTableDev where pageid >= $minid and pageid <= $maxid;");

		// TODO - should we do this for $gbMobile too???
		lprint("Copy 'urls' rows to production...");
		// This is scary but the issue is we need to clear out all the previous ranks, optouts, others, etc. and use what's in urlsdev.
		doSimpleCommand("delete from $gUrlsTableDesktop;");
		doSimpleCommand("insert into $gUrlsTableDesktop select * from $gUrlsTableDev;");

		lprint("...DONE.");
	}
}



// Compute stats
require_once("../stats.inc");
require_once("../dbapi.inc");
$device = ( $gbMobile ? "iphone" : "IE8" );

if ( getStats($gLabel, "All", $device) ) {
	lprint("Stats already computed.");
}
else {
	lprint("Computing stats...");

	// remove any incomplete cache data that might have been created during the crawl
	removeStats($gLabel, NULL, $device);

	computeMissingStats($device, true);

	if ( ! $gbMobile && ( $gStatsTableDesktop != $gStatsTableDev ) ) {
		lprint("Copy stats to production...");
		$cmd = "replace into $gStatsTableDesktop select * from $gStatsTableDev where device='IE8';";
		doSimpleCommand($cmd);
	}
	lprint("...stats computed and copied.");
}



// mysqldump file
$dumpfile = dumpfileName($gLabel);
if ( file_exists("$dumpfile.gz") ) {
	lprint("Mysqldump file \"$dumpfile\" already exists.");
}
else {
	lprint("Creating mysqldump file $dumpfile ...");
	if ( $gbMobile ) {
		$cmd = "mysqldump --where='pageid >= $minid and pageid <= $maxid' --no-create-db --no-create-info --skip-add-drop-table --complete-insert -u $gMysqlUsername -p$gMysqlPassword -h $gMysqlServer $gMysqlDb $gRequestsTableMobile $gPagesTableMobile | gzip > $dumpfile.gz";
	}
	else {
		$cmd = "mysqldump --where='pageid >= $minid and pageid <= $maxid' --no-create-db --no-create-info --skip-add-drop-table --complete-insert -u $gMysqlUsername -p$gMysqlPassword -h $gMysqlServer $gMysqlDb $gRequestsTableDesktop $gPagesTableDesktop | gzip > $dumpfile.gz";
	}
	exec($cmd);

	lprint("...mysqldump file created: $dumpfile.gz");
}


// stats mysql dump - create this after all crawls both desktop & mobile
$dumpfile = "../downloads/httparchive_stats";
lprint("Creating mysqldump file $dumpfile ...");
$cmd = "mysqldump --no-create-db --no-create-info --skip-add-drop-table --complete-insert -u $gMysqlUsername -p$gMysqlPassword -h $gMysqlServer $gMysqlDb $gStatsTableDesktop | gzip > $dumpfile.gz";
exec($cmd);
lprint("...mysqldump file created: $dumpfile.gz");

// only create these for desktop
if ( ! $gbMobile ) {
	// schema mysql dump
	$dumpfile = "../downloads/httparchive_schema.sql";
	lprint("Creating mysqldump file $dumpfile ...");
	$cmd = "mysqldump --no-data --skip-add-drop-table -u $gMysqlUsername -p$gMysqlPassword -h $gMysqlServer $gMysqlDb $gStatsTableDesktop $gRequestsTableDesktop $gPagesTableDesktop $gRequestsTableMobile $gPagesTableMobile > $dumpfile";
	exec($cmd);
	lprint("...mysqldump file created: $dumpfile");

	// urls mysql dump
	$dumpfile = "../downloads/httparchive_urls";
	lprint("Creating mysqldump file $dumpfile ...");
	$cmd = "mysqldump --no-create-db --no-create-info --skip-add-drop-table --complete-insert -u $gMysqlUsername -p$gMysqlPassword -h $gMysqlServer $gMysqlDb $gUrlsTableDesktop | gzip > $dumpfile.gz";
	exec($cmd);
	lprint("...mysqldump file created: $dumpfile.gz");
}


cprint(date("G:i") . ": DONE copying latest run to production.");
