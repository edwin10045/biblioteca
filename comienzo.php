<?php
session_start(); 
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Empleado') {
    header("Location: index.html");
    exit();
}
include 'encabezado_empleado.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body {
            background: #f0f8ff;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            text-align: center;
        }
        .btn-catalogo {
            background: #67aee8;
            color: #fff;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            margin-top: 40px;
            transition: background 0.3s;
        }
        .btn-catalogo:hover {
            background: #4c94d4;
        }
    </style>
</head>
<body>
    <h1>Bienvenido</h1>

    <a href="catalogo.php">
        <button class="btn-catalogo">Ver Catálogo</button>
    </a>
    <br><br>
    <a href="registrar_compra.php">
        <button class="btn-catalogo" style="background:#2980b9;">Registrar compra</button>
    </a>
    <br><br>

    <a href="enviar_comprobantes.php">
        <button class="btn-catalogo" style="background:#8e44ad;">📧 Enviar Comprobantes</button>
    </a>
    <br><br>

    <a href="consultasReservas.php">
        <button class="btn-catalogo" style="background:#d35400;">Administrar Reservas</button>
    </a>
    <br><br>

    <a href="adminPrestamos.php">
        <button class="btn-catalogo" style="background:#27ae60;">Administrar Préstamos</button>
    </a>
    <br><br>

     <a href="devoluciones.php">
        <button class="btn-catalogo" style="background:#27ae60;">Administrar Devoluciones</button>
    </a>
</body>
<?php include 'pie.php'; ?>
