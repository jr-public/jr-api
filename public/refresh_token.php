<?php

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

$dql = "SELECT c FROM App\Entity\Client c ORDER BY c.id DESC";
$query = $entityManager->createQuery($dql);
$query->setMaxResults(1);
$clientResult = $query->getResult();
$requestingClient = $clientResult[0];
$requestingDevice = "DEV_DEVICE";

try {
	session_start();
	if (!isset($_SESSION['jotaerre_token'])) {
		throw new \Exception('Unauthorized access.');
	}
	$auth = new App\Service\AuthService($entityManager);
	$claims = [
		'iss' => $requestingClient->get('id'),
		'dev' => $requestingDevice,
	];
	$active_user = $auth->authorize($_SESSION['jotaerre_token'], $claims);
	define('ACTIVE_USER', $active_user);
} catch (\Throwable $th) {
	header("Location: index.php?error=".$th->getMessage());
	die();
}


try {
    $jwt_s = new App\Service\JWTService();
    $token = $jwt_s->refreshToken($_SESSION['jotaerre_token']);
    $_SESSION['jotaerre_token'] = $token;
} catch (\Throwable $th) {
	header("Location: index.php?error=".$th->getMessage());
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Refresh Token</title>
</head>
<body>
    <h1>Token Refreshed</h1>
    <p>Your token has been successfully refreshed.</p>
    <p style='word-break: break-all;'x><?= htmlspecialchars($token) ?></p>
    <p><a href="user_list.php">Go to User List</a></p>
</body>
</html>
