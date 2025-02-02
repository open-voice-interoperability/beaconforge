<?php
// Author: Emmett Coin 2025
global $agentFunctionsFileName;

if (file_exists($agentFunctionsFileName)) {
    include $agentFunctionsFileName;
} else {
    echo "The file '$agentFunctionsFileName' does not exist.";
}

function simpleProcessOVON($inputData, $agentFileName ) {
    $agFun = new agentFunctions( $agentFileName );
    $oms = new ovonMessages( $inputData, $agFun );
    $agFun->shareOVONmsg( $oms );
    $mySpeakerId = $agFun->getSpeakerId();
    $myURL = $agFun->getURL();
    $agFun->startUpAction();

    if (isset($inputData['ovon']['events'])) { // is this the expected OVON?
        foreach ($inputData['ovon']['events'] as $event) { // Loop to find "invite"
            //if ( $mySpeakerId === $event['to'] || $myURL === $event['to']){ 
                if ($event['eventType'] === 'invite') {
                    $say = $agFun->inviteAction();
                    $oms->buildUttReply( $say );
                }
            //}
        }
        foreach ($inputData['ovon']['events'] as $event) {
            // ONLY respond to things directed to you
            if ( $mySpeakerId === $event['to'] || $myURL === $event['to']){ 
                if ($event['eventType'] === 'utterance') {
                    $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    $say = $agFun->utteranceAction( $heard );
                    $oms->buildUttReply( $say );
                }elseif ($event['eventType'] === 'whisper') {
                    $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    $say = $agFun->whisperAction( $heard ); // This was private message
                    $oms->buildWhispReply( $say );
                }elseif ($event['eventType'] === 'requestManifest') {
                    $manifest = $agFun->getManifest();
                    $oms->buildManifestReply( $manifest);
                    $oms->buildUttReply( "Manifest sent." );
                }
            }
        }
    }
    $agFun->wrapUpAction();
    return $oms->loadForReturn();
}

class ovonMessages {
    private $eventArray;
    private $retOVON;
    private $mySpeakerId;
    private $myURL;
    private $replyTo;

    public function __construct( $inputOVON, $agFunc ) {
        $this->eventArray = [];
        $this->retOVON = $inputOVON;
        $this->retOVON['ovon']['sender']['from'] = $agFunc->getURL();
        $this->mySpeakerId = $agFunc->getSpeakerId();
        $this->myURL = $agFunc->getURL();
        $this->replyTo = $inputOVON['ovon']['sender']['from'];
    }

    public function loadForReturn(){
        if ( empty( $this->eventArray ) ) {
            $this->buildAcknowledge();
        }
        $this->retOVON['ovon']['events'] = $this->eventArray; // add the new events
        $currentDateTime = new DateTime();
        $this->retOVON['ovon']['conversation']['startTime'] = $currentDateTime->format('m-d-Y_H:i:s');
        return $this->retOVON;
    }

    public function buildUttReply( $whatToSay ){
        $this->buildReply( 'utterance', $whatToSay );
    }

    public function buildWhispReply( $whatToSay ){
        $this->buildReply( 'whisper', $whatToSay );
    }

    public function buildReply( $type, $whatToSay ){
        $newEvent = [
            'to' => $this->replyTo,
            'eventType' => $type,
            'parameters' => [
                'dialogEvent' =>[
                    'speakerId'=> $this->mySpeakerId,
                    'features' => [
                        'text' => [
                            'mimeType' => 'text/plain',
                            'tokens' => [
                                ['value' => $whatToSay ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->eventArray[] = $newEvent;
    }

    public function buildManifestReply( $theManifest ){
        $newEvent = [
            'to' => $this->replyTo,
            'eventType' => 'publishManifest',
            'parameters' => [
                'manifest' => $theManifest
            ]
        ];
        $this->eventArray[] = $newEvent;
    }

    public function buildAcknowledge(){
        // think about who should get this convener? floor? all?
        $newEvent = [
            'to' => $this->replyTo,
            'eventType' => 'acknowledge',
        ];
        $this->eventArray[] = $newEvent;
    }
}
?>