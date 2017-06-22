<?php

namespace Excite\Models;

use Auth;
use DB;
use Input;

class groupDbModel {

	// Voor groepskeuze dropdown list; somewhat hacked parameters for backwards compatibility
	// with existing caller code
	// viewGroups() returns all active groups (deleted = 0 user_del = 0)
	// viewGroups(true) returns all inactive groups (deleted = 1 user_del = 0)
	// viewGroups(true,true) or viewGroups(false,true) returns all active _and_ inactive groups
	public static function viewGroups($showDeleted = false, $getAllAnyway = false){
		$uType = CustomerDbModel::getuserType();

  		$q = DB::table('groups')
	        ->select('id','name')
	    	->where('user_id', Auth::user()->id )
			->where('user_del', 0) // can only be 1 when deleted is 1
	    	->orderBy('name','asc');
		if ( ! $getAllAnyway )
			$q->where('deleted',($showDeleted)? '1': '0');

		if($uType == CustomerDbModel::EXCITE || $uType == CustomerDbModel::EXPRESS) {
			$q->where('customer_contract_id','>', 0);
		}
		return $q->lists('name', 'id');		
	}
	
	public static function getGroupName($group_id) {
  		$getGroupName = DB::table('groups')
	        ->select('name')
	    	->where('id',$group_id)
	        ->first();

        return $getGroupName;		
	}
	
	// give group names of all active and inective groups for this user
	public static function getGroupNames($userId, $uType) {
			$q = DB::table('groups')
	        ->selectRaw('DISTINCT LCASE (name) AS name')
	    	->where('user_id', $userId )
			->where('user_del', 0)
	    	->orderBy('name','asc');
			/* if($uType == CustomerDbModel::EXPRESS ) // is niet nodig hier
				$q = $q->where('customer_contract_id','>', 0);*/
			return $q->lists('name');
	}
	
	// edit_groups.blade.php
	public static function viewEditGroup($groupId){
		
  		$viewEditGroup = DB::table('groups')
	        ->select('customer_contracts.properties','customer_contracts.radius','customer_contracts.population','groups.id','groups.type','groups.name','groups.color','groups.image','groups.date_expired','groups.deleted','groups.express_label','groups.sort_type','groups.group_display')
	        ->leftJoin('customer_contracts','groups.customer_contract_id', '=' , 'customer_contracts.id')
	    	->where('groups.user_id', Auth::user()->id )
	    	->where('groups.id',$groupId)
	        ->first();
		//dd($viewEditGroup);
        return $viewEditGroup;
		
	}
	
	public static function insertGroups($name,$image,$timestamp,$colors,$date_expired,$groupType,$groupSubname,$groupSort,$groupInactive,$GroupLabelActivate) {

		$addGroup = DB::table('groups')->insert(
			array(
			'user_id' => Auth::user()->id,
			'name' => $name,
			'created_at' => $timestamp,
			'updated_at' => $timestamp,
			'color' => $colors,
			'image' => $image,
			'date_expired' => $date_expired,
			'type' => $groupType,
			'express_label' => $groupSubname,
			'sort_type' => $groupSort,
			'deleted' => $groupInactive,
			'group_display' => $GroupLabelActivate,
			'customer_contract_id' => 1, // we do not use or make a contract yet
			)
    
		);
	}

	 public static function updateGroups($id,$args) {
  
		  $updateGroup = DB::table('groups')
		   ->where('id',$id)
		   ->where('user_id',Auth::user()->id)
		   ->limit(1)
		   ->update($args); 
	}

}