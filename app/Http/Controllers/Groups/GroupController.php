<?php

namespace Excite\Http\Controllers\Groups;

use Redirect;
use Excite\Http\Controllers\Controller;
use Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Excite\Models\groupDbModel;
use Excite\Models\CustomerDbModel;
use Excite\Models\UserDbModel;
use Excite\Http\Controllers\Auth\AuthController;
use Input;
use Image;
use DB;
use File;
use Auth;
use Mail;
use Hash;

class GroupController extends Controller
{
    /**
     * Show a list of all of the application's users.
     *
     * @return Response
     */
    private $indexMess = '';
    
    public function index()
    {
		$deletedGroups = groupDbModel::viewGroups(true);
		$viewGroups = groupDbModel::viewGroups();
		$view = view('groups.index_groups')->with(['viewGroups' => $viewGroups])->with(['deletedGroups' => $deletedGroups]);
		if ( $this->indexMess == '')
			return $view;
		return $view->withErrors($this->indexMess);
    }

	public function getGroupFormHTML(Request $r) { // show group info; dit is een ajax call; return de HTML van het form view
		$groupId = $r->all()['groupId']; // is een meegezonden var zelfde waarde als 'hiddenGroupId' in het Form

		$selectGroup = groupDbModel::viewEditGroup($groupId);
		//dd($selectGroup);
		$viewGroups = groupDbModel::viewGroups();
		$deletedGroups = groupDbModel::viewGroups(true);
		return view('groups/form_groups')->with(['selectGroup' => $selectGroup,'viewGroups' => $viewGroups, 'deletedGroups' => $deletedGroups, 'lastInsertedGroupId' => $groupId]);
	}
	
	// not in use; stays for future usage
	public function changeGroup(Request $r) { // wijziging bestaande group; dit is een ajax POST call; return de HTML van het form view met alle waarden
		//dd($r->all());
		$groupId = $r->all()['groupId']; // is een meegezonden var zelfde waarde als 'hiddenGroupId' in het Form
										 // hier bestaand groupId > 0

		return $this->AddGroup($r);
	}

