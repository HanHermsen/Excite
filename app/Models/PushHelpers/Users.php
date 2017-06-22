<?php

namespace Excite\Models\PushHelpers;

use Illuminate\Database\Eloquent\Model;
use DB;
use Vinkla\Hashids\Facades\Hashids;
use Config;
use Carbon\Carbon;

class Users extends Model
{   
	/**
     * Get existing user with provided param
     *
     * @param  string  email
     * @return object
     */
    public static function getWithEmail($email)
    {
        $user = DB::table('users')
	    				->where('email', $email)
	    				->first();
	    				
		return $user;
	}
	
	/**
     * Get existing user with provided param
     *
     * @param  string  display name
     * @return object
     */
	public static function getWithDisplayName($displayName)
    {
        $user = DB::table('users')
	    				->where('display_name', $displayName)
	    				->first();
	    				
		return $user;
	}
	
	/**
     * Get existing user with provided param
     *
     * @param  string  authentication token
     * @return object
     */
	public static function getWithAuthenticationToken($token)
    {
        $user = DB::table('users')
	    				->where('authentication_token', $token)
	    				->first();
	    				
		return $user;
	}
	
	/**
     * Get existing user with provided param
     *
     * @param  string  reset token
     * @return object
     */
	public static function getWithResetToken($token)
    {
        $user = DB::table('users')
	    				->where('reset_token', $token)
	    				->first();
	    				
		return $user;
	}
	
	/**
     * Set new password for user user
     *
     * @param  string  user id
     * @param  string  new password
     */
	public static function setNewPassword($userId, $password)
	{
		$passwordDigest = password_hash($password, PASSWORD_BCRYPT);
	    $date = Carbon::now();
		
		DB::table('users')
            ->where('id', $userId)
            ->update(['password_digest' => $passwordDigest, 'reset_token' => NULL, 'updated_at' => $date]);
	}
	
	/**
     * Create new user (and dataset) with provided param and return authentication token
     *
     * @param  string  email
     * @param  string  password
     * @param  string  displayName
     * @return string
     */
	public static function register($email, $password, $displayName, $countryId)
    {
	    $authenticationToken = Users::_generateAuthenticationToken();
	    $passwordDigest = password_hash($password, PASSWORD_BCRYPT);
	    $date = Carbon::now();
	    
	    $dataSetId = DB::table('datasets')->insertGetId([
			'created_at' => $date, 
			'updated_at' => $date
		]);
	    
	    DB::table('users')->insert([
			'email' => $email, 
			'password_digest' => $passwordDigest, 
			'display_name' => $displayName, 
			'country_id' => $countryId,
			'authentication_token' => $authenticationToken, 
			'created_at' => $date, 
			'updated_at' => $date, 
			'dataset_id' => $dataSetId
		]);
        				
		return $authenticationToken;
	}
	
	/**
     * Generate random authentication token, check if it's unique and return the authentication token
     *
     * @return string
     */
	private static function _generateAuthenticationToken()
	{
		$bytes = openssl_random_pseudo_bytes(32);
		$hex = bin2hex($bytes);
		$base64endcoded = str_replace('=', '', strtr(base64_encode($hex), '+/', '-_'));
		
		$existingUser = Users::getWithAuthenticationToken($base64endcoded);
		if(!$existingUser) {
			// token is unique
			return $base64endcoded;
		}
		else {
			// token is not unique: create a new one
			return Users::_generateAuthenticationToken();
		}
	}
	
	/**
     * Set user reset token and reset date and return the token
     *
     */
	public static function resetUserPassword($userId)
	{
		$resetToken = Users::_generateResetToken();
		$date = Carbon::now();
		
		DB::table('users')
            ->where('id', $userId)
            ->update(['reset_token' => $resetToken, 'reset_at' => $date]);
        
        return $resetToken;
	}
	
	/**
     * Generate random reset token, check if it's unique and return the reset token
     *
     * @return string
     */
	private static function _generateResetToken()
	{
		$bytes = openssl_random_pseudo_bytes(10);
		$hex = bin2hex($bytes);
		$base64endcoded = str_replace('=', '', strtr(base64_encode($hex), '+/', '-_'));
		
		$existingUser = Users::getWithResetToken($base64endcoded);
		if(!$existingUser) {
			// token is unique
			return $base64endcoded;
		}
		else {
			// token is not unique: create a new one
			return Users::_generateAuthenticationToken();
		}
	}
	
	/**
     * Enable questions which were marked "Answer later" (type 1) more than a day ago
     */
	public static function cleanupHides()
    {
	    $formatted_date = Carbon::now()->subDay()->toDateTimeString();
	    DB::table('question_user_attributes')
	    		->where('user_id', '=', Config::get('userId'))
	    		->where('type', '=', 1)
	    		->where('updated_at', '<', $formatted_date)
	    		->delete();
	}
	
	public static function resetUnreadCount()
    {
	    DB::table('users')
           		 ->where('id', Config::get('userId'))
		   		 ->update(['unread_count' => 0]);
	}
	
	public static function increaseUnreadCount($userIds)
    {
	    DB::table('users')
	    		->whereIn('id', $userIds)
		   		->increment('unread_count');
	}
	
