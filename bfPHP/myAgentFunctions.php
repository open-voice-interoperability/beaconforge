<?php
// Author: Emmett Coin 2025
include 'baseAgentFunctions.php';
include 'simpleNLP.php';

class agentFunctions extends baseAgentFunctions {
    private $nlp;
    //private $contextObj; // for context maintanence

    public function __construct( $fileName ) {
        parent::__construct( $fileName ); // You  MUST KEEP this line
        // add any other thing you might want to init here
        $this->nlp = new SimpleNLP( 'intentConcepts.json' );
    }

    public function startUpAction() {
        // some code to initialize this.
        // e.g. read persistant data or set up llm

        // Do the following to retrieve an object from the last turn
        //$this->contextObj = $this->getPersistObject(); 
    }

    public function wrapUpAction() {
        // some code to finalize this.
        // e.g. save persistant data or do final llm post

        // Do the following to save the object for the next turn
        //$this->savePersistObject( $this->contextObj ); 
    }
    public function inviteAction() {
        $say = 'Hi, how can I help?';
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
        //The following was a private message just to you.
        $say = "I heard you whisper: $heard"; 
        $result = $this->nlp->simpleIntentFromText( $heard );
        $intents = $this->nlp->simpleIntent($result);
        if( $intents ){
            $say = 'I found some intents.'; // do something with them
        }
        return $say;
    }
}
?>