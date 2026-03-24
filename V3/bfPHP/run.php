<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {// preflight OPTIONS requests
    http_response_code(200); // Send HTTP 200 OK
    exit();
}

// Author: Emmett Coin 2026

global $agentFunctionsFileName;
global $pathForPersistantStorage;

$agentFunctionsFileName = 'myAgFun.php'; // Include agent specific code
$agentDefinitionJSON = 'agDef.json'; // AgentDefinition JSON

// Modify the following 2 code lines to use your directory INSTEAD of "ejOFP'
//      -You must create this directory parallel to your server "public" directory
//      -This PRESUMES that this run.php file is under your "public" dir nested like:
//          public/ov1/ag/agent99/run.php (along with "myAgfun.php" & "agDef.json")
//$pathForPersistantStorage = '../../../../ejOFP/'; // Where the persistant files are written
$pathForPersistantStorage = ''; // Where the persistant files are written
$pathForCommonPHP = $pathForPersistantStorage . 'beaconforgeV3.php'; // common code
//  Persistent data exists in a subdirectory "conversationalName" (from the agent manifest)
//      e.g. '../../../ejOFP/Polly/'
include $pathForCommonPHP; // load basic functionality

$inputOpenfloor = json_decode( file_get_contents('php://input'), true); // JSON to PHP var
if (json_last_error() !== JSON_ERROR_NONE) { // Good decode?
    echo json_encode(['error' => json_last_error()]);
    exit;
}

echo json_encode( simpleProcessOFP($inputOpenfloor, $agentDefinitionJSON ) ); // return OFP
?>