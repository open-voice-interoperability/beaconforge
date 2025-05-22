# Beaconforge V2
<img src="images/b_forge_logo_tight.png" width="30%" alt="BeaconForge Logo">

### An agentic AI framework designed to enable multi-agent collaboration through NLP (Natural Language Processing)-based APIs.

# Overview
Beaconforge provides a Python framework (with future plans for other languages) for initializing an interoperable intelligent assistant that uses the Open Voice Interoperability Initiative specifications.<br />
<img src="images/aiovon.png" width="60%" alt="BeaconForge Logo">
<br />

The picture shows a user interacting with several assistants, agent assistant a, agent assistant b and agent assistant c, which all communicate through the Open Voice specifications. The code in this repository will enable you to create your own versions of these assistants.

See please the following Arxiv papers for more information about the specifications:<br />
<a href="https://arxiv.org/abs/2407.19438" target="_blank">Link to Agentic Research Paper #1</a><br />
<a href="https://arxiv.org/abs/2411.05828" target="_blank">Link to Multi-party Research Paper #2</a><br />
The official specifications can be found in <br/>
https://github.com/open-voice-interoperability/docs/tree/main/specifications


# V2
Version 2 of the repository provides a Multiagent Python framework suitable for Pythonanywhere with 3 x AI Agents ready to be used.

* Pete: a general purpose agent to provide general infos
* Athena: a smart library agent to provide information about Books and Authors
* Zeus: an AI agent using the Openweathermap.org APIs to provide weather infos

A frontend Orchestrator Intent-Based takes care of the routing among AI Agents, based on the user intents.

<img src="images/beaconforge_framework.png" width="60%" alt="BeaconForge Logo">

# Quickstart

### PythonAnywhere-hosted Assistant

The PythonAnywhere-hosted assistant (PythonAnywhere folder) consists of two main files: `flask_app.py` and `assistant.py`. Two additional files `intentContents.json` and `weather_api.py` provide additional functionality and a weather example (how to use an external API) to help build a simple OVON server. This assistant is pre-built to respond to various events, including invites, user utterances, and manifest requests. It demonstrates how to integrate the assistant with a Flask server. This directory "PythonAnywhere" can be copied to pythonanywhere.com and will run on a free account (that you will need to set up). You must follow the instructions at the top of `flask_app.py`.
```
# Note!!!! you will need to install flask_cors
#    open a bash console and do this
#    pip3.13 install --user flask_cors
```
You will only need to do this once after creating you PythonAnywhere account. This will be explained further later in this document.

### Using the OVON messages

