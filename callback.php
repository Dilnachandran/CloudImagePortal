<?php
// Load the Google API PHP client library
require 'vendor/autoload.php';

// Start a session to store tokens
session_start();

// Initialize Google Client
$client = new Google_Client();
$client->setAuthConfig('credentials.json'); // Path to your credentials.json
$client->addScope(Google_Service_Drive::DRIVE); // Set the scope (Google Drive access)
$client->setRedirectUri('http://localhost:8000/callback.php'); // Redirect URI must match your Google Console settings

// Check if we have an authorization code from the Google OAuth response
if (isset($_GET['code'])) {
    // Fetch the access token using the authorization code
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    // If there's an error while fetching the token
    if (isset($token['error'])) {
        echo "Error fetching access token: " . $token['error_description'];
        exit;
    }

    // Store the access token in the session
    $_SESSION['access_token'] = $token;

    // Redirect the user to the main page (e.g., image-upload.php)
    header('Location: image-upload.php');
    exit;
} else {
    echo "No authorization code found.";
}

// After getting the access token, you can fetch file details using its file ID
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    // Set the access token for the client
    $client->setAccessToken($_SESSION['access_token']);
    
    // Initialize Google Drive API service
    $service = new Google_Service_Drive($client);
    
    // Define the file ID (You need to get this from the user or URL)
    $fileId = isset($_GET['fileId']) ? $_GET['fileId'] : null;  // Replace this with actual logic to get fileId
    
    if ($fileId) {
        try {
            // Get file details by fileId
            $file = $service->files->get($fileId);
            
            // Output file details (You can customize this based on what details you need)
            echo "File Name: " . $file->getName() . "<br>";
            echo "File ID: " . $file->getId() . "<br>";
            echo "Mime Type: " . $file->getMimeType() . "<br>";
            echo "Web View Link: " . $file->getWebViewLink() . "<br>";
        } catch (Google_Service_Exception $e) {
            echo "Error fetching file details: " . $e->getMessage();
        }
    } else {
        echo "File ID not provided.";
    }
} else {
    echo "User is not authenticated.";
}
