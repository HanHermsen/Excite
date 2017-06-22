<?php

namespace Excite\Http\Controllers\Guests;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
//use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Http\Request;

use Excite\CustomClasses\DataTables;
use Excite\Models\GuestDbModel;
use Auth;

class GuestTableController extends Controller {

	public function getTableData(Request $r) {
		$groupId = $r->get('groupId');
		/**
		if ( Session::has('viewID') ) {
			$groupId = Session::get('viewID');
		}
		**/
		
		//if ($r->has('groupId'))
			//$groupId = $r->input('groupId');
			
		// get some from the environment when needed; f.i user id from login when needed in db query
		$userId = Auth::user()->id;
		// tmp for test 55 is Leo 56 is Arie
		//$userId = 56;
		

		// specify: give db => fieldname  dt => column number in table tName => table name of the field
		// when tname = '' its a variable name
		$columns = array(
			// 0 checkbox is rendered on Client
			array( 'db' => 'checkbox', 'dt' => 0 , 'tName' => '' ),
			// 1 Guest
			array( 'db' => 'email', 'dt' => 1 , 'tName' => '' ),
			// 2 Date in
			//array( 'db' => 'created_at', 'dt' => 2 , 'tName' => 'users' ),
			array( 'db' => 'users_created_at', 'dt' => 2 , 'tName' => '' ),
			// 3 group name
			array( 'db' => 'name', 'dt' => 3 , 'tName' => 'groups' ),
			// 4 questionCnt
			array( 'db' => 'groupQuestionCnt', 'dt' => 4 , 'tName' => '' ),
			// 5 percentage is calculated on client
			// 6 answer count
			array( 'db' => 'memberAnswerCnt', 'dt' => 6 , 'tName' => '' ),
			// 7 Date out ???? Out of what?
			array( 'db' => 'users_created_at', 'dt' => 7 , 'tName' => '' ),
			// 8 uid hidden
			array( 'db' => 'uid', 'dt' => 8 , 'tName' => '' ),
			// 9 display_name hidden
			array( 'db' => 'display_name', 'dt' => 9 , 'tName' => '' ),
			// 10 display_email hidden
			array( 'db' => 'display_email', 'dt' => 10 , 'tName' => 'members' ),
			// 11 hidden_email hidden will be noReply@yixow.com for all; not in use.
			array( 'db' => 'hidden_email', 'dt' => 11 , 'tName' => 'users' ),
			array( 'db' => 'deleted', 'dt' => 12 , 'tName' => 'groups' ),
			array( 'db' => 'groupId', 'dt' => 13 , 'tName' => '' )
			
		);
		
		//$tstGid = '';
		//if ($groupId != 0 )
			//tstGid = ' AND groups.id != ' . $groupId . ' ';
		// specify: the JOIN part of the query when more Tables are involved; can be ''
		$join = 'JOIN groups ON groups.user_id = ' . $userId . ' AND members.group_id = groups.id AND groups.user_del = 0 AND (groups.customer_contract_id > 0) JOIN users ON users.id = members.user_id LEFT JOIN questions ON questions.group_id = groups.id AND questions.deleted = 0 ';
		// specify: a GROUP BY when needed; can be ''
		$groupBy = "GROUP BY members.id";
		
		$join2 = 'JOIN groups ON groups.user_id = ' . $userId . ' AND members.group_id = groups.id AND groups.user_del = 0 ';

		// specify: first query prefix part for retrieval of the data
		// after this query the query 'SELECT FOUND_ROWS()' will be done automatically
		// for getting the count of the records in the SELECT

		// de groupAnswerCnt komt uit memeb komen het is een geindividualiseerde total answer count
		$q  = 'SELECT SQL_CALC_FOUND_ROWS 1 AS checkbox, CASE ';
		$q .= 'WHEN members.display_email = 0 THEN REPLACE (REPLACE(TRIM( LCASE(users.display_name))," ","_"),"/","_") ';
        $q .= 'ELSE LCASE(users.email) ';
		$q .= 'END AS email, ';
		$q .= 'DATE (users.created_at) AS users_created_at, groups.name, COUNT(questions.id) as groupQuestionCnt, members.response AS memberAnswerCnt, users.id AS uid, REPLACE( REPLACE(TRIM( LCASE(users.display_name)), " ", "_"),"/" , "_") AS display_name, members.display_email, "noReply@yixow.com" AS hidden_email, groups.deleted, groups.id AS groupId FROM members ' . $join;
		
		$sql_first = $q;
		
		// specify: second query prefix part for total JOIN record count
		$sql_second = 'SELECT COUNT(DISTINCT members.id) FROM members ' . $join2;
		// keep hidden email out of the query
			$xtraWhere = ' members.display_email = 1  ';

		// from here: do not change

		$out = DataTables::getTableData( $r->all(), $columns, $sql_first , $sql_second, $groupBy /*, $xtraWhere*/ );
		//dd($out);
		$display = " style='display: none' ";
		if ($groupId == 0) $display = '';
		$i = 0;
		foreach ( $out['data'] as $d ) {
		//var_dump($d);
			$uid = GuestDbModel::mapUid($d[8],$d[9]);
			$luid = $uid . $d[13];
			//if ( $groupId != 0 && $groupId == $out['data'][$i][13] )
				//$display = " style='display: none' ";
			$out['data'][$i][0] = "<input id='mappedUid" . $luid . "' class='selectGuest mappedUid" . $luid . "' uid='" . $uid . "' type='checkbox' name='selectGuest' email='" . $d[1] . "' display_email='" . $d[10] . "'" . '>';
			if ( $d[10] == 0) {
				//$out['data'][$i][1] = $d[1] . '/' . $uid . ' (afgeschermd: ' . $d[8] . ',' . $d[11] . ')';
				$out['data'][$i][1] = $d[1] . '/' . $uid . ' (afgeschermd)';
			}
			$out['data'][$i][8] = $uid;
			$i++;
		}
		//$out['groupId'] = $groupId;
		return response()->json($out);
	}
}
