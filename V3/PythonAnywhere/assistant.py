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
                "Always begin your first response by saying: 'Hi, I'm Pete, your Personal Assistant.' "
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
    """
    Handles only OpenFloor v1.0.0 payloads, supporting getManifests, invite, and private utterance for Zeus.
    """
    payload = inputOVON.get("openFloor", {})
    openai_api_key = payload.get("conversation", {}).get("openAIKey")
    if openai_api_key:
        client.api_key = openai_api_key

    conversation_id = payload.get("conversation", {}).get("id")
    if conversation_id not in conversation_state:
        conversation_state[conversation_id] = {}

    location_from_whisper = None
    user_input_text = None
    detected_intents = []
    include_manifest_request = False
    current_manifest = manifest
    response_text = None

    # Process incoming events
    for event in payload.get("events", []):
        event_type = event.get("eventType")

        if event_type == "getManifests":
            to_url = event.get("to", {}).get("serviceUrl", "").lower()
            for name in ["athena", "zeus"]:
                if name in to_url:
                    current_manifest = agent_config.get(name, {}).get("manifest", manifest)
                    break
            include_manifest_request = True
            continue

        if event_type == "invite":
            to_info = event.get("to", {})
            service_url = to_info.get("serviceUrl", "").lower()
            if "athena" in service_url:
                response_text = "Hello, I'm Athena, your Smart Library Agent. Ask me about books or authors!"
            elif "zeus" in service_url:
                response_text = (
                    "Hello, I'm Zeus, your Weather Agent. "
                    "Send me a public utterance with your question, then a private utterance with the location."
                )
            else:
                response_text = "Hi, I'm Pete, your Personal Assistant. How can I assist today?"
            continue

        if event_type != "utterance":
            continue

        to_info = event.get("to", {})
        private_flag = to_info.get("private", False)
        dialog = event.get("parameters", {}).get("dialogEvent", {})
        tokens = dialog.get("features", {}).get("text", {}).get("tokens", [])
        text_value = tokens[0].get("value") if tokens else None

        if not private_flag:
            user_input_text = text_value
            detected_intents.extend(search_intent(user_input_text) or [])
        else:
            location_from_whisper = text_value

    # Handle intents
    if detected_intents:
        for intent in detected_intents:
            if intent["intent"] in ["weather", "zeus"]:
                location = location_from_whisper or intent.get("location", "unknown")
                if location and location.lower() != "unknown":
                    try:
                        weather_data = weather_api.get_weather(weather_key, location)
                        temp, humidity, weather_report = weather_api.parse_weather_data(weather_data)
                        conversation_state[conversation_id]["temp_in_celsius"] = temp
                        response_text = (
                            f"Weather in {location}: {weather_report}, Temperature: {temp}°C, Humidity: {humidity}%"
                        )
                    except Exception as e:
                        response_text = f"Sorry, I couldn't retrieve the weather for {location}. {e}"
                else:
                    response_text = "Could you please provide the location?"
            elif intent["intent"] == "convertTemperature":
                if "temp_in_celsius" in conversation_state[conversation_id]:
                    temp_f = celsius_to_fahrenheit(conversation_state[conversation_id]["temp_in_celsius"])
                    response_text = f"The temperature in Fahrenheit is {temp_f}°F."
                else:
                    response_text = "I don't have a temperature to convert. Please ask for the weather first."
            elif intent["intent"] == "athena":
                response_text = generate_athena_response(user_input_text)
                current_manifest = agent_config.get("athena", {}).get("manifest", manifest)

    if not detected_intents and user_input_text and not response_text:
        response_text = generate_openai_response(user_input_text)
    if not response_text:
        response_text = "I'm not sure how to respond."

    # Build the OpenFloor response
    current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S+02:00")
    openfloor_response = {
        "openFloor": {
            "conversation": payload.get("conversation", {}),
            "schema": {"version": "1.0.0"},
            "sender": {
                "serviceUrl": sender_from,
                "speakerUri": f"tag:{sender_from.split('//')[-1]},2025:4567"
            },
            "events": []
        }
    }

    # Include manifest if requested
    if include_manifest_request:
        openfloor_response["openFloor"]["events"].append({
            "eventType": "publishManifest",
            "parameters": {"manifest": current_manifest}
        })

    # Always reply with an utterance event
    utterance_event = {
        "eventType": "utterance",
        "parameters": {
            "dialogEvent": {
                "speakerUri": f"tag:{sender_from.split('//')[-1]},2025:4567",
                "span": {"startTime": current_time},
                "features": {
                    "text": {
                        "mimeType": "text/plain",
                        "tokens": [{"value": response_text}]
                    }
                }
            }
        }
    }
    openfloor_response["openFloor"]["events"].append(utterance_event)

    return json.dumps(openfloor_response)
