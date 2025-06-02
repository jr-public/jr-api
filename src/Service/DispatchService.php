<?php
namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;

class DispatchService {
    public function dispatch(object $controllerInstance, array $route, Request $request): mixed {
        
        $reflectionMethod = new \ReflectionMethod($controllerInstance, $route['_method']);
        if (!$reflectionMethod->isPublic()) {
            throw new \RuntimeException("Controller method '".$route['_method']."' is not public", 500);
        }

        $arguments = [];
        foreach ($reflectionMethod->getParameters() as $param) {
            $paramName = $param->getName();

            // Check route parameters (e.g., /users/{id} -> $id)
            if (array_key_exists($paramName, $route)) {
                $arguments[] = $route[$paramName];
                continue;
            }

            // Check query parameters (for GET/DELETE) or request body (for POST/PUT/PATCH)
            $value = null;
            if (in_array($request->getMethod(), ['GET', 'DELETE'])) {
                $value = $request->query->get($paramName);
            } else {
                // For POST/PUT/PATCH, check both JSON and form-urlencoded bodies
                if ($request->headers->get('Content-Type') === 'application/json') {
                    $requestBody = json_decode($request->getContent(), true);
                    $value = $requestBody[$paramName] ?? null;
                } else {
                    // This handles application/x-www-form-urlencoded and multipart/form-data
                    $value = $request->request->get($paramName);
                }
            }

            // Handle missing required parameters or use default values
            if ($value === null) {
                if ($param->isDefaultValueAvailable()) {
                    $arguments[] = $param->getDefaultValue();
                    continue;
                }
                // If we reach here, a required parameter is missing and no default was provided.
                throw new ValidationException(
                    'MISSING_ARGUMENT',
                    "Missing required argument '{$paramName}' for method '{$reflectionMethod->getName()}' " .
                    "in controller '{".$route['_controller']."}'"
                );
            }

            $arguments[] = $value;
        }

        // 3. Invoke the Controller Method
        return $reflectionMethod->invokeArgs($controllerInstance, $arguments);
    }
}