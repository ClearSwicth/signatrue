<?php

namespace ClearSwitch\Signatrue\Middleware;

use App\SignatrueGuard;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatrueAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guard=Auth::guard('api');
        if(!$guard->validated()){
            $result=[
                'code'=>$guard->getErrCode(),
                'msg'=>$guard->getErrMessage(),
                'data'=>[]
            ];
            throw new HttpResponseException(response()->json($result));
        }
        return $next($request);
    }
}
