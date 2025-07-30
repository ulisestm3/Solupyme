<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar si hay sesión activa
if (!isset($_SESSION['idusuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

function verificarRol($rolesPermitidos = []) {
    if (!in_array($_SESSION['idrol'], $rolesPermitidos)) {
        // Rol no permitido: redirigir a inicio
        header("Location: ../admin/dashboard_admin.php?error=Acceso no autorizado.");
        exit();
    }
}
?>
