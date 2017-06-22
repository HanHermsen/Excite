<?php

namespace Excite\Http\Controllers\Ego;

use Illuminate\Routing\Controller;
//use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Http\Request;

use Excite\CustomClasses\DataTables;

use Auth;

class ContractTableController extends Controller {

	public function getTableData(Request $r) {
	
		// get some from the environment when needed; f.i user id from login when needed in db query
		$userId = Auth::user()->id;
		// tmp for test
		//$userId = 16;

		// specify: give db => fieldname  dt => column number in table tName => table name of the field
		$columns = array(
			array( 'db' => 'name', 'dt' => 0 , 'tName' => 'groups' ),
			array( 'db' => 'express_label', 'dt' => 1 , 'tName' => 'groups' ),
			array( 'db' => 'zipcode', 'dt' => 2 , 'tName' => '' ),
			array( 'db' => 'radius', 'dt' => 3 , 'tName' => '' ),
			array( 'db' => 'period', 'dt' => 4 , 'tName' => '' ),
			array( 'db' => 'startDate', 'dt' => 5 , 'tName' => '' ),
			array( 'db' => 'endDate', 'dt' => 6 , 'tName' => '' )
		);
	
		// specify: the JOIN part of the query when more Tables are involved; can be ''

		$join = '  JOIN customer_contracts ON groups.customer_contract_id = customer_contracts.id ';
		//$join .= ' LEFT JOIN type_contract ON type_contract.id = customer_contracts.id_contract '
		// only in EXPRESS so test on group_display is ok here
		$where = ' groups.user_del = 0 AND groups.group_display = 1 AND groups.user_id = ' . $userId;

		//$groupBy = 'GROUP BY groups.id';
		$groupBy = '';

		// specify: first query prefix part for retrieval of the data

		$sql_first = 'SELECT SQL_CALC_FOUND_ROWS groups.name, groups.express_label, customer_contracts.properties AS zipcode, customer_contracts.radius AS radius, TIMESTAMPDIFF(MONTH,customer_contracts.date_in, customer_contracts.date_out) AS period, DATE(customer_contracts.date_in) AS startDate, DATE(customer_contracts.date_out) AS endDate FROM groups ' . $join;
		// specify: second query prefix part for total record count
		$sql_second = 'SELECT COUNT(groups.id) FROM groups ';
		// do not change
		$out = DataTables::getTableData( $r->all(), $columns, $sql_first , $sql_second, $groupBy, $where );
		return response()->json($out);
	}
}
