# This file contains the WSGI configuration required to serve up your
# web application at http://<your-username>.pythonanywhere.com/
# It works by setting the variable 'application' to a WSGI handler of some
# description.
# You can use this file to EDIT your WSGI configuration file that you can find in the Web Tab of your pythonanywhere environment
# SUBSTITUTE every occurence of "beaconforge" with your pythonanywhere endpoint and the KEYS with your API keys
# The below has been auto-generated for your Flask project

import sys

import os
os.environ["OPENAI_API_KEY"] = "sk-proj-your-key"  # Your actual OpenAI key here
os.environ["WEATHER_API_KEY"] = "your-key"
os.environ["SERVICE_ENDPOINT"] = "https://beaconforge.pythonanywhere.com"

# add your project directory to the sys.path
project_home = '/home/beaconforge/mysite'
if project_home not in sys.path:
    sys.path = [project_home] + sys.path

# import flask app but need to call it "application" for WSGI to work
from flask_app import app as application  # noqa
