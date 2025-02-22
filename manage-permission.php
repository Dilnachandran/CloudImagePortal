<?php
require 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE);

if (!isset($_SESSION['access_token'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

$client->setAccessToken($_SESSION['access_token']);
$driveService = new Google_Service_Drive($client);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileId = $_POST['file_id'];
    $email = $_POST['email'];
    $action = $_POST['action'];

    if ($action == 'grant') {
        $permission = new Google_Service_Drive_Permission([
            'type' => 'user',
            'role' => 'reader',
            'emailAddress' => $email
        ]);
        $driveService->permissions->create($fileId, $permission);
        echo "Access granted to $email.";
    } elseif ($action == 'revoke') {
        $permissions = $driveService->permissions->listPermissions($fileId);
        foreach ($permissions as $perm) {
            if ($perm->getEmailAddress() === $email) {
                $driveService->permissions->delete($fileId, $perm->getId());
                echo "Access revoked from $email.";
            }
        }
    }
}
?>

<form method="POST">
    <input type="text" name="file_id" placeholder="File ID" required>
    <input type="email" name="email" placeholder="Google Account Email" required>
    <select name="action">
        <option value="grant">Grant Access</option>
        <option value="revoke">Revoke Access</option>
    </select>
    <button type="submit">Submit</button>
</form>
