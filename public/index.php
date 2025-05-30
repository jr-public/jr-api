<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');

use App\Bootstrap\DIContainerBootstrap;
use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use App\Service\RequestContextService;

try {
	//
	$container 	= DIContainerBootstrap::create();
	$context	= $container->get(RequestContextService::class);
	$request	= $context->getRequest();
    $router 	= $container->get(RouterService::class)->match($request);
	if ($router['_group'] != 'guest') {
		$auth 		= $container->get(AuthService::class);
		$token 		= $auth->extractJwt($request);
		$user 		= $auth->authorize($token);
		$context 	= $context->setUser($user);
	}
	//
	$instance 	= $container->get($router['_controller']);
	$response 	= $container->get(DispatchService::class)->dispatch($instance, $router, $request);
	echo $response;
} catch (\Throwable $th) {
    http_response_code(500);
    $errorResponse = [
        'error' 	=> true,
        'type' 		=> 'internal_error',
        'code' 		=> 'INTERNAL_SERVER_ERROR',
        'message' 	=> $th->getMessage(),
        'context' 	=> []
    ];
    
	$errorResponse['debug'] = [
		'file' 	=> $th->getFile(),
		'line' 	=> $th->getLine(),
		'trace' => $th->getTraceAsString()
	];
    
    echo json_encode($errorResponse);
}
