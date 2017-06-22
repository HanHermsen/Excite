<?php

namespace Excite\Http\Controllers\Home;

use Illuminate\Routing\Controller as BaseController;
use DB;
use Mail;
use Request;
use Excite\Models\GuestDbModel;

class QBrowserController extends BaseController {
	public function index(Request $r) {
/*
	Mail::send('emails/qByEmail', [], function($message) {
		//$message->to('ha.herms@gmail.com', 'Jon Doe')->subject('Test')->cc('leo@anl.nl', $name = null);
		$message->to('ha.herms@gmail.com', 'Jon Doe')->subject('Test');
	}); */
		if (! Request::has('option'))
				return view('home/qbrowser/index')->with('qu', $this->getQuestions()) ; // full experimental page
		$req = Request::all();

		if ( $req['option'] == 'embed' )
			return view('home/qbrowser/dialog')->with('qu', $this->getQuestions()); // return experimental embed stuff only

		// had geen zin iets te gaan veranderen in routes.php; dus ......

		if ( $req['option'] == 'qByEmail' ) { // call from Slider/MiniStats
			// allow cross domain access
			header("Access-Control-Allow-Origin: *");
			return response()->json( $this->qByEmail($req) );
		}
		// any other value for option: return HTML for slider
		$groupId = 0;
		$source = "'publiek'";
		if (Request::has('email')) {
			$email = $req['email'];
			$q = 'SELECT group_id FROM StatsRemotePermissions ';
			$q .= 'WHERE email = ? LIMIT 1';
			$result = DB::select( $q, [$email] );
			if (count($result) != 0 ) {
				$groupId = $result[0]->group_id;
				$res = DB::select('SELECT name FROM groups WHERE id = ? LIMIT 1',[$groupId]);
				$source = $res[0]->name;
			}
		}
		// allow cross access from other domains that will embed the ministats
		header("Access-Control-Allow-Origin: *");
		return view('home/qbrowser/slider')->with('qu', $this->getQuestions($groupId))->with('source', $source)->with('groupId', $groupId);

	}
	
