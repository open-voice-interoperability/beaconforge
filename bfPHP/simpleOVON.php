<?php
include 'myAgentFunctions.php';

function simpleProcessOVON($inputData, $agentFunctions ) {
    $outputData = $inputData;
    $outputData['ovon']['sender']['from'] = $agentFunctions->getURL();
    $mySpeakerId = $agentFunctions->getSpeakerId();
    $myURL = $agentFunctions->getURL();
    $convoID = $inputData['ovon']['conversation']['id'];
    $replyTo = $inputData['ovon']['sender']['from'];

    if (isset($inputData['ovon']['events'])) { // is this the expected OVON?
        $outputData['ovon']['hasEvents'] = 'true'; // ejcDBG
        $newEventArray = [];
        foreach ($inputData['ovon']['events'] as $event) { // Loop to find "invite"
            //if ( $mySpeakerId === $event['to'] || $myURL === $event['to']){ 
                if ($event['eventType'] === 'invite') {
                    $outputData['ovon']['hasInvite'] = 'true';
                    $say = $agentFunctions->inviteAction();
                    $newEventArray[] = buildReply( 'utterance', $replyTo,  $mySpeakerId, $say );
                }
            //}
        }
        foreach ($inputData['ovon']['events'] as $event) {
            // ONLY respond to things directed to you

            $outputData['ovon']['event.to'] = $event['to']; // ejcDBG
            $outputData['ovon']['mySpeakerId'] = $mySpeakerId; // ejcDBG
            $outputData['ovon']['myURL'] = $myURL; // ejcDBG

            if ( $mySpeakerId === $event['to'] || $myURL === $event['to']){ 
                $outputData['ovon']['matches spID or url'] = 'true'; // ejcDBG

                if ($event['eventType'] === 'utterance') {
                    $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    $say = $agentFunctions->utteranceAction( $heard );
                    $newEventArray[] = buildReply( 'utterance', $replyTo,  $mySpeakerId, $say );
                }elseif ($event['eventType'] === 'whisper') {
                    $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    // The is a private message just to you.'
                    $say = $agentFunctions->whisperAction( $heard );
                    $newEventArray[] = buildReply( 'whisper', $replyTo,  $mySpeakerId, $say );
                }elseif ($event['eventType'] === 'requestManifest') {
                    $outputData['ovon']['detected reqMani'] = 'true'; // ejcDBG

                    $manifest = $agentFunctions->getManifest();
                    $newEventArray[] = buildManifestReply( 'convener', $mySpeakerId, $manifest);
                    $say = "Manifest sent.";
                    $newEventArray[] = buildReply( 'utterance', 'human',  $mySpeakerId, $say );
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