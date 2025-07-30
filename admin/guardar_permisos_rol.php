<?php
require_once('../config/database.php');
session_start();

// Solo admin
if ($_SESSION['idrol'] != 1) {
    header("Location: dashboard_admin.php");
    exit();
}

$conn = getConnection();
$idrol = $_POST['idrol'];
$paginasSeleccionadas = $_POST['paginas'] ?? [];

// Eliminar permisos actuales
$stmt = $conn->prepare("DELETE FROM roles_paginas WHERE idrol = ?");
$stmt->bind_param("i", $idrol);
$stmt->execute();

// Insertar nuevos permisos
$stmt = $conn->prepare("INSERT INTO roles_paginas (idrol, pagina) VALUES (?, ?)");
foreach ($paginasSeleccionadas as $pagina) {
    $stmt->bind_param("is", $idrol, $pagina);
    $stmt->execute();
}

header("Location: permisos_por_rol.php?idrol=" . $idrol);
exit();
