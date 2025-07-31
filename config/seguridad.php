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

function verificarPermisoPagina() {
    if (!isset($_SESSION['idrol'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    $paginaActual = basename($_SERVER['PHP_SELF']); // Ejemplo: usuarios.php

    require_once('database.php');
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT 1 
        FROM permisos 
        WHERE idrol = ? 
          AND pagina = ? 
          AND activo = b'1'
        LIMIT 1
    ");
    
    $stmt->bind_param("is", $_SESSION['idrol'], $paginaActual);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // No tiene permiso
        header("Location: ../admin/dashboard_admin.php?error=Acceso denegado");
        exit();
    }
}

function obtenerMenusUsuario($idusuario) {
    $conn = getConnection();
    $menus = [];

    $sql = "SELECT usuario, clave FROM vista_permisos_menus WHERE idusuario = ? AND activo = b'1' ORDER BY usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $menus[] = $row;
    }

    return $menus;
}

?>
