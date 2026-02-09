<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include 'conexion.php'; 
include 'encabezado_admin.php';

$id_admin = $_SESSION['id'];

$consulta = "SELECT nombre_completo, usuario, telefono, fecha_contratacion, estado, rol
             FROM Empleados
             WHERE id = $id_admin";

$resultado = mysqli_query($enlace, $consulta);
$admin = mysqli_fetch_assoc($resultado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta - Administrador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff7f0;
            color: #333;
            padding: 30px;
        }

        .perfil-container {
            max-width: 500px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            padding: 30px;
            text-align: center;
        }

        .perfil-container h2 {
            color: #3bb2cc;
            margin-bottom: 20px;
        }

        .perfil-dato {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .perfil-dato span {
            font-weight: bold;
        }

        .btn-cerrar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-cerrar:hover {
            background-color: #d93636;
        }
    </style>
</head>
<body>

<div class="perfil-container">
    <h2>Información de la cuenta</h2>

    <div class="perfil-dato"><span>Nombre completo:</span> <?= htmlspecialchars($admin['nombre_completo']) ?></div>
    <div class="perfil-dato"><span>Usuario:</span> <?= htmlspecialchars($admin['usuario']) ?></div>
    <div class="perfil-dato"><span>Teléfono:</span> <?= htmlspecialchars($admin['telefono']) ?></div>
    <div class="perfil-dato"><span>Fecha de contratación:</span> <?= htmlspecialchars($admin['fecha_contratacion']) ?></div>
    <div class="perfil-dato"><span>Estado:</span> <?= htmlspecialchars($admin['estado']) ?></div>
    <div class="perfil-dato"><span>Rol:</span> <?= htmlspecialchars($admin['rol']) ?></div>

    <a href="cerrarSesionAdmin.php" class="btn-cerrar">Cerrar sesión</a>
</div>

</body>
</html>

<?php include 'pie.php'; ?>
