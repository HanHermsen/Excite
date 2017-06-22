<?php

namespace Excite\Http\Controllers\Questions;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Excite\CustomClasses\Stats;
use Excite\CustomClasses\StatsOld;

use Excite\Models\groupDbModel;
use Excite\Models\questionDbModel;
use Excite\CustomClasses\PushHelpers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use Image;

use Input;
use Session;
use Redirect;

use DB;

class QController extends BaseController {
	private $answArray;

	public function index(Request $request) {
		$deletedGroups = groupDbModel::viewGroups(true);
        $viewGroups = groupDbModel::viewGroups();
    	return view('questions/questions_index')->with('qCtrl', $this)->with(['viewGroups' => $viewGroups])->with(['deletedGroups' => $deletedGroups]);

	}

	public function store(Request $request) {
       //dd($request->all());
        $this->answArray = array_filter(array_map('trim',$request->only('answers1','answers2','answers3','answers4','answers5','answers6')));

        $validator = Validator::make($request->all(),[	
			'groups' => 'required|between:2,24',
			'question' => 'required|between:3,200',
			//'inputDateFrom' => 'required|date|after:yesterday|before:inputDateTill',
			'inputDateFrom' => 'required|date|after:yesterday',
			'inputDateTill' => 'date|after:inputDateFrom'|'',
            'user_image' => 'image|mimes:jpeg,png,gif|max:1000000',           
		]);

		$validator->after(function($validator) { // je hebt binnen deze anonieme functie een class variabele nodig
												 // die is globaal; een gewone var zit niet in scope
			if (count($this->answArray) < 2) {
				$validator->errors()->add('answers', 'Geef minstens twee antwoorden.');
			}
		});

        if($validator->fails()) {
			return back()->withInput()->withErrors($validator->errors());
		}

		// Process image
		/* orignal code with server side scaling
			if($request->file('user_image')) {
				// Han: dit levert een image op, _geen_ filename; beter is $img = .....
				$imageFileName = Image::make($request->file('user_image'));
				$imageFileExt = $request->file('user_image')->getClientOriginalExtension();
				$imageFileNameMD5 = md5(microtime()) . '.' . $imageFileExt;
				// alweer een make is fout; gewoon $img->
				$ImageCreate = Image::make($imageFileName)
					->resize(320, 240,function ($c){
					  	$c->aspectRatio();
    					$c->upsize();
					})
				->save('api/api/images/' . $imageFileNameMD5);
			} else { $imageFileNameMD5 = null; }
		*/
		// take client side processed image
		$imageFileNameMD5 = null;
		$imageDataUrl = $request->get('hiddenImageDataUrl');		
		if ( $imageDataUrl != '' ) {
			$img = Image::make($imageDataUrl);
			$imageFileNameMD5 = md5(microtime()) . '.' . 'png';
			$img->save('api/api/images/' . $imageFileNameMD5);

		} else {
			// Controle Hergebruik image 
			$oldImage = $request->get('hiddenImageFile');
			if ( $oldImage != '' )
				$imageFileNameMD5 = $oldImage;
		}

		$groups = $request->get('groups');
		$question = trim( $request->get('question'));
		$timestamp = date('Y-m-d H:i:s');

		// DateTime to SQL string

		$formDateFrom = date("Y-m-d H:i:s",strtotime($request->get('inputDateFrom')));

		//$formDateTill = date("Y-m-d H:i:s",strtotime($request->get('inputDateTill')));
		$formDateTill = $request->get('inputDateTill');
		if ($formDateTill == '') {
			$formDateTill = null;
		}
		else 
			$formDateTill = date("Y-m-d H:i:s",strtotime($formDateTill));


		$dateExplode = explode(' ',$formDateFrom);
		
		if($dateExplode[0] == date('Y-m-d')) {
			// Datum vandaag
			$today = null;
		} else {
			// Datum na vandaag
			$today = $dateExplode[0] . 'T00:00:00';
		}

		// End Time Limit Calculate
		
		$addQuestion = questionDbModel::insertQuestion($question,$timestamp,$formDateFrom,$formDateTill,$groups,$imageFileNameMD5);

		if($addQuestion) {

			foreach($this->answArray as $answers) {
				
				$addAnswers = questionDbModel::insertAnswer($addQuestion,$answers,$timestamp);
				
			}
			//PushHelpers::sendNewGroupQuestionNotification($groups,$today);
			if($today == null) {
				$mess = null; // custom message option
				$debug = false;
				$all = true; // send to all members of the group without constraints
				PushHelpers::sendNewGroupQuestionNotification($groups, $mess, $debug, $all);
			}

			if($addAnswers) {
			
				return back()->withErrors(['Toegevoegd'])->with('groupId', $groups);
			
			} else {
				// Han: huh? never reached I think
				true;
				
			}

		} else {
			// Han: huh? never reached I think
			true;
					
		}
        
	}
	// works fine but not called; stays for possible future use
	public function getMapDataAsJson(Request $r) {
		$x = $r->all();
		$s = new Stats($x['questionId'], 'Postcode', 'Geo');
		return response()->json($s->getMapDataForJson());
	}
	// Note: debug this function with http://............/questions/getStatsHTML?questionId=....&statsType=....
	//
	// handle Javascript ajax text file call from questions Web Page S-Button or from stats Window Button
	// called by public/js/questions.js:  ajaxGetStats(questionId, questionText [, statsType] [, viewType])
	// with URL /questions/getStatsHTML that is routed to this method
	
