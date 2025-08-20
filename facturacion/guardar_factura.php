<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
//verificarPermisoPagina();

$conn = getConnection();
$conn->begin_transaction();

try {
    // Valores desde el formulario
    $idcliente    = intval($_POST['idcliente']);
    $subtotal     = floatval($_POST['subtotal']);
    $iva          = floatval($_POST['iva']);
    $total_final  = floatval($_POST['total_final']);

    $productos    = $_POST['productos'] ?? [];
    $cantidades   = $_POST['cantidades'] ?? [];
    $precios      = $_POST['precios'] ?? [];

    // Usuario logueado
    $idusuario = $_SESSION['idusuario'] ?? 1; // <-- ajusta según tu login

    if (empty($idcliente) || count($productos) == 0) {
        throw new Exception("Datos incompletos para guardar la factura");
    }

    // Insertar factura
    $stmt = $conn->prepare("
        INSERT INTO facturas (idcliente, idusuario, fecha, total, iva, activo, usuarioregistra, fecharegistro) 
        VALUES (?, ?, NOW(), ?, ?, 1, ?, NOW())
    ");
    $stmt->bind_param("iidds", $idcliente, $idusuario, $total_final, $iva, $idusuario);
    $stmt->execute();
    $idfactura = $conn->insert_id;
    $stmt->close();

    // Insertar detalle + movimientos
    $stmtDetalle = $conn->prepare("
        INSERT INTO detalle_factura (idfactura, idproducto, cantidad, precio, subtotal) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmtMovimiento = $conn->prepare("
        INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, fecha, idusuario, activo) 
        VALUES (?, 'salida', ?, ?, NOW(), ?, 1)
    ");

    foreach ($productos as $index => $productoData) {
        $idproducto = intval(explode("|", $productoData)[0]);
        $cantidad   = intval($cantidades[$index]);
        $precio     = floatval($precios[$index]);
        $subtotalP  = $cantidad * $precio;

        // Guardar detalle
        $stmtDetalle->bind_param("iiidd", $idfactura, $idproducto, $cantidad, $precio, $subtotalP);
        $stmtDetalle->execute();

        // Guardar movimiento
        $comentario = "Factura #".$idfactura;
        $stmtMovimiento->bind_param("iisi", $idproducto, $cantidad, $comentario, $idusuario);
        $stmtMovimiento->execute();
    }

    $stmtDetalle->close();
    $stmtMovimiento->close();

    $conn->commit();

    $_SESSION['mensaje'] = "Factura guardada correctamente, No. Fact: $idfactura";
    $_SESSION['tipoMensaje'] = "success";
    header("Location: facturas.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['mensaje'] = "Error al guardar factura: " . $e->getMessage();
    $_SESSION['tipoMensaje'] = "error";
    header("Location: facturas.php");
    exit;
}
?>