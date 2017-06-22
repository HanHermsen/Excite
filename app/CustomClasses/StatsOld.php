<?php

namespace Excite\CustomClasses;

use DB;
use Auth;
use Request;
use Excite\CustomClasses\UserProfile;

class StatsOld {
	const PIE = 0;
	const ANSB = 1;
	const CATB = 2;
	const GEOPROV = 3;
	const MAP = 4;
	
	const ABS_LIMIT = 5;

	// javascript object literal data strings; to be embedded in blade
	private $pieData = '';
	private $answerBarData = '';
	private $categoryBarData = '';
	private $geoProvData = '';
	private $geoUnshared; // string met aantal niet gedeelde postcodes/woonplaatsen
	
	private $munMapData = ''; // municipalities/gemeenten
	private $locMapdata = ''; // locations/plaatsen
	private $zipMapData = ''; // zipcodes full
	private $zip4MapData = '';// zipcodes 4 cijfers
	
	private $colorScheme = [
	/*0*/	['DarkGreen'],
	/*1*/	['Red','DarkGreen'],
	/*2*/	['red','blue', 'darkGreen'],
	/*3*/	['red','blue', 'Gold', 'DarkGreen'],
	/*4*/	['DarkRed','Red', 'OrangeRed', 'DarkOrange','Orange', 'Yellow', 'Gold', 'GreenYellow','Green', 'DarkGreen'],
	/*5*/	['DarkRed','Red', 'OrangeRed', 'DarkOrange','Orange', 'Yellow', 'Gold', 'GreenYellow','Green', 'DarkGreen'],
	/*6*/	['DarkRed','Red', 'OrangeRed', 'DarkOrange','Orange', 'Yellow', 'Gold', 'GreenYellow','Green', 'DarkGreen'],
	/*7*/	['DarkRed','Red', 'OrangeRed', 'DarkOrange','Orange', 'Yellow', 'Gold', 'GreenYellow','Green', 'DarkGreen'],
	/*8*/	['DarkRed','Red', 'OrangeRed', 'DarkOrange','Orange', 'Yellow', 'Gold', 'GreenYellow','Green', 'DarkGreen'],
	/*9*/	['DarkRed','Red', 'OrangeRed', 'DarkOrange','Orange', 'Yellow', 'Gold', 'GreenYellow','Green', 'DarkGreen']
	];
	private $radiusScheme = [
	/*0*/	[2000],
	/*1*/	[500, 	1000],
	/*2*/	[1200,	1400, 	1600],
	/*3*/	[1000, 	1200,	1400, 	1600],
	/*4*/	[800, 	1000, 	1200,	1400,	1600],
	/*5*/	[600,	800,	1000,	1200,	1400,	1600],
	/*6*/	[400,	600,	800,	1000,	1200,	1400,	1600],
	/*7*/	[200,	400,	600,	800,	1000,	1200,	1400,	1600],
	/*8*/	[150,	200,	400,	600,	800,	1000,	1200,	1400,	1600],
	/*9*/	[100,	150,	200,	400,	600,	800,	1000,	1200,	1400,	1600]
	];


	private $catValues;  // all possible category values
	private $allOptions; // all possible options
	
	// the count arrays
	private $optionCatCount; // key [option][cat]
	private $catOptionCount; // key [cat][option]
	private $catTotalCount;	 // key [cat]
	private $optionTotalCount; // key [option]

	private $munCount;  // municipality counter[2] with Lat[0]/Lon[1]; key: gemeentenaam
	private $locCount;  // location counter[2]with Lat[0]/Lon[1]; key: plaatsnaam
	private $zipCount;  // full zip counter[2] with Lat[0]/Lon[1]; key: full zipcode
	private $zip4Count;  // zip 4 counter[2] with Lat[0]/Lon[1]; key: 4 digit zipcode
	
	// count vars
	private $allAnswerWithCatCount = 0; // all answers met gedeelde profielwaarde
	private $allGivenAnswerCount = 0; // all answers given includes 'niet gedeeld'

	private $extraInfo;
	
	private $allGivenAnswerQResult;

	public function getAllData() {
		if ($this->viewType == 'Geo' ) {
			$data = "\n" . "Excite.qu.geoUnshared = '" . $this->getGeoUnshared() . "';";
			$data .= "\n" . "Excite.qu.extraInfo = '" . $this->getExtraInfo() . "';";
			$data .= "\n" . "Excite.qu.geoProvData = " . $this->getGeoProvData();
			$data .= "\n" . "Excite.qu.mapDataArr['mun'] = " . $this->getMunMapData();
			$data .= "\n" . "Excite.qu.mapDataArr['loc'] = " . $this->getLocMapData();
			$data .= "\n" . "Excite.qu.mapDataArr['zip'] = " . $this->getZipMapData();
			$data .= "\n" . "Excite.qu.mapDataArr['zip4'] = " . $this->getZip4MapData() . ";\n";
		} else {
			$data = "\n" . 'Excite.qu.pieData = ' . $this->getPieData();
			$data .= "\n" . 'Excite.qu.answerBarData = ' . $this->getAnswerBarData();
			$data .= "\n" . 'Excite.qu.categoryBarData = ' . $this->getCategoryBarData();
			$data .= "\n" . 'Excite.qu.allGivenAnswerCount = ' . $this->getAllGivenAnswerCount() . ";\n";
		}
		return $data;
	}
	
