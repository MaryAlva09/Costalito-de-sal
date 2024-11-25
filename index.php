<?php
// Datos de la conexión a la base de datos
$host = "MYSQL5035.site4now.net";
$usuario_db = "aaf49d_quiosco";
$clave_db = "rmskk2020";
$nombre_db = "db_aaf49d_quiosco";

// Crear la conexión
$conn = new mysqli($host, $usuario_db, $clave_db, $nombre_db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los datos de la tabla `contenido`
$sql = "SELECT titulo, descripcion, imagen, enlace FROM contenido";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiosco Digital</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        nav {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
        }
        nav a {
            color: #1f2937;
            text-decoration: none;
            margin-left: 1rem;
        }
        h1 {
            font-size: 2.25rem;
            color: #1f2937;
            margin-bottom: 2rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-content {
            padding: 1.5rem;
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
        }
        footer {
            background-color: #1f2937;
            color: #ffffff;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <a href="#" style="font-size: 1.5rem; font-weight: bold;">Quiosco Digital</a>
            <div>
                <a href="#home">Inicio</a>
                <a href="#search">Buscar</a>
                <a href="perfil.php">Perfil</a>
                <a href="login.html" class="button">Login</a>
            </div>
        </div>
    </nav>

    <main class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        <section id="home">
            <h1>Bienvenido al Quiosco Digital</h1>
            <div class="grid">
                <?php
                if ($resultado->num_rows > 0) {
                    // Generar tarjetas para cada registro en la tabla
                    while ($fila = $resultado->fetch_assoc()) {
                        echo '<div class="card">';
                        echo '<img src="' . htmlspecialchars($fila['imagen']) . '" alt="' . htmlspecialchars($fila['titulo']) . '" style="width: 100%; height: 200px; object-fit: cover;">';
                        echo '<div class="card-content">';
                        echo '<h2>' . htmlspecialchars($fila['titulo']) . '</h2>';
                        echo '<p>' . htmlspecialchars($fila['descripcion']) . '</p>';
                        echo '<a href="' . htmlspecialchars($fila['enlace']) . '" class="button">Leer ahora</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No hay contenido disponible.</p>";
                }
                ?>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2024 Quiosco Digital. Todos los derechos reservados.</p>
    </footer>
    <script>
    const socket = new WebSocket('ws://localhost:5000');

    document.addEventListener('DOMContentLoaded', () => {
        Notification.requestPermission().then(resultado => {
            console.log('Permiso de notificación:', resultado);
        });
    });

    socket.addEventListener('message', (event) => {
        const data = JSON.parse(event.data);
        console.log('Notificación recibida:', data);

        if (data && data.event === 'subscription_created' && data.data) {
            manejarNotificacionSuscripcion(data.data);
        } else if (data && data.action && data.data) {
            manejarNotificacionContenido(data);
        } else {
            console.error('Datos no válidos o tipo de mensaje desconocido:', data);
        }
    });

    function manejarNotificacionContenido(data) {
        const { action } = data;
        const { titulo, descripcion } = data.data;

        console.log('Titulo recibido:', titulo);
        console.log('Descripcion recibida:', descripcion);

        let notificationTitle = '';
        switch (action) {
            case 'create':
                notificationTitle = `📢 Nuevo contenido agregado: ${titulo || 'Sin título'}`;
                break;
            case 'update':
                notificationTitle = `📢 Contenido actualizado: ${titulo || 'Sin título'}`;
                break;
            case 'delete':
                notificationTitle = `📢 Contenido borrado: ${titulo || 'Sin título'}`;
                break;
            default:
                console.error('Acción desconocida:', action);
                return;
        }

        const notificationBody = descripcion || 'Sin descripción';

        console.log('Título de la notificación:', notificationTitle);
        console.log('Cuerpo de la notificación:', notificationBody);

        if (Notification.permission === 'granted') {
            new Notification(notificationTitle, {
                body: notificationBody,
                icon: 'logo.jpg',
            });
        } else {
            console.error('Permiso de notificación denegado o no solicitado.');
        }
    }

    function manejarNotificacionSuscripcion(subscriptionData) {
    const { user_id, title, username } = subscriptionData;  // Asegúrate de que 'username' esté en los datos

    if (!user_id || !title || !username) {
        console.error('Datos de suscripción incompletos.');
        return;
    }

    const notificationTitle = `🔔 Nueva suscripción creada`;
    const notificationBody = `El usuario ${username} se ha suscrito al plan ${title}.`;  // Usamos 'username' en lugar de 'user_id'

    console.log('Título de la notificación de suscripción:', notificationTitle);
    console.log('Cuerpo de la notificación de suscripción:', notificationBody);

    if (Notification.permission === 'granted') {
        new Notification(notificationTitle, {
            body: notificationBody,
            icon: 'subscription_icon.jpg',  // Cambia esta ruta si necesitas un ícono específico
        });
    } else {
        console.error('Permiso de notificación denegado o no solicitado.');
    }
}

    socket.addEventListener('close', () => {
        console.log('Conexión al WebSocket cerrada.');
    });

    socket.addEventListener('error', (error) => {
        console.error('Error en WebSocket:', error);
    });
</script>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
