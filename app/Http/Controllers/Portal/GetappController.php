<?php

namespace Excite\Http\Controllers\Portal;

use Excite\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use Jenssegers\Agent\Agent;

class GetappController extends Controller {

	/*
    |--------------------------------------------------------------------------
    | Getapp Controller
    |--------------------------------------------------------------------------
    */
    
    protected $playStorePath = 'https://play.google.com/store/apps/details?id=nl.montanamedia.yixow&amp;hl=nl';
    protected $appStorePath = 'https://itunes.apple.com/nl/app/yixow-open-in-opinie.-zelf/id999394569?mt=8';

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

	public function index(Request $r) {
		
		$agent = new Agent();
	    if($agent->isAndroidOS()) {
		    // redirect to Play Store
		    return redirect($this->playStorePath);
	    }
	    else if($agent->isiOS()) {
		    // redirect to App Store
		    return redirect($this->appStorePath);
	    }
		else {
			return view('portal/pages/getapp', ['title' => 'App Downloaden']);
		}
	}
}