	// public get functions for the build of the blade templates
	public function getPieData() {
		return "\n[" . $this->pieData . "\n];";
	}

	public function getAnswerBarData() {
		return "\n[" . $this->answerBarData . "\n];";
	}
	
	public function getCategoryBarData() {
		return "\n[" . $this->categoryBarData . "\n];";
	}
	
	public function getGeoProvData() {
		return "\n[" . $this->geoProvData . "\n]";
	}
	
	public function getMunMapData() {
		return "\n[" . $this->munMapData . "\n]";
	}
	public function getLocMapData() {
		return "\n[" . $this->locMapData . "\n]";
	}
	
	public function getZipMapData() {
		return "\n[" . $this->zipMapData . "\n]";
	}
	
	public function getZip4MapData() {
		return "\n[" . $this->zip4MapData . "\n]";
	}
	
	public function getGeoUnshared() {
		return $this->geoUnshared;
	}	
	public function getAllGivenAnswerCount () {
		return $this->allGivenAnswerCount;
	}	
	public function getExtraInfo () {
		return $this->extraInfo;
	}

	private $geoArr;
	public function getMapDataForJson () {
		return $this->geoArr;
	}
	
	private $userProfileData = '{}';
	public function getUserProfileData() {
		return $this->userProfileData;
	}

	function __construct($questionId, $statsType = null, $viewType = null) {
		ini_set('memory_limit','256M');
		$this->viewType = $viewType;
		// statsType is de category/profielnaam
		// map some special external Button names on the right internal db sleutel name
		switch ($statsType) {
			case null:
				$statsType = 'Geluk'; // default
				break;
			case 'Provincie':
				$statsType = 'Postcode';
				break;
			case 'Leeftijd':
				$statsType = 'Geboortejaar';
				break;
			case 'Transport':
				$statsType = 'Transportmiddelen';
				break;
			case 'Titel':
				$statsType = 'Titels';
				break;
		}
		if ($viewType == 'Mini') {
			//Postcode was for doGeo; you can take any Profile key
			//$this->mainData($questionId, 'Postcode');
			$this->mainData($questionId, 'Geslacht');
			$this->doPie($this->optionCatCount, true);
			// for province map; not needed enymore
			//$this->doGeo($this->catTotalCount, true);
			return;
		}
		if ( $viewType == 'Geo' ) { // voor de Kaart alleen dit nodig
			$this->mainGeoData($questionId);
			//dd($this->catTotalCount);
			$this->doGeo($this->catTotalCount);
			$this->extraInfo = "Gemeenten: " . count($this->munCount). "<br />";
			$this->extraInfo .= "Plaatsen: " . count($this->locCount). "<br />";
			$this->extraInfo .= "Postcode: " . count($this->zipCount). "<br />";
			$this->extraInfo .= "Postcode4: " . count($this->zip4Count). "<br />";
			return;
		}
		$up = new UserProfile();
		$this->userProfileData = $up->getUserProfileData();
		$this->mainData($questionId, $statsType);
		$this->doPie($this->optionCatCount);
		$this->doAnswerBar($this->optionCatCount);
		$this->doCategoryBar($this->catOptionCount);
	}

	/** private functions **/
	
