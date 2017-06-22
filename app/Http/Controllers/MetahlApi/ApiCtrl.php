<?php
namespace Excite\Http\Controllers\MetahlApi;

use Illuminate\Routing\Controller as BaseController;
use DB;
//use Request;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;

class ApiCtrl extends BaseController {
	public function getToken() { // for POST security
		$ip = $_SERVER['REMOTE_ADDR'];
		$q  ='SELECT * FROM P_WebStats WHERE ip = ?';
		$res = DB::select($q,[$ip]);
		$id = 0;
		if ( count ($res) > 0 )
				$id = -1 * $res[0]->id;
		$out['id'] = $id;
		$out['token'] = csrf_token();
		return response()->json($out);
	}

	public function getGpsList(Request $req) {
		return response()->json($this->getGpsListNew($req));
	}
	public function getGpsListNew(Request $req) {
		$debug = $req->get('debug', 0);
		//$this->dd($debug);
		if ( $debug )
			$q = 'SELECT * FROM P_Scores3';
		else
			$q = 'SELECT * FROM P_Scores2';
		$scores = DB::select($q);
		//$this->dd($scores);
		$munScores = [];
		$provScores = [];
		$provScoresPop = [];
		$badList = [];
		$allCnt = 0;
		$notSharedCnt = 0;
		$notShared = 'Woonplaats niet gedeeld';
		$unknown = 'Woonplaats onbekend';
		foreach ( $scores as $sc ) {
			//$orgName = $sc->name;
			if ( $sc->name == $unknown ) {
				$sc->provCode = 1;
				$sc->prov = 'Niet gevonden';
				$sc->munLat = $sc->locLat = 53.56085;
				$sc->munLon = $sc->locLon = 6.75934;
			}
			if ( $sc->name == $notShared ) {
				$sc->provCode = 0;
				$sc->prov = 'Niet publiek bekend';
				$sc->munLat = $sc->locLat = 53.56085; //53.561667000
				$sc->munLon = $sc->locLon = 6.43215; //6.308073000
				$notSharedCnt += $sc->cnt;
			}

			$munAlert = false;
			$cnt = $sc->cnt;
			$flag = $sc->flag;
			if ( $flag == '0' ) {
				$munAlert = true;
			}
			if ( ! array_key_exists ($sc->mun, $munScores ) ) {
				$munScores[$sc->mun] = ['name' => $sc->mun, 'cnt' => $cnt, 'lat' => $sc->munLat, 'lon' => $sc->munLon, 'munAlert' => $munAlert];
			}
			else {
				$munScores[$sc->mun]['cnt'] += $cnt;
				if ($munAlert )
					$munScores[$sc->mun]['munAlert'] = $munAlert;
			}
			if ( ! array_key_exists ($sc->provCode, $provScores ) ) {
				if( $sc->provCode > 0 )
					$provScores[$sc->provCode] = [$sc->prov, $cnt, $cnt];
			}
			else {
				if( $sc->provCode > 0 )
					$provScores[$sc->provCode][1] = $provScores[$sc->provCode][2] += $cnt;
			}
			if ( ! array_key_exists ($sc->provCode, $provScoresPop ) ) {
				if( $sc->provCode > 1 ) {
					$provScoresPop[$sc->provCode] = [$sc->prov, $cnt, $cnt, $sc->popProv];
				}
			}
			else {
				if( $sc->provCode > 1 )
					$provScoresPop[$sc->provCode][1] = $provScoresPop[$sc->provCode][2] += $cnt;
			}
			$allCnt += $cnt;
			//if ( $orgName == $unknown ) continue;
			$sc->loc = $sc->name;
			$sc->flag = $flag;
			$sc->name = $sc->name . ' (gem. ' . $sc->mun . ')';
		}

		$munScores2 = [];
		foreach ($munScores as $ms) {
			$munScores2[] = $ms;
		}
		ksort($provScores); // sort on key value to get the standard province name ordering
		ksort($provScoresPop);
		$provScores2[0] = ['Provincie', 'Aantal', 'Annotation'];
		$provScoresPop2[0] = ['Provincie', 'Aantal per 1000 inwoners', 'Annotation', 'Inwoneraantal in honderdduizenden'];
		foreach ($provScores as $ps) {
			$provScores2[] = $ps;
		}
		foreach ($provScoresPop as $ps) {
			$ps[1] = $ps[2] = floor( $ps[1]/($ps[3]/1000));
			$ps[3]	= ceil($ps[3]/100000) ;		
			$provScoresPop2[] = $ps;
		}
		$out['locScores'] = $scores;
		$out['munScores'] = $munScores2;
		$out['provScores'] = $provScores2;
		$out['provScoresPop'] = $provScoresPop2;
		$out['allCnt'] = $allCnt;
		$out['notSharedCnt'] = $notSharedCnt;
		$out['badList'] = $badList;
		return $out;
		return response()->json($out);
	}
	public function tstGps(Request $r) {
		return $this->getGps2($r->get('name'));
	}
	private function getGpsNew($loc,$mun) {
		// replace non ascii by %
		//$name = preg_replace('/[^\x20-\x7E]/','%', $name);
		//$name = preg_replace('/[- ]/','%', $name);

		$q  = 'SELECT Gemeente AS mun, Glat AS munLat, Glong AS munLon, Plaats AS loc, Provincie AS prov, ';
		$q .= ' Plat AS locLat, Plong AS locLon FROM YixowPC ';
		$q .= 'WHERE (Plaats = ? AND Gemeente = ?) ';
		$q .= 'LIMIT 1';
		// $q .= 'GROUP BY Gemeente, Plaats ';
		$res = DB::select($q,[$loc, $mun]);
		return $res;
	}
	private function getGps($name) {
		// replace non ascii by %
		$name = preg_replace('/[^\x20-\x7E]/','%', $name);
		$name = preg_replace('/[- ]/','%', $name);

		$q  = 'SELECT Gemeente AS mun, Glat AS munLat, Glong AS munLon, Plaats AS loc, Provincie AS prov, ';
		$q .= ' Plat AS locLat, Plong AS locLon FROM YixowPC ';
		$q .= 'WHERE (Plaats LIKE ? ) LIMIT 1';
		//$q .= 'GROUP BY Gemeente, Plaats LIMIT 1';
		//$q .= 'WHERE (Plaats LIKE ? OR Gemeente LIKE ?) ';
		//$q .= 'GROUP BY Plaats';
		//$q .= 'WHERE MATCH(Gemeente) AGAINST( ? ) OR MATCH(Plaats) AGAINST( ? ) LIMIT 1';
		//$q .= 'WHERE SOUNDEX(Gemeente) = SOUNDEX(?) OR SOUNDEX(Plaats) = SOUNDEX(?) LIMIT 1';
		$res = DB::select($q,[$name]);
		return $res;
	}
	private function getGps2($name) {
		// replace non ascii by %
		$name = preg_replace('/[^\x20-\x7E]/','%', $name);
		$name = preg_replace('/[- ]/','%', $name);
		//$name = preg_replace('/[aeoui]/','%', $name);
		$q  = 'SELECT Gemeente AS mun, Glat AS munLat, Glong AS munLon, Plaats AS loc, Provincie AS prov, ';
		$q .= ' Plat AS locLat, Plong AS locLon FROM YixowPC ';
		$q .= 'WHERE Plaats LIKE ? ';
		$q .= 'GROUP BY Gemeente, Plaats';
		//$q .= 'WHERE MATCH(Gemeente) AGAINST( ? ) OR MATCH(Plaats) AGAINST( ? ) LIMIT 1';
		//$q .= 'WHERE SOUNDEX(Gemeente) = SOUNDEX(?) OR SOUNDEX(Plaats) = SOUNDEX(?) LIMIT 1';
		$res = DB::select($q,[$name]);
		if (count($res)) $res[0]->matchName = $name;
		return $res;
	}
	public function getLatLon (Request $r) {
		$out = $this->getLatLonQ($r->get('zipCode'));
		return response()->json($out);
	}	
	private function getLatLonQ ( $zipCode, $checkZip = false ) {
		$found = false;
		$lat = 0;
		$lng = 0;
		$q  = 'SELECT PClat, PClong FROM YixowPC ';
		$q .= 'WHERE PC LIKE CONCAT(?,"%") ';
		$q .= 'LIMIT 1';
		$result = DB::select($q, [$zipCode]);
		if ( count($result) > 0 ) {
			$found = true;
			$lat = $result[0]->PClat;
			$lng = $result[0]->PClong;
		}
		if ( $found && $checkZip ) { // get lat/lng of the 'other' nearby zipcode
			$newZip = $this->getZipCode($lat,$lng);
			$this->getLatLonQ($newZip, false);
		}
		return ['zipFound'=> $found, 'lat'=> $lat, 'lng'=> $lng];
	}
	// Get ZipCode
	private function getZipCode($lat,$lng) {

		$latLow = (substr($lat,0,5) - 0.01);
		$latHigh = (substr($lat,0,5) + 0.01);
		$lngLow = (substr($lng,0,4) - 0.01);
		$lngHigh = (substr($lng,0,4) + 0.01);
		
		$qGetZipCode = 'SELECT pc FROM YixowLatLong where (latitude >= ? and latitude<= ?) and (longitude >= ? and longitude <= ?) limit 1';
		$getZipCode = DB::select($qGetZipCode,[$latLow,$latHigh,$lngLow,$lngHigh]);
		
		if( count($getZipCode) == 0 )
			$getZipCode = '';
		else
			$getZipCode = $getZipCode[0]->pc;

    	return $getZipCode;
	}
	public function updateWebStatsScore (Request $r) {
		$allCnt = $r->get('allCnt');
		$id = $r->get('id');
		$qu = 'UPDATE P_WebStats ';
		$qu .= 'SET scoreOnCreate = ? ' ;
		$qu .= 'WHERE id = ?';
		DB::update($qu,[$allCnt,$id]);
		return response()->json([]);
	}
	public function updateClickCnt (Request $r) {
		$clickCnt = $r->get('clickCnt');
		$popups = $r->get('popupMon','');
		$id = $r->get('id');
		$qu = 'UPDATE P_WebStats ';
		$qu .= 'SET clickCnt = ?, popups = ? ' ;
		$qu .= 'WHERE id = ?';
		DB::update($qu,[$clickCnt,$popups,$id]);
		return response()->json([]); // just give empty response
	}
	public function updateWebStats (Request $r) {
		//$this->dd($r->all());
		$location = $r->get('location', ''); // heb je niks aan
		$browser = $r->get('browser', '');
		$timestamp = date('Y-m-d H:i:s');
		$ip = $_SERVER['REMOTE_ADDR'];
		$ref = $_SERVER['HTTP_REFERER']; // heb je niks aan
		//$ref = $_SERVER['REQUEST_URI']; // heb je niks aan
		//$rHost = $_SERVER['REMOTE_HOST']; // is afgeschermd
		$rHost = gethostbyaddr($ip); // dan maar zo
		$q  ='SELECT * FROM P_WebStats WHERE ip = ?';
		$res = DB::select($q,[$ip]);
		if ( count($res) == 0 ) {
			$qi = 'INSERT P_WebStats ';
			$qi .= 'SET ip = ?, cnt = 1, hostname = ?,createDate = ?, location = ?, browser = ? ';
			DB::insert($qi,[$ip, $rHost, $timestamp, $ref, $browser]);
			$updateId = DB::getPdo()->lastInsertId();
			$cnt = 1;
		} else {
			$id = $res[0]->id;
			$cnt = $res[0]->cnt + 1;
			// update the count
			$qu = 'UPDATE P_WebStats ';
			$qu .= 'SET cnt = ?, modDate = ? ' ;
			$qu .= 'WHERE id = ?';
			DB::update($qu,[$cnt, $timestamp,$id]);
			if ( $res[0]->scoreOnCreate == 0 ) 
				$updateId = $id; // try again
			else
				$updateId = -1 * $id; // do not update score
		}
		
		//$out['ok'] = true;
		//$out['ip'] = $_SERVER['REMOTE_ADDR'];
		//$out['cnt'] = $cnt;
		$out['updateId'] = $updateId;
		return response()->json($out);
	}
	
	private function dd($arg) {
		header("Access-Control-Allow-Origin: *");
		dd($arg);
	}
	
}