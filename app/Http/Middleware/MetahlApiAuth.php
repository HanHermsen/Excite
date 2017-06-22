<?php 

namespace Excite\Http\Middleware;

use Closure;
use DB;

class MetahlApiAuth {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next) {
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
		// always allow access from anywhere
		return $next($request)
		->header('Access-Control-Allow-Origin', '*');
    }

}