<?php
include 'myAgentFunctions.php';

function simpleProcessOVON($inputData, $agentFunctions ) {
    $outputData = $inputData;
    $outputData['ovon']['sender']['from'] = $agentFunctions->getURL();
    $mySpeakerId = $agentFunctions->getSpeakerId();
    $convoID = $inputData['ovon']['conversation']['id'];

    if (isset($inputData['ovon']['events'])) { // is this the expected OVON?
        $newEventArray = [];
        foreach ($inputData['ovon']['events'] as $event) { // Loop to find "invite"
            if ( $mySpeakerId === $event['to'] || $myURL === $event['to']){ 
                if ($event['eventType'] === 'invite') {
                    $say = inviteAction();
                }
            }
        }
        foreach ($inputData['ovon']['events'] as $event) {
            // ONLY respond to things directed to you
            if ( $mySpeakerId === $event['to'] || $myURL === $event['to']){ 
                if ($event['eventType'] === 'utterance') {
                    $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    $say = utteranceAction( $heard );
                    $newEventArray[] = buildReply( 'utterance', $to,  $mySpeakerId, $say );
                }elseif ($event['eventType'] === 'whisper') {
                    $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    // The is a private message just to you.'
                    $say = whisperAction( $heard );
                    $newEventArray[] = buildReply( 'whisper', $to,  $mySpeakerId, $say );
                }elseif ($event['eventType'] === 'requestManifest') {
                    $manifest = $agentFunctions->getManifest();
                    $newEventArray[] = buildManifestReply( 'convener', $mySpeakerId, $manifest);
                    $say = "Manifest sent.";
                    $newEventArray[] = buildUttReply( 'human',  $mySpeakerId, $say );
                }
            }
        }
        $outputData['ovon']['events'] = $newEventArray; // add the new events
        $currentDateTime = new DateTime();
        $outputData['ovon']['conversation']['startTime'] = $currentDateTime->format('m-d-Y_H:i:s');
    }
    return $outputData;
}

function buildReply( $type, $to, $mySpeakerID, $whatToSay ){
    $newEvent = [
        'to' => $to,
        'eventType' => $type,
        'parameters' => [
            'dialogEvent' =>[
                'speakerId'=> $mySpeakerID,
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
    return $newEvent;
}

function buildManifestReply( $to, $mySpeakerID, $theManifest ){
    $newEvent = [
        'to' => $to,
        'eventType' => 'publishManifest',
        'parameters' => [
            'manifest' => $theManifest
        ]
    ];
    return $newEvent;
}
?>