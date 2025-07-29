<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Limpiar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión completamente
session_destroy();

// Opcional: eliminar la cookie de sesión (más seguro)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Redirigir a la página de login
header("Location: ../index.php");
exit();
