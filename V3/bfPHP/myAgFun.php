<?php
// Author: Emmett Coin 2026

class agentFunctions extends baseAgentFunctions {
    private $myPersistantDataObject;

    public function __construct( $fileName ) {
        parent::__construct( $fileName ); // You  MUST KEEP this line
        // add any other thing you might want to init here
    }

    public function startUpAction() {
        parent::startUpAction(); // You MUST call this to restore the persist object
        // Use the persistant object to your code
        // it is persisted as a JSON string in the file
        // Structure this as you like.
        if( $this->persistObject == null ){ // first time, so init it
            $this->persistObject = [
            'exchanges' => []
            ];
        }
    }
    
    public function wrapUpAction() {
        // Do whatever needs doing before leaving
        // REMEMBER: persistObject will be stringified and saved
        parent::wrapUpAction(); // You MUST call this to save the persist object
    }

    public function inviteAction( $reason ) {
        $say = 'Thankyou for the invitation. got any crackers?';
        // Note: if $reason=="@sentinal" then modify your behavior accordingly
        return $say;
    }

    public function utteranceAction( $heard, $fromUri, $directedToMe, $directedToSomeoneElse ) {
        // To be polite you may want to respond ONLY if $directedToMe is true.
        // You may want to avoid responding if it is meant for someone else
        if( strlen( $heard ) <1 ){
            $heard = "nothing";
        }
        if( $directedToMe ){ // it was meant for me, so respond regardless.
            $say = 'Thanks for referencing me! I heard you say ' . $heard . '. Polly wants a cracker!';
        }else if( $directedToSomeoneElse ){// You may want to avoid responding here
            $say = '';
        }else{ // Must be directed to everyone
            $say = 'I still need a cracker, but I heard '. $heard . ' and I am not sure if it was meant for me.';
        }
        // keep track of what you heard in the persistant object
        $this->persistObject['exchanges'][] = ["utt" => $heard, "from" => $fromUri, "toMe" => $directedToMe, "toSomeoneElse" => $directedToSomeoneElse, "said" => $say];

    return $say;
    }
}
?>