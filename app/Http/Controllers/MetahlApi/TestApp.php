<?php
namespace Excite\Http\Controllers\MetahlApi;

use Illuminate\Routing\Controller as BaseController;
use DB;

use Illuminate\Http\Request;


class TestApp extends BaseController {
	private $saintIn = ['Sint ','Sint-', 'St.-', 'St. '];
	private $saintOut = ['Sint ','Sint-', 'St.-', 'St. ', 'St ', 'St-'];

	public function getLocNames(Request $r) {
		//$test = $r->get('test', -1);
		$alias = [];
		$q = 'SELECT name FROM P_Scores2 ';
		$q .= 'WHERE name <> "Woonplaats niet gedeeld" AND name <> "Woonplaats onbekend" ';
		$q .= 'GROUP BY name';
		$res = DB::select($q);
		$locNames = [];
		foreach ($res as $r ) {
			$name = $r->name;
			$locNames[] = $name;
			if ( strpos($name, "'" ) === 0 ) {
				if ( strpos($name,"'t ") === 0 ||
					 strpos($name,"'s ") === 0 ) {
					$name = substr_replace( $name, '-', 2, 1);
						
				} else {
					if ( strpos($name,"'t-") === 0 ||
						 strpos($name,"'s-") === 0 ) {
						$name = substr_replace( $name, ' ', 2, 1);
					}
				}
				$locNames[] = $name;
				$alias[$name] = $r->name;
			}
			foreach( $this->saintIn as $sin ) {
				if ( strpos($name,$sin) === 0 ) {
					foreach ($this->saintOut as $sout) {
						if ($sout == $sin ) continue;
						$name2 = substr_replace( $name, $sout, 0, strlen($sin));
						$locNames[] = $name2;
						$alias[$name2] = $r->name;					
					}				
				}			
			}
			/*
			if ( $name == "'s-Gravenhage" ) {
				$locNames[] = '2561';
				$alias['2561'] = $r->name;
			} */
		}
		$q = 'SELECT mun FROM P_Scores2 ';
		$q .= 'WHERE mun <> "Woonplaats niet gedeeld" AND name <> "Woonplaats onbekend" ';
		$q .= 'GROUP BY mun';
		$res = DB::select($q);
		$munNames = [];
		foreach ($res as $r ) {
			if ( ! in_array($r->mun,$locNames) )
			$munNames[] =  $r->mun;
		}
		//if ( $test != -1 )
			$q = 'SELECT name, typeAheadAlias FROM P_Scores_Alias2 ';
		//else
			//$q = 'SELECT name, typeAheadAlias FROM P_Scores_Alias2 ';
		$q .= 'WHERE typeAheadAlias IS NOT NULL AND typeAheadAlias <> "" ';
		$q .= 'GROUP BY typeAheadAlias';
		$res = DB::select($q);

		foreach ($res as $r ) { // merge in locnames too
			//if ($r->displayName == null) continue;
			if ( ! in_array($r->typeAheadAlias, $locNames) ) {
				$locNames[] = $r->typeAheadAlias;
				$alias[$r->typeAheadAlias] = $r->name;
			}
		}
		sort($locNames);
		
		$out['locNames'] = $locNames;
		$out['munNames'] = $munNames;		
		$out['mapList'] = $alias;		
		return response()->json($out);
	}
	
	public function getZipNames(Request $req) {
		$zip = $req->get('zip', 0);
		$zip = $zip . '%';
		$q  = 'SELECT Plaats FROM YixowPC ';
		$q .= 'WHERE PC LIKE ? ';
		$q .= 'GROUP BY Plaats ';
		$res = DB::select($q,[$zip]);
		return response()->json($res);
	}
	
}