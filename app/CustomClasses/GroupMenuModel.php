<?php

namespace Excite\CustomClasses;
use Auth;
use Excite\CustomClasses\Groups;

// Model van de Choice List; kan worden gebruikt in Vragen, Gasten en Overzicht; test op grouplist == null met hasGroups() dan naar groepen met alert.
class GroupMenuModel {
	// alleen toegankelijk via methods, dat hoort zo
	private $groupList = null; // de lijst van ids en names
	private $groupChoiceId; // de laatste id van de voorkeuze
	private $firstChoiceId; // de eerste default keuze
	
	function __construct() {
		$gList = Groups::getGroupsForUser();
		if ( count($gList) > 1 ) {
			$tmp[0] = [ 'id' => 0, 'name' => "Kies een Group!" ];
			$this->groupList = array_merge($tmp,$gList);
			$this->groupChoiceId = 0;
			$this->firstChoiceId = 0;
			
		} else {
			if(count($qlist) == 1) {
				// er is maar 1 Groep; letop 0 groups kan ook wat dan? test op null van groupList met hasGroups()
				$this->groupList = $gList;
				$this->groupChoiceId = $gList[0]['id'];
				$this->firstChoiceId = $this->groupChoiceId;
			}
		}
	}
	
	public function setGroupChoiceId($groupChoiceId) {
	// TODO hier MOET je testen of het een legale waarde is ie er bestaat een groupList[]['id'] voor
	// als fout dan niks doen en return false; eigenlijk een throw Exception, want 'who cares about the return value'.
	// slordige programmeurs dienen hard aangepakt te worden.
		$this->groupChoiceId = $groupChoiceId;
		return true;
	}

	public function getGroupChoiceId() {
		return $this->groupChoiceId;
	}
	
	public function getGroupList() {
		return $this->groupList;
	}
	
	public function hasGroups() {
		if ( $this->groupList == null )
			return false;
		return true;
	}
	
	public function reset() {
		$this->groupChoiceId = $this->firstChoiceId;
	}
	
	public function getHTML() {
	
		$gl = $this->groupList;
		$cId = $this->groupChoiceId;
		for ( $i = 0 ; $i < count($gl); $i++) {
			$selected = '';
			$group = $gl[$i];
			if ( $cId == $group['id'] ) $selected = 'selected';
			echo "<option value=" . $group['id'] . " " . $selected . ">" . $group['name']. "</option>\n";
		}
	}
	
	public function getHTMLstr() {
		$out= '';
		$gl = $this->groupList;
		$cId = $this->groupChoiceId;
		for ( $i = 0 ; $i < count($gl); $i++) {
			$selected = '';
			$group = $gl[$i];
			if ( $cId == $group['id'] ) $selected = 'selected';
			$out .= "<option value=" . $group['id'] . " " . $selected . ">" . $group['name']. "</option>\n";
		}
		return $out;
	}
	
	public function getMembersJSON () {
	// hier de code voor de json encoded array met eamiladresses van members
	// uit $groupChoiceId 
		return '';	
	}
	
}

?>
