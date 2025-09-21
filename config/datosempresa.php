<?php
function getDatosEmpresa() {
    $conn = getConnection();
    $sql = "SELECT idempresa, nombrecormercial, razonsocial, ruc, direccion, contacto FROM empresa LIMIT 1";
    $result = $conn->query($sql);
    $empresa = $result->fetch_assoc();
    $conn->close();
    return $empresa;
}
?>