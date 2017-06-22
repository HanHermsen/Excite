<?php

namespace Excite\Models;

use DB;
use Session;

class GuestDbModel {

	/**
	 * guestDbModel::checkUser()
	 * Check if user is in table Users and Members
	 * @param mixed $userEmail
	 * @param mixed $groupId
	 * @return
	 */
	private static function checkUser($userEmail,$groupId){
		
        $checkMemberUser = DB::table('users')
            ->select(DB::raw('users.id,users.email,members.group_id,members.id as memberId'))
            ->leftJoin('members', function($join) use ($groupId)
                {
                $join->on('users.id', '=', 'members.user_id')
                     ->where('members.group_id', '=', $groupId);
                }
            )
            ->where('users.email', $userEmail)
        	->first();
        	
        	return $checkMemberUser;
		
	}
	
	public static function getInviteId($email,$groupId) {
		$selectIdInvite = DB::table('invitations')
            ->select('id')
            ->where('email', $email)
            ->where('group_id',$groupId)
        	->first();
        	
        	return $selectIdInvite;		
	}
	// new Han
	public static function getUserEmail($id) {
		$q  = 'SELECT email FROM users ';
		$q .= 'WHERE id = ?';
		$result = DB::select($q,[$id]);
		return $result[0]->email;
	}

	/**
	 * guestDbModel::checkInvited()
	 * Check if user is invited already
	 * @param mixed $userEmail
	 * @param mixed $groupId
	 
	 * @return
	 */

	public static function checkInviteId($email,$groupId) {
		$q  = 'SELECT id FROM invitations ';
		$q .= 'WHERE email = ? AND group_id = ?';
		$result = DB::select($q, [$email,$groupId]);
		if ( count($result) == 0 ) return 0;
		return $result[0]->id;
	}
	
	public static function checkInvited($userEmail,$groupId) {
      	
		$checkMemberIsInvited = DB::table('invitations')
            ->select('email')
            ->where('email', $userEmail)
            ->where('group_id',$groupId)
        	->first();
        	
        	return $checkMemberIsInvited;
	}
	
	/**
	 * guestDbModel::insertInvite()
	 * 
	 * @param mixed $userEmail
	 * @param mixed $groupId
	 * @param mixed $timeStamp
	 * @return
	 */
	public static function insertInvite($userEmail,$groupId,$timeStamp,$display_email) {
		
		$addToInvitations = DB::table('invitations')->insert(
			array(
			'group_id' => $groupId,
			'created_at' => $timeStamp,
            'updated_at' => $timeStamp,
            'email' => $userEmail,
            'accepted' => 0,
			'display_email' => $display_email,
			)
		);	
		
		return $addToInvitations;
			
	}

	/**
	 * guestDbModel::viewGuests()
	 * 
	 * @param mixed $groupId
	 * @return
	 */
	public static function viewGuests($groupId) {
		
		if($groupId == null )
		// Oh Les wat doe je hier nu? Dit is het tegendeel van MVC. De calling Controller moet de juiste groupId leveren!!!!!
		// het Model is een abstractie die over dit soort dingen _niks_ mag weten
			$groupId = Session::get('viewID');

    	$viewGuests = DB::table('members')
        ->select('email')
    	->join('groups','groups.id', '=', 'members.group_id')
    	->join('users','users.id','=','members.user_id')
    	->where('groups.id', $groupId)
    	->get();

        return $viewGuests;
		
	}
	public static function getMembersLcSorted($groupId) {
		$q  = 'SELECT CASE ';
		$q .= 'WHEN members.display_email = 0 THEN CONCAT ( REPLACE(REPLACE( TRIM(LCASE(users.display_name))," ","_"), "/", "_" ), "/", ((users.id  + 3) * 10)DIV 2 ) ';
        $q .= 'ELSE LCASE(users.email) ';
		$q .= 'END AS email, ';
		$q .= 'TRIM(members.display_email) AS display_email , ((users.id + 3) * 10)DIV 2 AS mappedUid FROM members ';
		$q .= 'JOIN users ON users.id = members.user_id ';
		$q .= 'WHERE group_id = ? ';
		//$q .= 'WHERE group_id = ? AND display_email = 1) ';
		$q .= 'ORDER BY email ASC ';
		return  DB::select($q,[$groupId]);
	}
	public static function getInvitationsLcSorted($groupId) {
		$q  = 'SELECT CASE ';
		$q .= 'WHEN display_email = 0 THEN CONCAT ( REPLACE(REPLACE( TRIM(LCASE(users.display_name))," ","_"), "/", "_" ), "/", ((users.id + 3) * 10)DIV 2 ) ';
        $q .= 'ELSE LCASE(invitations.email) ';
		$q .= 'END AS email, ';
		$q .= 'TRIM(display_email) AS display_email, ((users.id + 3) * 10)DIV 2 AS mappedUid FROM invitations ';
		$q .= 'LEFT JOIN users ON users.email = invitations.email ';
		$q .= 'WHERE group_id = ? AND accepted = 0 ';
		//$q .= 'WHERE group_id = ? AND accepted = 0 AND display_email = 1 ';
		$q .= 'ORDER BY email ASC ';
		return DB::select($q,[$groupId]);		
	}
	public static function mapUid($uid,$name) {
		//$o = (($uid+strlen($name)+3)*10)/2;
		$o = (($uid+3)*10)/2;
		return $o;
		//return self::unmapUid($o,$name);
	}
	public static function unmapUid($uid,$name) {
		return (( $uid * 2 )/ 10) - 3;
	}
	/**
	 * guestDbModel::viewInvites()
	 * 
	 * @param mixed $groupId
	 * @return
	 */
	public static function viewInvites($groupId) {
		
		$invitations = DB::table('invitations')
			->select('email')
			->where('group_id', $groupId)
			->where('accepted',0)
			->orderBy('email', 'asc')

			->get();

		return $invitations;
		
	}
	
