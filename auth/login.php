<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if (empty($usuario) || empty($password)) {
        header("Location: ../index.php?error=Usuario o contraseña requeridos.");
        exit();
    }

    try {
        $conn = getConnection();

        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = b'1' LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $hashedInput = hash('sha256', $password);

            if ($hashedInput === $user['contrasena']) {
                // Guardar datos en la sesión
                $_SESSION['idusuario'] = $user['idusuario'];
                $_SESSION['nombrecompleto'] = $user['nombrecompleto'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['idrol'] = $user['idrol'];

                header("Location: ../admin/dashboard_admin.php");
                exit();
            } else {
                header("Location: ../index.php?error=Contraseña incorrecta.");
                exit();
            }
        } else {
            header("Location: ../index.php?error=Usuario no encontrado o inactivo.");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error login: " . $e->getMessage());
        header("Location: ../index.php?error=Error en el servidor.");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
