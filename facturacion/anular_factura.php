<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
// verificarPermisoPagina();

header('Content-Type: application/json');

$conn = getConnection();
$conn->begin_transaction();

try {
    $idfactura = intval($_POST['idfactura'] ?? 0);

    if ($idfactura <= 0) {
        throw new Exception("ID de factura inválido.");
    }

    $idusuario = $_SESSION['idusuario'] ?? 1;

    // === 1. Verificar que la factura exista y esté activa ===
    $stmtCheck = $conn->prepare("SELECT activo FROM facturas WHERE idfactura = ?");
    $stmtCheck->bind_param("i", $idfactura);
    $stmtCheck->execute();
    $factura = $stmtCheck->get_result()->fetch_assoc();

    if (!$factura) {
        throw new Exception("Factura no encontrada.");
    }

    if ($factura['activo'] == 0) {
        throw new Exception("La factura ya está anulada.");
    }

    // === 2. Obtener productos y cantidades ===
    $stmtDetalle = $conn->prepare("
        SELECT idproducto, cantidad
        FROM detalle_factura
        WHERE idfactura = ?
    ");
    $stmtDetalle->bind_param("i", $idfactura);
    $stmtDetalle->execute();
    $result = $stmtDetalle->get_result();
    $productos = $result->fetch_all(MYSQLI_ASSOC);

    // === 3. Insertar movimientos de entrada (devolución) ===
    $stmtMovimiento = $conn->prepare("
        INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, fecha, idusuario, activo) 
        VALUES (?, 'entrada', ?, ?, NOW(), ?, 1)
    ");

    $comentario = "Anulación Factura #{$idfactura}";

    foreach ($productos as $p) {
        $stmtMovimiento->bind_param("iisi", $p['idproducto'], $p['cantidad'], $comentario, $idusuario);
        $stmtMovimiento->execute();
    }

    // === 4. Anular factura ===
    $stmtAnular = $conn->prepare("
        UPDATE facturas 
        SET activo = 0, fechaactualizacion = NOW(), usuarioactualiza = ? 
        WHERE idfactura = ?
    ");
    $stmtAnular->bind_param("ii", $idusuario, $idfactura);
    $stmtAnular->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Factura #{$idfactura} anulada correctamente."
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
