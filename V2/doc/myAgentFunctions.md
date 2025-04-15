# Documentation: `agentFunctions` PHP Class

The `agentFunctions` class is designed to provide functionalities for an agent, including handling different types of spoken interactions and obtaining agent-specific metadata. It utilizes the `SimpleNLP` class for natural language processing tasks to determine intents from spoken text.

## Dependencies

- **simpleNLP.php**: This dependency is necessary for the `SimpleNLP` class, which provides methods for intent matching based on predefined concepts.

## Class: `agentFunctions`

### Properties

- **$nlp** (private object): An instance of the `SimpleNLP` class used for processing text input to detect intents.
- **$agent** (private array): Storage for the agent's data read from a JSON file.
- **$URL** (private string): The agent's service endpoint URL.
- **$speakerId** (private string): The agent's speaker ID.
- **$manifest** (private array): The agent's manifest details.
- **$callerId** (private string): Identifier for the caller, initialized as 'unknown'.
- **$persistFileName** (private string): Persistence file name placeholder.
- **$persistObject** (private object|null): Placeholder for an object that might persist state information.

### Constructor

#### `__construct($fileName)`

- **Parameters**: 
  - **$fileName** (string): The file name for the agent's JSON configuration.
  
- **Functionality**: 
  - Initializes an instance of `SimpleNLP` using a file named `intentConcepts.json`.
  - Reads the agent's JSON configuration using `readJSONFromFile()` and extracts information for `URL`, `speakerId`, and `manifest`.

### Methods

#### `inviteAction($heard)`

- **Parameters**: 
  - **$heard** (string): The input heard by the agent.

- **Returns**: 
  - A default greeting message `"Hello, how can I help?"`.

- **Functionality**: 
  - Generates a simple greeting when an invite is detected.

#### `utteranceAction($heard)`

- **Parameters**: 
  - **$heard** (string): The input heard by the agent.

- **Returns**: 
  - A string response that echoes what was heard and potentially executes an intent if identified.

- **Functionality**:
  - Constructs a response indicating what the agent heard.
  - Uses the `SimpleNLP` instance to extract intents from the input.
  - Adjusts the response if specific intents such as "return" are detected, potentially embedding agent actions in the reply.

#### `whisperAction($heard)`

- **Parameters**: 
  - **$heard** (string): The input heard by the agent.

- **Returns**: 
  - A string indicating the agent heard the input and potentially an intention to handle detected intents.

- **Functionality**:
  - Similar to `utteranceAction`, it echoes the heard input and processes detected intents.

#### `getURL()`

- **Returns**: 
  - The agent's service endpoint URL.

#### `getManifest()`

- **Returns**: 
  - The agent's manifest details.

#### `getSpeakerId()`

- **Returns**: 
  - The agent's speaker ID.

## Usage Considerations

- Ensure paths and filenames are correct and accessible as the constructor depends on JSON files.
- The class relies on correct initialization of the `SimpleNLP` instance to effectively process intents and actions.
- This class is designed for use in environments where agents need to handle simple NLP processing and respond to user interactions intelligently.

This class provides essential features for an agent's interaction model, empowering it with basic speech processing and intent determination capabilities.