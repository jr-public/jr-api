<?php 
if (getenv('APP_ENV') === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
$config = require getenv("PROJECT_ROOT") . 'config/app.php';

use App\Bootstrap\DIContainerBootstrap;
use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use App\Service\ResponseService;
use App\Service\RequestContextService;

try {
	//
	$container 	= DIContainerBootstrap::create();
	$response 	= $container->get(ResponseService::class);
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
	$data 		= $container->get(DispatchService::class)->dispatch($instance, $router, $request);
	$response 	= $response->success($data);
} catch (\Throwable $th) {
	$response 	= $response->error($th);
}
$response->send();