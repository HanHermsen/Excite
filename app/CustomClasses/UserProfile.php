<?php

namespace Excite\CustomClasses;

use DB;
use Auth;
use Request;

class UserProfile {
	// data as JS object literal string
	private $userProfileData = '{}';
	public function getUserProfileData() {
		return $this->userProfileData;
	}
	// dta as php array; not yet in use; never tested
	private $userProfileArr = [];
	public function getUserProfileArr() {
		return $this->userProfileArr;
	}

	function __construct() {

		$this->userProfile();
	}

	/** private functions **/
	
	private function userProfile() {
		$up = &$this->userProfileData;
		$uphp = &$this->userProfileArr;
		$userId = 55;
		$userId = Auth::user()->id;
		$contactId = Request::user()->contact_id;
		$uphp['contact'] = $contactId;

		if ( $contactId == null || $contactId == '' ) $contactId = 'null';
		$result = $this->getUserProfileQ($userId);
		if ($result[0]->key == null) { // dit is getest
			//user has no profile at all; is geen db fout
			$up = '{ any: false, user: ' . $userId . ', contact: ' . $contactId . ',}';
			return;
		}
		$up = '{ any: true, user: ' . $userId . ', contact: ' . $contactId . ',';
		$lastKey = '';
		foreach( $result as $r ) {
			$phpKey = $r->key;
			switch ($r->key) { // map for JS some db names on button names and fix value for some
				case 'Sexuele geaardheid':
					$r->key = 'SexNotInUse';
					break;
				case 'Postcode': // Button keys are Provincie and Kaart fix below
					$r->value = $r->meta; // hier staat opgegeven Postcode
					break;
				case 'Geboortejaar':
					$r->key = 'Leeftijd';
					$r->value = $r->meta;
					break;
				case 'Transportmiddelen':
					$r->key = 'Transport';
					break;
				case 'Titels':
					$r->key = 'Titel';
					break;
				case 'Gezondheid':
				case 'Geluk':
					$r->value = $r->meta;
			}
			if ( $r->key == $lastKey ) // for multiple entries
				continue;
			$lastKey = $r->key;
			// Note: "xxxx" == 0 gives true in php "xxxx" === 0 false
			if ( $r->value === null ) {
				$up .= $r->key . ': false,';
				if( $r->key == 'Postcode') {
					$up .= 'Provincie: false,';
					$up .= 'Kaart: false,';
				}
			}
			else {
				$up .= $r->key . ': true,';
				if( $r->key == 'Postcode') {
					$up .= 'Provincie: true,';
					$up .= 'Kaart: true,';
				}
			}
			$uphp[$phpKey] = $r->value;	
		}		
		$up .= '}';
	}
	
	// must go to User Model
	private function getUserProfileQ($userId) {
		$result = DB::table('sleutels')
		->select('sleutels.text AS key', 'waardes.text AS value', 'datapairs.meta', 'datapairs.metahl')
		->leftJoin('users', 'users.id', '=', DB::raw("$userId"))
		->leftJoin('datapairs', function($join) {
									$join->on ('users.dataset_id', '=', 'datapairs.dataset_id')
									->on('datapairs.sleutel_id', '=', 'sleutels.id');
								} )
		->leftJoin('datapairs_waardes', 'datapairs_waardes.datapair_id', '=', 'datapairs.id')
		->leftJoin('waardes', 'waardes.id', '=', 'datapairs_waardes.waarde_id')
		->get();
		//var_dump($result);
		
		return $result;
	}
}