<div style="text-align:center; margin-top:20px;">
</body>

<?php
session_start();  

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Empleado') {
    header("Location: index.html");
    exit();
}

include 'conexion.php';

if (!isset($_GET['id'])) {
  echo "<p style='color:#f44;'>⛔ No se especificó un libro.</p>";
  exit();
}

$id = mysqli_real_escape_string($enlace, $_GET['id']);

$query = "SELECT l.*, a.nombre_completo AS autor, e.nombre AS editorial, c.nombre AS categoria
          FROM libros l
          JOIN autores a ON l.autor_id = a.id
          JOIN editoriales e ON l.editorial_id = e.id
          JOIN categorias c ON l.categoria_id = c.id
          WHERE l.id = '$id'";

$resultado = mysqli_query($enlace, $query);

if (mysqli_num_rows($resultado) != 1) {
  echo "<p style='color:#f44;'>❌ Libro no encontrado.</p>";
  exit();
}

$libro = mysqli_fetch_assoc($resultado);
include 'encabezado_empleado.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Descripción del Libro</title>
  <link rel="stylesheet" href="estilo.css">
  <style>
    body {
      background-color: #f0f8ff;
      font-family: 'Poppins', sans-serif;
      padding: 40px;
      color: #333;
    }

    .detalle {
      background-color: #fff3e0;
      padding: 30px;
      max-width: 700px;
      margin: auto;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }

    .detalle h2 {
      color: #d35400;
      margin-bottom: 20px;
      font-size: 26px;
      text-align: center;
    }

    .detalle img {
      max-width: 200px;
      display: block;
      margin: 0 auto 20px;
      border-radius: 8px;
      box-shadow: 0 0 6px rgba(0,0,0,0.1);
    }

    .campo {
      margin-bottom: 10px;
      font-size: 16px;
    }

    .campo strong {
      color: #e67e22;
    }
  </style>
</head>
<body>
  <div class="detalle">
    <h2><?= htmlspecialchars($libro['titulo']) ?></h2>
    <img src="<?= htmlspecialchars($libro['portada_url']) ?>" alt="Portada del libro">

    <div class="campo"><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></div>
    <div class="campo"><strong>Editorial:</strong> <?= htmlspecialchars($libro['editorial']) ?></div>
    <div class="campo"><strong>Categoría:</strong> <?= htmlspecialchars($libro['categoria']) ?></div>
    <div class="campo"><strong>Idioma:</strong> <?= htmlspecialchars($libro['idioma']) ?></div>
    <div class="campo"><strong>Año:</strong> <?= htmlspecialchars($libro['año_publicacion']) ?></div>
    <div class="campo"><strong>Edición:</strong> <?= htmlspecialchars($libro['edicion']) ?></div>
    <div class="campo"><strong>Páginas:</strong> <?= htmlspecialchars($libro['num_paginas']) ?></div>
    <div class="campo"><strong>Formato:</strong> <?= htmlspecialchars($libro['formato']) ?></div>
    <div class="campo"><strong>Estado:</strong> <?= htmlspecialchars($libro['estado']) ?></div>
    <div class="campo"><strong>Venta:</strong> $<?= number_format($libro['precio_venta'], 2) ?></div>
    <div class="campo"><strong>Alquiler:</strong> $<?= number_format($libro['precio_alquiler'], 2) ?></div>
    <div class="campo"><strong>Stock disponible:</strong> <?= $libro['stock_disponible'] ?> / <?= $libro['stock_total'] ?></div>
    <div class="campo"><strong>Sinopsis:</strong> <?= htmlspecialchars($libro['sinopsis']) ?></div>
  </div>
  
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

</div>
</body>
</html>
<?php include 'pie.php'; ?>
