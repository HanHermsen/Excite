<?php

namespace Excite\Http\Controllers\Ego;

use Redirect;
use Excite\Http\Controllers\Controller;
use Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Excite\Models\CustomerDbModel;
use Excite\Models\UserDbModel;
use Excite\Models\groupDbModel;

use Excite\Http\Controllers\Auth\AuthController; 
use DB;
use Auth;
use Hash;
use Mail;

class EgoController extends Controller
{

/* Imported stuff from GroupController */
/* private functions */
	private function getPcRange ($getZipCode, $incrementArea ) {
		$range = [];
		$pcSelect = CustomerDbModel::getPc($getZipCode,$incrementArea);
		foreach($pcSelect as $res) {
			$firstPc = str_replace($getZipCode,'',substr($res->pc,0,4));
			$secondPc = str_replace($getZipCode,'',substr($res->pc,4,4));

			$range[] = (!empty($firstPc) ? $firstPc .',' : '') . (!empty($secondPc) ? $secondPc . ',' : '');
		}
		$pcRange = $getZipCode . ',' . rtrim((implode('',$range)),',');
		return $pcRange;
	}
	private function makeExpressContract($r, $user) {
		$uid = $user->id;
		$userEmail = $user->email;
		$radius = $r->get('area');
		$getPopulation = $r->get('hiddenPopulationVal');
		if ( $radius == 0 ) {
			$lat = 0;
			$lng = 0;
			$getZipCode = '';
			$pcRange = '';
		} else {
			$lat = $r->get('lat');
			$lng = $r->get('lng');
			// Postcode ophalen
			$getZipCode = $r->get('hiddenZipCode');
			// Postcode selecteren op basis van Lat/Lng
			$incrementArea = $r->get('area') + 1; 

			$pcRange = $this->getPcRange ($getZipCode, $incrementArea );
		}

		$result = CustomerDbModel::getCustomerId($userEmail);
		$customerId = $result->id;
		$customerIdCustomer = $result->id_customer;
		$contractId = CustomerDbModel::getPriceContractId($r->get('area'),$r->get('period'))->id;
		
		// Current date time
		$timestamp = date('Y-m-d H:i:s');
		// Eind datum berekenen op basis van Period		
		$endDate = strtotime ( '+' . $r->get('period') . 'month' , strtotime ( $timestamp ) ) ;
		$newEndDate = date ( 'Y-m-d H:i:s' , $endDate );
		

		// Insert customer_contract
		$argsContract = ([
			'id_customer' => $customerIdCustomer,
			'id_contract' => $contractId,
			'id_customer_contact' => $customerId,
			'date_in' => $timestamp,
			'date_out' => $newEndDate,
			'anualcost' => $r->get('price'),
			'properties' => $getZipCode,
			'range' => $pcRange,
			'population' => $getPopulation,
			'radius' => $radius,
			'latitude' => $lat,
			'longitude'=> $lng,
		]);		
		
		// Insert group
		$argsGroup = ([
			'user_id' => $uid,
			'name' => $r->get('GroupName'),
			'express_label' => $r->get('LabelName'),
			'created_at' => $timestamp,
			'updated_at' => $timestamp,
			'group_display' => '1',
			'range' => $pcRange,
			'date_expired' => $newEndDate,
		]);

		CustomerDbModel::storeContract($argsContract,$argsGroup);
		
		$firstname = CustomerDbModel::getFirstname($userEmail);
		if ( $_SERVER['HTTP_HOST'] != 'excite.app' ) { // dit hoeft Han niet
			Mail::send('emails.excite_order',['Firstname' => $firstname] , function($message) use ($userEmail)  {
				$message->to($userEmail, null)->subject('Yixow Bestelling');	
			});
		}
	}
	
