<?php

namespace Excite\Models;

use DB;

class registrationDbModel {

	public $id;

	//public function addCustomer($Company,$KVK,$Firstname,$Lastname,$Phone,$Email,$Status,$Language,$Admin) {
	public function addCustomer($compData) {
		/*/ controle KVK bekend
 		$getKVK = DB::table('klanten')
	        ->select('kvk')
	    	->where('kvk',$KVK)
	        ->count();

     	if($getKVK != 0) {
			exit('klant al bekend, contact opnemen');
		}
		*/

		// klant gegevens toevoegen	
		$klantId = DB::table('customers')->insertGetId(
					array('compname' => $compData['company'],'coc' => $compData['kvk'])
				);

		// klanten_contacten toevoegen
		$addKlanten_contact = DB::table('customer_contacts')->insertGetId(
			array(
			'id_customer' => $klantId,
			'firstname' => $compData['firstname'],
			'surname' => $compData['lastname'],
			'initials' => $compData['firstname'][0],
			'email' => $compData['email'],
			'teloffice' => $compData['phone'],
			'status' => '0',
			'language' => 'NL',
			'admin' => 'J'
			)
		);
		
		$this->id = $addKlanten_contact;
	}
	
	public function updateUserContactId($lastId,$Email) {
			      
	   	// Update users met contact_id
	   	DB::table('users')
	        ->where('email', $Email)
	        ->update(['contact_id' => $lastId]);
	}
	
	public static function dataSet($timeStamp) {
		$addDataSet = DB::table('datasets')->insertGetId(
			array(
			'created_at' => $timeStamp,
			'updated_at' => $timeStamp
			)
		);
		return $addDataSet;
	}
}