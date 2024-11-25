<?php
// Configuración de la base de datos
$host = "MYSQL5035.site4now.net";
$usuario_db = "aaf49d_quiosco";  // Asegúrate de que es el usuario correcto
$clave_db = "rmskk2020";        // Asegúrate de que es la contraseña correcta
$nombre_db = "db_aaf49d_quiosco";  // Reemplaza con el nombre correcto de tu base de datos

// Crear la conexión
$conn = new mysqli($host, $usuario_db, $clave_db, $nombre_db);


// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Comprobamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Verificamos si el correo ya está registrado
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Si el correo ya está registrado, mostramos un mensaje
        echo "<script>alert('Este correo ya está registrado.'); window.location.href='login.html';</script>";
    } else {
        // Si el correo no está registrado, procedemos con el registro
        // Hash de la contraseña para guardarla de manera segura
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertamos los datos en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, correo, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $apellido, $correo, $password);

        if ($stmt->execute()) {
            // Si se registró correctamente, redirigimos al login
            echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión.'); window.location.href='login.html';</script>";
        } else {
            // Si ocurre algún error en la inserción
            echo "<script>alert('Error en el registro.'); window.location.href='registro.html';</script>";
        }
    }

    // Cerramos la conexión
    $stmt->close();
    $conn->close();
}
?>
