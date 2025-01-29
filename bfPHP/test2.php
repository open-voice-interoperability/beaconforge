<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight requests (OPTIONS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200); // Send HTTP 200 OK
    exit();
}

include 'simpleOVON.php'; // Include file containing simpleProcessOVON 

$agFunc = new agentFunctions( 'myManifest2.json' );

$jsonInput = file_get_contents('php://input'); // get raw POST data
$inputOVON = json_decode($jsonInput, true); // Decode JSON string to PHP variable
if (json_last_error() !== JSON_ERROR_NONE) { // Good decode?
    echo json_encode(['error' => json_last_error()]);
    exit;
}
$response = simpleProcessOVON($inputOVON, $agFunc );
echo json_encode($response); // return the OVON response
?>