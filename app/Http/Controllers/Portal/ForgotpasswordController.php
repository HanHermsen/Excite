<?php

namespace Excite\Http\Controllers\Portal;

use Excite\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use Redirect;
use Hash;
use DB;

class ForgotpasswordController extends Controller {

	/*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

	protected $redirectPath = '/';

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

	public function index(Request $r) {
		return view('portal/pages/forgotpassword', ['title' => 'Wachtwoord vergeten', 'showForm' => true, 'message' => '']);
	}
	
	public function indexPost(Request $r) {
		
		$this->validate($r, ['email' => 'required|email']);

        $response = Password::sendResetLink($r->only('email'), function (Message $message) {
            $message->subject('Your Password Reset Link');
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
            	return view('portal/pages/forgotpassword', ['title' => 'Wachtwoord vergeten', 'showForm' => false, 'message' => trans($response)]);
                return redirect::back()->withErrors(['status' => trans($response)]);

            case Password::INVALID_USER:
            	return view('portal/pages/forgotpassword', ['title' => 'Wachtwoord vergeten', 'showForm' => true, 'message' => trans($response)]);
        }		
		
	}
	
	//public function getReset(Request $r) {
	//	return view('portal/pages/resetpassword', ['title' => 'Wachtwoord vergeten', 'showForm' => true, 'message' => '']);
	//}
	
	public function getReset($token = null)
	{
	    if (is_null($token))
	    {
	        throw new NotFoundHttpException;  
	    }
		
	    return view('portal/pages/resetpassword', ['title' => 'Wachtwoord vergeten', 'showForm' => true, 'message' => '', 'token' => $token]);
	}
	
	protected function resetPassword($user, $password)
    {
   		$pwdHash = Hash::make($password, array('rounds' => 13));
    	$pwdHashFinal = substr_replace($pwdHash,"$2a$13",0,6);

        $user->password_digest = $pwdHashFinal;

        $user->save();

        Auth::login($user);

    }
}