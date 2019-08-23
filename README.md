# Borsch - Simple Request Router Middleware

A simple conditional request router PSR-15 middleware.  
As it only uses **if** statement to match routes and does not rely on complex regular expressions, it is also really fast !

## Installation

Via [Composer](https://getcomposer.org/) :  
`composer require borsch/simple-request-router-middleware`

## Usage

You will need to use this middleware with a PSR-15 Request Handler. For instance :
* [Borsch Server Request Handler](https://github.com/debuss/borsch-server-request-handler)
* [Awesome PSR-15 Middleware](https://github.com/middlewares/awesome-psr15-middlewares)

Here is a basic usage example with Borsch Server Request Handler :
```php
<?php declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Borsch\Http\Factory;
use Borsch\Http\Middleware\SimpleRouterMiddleware;
use Borsch\Http\Server\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Initialise the PSR-15 Request Handler
$dispatcher = new Dispatcher();

// Initialise the Simple Router Middleware with default not found and not allowed method response.
$router = new SimpleRouterMiddleware(
    Factory::getInstance()->createResponse(404),
    Factory::getInstance()->createResponse(405)
);

// Adding a route for any request method on root path
$router->any('/', function(ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write('Home !');
    return $response;
});

// Adding a route GET on path /hello/world
$router->get('/hello/world', function(ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write('Hello World !');
    return $response;
});

// Adding a redirection
$router->get('/redirect', function(ServerRequestInterface $request, ResponseInterface $response) {
    return $response
        ->withBody(Factory::getInstance()->createStream(''))
        ->withStatus(302)
        ->withHeader('Location', 'http://localhost:8080/hello/world');
});

$dispatcher->withMiddleware($router);

$dispatcher->run();

```

### Methods (=verbs) available

You can use this middleware to create routes with any of these methods :
* GET
* POST
* PUT
* DELETE
* OPTIONS
* PATCH
* HEAD

There are shortcuts methods for them :
```php
$router->get('/get-route', 'get_handler');
$router->post('/post-route', 'post_handler');
// put, delete, ...
```

The `match` method allows you to specify different methods for a route :
```php
$router->match(['GET', 'POST'], '/get-post-route', 'get_post_handler');
```

The `any` method allows you to specify a route for any method :
```php
$router->any('/any-route', 'any_handler');
// equivalent to :
$router->match(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD'], '/any-route', 'any_handler');
```

These methods are fluent, therefore you can link their calls :
```php
$router
    ->get('/get-route', 'get_handler')
    ->post('/post-route', 'post_handler');
```

:warning: _Requests with a method other than the ones upper will return a ResponseInterface with HTTP Status Code of 405._

### Not Found and Method Not Allowed responses

You need to provide a default PSR-7 ResponseInterface so this middleware can return a 404 or 405 ResponseInterface if no route is found or
if the method is not allowed.

You can do so in the constructor :
```php
// Initialise the Simple Router Middleware with default not found and not allowed method response.
$router = new SimpleRouterMiddleware(
    Factory::getInstance()->createResponse(404),
    Factory::getInstance()->createResponse(405)
);
```

or via setters :
```php
$router->setNotFoundResponse(Factory::getInstance()->createResponse(404));
$router->setMethodNotAllowedResponse(Factory::getInstance()->createResponse(405));
```

Getters are also available.

### Found response

When a route matches, the middleware simply add an attribute in the ServerRequestInterface with the route handler.
It can then be dealt with later by the PSR-15 Request Handler or any other middleware.

## License

```
MIT License

Copyright (c) 2019 Alexandre DEBUSSCHERE <zizilex@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```