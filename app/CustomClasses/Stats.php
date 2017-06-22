<?php

namespace Excite\CustomClasses;

use DB;
use Auth;
use Request;
use Excite\CustomClasses\UserProfile;
// use RunStatsD Classes
// path relative to /public dir
require_once '../php-scripts/includes/RunStatsC.incl';
require_once '../php-scripts/includes/DBx.incl';

class Stats {
	const ABS_LIMIT = 5;
	// count vars
	private $allGivenAnswerCount = 0; // all answers given includes 'niet gedeeld'
	
	private $allGivenAnswerQResult;
	
	private $getDataFromCache = false;

	/*	
	private function startCheck ($questionId,$statsType) {
			
		$qs = 'SELECT id, answerCnt, updateInProgress FROM StatsCache ';
		$qs .= 'WHERE question_id = ? AND statsType = ? FOR UPDATE';
		$qu = 'UPDATE StatsCache ';
		$qu .= 'SET updateInProgress = 1 '  ;
		$qu .= 'WHERE question_id = ? AND statsType = ? AND updateInProgress = 0 ';
		$qi = 'INSERT StatsCache ';
		$qi .= 'SET jsData = ?, qResult = ?, question_id = ?, statsType = ?, answerCnt = -1, updateInProgress = 1 ' ;	
		DB::beginTransaction();
			$result = DB::select ( $qs , [$questionId, $this->statsType]);
			if (count($result) > 0 )
				DB::update($qu,[$questionId,$statsType]);
			else { // create row for RunStats
				DB::insert($qi,['',json_encode([]),$questionId,$statsType]);
			}
		DB::commit(); // row released
		return $result;
	
	} */
	private function checkDataInCache($questionId,$statsType,$viewType) {
		$qs = 'SELECT id, answerCnt, updateInProgress FROM StatsCache ';
		$qs .= 'WHERE question_id = ? AND statsType = ? LIMIT 1';
		$result = DB::select ( $qs , [$questionId, $statsType]);

		if ( count($result) > 0 ) {
			if ($result[0]->answerCnt >= 50 ) // was tijdelijk 500
				return true;
		}
		return false;
	}
	// public get functions for the build of the blade templates
	public function getAllData() {
		$statsType = $this->statsType;
		if ( $this->viewType == 'Geo' ) {
			$statsType = 'Map';
		}
		// always take statscache
		$q = 'SELECT id, answerCnt, jsData, updateInProgress FROM StatsCache ';
		$q .= 'WHERE question_id = ? AND statsType = ? ';
		$result = DB::select ( $q , [$this->questionId, $statsType]);
		
		return $result[0]->jsData;
	}
	public function getPieData() { // for ministats template
		return $this->getAllData();
	}

	public function getAllGivenAnswerCount () {
		return number_format($this->allGivenAnswerCount);
	}

	private $userProfileData = '{}';
	public function getUserProfileData() {
		return $this->userProfileData;
	}

	function __construct($questionId, $statsType = null, $viewType = 'null') {
		//ini_set('memory_limit','256M');
		//$this->memUsageStart = memory_get_usage(true);
		//echo $cwd;
		$this->viewType = $viewType;
		$this->questionId = $questionId;
        
		// statsType is de category/profielnaam
		// map some special external Button names on the right internal db sleutel name
		switch ($statsType) {
			case null:
			case 'null':
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
		if ($viewType == 'Geo') $statsType = 'Map';
		if ($viewType == 'Mini') $statsType = 'Mini';
		$this->statsType = $statsType;
		
		if ($viewType != 'Mini' && $viewType != 'Geo' ) {
				$up = new UserProfile();
				$this->userProfileData = $up->getUserProfileData();
		}
		$this->getDataFromCache = true;
		$this->allGivenAnswerQ($questionId);
		$hostname = gethostname();

		if ( $this->checkDataInCache($questionId,$statsType,$viewType) ) {
			if ("$hostname" != "yixow.local")
				echo 'Data from StatsCache<br />';
			return;
		}
		if ("$hostname" != "yixow.local")
			echo 'Call RunStats and wait... ';
		//$DB = new \DBx(base_path() . '/.env'); // .env is used for db parameter info
		$DB = new \DBx; // root for files is .../local; DBx uses ../.env; is ok
		new \RunStatsC($questionId, $statsType, $viewType, 1, $DB); // this gives a wait
		//$this->dispatch(new RunStats($questionId, $statsType, $viewType)); // run in background
	}

	/** private functions **/
	// maak counts aan per option en bereken alle gegeven antwoorden
	private function allGivenAnswerQ ($questionId) {
		//$count = DB::table('answers')->where('question_id', '=' ,$questionId)->count();
		$result = DB::table('answers')
				->selectRaw('option_id, COUNT(option_id) AS count')
				->where('answers.question_id', '=' ,$questionId)
				->groupBy('option_id')
				->get();
		//dd($result);
		$this->allGivenAnswerQResult = $result;
		$all = 0;
		foreach ( $result as $r ) {
			$all += $r->count;
		}
		$this->allGivenAnswerCount = $all;
		return $result;
	}

}

