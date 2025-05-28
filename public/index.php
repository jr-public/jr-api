<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');

use App\Bootstrap\DIContainerBootstrap;
use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use App\Service\UserContextService;
use Symfony\Component\HttpFoundation\Request;

try {
	//
	$container = DIContainerBootstrap::create();
	//
	$request	= Request::createFromGlobals();
	$device = $request->headers->get('User-Agent', 'unknown');
	//
	$auth = $container->get(AuthService::class);
	// $token 		= $auth->extractJwt($request);
	// DEV
	$jwt_s		= new App\Service\JWTService();
	$token		= $jwt_s->createToken([
		'sub' => 1
	]);
	// END DEV
	$user 		= $auth->authorize($token);
	$userContext = new UserContextService($user, $device);
	// This makes the container stateful, and i dont like it
	// I need to find a different solution but it will do for now.
	// 28/05/25
	$container->set(UserContextService::class, $userContext);
	//
	$router = $container->get(RouterService::class);
    $routeInfo 	= $router->match($request);
	//
	$controllerInstance = $container->get($routeInfo['_controller']);
	//
	$dispatch = $container->get(DispatchService::class);
	$response 	= $dispatch->dispatch($controllerInstance, $routeInfo, $request);
	echo $response;
} catch (\Throwable $th) {
    http_response_code($th->getCode() ?: 500);
    echo json_encode(['error' => $th->getMessage()]);
}
