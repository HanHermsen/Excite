<?php 
namespace Excite\CustomClasses;
use Config;
use Excite\Models\PushHelpers\Devicetokens;
use Excite\Models\PushHelpers\Groups;
use Excite\Models\PushHelpers\Users;
use Aws\Laravel\AwsFacade as AWS;
use Auth;
use DB;
 
class PushHelpers{
	// My common helper functions
	
	private static $iosArnId = 'arn:aws:sns:eu-central-1:787482663491:app/APNS/Yixow_iOS_Push_Production';
	private static $androidArnId = 'arn:aws:sns:eu-central-1:787482663491:app/GCM/Yixow_Android_Push';
	
	public static function sendNewGroupQuestionNotification($groupId,$customMsg = null, $debug = true, $all = false)
	{		
		// get group
		$group = Groups::getGroup($groupId, false);
		if ( $group == null )
			return 'Groep ' . $groupId . ' niet gevonden';

		// get device tokens
		$tokens = Devicetokens::getTokensForGroupMembers($groupId);
		
		/* some extra things by Han; also: debug mode */
		if( ! $all ) { // only send to users that did not answer all questions....
			// get number of questions; doen we voor het gemak even hier & niet via een Model in Models
			// $q = 'SELECT id FROM questions WHERE group_id = ? AND questions.deleted = 0 ';
			$q = 'SELECT id FROM questions WHERE group_id = ? ';
			$res = DB::select($q,[$groupId]);
			$questionCnt = count($res);
			if ( count($res) == 0 ) return 'Geen vragen bij deze groep';
			// get uids of members that did not answer all questions;
			// doen we voor het gemak hier & niet via een Model in Models
			$q = 'SELECT user_id FROM members WHERE members.group_id = ? AND members.response < ? ';
			$res = DB::select($q,[$groupId,$questionCnt]);
			if ( count($res) == 0 )
				return 'Alle vragen door alle members beantwoord';			
			$poorUserids = [];
			foreach ($res as $r ) { // make plain array for later usage
				$poorUserIds[] = $r->user_id;
			}
		}
		/* end extra's by Han */
		
		// set message
		if($customMsg != null) {
			$msg = $customMsg;
		} else {
			$msg = "Er is belangstelling voor jouw mening in de groep ".$group->name;
		}
		
		$userIds = array();
		$client = AWS::createClient('sns');
	    foreach ($tokens as $token) {
		    if($token->user_id != Config::get('userId') ) {
				if ( ! $all ) { // extra by Han
					// don't send notification to users that answered ALL questions ALREADY
					if ( in_array($token->user_id, $poorUserIds)) {
						$userIds[] = $token->user_id;
						if ( ! $debug )
							PushHelpers::_sendPushMessage($client, $token->type, $msg, $token->aws_arn, $token->unread_count+1, $token->token);
					}
				} else { // send to all; original code
					$userIds[] = $token->user_id;
					if ( ! $debug )
						PushHelpers::_sendPushMessage($client, $token->type, $msg, $token->aws_arn, $token->unread_count+1, $token->token);
				}	
		    }		 	
		}		
		// increase unread count for all users
		$userIds = array_unique($userIds);
		if ( $debug ) {
			sort($userIds);
			sort($poorUserIds);
			var_dump($poorUserIds);
			dd($userIds);
		}
		Users::increaseUnreadCount($userIds);
		return '';
	}	
		
