<?php

namespace Excite\Models;

use Auth;
use DB;

class CustomerDbModel {
	/** SettingsController calls Leslie **/
	// Voor Dropdown lijst
	public static function viewSettings() {
		
  		$viewSettings = DB::table('customers')
		  	->select('compname','coc','firstname','surname','teloffice','display_name','customer_contacts.email')
  			->join('customer_contacts','customers.id','=','customer_contacts.id_customer')
  			->join('users','users.email','=','customer_contacts.email')
	    	->where('users.email', Auth::user()->email )
			->first();
        return $viewSettings;
	}
	
	public static function changeSettings($firstname,$lastname,$phone,$displayname) {
		
		DB::table('users')
            ->where('email', Auth::user()->email)
            ->update(array('display_name' => $displayname));
		
		DB::table('customer_contacts')
            ->where('email', Auth::user()->email)
            ->update(array(
				'firstname' => $firstname,
				'surname' => $lastname,
				//'email' => $email,
				'teloffice' => $phone
				));
		// TODO: CUSTOMERS -> compname , Coc
	}
	
	// Change password when logged in
	public static function changePwd($password) {
		DB::table('users')
            ->where('email', Auth::user()->email)
            ->update(array('password_digest' => $password));
	}
	
	/*** user type determination; global usage; provides Global JScript parameter in master.blade ***/
	const PUBL = -1;
	const LIGHT = 0;
	const EXTRA = 0;
	const EXPRESS = 1;
	const EXCITE = 2;
	static function getUserType($user = null) {
		if( $user != null ) {
			$c = $user->contact_id;
			if ( $c == null || $c == 0) return self::LIGHT;
			if ( $user->abo_type == 0 ) return self::EXPRESS;
			return self::EXCITE;	
		}
		if( ! Auth::user() ) return self::PUBL;
		$c = Auth::user()->contact_id;
		if ( $c == null || $c == 0) return self::LIGHT;
		if ( Auth::user()->abo_type == 0 ) return self::EXPRESS;
		return self::EXCITE;
	}	
	static function setUserType($type, $user = null) {
		if ( $user == null)
			$user = Auth::user();
		$abo_type = 0;
		if ( $type == self::EXCITE ) $abo_type = 1;
		DB::table('users')
		->where('id', $user->id)
		->update(array('abo_type' => $abo_type));
	}
	
	/*** GroupController call for Express Group Order GUI **/
	
	static function getArea($groupId) {
		$falseResult = ['groupType' => 'none', 'found' => false, 'lat'=>-1, 'lng'=>-1, 'radius'=> -1];
	  	if ( $groupId == 0 )
	   		return $falseResult;
	
		$q  = 'SELECT groups.id AS id, groups.customer_contract_id AS contractId, groups.express_customer_contract_id AS expressBackup, groups.deleted AS deleted, ';
		$q .= 'customer_contracts.radius AS radius, customer_contracts.latitude AS latitude, customer_contracts.longitude AS longitude, type_contract.definition AS contractType ';
		$q .= 'FROM groups ';
		$q .= 'LEFT JOIN customer_contracts ON customer_contracts.id = groups.customer_contract_id ';
		$q .= 'LEFT JOIN type_contract ON customer_contracts.id_contract = type_contract.id ';
		$q .= 'WHERE groups.user_del = 0 AND groups.id = ?';
		
		$result = DB::select($q,[$groupId]);
		//dd($result);
		$result = $result[0];
		if ( $result->contractId == 0 ) { // Public group
			$falseResult['groupType'] = 'public';
			return $falseResult;
		}
		if ( $result->contractType == 'postcode' ) { // eXpress group
			return ['groupType' => 'eXpress', 'found' => true, 'lat'=>$result->latitude, 'lng'=>$result->longitude, 'radius'=> $result->radius];
		}
		if ( $result->contractId == 1 ) { // Excite group with no contract yet
			if ( $result->expressBackup > 0 ) { // eXcite trial
				$falseResult['groupType'] = 'eXciteTrial';
				return $falseResult;
			}
			$falseResult['groupType'] = 'eXcite';
			return $falseResult;		
		}
		
	}
	
