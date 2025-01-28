<?php
include 'simpleNLP.php';

class agentFunctions {
    private $nlp;
    private $agent;
    private $URL;
    private $speakerId;
    private $manifest;
    private $callerId = 'unknown';
    private $persistFileName = '';
    private $persistObject = null;

    public function __construct( $fileName ) {
        $this->$nlp = new SimpleNLP( 'intentConcepts.json' );
        $this->$agent = readJSONFromFile( $agentFileName );
        $this->$URL = $agent['manifest']['identification']['serviceEndpoint'];
        $this->$speakerId = $agent['manifest']['identification']['speakerId'];
        $this->$manifest = $agent['manifest'];
    }

    public function inviteAction( $heard ) {
        $say = "Hello, how can I help?"; 
        return $say;
    }

    public function utteranceAction( $heard ) {
        $say = "I heard you ask: $heard"; 
        $result = $nlp->ejSimpleIntentFromText($inputText);
        $intents = $nlp->ejSimpleIntent($result);
        if( $intents ){
            if( $intents['return'] === true ){
                if( strlen($intents['assistantName']) > 0 ){
                    $say = "Okay, back to " . $intents['assistantName']; 
                }else{
                    $say = "Sure."; 
                }
                $say .= " <<<EMBED_action=return:convener>>>"; // add the embedded 'return' action
            }
        }
        return $say;
    }

    public function whisperAction( $heard ) {
        $say = "I heard you ask: $heard"; 
        $result = $nlp->ejSimpleIntentFromText($inputText);
        $intents = $nlp->ejSimpleIntent($result);
        if( $intents ){
            $say = 'I found some intents.'; // do something with them
        }
        return $say;
    }

    public function getURL() {
        return $this->$URL;
    }

    public function getManifest() {
        return $this->$manifest;
    }

    public function getSpeakerId() {
        return $this->$speakerId;
    }
}
?>