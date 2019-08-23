<?php declare(strict_types=1);
/**
 * @author AlexandreDEBUSSCHERE
 */

namespace Borsch\Http\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class SimpleRouterMiddleware
 *
 * @package Borsch\Http\Middleware
 */
class SimpleRouterMiddleware implements MiddlewareInterface
{

    const REQUEST_HANDLER_INDEX = 'request-handler';

    /** @var array */
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'OPTIONS' => [],
        'PATCH' => [],
        'HEAD' => []
    ];

    /** @var ResponseInterface */
    protected $not_found_response;

    /** @var ResponseInterface */
    protected $method_not_allowed_response;

    /**
     * SimpleRouterMiddleware constructor.
     *
     * @param ResponseInterface|null $not_found_response
     * @param ResponseInterface|null $method_not_allowed_response
     */
    public function __construct(?ResponseInterface $not_found_response = null, ?ResponseInterface $method_not_allowed_response = null)
    {
        $this->not_found_response = $not_found_response;
        $this->method_not_allowed_response = $method_not_allowed_response;
    }

    /**
     * @return ResponseInterface
     */
    public function getNotFoundResponse(): ResponseInterface
    {
        return $this->not_found_response;
    }

    /**
     * @param ResponseInterface $not_found_response
     * @return SimpleRouterMiddleware
     */
    public function setNotFoundResponse(ResponseInterface $not_found_response): SimpleRouterMiddleware
    {
        $this->not_found_response = $not_found_response;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getMethodNotAllowedResponse(): ResponseInterface
    {
        return $this->method_not_allowed_response;
    }

    /**
     * @param ResponseInterface $method_not_allowed_response
     * @return SimpleRouterMiddleware
     */
    public function setMethodNotAllowedResponse(ResponseInterface $method_not_allowed_response): SimpleRouterMiddleware
    {
        $this->method_not_allowed_response = $method_not_allowed_response;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function get(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function post(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function put(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function delete(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function options(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function head(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function patch(string $path, callable $handler): SimpleRouterMiddleware
    {
        $this->routes[strtoupper(__FUNCTION__)][$path] = $handler;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function any(string $path, callable $handler): SimpleRouterMiddleware
    {
        return $this
            ->get($path, $handler)
            ->post($path, $handler)
            ->put($path, $handler)
            ->delete($path, $handler)
            ->options($path, $handler)
            ->head($path, $handler)
            ->patch($path, $handler);
    }

    /**
     * @param string[] $methods
     * @param string $path
     * @param callable $handler
     * @return SimpleRouterMiddleware
     */
    public function match(array $methods, string $path, callable $handler): SimpleRouterMiddleware
    {
        $methods = array_map('strtoupper', $methods);

        foreach ($methods as $method) {
            if (!in_array($method, array_keys($this->routes))) {
                throw new InvalidArgumentException(sprintf(
                    'The method [%s] is unknown or not authorized.',
                    $method
                ));
            }

            $this->{strtolower($method)}($path, $handler);
        }

        return $this;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        $path = $request->getUri()->getPath();

        if (!in_array($method, array_keys($this->routes))) {
            return $this->method_not_allowed_response;
        }

        if (!isset($this->routes[$method][$path])) {
            return $this->not_found_response;
        }

        $request = $request->withAttribute(self::REQUEST_HANDLER_INDEX, $this->routes[$method][$path]);

        return $handler->handle($request);
    }
}