	private function getQuestions($groupId = 0) {
		$q =  'SELECT id, question, image, TIMESTAMPDIFF(MINUTE,created_at, NOW()) as ago FROM questions ';
		if ( $groupId > 0 ) {
			$q .= 'WHERE group_id = ?  AND questions.deleted = 0 ';
			$q .= 'ORDER BY created_at DESC ';
			$q .= 'LIMIT 6';
			$result = DB::select( $q, [$groupId] );

		} else {
			$q .= 'WHERE group_id IS NULL  AND questions.deleted = 0 ';
			$q .= 'ORDER BY created_at DESC ';
			$q .= 'LIMIT 6';
			$result = DB::select( $q );
		}
		foreach ( $result as $r) {
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
						if ($tmp > 365 ) {
							$tmp = '';
							$suffix = 'Meer dan een jaar geleden';
						}
					}
				}
			}
			// $tmp = ''; $suffix = 'Meer dan een jaar geleden';
			$r->ago = $tmp . $suffix;
			// het root path voor een file is ......./Excite/app; zie config/filesystems.php
			if ( $r->image != null && ! file_exists ( '../public/api/api/images/' . $r->image ) ) {
				// fix db shit
				$r->image = null;
			}
		}
		return $result;	
	}
	
	private function qByEmail($r) {
		$questionId = $r['questionId'];
		$email = $r['email'];
		$sliderGroupId = $r['sliderGroupId'];
		$qImage = $r['qImage'];
		$out['ok'] = 0;
		//$out['questionId'] = $questionId;
		//$out['sliderGroupId'] = $sliderGroupId;

		// does the question exist and is it a question from the slider group? Prevents Url hacking!
		$res = DB::select('SELECT question, group_id FROM questions WHERE id = ? LIMIT 1',[$questionId]);
		if ( count($res) > 0 ) {
			$groupId = $res[0]->group_id;
			if($groupId == null ) $groupId = 0;
			if ( $groupId == 0 && $sliderGroupId > 0) {
				$out['msg'] = 'Groep publiek klopt niet';
				return $out;
			} elseif ($groupId != $sliderGroupId) {
				$out['msg'] = 'Groep klopt niet';
				return $out;								
			}
			$questionText = $res[0]->question;
		}
		else {
			$out['msg'] = 'Vraag niet gevonden';
			return $out;
		}
		
		// is user een bekende?
		$answerUid = 0;
		$q = 'SELECT id FROM users WHERE email = ? LIMIT 1';
		$res = DB::select($q,[$email]);
		if (count($res) > 0 )
			$answerUid = $res[0]->id;
		$out['answerUid'] = $answerUid;
		if ( $answerUid ) {
			// user bekend; is de vraag al eerder beantwoord door user?
			$q = 'SELECT id FROM answers WHERE question_id = ? AND user_id = ? LIMIT 1';
			$res = DB::select($q,[$questionId,$answerUid]);
			if ( count($res) > 0 ) {
				$out['ok'] = 1;
				$out['msg'] = 'Je hebt deze vraag al beantwoord.';
				return $out;
			}
		}
		// heeft de user al eens de vraag per Email beantwoord?
		$q = 'SELECT * FROM invitations_email WHERE question_id = ? AND email = ? LIMIT 1';
		$res = DB::select($q,[$questionId,$email]);
		if ( count($res) > 0 ) {
			if ( $res[0]->done ) {
				// dit moet een anoniem antwoord by Email zijn geweest
				if (! $answerUid ) { // opniew anoniem; mag niet
					$out['ok'] = 1;
					$out['msg'] = 'Je hebt deze vraag al eens per Email beantwoord.';
					return $out;				
				}
			}
		}
		

		if ( $sliderGroupId == 0 ) {
			// question is public; permission always granted
			$out['ok'] = 1;
			$out['msg'] = 'Vraag is verstuurd';
			$this->sendQuestionEmail($questionId,$email,$answerUid, $questionText,$sliderGroupId,$qImage);
			return $out;
		}
		$res = $this->checkGroupRelation ($groupId, $answerUid, $email);
		if ($res['isMember'] /*|| $res['isInvited'] */) {
			$out['ok'] = 1;
			$out['msg'] = 'Vraag is verstuurd';
			$this->sendQuestionEmail($questionId,$email,$answerUid, $questionText,$sliderGroupId,$qImage);
			return $out;
		}
		// is altijd true; een besloten te houden groep moet niet in StatsRemotePermissions worden gezet!
		if ($res['canBeInvited']) {
			$this->sendGroupInvitationEmail($email,$groupId,$res['isInvited']);
			$out['ok'] = 1;
			$out['msg'] = "Je kunt pas een antwoord geven als je bekend bent bij de groep.  Je krijgt een email met een uitnodiging.";
			return $out;
		}
		// never reached
		$out['ok'] = 1;	
		$out['msg'] = "Helaas: dit is een besloten groep. Alleen de beheerder kan je uitnodigen om antwoord te kunnen geven.";
			
		return $out;
	}
	private function sendQuestionEmail($questionId,$email,$answerUid, $questionText, $groupId, $qImage) {
		if ($answerUid == 0) $answerUid = 565;

		$mailHash = strtoupper (md5($questionId . $email));
		$updatedAt = date('Y-m-d H:i:s');
		$q = 'SELECT * FROM invitations_email WHERE question_id = ? AND email = ? LIMIT 1';
		$res = DB::select($q,[$questionId,$email]);
		if ( count($res) > 0 ) { // update; it's checked earlier that this can be done
			$id = $res[0]->id;
			$qu = 'UPDATE invitations_email ';
			$qu .= 'SET mail_send = 1, done = 0, mail_hash = ?, answerUid = ?, updatedAt = ? ' ;
			$qu .= 'WHERE id = ?';
			DB::update($qu,[$mailHash,$answerUid,$updatedAt, $id]);
		} else { //insert
			$qi = 'INSERT invitations_email ';
			$qi .= 'SET question_id = ?, group_id = ?, email = ?, mail_send = 1, mail_hash = ?, answerUid = ?, updatedAt = ? ' ;
			DB::insert($qi,[$questionId,$groupId,$email,$mailHash,$answerUid,$updatedAt]);		
		}
		$linkUrl = "https://www.yixow.com/question-by-email?option=" . $mailHash;
		Mail::send('emails/qByEmail', ['linkUrl'=> $linkUrl, 'questionText' => $questionText, 'qImage' => $qImage], function($message) use ($email) {
			//$message->to('ha.herms@gmail.com', 'Jon Doe')->subject('Test')->cc('leo@anl.nl', $name = null);
			//$message->to('ha.herms@gmail.com', 'Jon Doe')->subject('Test');
			$message->to($email, '')->subject('Yixow vraag beantwoorden per email');
		});
	}
	private function sendGroupInvitationEmail($email, $groupId, $isInvited) {
			if ( ! $isInvited ) {
				$display_email = 1;
				$timestamp = date('Y-m-d H:i:s');
				DB::transaction(function() use ($email,$groupId,$timestamp,$display_email){
				$this->addToInvitations = GuestDbModel::insertInvite($email,$groupId,$timestamp,$display_email);
				$this->inviteId = DB::getPdo()->lastInsertId();
				});
			} else $this->inviteId = $isInvited;
			$q = 'SELECT groups.name , users.email FROM groups ';
			$q .= 'JOIN users ON users.id = groups.user_id ';
			$q .= 'WHERE groups.id = ?';
			$res = DB::select($q,[$groupId]);
			$groupName = $res[0]->name;
			$groupUserEmail = $res[0]->email;
			$hashInviteId = \Hashids::encode($this->inviteId);
			Mail::send('emails/invitedQbyEmail', ['invitedBy' => $groupUserEmail,'hashInviteId' => $hashInviteId,'selectGroupName' => $groupName], function($message) use ($email) {
			//$message->to('ha.herms@gmail.com', 'Jon Doe')->subject('Test')->cc('leo@anl.nl', $name = null);
			//$message->to('ha.herms@gmail.com', 'Jon Doe')->subject('Test');
			$message->to($email, '')->subject('Uitnodiging voor Yixow groep');
		});	
	}
	private function checkGroupRelation ($groupId, $userId, $email) {
		$out['isMember'] = false;
		$out['isInvited'] = 0;
		$out['canBeInvited'] = true; // mag altijd, anders publiceer je de groep niet zo
		// kijk of de user bekend is in de group
		// check invitation
		$res = DB::select('SELECT id FROM invitations WHERE group_id = ? AND email = ? AND accepted = 0 LIMIT 1',[$groupId, $email]);
		if (count($res) > 0 ) {
			$out['isInvited'] = $res[0]->id;
			return $out;
		}
		if ($userId > 0 ) {
			// check membership
			$res = DB::select('SELECT id FROM members WHERE group_id = ? AND user_id = ? LIMIT 1',[$groupId, $userId]); 
			if (count($res) > 0 ) {
				$out['isMember'] = true;
				return $out;
			}
		}	
		// kijk of de group open is voor deelnemers uitnodigen (type = 1 of 2)
		//$res = DB::select('SELECT id FROM groups WHERE id = ? AND type <= 2 LIMIT 1',[$groupId]);
		//if (count($res) > 0 ) {
		//	$out['canBeInvited'] = true;
		//}
		return $out;	
	}
}