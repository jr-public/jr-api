<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');
header('Content-Type: application/json');

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400) {
    sendResponse(['error' => $message], $status);
}

try {
	echo $_SERVER['REQUEST_URI'];
	die();
    $path = getenv("PROJECT_ROOT") . "/" . $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
	echo $path;
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
