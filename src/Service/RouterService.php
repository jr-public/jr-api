<?php
namespace App\Service;

use App\Exception\NotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class RouterService
{
    private RouteCollection $routes;
    private array $routeGroups = [];

    public function __construct() {
        $this->routes = new RouteCollection();
        $this->loadRoutesFromFile(getenv('PROJECT_ROOT') . 'config/routes.php');
    }

    private function loadRoutesFromFile(string $filePath): void
    {
        $config = require $filePath;

        foreach ($config as $groupName => $groupRoutes) {
            foreach ($groupRoutes as $routeName => $route) {
                $this->routes->add($routeName, new Route(
                    $route['path'],
                    ['_controller' => $route['controller']],
                    $route['requirements'] ?? [],
                    [],
                    '',
                    [],
                    $route['methods'] ?? ['GET']
                ));
                
                // Store which group this route belongs to
                $this->routeGroups[$routeName] = $groupName;
            }
        }
    }

    public function match(Request $request): array {
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);
        
        try {
            $routeInfo = $matcher->match($request->getPathInfo());
        } catch (NoConfigurationException $th) {
            throw new NotFoundException('ROUTING_ERROR', 'No configuration found', 404);
        } catch (ResourceNotFoundException $th) {
            throw new NotFoundException('ROUTING_ERROR', 'Resource not found', 404);
        } catch (MethodNotAllowedException $th) {
            throw new NotFoundException('ROUTING_ERROR', 'Method not allowed', 404);
        } catch (\Throwable $th) {
            throw new NotFoundException('ROUTING_ERROR', 'Unknown error', 404);
        }
        
        [$controllerClass, $method] = explode('::', $routeInfo['_controller']);
        if (!class_exists($controllerClass)) {
            throw new NotFoundException('ROUTING_ERROR', "Controller class '$controllerClass' not found", 404);
        }
        if (!method_exists($controllerClass, $method)) {
            throw new NotFoundException('ROUTING_ERROR', "Controller method '$method' not found in class '$controllerClass'", 404);
        }
        
        $routeInfo['_controller'] = $controllerClass;
        $routeInfo['_method'] = $method;
        
        // Add the route group to the return info
        $routeName = $routeInfo['_route'];
        $routeInfo['_group'] = $this->routeGroups[$routeName] ?? 'unknown';
        
        return $routeInfo;
    }
}