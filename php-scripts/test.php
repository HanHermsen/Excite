<?php

$dir = __DIR__;
//echo $dir;
$f = fopen("../.env", "r") or die("Unable to open file!");
$dbInfo = [];
while(!feof($f)) {
  $line = fgets($f);
  if ( strpos($line,'DB_HOST=') === 0 ) {
	$dbInfo['host'] = substr( $line, strlen('DB_HOST=')); 
  } elseif ( strpos($line,'DB_DATABASE=') === 0 ) {
	$dbInfo['db'] = substr( $line, strlen('DB_DATABASE=')); 
  } elseif ( strpos($line,'DB_USERNAME=') === 0 ) {
	$dbInfo['user'] = substr( $line, strlen('DB_USERNAME=')); 
  } elseif ( strpos($line,'DB_PASSWORD=') === 0 ) {
	$dbInfo['passwd'] = substr( $line, strlen('DB_PASSWORD='));
  }
  if ( count($dbInfo) == 4 ) break;
}

fclose($f);
var_dump($dbInfo);

