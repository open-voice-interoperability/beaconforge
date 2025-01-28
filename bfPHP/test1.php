<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
// NOTE: The previous 2 lines MUST be the first 2 lines in the PHP
//    script that returns the result.

$test = 'Your server can process PHP scripts.';
echo $test; // return the response
?>