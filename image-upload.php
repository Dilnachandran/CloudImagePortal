<?php
require 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE); // Add the DRIVE_FILE scope
$client->setAccessType('offline');

if (!isset($_SESSION['access_token'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

$client->setAccessToken($_SESSION['access_token']);

// Refresh the token if it's expired
if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    $_SESSION['access_token'] = $client->getAccessToken();
}

$driveService = new Google_Service_Drive($client);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['image']['name']);
    $file->setParents(['1DAhA-K2jxmb_F-ETSRWhDTwfbIy7pu1A']); // Replace with your folder ID

    $fileData = file_get_contents($_FILES['image']['tmp_name']);
    try {
        $createdFile = $driveService->files->create($file, [
            'data' => $fileData,
            'mimeType' => $_FILES['image']['type'],
            'uploadType' => 'multipart',
            'supportsAllDrives' => true
        ]);
        echo "Image uploaded successfully!";
    } catch (Google_Service_Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" required>
    <button type="submit">Upload Image</button>
</form>
