const WebSocket = require('ws');
const http = require('http');

// Crear un servidor HTTP
const server = http.createServer((req, res) => {
    if (req.method === 'POST' && req.url === '/webhook') {
        let body = '';

        req.on('data', (chunk) => {
            body += chunk.toString(); // Acumular datos del cuerpo de la solicitud
        });

        req.on('end', () => {
            try {
                const data = JSON.parse(body); // Convertir el cuerpo en JSON
                console.log('Datos recibidos:', data);

                // Enviar los datos a los clientes WebSocket conectados
                wss.clients.forEach((client) => {
                    if (client.readyState === WebSocket.OPEN) {
                        // Enviar los datos a los clientes como un mensaje JSON
                        client.send(JSON.stringify(data));
                    }
                });

                // Responder al webhook
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ message: 'Notificación enviada a los clientes.' }));
            } catch (err) {
                console.error('Error al procesar la solicitud:', err);
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Solicitud inválida.' }));
            }
        });
    } else {
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end('Ruta no encontrada.');
    }
});

// Crear un servidor WebSocket
const wss = new WebSocket.Server({ server });

wss.on('connection', (ws) => {
    console.log('Cliente WebSocket conectado.');

    // Manejar mensaje recibido del cliente
    ws.on('message', (message) => {
        console.log('Mensaje recibido del cliente:', message);
    });

    // Manejar desconexión del cliente
    ws.on('close', () => {
        console.log('Cliente WebSocket desconectado.');
    });

    // Enviar un mensaje inicial al cliente cuando se conecte
    ws.send(JSON.stringify({
        action: 'welcome',
        data: { message: 'Conexión establecida con el servidor WebSocket.' }
    }));
});

// Iniciar el servidor en el puerto 5000
server.listen(5000, () => {
    console.log('Servidor ejecutándose en http://localhost:5000');
});