	private function mainGeoData($questionId) {
		$statsType = 'Postcode';
		// get data with modified Montana query
		$result = $this->getIndex($questionId,$statsType);

		// haal provicienamen
		$this->catValues = $this->catValues($statsType);
		$this->allOptions = $this->allOptions($questionId);
		
		$catValues = &$this->catValues;
		$allOptions = &$this->allOptions;
		// initialize count arrays
		foreach ( $catValues as $cat ) {
				$this->catTotalCount[$cat->id] = 0;
		}
		foreach ( $allOptions as $opt ) {
			$this->optionTotalCount[$opt->optionsId] = 0;
		}

		// collect the counts in result and put them in the count arrays
		//dd($result);
		foreach ( $result as $r ) {
			$cat = $r->metahl;
			$opt = $r->option_id;
			// replace possible niet gedeeld default values
			// -1, is answer by email
			if ($cat === null || trim($cat) === '' || $cat == 0 || $cat == '-1,') $cat = '0,';
	
			//if ( !(strpos($cat,',') === false)  ) {
				$multiCat = explode(',', $cat);
				$cat = $multiCat[0]; // code provincienaam

				if ( $cat != 0 && $cat != -1 ) { // voor de kaart en alleen gedeelde!
					// gemeentenaam in $multicat[1] dan lat en lon etc
					if (! $this->geoInput($multiCat) ) {
						//var_dump($cat + ', ' + $multiCat);
						die("Probleem met Postcode string: " . $r->metahl);
					}					
				}
			//} else { var_dump($cat); die("Probleem met Postcode string; geen komma"); };
			$incr = 1;
			
			$this->catTotalCount[$cat] += $incr;
			$this->optionTotalCount[$opt] += $incr;
			$this->allAnswerWithCatCount += $incr;
		}
		// niet gedeeld moet ook bij Postcode!!!
		$result = $this->allGivenAnswerQResult;
		//dd($this->catTotalCount);
		//dd($optionTotalCount);
		$all = 0;
		// TODO nog eens goed bekijken
		// niet gedeeld tgv geen datapairs zijn al meegeteld..... maar dit gaat verder goed... allen ophogen
		foreach ( $result as $r ) {
			$all += $r->count;
			$notShared = $r->count - $this->optionTotalCount[$r->option_id];
			$this->catTotalCount[0] += $notShared;			
		}
		$this->allGivenAnswerCount = $all;
		
		// sorteer zipCounts van hoog naar laag op count dan komen de grotere cirkels onder kleinere te liggen op de kaart
		// kan je (vrijwel) overal op blijven klikken TODO andere map data arrays ook? TODO dit moet generiek
		if ( isset($this->zip4Count))
			uasort($this->zip4Count, function($a,$b) { // anonymous function kan ook bij php!
				if ($a[2] == $b[2]) {
					return 0;
				}
				return ($a[2] < $b[2]) ? 1 : -1;
			});
		if( isset($this->zipCount))
			uasort($this->zipCount, function($a,$b) {
				if ($a[2] == $b[2]) {
					return 0;
				}
				return ($a[2] < $b[2]) ? 1 : -1;
			});

	}

	private function mainData($questionId, $statsType) {
		// get data with Montana query
		$result = $this->getIndex($questionId,$statsType);
//echo "getIndex main query " . count($result) . "<br>";
		$this->catValues = $this->catValues($statsType);
//echo "catValues " . count($this->catValues) . "<br>";

		$this->allOptions = $this->allOptions($questionId);
//echo "allOptions " . count($this->allOptions) . "<br>";
		//if ( $statsType == 'Postcode' ) dd($this->allOptions);
		$allOptions = &$this->allOptions;
		$catValues = &$this->catValues;
		// initialize main count arrays
		foreach ( $allOptions as $opt ) {
			$this->optionTotalCount[$opt->optionsId] = 0;
			foreach ( $catValues as $cat ) {
				$this->optionCatCount[$opt->optionsId][$cat->id] = 0;
				$this->catOptionCount[$cat->id][$opt->optionsId] = 0;
			}
		}
		// initialize catTotalCount
		foreach ( $catValues as $cat ) {
			$this->catTotalCount[$cat->id] = 0;
		}

		// collect the counts in result and put them in the count arrays
		foreach ( $result as $r ) {
			$opt = $r->option_id;
			$cat = $r->waarde_id;

			if($statsType == 'Postcode' || $statsType == 'Geboortejaar')
				$cat = $r->metahl; // Postcode string resp. index in waardetabel voor 'Geboortejaar'
			if($statsType == 'Geluk' || $statsType == 'Gezondheid') {
				$cat = $r->meta; // waarde staat in ->meta  TODO Leo nog niet op prod?
			}
			if ($cat == null || trim($cat) == '') $cat = 0;
			if ( $cat == 0 && $statsType == 'Postcode' )
				$cat = '0,';

			//if ( !(strpos($cat,',') === false)  ) {
				// alleen het geval bij Postcode string
				if ($statsType == 'Postcode')
					$cat = explode(',', $cat)[0]; // gebruik alleen provinciecode hier; de rest is voor Geo stats

			//}
			//echo $opt . " " . $cat . ' ' . $r->incr . '<br />';
			$this->optionCatCount[$opt][$cat] += $r->incr;
			$this->catOptionCount[$cat][$opt] += $r->incr;

			$this->catTotalCount[$cat] += $r->incr;
			$this->optionTotalCount[$opt] += $r->incr;
			$this->allAnswerWithCatCount += $r->incr;
		}

		// niet gedeeld afhandeling
		$result = $this->allGivenAnswerQResult;
		$all = 0;
		// TODO nog eens goed bekijken
		// niet gedeeld tgv geen datapairs zijn al meegeteld..... maar dit gaat verder goed... allen ophogen
		foreach ( $result as $r ) {
			$all += $r->count;
			$notShared = $r->count - $this->optionTotalCount[$r->option_id];
			$this->optionCatCount[$r->option_id][0] += $notShared;
			$this->catOptionCount[0][$r->option_id] += $notShared;
			$this->catTotalCount[0] += $notShared;			
		}
		$this->allGivenAnswerCount = $all;
	}
	// maak counts aan per option
	private function allGivenAnswerQ ($questionId) {
		//$count = DB::table('answers')->where('question_id', '=' ,$questionId)->count();
		$result = DB::table('answers')
				->selectRaw('option_id, COUNT(option_id) AS count')
				->where('answers.question_id', '=' ,$questionId)
				->groupBy('option_id')
				->get();
		//dd($result);
		$this->allGivenAnswerQResult = $result;
		return $result;
	}
	
	
	private function geoInput ($in) { //TODO kan beter
		for ( $i = 0; $i <= 9; $i++ ) {
			if ( ! isset($in[$i] ) ) { // accepteer alleen fully specified
				return false;
			}
		}
		$provCode = $in[0];
		$munName = $in[1];	$munLat = $in[2];	$munLon = $in[3];
		$locName = $in[4];	$locLat = $in[5];	$locLon = $in[6];
		$zipCode = $in[7];	$zipLat = $in[8];	$zipLon = $in[9];
		$cnt = 1;
		
		if  (! isset($this->munCount[$munName] ))
			$this->munCount[$munName] = [$munLat,$munLon,$cnt];
		else
			$this->munCount[$munName][2] += $cnt;

		$key = $locName;
		if ( $locName != $munName )
			$key = $locName . ';' . $munName;
		if  (! isset($this->locCount[$key] ) )
			$this->locCount[$key] = [$locLat,$locLon,$cnt];
		else
			$this->locCount[$key][2] += $cnt;

		$key = $zipCode. ',' . $locName;
		if  (! isset($this->zipCount[$key] ) )
			$this->zipCount[$key] = [$zipLat,$zipLon,$cnt];
		else
			$this->zipCount[$key][2] += $cnt;
			
		$zipCode = substr($zipCode,0,4);
		$key = $zipCode. ',' . $locName;
		if  (! isset($this->zip4Count[$key] ) )
			$this->zip4Count[$key] = [$zipLat,$zipLon,$cnt];
		else
			$this->zip4Count[$key][2] += $cnt;
		return true;
	}
	
