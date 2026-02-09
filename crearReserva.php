<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Empleado' && $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include 'conexion.php';
if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}
// Guardar reserva si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libro_id = intval($_POST['libro_id']);
    $cliente_nombre = mysqli_real_escape_string($enlace, $_POST['cliente_nombre']);
    $cliente_telefono = mysqli_real_escape_string($enlace, $_POST['cliente_telefono']);
    $fecha_solicitud = date('Y-m-d');
    $estado = 'Pendiente';

    $sql = "INSERT INTO reservas (libro_id, cliente_nombre, cliente_telefono, fecha_solicitud, estado)
            VALUES ($libro_id, '$cliente_nombre', '$cliente_telefono', '$fecha_solicitud', '$estado')";
    
    if (mysqli_query($enlace, $sql)) {
        echo '<script>alert("Reserva Guardada");window.location="consultasReservas.php";</script>';
        exit();
    } else {
        $error = "Error al registrar la reserva.";
    }
}

// Obtener libros disponibles (sin préstamos activos), agregue para no mostrar libros sin stock
$libros = mysqli_query($enlace, "
    SELECT l.id, l.titulo, l.stock_disponible
    FROM libros l
    WHERE l.stock_disponible > 0
    AND NOT EXISTS (
        SELECT 1 FROM prestamos p 
        WHERE p.libro_id = l.id AND (p.estado = 'Prestado' OR p.estado = 'Activa')
    )
");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Reserva</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body { background: #f0f8ff; font-family: 'Poppins', sans-serif; padding: 40px; }
        .form-container {
            background: #fff3e0;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { color: #d35400; text-align: center; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #d35400; }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn-guardar {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .btn-guardar:hover {
            background-color: #1e8449;
        }
        .btn-cancelar {
            background-color: #2980b9;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
        }
        .btn-cancelar:hover {
            background-color: #1c5980;
        }
        .acciones {
            text-align: center;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Registrar Nueva Reserva</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="libro_id">Libro:</label>
            <select name="libro_id" id="libro_id" required>
                <option value="">-- Seleccione un libro --</option>
                <?php while ($libro = mysqli_fetch_assoc($libros)): ?>
<option value="<?= $libro['id'] ?>">
    <?= htmlspecialchars($libro['titulo']) ?> (Stock: <?= $libro['stock_disponible'] ?>)
</option>
                <?php endwhile; ?>
            </select>

            <label for="cliente_nombre">Nombre del cliente:</label>
            <input type="text" name="cliente_nombre" id="cliente_nombre" required>

            <label for="cliente_telefono">Teléfono del cliente:</label>
            <input type="text" name="cliente_telefono" id="cliente_telefono" required>

            <label for="fecha_disponibilidad">Fecha de disponibilidad estimada:</label>
            <input type="date" name="fecha_disponibilidad" id="fecha_disponibilidad">

<div class="acciones">
    <button type="submit" class="btn-guardar">+ Guardar Reserva</button>
    <button type="button" class="btn-cancelar" onclick="window.history.back();">← Cancelar</button>
</div> 

    </div> 

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
        <?php if ($_SESSION['rol'] === 'Empleado'): ?>

    <a href="catalogo.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        📚 Volver al Catálogo
    </a>
    <?php endif; ?>
    <?php if ($_SESSION['rol'] === 'Empleado'): ?>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
    <?php endif; ?>
</div>

    <?php include 'pie.php'; ?>

</body>
</html>
<?php
