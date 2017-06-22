<?php 

namespace Excite\Http\Middleware;

use Closure;

class Permissions {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
	private $trustedUsers = [
		'arie@yixow.com',
		'bassybag@gmail,com',
		'ha.herms@gmail.com',
		'janouschka@yixow.com',
		'leo@anl.nl',
		'leslie@anl.nl',
	];
    public function handle($request, Closure $next)
    {
		// direct na login kom je hier als een geautoriseerd gebruiker
		// verkeerde naam en/of wachtwoord komt hier niet
		// in dat geval krijg je eerder/elders een nieuwe login voorgeschoteld.
		// evt TODO Leslie: als nieuwe login nodig is op prod.yixow.com, test.yixow.com of demo.yixow.com
		// dan: return redirect('http://www.yixow.com');  		

    	if ( ! \Auth::user() ) { // dit wordt alleen 'later' het geval als de sessie voor de gebruiker is verlopen
    		return redirect('/auth/login');
   		}
		
		$s = $_SERVER['HTTP_HOST'];

		// test.yixow.com & prod.yixow.com & demo.yixow.com alleen enkele gebruikers toelaten
   		if ( /* $s == 'prod.yixow.com' || $s == 'test.yixow.com' || */ $s == 'demo.yixow.com') {
   			if( ! in_array( \Auth::user()->email,$this->trustedUsers) ) {
  				\Auth::Logout();
				return redirect('http://www.yixow.com');  				
			}
   		}
   		// do some weird magic for different user types....
		$userType = \Excite\Models\CustomerDbModel::getUserType();
		$path = $request->path();  // haal het url path uit het request

		if ($userType == \Excite\Models\CustomerDbModel::LIGHT) {
				// hier stond slechts: return redirect('/questions'); dat is te grof en dus fout!
				// omdat questions zo ook een redirect krijg naar questions
				// levert dat een redirect loop op als je questions in de Permissions route group zet
				// Permissions moet wel en dat kan als je het alsvolgt aanpakt:
				if ( strpos( $path, 'questions') !== 0 ) { // url die niet met questions begint; mag niet
					return redirect('/questions');
				} else { // /questions/.. urls niet toegestaan voor LIGHT
					if ( strpos($path , 'questions/getQ') === 0 || strpos($path , 'questions/updateQdates') === 0 )
						return redirect('/questions');
				}
		}
		if ($userType == \Excite\Models\CustomerDbModel::EXPRESS) {
		   // alleen een url path 'guests....' moet in dit geval een redirect krijgen!! 
			if ( strpos( $path, 'guests') === 0 ) {
			    return redirect('/questions');
   			}
   		}
		if ($userType == \Excite\Models\CustomerDbModel::EXCITE ) {
				// Kan door wat volgt terecht niet stiekem een eXpress Groep of een eXcite proefabo bestellen
				if ( strpos($path , 'ego') === 0 )  // disable all ego... urls
					return redirect('/questions');
   		}   
		// default return when not returned earlier
		return $next($request);

    }

}