	public static function getProfileData()
    {
	    $arr = DB::table('datapairs')
	    				->where('dataset_id', '=', Config::get('userDatasetId'))
	    				->join('sleutels', 'datapairs.sleutel_id', '=', 'sleutels.id')
	    				->select('sleutels.*', 'datapairs.id AS datapairId', 'datapairs.meta AS metaValue')
	    				->orderBy('sleutels.order', 'asc')
	    				->get();
	    				
	    return $arr;
	}
	
	public static function getProfileDataValues($dataPairIds)
    {
	    $arr = DB::table('datapairs_waardes')
	    				->whereIn('datapairs_waardes.datapair_id', $dataPairIds)
	    				->join('waardes', 'datapairs_waardes.waarde_id', '=', 'waardes.id')
	    				->select('waardes.*', 'datapairs_waardes.datapair_id AS datapairId')
	    				->get();
	    				
	    return $arr;
	}
	
	public static function getDatapair($keyId)
	{
		$object = DB::table('datapairs')
	    				->where('dataset_id', '=', Config::get('userDatasetId'))
	    				->where('sleutel_id', '=', $keyId)
	    				->first();
	    				
	    return $object;
	}
	
	public static function getProfileZipCode()
    {
	    $obj = DB::table('datapairs')
	    				->where('dataset_id', '=', Config::get('userDatasetId'))
	    				->join('sleutels', 'datapairs.sleutel_id', '=', 'sleutels.id')
	    				->where('sleutels.text', 'like', '%postcode%')
	    				->select('datapairs.meta AS zipCode')
	    				->orderBy('datapairs.id', 'asc')
	    				->first();
	    				
	    return $obj;
	}
	
	public static function deleteProfileKey($keyId)
	{
		DB::table('datapairs')
						->where('sleutel_id', '=', $keyId)
						->where('dataset_id', Config::get('userDatasetId'))
						->delete();
	}
	public static function setProfileKey($dataPair, $keyId, $meta = null)
	{
		if($dataPair == null) {
			// create row
			return DB::table('datapairs')->insertGetId([
				'dataset_id' => Config::get('userDatasetId'), 
				'updated_at' => Carbon::now(), 
				'created_at' => Carbon::now(), 
				'sleutel_id' => $keyId, 
				'meta' => $meta
			]);
		}
		else {
			// update row
			DB::table('datapairs')
           		 	->where('id', $dataPair->id)
		   		 	->update(['updated_at' => Carbon::now(), 'sleutel_id' => $keyId, 'meta' => $meta]);
		    return $dataPair->id;
		}
	}
	
	public static function deleteProfileValues($dataPairId){
		DB::table('datapairs_waardes')
						->where('datapair_id', '=', $dataPairId)
						->delete();
	}
	
	public static function setProfileValue($dataPairId, $valueId)
	{
		DB::table('datapairs_waardes')->insert([
			'datapair_id' => $dataPairId, 
			'waarde_id' => $valueId, 
		]);
	}
	
	public static function getUserCat($catId = null) 
	{
		$query = DB::table('categories_datasets')
	    					->where('dataset_id', Config::get('userDatasetId'))
	    					->join('categories', 'categories_datasets.category_id', '=', 'categories.id')
	    					->select('categories.id', 'categories.name')
	    					->orderBy('categories.name', 'asc');
	    if($catId != null) {
		    $query->where('categories.id', $catId);
		    $arr = $query->first();
	    }
	    else {
		    $arr = $query->get();
	    }	   
		
		return $arr;
	}
	
	public static function userIsAlreadySubscribedToCategory($catId) {
		$object = Users::getUserCat($catId);	    					
	    if($object != null)
	    {
		    return true;
	    }	   
		return false;
	}
	
	public static function subcribeToCat($categoryId)
	{
		// create row
		return DB::table('categories_datasets')->insert([
			'dataset_id' => Config::get('userDatasetId'), 
			'category_id' => $categoryId
		]);
	}
	
	public static function unsubcribeFromCat($categoryId)
	{
		// delete row
		DB::table('categories_datasets')
						->where('dataset_id', '=', Config::get('userDatasetId'))
						->where('category_id', '=', $categoryId)
						->delete();
	}
	
	public static function getUserQuestionAttribute($questionId)
    {
	    $attribute = DB::table('question_user_attributes')
	    					->where('question_id', $questionId)
	    					->where('user_id', Config::get('userId'))
	    					->first();
	}
	
	public static function setUserQuestionAttribute($attribute, $type, $questionId)
    {
	    if($attribute == null) {
			// create row
			return DB::table('question_user_attributes')->insert([
				'question_id' => $questionId, 
				'user_id' => Config::get('userId'), 
				'type' => $type, 
				'updated_at' => Carbon::now(), 
				'created_at' => Carbon::now()
			]);
		}
		else {
			// update row
			DB::table('question_user_attributes')
           		 	->where('id', $attribute->id)
		   		 	->update(['type' => $type, 'updated_at' => Carbon::now()]);
		}
    }
    
    // Creates a new dataset containing the current datasets data, and sets the new one as the users current dataset
    public static function fork()
    {
	    $date = Carbon::now();
	    
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
		
    }
}