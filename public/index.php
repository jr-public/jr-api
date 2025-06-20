<?php 
if (getenv('APP_ENV') === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Check if origin is in the allowed list
$origin		= $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed	= [
    'https://localhost',
    'http://localhost',
];
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Content-Type: application/json');
    http_response_code(200);
    exit;
}


require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');

use App\Bootstrap\DIContainerBootstrap;
use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use App\Service\ResponseService;
use App\Service\RequestContextService;
try {
	//
	$container  = DIContainerBootstrap::create();
	$response   = $container->get(ResponseService::class);
	$context    = $container->get(RequestContextService::class);
	$router     = $container->get(RouterService::class)->match($context->getRequest());
	// Validate token then authorize the user. Could (should?) be middleware instead.
	if ($router['_group'] != 'guest') {
		$auths	= $container->get(AuthService::class);
		$token 	= $auths->extractJwt($context->getRequest());
		$user 	= $auths->authorize($token);
		$context->setUser($user);
	}
	// Get controller instance and dispatch
	$instance   = $container->get($router['_controller']);
	$data       = $container->get(DispatchService::class)->dispatch($instance, $router, $context->getRequest());
	$response   = $response->success($data);
} catch (\Throwable $th) {
	$response 	= $response->error($th);
}
//
$response->send();