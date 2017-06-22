<?php

namespace Excite\Models;

//use Auth;
use DB;
use Config;
//use Input;

class UserDbModel {

	public static function checkUser($userEmail){
		
        $checkUser = DB::table('users')
            //->select('email','password_digest')
            ->where('email', $userEmail)
        	->first();
        	
        	return $checkUser;
		
	}

// Deze methode komt uit de Montana Api: api/app/Models/Users.php
// Kleine modificates door Han om binnen Excite te kunnen benutten.
// Moet worden gebruikt om een user een nieuwe copy van het Profile te te geven _na_
// het noteren van een antwoord in answers; het answers.dataset_id moet het _huidige_ users.dataset_id worden
// Aldus wordt een Profile dat niet meer veranderd aan het antwoord gekoppeld.
// Dit is niet handig, maar het is nu eenmaal zo geimplementeerd door YipYip->Montana

    public static function cloneProfile($userId = 0)
    {
		// fixes by Han; rename fork() -> cloneProfile()
		// parameter added for the call
		if ( $userId <= 0 ) return false;
		// get datasetId
		$q = 'SELECT dataset_id FROM users WHERE id = ? LIMIT 1';
		$res = DB::select($q, [$userId]);
		if ( count($res) == 0 ) return false; // userId unknown		
		$userDatasetId = $res[0]->dataset_id;
		// $date = Carbon::now(); not available for Excite
		$date = date('Y-m-d H:i:s');
		// Montana uses Config for a globally available set of variables
		// these 2 are used in the unchanged Montana code below
		Config::set('userId',$userId);
		Config::set('userDatasetId', $userDatasetId);
		// end fixes by Han
	    
	    // create new dataset
	    $newDataSetId = DB::table('datasets')->insertGetId([
			'created_at' => $date, 
			'updated_at' => $date
		]);
		
		// copy old categories
		$categories = DB::table('categories_datasets')
	    					->where('dataset_id', Config::get('userDatasetId'))
	    					->get();
	    $categoriesNew = array();
		foreach($categories as &$category)
		{
			$category->dataset_id = $newDataSetId;
			$categoriesNew[] = (array)$category;
		}
		DB::table('categories_datasets')->insert($categoriesNew);
		
		// copy old datapairs
		$datapairs = DB::table('datapairs')
	    				->where('dataset_id', '=', Config::get('userDatasetId'))
	    				->orderBy('id', 'asc')
	    				->get();
		$datapairIds = array();
		$datapairsNew = array();
	    foreach($datapairs as &$datapair)
		{
			$datapairIds[] = $datapair->id;
			unset($datapair->id);
			$datapair->dataset_id = $newDataSetId;
			$datapair->created_at = $date;
			$datapair->updated_at = $date;
			$datapairsNew[] = (array)$datapair;			
		}
		DB::table('datapairs')->insert($datapairsNew);
		
		// copy old datapair_waardes
		// get new id's of new datapairs
		$newDatapairIds = array();
		$newDatapairs = DB::table('datapairs')
	    				->where('dataset_id', '=', $newDataSetId)
	    				->orderBy('id', 'asc')
	    				->get();
	    foreach($newDatapairs as &$newDatapair)
		{
			$newDatapairIds[] = $newDatapair->id;
		}

	    // then get old datapair_waardes
	    $datapairsWaardes = DB::table('datapairs_waardes')
	    				->whereIn('datapair_id', $datapairIds)
	    				->orderBy('datapair_id', 'asc')
	    				->get();
	    $datapairsWaardesNew = array();
	    foreach($datapairsWaardes as &$datapairWaarde)
		{
			// search old datapair_id and then match it to the new datapair_id
			$key = array_search($datapairWaarde->datapair_id, $datapairIds);
			$datapairWaarde->datapair_id = $newDatapairIds[$key];
			$datapairsWaardesNew[] = (array)$datapairWaarde;
		}
		DB::table('datapairs_waardes')->insert($datapairsWaardesNew);
		
		// add new dataset id to current user
		DB::table('users')
            ->where('id', Config::get('userId'))
            ->update(['dataset_id' => $newDataSetId]);
		// return is added by Han
		return true;
		
    }
	
}