<?php

namespace Excite\Http\Controllers\Guests;

use Input;
use Redirect;
use Auth;
use Excite\Http\Controllers\Controller;
use Mail;
use Excite\Models\groupDbModel;
use Excite\Models\GuestDbModel;
use DB;

use Illuminate\Http\Request;

class GuestController extends Controller
{

    public function index()
    {
        $viewGroups = groupDbModel::viewGroups();
		$deletedGroups = groupDbModel::viewGroups(true);
		
        $viewGuests = GuestDbModel::viewGuests($groupId = null);
        
   		return view('guests.index_guests' ,['viewGroups' => $viewGroups,'viewGuests' => $viewGuests, 'deletedGroups' => $deletedGroups]);

    }

    private function getGroupId()
    {
        $selectGroupId = Input::get('groups');
        return $selectGroupId;
    }
	// new Han
	private function extractUid($str) {
		$arr = explode('/',$str);
		$cnt = count($arr);
		if ( $cnt == 1 && $arr[0] == '' ) { // no / in string
			return 0;
		}
		if ( ! is_numeric($arr[$cnt -1] ) ) // another / somewhere
			return 0;
		if ( $cnt == 2 ) {
			$id = $arr[1];
			$name = $arr[0];
		} else { 
			$id = $arr[$cnt -1];
			unset($arr[$cnt -1]);
			$name = implode('/',$arr);
		}
		return GuestDbModel::unmapUid($id, $name);
	}

    public function postData(Request $request)
    {
		$debug = false;
		if ( $debug) {
			echo "<br />Request<br />";
			var_dump($request->all());
			echo "The time is " . date("h:i:sa") . '<br />';
			//dd("Debug end; Just show this");
		}
		$selectGroupId = $this->getGroupId(); 
		$selectGroupName = groupDbModel::getGroupName($selectGroupId)->name;
		        
		$timestamp = date('Y-m-d H:i:s');
		$newInvitations = explode ( ',' , $request->get('hiddenNewInvitationInput'));
		if ( $newInvitations[0] == '' ) $newInvitations = [];
		$doDelete = explode(',',$request->get('hiddenDoDeleteInput'));
		if ( $doDelete[0] == '' ) $doDelete = [];
		
		$memberDel=[];
		$memberUidDel=[];
		$invitationDel=[];
		$invitationUidDel =[];
		// aangepast door Han
		foreach ($doDelete as $d ) {
			if ( $d[0] == '.') { // member
				$tmp = substr($d, 1);
				if ( ($uid = $this->extractUid($tmp)) != 0 )
					$memberUidDel[] = $uid;
				else
					$memberDel[] = $tmp;
			} else {
				if ( ($uid = $this->extractUid($d)) != 0 )
					$invitationUidDel[] = $uid;
				else
					$invitationDel[] = $d;
			}
		}
		if ($debug) {
			echo "<br /><br />memberDel<br />";
			var_dump($memberDel);
			echo "<br /><br />memberUidDel<br />";
			var_dump($memberUidDel);
			echo "<br /><br />invitationDel<br />";
			var_dump($invitationDel);
			echo "<br /><br />invitationUidDel<br />";
			var_dump($invitationUidDel);
			echo "The time is " . date("h:i:sa") . '<br />';
		}
			// Delete Guests on email
			foreach($memberDel as $email) {
					GuestDbModel::deleteMember($selectGroupId,$email);
			}
			// delete Guests on uid
			foreach($memberUidDel as $id) {
				GuestDbModel::deleteMemberByUid($selectGroupId,$id);
			}
			// Delete invites on email
			foreach($invitationDel as $email) {
					GuestDbModel::deleteInvite($selectGroupId,$email);
			}
			// Delete invites on id
			foreach($invitationUidDel as $id) {
				GuestDbModel::deleteInviteByUid($selectGroupId,$id);
			}

		if ($debug) {
			echo "<br /><br />newInvitations<br />";
			var_dump($newInvitations);
		}
		$inviteCnt = 0;
		// # emails that will actually be sent for testing on test.yixow.com
		$sendEmailLimit = 0;
		if( $debug ) $sendEmailLimit = 0;
		foreach ( $newInvitations as $n ) {
			$inviteCnt++;
			$email = $n;
			$display_email = 1;
			if ( ($uid = $this->extractUid($n)) > 0 ) {
				$email = GuestDbModel::getUserEmail($uid);
				$display_email = 0;
			} else { // we have an email address that can be showed
					 // check (1) exists a member of groups of the owner with display_email = 0 and that address?
					 // (2) exists an invitation for groups of the owner with display_email = 0 and that address?
					 // action when found: fix display_email
					 // returns true when there was a member of this group fix; no further action needed
					 // returns false otherwise: this email must be (re)invited
					 if ( GuestDbModel::fixDisplayEmail($email,$selectGroupId, Auth::user()->id) ) {
						if ($debug)
							echo "<br /><br />Display email fixed for this group's member<br />";
						continue;
					}
			}
			$result = GuestDbModel::checkInviteId($email,$selectGroupId);		
			if ($result  > 0 ) { // existing invitation
				$inviteId = $result;
				if($debug) {
					echo "Reinvite uid $uid email $email<br />";
					echo "The time is " . date("h:i:sa") . '<br />';
				}
			} else {
				if($debug)
					echo "New invitation uid $uid email $email<br />";
				DB::transaction(function() use ($email,$selectGroupId,$timestamp,$display_email){
				$this->addToInvitations = GuestDbModel::insertInvite($email,$selectGroupId,$timestamp,$display_email);
				$this->inviteId = DB::getPdo()->lastInsertId();
				});
				DB::commit(); 
				$inviteId = $this->inviteId;
			}
			
			if(!empty($inviteId)) {
				if ($debug) {
					echo "Mail invitation $uid email $email<br />";
				}
				$s = $_SERVER['HTTP_HOST'];
				if ( $s == 'excite.app' || $s == 'demo.yixow.com' || ($s == 'test.yixow.com' && $inviteCnt > $sendEmailLimit) ){
					if ($debug) {
						echo "Email NOT sent $inviteCnt<br /><br />";
					}
					continue;
				}
				if ($debug) {
						echo "Email sent<br /><br />";
				}
			    $hashGroupId = \Hashids::encode($inviteId);
			    Mail::send('emails.invited', ['invitedBy' => Auth::user()->email,'hashGroupId' => $hashGroupId,'selectGroupName' => $selectGroupName], function($message) use ($email) {
				$message->to($email, null)->subject('Yixow invitation');
				});
			}
		}
		// clear some fields; their content is not needed and can be very large in some rare cases
		$request->merge( [ 'hiddenMemberInput' => '', 'hiddenInvitationInput' => '', 'hiddenDoDeleteInput' => '', 'hiddenNewInvitationInput' => '']);
		if ($debug) {
			dd("Debug end; db transactions done");
		}
        return Redirect::to('guests')->withInput();
    }

	public function getGroupMembers(Request $r) 
    {
		$out['members'] = GuestDbModel::getMembersLcSorted($r->input('groupId'));
		$out['invitations'] = GuestDbModel::getInvitationsLcSorted($r->input('groupId'));
		return response()->json( $out );
	}


}