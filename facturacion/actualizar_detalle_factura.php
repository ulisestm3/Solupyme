<?php 
require_once('../config/database.php');
require_once('../config/seguridad.php');
// verificarPermisoPagina();

$conn = getConnection();
$conn->begin_transaction();

try {
    $idfactura   = intval($_POST['idfactura']);
    $productos   = $_POST['productos'] ?? [];
    $cantidades  = $_POST['cantidades'] ?? [];
    $precios     = $_POST['precios'] ?? [];
    $subtotal    = floatval($_POST['subtotal']);
    $iva         = floatval($_POST['iva']);
    $total_final = floatval($_POST['total_final']);

    if (empty($productos) || $idfactura <= 0) {
        throw new Exception("Datos incompletos para guardar.");
    }

    if (count($productos) !== count($cantidades) || count($productos) !== count($precios)) {
        throw new Exception("Error en los datos enviados de productos.");
    }

    $idusuario = $_SESSION['idusuario'] ?? 1;

    // === 1. Obtener productos actuales (para devolver stock) ===
    $stmtAnterior = $conn->prepare("
        SELECT df.idproducto, df.cantidad
        FROM detalle_factura df
        WHERE df.idfactura = ?
    ");
    $stmtAnterior->bind_param("i", $idfactura);
    $stmtAnterior->execute();
    $resultAnterior = $stmtAnterior->get_result();
    $productosAnteriores = $resultAnterior->fetch_all(MYSQLI_ASSOC);

    // === 2. Obtener idcliente actual ===
    $stmtCliente = $conn->prepare("SELECT idcliente FROM facturas WHERE idfactura = ?");
    $stmtCliente->bind_param("i", $idfactura);
    $stmtCliente->execute();
    $cliente = $stmtCliente->get_result()->fetch_assoc();
    $idcliente = $cliente['idcliente'];

    // === 3. Eliminar detalle actual ===
    $stmtDelete = $conn->prepare("DELETE FROM detalle_factura WHERE idfactura = ?");
    $stmtDelete->bind_param("i", $idfactura);
    $stmtDelete->execute();

    // === 4. Preparar inserciones ===
    $stmtDetalle = $conn->prepare("
        INSERT INTO detalle_factura (idfactura, idproducto, cantidad, precio, subtotal) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmtMovimiento = $conn->prepare("
        INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, fecha, idusuario, activo) 
        VALUES (?, ?, ?, ?, NOW(), ?, 1)
    ");

    $comentario_update = "Actualización Factura #{$idfactura}";

    // --- Devolver stock anterior (entrada) ---
    foreach ($productosAnteriores as $p) {
        $tipo = "entrada";
        $stmtMovimiento->bind_param("isisi", $p['idproducto'], $tipo, $p['cantidad'], $comentario_update, $idusuario);
        $stmtMovimiento->execute();
    }

    // --- Registrar nuevas salidas ---
    foreach ($productos as $index => $idproducto) {
        $idproducto = intval($idproducto);
        $cantidad   = intval($cantidades[$index]);
        $precio     = floatval($precios[$index]);
        $subtotalP  = $cantidad * $precio;

        // Insertar detalle
        $stmtDetalle->bind_param("iiidd", $idfactura, $idproducto, $cantidad, $precio, $subtotalP);
        $stmtDetalle->execute();

        // Registrar salida
        $tipo = "salida";
        $stmtMovimiento->bind_param("isisi", $idproducto, $tipo, $cantidad, $comentario_update, $idusuario);
        $stmtMovimiento->execute();
    }

    // === 5. Actualizar factura ===
    $stmtFactura = $conn->prepare("
        UPDATE facturas 
        SET idcliente = ?, total = ?, iva = ?, fechaactualizacion = NOW(), usuarioactualiza = ? 
        WHERE idfactura = ?
    ");
    $stmtFactura->bind_param("iddii", $idcliente, $total_final, $iva, $idusuario, $idfactura);
    $stmtFactura->execute();

    $conn->commit();

    $_SESSION['mensaje'] = "Factura actualizada correctamente. No. Fact: $idfactura";
    $_SESSION['tipoMensaje'] = "success";
    header("Location: detalle_factura.php?id=$idfactura");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['mensaje'] = "Error al guardar: " . $e->getMessage();
    $_SESSION['tipoMensaje'] = "error";
    header("Location: detalle_factura.php?id=$idfactura");
    exit();
}
