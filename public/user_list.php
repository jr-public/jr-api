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
	die($th->getMessage());
}

$messages = [];

if ( isset($_GET['action']) ) {
	try {
		$ums = new App\Service\UserManagementService($entityManager, ACTIVE_USER);
		$t_user = $entityManager->find('App\Entity\User', $_GET['id']);
		if (!$t_user) {
			throw new \Exception('Target user not found.');
		}
		switch ($_GET['action']) {
			case 'block':
				$ums->blockUser($t_user);
			break;
			case 'unblock':
				$ums->unblockUser($t_user);
			break;
			case 'activate':
				$t_user->activate();
				$entityManager->flush();
			break;
			default:
			break;
		}
	} catch (\Throwable $th) {
		$messages[] = $th->getMessage();
	}
}
try {
	$dql = "SELECT u FROM App\Entity\User u WHERE u.client = :client ORDER BY u.id ASC";
	$users = $entityManager->createQuery($dql);
	$users->setParameter("client", ACTIVE_USER->get('client')->get('id'));
	$users = $users->getArrayResult();
} catch (\Throwable $th) {
	$users = [];
	$messages[] = $th->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-active {
            color: green;
        }
        .status-pending {
            color: orange;
        }
        .status-blocked {
            color: red;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <h1>User Management</h1>
    
    <?php if (!empty($messages)): ?>
        <div class="message success">
            <?= implode('<br /><br />', $messages) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td class="status-<?= htmlspecialchars($user['status']) ?>"><?= htmlspecialchars($user['status']) ?></td>
                    <td>
                        <select name="action" onchange="if(this.value) window.location.href=this.value;">
                            <option value="">Select Action</option>
                            <?php if ($user['status'] === 'active'): ?>
                                <option value="?action=block&id=<?= $user['id'] ?>">Block</option>
                            <?php elseif ($user['status'] === 'blocked'): ?>
                                <option value="?action=unblock&id=<?= $user['id'] ?>">Unblock</option>
                            <?php elseif ($user['status'] === 'pending'): ?>
                                <option value="?action=activate&id=<?= $user['id'] ?>">Activate</option>
                            <?php endif; ?>
                            <option value="?action=delete&id=<?= $user['id'] ?>">Delete</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="registration.php">Register New User</a> | <a href="index.php">Login Page</a></p>
</body>
</html>