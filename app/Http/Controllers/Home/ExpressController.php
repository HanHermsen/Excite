<?php

namespace Excite\Http\Controllers\Home;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Excite\Models\CustomerDbModel;
use DB;


class ExpressController extends BaseController {

	public function index(Request $r) {
		return view('home/express/page');
	}
	public function store(Request $r) {
		// voor later
		// CustomerDbModel::storeContract();
		$msg	= print_r($r->all(), true);
		return back()->withInput()->withErrors("Bestelling ontvangen; er komt een bevestiging per Email. ------ [ Maar niet heus nog ]");
	}
	private function getPcRange ($getZipCode, $incrementArea ) {
		$range = [];
		$pcSelect = CustomerDbModel::getPc($getZipCode,$incrementArea);
		foreach($pcSelect as $res) {
			$firstPc = str_replace($getZipCode,'',substr($res->pc,0,4));
			$secondPc = str_replace($getZipCode,'',substr($res->pc,4,4));

			$range[] = (!empty($firstPc) ? $firstPc .',' : '') . (!empty($secondPc) ? $secondPc . ',' : '');
		}
		$pcRange = $getZipCode . ',' . rtrim((implode('',$range)),',');
		return $pcRange;
	}
	
	// ajax request; geef de prijslijst
	public function getPrice(Request $r) {
		$out['priceList'] = CustomerDbModel::getPriceList();
		return response()->json($out);
	}
	
	public function getZipCode(Request $r) {
	//dd($r->all());
		$zipCode = $out['zipCode'] = CustomerDbModel::getZipCode($r->get('lat'),$r->get('lng'));
		
		$getPopulation = 0;
		$pcRange = '';
		if ( $zipCode != '' ) {
		
			$incrementArea = $r->get('area') + 1; 
			$pcRange = $this->getPcRange ($zipCode, $incrementArea );
			// Get Population
			$getPopulation = CustomerDbModel::getPopulation($pcRange);
		}
		$out['population'] = $getPopulation;
		$out['range'] = $pcRange;
		return response()->json($out);
	}

	// ajax request: geef lat/lng van een Postcode 4 cijfers
	public function getLatLng (Request $r) {
		$out = CustomerDbModel::getLatLng($r->get('zipCode'));
		return response()->json($out);
	}
	// ajax request
	public function getGroupNames() {
		$out = groupDbModel::getGroupNames(Auth::user()->id);
		return response()->json($out);
	}
	
}