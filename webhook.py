from flask import Flask, request, jsonify
from flask_socketio import SocketIO, emit

app = Flask(__name__)
app.config['SECRET_KEY'] = 'secret!'
socketio = SocketIO(app, cors_allowed_origins="*")

# Ruta del webhook para recibir notificaciones
@app.route('/webhook', methods=['POST'])
def webhook():
    data = request.json

    # Validar datos
    if not data or 'action' not in data or 'data' not in data:
        return jsonify({"error": "Datos inválidos"}), 400

    action = data['action']
    contenido = data['data']

    # Determinar el mensaje según la acción
    if action == "create":
        message = f"Nuevo contenido agregado: {contenido['titulo']}."
    elif action == "update":
        message = f"Contenido actualizado: {contenido['titulo']}."
    else:
        return jsonify({"error": "Acción no válida"}), 400

    # Enviar la notificación a los clientes conectados
    socketio.emit('notification', {'message': message})

    return jsonify({"message": "Notificación enviada correctamente"}), 200

# Ruta para la conexión WebSocket
@socketio.on('connect')
def handle_connect():
    print('Cliente conectado')

@socketio.on('disconnect')
def handle_disconnect():
    print('Cliente desconectado')

if __name__ == '__main__':
    socketio.run(app, port=5001)
