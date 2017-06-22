<?php

namespace Excite\Http\Controllers\Guests;

use Input;
use Redirect;
use Excite\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Excite\Models\inviteDbModel;

class InviteController extends Controller
{

    public function index(Request $request, $option = null, $id = null ,$gId = null)
    {
        
        $getInvites = inviteDbModel::getInvites();

        if(!empty($option)){
            
            if($option == 'accept') {
                
                inviteDbModel::acceptInvite($gId);
                inviteDbModel::deleteInvite($id);
                return Redirect::back();

            }
            
            if($option == 'delete') {

                inviteDbModel::deleteInvite($id);
                return Redirect::back();

            }

        }

        return view('guests.invite_guests',['invited' => $getInvites]);
    }
   
    public static function countInvites() {
    	return "<counter>";
    }

}