	private function catValues($statsType) {
		if ( $statsType == 'Geluk' || $statsType == 'Gezondheid' ) { // maak eigen 10 pts schaal
			$result = [];
			for ( $i = 10; $i >0; $i-- ) {
				$result[] = (Object) [ 'id'=>$i, 'text' => "$i" ];
			}
			$result[] = (Object) [ 'id' => 0, 'text' => 'Niet gedeeld'];
			$result[] = (Object) [ 'id' => -1, 'text' => 'Per email'];
			return $result;
		}
		$result = DB::table('sleutels')
        ->select('waardes.id','waardes.text', 'sleutels.meta', 'sleutels.id AS sleutelId')
		->leftJoin('waardes', "waardes.sleutel_id" , '=' , 'sleutels.id')
    	->where('sleutels.text', '=', $statsType)
		->orderBy('waardes.sort', 'asc')
    	->get();

		if ( count($result) == 0 ) {
			var_dump($result);
			die ("Geen statistiek: Probleem met waarden van categorie '$statsType'");
		}
		if( (count($result) == 1 && $result[0]->text == null)) {
				var_dump($result);
				die("Geen statistiek voor '$statsType'");
		}

		// voeg 'Niet gedeeld' catValue toe
		array_push($result, (Object) [ 'id' => 0, 'text' => 'Niet gedeeld']);
		array_push($result, (Object) [ 'id' => -1, 'text' => 'Per email']);
		return $result;
	}
	private function allOptions($questionId) {
		$q = 'SELECT options.id AS optionsId, options.text FROM options ';
		$q .= 'WHERE options.question_id = ? ';
		$q .= 'ORDER BY id ASC' ; // de eerst toegevoegde eerst; is goed als antwoorden in 'juiste' volgorde worden geplaatst; er is geen order column
		$allOpts = DB::select( $q , [$questionId]);
//echo "allOptions Q1 allOpts " . count($allOpts) . "<br>";
/*		
		$q = 'select DISTINCT option_id, answers.id AS answersId FROM answers ';
		$q .= 'WHERE answers.question_id = ? ';
*/
		$q = 'select option_id, answers.id AS answersId FROM answers ';
		$q .= 'WHERE answers.question_id = ? ';
		$q .= 'GROUP BY option_id';
		$usedOpts = DB::select( $q , [$questionId]);
//echo "allOptions Q2 usedOpts " . count($usedOpts) . "<br>";	
		$result = [];
		foreach ( $allOpts as $all ) {
			// remove illegal enters (horen niet in de db, maar kwam bij tests per ongeluk wel voor ...)
			$all->text = str_replace(["\r\n", "\n", "\r"], ' ',$all->text);
			// fix quotes!
			$all->text = str_replace("'", "\'", $all->text);
			$all->text = str_replace('"', '\"', $all->text);
			$match = false;
			foreach ( $usedOpts as $used ) {
				if ( $all->optionsId == $used->option_id ) {
					$all->answersId = $used->answersId;
					$result[] = $all;
					$match = true;
					break;
				}
			}
			if ( ! $match ) {
				$all->answersId = null;
			}
		}
		foreach ( $allOpts as $all ) {
			if ( $all->answersId == null ) {
				$all->text = '-' . $all->text;
				$result[] = $all;
			}
		}
		//dd($result);
		return $result;
	}	
	// get all possible options; the ones that are actually used in an answer come first
	// TODO dit kan zonder een join met answers; is waarschijnlijk goedkoper bij grote populaties
	// DONE zie boven
	private function allOptions2($questionId) {
		$result = DB::table('options')
		//->selectRaw("options.id AS optionsId, options.text, answers.id AS answersId")
		->select("options.id AS optionsId", "options.text", "answers.id AS answersId")
		->leftJoin('answers', "answers.option_id" , '=' , 'options.id')
    	->where('options.question_id', '=', $questionId)
		->groupBy('options.id')
		->orderByRaw('ISNULL(answers.id)')
    	->get();
		// clean up option text and put - for text when 0 answers use this option
		foreach ($result as $r ) {
			// remove illegal enters (horen niet in de db, maar kwam bij tests per ongeluk wel voor ...)
			$r->text = str_replace(["\r\n", "\n", "\r"], ' ',$r->text);
			// fix quotes!
			$r->text = str_replace("'", "\'", $r->text);
			$r->text = str_replace('"', '\"', $r->text);
			if ( $r->answersId == null ) {
				$r->text = '-' . $r->text;
			}
		}
		//var_dump($result);
		return $result;
	}
	
