<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { // preflight (OPTIONS) requests
    http_response_code(200); // Send HTTP OK
    exit();
}

// Author: Emmett Coin 2025

// You can make a different version of the agent functions
//   and it will be included in the simpleProcessOVON
global $agentFunctionsFileName;
global $pathForPersistantStorage;

$agentFunctionsFileName = 'myAgentFunctions.php'; // this works for testing
$pathForPersistantStorage = '../../private/'; // Where the persistant files a written
$agentDefinitionJSON = 'myManifest2.json'; // AgentDefinition JSON


include 'simpleOVON.php'; // load simpleProcessOVON 

$inputOVON = json_decode( file_get_contents('php://input'), true); // JSON to PHP var
if (json_last_error() !== JSON_ERROR_NONE) { // Good decode?
    echo json_encode(['error' => json_last_error()]);
    exit;
}

echo json_encode( simpleProcessOVON($inputOVON, $agentDefinitionJSON ) ); // return OVON
?>