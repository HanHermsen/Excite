<?php
/* //use simple autoloader for Classes from ./includes/ Folder
// is alternative for require/include of individual Class files that could live there
spl_autoload_register(function ($className) {
    include 'includes/' . $className . '.incl';
}); */
require_once 'includes/DBx.incl'; // include PDO db access Class van Han.
                                  // Laravel's DB:: kan hier _niet_ worden gebruikt. Het kan, maar...
                                  // Het is nogal gedoe om dit voor elkaar te krijgen.

$db = new DBx; // maak een instance van de Class; connectie is naar Laravel project db

// voorbeeldje van gebruik: select() met query parameters; lijkt op DB::select() van Laravel
$limit = 3;
$responseLimit = 100;
$q = "SELECT id, question, response FROM questions WHERE response > ? LIMIT ?";
$questions = $db->select($q, [$responseLimit, $limit]);
var_dump($questions);
