<?php

namespace Excite\Http\Controllers\Yixow;

use Illuminate\Routing\Controller as BaseController;
use DB;
//use Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YixowController extends BaseController {
	private $tSelect = '';
	
	public function questions(Request $r) {
		$out['ok'] = true;
		$out['public'] = $this->getQuestions();
		//$out['session'] = $r->session()->all();
		return response()->json($out);
	}
	public function yixow_ion () {
	}
	public function groups(Request $r) {
		$qTab = $r->input('qTab', '1');
		if ( $qTab == '1' ) {
			//header("Access-Control-Allow-Origin: *");
			return view('yixow-onse/groups')->with('toolbarLabel', 'Groepen');
		}
		return view('yixow/groups' . $this->tSelect );
	}

	public function groupsOffered(Request $r) {
		$qTab = $r->input('qTab', '1');
		if ( $qTab == '1' ) {
			//header("Access-Control-Allow-Origin: *");
			return view('yixow-onse/groupsOffered')->with('toolbarLabel', 'Groepen aanbod');
		}
		return view('yixow/groupsOffered' . $this->tSelect );
	}

	public function chkAuth (Request $r) {
		$out['user'] = Auth::user();
		$out['reqUser'] = $r->user();		
		$out['session'] = $r->session()->all();
		header("Access-Control-Allow-Origin: http://localhost:8100");
		header("Access-Control-Allow-Credentials: true");
		return response()->json($out);
	}
	
	public function login(Request $request)
    {

    	//$remember = Input::get('remember');
		$remember = true;
		$credentials = $request->only(['user', 'passwd']);
        if (Auth::attempt(['email' => $credentials['user'], 'password' => $credentials['passwd']], $remember)) {
			// Authentication passed...
			$out['ok'] = true;
			//$out['user'] = Auth::user();
        } else {
			$out['ok'] = false;
			$out['user'] = null;

        }
		//$out['session'] = $request->session()->all();
		//$out['req'] = $request->all();
		header("Access-Control-Expose-Headers: X-AUTHENTICATION-TOKEN");
		if ($out['ok'] )
			header("X-AUTHENTICATION-TOKEN: " . Auth::user()->authentication_token);
		return response()->json($out);
    }

	private function getQuestions($groupId = 0) {
		$q =  'SELECT id, question, image, TIMESTAMPDIFF(MINUTE,created_at, NOW()) as ago, created_at FROM questions ';
		if ( $groupId > 0 ) {
			$q .= 'WHERE group_id = ?  AND questions.deleted = 0 AND inappropriate < 8 ';
			$q .= 'ORDER BY created_at DESC ';
			//$q .= 'LIMIT 6';
			$result = DB::select( $q, [$groupId] );

		} else {
			$q .= 'WHERE group_id IS NULL  AND questions.deleted = 0 ';
			$q .= 'ORDER BY created_at DESC ';
			//$q .= 'LIMIT 100';
			$result = DB::select( $q );
		}

		foreach ( $result as $r) {
			$q  = 'SELECT categories.name AS name FROM categories_questions ';
			$q .= 'JOIN categories ON categories_questions.category_id = categories.id ';
			$q .= 'WHERE question_id = ? ';
			$r->catNames = DB::select( $q, [$r->id]);
			$suffix = ' minuten geleden';
			$tmp = $r->ago;
			if ( $tmp <= 1 ) {
				$suffix = ' minuut geleden';
				$tmp = 1;
			}
			else {
				if ($tmp >= 60 ) {
					$tmp = ceil($r->ago / 60);
					$suffix = " uur geleden";
					if ( $tmp >= 24 ) {
						$suffix = ' dagen geleden';
						$tmp = floor($tmp / 24);
						if ($tmp == 1) $suffix = " dag geleden";
						if ($tmp > 7 ) {
							$tmp = '';
							$suffix = $r->created_at;
						}
					}
				}
			}
			$r->ago = $tmp . $suffix;
			// het root path voor een file is ......./Excite/app; zie config/filesystems.php
			if ( $r->image != null && ! file_exists ( '../public/api/api/images/' . $r->image ) ) {
				// fix db shit
				$r->image = null;
			}
			if ( $r->image != null )
				$r->image = 'https://yixow.com/api/api/images/' . $r->image;

		}
		return $result;	
	}
	
}