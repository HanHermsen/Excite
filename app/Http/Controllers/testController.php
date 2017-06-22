<?php

namespace Excite\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Excite\Http\Controllers\Controller;
use Excite\CustomClasses\PushHelpers;
use Illuminate\Http\Request;

use DB;
use Mail;
use PDF;
use Auth;

class testController extends BaseController {
	

	public function index() {

		if(Auth::user()->email == 'leo@anl.nl') {
			
			return view('test');
			
		//PushHelpers::sendNewGroupQuestionNotification('5634634','text'));
		}
		

	}
	
	public function push(Request $r) {
	
		if(Auth::user()->email == 'leo@anl.nl') {
			
			PushHelpers::sendNewGroupQuestionNotification($r->pushId,$r->pushMsg);
			
		}
	}

}