<?php 

namespace Excite\Http\Middleware;

use Closure;
use DB;

class YixowApiAuth {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next) {
		//$s = $_SERVER['HTTP_HOST'];
		$method = $request->method();
		if ( $method == 'OPTIONS' ) { // preflight request; occurs when default request header is changed in client app
			// add CORS compliant headers that are needed
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Request-Headers: X-AUTHENTICATION-TOKEN, Content-Type");
			header("Access-Control-Allow-Headers: X-AUTHENTICATION-TOKEN, Content-Type, X-XSRF-TOKEN");
			header("Access-Control-Expose-Headers: X-AUTHENTICATION-TOKEN");
			header("Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE");
			return; // just return the headers to the Client
		}
		//header("Access-Control-Allow-Origin: *");
		//var_dump($method);
		//dd($header);
		$path = $request->path();  // haal het url path uit het request
		$header = $request->header();
		// check token
		if ( strpos( $path, 'yixow/login') === false )  { // login is always accepted
			$bad = false;
			if ( ! isset($header['x-authentication-token']) ) {
				$bad = true;
			} else {
				$tok = $header['x-authentication-token'][0];
				if ( $tok == null || $tok == '' )
					$bad = true;
				else {
					$q  = "SELECT id FROM users ";
					$q .= "WHERE authentication_token = ? ";
					$res = DB::select($q, [$tok]);
					if ( count($res) == 0 ) $bad = true;
				}
			}
			if ($bad) {
				header("Access-Control-Allow-Origin: *");
				$out['ok'] = false;
				return response()->json($out);
			}
		}
		// default authorized return when not returned earlier
		return $next($request)
		->header('Access-Control-Allow-Origin', '*');
    }

}