You will need a way to send OVON messages to your assistant server. There are several ways to accomplish this. As a simple test, you can use a tool like **Postman** (see <a href="using_postman.md" target="_blank">Using Postman</a> for more information)  or **curl** to send OVON messages directly to your assistant server by HTTP POST. You can also use a client to provide a user interface and to send OVON messages to the server assistant from the client. The **open-voice-sandbox** is a voice and text client that will inteact with OVON assistants and it has tools to examine the messages and flow of your interactions. Download the Sandbox [here](https://github.com/open-voice-interoperability/open-voice-sandbox).

You are encouraged to use the code in the sandbox to build your own client. 

###### IMPORTANT NOTES:  The pythonanywhere server template we have created in the sandbox uses `weather_api.py` to serve Zeus (the weather agent), and the `OpenAI API` to serve Athena (smart library agent) and Pete (general purpose agent). Therefore if you are copying the code over please make sure to add that file to your structure as well as insuring your own API key from [here]( https://openweathermap.org/api) is present in your `wsgi.py` file (see please the Web section tab of your Pythonanywhere environment).

## Beaconforge  setup

### Code Overview
* The Flask server listens for POST requests on the `/` endpoint.
* It imports the assistant module (`assistant.py`) for response generation.
* The `generate_response` function is called to handle incoming OVON messages.
* The `intentConcepts.json` file is used for "word-spotting" by the **search_intent(input_text)** function in `assistant.py. It is a basic tool to detect very basic intents that can be used by your assistant. You should not play with this file just yet, but later (after the basic server is running) you can add a new concept by just adding a new concepts array element e.g.
```
    {
      "name": "amphibian",
      "examples": [
        "frog",
        "toad",
        "salamander",
        "newt",
        "caecilian"
      ]
    },
```
When the input_text contains **any** of the examples it will return "amphibian".
### assistant.py
### Code Overview
* The assistant file defines a `generate_response` function that processes OVON events and generates appropriate responses. 
* It recognizes different **event** types, such as **invite**, **utterance**, and **requestManifest** adapting responses accordingly.
* This particular simple assistant checks for greetings and specific keywords (e.g., "weather") to provide context-aware responses.


### Customization
* Modify `greetings` and `weather_terms` examples (or add new `concepts`) to tailor the assistant's behavior to specific needs. 
* Adapt the response logic based on specific use cases.

## Creating your own PythonAnywhere assistant
#### 1. Create a PythonAnywhere account
* If you don't have a PythonAnywhere account, sign up at [PythonAnywhere](https://www.pythonanywhere.com/)
#### 2. Access PythonAnywhere 
* Log in to  your PythonAnywhere account and navigate to the Dashboard

* Install Dependencies: **Make sure** to install the required dependencies by opening a Bash console and running the following command:
    * ``` pip3.13 install --user flask_cors```
    * ``` pip3.13 install --user openai```

#### 3. Create Web App in PythonAnywhere 
* Navigate to the Web tab and follow the steps they show to create a web app.
* Once finished, it will create a new folder named `/mysite`, this is where we will be working and uploading the files. Select `flask_app` as a framework.
#### 4. Upload Your Files
* Navigate to the "mysite" directory (go to "Files" tab) and upload your assistant files (`flask_app.py`, `assistant.py`, `weather_api.py`, and `intentConcepts.json`) that are found in the beaconforge/PythonAnywhere directory.
* Note: You should modify the "manifest" section of code in `assistant.py` to describe your assistant. It will simplify adding your assistant to the sandbox client, and it is required for clients in the future to locate your assistant (think web search to find a site).
* What is in the file:
```
        manifestRequestEvent = {
            "eventType": "publishManifest",
            "parameters": {
                "manifest" : {
                    "identification":
                    {
                        "serviceEndpoint": "http://someAcctName.pythonanywhere.com",
                        "organization": "Sandbox_LFAI",
                        "conversationalName": "Pete",
                        "serviceName": "Python Anywhere",
                        "role": "Basic assistant",
                        "synopsis" : "I am a pretty dumb assistant."
                    },
                    "capabilities": [
                        {
                            "keyphrases": [
                                "dumb",
                                "basic",
                                "lazy"
                            ],
                            "languages": [
                                "en-us"
                            ],
                            "descriptions": [
                                "just some test code to test manifest messages",
                                "simple minded unit test code"
                            ],
                            "supportedLayers": [
                                "text"
                            ]
                        }
                    ]
                }
            }
        }
```
* What you should personalize for your assistant (the XXXXXXX values):
```
        manifestRequestEvent = {
            "eventType": "publishManifest",
            "parameters": {
                "manifest" : {
                    "identification":
                    {
                        "serviceEndpoint": "http://XXXXXXXX.pythonanywhere.com",
                        "organization": "XXXXXXX",
                        "conversationalName": "XXXXXXX",
                        "serviceName": "XXXXXXXX",
                        "role": "XXXXXXXX",
                        "synopsis" : "XXXXXXXXXXXXXXXXXXXXXXXXXX"
                    },
                    "capabilities": [
                        {
                            "keyphrases": [
                                "XXXXX",
                                "XXXXXX",
                                "XXX"
                            ],
                            "languages": [
                                "en-us"
                            ],
                            "descriptions": [
                                "XXXXXXXXXXXXXXXXXXXXX",
                                "XXXXXXXXXXXXXXXXXXXXXX"
                            ],
                            "supportedLayers": [
                                "text"
                            ]
                        }
                    ]
                }
            }
        }
```
#### 5. Open Bash Console
* Navigate to "Consoles" tab and open a Bash console.
#### 6. Install Dependencies
* In the Bash console, install the necessary dependencies. For example, we need to use Flask and Flask-CORS, run:
```pip3.13 install --user flask flask-cors```
* If your assistant will use any other special imports then install them now in the same way.
#### 7. Running the Server
* From the "Files" tab, locate the `mysite/flask_app.py` file, and click on it to open it in the PythonAnywhere editor.
* You **must** upload your server to the pythonanywhere host server. This is not obvious but the `>>>Run` button **only** runs it in your dedicated space. The "swirly-arrows" button **uploads** it to be served on the internet. This may take 10-30 seconds. The last button (just to the right of the `>>>Run` button) is what you want.

<img src="images/uploadButton.png" width="100%" alt="BeaconForge Logo">

* At this point you should be able to test if it is running. It will be accessable at `http://yourAcctName.pythonanywhere.com`
* You can do a postman or curl test now.
# 8. Basic tests with Postman
### Example 1: Get the Manifest from the General Purpose AI Agent (Pete)
* POST request to: http://youraccount.pythonanywhere.com
* Body:
```
{
  "ovon": {
    "schema": {
      "version": "0.9.4"
    },
    "conversation": {
      "id": "31050879662407560061859425913208"
    },
    "sender": {
      "from": "https://someBot.com"
    },
    "events": [
      {
        "to": "http://youraccount.pythonanywhere.com",
        "eventType": "requestManifest"
      }
    ]
  }
}
```
* Expected answer:
```
{"ovon": {"conversation": {"id": "31050879662407560061859425913208"}, "schema": {"version": "0.9.4", "url": "not_published_yet"}, "sender": {"from": "http://youraccount.pythonanywhere.com"}, "events": [{"eventType": "publishManifest", "parameters": {"manifest": {"identification": {"conversationalName": "pete", "serviceName": "Personal Assistant", "organization": "BeaconForge", "serviceEndpoint": "https://youraccount.pythonanywhere.com", "role": "Help with general tasks.", "synopsis": "Sort of like Jarvis in Iron Man."}, "capabilities": {"keyphrases": ["personal", "assistant", "schedule", "appointments"], "languages": ["en-us"], "descriptions": ["A general purpose administrative assistant.", "Help the human with basic daily tasks."], "supportedLayers": ["text", "voice"]}}}}, {"eventType": "utterance", "parameters": {"dialogEvent": {"speakerId": "assistant", "span": {"startTime": "2025-04-16 14:11:58"}, "features": {"text": {"mimeType": "text/plain", "tokens": [{"value": "Thanks for asking, here is my manifest."}]}}}}}]}}
```
### Example 2: Get the Manifest from a specific AI Agent (i.e. Athena)
* POST request to: http://youraccount.pythonanywhere.com
* Body:
```
{
  "ovon": {
    "schema": {
      "version": "0.9.4"
    },
    "conversation": {
      "id": "31050879662407560061859425913208"
    },
    "sender": {
      "from": "https://someBot.com"
    },
    "events": [
      {
        "to": "http://youraccount.pythonanywhere.com/athena",
        "eventType": "requestManifest"
      }
    ]
  }
}
```
* Expected answer:
```
{"ovon": {"conversation": {"id": "31050879662407560061859425913208"}, "schema": {"version": "0.9.4", "url":
"not_published_yet"}, "sender": {"from": "http://youraccount.pythonanywhere.com"}, "events": [{"eventType":
"publishManifest", "parameters": {"manifest": {"identification": {"conversationalName": "athena", "serviceName": "Smart
Library", "organization": "BeaconForge", "serviceEndpoint": "https://youraccount.pythonanywhere.com", "role": "Provide
information about books and authors", "synopsis": "Cradle of knowledge"}, "capabilities": {"keyphrases": ["book",
"author", "library", "literature", "novel"], "languages": ["en-us"], "descriptions": ["Provides book summaries and
author bios.", "Ideal for literary inquiries and library-style info."], "supportedLayers": ["text"]}}}}, {"eventType":
"utterance", "parameters": {"dialogEvent": {"speakerId": "assistant", "span": {"startTime": "2025-04-16 14:13:08"},
"features": {"text": {"mimeType": "text/plain", "tokens": [{"value": "Thanks for asking, here is my manifest."}]}}}}}]}}
```
### Example 3: Send a specific utterance.
* In this example the Orchestrator dispatcher recognizes that the utterance request is related to Book concepts, therefore it routes the message to be served by the Athena AI Agent.
* POST request to: http://youraccount.pythonanywhere.com
* Body:
```
{
  "ovon": {
    "schema": {
      "version": "0.9.4",
      "url": "https://openvoicenetwork.org/schema/dialog-envelope.json"
    },
    "conversation": {
      "id": "conv_1699812834794"
    },
    "sender": {
      "from": "https://organization_url_from",
      "reply-to": "https://organization_url_to"
    },
    "responseCode": {
      "code": 200,
      "description": "OK"
    },
    "events": [
      {
        "eventType": "utterance",
        "parameters": {
          "dialogEvent": {
            "speakerId": "humanOrAssistantID",
            "span": { "startTime": "2023-11-14 02:06:07+00:00" },
            "features": {
              "text": {
                "mimeType": "text/plain",
                "tokens": [
                  {
                    "value": "Tell me about the book The Heart of Darkness please"
                  }
                ]
              }
            }
          }
        }
      }
    ]
  }
}
```
* Expected answer:
```
{"ovon": {"conversation": {"id": "conv_1699812834794"}, "schema": {"version": "0.9.4", "url": "not_published_yet"},
"sender": {"from": "http://youracount.pythonanywhere.com"}, "events": [{"eventType": "utterance", "parameters":
{"dialogEvent": {"speakerId": "assistant", "span": {"startTime": "2025-04-15 07:26:16"}, "features": {"text":
{"mimeType": "text/plain", "tokens": [{"value": "Hello, I'm Athena, your Smart Library Agent. I'm glad you're interested
in \"Heart of Darkness\". It is an intense and powerful novella written by Joseph Conrad, first published in serial form
in 1899 and then in book form in 1902.\n\nThe story recounts the travel of Charles Marlow, the protagonist, up the Congo
River in Central Africa, as an agent for a Belgian ivory trading company. The novel is a complex exploration of the
attitudes people hold on what constitutes a barbarian versus a civilized society, and the attitudes on colonialism and
racism that were part and parcel of European imperialism.\n\n\"Heart of Darkness\" is notable for its narrative
structure, as it's a story within a story. Conrad uses innovative, impressionistic methods of description that were new
and exciting at the time of publication and have since become hallmarks of modernist literature.\n\nPlease note that
\"Heart of Darkness\" has been both praised and criticized for its handling of the colonial subjects and its portrayal
of Africa and Africans. It is a profound and influential work that continues to inspire discussions and
analyses."}]}}}}}]}}
```
### Example 4: Send a general utterance.
* In this example the Orchestrator dispatcher recognizes that the utterance request is generic and not related to any specific intent concepts, therefore it routes the message to be served by the Pete General Purpose AI Agent.
* POST request to: http://youraccount.pythonanywhere.com
* Body:
```
{
  "ovon": {
    "schema": {
      "version": "0.9.4",
      "url": "https://openvoicenetwork.org/schema/dialog-envelope.json"
    },
    "conversation": {
      "id": "conv_1699812834794"
    },
    "sender": {
      "from": "https://organization_url_from",
      "reply-to": "https://organization_url_to"
    },
    "responseCode": {
      "code": 200,
      "description": "OK"
    },
    "events": [
      {
        "eventType": "utterance",
        "parameters": {
          "dialogEvent": {
            "speakerId": "humanOrAssistantID",
            "span": { "startTime": "2023-11-14 02:06:07+00:00" },
            "features": {
              "text": {
                "mimeType": "text/plain",
                "tokens": [
                  {
                    "value": "How can I get from London to Paris?"
                  }
                ]
              }
            }
          }
        }
      }
    ]
  }
}
```
* Expected answer:
```
{"ovon": {"conversation": {"id": "conv_1699812834794"}, "schema": {"version": "0.9.4", "url": "not_published_yet"},
"sender": {"from": "http://youraccount.pythonanywhere.com"}, "events": [{"eventType": "utterance", "parameters":
{"dialogEvent": {"speakerId": "assistant", "span": {"startTime": "2025-04-16 14:05:32"}, "features": {"text":
{"mimeType": "text/plain", "tokens": [{"value": "Hi, I'm Pete, your Personal Assistant. There are several ways for you
to travel from London to Paris:\n\n1. By Train: You can take the Eurostar train from London St. Pancras International to
Paris Gare du Nord. The journey typically takes a little over 2 hours.\n\n2. By Plane: Numerous airlines fly this route.
The flight typically takes about an hour, but you'll also need to factor in time for airport security and travel to and
from the airports.\n\n3. By Car and Ferry: You can drive to Dover, take a ferry to Calais, and then continue driving to
Paris. This can take 6-7 hours or more, depending on traffic and ferry schedules.\n\n4. By Bus: Various companies, like
FlixBus or Eurolines, offer bus services from London to Paris. This is a cheaper but longer option, typically taking
about 8-9 hours.\n\nRemember to check the current travel guidelines and restrictions due to COVID-"}]}}}}}]}}
```
### Example 5: Send an invite to a specific agent (i.e. Athena)
* POST request to: http://youraccount.pythonanywhere.com
* Body:
```
{
  "ovon": {
    "schema": {
      "version": "0.9.4",
      "url": "https://openvoicenetwork.org/schema/dialog-envelope.json"
    },
    "conversation": {
      "id": "conv_1699812834794"
    },
    "sender": {
      "from": "https://organization_url_from",
      "reply-to": "https://organization_url_to"
    },
    "responseCode": {
      "code": 200,
      "description": "OK"
    },
    "events": [
      {
        "eventType": "invite",
        "parameters": {
          "to": {
            "url": "https://youraccount.pythonanywhere.com/athena"
          }
        }
      }
    ]
  }
}
```
* Expected answer:
```
{"ovon": {"conversation": {"id": "conv_1699812834794"}, "schema": {"version": "0.9.4", "url": "not_published_yet"},
"sender": {"from": "http://youraccount.pythonanywhere.com"}, "events": [{"eventType": "utterance", "parameters":
{"dialogEvent": {"speakerId": "assistant", "span": {"startTime": "2025-04-15 21:25:38"}, "features": {"text":
{"mimeType": "text/plain", "tokens": [{"value": "Hello, I'm Athena, your Smart Library Agent. You can ask me about books
and authors, and I will be happy to help."}]}}}}}]}}
```
### Example 6: Ask about the weather to the Zeus Agent. 
* This action requires you to properly fill the utterance token with a request for weather and the whisper token value with the specific location (i.e. city), in order to properly call the external openweathermap API.
* POST request to: http://youraccount.pythonanywhere.com
* Body:
```
{
    "ovon": {
      "schema": {
        "version": "0.9.4",
        "url": "https://openvoicenetwork.org/schema/dialog-envelope.json"
      },
      "conversation": {
        "id": "conv_1699812834794"
      },
      "sender": {
        "from": "https://organization_url_from",
        "reply-to": "https://organization_url_to"
      },
      "responseCode": 200,
      "events": [
        {
          "eventType": "invite",
          "parameters": {
            "to": {
              "url": "https://youraccount.pythonanywhere.com"
            }
           }
         },
          {
          "eventType": "utterance",
            "parameters": {
              "dialogEvent": {
                "speakerId": "humanOrAssistantID",
                "span": { "startTime": "2023-11-14 02:06:07+00:00" },
                "features": {
                  "text": {
                    "mimeType": "text/plain",
                    "tokens": [ { "value": "What's the weather like?" } ] 
                  }
                }
              }
            }
          },
          {
          "eventType": "whisper",
            "parameters": {
              "dialogEvent": {
                "speakerId": "humanOrAssistantID",
                "span": { "startTime": "2023-11-14 02:06:07+00:00" },
                "features": {
                  "text": {
                    "mimeType": "text/plain",
                    "tokens": [ { "value": "New York" } ] 
                  }
                }
              }
            }
          }
      ]
    }
  }
```
* Expected answer:
```
{"ovon": {"conversation": {"id": "conv_1699812834794"}, "schema": {"version": "0.9.4", "url": "not_published_yet"},
"sender": {"from": "http://youraccount.pythonanywhere.com"}, "events": [{"eventType": "utterance", "parameters":
{"dialogEvent": {"speakerId": "assistant", "span": {"startTime": "2025-04-16 08:49:40"}, "features": {"text":
{"mimeType": "text/plain", "tokens": [{"value": "Weather in New York: overcast clouds, Temperature: 8.23\u00b0C,
Humidity: 52%"}]}}}}}]}}
```