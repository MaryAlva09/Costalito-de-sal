<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no está autenticado
    exit();
}

// Conexión a la base de datos
$host = "MYSQL5035.site4now.net";
$usuario_db = "aaf49d_quiosco";
$clave_db = "rmskk2020";
$nombre_db = "db_aaf49d_quiosco";

try {
    // Construir el DSN
    $dsn = "mysql:host=$host;dbname=$nombre_db;charset=utf8";
    
    // Crear la conexión PDO
    $pdo = new PDO($dsn, $usuario_db, $clave_db);
    
    // Configurar el modo de error de PDO a excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión exitosa.";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}

// Recuperar el correo del usuario desde la sesión
$email = $_SESSION['usuario'];

// Consultar los datos del usuario en la base de datos
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit();
}

// Obtener el ID del usuario
$user_id = $usuario['id'];

// Consultar la suscripción asociada al usuario
$stmtSubs = $pdo->prepare("SELECT s.sub AS suscripcion
                           FROM subscripciones s
                           WHERE s.id = :user_id AND s.estatus = 1"); // Solo suscripciones activas
$stmtSubs->bindParam(':user_id', $user_id);
$stmtSubs->execute();
$suscripciones = $stmtSubs->fetch(PDO::FETCH_ASSOC);

if (!$suscripciones) {
    echo "No tienes ninguna suscripción activa.";
    $suscripciones['suscripcion'] = 'Ninguna'; // Si no tiene suscripción
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil</title>
  <link rel="stylesheet" href="styleperfil.css">
</head>
<body>
  <header>
    <nav>
      <div class="logo"><h1>Kiosco Digital</h1></div>
      <div class="links">
        <ul>
          <li><a class="active" href="#">Perfil</a></li>
          <li><a href="index.php">Inicio</a></li>
          <li><a href="#">Buscar</a></li>
        </ul>
      </div>

      <div class="login-sec">
        <button><a href="logout.php">Logout</a></button> <!-- Enlace para cerrar sesión -->
      </div>
    </nav>

    <section class="user-profile">
      <div class="profile-image">
          <img src="pngwing.com.png" alt="Foto de perfil">
      </div>
      <div class="profile-info">
          <h2>Nombre de Usuario: <?php echo $usuario['nombre']; ?></h2> <!-- Nombre del usuario -->
          <p>Apellido: <?php echo $usuario['apellido']; ?></p> <!-- Descripción del usuario -->
          <p>Correo electrónico: <?php echo $usuario['correo']; ?></p> <!-- Correo del usuario -->
          <p>Suscripción: <?php echo $suscripciones['suscripcion']; ?></p> <!-- Suscripción del usuario -->
          <a href="suscripcion.php">Cambiar suscripción</a> <!-- Enlace para cambiar la suscripción -->
      </div>
    </section>
  </header>
</body>
</html>
