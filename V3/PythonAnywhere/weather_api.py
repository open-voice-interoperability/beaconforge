import requests

def get_weather(api_key, city):
    base_url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(base_url)
    return response.json()

def parse_weather_data(weather_data):
    # Safely check if the response contains valid weather info
    if "main" not in weather_data or "weather" not in weather_data:
        error_message = weather_data.get("message", "Unexpected response structure")
        print(f"[Weather Error] Response: {weather_data}")  # Log the raw response for debug
        raise ValueError(f"Invalid weather data received: {error_message}")

    main = weather_data["main"]
    temp = main["temp"]
    humidity = main["humidity"]
    weather_report = weather_data["weather"][0]["description"]
    return temp, humidity, weather_report
