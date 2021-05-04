<?php
/**
 *
 * User: daikai
 * Date: 2021/4/30
 */

namespace ClearSwitch\Signatrue\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class WebSignatrueAuthenticate{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
     public function handle(Request $request, Closure $next)
{
    $guard=Auth::guard('web2');
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