	private static function _sendPushMessage($client, $type, $message, $arn, $badge, $token)
	{
		$payloadKey = '';
		$payload = '';
		if ($type == 'ios') {
			$payloadKey = 'APNS';
			$payload = array(
	                'aps' => array(
	                    	'alert' => $message,
							'sound' => 'default',
							'badge' => $badge
	                    )
	                );
		}
		else if ($type == 'android') {
			$payloadKey = 'GCM';
			$payload = array(
	                'data' => array(
			                'data' => array(
		                    	'alert' => $message,
								'sound' => 'default',
								'badge' => $badge
		                    )
	                    )
	                );
		}
		
		try {
			$result = $client->publish(array(
		        'TargetArn' => $arn,
		        'MessageStructure' => 'json',
		        'Message' => json_encode(array(
		            'default' => $message,
		            $payloadKey => json_encode($payload)
		        ))
		    ));
		    //print_r($result);
	    }
	    catch (\Exception $e)
		{
			if (strpos($e->getMessage(), 'EndpointDisabled') !== false) {
			    //echo "\n - EndpointDisabled for ".$arn." Delete token - ";
			    PushHelpers::removeToken($token, $type);
			}			
			//continue with rest of application
		}
	}
	
	public static function addToken($token, $type, $oldToken)
	{
		// check if token already exists
		$dbToken = Devicetokens::getToken($token, $type);
		
		// remove old token if it exists
		if($oldToken != null) {
			// get aws id for old token in database
			$dbOldToken = Devicetokens::getToken($oldToken, $type);
			
			if($dbOldToken != null) {
				// delete token in aws
				PushHelpers::_deleteAWSToken($dbOldToken->aws_arn);
				
				// delete token in database
				Devicetokens::deleteToken($dbOldToken->aws_arn);
			}
		}
		
		// check if token was registered to different user, then delete it first
		if($dbToken != null && $dbToken->user_id != Config::get('userId')) {
			// delete token in aws
			PushHelpers::_deleteAWSToken($dbToken->aws_arn);
			
			// delete token in database
			Devicetokens::deleteToken($dbToken->aws_arn);
			
			// set to null
			$dbToken = null;
		}
		
		// only try to save token if it doesn't exist
		if($dbToken == null) {
			// send token to aws
			$awsArn = PushHelpers::_sendTokenToAWS($token, $type);
			
			// add device token to database
			Devicetokens::createToken($awsArn, $token, $type);
		}					
	}
	
	public static function removeToken($token, $type)
	{
		// check if token exists
		$dbToken = Devicetokens::getToken($token, $type);
		
		if($dbToken == null) {
			// token not found
			return;
		}
		
		// delete token in aws
		PushHelpers::_deleteAWSToken($dbToken->aws_arn);
		
		// delete token in database
		Devicetokens::deleteToken($dbToken->aws_arn);		
	}
	
	private static function _sendTokenToAWS($token, $type)
	{
		$client = AWS::createClient('sns');
		$platformArn = PushHelpers::$iosArnId;
	    if ($type == 'android') {
			$platformArn = PushHelpers::$androidArnId;
		}
	    
	    $result = $client->createPlatformEndpoint(array(
		    'PlatformApplicationArn' => $platformArn,
		    'Token' => $token,
		    'CustomUserData' => Config::get('userId')
		));
	    $arn = $result->get('EndpointArn');
		return $arn;
	}
	
	private static function _deleteAWSToken($awsArn)
	{
		$client = AWS::createClient('sns');
		$result = $client->deleteEndpoint(array(
		    'EndpointArn' => $awsArn,
		));
	}
	
	/* migrate from Parse to AWS */
	public static function migrateParseIds()
	{
		$client = AWS::createClient('sns');
		
		// get device tokens
		$tokens = Devicetokens::getAllTokens();
		echo " migration <br><br>";
		$userIds = array();
	    foreach ($tokens as $token) {		    
		    $platformArn = PushHelpers::$iosArnId;
		    if ($token->type == 'android') {
				$platformArn = PushHelpers::$androidArnId;
			}
		    
		    $result = $client->createPlatformEndpoint(array(
			    'PlatformApplicationArn' => $platformArn,
			    'Token' => $token->token,
			    'CustomUserData' => $token->user_id
			));
		    $arn = $result->get('EndpointArn');
		    echo "add: ".$arn."<br>";
		    Devicetokens::addArnToToken($token->parse_id, $token->token, $token->type, $arn);	 	
		}		
	}

}
?>