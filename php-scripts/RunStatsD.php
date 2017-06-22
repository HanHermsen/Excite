<?php

require_once 'includes/RunStatsC.incl'; // stats Class
require_once 'includes/DBx.incl'; // PDO db access Class for Laravel DB:: wrapping



$DB = new DBx;
$qs = 'SELECT id FROM questions WHERE response > 0 ';
$questionIds = $DB->select($qs);

$qs = 'SELECT text FROM sleutels ';
$sleutels = $DB->select($qs);
$sleutels[] = (Object) [ 'text' => 'Map'];
$sleutels[] = (Object) [ 'text' => 'Mini'];

while (true) {
	$startTime = time();
	echo "New loop\n";
	foreach ( $questionIds as $qId ) {
		$questionId = $qId->id;
		foreach ( $sleutels as $s ) {
			$statsType = $s->text;
			$viewType = 'XXX';
			if ($statsType == 'Map')
				$viewType = 'Geo';
			new RunStatsC($questionId, $statsType, $viewType, 0, $DB);
		}
	}
	$runTime = time() - $startTime;
	$hours = floor($runTime / 3600);
	$minutes = floor(($runTime / 60) % 60);
	$seconds = $runTime % 60;
	$d = date(DateTime::RFC822);
	echo "End loop " . $d . "\nTime to run this loop: $hours:$minutes:$seconds\n";	
	sleep(5);
}
