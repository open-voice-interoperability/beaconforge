# Documentation: `simpleProcessOVON` PHP Function

The provided PHP script defines a function `simpleProcessOVON`, which is designed to process input data in the Open Voice Open Network (OVON) format, manipulate events directed at a particular agent, and generate responses based on the type of events received. This script assumes integration with an external utility file `myAgentFunctions.php` that provides agent-related utility functions.

## Dependencies

- **myAgentFunctions.php**: This file must contain methods `getURL()`, `getSpeakerId()`, and `getManifest()`, which are used to fetch the agent's URL, speaker ID, and manifest respectively.

## Primary Function: `simpleProcessOVON`

### Purpose

The function is responsible for handling a series of events in the input data, processing those directed to the agent, and creating appropriate responses.

### Parameters

- **$inputData** (array): An associative array representing the incoming data in OVON format.
- **$agentFunctions** (object): An instance of a class containing methods for retrieving agent-specific information.

### Returns

- **$outputData** (array): An associative array in the OVON format with added or modified event responses.

### Function Workflow

1. **Initialize Output**: Copies the input data to `$outputData` for further manipulation.

2. **Set Sender Information**: 
   - Sets the `from` field in `ovon.sender` to the agent's URL.
   - Retrieves the speaker ID and conversation ID for further processing.

3. **Process Events**: Checks for the presence of `events` in the input `ovon` data and processes them in the following manner:
   - **Detect Invitation Events**: Iterates through events to identify any 'invite' event directed to the agent and potentially trigger `inviteAction()`.
   - **Handle Event Types**:
     - **Utterance**: Listens for textual utterance events to the agent, extracts the first token, and responds via `utteranceAction()`.
     - **Whisper**: Listens for private messages aimed at the agent and invokes `whisperAction()` to generate a response.
     - **Request Manifest**: On receiving a request for a manifest, it uses `getManifest()` to retrieve and respond with the agent's manifest data.
   - **Response Generation**: Each processed event results in a response added to a new event array using helper functions `buildReply()` or `buildManifestReply()`.

4. **Update Events and Timestamp**:
   - Replaces the original `events` with the newly generated responses.
   - Updates the conversation's start time with the current time.

5. **Return Updated Data**: Outputs the modified data structure including the responses to the incoming events.

## Helper Functions

1. **`buildReply()`**
   - Constructs a reply event of a specified type (e.g., 'utterance', 'whisper') containing the agent's response text directed to a recipient.

2. **`buildManifestReply()`**
   - Builds an event specifically for publishing a manifest, which includes sending detailed agent information back to the request initiator.

## Usage Considerations

- Ensure `myAgentFunctions.php` provides the required functional interfaces.
- Customize event processing logic (`inviteAction`, `utteranceAction`, etc.) based on application-specific requirements.
- The function currently assumes that utterances and whispers utilize the first token for response purposes. Make adjustments if dealing with more complex linguistic processing.

The script provides a foundational framework for agents within an OVON system to autonomously handle events and communicate effectively with other entities.