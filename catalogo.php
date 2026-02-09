<div style="text-align:center; margin-top:20px;">
<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Empleado') {
    header("Location: index.html");
    exit();
}

// Obtener parámetros de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;
$autor_id = isset($_GET['autor_id']) ? intval($_GET['autor_id']) : 0;

// Obtener opciones de categorías y autores
$categorias = mysqli_query($enlace, "SELECT id, nombre FROM Categorias ORDER BY nombre");
$autores = mysqli_query($enlace, "SELECT id, nombre_completo FROM Autores ORDER BY nombre_completo");

// Construir consulta con filtros de búsqueda
$consulta = "SELECT l.*, a.nombre_completo AS autor, e.nombre AS editorial, c.nombre AS categoria
             FROM libros l
             JOIN autores a ON l.autor_id = a.id
             JOIN editoriales e ON l.editorial_id = e.id
             JOIN categorias c ON l.categoria_id = c.id
             WHERE l.estado = 'Disponible'";

// Si hay búsqueda, agregar condiciones

if (!empty($busqueda)) {
    $busqueda_segura = mysqli_real_escape_string($enlace, $busqueda);
    $consulta .= " AND (l.titulo LIKE '%$busqueda_segura%' 
                   OR a.nombre_completo LIKE '%$busqueda_segura%')";
}
if ($categoria_id > 0) {
    $consulta .= " AND l.categoria_id = $categoria_id";
}
if ($autor_id > 0) {
    $consulta .= " AND l.autor_id = $autor_id";
}

$resultado = mysqli_query($enlace, $consulta);
include 'encabezado_empleado.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Catálogo de Libros</title>
  <link rel="stylesheet" href="estilo.css">
  <style>
    body {
      background-color: #f0f8ff;
      font-family: 'Poppins', sans-serif;
      padding: 40px;
      text-align: center;
      color: #333;
    }

    .libro {
      background-color: #fff3e0;
      padding: 20px;
      margin: 20px auto;
      max-width: 600px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      text-align: left;
    }

    .libro h3 {
      margin-top: 0;
      color: #d35400;
    }

    .libro p {
      margin: 6px 0;
      font-size: 15px;
    }

    .botones {
      margin-top: 15px;
      text-align: center;
    }

    .botones form {
      display: inline-block;
      margin: 5px;
    }

    .botones button {
      background-color: #67aee8;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .botones button:hover {
      background-color: #4c94d4;
    }

    /* Estilos para el buscador */

    .buscador {
      background: linear-gradient(135deg, #f8fafc 60%, #e3eafc 100%);
      padding: 28px 28px 22px 28px;
      margin: 24px auto 32px auto;
      max-width: 650px;
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(39,174,96,0.08), 0 0 12px rgba(0,0,0,0.07);
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .buscador h2 {
      color: #d35400;
      margin-bottom: 18px;
      font-size: 22px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .buscador form {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: center;
      align-items: center;
      width: 100%;
    }

    .buscador input[type="text"] {
      flex: 2 1 220px;
      min-width: 180px;
      padding: 12px 14px;
      border: 2px solid #67aee8;
      border-radius: 8px;
      font-size: 16px;
      margin-right: 0;
      background: #f8fafc;
      transition: border-color 0.3s, box-shadow 0.3s;
      box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .buscador input[type="text"]:focus {
      border-color: #2980b9;
      outline: none;
      background: #eef6fb;
      box-shadow: 0 0 0 2px #67aee8;
    }

    .buscador select {
      flex: 1 1 140px;
      min-width: 120px;
      padding: 11px 12px;
      border: 1.5px solid #bdc3c7;
      border-radius: 8px;
      font-size: 15px;
      background: #f8fafc;
      color: #2c3e50;
      transition: border-color 0.3s, box-shadow 0.3s;
      box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .buscador select:focus {
      border-color: #2980b9;
      outline: none;
      background: #eef6fb;
      box-shadow: 0 0 0 2px #67aee8;
    }

    .buscador button {
      background-color: #67aee8;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 17px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
      box-shadow: 0 2px 8px rgba(39,174,96,0.08);
    }

    .buscador button:hover {
      background-color: #2980b9;
    }

    .limpiar-busqueda {
      background-color: #95a5a6 !important;
      margin-left: 5px;
      padding: 10px 15px;
      text-decoration: none;
      display: inline-block;
      border-radius: 6px;
      color: white;
      font-size: 16px;
    }

    .limpiar-busqueda:hover {
      background-color: #7f8c8d !important;
    }

    .resultado-busqueda {
      color: #27ae60;
      font-weight: bold;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <h1>📚 Catálogo de Libros</h1>


  <!-- Formulario de búsqueda -->
  <div class="buscador">
    <h2>🔍 Buscar Libros</h2>
    <form method="GET" action="">
      <input type="text" 
             name="busqueda" 
             placeholder="Buscar por título o autor..." 
             value="<?= htmlspecialchars($busqueda) ?>">
      <select name="categoria_id" style="margin-left:10px;">
        <option value="0">Todas las categorías</option>
        <?php while ($cat = mysqli_fetch_assoc($categorias)) { ?>
          <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
        <?php } ?>
      </select>
      <select name="autor_id" style="margin-left:10px;">
        <option value="0">Todos los autores</option>
        <?php mysqli_data_seek($autores, 0); while ($aut = mysqli_fetch_assoc($autores)) { ?>
          <option value="<?= $aut['id'] ?>" <?= ($autor_id == $aut['id']) ? 'selected' : '' ?>><?= htmlspecialchars($aut['nombre_completo']) ?></option>
        <?php } ?>
      </select>
      <button type="submit">Buscar</button>
      <?php if (!empty($busqueda) || $categoria_id > 0 || $autor_id > 0) { ?>
        <a href="catalogo.php" class="limpiar-busqueda">Limpiar</a>
      <?php } ?>
    </form>
  </div>

  <!-- Resultado de búsqueda -->
  <?php if (!empty($busqueda)) { ?>
    <div class="resultado-busqueda">
      Resultados para: "<?= htmlspecialchars($busqueda) ?>"
    </div>
  <?php } ?>

  <?php 
  // Verificar si hay resultados
  if (mysqli_num_rows($resultado) == 0) {
    if (!empty($busqueda)) {
      echo "<p style='color: #e74c3c; font-size: 18px;'>No se encontraron libros que coincidan con tu búsqueda.</p>";
    } else {
      echo "<p style='color: #e74c3c; font-size: 18px;'>No hay libros disponibles en este momento.</p>";
    }
  }
  ?>

  <?php while ($libro = mysqli_fetch_assoc($resultado)) { ?>
    <div class="libro">
      <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
      <p><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></p>
      <p><strong>Editorial:</strong> <?= htmlspecialchars($libro['editorial']) ?></p>
      <p><strong>Categoría:</strong> <?= htmlspecialchars($libro['categoria']) ?></p>

      <div class="botones">
        <form method="GET" action="verDescripcion.php">
          <input type="hidden" name="id" value="<?= $libro['id'] ?>">
          <button type="submit">Ver descripción</button>
        </form>

        <form method="GET" action="crearReserva.php">
          <input type="hidden" name="id_libro" value="<?= $libro['id'] ?>">
          <button type="submit">Reservar libro</button>
        </form>

        <form method="GET" action="nuevoPrestamo.php">
          <input type="hidden" name="id" value="<?= $libro['id'] ?>">
          <button type="submit">Prestar libro</button>
        </form>
      </div>
    </div>
    </table>

</div>
  <?php } ?>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
</div>

</body>
</html>
<?php include 'pie.php'; ?>
