<?php

namespace A17\HttpBasicAuth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use A17\HttpBasicAuth\Services\HttpBasicAuth as HttpBasicAuthService;

/**
 * @method static HttpBasicAuthService instance()
 * @method static HttpBasicAuthService setRequest(Request $request)
 * @method static Request getRequest()
 * @method static bool enabled()
 * @method static HttpBasicAuthService setConfig()
 * @method static Response|RedirectResponse|JsonResponse|Application|ResponseFactory|null checkAuth(Request $request, $options = [])
 **/
class HttpBasicAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'a17.http-basic-auth.service';
    }
}