	private function doPie($optionCatCount, $mini = false)
	{
		$result = [];
		$xNames = ['Antwoord' , 'Aantal'];
		$this->addRow($xNames, $this->pieData, self::PIE, true);
		foreach ( $this->allOptions as $opt ) {
			$total = 0;
			foreach($optionCatCount[$opt->optionsId] as $cnt)
				$total += $cnt;
			$result[] = [ 'cnt' => $total, 'text' => $opt->text ];
			//$v = [ $opt->text, $total ];
			//$this->addRow($v, $this->pieData, self::PIE);
		}
		if ( $mini ) {
			uasort($result, function($a,$b) { // anonymous function kan ook bij php!
					if ($a['cnt'] == $b['cnt']) {
						return 0;
					}
					return ($a['cnt'] < $b['cnt']) ? 1 : -1;
			});
		}
		foreach ( $result as $r ) {
			$v = [ $r['text'], $r['cnt'] ];
			$this->addRow($v, $this->pieData, self::PIE);
		}
	}
	
	private function doAnswerBar($optionCatCount) {
		$xNames[0] = 'Antwoord';
		$i = 1;
		foreach ($this->catValues as $cat){
			$xNames[$i++] = $cat->text;
		}
		$this->addRow($xNames, $this->answerBarData, self::ANSB,true);
		foreach ( $this->allOptions as $opt) {
			$v[0] = $opt->text;
			$vindex =1;
			foreach ( $optionCatCount[$opt->optionsId] as $cnt ) {
				$v[$vindex++] = $cnt;
			}
			$this->addRow($v, $this->answerBarData, self::ANSB);
		}		
	}
	
	private function doCategoryBar($catOptionCount) {
		$xNames[0] = 'Categorie';
		$i = 1;
		foreach ($this->allOptions as $opt){
			$xNames[$i++] = $opt->text;
		}
		$this->addRow($xNames, $this->categoryBarData, self::CATB, true);
		// dd($catOptionCount); // dit resultaat is gek
		foreach ( $this->catValues as $cat) {
			$v[0] = $cat->text;
			$vindex =1;
			foreach ( $catOptionCount[$cat->id] as $cnt ) {
				$v[$vindex++] = $cnt;
			}			
			$this->addRow($v, $this->categoryBarData, self::CATB);
		}
	}
	
