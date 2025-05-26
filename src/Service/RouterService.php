<?php
namespace App\Service;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\HttpFoundation\Request;

class RouterService
{
    private RouteCollection $routes;

    public function __construct() {
        $this->routes = new RouteCollection();
        $this->loadRoutesFromFile(getenv('PROJECT_ROOT') . 'config/routes.php');
    }

    private function loadRoutesFromFile(string $filePath): void
    {
        $config = require $filePath;

        foreach ($config as $name => $route) {
            $this->routes->add($name, new Route(
                $route['path'],
                ['_controller' => $route['controller']],
                $route['requirements'] ?? [],
                [],
                '',
                [],
                $route['methods'] ?? ['GET']
            ));
        }
    }

    public function match(Request $request): array
    {
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $routeInfo = $matcher->match($request->getPathInfo());
            [$controllerClass, $method] = explode('::', $routeInfo['_controller']);
        } catch (ResourceNotFoundException $e) {
            throw new \RuntimeException('Route not found', 404);
        } catch (MethodNotAllowedException $e) {
            throw new \RuntimeException('Method not allowed', 405);
        }
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class '$controllerClass' not found", 404);
        }
        if (!method_exists($controllerClass, $method)) {
            throw new \RuntimeException("Controller method '$method' not found in class '$controllerClass'", 404);
        }
        $routeInfo['_controller'] = $controllerClass;
        $routeInfo['_method'] = $method;
        return $routeInfo;
    }

    
}
