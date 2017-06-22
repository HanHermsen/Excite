<?php

namespace Excite\Models\PushHelpers;

use Illuminate\Database\Eloquent\Model;
use DB;
use Config;
use Carbon\Carbon;

class Devicetokens extends Model
{   
    public static function getToken($token, $type)
    {
	    //
        // Get Token
	    //
	    
	    $token = DB::table('device_tokens')
                    ->where('token', $token)
                    ->where('type', $type)
                    ->first();
	    return $token;
	}
	
	public static function getTokensForGroupMembers($groupId)
    {
	    //
        // Get Tokens for all devices from all group members and the group owner
	    //
	    
	    $arr = DB::table('device_tokens')
	    			->leftJoin('members', 'device_tokens.user_id', '=', 'members.user_id')
	    			->leftJoin('groups', 'device_tokens.user_id', '=', 'groups.user_id')
	    			->leftJoin('users', 'device_tokens.user_id', '=', 'users.id')
	    			->where(function($query) use ($groupId){
							$query->where('members.group_id', $groupId);	
							$query->orWhere('groups.id', $groupId);						
						})
                    ->groupBy('device_tokens.aws_arn')
                    ->select('device_tokens.aws_arn', 'device_tokens.token', 'device_tokens.user_id', 'device_tokens.type', 'users.unread_count')
                    ->get();
	    return $arr;
	}
	
	public static function createToken($awsArn, $token, $type)
    {
	    //
        // Save new token
	    //
	    
	    // add row
		DB::table('device_tokens')->insert([
			'aws_arn' => $awsArn,
			'user_id' => Config::get('userId'),
			'type' => $type,
			'token' => $token,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
	}
	
	public static function deleteToken($awsArn)
    {
	    //
        // Delete Token
	    //
	    
		DB::table('device_tokens')
						->where('aws_arn', '=', $awsArn)
						->delete();
	}
	
	//
	// Migration code from Parse to Amazon
	//
	
	public static function getAllTokens()
    {
	    //
        // Get Tokens for all devices
	    //
	    
	    $arr = DB::table('device_tokens')
	    			->leftJoin('members', 'device_tokens.user_id', '=', 'members.user_id')
	    			->leftJoin('users', 'device_tokens.user_id', '=', 'users.id')
                    ->groupBy('device_tokens.parse_id')
                    ->select('device_tokens.parse_id', 'device_tokens.token', 'device_tokens.user_id', 'device_tokens.type', 'users.unread_count')
                    ->get();
	    return $arr;
	}

	
	public static function addArnToToken($parseId, $token, $type, $awsArn)
    {
	    //
        // Update token with Amazon arn
	    //
	    
	    DB::table('device_tokens')
       		 	->where('parse_id', $parseId)
       		 	->where('token', $token)
       		 	->where('type', $type)
	   		 	->update(['updated_at' => Carbon::now(), 'aws_arn' => $awsArn]);
	}
}