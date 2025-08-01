<?php
require_once('../config/database.php');
$conn = getConnection();

$response = ['success' => false, 'stock' => 0];

if (isset($_GET['idproducto'])) {
    $idproducto = intval($_GET['idproducto']);
    $stmt = $conn->prepare("SELECT stock FROM productos WHERE idproducto = ? AND activo = b'1'");
    $stmt->bind_param("i", $idproducto);
    $stmt->execute();
    $stmt->bind_result($stock);
    if ($stmt->fetch()) {
        $response['success'] = true;
        $response['stock'] = $stock;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
