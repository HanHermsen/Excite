<?php

namespace Excite\Http\Controllers\Portal;

use Illuminate\Routing\Controller as BaseController;
use DB;
use Request;
use Excite\Models\questionDbModel;

class TermsController extends BaseController {

	public function index(Request $r) {
		$questions = questionDbModel::getRecentQuestions();
		return view('portal/pages/terms', ['title' => 'Voorwaarden', 'questions' => $questions]);
	}
}