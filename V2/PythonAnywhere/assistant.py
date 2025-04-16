from openai import OpenAI
import json
from datetime import datetime
import os
import weather_api
import re

conversation_state = {}

with open(os.path.join(os.path.dirname(__file__), "assistant_config.json"), "r") as file:
    agent_config = json.load(file)

client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))
weather_key = os.environ.get("WEATHER_API_KEY")
manifest = agent_config.get("manifest")

service_endpoint = os.environ.get("SERVICE_ENDPOINT")
if service_endpoint:
    agent_config["manifest"]["identification"]["serviceEndpoint"] = service_endpoint
    if "athena" in agent_config and "manifest" in agent_config["athena"]:
        agent_config["athena"]["manifest"]["identification"]["serviceEndpoint"] = service_endpoint
    if "zeus" in agent_config and "manifest" in agent_config["zeus"]:
        agent_config["zeus"]["manifest"]["identification"]["serviceEndpoint"] = service_endpoint

messages = [
    {"role": "system", "content": agent_config["personalPrompt"]},
    {"role": "system", "content": agent_config["functionPrompt"]}
]

def search_intent(input_text):
    my_dir = os.path.dirname(__file__)
    json_file_path = os.path.join(my_dir, 'intentConcepts.json')
    with open(json_file_path, 'r') as f:
        concepts_data = json.load(f)

    matched_intents = []
    input_text_lower = input_text.lower()

    for concept in concepts_data["concepts"]:
        matched_words = [word for word in concept["examples"] if word in input_text_lower]
        if matched_words:
            matched_intents.append({"intent": concept["name"], "matched_words": matched_words})

    return matched_intents if matched_intents else None

def celsius_to_fahrenheit(celsius):
    return (celsius * 9/5) + 32

def generate_openai_response(prompt):
    try:
        message_history = [{
            "role": "system",
            "content": (
                "You are Pete, the Personal Assistant. "
                "Always begin your first response by saying: "
                "'Hi, I'm Pete, your Personal Assistant.' "
                "Then continue with your helpful and friendly answer."
            )
        }]

        if "messages" in conversation_state:
            message_history.extend(conversation_state["messages"])
        message_history.append({"role": "user", "content": prompt})

        response = client.chat.completions.create(
            model="gpt-4",
            messages=message_history,
            max_tokens=200,
            temperature=0.7
        )

        assistant_reply = response.choices[0].message.content.strip()

        if "messages" not in conversation_state:
            conversation_state["messages"] = []
        conversation_state["messages"].append({"role": "user", "content": prompt})
        conversation_state["messages"].append({"role": "assistant", "content": assistant_reply})

        return assistant_reply

    except Exception as e:
        print(f"Error with OpenAI API: {e}")
        return f"Error with OpenAI API: {str(e)}"

def generate_athena_response(prompt):
    try:
        message_history = [
            {"role": "system", "content": agent_config["athena"]["personaPrompt"]},
            {"role": "user", "content": prompt}
        ]

        response = client.chat.completions.create(
            model="gpt-4",
            messages=message_history,
            max_tokens=300,
            temperature=0.7
        )

        return response.choices[0].message.content.strip()

    except Exception as e:
        return f"Error with Athena's response: {str(e)}"

