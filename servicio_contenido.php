<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath("/servicio1");

// Configuración de la conexión a la base de datos
$host = "MYSQL5035.site4now.net";
$db = "db_aaf49d_quiosco";
$user = "aaf49d_quiosco";
$pass = "rmskk2020";
$dsn = "mysql:host=$host;dbname=$db;charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Función para enviar notificaciones al webhook
function enviarWebhook($action, $data) {
    $url = 'http://localhost:5000/webhook'; // URL del webhook
    $payload = json_encode([
        'action' => $action,
        'data' => $data,
    ]);

    // Depuración para ver qué datos se están enviando
    error_log("Webhook Data: " . print_r($data, true));  // Esto imprimirá los datos en el log de PHP

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload),
    ]);

    // Ejecuta la solicitud cURL
    $response = curl_exec($ch);

    // Revisa si hubo algún error con cURL
    if ($response === false) {
        error_log("cURL Error: " . curl_error($ch));
    } else {
        error_log("cURL Response: " . $response);
    }

    // Obtén el código de respuesta HTTP
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    error_log("HTTP Response Code: " . $http_code);

    curl_close($ch);

    return $response;
}



// Ruta GET para obtener contenido
$app->get('/contenido', function (Request $request, Response $response) use ($pdo) {
    $sql = "SELECT * FROM contenido";
    $stmt = $pdo->query($sql);
    $contenido = $stmt->fetchAll();
    $response->getBody()->write(json_encode($contenido));
    return $response->withHeader('Content-Type', 'application/json');
});

// Ruta POST para agregar contenido
$app->post('/contenido', function (Request $request, Response $response) use ($pdo) {
    $data = json_decode($request->getBody()->getContents(), true);

    // Verificar si los datos necesarios están presentes
    if (isset($data['titulo'], $data['descripcion'], $data['categoria'], $data['imagen'], $data['enlace'])) {
        // Sentencia SQL para insertar el contenido
        $sql = "INSERT INTO contenido (titulo, descripcion, categoria, imagen, enlace) VALUES (:titulo, :descripcion, :categoria, :imagen, :enlace)";
        $stmt = $pdo->prepare($sql);

        // Ejecutar la consulta con los parámetros
        $stmt->execute([
            ':titulo' => $data['titulo'],
            ':descripcion' => $data['descripcion'],
            ':categoria' => $data['categoria'],
            ':imagen' => $data['imagen'],
            ':enlace' => $data['enlace']
        ]);

        // Enviar notificación mediante webhook
        enviarWebhook("create", [
            "titulo" => $data['titulo'],
            "descripcion" => $data['descripcion'],
            "categoria" => $data['categoria'],
            "imagen" => $data['imagen'],
            "enlace" => $data['enlace']
        ]);

        // Respuesta exitosa
        $response->getBody()->write(json_encode(['message' => 'Contenido agregado correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } else {
        // Respuesta con error si faltan datos
        $response->getBody()->write(json_encode(['error' => 'Faltan datos necesarios']));  
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});


/// Ruta PUT para actualizar contenido
$app->put('/contenido/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $id = $args['id']; // Obtener el ID de la URL
    $data = json_decode($request->getBody()->getContents(), true); // Obtener los datos del cuerpo de la solicitud

    // Composición de la sentencia SQL para la actualización
    $sql = "UPDATE contenido SET 
                titulo = :titulo, 
                descripcion = :descripcion, 
                categoria = :categoria, 
                imagen = :imagen, 
                enlace = :enlace 
            WHERE id = :id";

    // Preparar la consulta
    $stmt = $pdo->prepare($sql);

    // Ejecutar la actualización con los datos proporcionados
    $stmt->execute([
        ':titulo' => $data['titulo'] ?? null, // Usar null si el campo no está presente
        ':descripcion' => $data['descripcion'] ?? null,
        ':categoria' => $data['categoria'] ?? null,
        ':imagen' => $data['imagen'] ?? null,
        ':enlace' => $data['enlace'] ?? null,
        ':id' => $id
    ]);

    // Enviar un webhook con los datos actualizados
    enviarWebhook("update", [
        "id" => $id,
        "titulo" => $data['titulo'] ?? null,
        "descripcion" => $data['descripcion'] ?? null,
        "categoria" => $data['categoria'] ?? null,
        "imagen" => $data['imagen'] ?? null,
        "enlace" => $data['enlace'] ?? null
    ]);

    // Responder con un mensaje de éxito
    $response->getBody()->write(json_encode(['message' => 'Contenido actualizado correctamente']));
    return $response->withHeader('Content-Type', 'application/json');
});


// Ruta DELETE para borrar contenido
$app->delete('/contenido/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $id = $args['id'];

    // Obtener el título del contenido antes de eliminarlo
    $sqlSelect = "SELECT titulo FROM contenido WHERE id = :id";
    $stmtSelect = $pdo->prepare($sqlSelect);
    $stmtSelect->execute([':id' => $id]);
    $contenido = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if ($contenido) {
        $titulo = $contenido['titulo'];

        // Eliminar el contenido
        $sqlDelete = "DELETE FROM contenido WHERE id = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([':id' => $id]);

        // Enviar el webhook con el título incluido
        enviarWebhook("delete", [
            "id" => $id,
            "titulo" => $titulo
        ]);

        $response->getBody()->write(json_encode(['message' => 'Contenido eliminado correctamente']));
    } else {
        // Si no se encuentra el contenido, devolver un error
        $response->getBody()->write(json_encode(['error' => 'Contenido no encontrado']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    return $response->withHeader('Content-Type', 'application/json');
});


$app->addErrorMiddleware(true, true, true);

$app->run();
