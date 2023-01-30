<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // header(
        //     'Access-Control-Allow-Methods: POST, GET, OPTIONS, PATCH, PUT, DELETE '
        // );
        // header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization, Accept, charset,boundary,Content-Length');
        // // header('Access-Control-Allow-Origin: *');

        // return $next($request);

        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PATCH, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin'
        ];
        if ($request->getMethod() === "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $res = $next($request);

        foreach ($headers as $key => $value) {
            $res->header($key, $value);
        }

        return $res;
    }
}