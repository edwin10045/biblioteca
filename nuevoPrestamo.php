<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    header("Location: index.html");
    exit();
}

include 'conexion.php';
if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libro_id = intval($_POST['libro_id']);
    $cliente_nombre = mysqli_real_escape_string($enlace, $_POST['cliente_nombre']);
    $cliente_telefono = mysqli_real_escape_string($enlace, $_POST['cliente_telefono']);
    $fecha_limite = mysqli_real_escape_string($enlace, $_POST['fecha_limite']);
    $fecha_prestamo = date('Y-m-d');
    $estado = 'Prestado';
    $multa = 0;

    // Registrar el préstamo
    $insert = "INSERT INTO prestamos (libro_id, cliente_nombre, cliente_telefono, fecha_prestamo, fecha_limite, estado, multa)
               VALUES ($libro_id, '$cliente_nombre', '$cliente_telefono', '$fecha_prestamo', '$fecha_limite', '$estado', $multa)";
    mysqli_query($enlace, $insert);

    // Actualizar el libro
    mysqli_query($enlace, "UPDATE libros SET stock_disponible = stock_disponible - 1 WHERE id = $libro_id");

    header("Location: adminPrestamos.php");
    exit();
}

$libros = mysqli_query($enlace, "SELECT * FROM libros WHERE stock_disponible > 0");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Préstamo</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body { background: #f0fff0; font-family: Arial, sans-serif; padding: 40px; }
        form { max-width: 600px; margin: auto; background: #e8f5e9; padding: 20px; border-radius: 10px; }
        label, input, select { display: block; margin-bottom: 15px; width: 100%; }
        input, select { padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #2ecc71; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #27ae60; }
    </style>
</head>
<body>
   <center><h1>Registrar Nuevo Préstamo</h1></center>
    <form method="POST">
        <label for="libro_id">Libro:</label>
        <select name="libro_id" required>
            <option value="">Seleccione un libro</option>
            <?php while ($libro = mysqli_fetch_assoc($libros)) { ?>
                <option value="<?= $libro['id'] ?>"><?= htmlspecialchars($libro['titulo']) ?> (Stock: <?= $libro['stock_disponible'] ?>)</option>
            <?php } ?>
        </select>

        <label for="cliente_nombre">Nombre del Cliente:</label>
        <input type="text" name="cliente_nombre" required>

        <label for="cliente_telefono">Teléfono del Cliente:</label>
        <input type="text" name="cliente_telefono" required>

        <label for="fecha_limite">Fecha Límite:</label>
        <input type="date" name="fecha_limite" required>

        <button type="submit">Registrar Préstamo</button>
    </form>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
        <?php if ($_SESSION['rol'] === 'Empleado'): ?>

    <a href="catalogo.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        📚 Volver al Catálogo
    </a>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
        <?php endif; ?>
</div>

</body>
</html>
<?php
include 'pie.php';  
?>
