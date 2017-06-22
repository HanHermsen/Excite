<?php

namespace Excite\Http\Controllers\Portal;

use Illuminate\Routing\Controller as BaseController;
use DB;
use Request;
use Excite\Models\questionDbModel;

class HomeController extends BaseController {

	public function index(Request $r) {
		$questions = questionDbModel::getRecentQuestions();
		return view('portal/pages/index', ['title' => 'Wat jij vindt - in de samenleving, in de organisaties en in de markt', 'questions' => $questions]);
	}
}