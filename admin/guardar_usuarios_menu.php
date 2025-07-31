<?php
require_once('../config/database.php');
session_start();

$conn = getConnection();
$idusuario = $_POST['idusuario'];
$clavesSeleccionados = $_POST['claves'] ?? [];

// Eliminar permisos actuales
$stmt = $conn->prepare("DELETE FROM usuarios_menus WHERE idusuario = ?");
$stmt->bind_param("i", $idusuario);
$stmt->execute();

// Insertar nuevos permisos
$stmt = $conn->prepare("INSERT INTO usuarios_menus (idusuario, clave) VALUES (?, ?)");
foreach ($clavesSeleccionados as $clave) {
    $stmt->bind_param("is", $idusuario, $clave);
    $stmt->execute();
}

header("Location: asignar_menu_usuario.php?idusuario=" . $idusuario);
exit();