	private function doGeo($catTotalCount, $mini = false) {
		// GeoChart Provinces
		$xNames = ['Provincie' , 'Aantal Antwoorden'];
		$this->addRow($xNames, $this->geoProvData, self::GEOPROV, true);
		//var_dump($catTotalCount);
		$percentOnly = false;
		if ( $this->allGivenAnswerCount < self::ABS_LIMIT) $percentOnly = true;

		foreach ( $this->catValues as $cat) {
			if ( $this->allGivenAnswerCount != 0 )
				$percent = round ( ($catTotalCount[$cat->id] / $this->allGivenAnswerCount) *100 , 1);
			else $percent = 0;
			if( $cat->id == 0 ) {
				if ($percentOnly) {
					$this->geoUnshared = $percent . "%";
				}
				else{
					$this->geoUnshared = $catTotalCount[$cat->id] . " - " . $percent . "%";
				}
				continue;
			}
			$v[0] = $cat->text;
			if ($percentOnly)
				$v[1] = $percent;
			else
				$v[1] = $catTotalCount[$cat->id];
			$this->addRow($v, $this->geoProvData, self::GEOPROV);
			
		}
		if ( $mini ) return; // no MAP needed
		
		// go on for the MAP

		$this->doGeoArr($this->catTotalCount);
		return;
	}

	private function doGeoArr($catTotalCount, $strAlso = true) {
		// geoArr can be used for seperate ajax json download of map data
		// as an alternative for object literal text insert in the script text
		// works fine but not in use (yet)
		// optionally the data strings are built up too; default true
		$geoArr = &$this->geoArr;
		$percentOnly = false;
		if ( $this->allGivenAnswerCount < self::ABS_LIMIT) $percentOnly = true;
		$xNames = ['Lat' , 'Lon' , 'Label', 'Radius', 'Color'];
		$gI = ['mun', 'loc', 'zip', 'zip4'];
		//$gI = ['mun'];
		$counts = [$this->munCount, $this->locCount, $this->zipCount , $this->zip4Count];
		$targets = [ &$this->munMapData, &$this->locMapData, &$this->zipMapData, &$this->zip4MapData ];
		// give all targets at least a heading row
		for ( $i = 0; $i < count($gI); $i++ ){
			$geoArr["$gI[$i]"][] = $xNames;
			if($strAlso)
				$this->addRow($xNames, $targets[$i], self::MAP, true);
		}
		if ( ! isset($this->munCount) ) { // there's no data
			return;
		}
		$len = count($gI);
		for ( $i = 0; $i < $len; $i++ ) {
			$minCnt = 0;
			$maxCnt = 0;
			$countDensity = [];
			// TODO dit kan mis gaan als er niks is..........; uitzoeken
			foreach ( $counts[$i] as $key => $cnt) {
				$countDensity[$cnt[2]] = 1;
				if ( $maxCnt == 0 ) {
					$minCnt = $cnt[2];
					$maxCnt = $cnt[2];
					continue;
				}
				if ( $cnt[2] > $maxCnt ) $maxCnt = $cnt[2];
				else
					if ($cnt[2] < $minCnt ) $minCnt = $cnt[2];
			}
			$scaleSize = count($countDensity);
			if ( $scaleSize > 10 ) $scaleSize = 10;
			$schemeIndex = $scaleSize - 1;
			$this->schemeIndex = $schemeIndex;
			foreach ( $counts[$i] as $key => $cnt) {
				$o[0] = floatval($cnt[0]); //Lat
				$o[1] = floatval($cnt[1]); //Lon
				$p[0] = $cnt[0]; //Lat
				$p[1] = $cnt[1]; //Lon
				if ( $this->allGivenAnswerCount != 0 ) {
					$percent = round ( ($cnt[2] / $this->allGivenAnswerCount) *100 , 1);
					if ($percent == 0 ) // TODO bad coding
						$percent = round ( ($cnt[2] / $this->allGivenAnswerCount) *100 , 2);
				} else $percent = 0;
				$debug = '';
				$show = $debug . $percent . "%";
				if ( ! $percentOnly )
					$show = $cnt[2] . ' - ' . $show;
				$o[2] = $key . ": " . $show  ; // popup text
				$p[2] = '"' . $o[2] . '"' ;
				$t = (($cnt[2]- $minCnt)/ ($maxCnt -$minCnt + 1))*$scaleSize;
				$t = floor($t);
				if ( $i == 2 ) // complete postcode; keep dots small
					$o[3] = $p[3] = 10; // circle radius = 10 meter = 2 huizen
				else {
					$o[3] = $p[3] = $this->radiusScheme[$schemeIndex][$t]; // circle size
				}
				$o[4] = $this->colorScheme[$schemeIndex][$t]; // color of circle
				$p[4] = "'" . $o[4] . "'";

				$geoArr["$gI[$i]"][] = $o;
				if($strAlso)
					$this->addRow($p, $targets[$i], self::MAP);
			}
		}
		//dd($geoArr['mun']);
	}
	
