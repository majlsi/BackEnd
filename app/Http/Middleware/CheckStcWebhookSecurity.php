<?php

namespace App\Http\Middleware;

use Closure;
use Connectors\StcConnector;

class CheckStcWebhookSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $header = $request->header('X-Cartwheel-Signature');
        $payLoad = request()->getContent();
        $webhookSecretHmacSHA256 = hash_hmac('SHA256', $payLoad, config('stcConfig.webhook_secret'));

        StcConnector::logWebhookData($request->url(),$request->method(),$request->all(),['webhook_header' => $header,'payload_hmac'=> $webhookSecretHmacSHA256,'hash_equals' => hash_equals($header,$webhookSecretHmacSHA256)],$header,$payLoad);

        if(!hash_equals($header,$webhookSecretHmacSHA256)){
            return response()->json(['message' => ["Not Allowed"]], 401);
        }

        return $next($request);
    }
}
