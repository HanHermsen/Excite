<?php

namespace Excite\Models;

use DB;
use Auth;

class questionDbModel {

	public static function insertQuestion($question,$timeStamp,$formDateFrom,$formDateTill,$groupId,$image) {
		
		$addQuestion = DB::table('questions')->insertGetId(
				array(
				'user_id' => Auth::user()->id,
				'question' => $question,
				'inappropriate' => '0',
				'created_at' => $timeStamp,
				'updated_at' => $timeStamp,
				'start_date' => $formDateFrom,
				'end_date' => $formDateTill,
				'group_id' => $groupId,
				'image' => $image,
				'deleted' => '0',
				'popularity' => '0'
			)

		);	
		
		return $addQuestion;	
	}

	public static function insertAnswer($questionId,$answer,$timeStamp) {
		
		$addAnswer = DB::table('options')->insertGetId(
			array(
			'question_id' => $questionId,
			'text' => $answer,
			'created_at' => $timeStamp,
			'updated_at' => $timeStamp,
			)
		
		);
		
		return $addAnswer;
				
	}
	
	public static function updateQuestion($id,$args) {
  
		  $updateQuestion = DB::table('questions')
		   ->where('id',$id)
		   ->limit(1)
		   ->update($args); 
	}
	
	public static function getRecentQuestions() {
		
		$q =  'SELECT id, question, image, TIMESTAMPDIFF(MINUTE,created_at, NOW()) as ago FROM questions ';
		$q .= 'WHERE group_id IS NULL AND questions.deleted = 0 AND inappropriate < 8 ';
		$q .= 'ORDER BY created_at DESC ';
		$q .= 'LIMIT 6';
		$result = DB::select( $q );
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
					}
				}
			}
			$r->ago = $tmp . $suffix;
			// het root path voor een file is ......./Excite/app; zie config/filesystems.php
			if ( $r->image != null && ! file_exists ( '../public/api/api/images/' . $r->image ) ) {
				// fix db shit
				$r->image = null;
			}
		}
		return $result;					
	}
	public static function getQuestion($questionId) {
		$q =  'SELECT id, question, image, group_id, DATE(created_at) AS dateIn, DATE(end_date) AS dateOut FROM questions ';
		$q .= 'WHERE id = ? ';
		$result = DB::select( $q, [$questionId] );
		if ( count($result) > 0 ) {
			if ( $result[0]->image != null && ! file_exists ( '../public/api/api/images/' . $result[0]->image ) ) {
				// fix db shit
				$result[0]->image = null;
			}
		}
		return $result;
	}
	public static function getOptions($questionId) {
		$q =  'SELECT * FROM options ';
		$q .= 'WHERE question_id = ? ';
		return DB::select( $q, [$questionId] );	
	}
	
	public static function getInvitationsEmail ($emailHash) {
		$q =  'SELECT * FROM invitations_email ';
		$q .= 'WHERE mail_hash = ? ';
		return DB::select( $q, [$emailHash] );	
	}
	
	public static function setInvitationsEmailDone ($emailHash) {
		DB::table('invitations_email')
		   ->where('mail_hash',$emailHash)
		   ->limit(1)
		   ->update(['done' => 1]); 	
	}
	public static function unsubscribeInvitationsEmail ($emailHash) {
		$rs = self::getInvitationsEmail($emailHash);
		$groupId = $rs[0]->group_id;
		DB::table('invitations_email')
		   ->where('email',$rs[0]->email)
		   ->where('group_id',$groupId)
		   ->update(['unsubscribe' => 1]); 	
	}
/*	do not fix here; will if needed be done in eXcite QController or QuestionController of Portal
	private static function fixQtxt ( $questionText ) {
		// remove illegal enters; is bad for the stats; horen niet in de db, maar kwam bij tests per ongeluk wel voor ...
		$questionText = str_replace(["\r\n", "\n", "\r"], ' ',$questionText);
		// fix quotes
		$questionText = str_replace("&", "&amp;", $questionText);
		$questionText = str_replace("'", "&#39;", $questionText);
		return str_replace('"', '&quot;', $questionText);
	}
*/
	
}