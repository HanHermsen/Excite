<?php

namespace Excite\Http\Controllers\Questions;

use Illuminate\Routing\Controller;

use Illuminate\Http\Request;

use Excite\CustomClasses\DataTables;
use Excite\Models\CustomerDbModel;
use Auth;
use DB;

class QTableController extends Controller {
	const QUESTION = 1;
	const MEMBER_CNT = 5;
	const QUESTION_ID = 9;
	const GROUP_UID = 10;
	const GROUP_ID = 12;
	const QUESTION_INAPPROP = 16;

	// Called from: public/js/questions.js with url /questions/getTableData
	public function getTableData(Request $r) {
	
		$userType = CustomerDbModel::getUserType();
		$xtraWhere = null;
		$activeGroupsOnly = ' AND groups.deleted = 0 ';
		
		// get some from the environment when needed; f.i user id from login when needed in db query
		$userId = Auth::user()->id;
		$contactId = $r->user()->contact_id;

		$groupId = 0;
		if ($r->has('groupId'))
			$groupId = $r->input('groupId');
		$onlyThisGid = '';
		if ($groupId > 0) {
			$activeGroupsOnly = '';
			$onlyThisGid = ' groups.id = ' . $groupId . ' ';
		}

		// specify: give db => fieldname  dt => column number in table tName => table name of the field
		// when tname = '' its a variable name
		$columns = array(
			// 0 is markering voor Gast vragen
			// 1 question
			array( 'db' => 'question', 'dt' => 1 , 'tName' => 'questions' ),
			// 2 Stats Button
			// 3 Date in
			array( 'db' => 'startDate', 'dt' => 3 , 'tName' => '' ),
			// 4 group name
			array( 'db' => 'name', 'dt' => 4 , 'tName' => 'groups' ),
			// 5 memberCnt
			array( 'db' => 'memberCnt', 'dt' => 5 , 'tName' => '' ),
			// 6 percentage is calculated on client from 8
			// 7 Date out
			array( 'db' => 'endDate', 'dt' => 7 , 'tName' => '' ),
			// 8 answerCnt invisible; used by 6
			array( 'db' => 'answerCnt', 'dt' => 8 , 'tName' => '' ),
			// Invisible column for question id; when Stats Button Clicked this Id is used
			array( 'db' => 'id', 'dt' => 9 , 'tName' => 'questions' ),
			// Invisible column for group user id;
			array( 'db' => 'gUid', 'dt' => 10 , 'tName' => '' ),
			// Invisble column for question user id
			array( 'db' => 'qUid', 'dt' => 11 , 'tName' => '' ),
			array( 'db' => 'group_id', 'dt' => self::GROUP_ID , 'tName' => 'questions' ),
			array( 'db' => 'createdAt', 'dt' => 13 , 'tName' => '' ),
			array( 'db' => 'questionDeleted', 'dt' => 14, 'tName' => '' ),
			array( 'db' => 'groupDeleted', 'dt' => 15, 'tName' => '' ),
			array( 'db' => 'qInapprop', 'dt' => self::QUESTION_INAPPROP, 'tName' => '' ),
		);
		if ( $groupId >= 0 &&  $userType != CustomerDbModel::EXTRA ) { // this is stable for zak gebruikers
			// specify: the JOIN part of the query when more Tables are involved; can be ''
			if ($userType != CustomerDbModel::EXTRA)
				$activeGroupsOnly .= ' AND (groups.customer_contract_id > 0) ';
			
			$join = 'JOIN groups ON groups.user_id = ' . $userId . ' AND groups.id = questions.group_id ' . $activeGroupsOnly . ' LEFT JOIN members ON members.group_id = groups.id ';
			//$join = 'JOIN groups ON groups.user_id = ' . $userId . ' AND groups.id = questions.group_id LEFT JOIN members ON members.group_id = groups.id ';
			$groupBy = "GROUP BY questions.id";

			// specify: the second JOIN must be less restrictive.... otherwise the total record COUNT is not right... break the above JOIN from right to left down to test it
			//$join2 = 'JOIN groups ON groups.user_id = ' . $userId . ' AND groups.id = questions.group_id AND groups.deleted = 0';
			$join2 = 'JOIN groups ON groups.user_id = ' . $userId . ' AND groups.id = questions.group_id' . $activeGroupsOnly;
			$xtraWhere = ' questions.user_del = 0 ';
			if ( $onlyThisGid != '') {
				//$xtraWhere = ' questions.deleted = 0 ' . 'AND ' . $onlyThisGid;
				$xtraWhere .= ' AND ' . $onlyThisGid;
			}
			// specify: first query prefix part for retrieval of the data; don't forget the SQL_CALC_FOUND_ROWS parameter, since
			// after this query the query 'SELECT FOUND_ROWS()' will be done automatically by DataTables
			// for getting the count of the records in the last SELECT
			
		} else { // public space; groups are LEFT joined here instead of JIONed gives NULL group too
			// specify: the JOIN part of the query when more Tables are involved; can be '' 
			if ( $groupId >= 0 ) {
				$join = 'LEFT JOIN groups ON groups.id = questions.group_id AND groups.deleted = 0  LEFT JOIN members ON members.group_id = groups.id ';
				$groupBy = "GROUP BY questions.id";
				//$xtraWhere = 'questions.user_id = ' . $userId .  ' AND questions.deleted = 0 ' . $onlyThisGid;
				// LET OP user mag ook _alle_ vragen bekijken in group van haarzelf; ook die van Gasten in die groep
				// voor de rest alleen eigen vragen evt gesteld in groep van iemand anders
				// deze where does the job
				if ( $onlyThisGid != '' )
					$onlyThisGid = 'AND ' . $onlyThisGid;
				$xtraWhere = '(groups.user_id = ' . $userId . ' OR questions.user_id = ' . $userId .  ') AND questions.user_del = 0 ' . $onlyThisGid;
				// specify: the second JOIN is just for counting; exclude deleted groups
				$join2 = 'LEFT JOIN groups ON groups.id = questions.group_id AND groups.deleted = 0';
			} else { // NEW groupId -1 'Vragen buiten mijn Zakelijke Groepen'
				$join = 'LEFT JOIN groups ON groups.id = questions.group_id  LEFT JOIN members ON members.group_id = groups.id ';
				$groupBy = " GROUP BY questions.id ";
				//$xtraWhere = 'questions.user_id = ' . $userId .  ' AND questions.deleted = 0 ' . $onlyThisGid;
				// LET OP user mag ook _alle_ vragen bekijken in group van haarzelf; ook die van Gasten in die groep
				// voor de rest alleen eigen vragen evt gesteld in groep van iemand anders
				// deze where does the job
				//$xtraWhere = ' questions.user_id= ' . $userId . ' AND ( groups.user_id<> ' . $userId .  ' OR questions.group_id is null) ' ;
				//$x  = ' questions.user_id = ' . $userId . ' AND NOT ';
				$x  = ' (questions.user_id = ' . $userId . ' OR groups.user_id = ' . $userId . ') AND NOT ';
				$x .= ' ( NOT groups.id IS NULL AND groups.user_id = '  . $userId . '  AND  (groups.customer_contract_id > 0 )) ';
				$x .= ' AND questions.user_del = 0  AND groups.user_del = 0 ';
				
				$xtraWhere = $x;
				// specify: the second JOIN is just for counting; exclude deleted groups
				$join2 = 'LEFT JOIN groups ON groups.id = questions.group_id AND groups.deleted = 0';
			}
			
		}

		$q = 'SELECT SQL_CALC_FOUND_ROWS questions.question, DATE(questions.created_at) AS createdAt , ';
		//$q .= 'DATE(questions.start_date) AS startDate , ';
		$q .= 'CASE WHEN (questions.start_date IS NULL) THEN  DATE(questions.created_at) ';
		$q .= 'ELSE DATE(questions.start_date) END AS startDate , ';
		$q .= 'groups.name, COUNT( DISTINCT members.id) AS memberCnt, questions.response AS answerCnt,';
		$q .= 'DATE(questions.end_date)  AS endDate , questions.id, questions.group_id, groups.user_id AS gUid,';
		$q .= 'questions.user_id AS qUid , questions.deleted AS questionDeleted, groups.deleted AS groupDeleted, ';
		$q .= 'questions.inappropriate AS qInapprop FROM questions ' . $join;
		$sql_first = $q;
		$sql_second = 'SELECT COUNT(questions.id) FROM questions ' . $join2;
		
		
		// default action: get the data
		$out = DataTables::getTableData( $r->all(), $columns, $sql_first , $sql_second, $groupBy, $xtraWhere);
		//dd($out);
		// gather some additional data and store it in addData
		$data = &$out['data'];
		//if ( $groupId >= 0 &&  $userType != CustomerDbModel::EXTRA ) {
		//if ( $groupId >= 0 ) {
			$addData = [];
			foreach ( $data as &$d ) {
				if ( (int)$d[self::QUESTION_INAPPROP] >= 8 )
					$d[self::QUESTION] = '[Ongepast!] ' . $d[self::QUESTION];
				$groupId = (int)$d[self::GROUP_ID];
				if ($groupId == 0) continue; // skip public question not in public group
				$questionId = $d[self::QUESTION_ID];
				$groupUid = $d[self::GROUP_UID];
				$q  = 'SELECT user_id, COUNT(user_id) AS cnt FROM answers ';
				$q .= 'WHERE question_id = ? AND ( user_id = ? OR user_id = ?) ';
				$q .= 'GROUP BY user_id ';
				$rs = DB::select($q,[$questionId, $groupUid, 565]);
				$eac = $oac = 0;
				foreach ( $rs as $r ) {
					if ( $r->user_id == 565 )
						$eac = $r->cnt;
					else
						$oac = $r->cnt;
				}
				$q  = 'SELECT COUNT(email) AS cnt FROM invitations ';
				$q .= 'WHERE group_id = ? AND accepted = 0 ';
				$rs = DB::select($q,[$groupId]);
				$addData[$questionId]['groupId'] = $groupId;
				$addData[$questionId]['invitationCnt'] = $rs[0]->cnt;
				$addData[$questionId]['emailAnswerCnt'] = $eac;
				$addData[$questionId]['ownerAnswerCnt'] = $oac;				
			}
			$out['addData'] = $addData;
		//}
		$out['userId'] = $userId;
		return response()->json($out);
	}

}
