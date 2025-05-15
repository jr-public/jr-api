<?php
require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dql = "SELECT c FROM App\Entity\Client c ORDER BY c.id DESC";
    $query = $entityManager->createQuery($dql);
    $query->setMaxResults(1);
    $result = $query->getResult();
    $Client = $result[0];

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $dto = new \App\DTO\UserRegistrationDTO($name, $email, $password, $Client);
        // VALIDATE!
        $service = new App\Service\RegistrationService($entityManager);
        $user = $service->registration($dto);
        // 5. Display success message or user details
        $message = "User object created successfully in memory!<br>";
        $message .= "Name: " . htmlspecialchars($user->get('name')) . "<br>";
        $message .= "Email: " . htmlspecialchars($user->get('email')) . "<br>";
        $message .= "Password: " . htmlspecialchars($user->get('password')) . "<br>";
        $message .= "Client Name: " . htmlspecialchars($user->get('client')->get('name')) . "<br>";
        $message .= "Created At: " . $user->get('created')->format('Y-m-d H:i:s') . "<br>";
    } catch (Exception $e) {
        // Catch potential errors during object creation
        $error = "Error creating user object: " . htmlspecialchars($e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        .message { color: green; border: 1px solid green; padding: 10px; margin-bottom: 20px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Register New User</h1>

    <?php if ($message): ?>
        <div class="message">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div>
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? 'jotaerre') ?>">
        </div>
        <br>
        <div>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? 'jotaerre@email.com') ?>">
        </div>
        <br>
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '1234') ?>" required>
        </div>
        <br>
        <button type="submit">Register</button>
    </form>
</body>
</html>