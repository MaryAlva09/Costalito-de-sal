<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans</title>
    <link rel="stylesheet" href="stylesuscripcion.css">
    <script>
        // Función para crear una suscripción
async function createSubscription(planTitle) {
    // Obtener el ID del usuario desde la variable PHP
    const userId = <?php echo json_encode(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null); ?>;

    if (userId === null) {
        alert("Por favor, inicia sesión primero.");
        return;
    }

    // Realizar la solicitud POST al backend con parámetros de consulta en la URL
    fetch(`http://localhost:8000/subscriptions/?user_id=${userId}&title=${planTitle}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);
        alert(`Suscripción creada: ${data.subscription.sub}`);
    })
    .catch((error) => {
        console.error('Error:', error);
        alert("Hubo un error al crear la suscripción.");
    });
}

// Asociar el evento de clic a los botones
window.onload = function() {
    document.getElementById('free-plan').onclick = function() {
        createSubscription('Free');
    };
    document.getElementById('premium-plan').onclick = function() {
        createSubscription('Premium');
    };
};
        // Asociar el evento de clic a los botones
        window.onload = function() {
            document.getElementById('free-plan').onclick = function() {
                createSubscription('Free');
            };
            document.getElementById('premium-plan').onclick = function() {
                createSubscription('Premium');
            };
        };
    </script>
</head>
<body>
    <nav>
        <div class="logo">
            <h1>Kiosco Digital</h1>
        </div>
        <a href="index.php">
            <button type="button">Ir al Inicio</button>
        </a>
        <button>Sign Up</button>
    </nav>
    <div class="container">
        <h1 class="title">Elige Tu Plan</h1>
        <div class="plans">
            <div class="plan">
                <h2 class="plan-title">Free</h2>
                <p class="plan-price">$0/mes</p>
                <ul class="plan-features">
                    <li>-------</li>
                    <li>Acceso Limitado</li>
                    <li>-----</li>
                </ul>
                <button class="btn select-btn" id="free-plan">Obtener Free</button>
            </div>
            <div class="plan premium">
                <h2 class="plan-title">Premium</h2>
                <p class="plan-price">$19.99/mes</p>
                <ul class="plan-features">
                    <li>-------</li>
                    <li>Acceso Ilimitado</li>
                    <li>-------</li>
                </ul>
                <button class="btn select-btn" id="premium-plan">Obtener Premium</button>
            </div>
        </div>
    </div>
</body>
</html>
