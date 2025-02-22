<?php
require 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE_READONLY);

if (!isset($_SESSION['access_token'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

$client->setAccessToken($_SESSION['access_token']);
$driveService = new Google_Service_Drive($client);

$folderId = 'YOUR_FOLDER_ID'; // Replace with your folder ID
$query = "'$folderId' in parents and trashed = false";

$files = $driveService->files->listFiles([
    'q' => $query,
    'fields' => 'files(id, name, thumbnailLink, webContentLink)'
]);

foreach ($files->getFiles() as $file) {
    echo "<div>
        <h3>{$file->getName()}</h3>
        <img src='{$file->getThumbnailLink()}' alt='{$file->getName()}' />
    </div>";
}
?>
