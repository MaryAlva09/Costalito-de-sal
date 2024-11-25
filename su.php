<?php
// Conexión a la base de datos
$servername = "MYSQL5035.site4now.net";
$username = "aaf49d_quiosco"; // Cambia según tu configuración
$password = "rmskk2020";      // Cambia según tu configuración
$dbname = "db_aaf49d_quiosco";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener subscripciones con usuario y correo
$sql = "
    SELECT 
        s.id AS subscripcion_id, 
        CONCAT(u.nombre, ' ', u.apellido) AS usuario, 
        u.correo, 
        s.sub AS suscripcion, 
        s.estatus 
    FROM 
        subscripciones s
    JOIN 
        usuarios u ON s.id = u.id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualización de Subscripciones</title>
    <link rel="stylesheet" href="style3.css">
</head>
<body>
<div class="wrapper">
    <h1 class="panel-title">Información de Subscripciones</h1>
    <a href="interfazadmin.html">
            <button type="button">Ir al Administrador</button>
        </a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Subscripción</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subscripcion_id']) ?></td>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= htmlspecialchars($row['correo']) ?></td>
                        <td><?= htmlspecialchars($row['suscripcion']) ?></td>
                        <td><?= $row['estatus'] == 1 ? 'Activo' : 'Inactivo' ?></td>
                        <td>
                            <button onclick="deleteSubscription(<?= htmlspecialchars($row['subscripcion_id']) ?>)">Eliminar</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay datos disponibles</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    <script>
        async function deleteSubscription(subscriptionId) {
            if (confirm("¿Estás seguro de que deseas eliminar esta suscripción?")) {
                try {
                    const response = await fetch(`http://localhost:8000/subscriptions/${subscriptionId}`, {
                        method: 'DELETE',
                    });

                    if (!response.ok) {
                        throw new Error("Error al eliminar la suscripción");
                    }

                    const result = await response.json();
                    alert("Suscripción eliminada correctamente");
                    // Recargar la página para reflejar los cambios
                    location.reload();
                } catch (error) {
                    alert("Error: " + error.message);
                }
            }
        }
    </script>
</body>
</html>
