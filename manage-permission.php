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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
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

// Fetch and display images
$folderId = '1DAhA-K2jxmb_F-ETSRWhDTwfbIy7pu1A'; // Replace with your correct folder ID
$query = "'$folderId' in parents and trashed = false";

try {
    $files = $driveService->files->listFiles([
        'q' => $query,
        'fields' => 'files(id, name, thumbnailLink)',
        'supportsAllDrives' => true,
        'includeItemsFromAllDrives' => true
    ]);
} catch (Google_Service_Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>

<h2>Select an Image to Manage Permissions</h2>
<form method="POST">
    <select name="file_id" required>
        <?php foreach ($files->getFiles() as $file): ?>
            <option value="<?php echo $file->getId(); ?>">
                <?php echo $file->getName(); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="email" name="email" placeholder="Google Account Email" required>
    <select name="action">
        <option value="grant">Grant Access</option>
        <option value="revoke">Revoke Access</option>
    </select>
    <button type="submit">Submit</button>
</form>

<h2>Images</h2>
<div>
    <?php foreach ($files->getFiles() as $file): ?>
        <div>
            <h3><?php echo $file->getName(); ?></h3>
            <img src="<?php echo $file->getThumbnailLink(); ?>" alt="<?php echo $file->getName(); ?>" />
        </div>
    <?php endforeach; ?>
</div>
