<?php
declare(strict_types=1);

namespace OAuthServer\Middleware;

/**
 * For apache + php-fpm|php-cgi
 *
 * Set Authorization header from HTTP_AUTHORIZATION|REDIRECT_HTTP_AUTHORIZATION environment
 */
class AuthorizationEnvironmentMiddleware
{
    /**
     * @var array the Environment variable name that set for Authorization
     */
    protected $environment = [
        'HTTP_AUTHORIZATION',
        'REDIRECT_HTTP_AUTHORIZATION',
    ];

    /**
     * AuthorizationEnvironmentMiddleware constructor.
     *
     * @param array $environment the Environment variable name that set for Authorization
     */
    public function __construct(array $environment = ['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'])
    {
        $this->environment = $environment;
    }

    /**
     * Serve assets if the path matches one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke($request, $response, $next)
    {
        if ($request->hasHeader('Authorization')) {
            // If Authorization header is set, nothing to do.
            return $next($request, $response);
        }

        foreach ($this->environment as $env) {
            // Set Authorization header, if the environment variables is set.
            if (isset($_SERVER[$env])) {
                return $next($request->withHeader('Authorization', $_SERVER[$env]), $response);
            }
        }

        return $next($request, $response);
    }
}
