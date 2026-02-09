<?php
session_start();
include 'conexion.php';

$id_libro = mysqli_real_escape_string($enlace, $_GET['id_libro']);
include 'encabezado_empleado.php';

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Apartar Libro</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <h2>Apartar libro</h2>
  <form action="procesarApartado.php" method="POST">
    <input type="hidden" name="libro_id" value="<?= $id_libro ?>">
    <input type="hidden" name="empleado_id" value="<?= $_SESSION['id'] ?>">
    <input type="text" name="cliente_nombre" placeholder="Tu nombre" required>
    <input type="text" name="cliente_telefono" placeholder="Teléfono" required>
    <input type="date" name="fecha_prestamo" required>
    <input type="date" name="fecha_limite" required>
    <button type="submit">Confirmar apartado</button>
  </form>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
    <a href="catalogo.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        📚 Volver al Catálogo
    </a>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
</div>

</body>
</html>
<?php include 'pie.php'; ?>

