<?php

namespace Excite\Http\Controllers\Portal;

use Illuminate\Routing\Controller as BaseController;
use DB;
use Request;

class OrderController extends BaseController {

	public function indexExpress(Request $r) {
		return view('portal/pages/order', ['title' => 'Bestellen', 'name' => 'eXpress', 'type' => 'express']);
	}
	
	public function indexExcite(Request $r) {
		return view('portal/pages/order', ['title' => 'Bestellen', 'name' => 'eXcite', 'type' => 'excite']);
	}
}