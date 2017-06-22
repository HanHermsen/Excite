<?php
namespace Excite\CustomClasses;
use Auth;
use DB;
use PDO;


class Groups {
	static function getGroupsForUser () {
		// this is important; default is object array! Geeft gezeik bij merge etc.
		DB::setFetchMode(PDO::FETCH_ASSOC);
		$gList = DB::table('groups')
    	->select('id','name')
    	->where('user_id', Auth::user()->id )
    	->orderBy('name','asc')
		->get();
		return $gList;
	}
}


?>