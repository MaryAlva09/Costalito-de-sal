<?php
// Configuración de la base de datos
$host = "MYSQL5035.site4now.net";
$usuario_db = "aaf49d_quiosco";
$clave_db = "rmskk2020";
$nombre_db = "db_aaf49d_quiosco";

// Crear la conexión
$conn = new mysqli($host, $usuario_db, $clave_db, $nombre_db);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Comprobamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos los datos del formulario
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Preparamos la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Comprobamos si el usuario existe
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        // Verificamos si la contraseña es correcta (comparación directa, ya que está en texto plano)
        if ($password == $usuario['password']) {
            // Almacenamos la sesión
            session_start();
            $_SESSION['usuario'] = $email;
            $_SESSION['user_id'] = $usuario['id']; 

            // Si el usuario es el administrador
            if ($email == 'eduardo-cofe@hotmail.com') {
                // Redirigir a la página de administrador (formulario para ingresar contenido)
                header("Location: interfazadmin.html");
            } else {
                // Redirigir a la página principal para usuarios normales (index)
                header("Location: index.php");
            }
            exit(); // Detenemos la ejecución del script
        } else {
            // Contraseña incorrecta
            echo "<script>alert('Contraseña incorrecta.'); window.location.href='login.html';</script>";
        }
    } else {
        // Si no se encuentra el usuario
        echo "<script>alert('El correo no está registrado.'); window.location.href='login.html';</script>";
    }

    // Cerramos la conexión
    $stmt->close();
    $conn->close();
}
?>
