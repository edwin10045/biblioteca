<?php
session_start();
include 'conexion.php';

$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    // La contraseña ya no es obligatoria para la lógica, pero la capturamos para evitar errores si el form la envía
    $clave = trim($_POST['contraseña'] ?? '');

    // Solo verificamos que se haya escrito un usuario
    if ($usuario === '') {
        $mensaje_error = '❌ Por favor ingresa un nombre de usuario';
    } else {
        // Buscamos al usuario en la base de datos
        $stmt = $enlace->prepare("SELECT * FROM empleados WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Si existe el usuario (num_rows === 1), entramos directo
        if ($resultado && $resultado->num_rows === 1) {
            $datos = $resultado->fetch_assoc();

            // --- INICIO DE SESIÓN DIRECTO (Sin verificar clave ni estado) ---
            $_SESSION['id'] = $datos['id'];
            $_SESSION['nombre'] = $datos['nombre_completo'];
            $_SESSION['rol'] = $datos['rol'];

            if ($datos['rol'] === 'Administrador') {
                header("Location: menuadmin.php");
            } else {
                header("Location: comienzo.php");
            }
            exit();
            
        } else {
            $mensaje_error = '❌ Usuario no encontrado';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión (Solo Usuario)</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body { background: #f0f8ff; font-family: 'Poppins', sans-serif; }
        .login-box { max-width: 400px; margin: 80px auto; background: #fff3e0; padding: 30px 30px 20px 30px; border-radius: 10px; box-shadow: 0 2px 10px #ccc; }
        h2 { color: #d35400; text-align: center; }
        label { display: block; margin-top: 15px; color: #d35400; }
        input[type=text], input[type=password] { width: 100%; padding: 8px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
        button { width: 100%; margin-top: 20px; background: #67aee8; color: #fff; border: none; padding: 10px; border-radius: 7px; font-size: 16px; cursor: pointer; }
        button:hover { background: #4c94d4; }
        .error-msg { color: #f55; text-align: center; margin-bottom: 10px; background: none !important; box-shadow: none !important; border: none !important; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <?php if (!empty($mensaje_error)) { echo "<div class='error-msg'>$mensaje_error</div>"; } ?>
        <form method="POST" autocomplete="off">
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" required>
            
            <label for="contraseña">Contraseña (Opcional)</label>
            <input type="password" name="contraseña" id="contraseña">
            
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>