<?php

namespace Excite\Models\PushHelpers;

use Illuminate\Database\Eloquent\Model;
use DB;
use Config;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;

class Groups extends Model
{   
    public static function getGroups()
    {
	    $userId = Config::get('userId');
        $groups = DB::table('groups')
        				->where(function($query) use ($userId){							
							$query->where('members.user_id', $userId);			
							$query->orWhere('groups.user_id', $userId);						
						})
						->where('groups.deleted', '=', 0)			
	    				->leftJoin('members', 'groups.id', '=', 'members.group_id')
	    				->join('users', 'groups.user_id', '=', 'users.id')
	    				->groupBy('groups.id')
	    				->orderBy('groups.name', 'asc')
	    				->select('groups.*', 'users.display_name', 'users.id as owner_id')
	    				->get();
	    				
		return $groups;
	}
	
	public static function getExpressGroups($zipCode)
    {
	    //$zipCode = "%2974%";
	    $userId = Config::get('userId');
	    $query = DB::table('groups')
						->where('groups.deleted', '=', 0)	
						->where('groups.group_display', '=', 1)						
	    				->leftJoin('members', function ($join) {
							$join->on('members.group_id', '=', 'groups.id')
									->on('members.user_id', '=', DB::raw(Config::get('userId')));
        				})
	    				->join('users', 'groups.user_id', '=', 'users.id')
	    				->groupBy('groups.id')
	    				->orderBy('groups.express_label', 'asc')
	    				->select('groups.*', 'users.display_name', 'users.id as owner_id', 'members.user_id as member_id');
	    
	    if($zipCode != null && strlen($zipCode) >= 4) {
		    // strip letters and add % for selection
		    $zipCode = substr($zipCode, 0, 4);
		    $zipCode = '%'.$zipCode.'%';
		    
		    $query->where(function($query) use ($zipCode){							
							$query->where('groups.range', 'like', $zipCode);			
							$query->orWhere('groups.range', '');
						});
	    }
	    else {
		    $query->where('groups.range', '');
	    }
	    
	    $groups = $query->get();
	    				
		return $groups;
	}
	
	/*
	 * Get Group
	 *
	 * param int group id
	 * param bool if user must be a member or group owner
	*/
	public static function getGroup($groupId, $onlyPermittedUser = true, $userId = null)
    {
	    if($userId == null)
	    	$userId = Config::get('userId');
	    	
	    $query = DB::table('groups')
        				->where('groups.id', $groupId)
        				->where('groups.deleted', '=', 0)
	    				->leftJoin('members', 'groups.id', '=', 'members.group_id')
	    				->join('users', 'groups.user_id', '=', 'users.id')
	    				->select('groups.*', 'users.display_name', 'users.id as owner_id');
	    
	    if($onlyPermittedUser) {
		    $query->where(function($query) use ($userId){							
							$query->where('members.user_id', $userId);			
							$query->orWhere('groups.user_id', $userId);						
						});
	    }
	    
	    $group = $query->first();
	    				
		return $group;
	}
	
	public static function getGroupMemberCounts($groupIds)
    {
        $memberCounts = DB::table('members')
	    				->whereIn('group_id', $groupIds)
	    				->groupBy('group_id')
	    				->select(array('group_id', DB::raw('COUNT(user_id) as memberscount')))
	    				->get();
	    				
		return $memberCounts;
	}
	
	public static function getGroupMembers($groupId)
    {
        $memberCounts = DB::table('members')
	    				->where('group_id', $groupId)
	    				->join('users', 'members.user_id', '=', 'users.id')
	    				->groupBy('user_id')
	    				->select('users.id', 'users.display_name')
	    				->orderBy('users.display_name', 'asc')
	    				->get();
	    				
		return $memberCounts;
	}
	
	public static function getGroupQuestionCounts($groupIds)
    {
        $questionCounts = DB::table('questions')
	    				->whereIn('group_id', $groupIds)
	    				->where('questions.deleted', '=', 0)
	    				->groupBy('group_id')
	    				->select(array('group_id', DB::raw('COUNT(id) as questionscount')))
	    				->get();
	    				
		return $questionCounts;
	}
	
	public static function getGroupUnseenQuestionCount($groupId, $lastSeenDate)
    {
	    // select COUNT(id) as questionscount from questions where created_at > 2015-07-22 22:49:39 and group_id=141 group by group_id
        $questionCounts = DB::table('questions')
        				->where('created_at', '>', $lastSeenDate)
	    				->where('group_id', $groupId)
	    				->where('questions.deleted', '=', 0)
	    				->groupBy('group_id')
	    				->select(array(DB::raw('COUNT(id) as questionscount')))
	    				->first();
	    
	    if($questionCounts == NULL) {
		    $questionCounts = (object) array('questionscount' => 0);
	    }
	    				
		return $questionCounts;
	}
	
	public static function createGroup($name)
    {
	    $colors = array("B94794", "D91F7A", "DE2757", "E64520", "ED7705", "F6AA05", "E5CA05", "E5E124", "21A737", "00A88C");
	    $date = Carbon::now();
	    $colorHex = $colors[ rand(0, 9) ];
	    $color = hexdec($colorHex);
	    
	    $id = DB::table('groups')->insertGetId([
			'user_id' => Config::get('userId'), 
			'name' => $name, 
			'created_at' => $date, 
			'updated_at' => $date, 
			'color' => $color
		]);
		
		$newGroup = array();
		$newGroup['id'] = Hashids::encode($id);
		$newGroup['name'] = $name;
		$newGroup['color'] = "#".$colorHex;
		$newGroup['owner']['id'] = Hashids::encode(Config::get('userId'));
		$newGroup['owner']['display_name'] = Config::get('userDisplayName');
		$newGroup['member_count'] = 0;
		$newGroup['question_count'] = 0;
	    				
		return $newGroup;
	}
	
	public static function userIsOwner($groupId)
	{
        $group = DB::table('groups')
        				->where('id', $groupId)
        				->where('user_id', Config::get('userId'))
	    				->select('id')
	    				->first();
	    
	    if($group != null) {
		    return true;
	    }
		return false;
	}
	
	public static function deleteGroup($groupId)
	{
		// update row (no real deletion)
		DB::table('groups')
       		 	->where('id', $groupId)
	   		 	->update(['updated_at' => Carbon::now(), 'deleted' => 1,]);
	}
	
	public static function joinGroup($groupId)
	{
		// add row
		DB::table('members')->insert([
			'group_id' => $groupId,
			'user_id' => Config::get('userId')
		]);
	}
	
	public static function leaveGroup($groupId, $userId = null)
	{
		if($userId == null)
	    	$userId = Config::get('userId');
	    	
		// delete row
		DB::table('members')
						->where('group_id', '=', $groupId)
						->where('user_id', '=', $userId)
						->delete();
	}
	
	public static function decToHex($color) {
		$hex = dechex($color);
		$extraZeros = 6 - strlen($hex);
		for($i = 0; $i < $extraZeros; $i++) {
			$hex = "0".$hex;
		}
	    
	    return "#".$hex;
	}
	
	public static function getImgUrl($img)
    {
	    if($img != NULL) {
		    return url('/images/groups').'/'.$img;
	    }
	    return NULL;
    }
}