	private function makeExciteTrial($user) {
		$email = $user->email;
		$userId = $user->id;
		$errMsg = '';
		try {
			$result = CustomerDbModel::getCustomerId($email);
			$customerId = $result->id;
			$customerIdCustomer = $result->id_customer;
			// Current date time
			$timestamp = date('Y-m-d H:i:s');
			// Eind datum berekenen op basis van Period		
			$endDate = strtotime ( '+1 month' , strtotime ( $timestamp ) ) ;
			$newEndDate = date ( 'Y-m-d H:i:s' , $endDate );		
			
			// Insert customer_contract
			$argsContract = ([
				'id_customer' => $customerIdCustomer,
				'id_contract' => '13',
				'id_customer_contact' => $customerId,
				'date_in' => $timestamp,
				'date_out' => $newEndDate,
				'anualcost' => '0',
			]);

			CustomerDbModel::updateGroupsTrial($customerIdCustomer,$argsContract, $userId);
			CustomerDbModel::setUserType(CustomerDbModel::EXCITE, $user);
			
			$userEmail = $email;
			$firstname = CustomerDbModel::getFirstname($userEmail);
			if ( $_SERVER['HTTP_HOST'] != 'excite.app' ) { // dit hoeft Han niet
				Mail::send('emails.trial_order',['Firstname' => $firstname] , function($message) use ($userEmail)  {
					$message->to($userEmail, null)->subject('Yixow Bestelling');	
				});
			}
		
		} catch ( Exception $e ) {
			$errMsg = 'Er ging iets overwachts fout; probeer het later nog eens';
		}
		return $errMsg;
			
	}

	/* public functions */

	//show initial page
	public function ego(Request $r) {
		return view('groups.ego_groups');
	}

	// ajax post of the ego form by express.js
	public function storeContract(Request $r) {
		//dd($r->all());
		
		$validator = Validator::make($r->all(),[

			'GroupName' => 'required|max:72|unique:groups,name,' . Auth::user()->id,
			'LabelName' => 'required|max:72',
			'area' => 'required|numeric|in:0,5,10,25',
			'zipCode' => '',
			'period' => 'required|numeric|in:1,2,3,4,5,6,7,8,9,12',
			'price' => 'required|numeric',
			'lat' => 'required',
			'lng' => 'required'

		]);
		// Validate check
		if($validator->fails()) { 
				return response()->json(['error' => true, 'msg' => "Validator fails."]);
				//return back()->withInput()->withErrors($validator->errors());
		}
		$this->makeExpressContract($r, Auth::user());
		return response()->json(['error' => false, 'msg' => "Bestelling ontvangen, er komt een bevestiging per Email."]);
	}
	
	// ajax request from exciteShared.js after click on link
	public function exciteTrial() {
		$errMsg = $this->makeExciteTrial(Auth::user());
		if ( $errMsg != '' )
			return response()->json(['error' => true, 'msg' => $errMsg]);
		else
			return response()->json(['error' => false, 'msg' => 'Proefabonnement gaat meteen in; eXcite kan nu gebruikt worden']);
	}

	// ajax request; geef de prijslijst en inwoneraantal NL
	public function getPrice(Request $r) {
		$out['priceList'] = CustomerDbModel::getPriceList();
		$out['populationNl'] = 17000000;
		return response()->json($out);
	}
	// ajax request
	public function getZipCode(Request $r) {
	//dd($r->all());
		$zipCode = $out['zipCode'] = CustomerDbModel::getZipCode($r->get('lat'),$r->get('lng'));
		
		$getPopulation = 0;
		$pcRange = '';
		if ( $zipCode != '' ) {
		
			$incrementArea = $r->get('area') + 1; 
			$pcRange = $this->getPcRange ($zipCode, $incrementArea );
			// Get Population
			$getPopulation = CustomerDbModel::getPopulation($pcRange);
		}
		$out['population'] = $getPopulation;
		$out['range'] = $pcRange;
		return response()->json($out);
	}

	// ajax request: geef lat/lng van een Postcode 4 cijfers
	public function getLatLng (Request $r) {
		$out = CustomerDbModel::getLatLng($r->get('zipCode'));
		return response()->json($out);
	}
	// ajax request
	public function getGroupNames() {
		$uType = CustomerDbModel::getUserType();
		$out = groupDbModel::getGroupNames(Auth::user()->id,$uType);
		return response()->json($out);
	}
	// NEW ajax call; kan worden opgezocht in db
	public function isValidKvk(Request $r) {
		$kvk = $r['kvk'];
		if(!empty(CustomerDbModel::isValidKvk($kvk))) {
			$isValidKvk = true;
		} else {
			$isValidKvk = false;
		}
		//dd($isValidKvk);
		return response()->json(['result' => $isValidKvk]);
	}
	
