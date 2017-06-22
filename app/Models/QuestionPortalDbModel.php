<?php

namespace Excite\Models;

use DB;
use Session;
use Carbon\Carbon;

class QuestionPortalDbModel {

	public static function saveAnswer($optionId, $questionId, $answerUid)
    {
	    $date = Carbon::now();
		if ( $answerUid == 565 ) { // original code follows
			// get user for email answers 'start@yixow.com' (id: 565)
			$user = DB::table('users')
							->where('email', 'start@yixow.com')
							->first();
			
			// save answer */
			
			DB::table('answers')->insert([
				'user_id' => $user->id, 
				'question_id' => $questionId, 
				'option_id' => $optionId, 
				'created_at' => $date,
				'updated_at' => $date, 
				'dataset_id' => $user->dataset_id // must be NULL for the Stats
			]);
			return;
		}
		// $answerUid is not 565
		$user = DB::table('users')
				->where('id', $answerUid)
				->first();
		DB::table('answers')->insert([
				'user_id' => $user->id, 
				'question_id' => $questionId, 
				'option_id' => $optionId, 
				'created_at' => $date,
				'updated_at' => $date, 
				'dataset_id' => $user->dataset_id
			]);
		// give user a new Profile/dataset
		UserDbModel::cloneProfile($user->id);
	}
	
	public static function getAnswerStatisticsForQuestionId($questionId)
    {
	    $answers = DB::table('options')
	    				->where('options.question_id', $questionId)
	    				->leftJoin('answers', 'options.id', '=', 'answers.option_id')	    				
	    				->groupBy('options.id')
	    				->orderBy('amount', 'desc')
	    				->orderBy('options.id', 'asc')
		    			->select(DB::raw('options.id, options.text, count(answers.id) as amount'))			
	    				->get();
	    return $answers;
	}
}