    public function AddGroup(Request $request) { // submit POST from the Form can be new or change or delete
		//dd($request->all());
		$groupId = $request->all()['hiddenGroupId']; // 0 new; >0 change this group; <0 delete
		$selectGroup = groupDbModel::viewEditGroup($groupId);

		if ( $groupId < 0 ) { // <0 verwijder 'definitief'
			$groupId = -1 * $groupId;
			$args[ 'user_del' ] = 1;
			groupDbModel::updateGroups($groupId,$args);
			$this->indexMess = 'Groep is verwijderd';
			return $this->index();
		}
		$validator = Validator::make($request->all(),[

			'GroupName' => 'required|between:2,24',
			'GroupImage' => 'image|mimes:jpeg,png,gif|max:1000000',
			'GroupColor' => array('regex:/#([a-fA-F0-9]{3}){1,2}\b/'),
			'GroupExpire' => 'date|after:today',
			'groupType' => 'between:1,4',
			'GroupSort' => 'between:0,1',
			'GroupLabelActivate' => 'between:0,1',

		]);

		if($validator->fails()) { 
				return back()->withInput()->withErrors($validator->errors())->with(['lastInsertedGroupId' => $groupId]);
		
		} 
		// validation ok ; process the image
		/* original code van Les with server side scaling
			if($request->file('GroupImage')) {
				// dit levert geen file naam op maar een img; dus $img =
				$imageFileName = Image::make($request->file('GroupImage'));
				$imageFileExt = $request->file('GroupImage')->getClientOriginalExtension();
				$imageFileNameMD5 = md5(microtime()) . '.' . $imageFileExt;
				// alweer een make; niet nodig; gewoon $img->
				$ImageCreate = Image::make($imageFileName)
					->resize(320, 240,function ($c){
					  	$c->aspectRatio();
    					$c->upsize();
					})
					->save('api/api/images/groups/' . $imageFileNameMD5);
			} else { .....		
		*/

		// take scaled image uploaded from Client
		$imageDataUrl = $request->get('hiddenImageDataUrl');
		if ( $imageDataUrl != '' ) {
			$img = Image::make($imageDataUrl);
			$imageFileNameMD5 = md5(microtime()) . '.' . 'png';
			$img->save('api/api/images/groups/' . $imageFileNameMD5);

		} else {
			$imageFileNameMD5 = null;
		}

		$groupInputName = Input::get('GroupName');
		$groupSubname = Input::get('LabelName');
		$timestamp = date('Y-m-d H:i:s');
		$groupSort = Input::get('GroupSort');
		$GroupLabelActivate = Input::get('GroupLabelActivate',0);
		// controle of datum ingevuld is
		if(!empty(Input::get('GroupExpire'))) {
			$groupExpire = date("Y-m-d H:i:s",strtotime(Input::get('GroupExpire')));
		} else {
			$groupExpire = null;
		}

		/* Groeps type formule   1,2,3 of 4
		1 is allebei aan 
		2 is deeln uitn
		3 is vragen st mag
		4 is allebei uit */
		$groupType = Input::get('GroupType');	
		if(empty($groupType)) {
			$groupType = '4';
		} elseif (count($groupType) == 2) {
			$groupType = '1';
		} else {
			$groupType = implode(Input::get('GroupType'));
		}
		// Controle of image verwijderd wordt
		if(!empty(Input::get('hiddenImageDel') && empty($imageFileNameMD5))) { $imageFileNameMD5 = null; };

		// controle of er een kleur gekozen is
		if(Input::get('GroupColor') != '') {
			$colors = hexdec(Input::get('GroupColor'));
			// Let op 0 is default: dat wordt aan de client kant wit: #ffffff
			if ( $colors == 0 ) $colors = 1; // dit blijft black
		} else {
			$colorsArray = collect([11053224,4802889,2368548,14491776,16740384,11149312,11255,
									45104,15626368,16748576,16719872,37119,6738022,16748714,
									16763936,9449693,53503,11202133,16755404,16772720]);
			$colors = $colorsArray->random();
		}

		if ($groupId == 0) { //new group; hier wat extra commentaar voor Leslie
			DB::transaction(function() use ($groupInputName,$groupSubname,$imageFileNameMD5,$timestamp,$colors,$groupExpire,$groupType,$groupSort,$GroupLabelActivate){
				$addNewGroup = groupDbModel::insertGroups($groupInputName,$imageFileNameMD5,$timestamp,$colors,$groupExpire,$groupType,$groupSubname,$groupSort,0,$GroupLabelActivate);
				// aan Class instance var geven en straks oppakken in de function scope
				$this->groupId = DB::getPdo()->lastInsertId();
			});
			DB::commit(); // doe de transaction
			// $groupId van de function goed zetten
			$groupId = $this->groupId;
			//return Redirect::to('groups')->withErrors(array('errors' => trans('messages.groupAdded')))->withInput()->with(['lastInsertedGroupId' => $groupId]);
			$message = array('errors' => trans('messages.groupAdded'));
		} else { // group change

			$args = (['name' => $groupInputName,'updated_at' => $timestamp,'color' => $colors,'date_expired' => $groupExpire,'type' => $groupType,'express_label' => $groupSubname,'sort_type' => $groupSort, 'group_display' => $GroupLabelActivate]);
			if ($request->get('activate') != null ) { // activate or deactivate a group
				$args[ 'deleted' ] = $request->get('activate');
				if ($request->get('activate') == 1 ) // make inactive ie deleted = 1
					$args['group_display'] = 0;
			}
			if( $imageFileNameMD5 != null || $imageFileNameMD5 != '') {
				$args['image'] = $imageFileNameMD5;		
			} elseif ($request->get('hiddenImageDel') != '') {
				File::delete('api/api/images/groups/' . $selectGroup->image);
				$args['image'] = null;
			}			

			$updateGroup = groupDbModel::updateGroups($groupId,$args);
			$message = array('errors' => "Groep gewijzigd");
		}
		// haal op uit db want $selectGroup wordt nu eenmaal als Object array gebruikt in het blade;
		// kan beter maar who cares? Dit is simpel en werkt altijd goed.
		$selectGroup = groupDbModel::viewEditGroup($groupId);
		// altijd opnieuw ophalen! er kan een wijziging zijn van een groepsnaam zijn of het is een nieuwe!
		// kan beter maar who cares? Dit is simpel en werkt altijd goed.
		$viewGroups = groupDbModel::viewGroups();
		$getDeleted = true;
		$deletedGroups = groupDbModel::viewGroups($getDeleted);
		// TODO maak hier een redirect van; is natuurlijk beter; wijziging blades nodig!! vars komen uit Session!!!
		return view('groups.index_groups')->withErrors($message)->with(['lastInsertedGroupId' => $groupId])->with(['selectGroup' => $selectGroup])->with('viewGroups', $viewGroups)->with('deletedGroups', $deletedGroups);

    }
}