<?php namespace App\Http\Middleware;

use Closure;

class TokenMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	if (empty($request->input('access_token')) || empty($request->input('device'))) {
    		 //$request->input('access_token');
    		 //return redirect('home');
    	}
    	
        return $next($request);
    }

}