	// write one row to one of the Javascript data strings
	private function addRow($v, &$target, $targetId, $firstRow = false) {
		$fixTooltip = false;
		switch ($targetId) {
			case self::PIE:
					// never fix tooltip here; is fixed with chart option in blade
			case self::MAP:
					// no fix needed
				break;
			case self::GEOPROV:
			case self::CATB:
				$fixTooltip = true; // fix always needed
				break;
			case self::ANSB:
				// fix for small populations only
				if ( $this->allGivenAnswerCount < self::ABS_LIMIT) {
					$fixTooltip = true;
				}
		}
		if ( ! $firstRow )
			$target .= ",";
		$target .= "\n[";
		$cnt = count($v);
		for ( $i= 0; $i< $cnt; $i++ ) {
			if ( $firstRow ) {
				// quote all in first row 
				$target .= "'$v[$i]'";
				if ( $fixTooltip && $i > 0)
					$this->addTooltipElem($target, $targetId, $firstRow);
			}
			else { // normal row
				if ( $i == 0 ) { // is first elem
					if ( $targetId == self::MAP )
						$target .= "$v[$i]";
					else
						$target .=  "'$v[$i]'";
				}
				else { // is not first elem
					if ($targetId != self::MAP )
						$v[$i] = round($v[$i],2);
					$target .= "$v[$i]";
					if ( $fixTooltip) {
						$perc = '';
						if ( $targetId == self::CATB ) {
							if ($this->allGivenAnswerCount != 0 )
								$p = round(($v[$i]/$this->allGivenAnswerCount)*100, 1);
							else $p = 0;
							$abs = '';
							if ($this->allGivenAnswerCount >= self::ABS_LIMIT )
								$abs = $v[$i] . " - ";
							$perc = "\\n<br /> $abs $p% van alle antwoorden";
						}
						if ( $targetId == self::GEOPROV ) {
							if ($this->allGivenAnswerCount >= self::ABS_LIMIT ) {
								$abs = $v[$i] . " - ";
								$p = round(($v[$i]/$this->allGivenAnswerCount)*100, 1);
								$abs = $v[$i] . " - ";
								$perc = "\\n $abs $p%";
							} else { // percentage already available
								$p = $v[$i];
								$perc = "\\n $p%";					
							}
							
						}
						// TODO for ANSB gaat het goed, maar waarom?
						$this->addTooltipElem($target, $targetId, $firstRow, $i - 1, $v[0], $perc);
					}
				}
			}
			if ( $i != $cnt - 1)
				$target .= ',';			
		}
		$target .= "]";
	}
	
	// write extra element to data row string for Google Chart tooltip fix; kind of hacked code
	private function addTooltipElem(&$target, $targetId, $firstRow, $xIndex = 1, $yText = 'Y-tekst', $perc = '') {
		if ( $firstRow ) {
			// after each data item label extra elem with tooltip options
			$str = "{role: 'tooltip', p: { html: true}}";
			$target .= ", " . $str;
			return;
		}
		// add tooltip stuff after data elem
		$str = '';
		if ($targetId == self::GEOPROV)
			$str .= $perc;
		else
			$str = '<div class="tooltipLine"><span class="tooltipFirst">' . "$yText</span><br />";
		if ( $targetId == self::ANSB )
			$str .= $this->catValues[$xIndex]->text . '</div>';
		if ( $targetId == self::CATB ) {
			$str .= $this->allOptions[$xIndex]->text . $perc . '</div>';
		}
		$target .= ", '" . $str . "'";
	}
	
