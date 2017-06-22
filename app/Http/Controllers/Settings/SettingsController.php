<?php

namespace Excite\Http\Controllers\Settings;

use Redirect;
use Excite\Http\Controllers\Controller;
use Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Input;
use Excite\Models\CustomerDbModel;
use DB;
use Hash;

class SettingsController extends Controller
{
 
    public function index()
    {
    	$viewSettings = CustomerDbModel::viewSettings();
		return view('settings.index_settings')->with(['viewSettings' => $viewSettings]);
    }

	public function change(Request $request) {
		
		$validator = Validator::make($request->all(),[
			'company' => 'max:255',
        	'kvk' => 'digits:8',
        	'firstname' => '',
        	'lastname' => '',
        	'phone' => '',
        	'displayname' => '',
            'email' => 'email|max:255',
		]);

		if($validator->fails()) { 
			return back()->withInput()->withErrors($validator->errors());
		}

		try {
			CustomerDbModel::changeSettings(
				//$request->company,
				//$request->kvk,
				$request->firstname,
				$request->lastname,
				$request->phone,
				$request->displayname,
				$request->email
				);
	
			return redirect('/settings')->withErrors('Uw gegevens zijn aangepast.');
		}
		catch(Exception $e)
		{
			echo 'Something went wrong...';
		}

	}
	
	public function changePwd(Request $request) {
		
		$password = Input::get('password');
		
		$validator = Validator::make($request->all(),[
            'password' => 'required|confirmed|min:6'
		]);

		if($validator->fails()) { 
			return back()->withInput()->withErrors($validator->errors());
		}
		
		$pwdHash = Hash::make($password, array('rounds' => 13));
    	$pwdHashFinal = substr_replace($pwdHash,"$2a$13",0,6);
    	
    	CustomerDbModel::changePwd($pwdHashFinal);
    	
    	return back()->withInput()->withErrors(trans('messages.ChangePasswordNotification'));
		
	}

}