def generate_response(inputOVON, sender_from):
    global server_info
    global conversation_history
    server_info = ""
    response_text = None
    detected_intents = []
    include_manifest_request = False
    current_manifest = manifest

    openai_api_key = inputOVON["ovon"]["conversation"].get("openAIKey", None)
    if openai_api_key:
        client.api_key = openai_api_key

    conversation_id = inputOVON["ovon"]["conversation"]["id"]
    if conversation_id not in conversation_state:
        conversation_state[conversation_id] = {}

    location_from_whisper = None
    user_input_text = None

    for event in inputOVON["ovon"]["events"]:
        event_type = event["eventType"]

        if event_type == "invite":
            to_url = event.get("parameters", {}).get("to", {}).get("url", "")
            if "athena" in to_url.lower():
                response_text = "Hello, I'm Athena, your Smart Library Agent. You can ask me about books and authors, and I will be happy to help."
            elif "zeus" in to_url.lower():
                response_text = (
                    "Hello, I'm Zeus, your weather information agent. Ask me please about what's the weather like in the utterance "
                    "and send me the precise location city inside the whisper token, and I will lookup the real time weather information for you."
                )
            else:
                server_info = f"Server: {to_url}"
                response_text = "Hi, I'm Pete, your Personal Assistant. Thanks for the invitation — I'm ready to help!"

        elif event_type == "requestManifest":
            to_url = event.get("to", "").lower()
            for name in ["athena", "zeus"]:
                if name in to_url:
                    current_manifest = agent_config.get(name, {}).get("manifest", manifest)
                    break
            else:
                current_manifest = manifest

            server_info = f"Server: {to_url}"
            response_text = "Thanks for asking, here is my manifest."
            include_manifest_request = True

        elif event_type == "utterance":
            user_input_text = event["parameters"]["dialogEvent"]["features"]["text"]["tokens"][0]["value"]
            detected_intents.extend(search_intent(user_input_text) or [])

        elif event_type == "whisper":
            location_from_whisper = event["parameters"]["dialogEvent"]["features"]["text"]["tokens"][0]["value"]

    if detected_intents:
        for intent in detected_intents:
            if intent["intent"] in ["weather", "zeus"]:
                location = location_from_whisper or intent.get("location", "unknown")
                if location != "unknown":
                    try:
                        weather_data = weather_api.get_weather(weather_key, location)
                        temp, humidity, weather_report = weather_api.parse_weather_data(weather_data)
                        conversation_state[conversation_id]["temp_in_celsius"] = temp
                        response_text = (
                            f"Weather in {location}: {weather_report}, Temperature: {temp}°C, "
                            f"Humidity: {humidity}%"
                        )
                    except Exception as e:
                        response_text = f"Sorry, I couldn't retrieve the weather for {location}. {str(e)}"
                else:
                    response_text = "Could you please provide the location?"

            elif intent["intent"] == "convertTemperature":
                if "temp_in_celsius" in conversation_state[conversation_id]:
                    temp_in_fahrenheit = celsius_to_fahrenheit(conversation_state[conversation_id]["temp_in_celsius"])
                    response_text = f"The temperature in Fahrenheit is {temp_in_fahrenheit}°F."
                else:
                    response_text = "I don't have a temperature to convert. Please ask for the weather first."

            elif intent["intent"] == "athena":
                response_text = generate_athena_response(user_input_text)
                current_manifest = agent_config.get("athena", {}).get("manifest", manifest)

    if not detected_intents and user_input_text and not response_text:
        response_text = generate_openai_response(user_input_text)

    if not response_text:
        response_text = "I'm not sure how to respond."

    currentTime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    ovon_response = {
        "ovon": {
            "conversation": inputOVON["ovon"]["conversation"],
            "schema": {
                "version": "0.9.4",
                "url": "not_published_yet"
            },
            "sender": {"from": sender_from},
            "events": []
        }
    }

    if include_manifest_request:
        manifestRequestEvent = {
            "eventType": "publishManifest",
            "parameters": {
                "manifest": current_manifest
            }
        }
        ovon_response["ovon"]["events"].append(manifestRequestEvent)

    utterance_event = {
        "eventType": "utterance",
        "parameters": {
            "dialogEvent": {
                "speakerId": "assistant",
                "span": {
                    "startTime": currentTime
                },
                "features": {
                    "text": {
                        "mimeType": "text/plain",
                        "tokens": [{"value": response_text}]
                    }
                }
            }
        }
    }
    ovon_response["ovon"]["events"].append(utterance_event)

    return json.dumps(ovon_response)
