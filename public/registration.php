<?php
require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

// Import Symfony Validator
use Symfony\Component\Validator\Validation;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dql = "SELECT c FROM App\Entity\Client c ORDER BY c.id DESC";
    $query = $entityManager->createQuery($dql);
    $query->setMaxResults(1);
    $result = $query->getResult();
    $Client = $result[0];

    $username = $_POST['username'] ?? '';  // This should be changed to 'username' in the form
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Create validator instance
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        // Create DTO
        $dto = new \App\DTO\UserRegistrationDTO($username, $email, $password, $Client);
        
        // Create service with both entity manager AND validator
        $service = new App\Service\RegistrationService($entityManager, $validator);
        
        // Register user
        $registration = $service->registration($dto);
        $user = $registration['user'];
        // Display success message
        $message = "User object created successfully!<br>";
        $message .= "Username: " . htmlspecialchars($user['username']) . "<br>";
        $message .= "Client Name: " . htmlspecialchars($user['client']['id']) . "<br>";
        $message .= "Created At: " . $user['created']->format('Y-m-d H:i:s') . "<br>";
        $message .= "Token: " . htmlspecialchars($registration['token']) . "<br>";
        $message .= "<a href='activation.php?token=" . htmlspecialchars($registration['token']) . "'>Activate Account</a>";

    } catch (Exception $e) {
        // Catch validation errors and other exceptions
        $error = "Error creating user: " . htmlspecialchars($e->getMessage());
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
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? 'jotaerre') ?>">
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
    <p>
        <a href="activation.php">Activate Account</a> |
        <a href="login.php">Login</a> |
        <a href="forgot_password.php">Forgot Password?</a>
    </p>
</body>
</html>