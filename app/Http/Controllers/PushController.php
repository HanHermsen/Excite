<?php

namespace Excite\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Excite\Http\Controllers\Controller;
use Excite\CustomClasses\PushHelpers;
use Illuminate\Http\Request;

use DB;
use Input;
use Auth;
use Config;

class PushController extends BaseController {
	

	public function index() {
		if(Auth::user()->email == 'leo@anl.nl') {			
			return view('push');
		}
	}
	
	public function push(Request $r) {
		// hack wat validiteit tests erbij & geef de pagina terug; is nieuw t.o.v oorspronkelijke yixow.com/test
		$err = '';
		$pushId = $r->get('pushId', 0 );
		$pushMsg = $r->get('pushMsg', '');
		$pushMsg = trim($pushMsg);
		if ($pushId == 0 )
			$err = 'groupId niet goed';
		else {
			$pushId = intval($pushId);
			if ($pushId <= 0 ) $err = 'groupId ' . $pushId . ' niet ok';
		}
		if ($pushMsg == '' )
			if ($err == '')
				$err = 'Leeg bericht';
			else
				$err .= '; Leeg bericht';
		if ( $err != '' )
			$err .= '; Probeer nog eens';

		if ( $err == '' ) {	
			if(Auth::user()->email == 'leo@anl.nl') {
				config(['userId' => Auth::user()->id]);
				$msg = PushHelpers::sendNewGroupQuestionNotification($pushId,$pushMsg);
				if ( $msg != '' )
					$err = $msg;
				else
					$err = 'Gedaan';				
			} else
				$err = 'Illegal User';
		}
		if ( $err == 'Gedaan' ) {
			$err = 'Gedaan voor group ' . $pushId; // wordt weliswaar als 'error' doorgegeven, maar is ok
			// terug naar de pagina zonder pushId/groupId; voor de veiligheid
			return redirect()->back()->withErrors($err)->withInput(Input::except('pushId'));
		}
		// terug naar de pagina met alle originele inputs; er ging echt iets mis
		return redirect()->back()->withInput()->withErrors($err);
	}

}