<?php
require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DEV GETTING CLIENT
    $dql = "SELECT c FROM App\Entity\Client c ORDER BY c.id DESC";
    $query = $entityManager->createQuery($dql);
    $query->setMaxResults(1);
    $clientResult = $query->getResult();
    $requestingClient = $clientResult[0];
    $requestingDevice = "DEV_DEVICE";

    try {
        $auth_s = new \App\Service\AuthService($entityManager);
        $claims = [
            'iss' => $requestingClient->get('id'),
            'dev' => $requestingDevice,
        ];
        $auth   = $auth_s->login($_POST['username'] ?? '', $_POST['password'] ?? '', $claims);
        session_start();
        $_SESSION['jotaerre_token'] = $auth['token'];
        if ( $auth['user']['reset_password'] ) {
            header("Location: reset_password.php?id=" . $auth['user']['id']);
            die();
        }
        header("Location: user_list.php");
        die();
    } catch (\Throwable $th) {
        $message = "Login failed.<br />" . $th->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .error { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
    </style>
</head>
<body>
    <h1>User Login</h1>
    <?php if ($message && str_contains($message, "successful")): ?>
        <div class="message success">
            <?= $message ?>
        </div>
    <?php elseif ($message): ?>
        <div class="message error">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div><label for="username">username:</label><br><input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? 'jotaerre') ?>"></div><br>
        <div><label for="password">Password:</label><br><input type="password" id="password" name="password" required value="<?= htmlspecialchars($_POST['password'] ?? '1234') ?>"></div><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>