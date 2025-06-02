<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');

use App\Bootstrap\DIContainerBootstrap;
use App\Exception\ApiException;
use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use App\Service\RequestContextService;
use Symfony\Component\HttpFoundation\JsonResponse;

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
	$data = $container->get(DispatchService::class)->dispatch($instance, $router, $request);
	$response = new JsonResponse([
		'success' => true, 
		'data' => $data
	], 200);
} catch (ApiException $th) {
    $errorResponse = [
        'success' 	=> false,
        'message' 	=> $th->getMessage()
    ];
	$errorResponse['debug'] = [
        'detail' 	=> $th->getDetail(),
		'file' 		=> $th->getFile(),
		'line' 		=> $th->getLine(),
		'trace' 	=> $th->getTraceAsString()
	];
    $response = new JsonResponse($errorResponse, $th->getHttpStatus());
} catch (\Throwable $th) {
    $errorResponse = [
        'success' 	=> false,
        'message' 	=> 'INTERNAL_SERVER_ERROR'
    ];
	$errorResponse['debug'] = [
		'detail' 	=> $th->getMessage(),
		'file' 		=> $th->getFile(),
		'line' 		=> $th->getLine(),
		'trace' 	=> $th->getTraceAsString()
	];
    $response = new JsonResponse($errorResponse, 500);
}

$response->send();