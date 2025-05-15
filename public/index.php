<?php
require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $device = 'DEVICE_DEV';

    // $dql = "SELECT c FROM App\Entity\Client c ORDER BY c.id DESC"; // Example: Get the "default" or last client
    // $query = $entityManager->createQuery($dql);
    // $query->setMaxResults(1);
    // $clientResult = $query->getResult();
    // $requestingClient = $clientResult[0];

    $authDto = new \App\DTO\UserAuthDTO($email, $password, $device);
    // Validate
    $service = new \App\Service\AuthenticationService($entityManager);
    $authenticatedUser = $service->authenticate($authDto);

    if ($authenticatedUser) {
        $message = "Login successful for: " . htmlspecialchars($authenticatedUser->get('name'));
    } else {
        $message = "Login failed. Invalid email or password.";
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
            <?= htmlspecialchars($message) ?>
        </div>
    <?php elseif ($message): ?>
        <div class="message error">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div><label for="email">Email:</label><br><input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? 'jotaerre@email.com') ?>"></div><br>
        <div><label for="password">Password:</label><br><input type="password" id="password" name="password" required value="<?= htmlspecialchars($_POST['password'] ?? '1234') ?>"></div><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>