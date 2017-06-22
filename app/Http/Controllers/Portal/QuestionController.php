<?php

namespace Excite\Http\Controllers\Portal;

use Excite\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use Hash;
use DB;
use Excite\Models\questionDbModel;
use Excite\Models\groupDbModel;
use Excite\Models\QuestionPortalDbModel;
use Vinkla\Hashids\Facades\Hashids;
use Jenssegers\Agent\Agent;

class QuestionController extends Controller {

	/*
    |--------------------------------------------------------------------------
    | Question Controller
    |--------------------------------------------------------------------------
    */

	protected $redirectPath = '/';

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

	public function getEmail(Request $r) {
		
		$os = 'desktop';
		$agent = new Agent();
	    if($agent->isAndroidOS()) {
		    $os = 'android';
	    }
	    else if($agent->isiOS()) {
		    $os = 'ios';
	    }
	    
		if (! $r->has('option'))
			return view('portal/pages/questionemail', ['title' => 'Vraag beantwoorden', 'showForm' => false, 'message' => 'Vraag niet gevonden', 'os' => $os]);
		$option = $r->get('option');
		$result = questionDbModel::getInvitationsEmail($option);
				
		if (count($result) == 0)
			return view('portal/pages/questionemail', ['title' => 'Vraag beantwoorden', 'showForm' => false, 'message' => 'Vraag niet gevonden', 'os' => $os]);
		if ($result[0]->unsubscribe == 1) {
		    return view('portal/pages/questionemail', ['title' => 'Vraag beantwoorden', 'showForm' => false, 'message' => 'Je bent uitgeschreven en kan deze vraag niet meer beantwoorden.', 'os' => $os]);
		}
		if ($result[0]->done == 1) {
			return view('portal/pages/questionemail', ['title' => 'Vraag beantwoorden', 'showForm' => false, 'message' => 'Je hebt deze vraag al beantwoord.', 'os' => $os]);
		}
		if ($r->has('opt_out')) {
		    questionDbModel::unsubscribeInvitationsEmail($option);
			return view('portal/pages/questionemail', ['title' => 'Vraag beantwoorden', 'showForm' => false, 'message' => 'Je bent uitgeschreven en krijgt deze emails niet meer.', 'os' => $os]);
		}
		$questionId = $result[0]->question_id;
		$q = questionDbModel::getQuestion($questionId);
		$q[0]->question = $this->fixQtxt($q[0]->question);
		$opts = questionDbModel::getOptions($questionId);
		// fix by Han for null group
		if ( $q[0]->group_id == null )
			$groupName = 'Publiek';
		else {
			$group = groupDbModel::getGroupName($q[0]->group_id);
			$groupName = $group->name;
		}
				
		return view('portal/pages/questionemail', ['title' => 'Vraag beantwoorden', 'showForm' => true, 'message' => '', 'os' => $os, 'q' => $q[0], 'opts' => $opts, 'option' => $option, 'group' => $groupName, ]);
	}
	
	private function fixQtxt ( $questionText ) {
		// remove illegal enters; is bad for the stats; horen niet in de db, maar kwam bij tests per ongeluk wel voor ...
		$questionText = str_replace(["\r\n", "\n", "\r"], ' ',$questionText);
		// fix quotes
		$questionText = str_replace("'", "\'", $questionText);
		$questionText = str_replace('"', "\'", $questionText);
		return $questionText;
		/*
		$questionText = str_replace("'", "&#39;", $questionText);
		return str_replace('"', '&quot;', $questionText); */
	}
	
	public function postEmailAnswer(Request $r) {
		
		$option = $r->get('hiddenEmailLinkOption');
		$selectedOptionId = $r->get('selectedOptionId');
		$result = questionDbModel::getInvitationsEmail($option);
		$questionId = $result[0]->question_id;
		// fix by han
		$answerUid = $result[0]->answerUid;
		$q = questionDbModel::getQuestion($questionId);
		$answerDone = false;
		if ( $result[0]->done > 0 ) {
			// vraag al beantwoord
			$returnArr = array('error' => 'Je hebt deze vraag al beantwoord');
			return json_encode($returnArr);
		} else {
			// save answer
			QuestionPortalDbModel::saveAnswer($selectedOptionId, $questionId,$answerUid);
			
			// set user email answered
			questionDbModel::setInvitationsEmailDone($option);
			
			// get statistics
			$allAnwsers = QuestionPortalDbModel::getAnswerStatisticsForQuestionId($questionId);
			//foreach($allAnwsers as $anAnswer) {
			//	$anAnswer->id = Hashids::encode($anAnswer->id);
			//}
			
			$returnArr = array('error' => '', 'statistics' => $allAnwsers);
			return json_encode($returnArr);
		}	
	}
}