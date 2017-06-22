<?php

namespace Excite\Http\Controllers\Groups;

use Illuminate\Routing\Controller;
//use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Http\Request;

use Excite\CustomClasses\DataTables;
use Excite\Models\CustomerDbModel;

use Auth;

class GroupTableController extends Controller {

	public function getTableData(Request $r) {
	
		// get some from the environment when needed; f.i user id from login when needed in db query
		$userId = Auth::user()->id;
		$userType = CustomerDbModel::getUserType();
		// tmp for test
		//$userId = 16;

		// specify: give db => fieldname  dt => column number in table tName => table name of the field
		$columns = array(
			array( 'db' => 'name', 'dt' => 0 , 'tName' => 'groups' ),
			array( 'db' => 'created_at_date', 'dt' => 1 , 'tName' => '' ),
			array( 'db' => 'memberCnt', 'dt' => 2 , 'tName' => '' ),
			array( 'db' => 'questionCnt', 'dt' => 3 , 'tName' => '' ),
			// 4 is percentage wordt berekend on Client
			array( 'db' => 'responseCnt', 'dt' => 5 , 'tName' => '' ),
			array( 'db' => 'date_expired', 'dt' => 6 , 'tName' => '' ),
			array( 'db' => 'groupType', 'dt' => 7, 'tname' => '' ),
			array( 'db' => 'deleted', 'dt' => 8, 'tname' => 'groups' )
		);
	
		// specify: the JOIN part of the query when more Tables are involved; can be ''
		

		$join = ' LEFT JOIN members ON members.group_id = groups.id LEFT JOIN questions ON questions.group_id = groups.id AND questions.deleted = 0 ';
		$tmp = '';
		if ($userType != CustomerDbModel::EXTRA) {
			$tmp = ' AND (groups.customer_contract_id > 0) ';
		}
		$tmp = ''; // toch maar niet
		$where = '  groups.user_del = 0 AND groups.user_id = ' . $userId . $tmp;

		$groupBy = 'GROUP BY groups.id';

		// specify: first query prefix part for retrieval of the data

		// de responseCount moet hier uit de group komen!!!!!!!!!!!! = cnt van alle antwoorden op alle vragen in de groep
		$sql_first = 'SELECT SQL_CALC_FOUND_ROWS groups.name, DATE(groups.created_at) AS created_at_date, COUNT(DISTINCT members.id) memberCnt, COUNT( DISTINCT questions.id) AS questionCnt, groups.response AS responseCnt, DATE(groups.date_expired) AS date_expired, groups.customer_contract_id AS groupType, groups.deleted FROM groups ' . $join;
		// specify: second query prefix part for total record count
		$sql_second = 'SELECT COUNT(groups.id) FROM groups ';
		// do not change
		$out = DataTables::getTableData( $r->all(), $columns, $sql_first , $sql_second, $groupBy, $where );
		return response()->json($out);
	}
}