	public static function deleteMember($groupId,$email) {
        $delete = DB::table('members')
        ->join('users','users.id','=', 'members.user_id')
        ->where('group_id' , '=' , $groupId)
        ->where('email', '=' , $email)
        ->delete();
		// new Han: dit moet ook Leslie! Er hangen nu nog wel wat invitations uit van deleted members
		// dat geeft natuurlijk een probleem bij het opnieuw invoeren van zo iemand
		// wordt gezien als een heruitnodiging ..... van een nog bestaande uitnodiging die accepted is
		self::deleteInvite($groupId,$email);
	}
	// new Han
	public static function deleteMemberByUid($groupId,$uid) {
		$email = self::getUserEmail($uid);
		self::deleteMember($groupId,$email);
	}

	public static function deleteInvite($groupId,$email) {
		//dd("hier " . $groupId . ' ' . $email);
        $delete = DB::table('invitations')
        ->where('group_id' , '=' , $groupId)
        ->where('email', '=' , $email)
        ->delete();
	}
	// new Han
	public static function deleteInviteByUid($groupId,$uid) {
		$email = self::getUserEmail($uid);
		self::deleteInvite($groupId,$email);
	}
	
	public static function fixDisplayEmail($email, $groupId, $groupOwnerId = null) {
		//dd("Hierzo " . $email);
		$returnVal = false;
		$q  = 'SELECT members.id AS id FROM members ';
		$q .= 'JOIN users ON members.user_id = users.id ';
		$q .= 'WHERE LCASE(users.email) = ? AND members.group_id = ? ';
		$r = DB::select($q,[$email,$groupId]);
		if ( count($r) > 0 ) { // will fix for member of this group
			$returnVal = true;
		}
		$q  = 'SELECT members.id AS id FROM members ';
		$q .= 'JOIN users ON members.user_id = users.id ';
		$q .= 'JOIN groups ON groups.user_id = ? ' ;
		$q .= 'WHERE LCASE(users.email) = ? ';
		$q .= 'GROUP BY members.id ';
		$result = DB::select($q,[$groupOwnerId,$email]);
		foreach ( $result as $r ) {
			DB::table('members')
			->where('id',$r->id)
			->update(['display_email' => 1]); 		
		}
		
		$q  = 'SELECT invitations.id FROM invitations ';
		$q .= 'JOIN groups ON groups.user_id = ? ';
		$q .= 'WHERE LCASE(invitations.email) = ? ';		
		$q .= 'GROUP BY invitations.id ';
		$result = DB::select($q,[$groupOwnerId,$email]);
		foreach ($result as $r) {
			DB::table('invitations')
			->where('id',$r->id)
			->update(['display_email' => 1]);		
		}
		return $returnVal;
	}
	
}