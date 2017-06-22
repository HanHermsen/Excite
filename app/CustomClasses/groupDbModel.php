<?php

/**	Leslie
 * 	Nog niet in gebruik | TEST
 */

namespace Excite\CustomClasses;

use Auth;
use DB;

class groupDbModel extends Controller {
	
	private $userId = Auth::user()->id;

	
	public function __construct() {

		$this->userId = $userId;

	}

	public function insertGroup($name) {

		$this->name = $name;

		$addGroup = DB::table('groups')->insert(
    				array(
    				'user_id' => $this->userId,
    				'name' => $this->name,
    				'created_at' => $this->makeTimestamp(),
    				'updated_at' => $this->makeTimestamp(),
    				'color' => $this->makeColor(), 
    				)

    			);

	}

	public function viewGroup() {
	
		
		
	}
	
	private function makeTimestamp() {
		
		$this->timestamp = date('Y-m-d H:i:s');
		
		return $this->timestamp;
		
	}
	
	private function makeColor() {
		
		$colorsArray = collect([0,43148,2205495,12142484,14229370,14559063,15059461,15065380,15090976,15562501,16165381]);
  		$this->colors = $colorsArray->random();
  		
  		return $this->colors;
            
	}

}