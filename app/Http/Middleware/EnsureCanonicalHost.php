<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalHost
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_string($canonicalHost) || $canonicalHost === '') {
            return $next($request);
        }

        $requestHost = $request->getHost();

        if ($requestHost === $canonicalHost) {
            return $next($request);
        }

        $canonicalBaseUrl = rtrim((string) config('app.url'), '/');
        $canonicalUrl = $canonicalBaseUrl.$request->getRequestUri();

        return new RedirectResponse($canonicalUrl, 302);
    }
}
