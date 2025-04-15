<?php
// Author: Emmett Coin 2025
include 'miscTools.php';

class baseAgentFunctions {
    protected $agent;
    private $URL;
    private $speakerId;
    private $manifest;
    protected $persistFileName = '';
    protected $persistObject = null;
    private $usePersistObject = false;
    protected $ovonTool = null;
    private $convoId = '';
    private $agentJSONFile = '';

    public function startUpAction() {
        // some code to initialize this.
        // e.g. read persistant data or set up llm
    }

    public function wrapUpAction() {
        // some code to finalize this.
        // e.g. save persistant data or do final llm post
    }
    public function inviteAction() {
        $say = 'Hi, how can I help?';
        // This is what you say upon your invitation to the conversation
        return $say;
    }

    public function utteranceAction( $heard ) {
        $say = 'I heard: ' . $heard;
        // This is a public message sent to everyone on the conversation
        // Do your thing with $heard and create a $say
        return $say;
    }

    public function whisperAction( $heard ) {
        $say = 'I heard: ' . $heard;
        // This is a private message sent only to you
        // Do your thing with $heard and create a $say
        return $say;
    }

    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // The above functions will be overloaded in your EXTENDED class
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

    public function __construct( $fileName ) {
        $this->agentJSONFile = $fileName;
        $this->agent = readJSONFromFile( $fileName );
        $this->URL = $this->agent['manifest']['identification']['serviceEndpoint'];
        $this->speakerId = $this->agent['manifest']['identification']['speakerId'];
        $this->manifest = $this->agent['manifest'];
    }

    public function shareOVONmsg( $oms ) {
        $this->ovonTool = $oms;
    }

    public function usePersist( $relativePath ){
        // $relativePath [str e.g. '../../private/']
        $this->usePersistObject = true;
        $cleanName = $this->agent['manifest']['identification']['conversationalName'];
        $cleanName .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $this->convoId);
        $this->persistFileName = $relativePath . $cleanName . '.txt';
    }
     
    public function getPersistObject(){
        // this will return null if not there so you must build one
        $this->persistObject = null;
        if( $this->usePersistObject ){
            $this->persistObject = readJSONFromFile( $this->persistFileName );
        }
        return $this->persistObject;
    }
     
    public function savePersistObject( $pObject ){
        // this will save the object to a file for the next turn
        if( $this->usePersistObject && ($pObject != null) ){
            $strJSON = json_encode( $pObject );
            file_put_contents( $this->persistFileName, $strJSON );
        };
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
}
?>