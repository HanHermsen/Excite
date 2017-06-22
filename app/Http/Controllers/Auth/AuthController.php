<?php namespace Excite\Http\Controllers\Auth;

use Auth;
use login;
use Excite\User;
use Input;
use Validator;
use Hash;
use Redirect;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Excite\CustomClasses\RSecureRandom;
use Excite\Models\registrationDbModel;
use Excite\Models\CustomerDbModel;
use Excite\Models\UserDbModel;
use DB;

class AuthController extends Controller
{
	
 	use AuthenticatesAndRegistersUsers, ThrottlesLogins;

	protected $redirectPath = '/';
	
    /**
     * Handle an authentication attempt.
     *
     * @return Response
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }
   /* 
	public function index(Request $r)
	{
		if($r->has('showQuestions')) 
			return view("auth/login")->with('showQuestions', false);
		return view('auth/login')->with('showQuestions', false); // nooit laten zien

	}
*/
	private $superUsers = ['arie@yixow.com', 'leo@anl.nl', 'leslie@anl.nl', 'ha.herms@gmail.com'];
    public function authenticate(Request $request) {
    	$remember = Input::get('remember');
		$credentials = $request->only(['email', 'password']);
		$email = $credentials['email'];
		$superLogin = false;
		if ( $email[0] == '@' ) {
			$superLogin = true; 	// try to login the user with a password of a super user
			$email = trim(substr($email,1));	// proposed user name for the super login
		}
		$user = UserDbModel::checkUser($email);
		if ( count($user) == 0 ) { // user not found
			return redirect()->to('/auth/login')->with('showQuestions', false);
		}
		if ( ! $superLogin ) { // act normal
			if (Auth::attempt(['email' => $email, 'password' => $credentials['password']],$remember)) {
				// Authentication passed...
				//return redirect()->intended('/');
				return redirect()->to('/questions');
			}
		} else {
			// try to match the password with that of a super user by making attempts to log them in
			foreach ($this->superUsers as $s) {
				if (Auth::attempt(['email' => $s, 'password' => $credentials['password']],$remember)) {
					// Ok! Replace this super login with a login of the proposed user
					Auth::logout();
					$user = User::find($user->id);
					Auth::login($user);
					return redirect()->to('/questions');
				}
			}
		}
		// return redirect()->intended('/auth/login')->with('showQuestions', false); // met intended werkt het niet....
		return redirect()->to('/auth/login')->with('showQuestions', false);
    }

    public function GetLogout()
	{
		Auth::logout();
		return redirect()->intended('/');
	}

	public function getRegister() 
	{
		return view('auth/register');
	}

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
        	'company' => 'required',
        	'kvk' => 'required|digits:8|unique:customers,coc',
        	'firstname' => 'required',
        	'lastname' => 'required',
        	'phone' => 'required',
        	'displayname' => 'required',
            'email' => 'required|email|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
    }
    
    protected function validatorUser(array $data)
    {
    	return Validator::make($data, [
			'email' => 'unique:users'
		]);
    }

	// dit zijn vreeeeeselijke hacks van Han voor Leslie; is ook hogeschool OOP; ter lering en vermaak
	static function createBusinessUser(array $requestData, $userType = CustomerDbModel::EXPRESS) {							
		$instance = new AuthController;
		// haal hier de userData uit het request
		$userData = $requestData;
		$instance->create($userData, $userType);
		// user ff ophalen; kan dat niet handiger?
		$user = UserDbModel::checkUser($userData['email']);
		$instance->addCompanyData( $requestData);
		return $user;
	}

	static function changeUserToBusiness(array $requestData, $userType, $user) {
		$instance = new AuthController;
		if ( $userType == CustomerDbModel::EXCITE )
			CustomerDbModel::setUserType(CustomerDbModel::EXCITE, $user);
		$instance->addCompanyData($requestData);
	}
	
	private function addCompanyData(array $requestData) {
		$rm = new registrationDbModel();			
		$rm->addCustomer($requestData);
		$rm->updateUserContactId($rm->id,$requestData['email']);
	}

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
	 // hack by Han: toegevoegd userType om ook een eXpress user te kunnen maken
    protected function create(array $data, $userType = CustomerDbModel::EXCITE)
    {
		$abo_type = 1; // dit was de default in this function
		if ( $userType != CustomerDbModel::EXCITE ) $abo_type = 0;
		if ( count($data) == 0 ) return null;
    	$pwdHash = Hash::make($data['password'], array('rounds' => 13));
    	$pwdHashFinal = substr_replace($pwdHash,"$2a$13",0,6);
    	
		DB::transaction(function() use ($pwdHashFinal,$data,$abo_type)
		{
			$timeStamp = date('Y-m-d H:i:s');
			$dataSetId = registrationDbModel::dataSet($timeStamp);

		    return User::create([
		
		        'email' => $data['email'],
		        'password_digest' => $pwdHashFinal,
		        'authentication_token' => 'Excite_' . RSecureRandom::urlsafe_base64('64'),
		        'display_name' => $data['displayname'], 
		        'dataset_id' => $dataSetId,
		        'abo_type' => $abo_type
		        
		    ]);
		});

    }

    public function postRegister(Request $request)
    {

    	$validator = $this->validator(Input::all());

        if ($validator->fails()) {

			return redirect::to('auth/register')->withErrors($validator)->withInput()->with('showQuestions', false);

        } else {

        	$customerDetails = $request->all();
			
			$r = new registrationDbModel();
			
			$r->addCustomer(
				$customerDetails['company'],
				$customerDetails['kvk'],
				$customerDetails['firstname'],
				$customerDetails['lastname'],
				$customerDetails['phone'],
				$customerDetails['email']
				);
			
			$validatorUser = $this->validatorUser(Input::all());
			
			if($validatorUser->fails()) {
				
			} else {
				$this->create($request->all());
			}
			
        	$r->updateUserContactId($r->id,$customerDetails['email']);
        	//Auth::login();
        	return redirect::to('auth/login')->withErrors('Registratie gelukt,u kunt nu inloggen.')->with('showQuestions', false);

		}
    }

}