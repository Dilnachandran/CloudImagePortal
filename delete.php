<?php
require 'vendor/autoload.php';
session_start();

// Initialize Google Client
$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Drive::DRIVE);
$client->setAccessType('offline');

// Check if user is authenticated
if (!isset($_SESSION['access_token'])) {
    header('Location: index.php');
    exit;
}

$client->setAccessToken($_SESSION['access_token']);

// Refresh the token if expired
if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    $_SESSION['access_token'] = $client->getAccessToken();
}


$driveService = new Google_Service_Drive($client);

// Get file ID 
if (isset($_GET['fileId'])) {
    $fileId = $_GET['fileId'];

    try {
        // Delete the file
        $driveService->files->delete($fileId);
        $_SESSION['message'] = "File deleted successfully!";
    } catch (Google_Service_Exception $e) {
        $_SESSION['error'] = "Error deleting file: " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
?>
