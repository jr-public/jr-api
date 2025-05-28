<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
// require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

use App\Bootstrap\DoctrineBootstrap;
use App\Bootstrap\DIContainerBootstrap;
use App\Service\AuthService;
use App\Service\DispatchService;
use App\Service\RouterService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

try {
	//
	$entityManager = DoctrineBootstrap::create();
	//
	$request	= Request::createFromGlobals();
	$request->attributes->set('device', $request->headers->get('User-Agent'));
	//
	// echo $request->getContent();
	// die();
	// print_r(getallheaders());
	// die();
	// echo $request->headers->get('origin');
	// echo "<br />";
	// echo $request->getHost();
	// die();
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
	$validator = Validation::createValidatorBuilder()
		->enableAttributeMapping()
		->getValidator();
	$dispatch 	= new DispatchService($entityManager, $user, $validator);
	$response 	= $dispatch->dispatch($routeInfo, $request);
	echo $response;
} catch (\Throwable $th) {
    http_response_code($th->getCode() ?: 500);
    echo json_encode(['error' => $th->getMessage()]);
}
