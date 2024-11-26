# Beaconforge
<img src="images/beaconforge_logo.png" width="20%" alt="BeaconForge Logo">

## An agentic AI framework designed to enable multi-agent collaboration through NLP (Natural Language Processing)-based APIs.

# Overview
Beaconforge provides a Python framework for initializing an interoperable intelligent assistant that uses the Open Voice Interoperability Initiative specifications.<br />
<img src="images/aiovon.png" width="60%" alt="BeaconForge Logo">
<br />
See please the following Arxiv papers for more information about the specifications:<br />
<a href="https://arxiv.org/abs/2407.19438" target="_blank">Link to Agentic Research Paper #1</a><br />
<a href="https://arxiv.org/abs/2411.05828" target="_blank">Link to Multi-party Research Paper #2</a><br />
The official specifications can be found in <br/>
https://github.com/open-voice-interoperability/docs/tree/main/specifications

# Requirements
...

# Quickstart

### PythonAnywhere-hosted Assistant

The PythonAnywhere-hosted assistant consists of two main files: `local.py` and `assistant.py`. This assistant is designed to respond to various events, including invites and user utterances. It demonstrates how to integrate the assistant with a Flask server. This directory can be copied to pythonanywhere.com to be used or you can start the `local.py` file from your command line by navigating to the correct directory and using the command `python local.py`.

###### IMPORTANT NOTES:  The python anywhere server I have created (aka "Pete") in the sandbox uses `weather_api.py`, so if you are copying the code over please make sure to add that file to your structure as well as insuring your own API key from [here]( https://openweathermap.org/api) is present in the `assistant.py` file on line 90

## ${\textsf{\color{#3AABFC}local.py}}$
### Install Dependencies
* Make sure to install the required dependencies by opening a Bash console and running the following command:
    * ``` pip3.10 install --user flask_cors```


### Code Overview
* The Flask server listens for POST requests on the `/` endpoint.
* It imports the assistant module (`assistant.py`) for response generation.
* The `generate_response` function is called to handle incoming OVON messages.

## ${\textsf{\color{#3AABFC}assistant.py}}$
### Code Overview
* The assistant file defines a `generate_response` function that processes OVON events and generates appropriate responses. 
* It recognizes different event types, such as "invite" and "utterance", adapting responses accordingly.
* This particular assistant checks for greetings and specific keywords (e.g., "weather") to provide context-aware responses.


### Customization
* Modify `greetings` and `weather_terms` lists to tailor the assistant's behavior to specific needs. 
* Adapt the response logic based on specific use cases.

## ${\textsf{\color{#3AABFC}Creating your own PythonAnywhere assistant }}$
#### 1. Create a PythonAnywhere account
* If you don't have a PythonAnywhere account, sign up at [PythonAnywhere](https://www.pythonanywhere.com/)
#### 2. Access PythonAnywhere 
* Log in to  your PythonAnywhere account and navigate to the Dashboard
#### 3. Create Web App in PythonAnywhere 
* Navigate to the Web tab and follow the steps they show to create a web app.
* Once finished, it will create a new folder named `/mysite`, this is where we will be working and uploading the files.
#### 4. Upload Your Files
* Navigate to the "mysite" directory (go to "Files" tab) and upload your assistant files, `local.py` and `assistant.py` that are found in this directory.
#### 5. Open Bash Console
* Navigate to "Consoles" tab and open a Bash console.
#### 6. Install Dependencies
* In the Bash console, install the necessary dependencies. For example, if you are using Flask and Flask-CORS, run:
```pip3.10 install --user flask flask-cors```
#### 7. Running the Server
* To run the server, press the `>>>Run` button.
* If that doesn't work then press on the circle refresh looking button next to the `>>>Run` button.


