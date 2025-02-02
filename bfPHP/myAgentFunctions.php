<?php
// Author: Emmett Coin 2025
include 'simpleNLP.php';
include 'miscTools.php';

class agentFunctions {
    private $nlp;
    private $agent;
    private $URL;
    private $speakerId;
    private $manifest;
    private $persistFileName = '';
    private $persistObject = null;
    private $ovonTool = null;
    private $convoId = '';
    private $agentJSONFile = '';

    public function __construct( $fileName ) {
        $this->agentJSONFile = $fileName;
        $this->nlp = new SimpleNLP( 'intentConcepts.json' );
        $this->agent = readJSONFromFile( $fileName );
        $this->URL = $this->agent['manifest']['identification']['serviceEndpoint'];
        $this->speakerId = $this->agent['manifest']['identification']['speakerId'];
        $this->manifest = $this->agent['manifest'];
    }

    public function shareOVONmsg( $oms ) {
        $this->ovonTool = $oms;
    }

    public function startUpAction( $heard ) {
        // some code to initialize this.
        // e.g. read persistant data or set up llm
    }

    public function wrapUpAction( $heard ) {
        // some code to finalize this.
        // e.g. save persistant data or do final llm post
    }
    public function inviteAction() {
        $say = "Hello, how can I help?"; 
        return $say;
    }

    public function utteranceAction( $heard ) {
        $say = "I heard you ask: $heard"; 
        $result = $this->nlp->simpleIntentFromText( $heard );
        $intents = $this->nlp->simpleIntent($result);
        if( $intents ){
            if( $intents['return'] === true ){
                if( strlen($intents['assistantName']) > 0 ){
                    $say = "Okay, back to " . $intents['assistantName']; 
                }else{
                    $say = "Sure."; 
                }
                $say .= " <<<EMBED_action=return:convener>>>"; // add the embedded 'return' action
            }elseif( $intents['manifest'] === true ){
                if( $this->ovonTool != null ){
                    $this->ovonTool->buildManifestReply( $this->manifest );
                    $say = "Sure. Here it is!";
                }
            }
        }
        return $say;
    }

    public function whisperAction( $heard ) {
        $say = "I heard you ask: $heard"; 
        $result = $this->nlp->simpleIntentFromText( $heard );
        $intents = $this->nlp->simpleIntent($result);
        if( $intents ){
            $say = 'I found some intents.'; // do something with them
        }
        return $say;
    }

    public function getURL() {
        return $this->URL;
    }

    public function getManifest() {
        return $this->manifest;
    }

    public function getSpeakerId() {
        return $this->speakerId;
    }

    public function setConvoId( $someId ) {
        $this->convoId = $someId;
    }

    public function setPersistFileName( $pFileName ) {
        $this->persistFileName = $pFileName;
    }
}
?>