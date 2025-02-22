<?php
require 'vendor/autoload.php'; // Load Google API client library

session_start(); // Start PHP session to store tokens

$client = new Google_Client();
$client->setAuthConfig('credentials.json'); // Path to your credentials.json
$client->addScope(Google_Service_Drive::DRIVE); // Set necessary scopes
$client->setRedirectUri('http://localhost/callback.php'); // Redirect URI should match Google Console settings

// Check if the authorization code is returned
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']); // Exchange auth code for an access token

    if (isset($token['error'])) {
        echo "Error fetching access token: " . $token['error_description'];
        exit;
    }

    // Store the access token in the session
    $_SESSION['access_token'] = $token;

    // Redirect to the main page (e.g., upload.php)
    header('Location: image-upload.php');
    exit;
} else {
    echo "No authorization code found.";
}
?>
