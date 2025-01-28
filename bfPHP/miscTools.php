<?php
// Define the file path
//$filePath = 'example.txt';
function writeToFile($filePath, $content) {
    // Open the file for writing; creates the file if it doesn't exist
    $fileHandle = fopen($filePath, 'w');
    if ($fileHandle) {
        fwrite($fileHandle, $content); // Write content to file
        fclose($fileHandle);
    } else {
        echo "Failed to open the file for writing.\n";
    }
}

function readFromFile($filePath) {
    if (file_exists($filePath)) { // file exists?
        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle) {
            $content = fread($fileHandle, filesize($filePath));
            fclose($fileHandle);
            return $content;
        } else {
            return "Failed to open the file for reading.";
        }
    } else {
        return "File does not exist.";
    }
}

function readJSONFromFile($filePath) {
    if (file_exists($filePath)) { // file exists?
        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle) {
            $content = fread($fileHandle, filesize($filePath));
            fclose($fileHandle);
            $data = json_decode($content, true); // Decode JSON to PHP variable
            if (json_last_error() !== JSON_ERROR_NONE) { // Good decode?
                return "JSON Decode Error";
            }
            return $data;
        } else {
            return "Failed to open the file for reading.";
        }
    } else {
        return "File does not exist.";
    }
}
class FileIO {
    private $path;

    public function setPath($path) {
        $this->path = $path;
    }

    public function ejReadFile($fileName, $type, $isArray = true) {
        $filePath = $this->path . $fileName;
        if (!file_exists($filePath)) {
            throw new Exception("File not found: " . $filePath);
        }
        $fileContents = file_get_contents($filePath);
        if ($type === 'json') {
            return json_decode($fileContents, $isArray);
        }
        return $fileContents;
    }
}

?>