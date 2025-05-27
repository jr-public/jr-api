<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use ReflectionMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
// use Psr\Log\LoggerInterface; // Example for another common dependency

class DispatchService
{
    private EntityManagerInterface $entityManager;
    private ?object $activeUser; // Or your specific User class, e.g., App\Entity\User
    private ValidatorInterface $validator;
    // private ?LoggerInterface $logger; // Example of another injectable dependency

    /**
     * @param EntityManagerInterface $entityManager The Doctrine EntityManager.
     * @param object|null $activeUser The currently authenticated user, or null.
     * // @param LoggerInterface|null $logger An optional logger instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?object $activeUser,
        ValidatorInterface $validator,
        // ?LoggerInterface $logger = null
    ) {
        $this->entityManager = $entityManager;
        $this->activeUser = $activeUser;
        $this->validator = $validator;
        // $this->logger = $logger;
    }

    /**
     * Instantiates the controller, resolves its method arguments, and invokes it.
     *
     * @param string $controllerClass The fully qualified class name of the controller.
     * @param ReflectionMethod $reflectionMethod The ReflectionMethod for the action to call.
     * @param array $routeParameters Parameters extracted from the route (e.g., {id} from /users/{id}).
     * @param Request $request The current HTTP request object.
     * @return mixed The result returned by the controller method.
     * @throws \RuntimeException If a required argument cannot be resolved.
     */
    public function dispatch(
        array $route,
        Request $request
    ): mixed {
        
        $controllerInstance = new $route['_controller']($this->entityManager, $this->activeUser, $this->validator);
        
        $reflectionMethod = new \ReflectionMethod($route['_controller'], $route['_method']);
        if (!$reflectionMethod->isPublic()) {
            throw new \RuntimeException("Controller method '".$route['_method']."' is not public", 403);
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
                throw new \RuntimeException(
                    "Missing required argument '{$paramName}' for method '{$reflectionMethod->getName()}' " .
                    "in controller '{".$route['_controller']."}'",
                    400
                );
            }

            $arguments[] = $value;
        }

        // 3. Invoke the Controller Method
        return $reflectionMethod->invokeArgs($controllerInstance, $arguments);
    }
}