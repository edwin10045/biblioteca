<?php
session_start();
include 'conexion.php';
include 'encabezado_empleado.php';

if (!isset($_SESSION['id'])) {
    header('Location: index.html');
    exit;
}
$id_empleado = intval($_SESSION['id']);
$empleado = mysqli_fetch_assoc(mysqli_query($enlace, "SELECT * FROM empleados WHERE id = $id_empleado"));
if (!$empleado) {
    echo "Empleado no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta - Empleado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f8ff;
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
            color: #d35400;
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
            background-color: #d35400;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn-cerrar:hover {
            background-color: #b03a0c;
        }
    </style>
</head>
<body>

<div class="perfil-container">
    <h2>Información de la cuenta</h2>

    <div class="perfil-dato"><span>Nombre completo:</span> <?= htmlspecialchars($empleado['nombre_completo']) ?></div>
    <div class="perfil-dato"><span>Teléfono:</span> <?= htmlspecialchars($empleado['telefono']) ?></div>
    <div class="perfil-dato"><span>Fecha de contratación:</span> <?= htmlspecialchars($empleado['fecha_contratacion']) ?></div>
    <div class="perfil-dato"><span>Rol:</span> <?= htmlspecialchars($empleado['rol']) ?></div>

    <form method="POST" action="cerrarSesion.php" style="margin-top: 20px;">
        <button type="submit" class="btn-cerrar">Cerrar sesión</button>
    </form>
</div>

</body>
</html>
<?php include 'pie.php'; ?>