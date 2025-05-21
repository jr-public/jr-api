<?php

use App\Service\JWTService;

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

$message = '';
$error = '';

// Handle POST requests first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$user = $entityManager->find("App\Entity\User", $_POST['user_id']);
	if (!$user) {
		header("Location: index.php?error=User not found");
		die();
	}
	$user->resetedPassword($_POST['new_password']);
    $entityManager->flush();
    header("Location: index.php");
    die();
}

if ( !isset($_GET['id']) ) {
	header("Location: index.php?error=Missing user ID for password reset");
	die();
}

if ( isset($_GET['id']) && isset($_GET['token']) ) {
	$jwt_s = new JWTService();
	$decoded = $jwt_s->validateToken($_GET['token']);
	if (!$decoded) {
		header("Location: reset_password.php?id=".$_GET['id']."&error=Invalid token for password reset");
		die();
	}
	elseif ($decoded->sub != $_GET['id']) {
		header("Location: reset_password.php?id=".$_GET['id']."&error=Invalid token");
		die();
	}
}

// Handle GET requests
$userId = $_GET['id'];
$token = $_GET['token'] ?? '';


$jwt_s  = new JwtService();
$claims = [
	'sub' => $userId
];
$cheat_token  = $jwt_s->createToken($claims);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
        .message { 
            padding: 10px; 
            margin-bottom: 20px; 
            border-radius: 4px; 
        }
        .success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; word-wrap: break-word; }
        .error { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; }
    </style>
</head>
<body>
    <h1>Password Reset</h1>

    <?php if ($message): ?>
        <div class="message success">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if (empty($token)): ?>
        <div class="message success">
		    <?= $cheat_token ?>
        </div>
		<form method="POST" id="token_paste" action="">
            <label for="token">Token:</label>
            <input type="text" name="token" required>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
    <?php else: ?>
        <form method="POST" action="">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
    <?php endif; ?>



</body>
<script>
	document.getElementById('token_paste').onsubmit = function(e) {
		e.preventDefault();
		const token = document.querySelector('input[name="token"]').value;
		window.location.href = `reset_password.php?id=<?= htmlspecialchars($userId) ?>&token=${token}`;
		return;
	}
</script>
</html>