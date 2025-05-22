<?php
require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

// Import Symfony Validator
use Symfony\Component\Validator\Validation;

$message = '';
$error = '';
$success = false;

// Check if we have a token in the URL
$token = $_GET['token'] ?? '';

if ($token) {
    try {
        // Create validator instance
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        
        // Create registration service
        $service = new App\Service\RegistrationService($entityManager, $validator);
        
        // Activate the user
        $service->activation($token);
        
        // Display success message
        $message = "Your account has been successfully activated! You can now log in.";
        $success = true;
    } catch (Exception $e) {
        // Catch validation errors and other exceptions
        $error = "Error activating account: " . htmlspecialchars($e->getMessage());
    }
} else {
    $error = "No activation token provided. Please use the link from your activation email.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Activation</title>
    <style>
        .message { color: green; border: 1px solid green; padding: 10px; margin-bottom: 20px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Account Activation</h1>

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

    <?php if ($success): ?>
        <p>You can now <a href="login.php">log in</a> to your account.</p>
    <?php else: ?>
        <p>If you don't have an activation link, please check your email or <a href="registration.php">register again</a>.</p>
    <?php endif; ?>
</body>
</html>