	public function isValidDomain(Request $r) {
		$dom = $r['dom'];
		$val = gethostbyname($dom);
		return response()->json(['result' => $val]);
	}

/* stuff van/voor Leslie */

	protected function validator(array $data) {
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
	// prepare debug info for the response dialog
	private function debugVar($var) {
		$x = print_r($var, true);
		return ['error' => true, 'msg' => $x];
	}
	// ajax post eXpress or eXcite order form Portal
	public function portalOrder(Request $r) {
		//return response()->json($this->debugVar($r->all()));
		
		// eXcite proefabonnement
		$orderType = CustomerDbModel::EXCITE;
		if ( isset($r['hiddenZipCode']) ) {
			// arbitrary test for eXpress order; ontbreekt in het eXcite order Form
			$orderType = CustomerDbModel::EXPRESS;
		}

		// er is verschillende validatie nodig voor de twee orderTypes!!
		// (1) EXPRESS only: variabelen voor het eXpress contract (lat, lng, ZipCode etc); er is al een validator in storeContract()
		// die kun je als private expressValidator in deze Class opnemen en die dan daar en hier gebruiken! Zoals je hieronder ook doet
		// met validator.
		// ik zou die hieronder voor de helderheid dan companyValidator noemen
		// (2) EXCITE _en_ EXPRESS: variabelen voor de klantregistratie (company, kvk etc); 
		$validator = $this->validator($r->all());

        $messages = $validator->messages();
		$messageValidator = array();
		
        foreach ($messages->all('<li>:message</li>') as $message)
        {
            $messageValidator[] = $message;
        }
		
		if(!empty($messages->all())) {
	
			return response()->json(['error' => true, 'msg' => $messageValidator]);
		
		}

		$userEmail = trim($r->get('email'));
		$userPwd = trim($r->get('password'));
		
		$user = UserDbModel::checkUser($userEmail);
				//return response()->json($this->debugVar($user));
		if(!empty($user)) {

			// User bestaat
			$hashedPassword = $user->password_digest;
			
			if (Hash::check($userPwd, $hashedPassword))	{
				// The passwords match...
				$userType = CustomerDbModel::getuserType($user);
					//return response()->json($this->debugVar($userType));
				switch($userType) {
					case CustomerDbModel::LIGHT:
						// change user
						$userType = $orderType;
						AuthController::changeUserToBusiness($r->all(), $userType, $user);

						break;
					case CustomerDbModel::EXPRESS:
						if ( $orderType == CustomerDbModel::EXCITE ) { // expressTrial promoveer gebruiker alvast
							$userType = $orderType;
							CustomerDbModel::setUserType(CustomerDbModel::EXCITE, $user);
						}
						break;
					case CustomerDbModel::EXCITE:
						$msg = "Een <b>eXcite</b> gebruiker kan geen <b>eXpress</b> groep bestellen";
						if ($orderType == CustomerDbModel::EXCITE )
							$msg = "Een <b>eXcite</b> gebruiker kan geen proefabonnement bestellen";
						return response()->json(['error' => true, 'msg' => $msg]);
						break;
					// ERROR	
					default:
						return response()->json(['error' => true, 'msg' => "Onverwachte fout"]);						
				}
			} else {
				return response()->json(['error' => true, 'msg' => "gebruikersnaam en/of wachtwoord onjuist"]);
			}

		} else {
			// User bestaat nog NIET			
			$userType = $orderType;
			$user = AuthController::createBusinessUser($r->all(), $userType);
		}
		// user is bekend en al Excite of Express gebruiker gemaakt
		$messType = 'op de bestellingen pagina van eXpress';
		if ( $orderType == CustomerDbModel::EXCITE) {
			$messType = 'in eXcite';
			$this->makeExciteTrial($user);
		} else {
			$this->makeExpressContract($r, $user);
		}
		
		// login for javascript redirect to /ego (wordt een redirect voor eXcite)
		if(Auth::attempt(['email' => $userEmail, 'password' => $userPwd])) {
			return response()->json(['error' => false, 'msg' => "Bestelling ontvangen. Er komt een Email ter bevestiging met een factuur. Na Ok is er een automatische login $messType"]);
		}
		// never reached, unless the Auth::attempt fails ....
		return response()->json(['error' => true, 'msg' => "END|Onverwachte fout"]);		

	}
}