	public function getIndex($questionId, $statsType ) // strongly modified Montana query
    {
		$groupBy = false;
		$result = $this->allGivenAnswerQ($questionId);
		$all = 0;
		foreach ( $result as $r ) {
			$all += $r->count;	
			if ( $all > 500) {
				$groupBy = true;
				break;
			}
		}

		$q = 'SELECT ';
		if ($groupBy)
			$q .= 'COUNT(answers.user_id) AS optionCount, ';
		//$q .= 'answers.dataset_id AS datasetId, answers.id AS answerId, answers.question_id, answers.user_id, answers.option_id, sleutels.text AS sleutelText, sleutels.type, datapairs.meta, datapairs.metahl, datapairs_waardes.waarde_id, sleutels.id, datapairs.id FROM answers ';
		$q .= 'answers.user_id, answers.option_id, datapairs.meta, datapairs.metahl, datapairs_waardes.waarde_id, sleutels.text AS sleutelText FROM answers ';
		$q .= 'left join datapairs on answers.dataset_id = datapairs.dataset_id ';
		$q .= 'left join sleutels on datapairs.sleutel_id = sleutels.id ';
		$q .= 'left join datapairs_waardes on datapairs.id = datapairs_waardes.datapair_id ';
		$q .= 'where answers.question_id = ? AND (sleutels.text = ? OR sleutels.text IS NULL) ';

		if ($groupBy) // geen sleutels met multi verdeling; veeg ze bij elkaar
					  // option_id is needed for email user 565
			$q .= "group by answers.user_id, answers.option_id ";
		$q .= 'order by answers.created_at DESC';
		//echo "query " . $q . "<br />";
		$result = DB::select( $q , [$questionId, $statsType]);
		/*
		if( $statsType == 'Geslacht' )
			dd($result); */
		// mod: add increments to the result; default 1; when needed divide 1 over multiple sleutel,value pairs
		
		function fixIncr($result,$i, $sleutelCount, $lastUserId, $groupBy) {
			// note that $result is an object it points to the array; it acts as a reference 
			if ( $groupBy ) { // no fix needed for normal users
				if ( $lastUserId == 565 ) {
					for ( $j = $i - $sleutelCount; $j < $i; $j++ )
						$result[$j]->incr = $result[$j]->optionCount;
				}
				return;
			}
			if ( $lastUserId == 565 ) return; // do not fix for answers per email
			if ( $sleutelCount > 1 ) {
				$incr = 1/$sleutelCount;
				for ( $j = $i - $sleutelCount; $j < $i; $j++ )
					$result[$j]->incr = $incr;
			}
		}
		$i = 0;
		$lastDatasetId = 0;
		$lastUserId = 0;
		$sleutelCount = 0;
		foreach ($result as $r) {
			// normaal telt een antwoord voor 1
			$r->incr = 1;
			if ( $r->sleutelText == null) { // 'niet gedeeld' tgv datasetId = null wordt hier meteen meegeteld
				$r->sleutelText = $statsType;
				if ( $r->user_id != 565 ) {
					if ( $statsType == 'Postcode' )
						$r->metahl = '0,';
					else
						$r->metahl = 0;
					$r->meta = 0;
					$r->waarde_id = 0;
				} else { // answer by email
					if ( $statsType == 'Postcode' )
						$r->metahl = '-1,';
					else
						$r->metahl = -1;
					$r->meta = -1;
					$r->waarde_id = -1;				
				}
			}

			if ( $lastUserId != $r->user_id ) {
				fixIncr($result,$i, $sleutelCount, $lastUserId, $groupBy);
				$lastUserId = $r->user_id;
				$sleutelCount = 0;
			}
			++$i;
			++$sleutelCount;
		}
		// fix $r->incr of the last series; if any
		fixIncr($result,$i, $sleutelCount, $lastUserId, $groupBy);

		//echo "in index main query " . count($result) . "<br>";
		/*
		if( $statsType == 'Geslacht' )
			dd($result); */
		//$this->dbResult = $result;
		return $result;
	}
	public function newGetIndex($questionId, $statsType )
    {
		echo "This is handled by New stats Query<br />";
		$q  = 'SELECT CASE ';
		$q .= 'WHEN sleutels.text = "Geluk" OR sleutels.text = "Gezondheid" THEN datapairs.meta ';
		$q .= 'WHEN sleutels.text = "Geboortejaar" THEN datapairs.metahl ';
		if ( $this->viewType != 'Geo')
			$q .= 'WHEN sleutels.text = "Postcode" THEN SUBSTRING_INDEX(datapairs.metahl,",",1) '; // for Provincies
		else
			$q .= 'WHEN sleutels.text = "Postcode" THEN datapairs.metahl '; // for the Map
		$q .= 'ELSE datapairs_waardes.waarde_id ';
		$q .= 'END AS waardeValue, ';
		$q .= 'count(answers.option_id) AS incr, answers.dataset_id AS datasetId, answers.id AS answerId, answers.question_id, answers.user_id, answers.option_id, sleutels.text AS sleutelText, sleutels.type, datapairs.meta, datapairs.metahl, datapairs_waardes.waarde_id, sleutels.id, datapairs.id FROM answers ';
		$q .= 'left join sleutels on sleutels.text = ? ';
		$q .= 'left join datapairs on answers.dataset_id = datapairs.dataset_id AND datapairs.sleutel_id = sleutels.id ';
		//$q .= 'left join sleutels on datapairs.sleutel_id = sleutels.id ';
		$q .= 'left join datapairs_waardes on datapairs.id = datapairs_waardes.datapair_id ';
		$q .= 'where answers.question_id = ? AND (sleutels.text = ? OR datapairs.id IS NULL) ';

		$q .= "group by waardeValue,answers.option_id ";
		$q .= 'order by answers.option_id ';
		//$q .= 'order by answers.created_at DESC';
		//echo "query " . $q . "<br />";
		$result = DB::select( $q , [$statsType, $questionId, $statsType]);

		if( $statsType == 'geslacht' )
			dd($result);
		return $result;
	}
}