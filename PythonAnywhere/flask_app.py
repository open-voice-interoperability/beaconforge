# The latest working min Server code 20241217
# =================================================
# Note!!!! you will need to install flask_cors
#    open a bash console and do this
#    pip3.10 install --user flask_cors

from flask import Flask
from flask import request
from flask_cors import CORS
import json
import assistant

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

@app.route('/', methods=['POST'])
def home():
    inputOVON = json.loads(request.data)

    # Use PythonAnywhere's environment variables for the host
    host = request.host.split(":")[0]
    sender_from = f"http://{host}"
    ovon_response = assistant.generate_response(inputOVON, sender_from)

    return ovon_response

# You may need to add something like this to run as localhost on your computer
#if __name__ == '__main__':
#    app.run(host="0.0.0.0",port=8767, debug=True, use_reloader=False)