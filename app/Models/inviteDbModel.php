<?php

namespace Excite\Models;

use DB;
use Auth;

class inviteDbModel {


	/**
	 * inviteDbModel::getInvites()
	 * 
	 * @return
	 */
	public static function getInvites(){
	
 		$invited = DB::table('invitations')
            ->select('invitations.group_id','invitations.id','groups.name','invitations.created_at')
            ->join('groups', 'groups.id', '=', 'invitations.group_id')
            ->where('email', Auth::user()->email)
        	->get();

        return $invited;
	}
	
	/**
	 * inviteDbModel::deleteInvite()
	 * 
	 * @param mixed $id
	 * @return
	 */
	public static function deleteInvite($id) {
        
        $delete = DB::table('invitations')
            ->where('id' , '=' , $id)
            ->where('email', '=' , Auth::user()->email)
            ->delete();

    }

    /**
     * inviteDbModel::acceptInvite()
     * 
     * @param mixed $gId
     * @return
     */
    public static function acceptInvite($gId) {
        
		$accept = DB::table('members')->insert(
			array(
			'group_id' => $gId,
			'user_id' => Auth::user()->id,
			)

    	);

    }
	
}