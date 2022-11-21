<?php

namespace A17\HttpBasicAuth\Services;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class HttpBasicAuth
{
    protected array $config = [];

    public function checkAuth(Request $request, $options = []): Response|RedirectResponse|JsonResponse|Application|ResponseFactory|null
    {
        if ($this->disabled($options)) {
            return null;
        }

        /** TODO: add ignored routes to config */
        if ($this->routeShouldBeIgnored($request, $options)) {
            return null;
        }

        if ($this->userAuthenticated($request, $options)) {
            return null;
        }

        return $this->abort($request);
    }

    public function handle(Request $request, Closure $next, string $username = null, string $password = null): mixed
    {
        return $next($request);
    }

    public function disabled($options = []): bool
    {
        if (blank($options['username'] ?? null) || blank($options['password'] ?? null)) {
            return true;
        }

        return !($this->config['enabled'] ?? false);
    }

    public function userAuthenticated(Request $request, $options = []): bool
    {
        return $this->authenticateWithConfig($request, $options) || $this->authenticateWithDatabase($request, $options);
    }

    public function authenticateWithConfig($request, $options)
    {
        return $request->getUser() === ($options['username'] ?? $this->config['username'] ?? 'missing') &&
               $request->getPassword() === ($options['password'] ?? $this->config['password'] ?? 'missing');
    }

    public function authenticateWithDatabase($request, $options)
    {
        foreach ($options['guards'] ?? [] as $guard) {
            $usernameColumn = $guard['username-column'] ?? 'email';

            $succeeded = auth($guard)->attempt(
                [$usernameColumn => $request->getUser(), 'password' => $request->getPassword()],
            );

            if ($succeeded) {
                return true;
            }
        }

        return false;
    }

    public function abort(Request $request): Response|JsonResponse|Application|ResponseFactory
    {
        $header = ['WWW-Authenticate' => 'Basic realm="Basic Auth", charset="UTF-8"'];

        return $request->wantsJson()
            ? response()->json(
                [
                    'message' => '401 Authorization Required',
                ],
                401,
                $header,
            )
            : response('401 Authorization Required', 401, $header);
    }

    public function routeShouldBeIgnored(Request $request, $options = []): bool
    {
        $paths = $options['routes']['ignore']['paths'] ?? $this->config['routes']['ignore']['paths'] ?? [];

        foreach ($paths as $path) {
            if (Str::startsWith($path, '/')) {
                $path = Str::after($path, '/');
            }

            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }
}
