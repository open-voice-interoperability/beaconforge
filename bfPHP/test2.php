<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { // preflight (OPTIONS) requests
    http_response_code(200); // Send HTTP OK
    exit();
}

include 'simpleOVON.php'; // load simpleProcessOVON 

$inputOVON = json_decode( file_get_contents('php://input'), true); // JSON to PHP var
if (json_last_error() !== JSON_ERROR_NONE) { // Good decode?
    echo json_encode(['error' => json_last_error()]);
    exit;
}

echo json_encode( simpleProcessOVON($inputOVON, 'myManifest2.json' ) ); // return OVON
?>