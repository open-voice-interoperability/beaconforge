<?php
// Author: Emmett Coin 2026
global $agentFunctionsFileName;

if (file_exists($agentFunctionsFileName)) {
    include $agentFunctionsFileName;
} else {
    echo "The file '$agentFunctionsFileName' does not exist.";
}

function simpleProcessOFP($inputData, $agentFileName ) {
    global $pathForPersistantStorage;
    $agFun = new agentFunctions( $agentFileName );
    $agFun->setConvoId( $inputData['openFloor']['conversation']['id'] );

    $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', $agFun->convoName);
    //$myDataDir = $pathForPersistantStorage . $cleanName . '/';
    $myDataDir = $pathForPersistantStorage . $cleanName;
    if ( !is_dir( $myDataDir )) {// Directory does not exist, so try to create it
        if ( !mkdir($myDataDir, 0755, true)) {
            echo "Directory for persistant storage could NOT be created";
        }
    }

    $agFun->usePersist( $myDataDir );
    $agFun->useLog( $myDataDir );
    $agFun->startUpAction();
    $oms = new OFPMessages( $inputData, $agFun );
    $agFun->shareOFPmsg( $oms );

    $mySpeakerUri = $agFun->getSpeakerUri();
    $myURL = $agFun->getURL();
    $myAltURL = $agFun->getAltURL();
    $reason = 'none';
    $eventTo;
    $senderSpeakerUri = '';
    $senderServicerUrl = '';
    $sentByMe = false;

    if (isset($inputData['openFloor']['sender'])) { // who sent this?
        if (isset($inputData['openFloor']['sender']['speakerUri'])) {
            $senderSpeakerUri = $inputData['openFloor']['sender']['speakerUri'];
        }
        if (isset($inputData['openFloor']['sender']['serviceUrl'])) {
            $senderServicerUrl = $inputData['openFloor']['sender']['serviceUrl'];
        }
        if (($mySpeakerUri === $senderSpeakerUri) || ($myURL === $senderServicerUrl ) || ($myAltURL === $senderServicerUrl ) ){
            $sentByMe = true; // this agent sent this OFP so ignore it.
        }
    }

    if( !$sentByMe){
        if (isset($inputData['openFloor']['events'])) { // is this the expected OFP?
            foreach ($inputData['openFloor']['events'] as $event) { // Loop to find "invite"
                if ($event['eventType'] === 'invite') {
                    $eventTo = $event['to'];

                    $url = getUrs( $eventTo, 'serviceUrl' );
                    $uri = getUrs( $eventTo, 'speakerUri' );
                    $alturl = getUrs( $eventTo, 'ALTserviceUrl' );

                    if ( ($mySpeakerUri === $uri) || ($myURL === $url) || ($myAltURL === $url) ){
                        if( $url === $myAltURL){
                            $agFun->changeURL( $myAltURL ); // switch to the ALT URL if that is where the message is being directed
                            $note = "This message was directed to my ALT URL: $myAltURL";
                            $agFun->addConvoPersistNote( $note );
                        } 
                        $say = $agFun->inviteAction( $reason );
                        $oms->acceptInvite( true );
                        $note = "I received an invite with reason [ $reason ] from $senderSpeakerUri. I accepted the invite and said: $say";
                        $agFun->addConvoPersistNote( $note );
                        $oms->buildUttReply( $say );
                    }
                }
            }
            foreach ($inputData['openFloor']['events'] as $event) {
                $directedToMe = false;
                $directedToSomeoneElse = false;
                if (isset($event['to'])) {
                    $eventTo = $event['to'];
                    $url = getUrs( $eventTo, 'serviceUrl' );
                    $uri = getUrs( $eventTo, 'speakerUri' );
                    $alturl = getUrs( $eventTo, 'ALTserviceUrl' );
                    $directedToMe = ( $mySpeakerUri === $uri || $myURL === $url || $myAltURL === $url );
                    if(!$directedToMe){
                        $directedToSomeoneElse = true;
                    }
                    if( $url === $myAltURL){
                        $agFun->changeURL( $myAltURL ); // switch to the ALT URL if that is where the message is being directed
                        $note = "This message was directed to my ALT URL: $myAltURL";
                        $agFun->addConvoPersistNote( $note );
                    }
                }
                if ($event['eventType'] === 'utterance') {
                    if( isset($event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value']) ){
                        $heard = $event['parameters']['dialogEvent']['features']['text']['tokens'][0]['value'];
                    }
                    $note = "I heard: $heard from $senderSpeakerUri. toMe? " . ($directedToMe ? "Yes" : "No");
                    $agFun->addConvoPersistNote( $note );
///*
                    if (containsStr($heard, $agFun->getConversationalName())) { // my name is in the sentence so it is directed to me even if not explicitly
                        $directedToMe = true;
                        $directedToSomeoneElse = false;
                    }
//*/
                    $say = $agFun->utteranceAction( $heard, $senderSpeakerUri, $directedToMe, $directedToSomeoneElse );
                    $oms->buildUttReply( $say );

                    $note = "I said: $say";
                    $agFun->addConvoPersistNote( $note );

                }elseif ( ($event['eventType'] === 'getManifests') && $directedToMe ) {
                    $manifest = $agFun->getManifestArray();
                    $oms->buildManifestReply( $manifest);
                    $oms->buildUttReply( "Manifest sent." );
                    $note = "I sent the manifest and said: Manifest sent.";
                    $agFun->addConvoPersistNote( $note );

                }
            }
        }
    }else{
        $oms->buildAcknowledge(); // just acknowledge that you got the message (http politeness)
    }
    $agFun->wrapUpAction();
    return $oms->loadForReturn( $agFun);
}

function getUrs( $evTo, $type ){
    if (isset($evTo[$type])) {
        return $evTo[$type];
    }
}

function containsStr(string $sentence, string $searchFor): bool {
    // stripos() returns the position (an integer, 0 if at the start) or false if not found.
    // We use the strict comparison operator (!== false) to ensure a correct boolean result,
    // as 0 is a valid position but is "falsy" in loose comparisons.
    if (stripos($sentence, $searchFor) !== false) {
        return true;
    } else {
        return false;
    }
}

class OFPMessages {
    private $eventArray;
    private $retOFP;
    private $mySpeakerUri;
    private $myURL;
    private $replyTo;

    public function __construct( $inputOFP, $agFunc ) {
        $this->eventArray = [];
        $this->replyTo = $inputOFP['openFloor']['sender'];
        $this->retOFP = $inputOFP;

        // these are ignored on return so just delete them for neatness
        if (isset($this->retOFP['openFloor']['conversation']['conversants'])) { //remove conversants
            unset($this->retOFP['openFloor']['conversation']['conversants']);
        } 
        if (isset($this->retOFP['openFloor']['conversation']['assignedFloorRoles'])) { //remove assignedFloorRoles
            unset($this->retOFP['openFloor']['conversation']['assignedFloorRoles']);
        } 
        if (isset($this->retOFP['openFloor']['conversation']['floorGranted'])) { //remove floorGranted 
            unset($this->retOFP['openFloor']['conversation']['floorGranted']);
        } 

        //$this->retOFP['openFloor']['sender']['serviceUrl'] = $agFunc->getURL();
        //$this->retOFP['openFloor']['sender']['speakerUri'] = $agFunc->getSpeakerUri();
        $this->mySpeakerUri = $agFunc->getSpeakerUri();
        $this->myURL = $agFunc->getURL();
    }

    public function loadForReturn( $agFunc ){
        $this->retOFP['openFloor']['sender']['serviceUrl'] = $agFunc->getURL();
        $this->retOFP['openFloor']['sender']['speakerUri'] = $agFunc->getSpeakerUri();

        if ( empty( $this->eventArray ) ) {
            //$this->buildAcknowledge();
        }
        $this->retOFP['openFloor']['events'] = $this->eventArray; // add the new events
        $currentDateTime = new DateTime();
        $this->retOFP['openFloor']['conversation']['startTime'] = $currentDateTime->format('m-d-Y_H:i:s');
        return $this->retOFP;
    }

    public function buildUttReply( $whatToSay ){
        if( $whatToSay != '' ){ // otherwise ignore it
            $this->buildReply( 'utterance', $whatToSay );
        }
    }

    public function buildReply( $type, $whatToSay ){
        if( strlen($whatToSay) > 0 ){ // skip if nothing
            $newEvent = [
                'to' => $this->replyTo,
                'eventType' => $type,
                'parameters' => [
                    'dialogEvent' =>[
                        'speakerUri'=> $this->mySpeakerUri,
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
    }

    public function buildManifestReply( $theManifest ){
        $newEvent = [
            'to' => $this->replyTo,
            'eventType' => 'publishManifests',
            'parameters' => [
                'servicingManifests' => [
                    $theManifest
                ],
                'discoveryManifests' => [
                    $theManifest
                ]
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

    public function acceptInvite( $bool ){
        // think about who should get this convener? floor? all?
        $evType = 'declineInvite';
        if( $bool ) {
            $evType = 'acceptInvite';
        }
        $newEvent = [
            'to' => $this->replyTo,
            'eventType' => $evType,
        ];
        $this->eventArray[] = $newEvent;
    }

    public function addRawEvent( $someEvent ){
        // You are responsible for building a valid event
        $this->eventArray[] = $someEvent;
    }
}

class baseAgentFunctions {
    protected $agent;
    public $convoName;
    private $URL;
    private $AltURL = '';
    private $speakerUri;
    private $manifest;
    protected $persistFileName = '';
    protected $persistObject = null;
    protected $usePersistObject = false;
    protected $OFPTool = null;
    private $convoId = '';
    private $agentJSONFile = '';
    protected $relativePathForFiles;

    protected $thisTurnData = [];
    protected $logObject = null;
    protected $logFileName = '';
    protected $useLogObject = false;

    public function startUpAction() {
        // some code to initialize this.
        // e.g. read persistant data, or set up llm etc
        if (!file_exists($this->logFileName) && $this->useLogObject){ //start fresh
            $this->logObject = [
                'convoLog' => [],
            ];
            $this->saveLogObject( $this->logObject );
        }else{
            if( $this->useLogObject ){
                $this->logObject = $this->getLogObject();
            }
        }
        if (!file_exists($this->persistFileName) && $this->usePersistObject){ //start fresh
            $this->persistObject = null;
            $this->savePersistObject( $this->persistObject );
        }else{
            if( $this->usePersistObject ){
                $this->persistObject = $this->getPersistObject();
            }
        }
    }

    public function wrapUpAction() {
        // some code to finalize this.
        // e.g. save persistant data or do final llm post
        // REMEMBER: persistantLogObject is JSON stringified then saved
        if( $this->usePersistObject ){
            $this->savePersistObject( $this->persistObject ); //save for next turn
        }
        if( $this->useLogObject ){ // add this turn's data to the log and save it for next turn
            $this->saveLogObject( $this->logObject );
        }
    }

    public function inviteAction( $reason ) {
        $say = 'Hi, how can I help?';
        // This is what you say upon your invitation to the conversation
        return $say;
    }

    public function utteranceAction( $heard, $fromUri, $directedToMe, $directedToSomeoneElse ) {
        $say = 'I heard: ' . $heard;
        // This is a public message sent to everyone on the conversation
        // Do your thing with $heard and create a $say
        // Use the $directedToMe boolean to behave differently when not directed to you
        return $say;
    }
        
    public function getManifestArray() {
        $manifest = $this->agent['manifest'];
        return $manifest;
    }


    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // The above functions will be overloaded in your EXTENDED class
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

    public function __construct( $fileName ) {
        $this->agentJSONFile = $fileName;
        $this->agent = readJSONFromFile( $fileName );
        $this->manifest = $this->agent['manifest'];
        $this->URL = $this->agent['manifest']['identification']['serviceUrl'];
        //$this->agentName = $this->agent['manifest']['identification']['conversationalName'];
        if( isset($this->agent['manifest']['identification']['ALTserviceUrl']) ){
            $this->AltURL = $this->agent['manifest']['identification']['ALTserviceUrl'];
        }
        $this->convoName = $this->agent['manifest']['identification']['conversationalName'];
        $this->speakerUri = $this->agent['manifest']['identification']['speakerUri'];
    }

    public function shareOFPmsg( $oms ) {
        $this->OFPTool = $oms;
    }

    public function useLog( $relativePath ){
        // $relativePath [str e.g. '../../private']
        $this->relativePathForFiles = $relativePath;
        $this->useLogObject = true;
        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_',  $this->convoName);
        $cleanName .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $this->convoId);
        $this->logFileName = $relativePath . '/' . $cleanName .'.log';
    }
     
    public function getLogObject(){
        // this will return null if not there so you must build one
        $this->logObject = null;
        if( $this->useLogObject ){
            $this->logObject = readJSONFromFile( $this->logFileName );
        }
        return $this->logObject;
    }
     
    public function saveLogObject( $pObject ){
        // this will save the object to a file for the next turn
        if( $this->useLogObject && ($pObject != null) ){
            $strJSON = json_encode( $pObject );
            file_put_contents( $this->logFileName, $strJSON );
        };
    }

    public function usePersist( $relativePath ){
        // $relativePath [str e.g. '../../private']
        $this->relativePathForFiles = $relativePath;
        $this->usePersistObject = true;
        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_',  $this->convoName);
        $cleanName .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $this->convoId);
        $this->persistFileName = $relativePath . '/' . $cleanName .'.json';
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

    //public function getAgentName() {
    //    return $this->agentName;
    //}

    public function getURL() {
        return $this->URL;
    }

    public function changeURL( $newURL) {
        $this->URL = $newURL;
        return $this->URL;
    }

    public function getAltURL(){
        return $this->AltURL;
    }

    public function getSpeakerUri() {
        return $this->speakerUri;
    }

    public function getConversationalName() {
        return $this->convoName;
    }

    public function setConvoId( $someId ) {
        $this->convoId = $someId;
    }

    public function addConvoPersistNote( $someNote ) {
        // set time and note into this turn's data to be added to the persistant object at the end of the turn
        $this->thisTurnData[] = [
            'time' => date('m-d-Y_H:i:s'),
            'note' => $someNote
        ];
        $this->logObject['convoLog'][] = $this->thisTurnData;
        $this->saveLogObject( $this->logObject );
    }
}

// Define the file path
//$filePath = 'example.txt';
$errors = [];

function writeToFile($filePath, $content) {
    // Open the file for writing; creates the file if it doesn't exist
    $fileHandle = fopen($filePath, 'w');
    if ($fileHandle) {
        fwrite($fileHandle, $content); // Write content to file
        fclose($fileHandle);
    } else {
        $errors[] = "Failed to open the file for writing.\n";
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
            $errors[] = "Failed to open the file for reading.";
        }
    } else {
        $errors[] = "File does not exist.";
    }
}

function readJSONFromFile($filePath) {
    //echo 'filePath = ' . $filePath . PHP_EOL; //ejcdbg
    $data = null;
    if (file_exists($filePath)) { // file exists?
        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle) {
            $content = fread($fileHandle, filesize($filePath));
            fclose($fileHandle);
            $data = json_decode($content, true); // Decode JSON to PHP variable
            if (json_last_error() !== JSON_ERROR_NONE) { // Good decode?
                $errors[] = "JSON Decode Error";
            }
        } else {
            $errors[] = "Failed to open the file for reading." . PHP_EOL;
        }
    } else {
        $errors[] = "File does not exist." . PHP_EOL;
    }
    return $data;
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
            foreach ($this->myConcepts as $concept) {
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

    public function loadIntents( $type, $whatToSay ){
        $myConcepts = [
            [
                "name" => "bye",
                "examples" => [
                    "goodbye", "farewell", "bye", "see you", "have a good day", "goodnight", "talk to you later", "catch you later", "see you later", "take care", "I have to go", "I'm leaving", "until next time"
                ]
            ],
            [
                "name" => "maybe",
                "examples" => [
                    "maybe", "perhaps", "not sure", "I don't know", "possibly", "could be", "might be", "it's a possibility", "I guess so", "I suppose so"
                ]
            ],
            [
                "name" => "yes",
                "examples" => [
                    "yes", "yeah", "okay", "why not", "sure", "alright", "of course", "definitely"
                ]
            ],
            [
                "name" => "no",
                "examples" => [
                    "no", "not now", "never", "don't want to", "nope", "not really", "don't think so"
                ]
            ],
            [
                "name" => "stillThere",
                "examples" => [
                    "you still there", "you there", "are you listening", "can you here me"
                ]
            ],
            [
                "name" => "politeness",
                "examples" => [
                    "thank you", "excuse me", "please", "sorry", "do you mind", "pardon me", "welcome", "excuse me", "my apologies", "no problem", "don't mention it", "that's alright"
                ]
            ],
            [
                "name" => "greeting",
                "examples" => [
                    "hello", "hi", "hey", "how is it going", "good morning", "good afternoon", "good evening", "what's up", "howdy", "greetings", "salutations", "nice to meet you", "pleased to meet you"
                ]
            ]
        ];
    }
}
?>