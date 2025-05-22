<?php
require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');
require_once(getenv("PROJECT_ROOT") . 'src/doctrine-em.php');

// Import Symfony Validator
use Symfony\Component\Validator\Validation;

echo "Starting database population...<br />";

// Create validator instance
$validator = Validation::createValidatorBuilder()
    ->enableAttributeMapping()
    ->getValidator();

// Create a new client specifically for these users
echo "Creating new client for test users...<br />";
$client = new \App\Entity\Client();
$client->init(['name' => 'Test Client ' . date('Y-m-d H:i:s')]);
$entityManager->persist($client);
$entityManager->flush();
echo "Created new client: " . $client->get('name') . " (ID: " . $client->get('id') . ")<br />";

// Create the registration service
$service = new App\Service\RegistrationService($entityManager, $validator);

// Array of users to create
$users = [
    // Admin users
    [
        'username' => 'admin1',
        'email' => 'admin1@example.com',
        'password' => 'admin1234',
        'role' => 'admin'
    ],
    [
        'username' => 'admin2',
        'email' => 'admin2@example.com',
        'password' => 'admin1234',
        'role' => 'admin'
    ],
    [
        'username' => 'admin3',
        'email' => 'admin3@example.com',
        'password' => 'admin1234',
        'role' => 'admin'
    ],
    
    // Moderator users
    [
        'username' => 'mod1',
        'email' => 'mod1@example.com',
        'password' => 'mod1234',
        'role' => 'moderator'
    ],
    [
        'username' => 'mod2',
        'email' => 'mod2@example.com',
        'password' => 'mod1234',
        'role' => 'moderator'
    ],
    [
        'username' => 'mod3',
        'email' => 'mod3@example.com',
        'password' => 'mod1234',
        'role' => 'moderator'
    ],
    
    // Regular users
    [
        'username' => 'user1',
        'email' => 'user1@example.com',
        'password' => 'user1234',
        'role' => 'user'
    ],
    [
        'username' => 'user2',
        'email' => 'user2@example.com',
        'password' => 'user1234',
        'role' => 'user'
    ],
    [
        'username' => 'user3',
        'email' => 'user3@example.com',
        'password' => 'user1234',
        'role' => 'user'
    ]
];

$createdCount = 0;
$adminCount = 0;
$modCount = 0;
$userCount = 0;
$errorCount = 0;

// Print header for user creation
echo "\nCreating users for client ID " . $client->get('id') . ":<br />";
echo "------------------------------------------------------<br />";
printf("%-15s %-25s %-15s %s<br />", "Username", "Email", "Role", "Status");
echo "------------------------------------------------------<br />";

// Register each user
foreach ($users as $userData) {
    $username = $userData['username'];
    $email = $userData['email'];
    $password = $userData['password'];
    $role = $userData['role'];

    try {
        // Create DTO
        $dto = new \App\DTO\UserRegistrationDTO($username, $email, $password, $client);
        
        // Register user
        $registration = $service->registration($dto);
        
        // Get user and manually set role and activate
        $user = $entityManager->getRepository(\App\Entity\User::class)
            ->findOneBy(['username' => $username, 'client' => $client]);
        
        if ($user) {
            // We need to modify the role and activate the user
            if ($role !== 'user') {
                // Set the role using reflection since there's no setter method
                $reflectionClass = new \ReflectionClass(\App\Entity\User::class);
                $property = $reflectionClass->getProperty('role');
                $property->setAccessible(true);
                $property->setValue($user, $role);
            }
            
            // Activate the user
            $user->activate();
            $entityManager->flush();
            
            // Track counts by role
            switch ($role) {
                case 'admin':
                    $adminCount++;
                    break;
                case 'moderator':
                    $modCount++;
                    break;
                case 'user':
                    $userCount++;
                    break;
            }
            
            $createdCount++;
            printf("%-15s %-25s %-15s %s<br />", $username, $email, $role, "Created & Activated");
        }
    } catch (\Exception $e) {
        $errorCount++;
        printf("%-15s %-25s %-15s %s<br />", $username, $email, $role, "ERROR: " . $e->getMessage());
    }
}

echo "\nDatabase population summary:<br />";
echo "------------------------------------------------------<br />";
echo "Total users created: $createdCount<br />";
echo " - Admin users: $adminCount<br />";
echo " - Moderator users: $modCount<br />";
echo " - Regular users: $userCount<br />";
if ($errorCount > 0) {
    echo "Errors encountered: $errorCount<br />";
}
echo "------------------------------------------------------<br />";
echo "\nYou can now log in with any of these users at the login page.<br />";
?>