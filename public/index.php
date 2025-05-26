<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use Symfony\Component\HttpFoundation\Request;


try {
	//
	$request	= Request::createFromGlobals();
	//
	$auth 		= new AuthService($entityManager);
	// $token 		= $auth->extractJwt($request);
	// DEV
	$jwt_s		= new App\Service\JWTService();
	$token		= $jwt_s->createToken([
		'sub' => 1
	]);
	// END DEV
	$user 		= $auth->authorize($token);
	//
	$router 	= new RouterService();
    $routeInfo 	= $router->match($request);
	//
	$dispatch 	= new DispatchService($entityManager, $user);
	$response 	= $dispatch->dispatch($routeInfo, $request);
	//
	echo json_encode($response);
} catch (\Throwable $th) {
    http_response_code($th->getCode() ?: 500);
    echo json_encode(['error' => $th->getMessage()]);
}