	public function getStatsHTML(Request $r) {
		$x = $r->all();
		$x['debug'] = false;
		if ( ! isset($x['questionId'] ) ) die();
		if ( ! isset($x['questionText'] ) ) {
			$x['debug'] = true;
			$x['questionText'] = "Debug mode";
		}
		// argQuestionText is used in JavaScript args ' ..... ' escape ' with \'
		$x['argQuestionText'] = $this->fixQtxt ( $x['questionText']);
		
				//dd($x);
		if (! isset($x['statsType']) || $x['statsType'] == null ||  trim($x['statsType']) == '' ) {
			$x['statsType'] = 'Geluk'; // Default
		}
		if ( isset($x['viewType']) && $x['viewType'] == 'Geo' ) {
			if ( $x['newStatsReq'] == 1 )
				$s = new Stats($x['questionId'], $x['statsType'], $x['viewType']);
			else
				$s = new StatsOld($x['questionId'], $x['statsType'], $x['viewType']);
			return view('questions/questions_geostats')->with('statsData', $s)->with('reqData',$x);
		}
		// get stats data object
		if ( $x['newStatsReq'] == 1 )
			$s = new Stats($x['questionId'], $x['statsType']);
		else
			$s = new StatsOld($x['questionId'], $x['statsType']);
		if ( isset($x['altTemplate']) ) {
			return view('questions/questions_altstats')->with('statsData', $s)->with('reqData',$x);
		}
		// return HTML text for stats Dialog window
		return view('questions/questions_stats')->with('statsData', $s)->with('reqData',$x);
	}

	public function getMiniStatsHTML(Request $r) {
		// allow access from other domains that will embed the ministats
		header("Access-Control-Allow-Origin: *");
		$x = $r->all();

		$x['argQuestionText'] = $this->fixQtxt ( $x['questionText']);

		$s = new Stats($x['questionId'], 'Postcode', 'Mini');
		$answerOptions = [];
		$answerOptions = $this->getAnswerOptions($x['questionId']);

		return view('questions/questions_ministats')->with('statsData', $s)->with('reqData',$x)->with('answerOptions',$answerOptions);

	}
	private function getAnswerOptions($questionId) {
		return questionDbModel::getOptions($questionId);
	}
	public function getQ(Request $r) 
    {
		$out['question'] = questionDbModel::getQuestion($r->questionId);
		$out['options'] = questionDbModel::getOptions($r->questionId);
		//dd(response()->json( $out ));
		return response()->json( $out );
	}
	
	public function delQ(Request $r) {
		$questionId = $r->get('q');
		$delType = $r->get('t');
		$userDel = 0;
		if ( $delType == 2 ) {
			$delType = 1;
			$userDel = 1;
		}
		$args = ['deleted' => $delType,'user_del' => $userDel];
		questionDbModel::updateQuestion($questionId,$args);
		$delType += $userDel;
		$mess = 'Vraag definitief verwijderd.';
		if ($delType == 0 )
			$mess = "Vraag teruggezet in App.";
		if ($delType == 1 )
			$mess = "Vraag verwijderd uit App. ";
		//$mess .= " (questionId: $questionId)";
		return response()->json( [ 'mess' => $mess ] );
	}	
	
	public function updateQdates(Request $r) {

		$dateInSecs = $r->get('dateIn')/1000;
		$dbDateIn = date("Y-m-d H:i:s",$dateInSecs);
		$dbDateOut = null;
		if ( $r->get('dateOut') != '' ) {
			$dateOutSecs = $r->get('dateOut')/1000;
			$dbDateOut = date("Y-m-d H:i:s",$dateOutSecs);
		}
		//$dates = '(received: From ' . $dateIn . " To " . $dateOut .  ' questionId ' . $r->get('questionId') . ')';
		$args = (['start_date' => $dbDateIn,'end_date' => $dbDateOut]);
		questionDbModel::updateQuestion($r->get('questionId'),$args);
		return response()->json( ['mess' => "Looptijd gewijzigd."] );
	}
	
	public function qByEmail(Request $r) {
	//dd($r->all());
		if (! $r->has('option'))
			return view('questions/qByEmail/badLink');
		$option = $r->get('option');
		$result = questionDbModel::getInvitationsEmail($option);
		if ( count($result) == 0 )
			return view('questions/qByEmail/badLink');
		if ( $result[0]->unsubscribe == 1 ) {
		    return view('questions/qByEmail/optOut');
		}
		if ($r->has('opt_out')) {
		    questionDbModel::unsubscribeInvitationsEmail($option);
			return view('questions/qByEmail/optOut');
		}
		$questionId = $result[0]->question_id;
		$q = questionDbModel::getQuestion($questionId);
		$q[0]->question = $this->fixQtxt($q[0]->question);
		$opts = questionDbModel::getOptions($questionId);

		return view('questions/qByEmail/question')->with('q', $q[0])->with('opts', $opts)->with('option', $option);	
	}

	public function storeAnswer(Request $r) {
		//dd($r->all());
		$option = $r->get('hiddenEmailLinkOption');
		$result = questionDbModel::getInvitationsEmail($option);
		$questionId = $result[0]->question_id;
		$q = questionDbModel::getQuestion($questionId);
		$answerDone = false;
		if ( $result[0]->done > 0 ) {
			$answerDone = true;
		} else {
			// noot: het antwoord kan nu met de api van Montana in de db worden genoteerd
			// er is een dedicated user voor gereserveerd die geen lid is van groep
			questionDbModel::setInvitationsEmailDone($option);
		}
		return view('questions/qByEmail/okResponse')->with('q', $q[0])->with('answerDone', $answerDone);		
	}
	
	private function fixQtxt ( $questionText ) {
		// remove illegal enters; is bad for the stats; horen niet in de db, maar kwam bij tests per ongeluk wel voor ...
		$questionText = str_replace(["\r\n", "\n", "\r"], ' ',$questionText);
		$questionText = str_replace("'", "\'", $questionText);
		$questionText = str_replace('"', '&quot;', $questionText);
		return $questionText;
	}
	
	
}