	/*** ExpressController calls for Express Group order Form handling ***/
	const NL = 0;
	static function getPriceList() { // is called once on initial express group order page view
		/*$priceList = [
			12 => [5 => 1000,	10 => 2000, 25 => 3000,	self::NL => 10000],
			1  => [5 => 100,	10 => 200, 	25 => 300,	self::NL => 1000],
		];	*/
		$result = DB::table('type_contract')
			->where('definition','postcode')
			->get();
		foreach ( $result as $r ) {
			$priceList[$r->gen_rt][$r->addition] = $r->gen_anual_amount /100;
		}
		return $priceList;
	}

	static function getPriceContractId($area,$month) {
		
		if($month > 1 && $month < 10) {
			$month = 1;
		} else {
			$month = 12;
		};
		
		$getPriceContractId = DB::table('type_contract')
			->select('id')
			->where('definition','postcode')
			->where('addition',$area)
			->where('gen_rt',$month)
			->first();

		return $getPriceContractId;
		
	}

	// return GPS lat/lng for zipCode of 4 digits
	static function getLatLng ( $zipCode, $checkZip = true ) {
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
			$newZip = self::getZipCode($lat,$lng);
			self::getLatLng($newZip, false);
		}
		return ['zipFound'=> $found, 'lat'=> $lat, 'lng'=> $lng];
	}

	//	Get Customer ID
	static function getCustomerId($email = null) {
		if ( $email == null )
			$email = Auth::user()->email;
			
		$getCustomerId = DB::table('customer_contacts')
		  	->select('id','id_customer')
	    	->where('email', $email )
			->first();
        return $getCustomerId;
	}

	// Get Postcode
	static function getPc($postcode,$distance) {
		$qLatLng = 'SELECT Yixowdist.pc FROM Yixowdist where (left(pc,4)= ? or mid(pc,5)= ?) And dist < ?';
		$getPc = DB::select($qLatLng, [$postcode,$postcode,$distance]);		
		return $getPc;
	}

	// Get ZipCode
	static function getZipCode($lat,$lng) {

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

	// Get Population
	static function getPopulation($pcRange) {

		$pcRange = explode(',',$pcRange);

		$getPopulation = DB::table('Yixow_pop')
			->select(DB::raw('sum(population) as max'))
			->whereIn('zipcode',$pcRange)
			->first();
			
		return $getPopulation->max; 
	}

	// store a contract in the db
	static function storeContract($argsContract,$argsGroup) {
		
		// DB Transaction
		DB::transaction(function () use ($argsContract,$argsGroup) {		
			// insert customer_contracts
			DB::table('customer_contracts')->insert(array($argsContract));

			if(!empty($argsGroup)) {
				// insert groups
				$argsGroup['customer_contract_id'] = $argsGroup['express_customer_contract_id'] = DB::getPdo()->lastInsertId();
				
				DB::table('groups')->insert(array($argsGroup));
			}
		
		});
		
	}
	
	static function checkExciteTrialContract($customer_Id = null) {
		if( $customer_Id == null ) {
			$result = self::getCustomerId();
			if ( count($result) == 0 ) return [];
			$customer_Id = $result->id_customer;
		}
		return DB::table('customer_contracts')
			->select('id_customer')
			->where('id_customer',$customer_Id)
			->where('id_contract','13')
			->first();	
	}

	// Proef abo van eXpress -> eXpress-eXcite groep
	static function updateGroupsTrial($customer_Id,$argsContract, $userId) {

		$checkExistingContract = self::checkExciteTrialContract($customer_Id);
		//echo "<br>checkExistingContract customerId=$customer_Id<br />";
		//var_dump(	$checkExistingContract);
		if(empty($checkExistingContract)) {	
			//echo "<br>checkExistingContract true<br />";
			self::storeContract($argsContract,null);
				
		}
		//dd("Die");
		DB::table('groups')
				->where('user_id' , '=' , $userId)
				->where('customer_contract_id' , '>' , '1')
				->where('user_del' , '=' , '0')
				->update(['range' => '', 'customer_contract_id' => '1']);
				// voor het gemak: customer_contract_id op 1 zetten; later kan contract id worden ingevuld
	}
	
	// Get Firstname bases on Email
	static function getFirstname($Email) {

		$getFirstName = DB::table('customer_contacts')
			->select('firstname')
			->where('email',$Email)
			->first();
			
		return $getFirstName->firstname;
	}
	
	static function isValidKvk($kvk) {
		
		$getKvk = DB::table('kvk')
			->select('kvknumber')
			->where('kvknumber',$kvk)
			->first();
			
		return $getKvk;		
	}
		

}