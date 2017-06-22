<?php

namespace Excite\Http\Controllers\Portal;

use Illuminate\Routing\Controller as BaseController;
use DB;
use Request;
use Excite\Models\questionDbModel;

class SubscriptionsController extends BaseController {

	public function index(Request $r) {
		$questions = questionDbModel::getRecentQuestions();
		return view('portal/pages/subscriptions', ['title' => 'Abonnementen', 'questions' => $questions]);
	}
}