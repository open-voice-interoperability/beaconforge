<?php
// Author: Emmett Coin 2025
class SimpleNLP {
    private $myConcepts = null;

    public function __construct( $fileName ) {
        $pData = file_get_contents( $fileName );
        if( strlen($pData) > 40 ){
            $this->myConcepts = json_decode($pData, true); // ready to use
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo 'json_decode error: ' . json_last_error_msg();
            }
        }else{ // not a good file so rebuild the LLM agent
            echo 'no file for intents was found';
        }

    }

    public function simpleIntentFromText($inputMessage) {
        $matchedConcepts = [];
        $message = ' ' . preg_replace('/[^\w\s]/', ' ', $inputMessage) . ' ';
        $message = strtolower($message);
        $words = '';

        if ($this->myConcepts) {
            foreach ($this->myConcepts['concepts'] as $concept) {
                $matchedWords = array_filter($concept['examples'], function($word) use ($message) {
                    $spacedWord = ' ' . strtolower($word) . ' ';
                    return strpos($message, $spacedWord) !== false;
                });

                if (!empty($matchedWords)) {
                    foreach ($matchedWords as $value) { // only way to stop from getting the index also
                        $words = $value;
                    }
                    $matchedConcepts[] = [
                        "concept" => $concept['name'],
                        "matchedWords" => $words
                    ];
                }
            }
        }
        return $matchedConcepts;
    }

    public function simpleIntent($conceptJSON) {
        $concept = "";
        $intent = [
            "return" => false,
            "assistantName" => "",
            "repeatLastUtt" => false,
            "manifest" => false
        ];

        if ($conceptJSON) {
            foreach ($conceptJSON as $conceptData) {
                $concept = $conceptData['concept'];
                if ($concept === "return") {
                    $intent["return"] = true;
                } else if ($concept === "delegate") {
                    $intent["redirect"] = $concept;
                } else if ($concept === "assistantName") {
                    $intent["assistantName"] = $conceptData['matchedWords'];
                } else if ($concept === "repeatLastUtt") {
                    $intent["repeatLastUtt"] = true;
                } else if ($concept === "manifest") {
                    $intent["manifest"] = true;
                }
            }
        }
        return $intent;
    }
}
?>