<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

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