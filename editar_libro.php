<?php
session_start();
include("conexion.php");
include 'encabezado_admin.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; 

    $consulta = "
        SELECT l.*, 
               a.nombre_completo AS autor_nombre, 
               e.nombre AS editorial_nombre, 
               c.nombre AS categoria_nombre
        FROM Libros l
        LEFT JOIN Autores a ON l.autor_id = a.id
        LEFT JOIN Editoriales e ON l.editorial_id = e.id
        LEFT JOIN Categorias c ON l.categoria_id = c.id
        WHERE l.id = $id
    ";
    $resultado = mysqli_query($enlace, $consulta);
    if (!$resultado || mysqli_num_rows($resultado) == 0) {
        echo "Libro no encontrado.";
        exit();
    }
    $libro = mysqli_fetch_assoc($resultado);

    $autores = mysqli_query($enlace, "SELECT id, nombre_completo FROM Autores ORDER BY nombre_completo");
    $editoriales = mysqli_query($enlace, "SELECT id, nombre FROM Editoriales ORDER BY nombre");
    $categorias = mysqli_query($enlace, "SELECT id, nombre FROM Categorias ORDER BY nombre");
} else {
    echo "ID de libro no proporcionado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Libro</title>
    <style>
        body {
            background: linear-gradient(to right, #e0f7fa, #e1bee7);
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
        }
        form {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            margin: auto;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
        }
        button {
            margin-top: 20px;
            background: linear-gradient(to right, #7b1fa2, #4a148c);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease;
        }
        button:hover {
            background: linear-gradient(to left, #7b1fa2, #4a148c);
            transform: scale(1.05);
        }
        img {
            max-width: 200px;
            display: block;
            margin-bottom: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<form action="procesar_editar_libro.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $libro['id'] ?>">

    <label>Título</label>
    <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" required>

    <label>ISBN</label>
    <input type="text" name="isbn" value="<?= htmlspecialchars($libro['isbn']) ?>">

    <label>Año de Publicación</label>
    <input type="number" name="año_publicacion" value="<?= (int)$libro['año_publicacion'] ?>">

    <label>Edición</label>
    <input type="text" name="edicion" value="<?= htmlspecialchars($libro['edicion']) ?>">

    <label>Idioma</label>
    <input type="text" name="idioma" value="<?= htmlspecialchars($libro['idioma']) ?>">

    <label>Sinopsis</label>
    <textarea name="sinopsis"><?= htmlspecialchars($libro['sinopsis']) ?></textarea>

    <label>Número de Páginas</label>
    <input type="number" name="num_paginas" value="<?= (int)$libro['num_paginas'] ?>">

    <label>Portada Actual</label>
    <?php if (!empty($libro['portada_url'])): ?>
        <img src="<?= htmlspecialchars($libro['portada_url']) ?>" alt="Portada actual">
    <?php else: ?>
        <p>No hay portada disponible</p>
    <?php endif; ?>

    <label>Selecciona tipo de portada</label>
    <div>
        <input type="radio" id="url_portada" name="tipo_portada" value="url" checked onchange="togglePortadaInput()">
        <label for="url_portada">Usar URL</label>
    </div>
    <div>
        <input type="radio" id="subir_portada" name="tipo_portada" value="subir" onchange="togglePortadaInput()">
        <label for="subir_portada">Subir imagen</label>
    </div>

    <div id="input_url_portada" style="margin-top: 10px;">
        <label>URL de la Portada</label>
        <input type="text" name="portada_url" value="<?= htmlspecialchars($libro['portada_url']) ?>">
    </div>

    <div id="input_subir_portada" style="margin-top: 10px; display: none;">
        <label>Subir nueva imagen</label>
        <input type="file" name="portada_archivo" accept="image/*" onchange="previewPortada(event)">
        <div id="preview_portada" style="margin-top:10px; display:none;">
            <strong>Vista previa:</strong><br>
            <img id="img_preview_portada" style="max-width:150px; max-height:150px; border:1px solid #ccc; border-radius:8px;">
        </div>
    </div>

    <label>Formato</label>
    <input type="text" name="formato" value="<?= htmlspecialchars($libro['formato']) ?>">

    <label>Estado</label>
    <input type="text" name="estado" value="<?= htmlspecialchars($libro['estado']) ?>">

    <label>Precio de Venta</label>
    <input type="number" step="0.01" name="precio_venta" value="<?= number_format($libro['precio_venta'], 2) ?>">

    <label>Precio de Alquiler</label>
    <input type="number" step="0.01" name="precio_alquiler" value="<?= number_format($libro['precio_alquiler'], 2) ?>">

    <label>Stock Total</label>
    <input type="number" name="stock_total" value="<?= (int)$libro['stock_total'] ?>">

    <label>Stock Disponible</label>
    <input type="number" name="stock_disponible" value="<?= (int)$libro['stock_disponible'] ?>">

    <label>Autor</label>
    <select name="autor_id" required>
        <?php while ($autor = mysqli_fetch_assoc($autores)) : ?>
            <option value="<?= $autor['id'] ?>" <?= ($autor['id'] == $libro['autor_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($autor['nombre_completo']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Editorial</label>
    <select name="editorial_id" required>
        <?php while ($editorial = mysqli_fetch_assoc($editoriales)) : ?>
            <option value="<?= $editorial['id'] ?>" <?= ($editorial['id'] == $libro['editorial_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($editorial['nombre']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Categoría</label>
    <select name="categoria_id" required>
        <?php while ($categoria = mysqli_fetch_assoc($categorias)) : ?>
            <option value="<?= $categoria['id'] ?>" <?= ($categoria['id'] == $libro['categoria_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($categoria['nombre']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Guardar Cambios</button>
</form>

<script>
function togglePortadaInput() {
    const tipo = document.querySelector('input[name="tipo_portada"]:checked').value;
    document.getElementById('input_url_portada').style.display = tipo === 'url' ? 'block' : 'none';
    document.getElementById('input_subir_portada').style.display = tipo === 'subir' ? 'block' : 'none';
    if (tipo === 'url') {
        document.getElementById('preview_portada').style.display = 'none';
        const fileInput = document.querySelector('input[name="portada_archivo"]');
        if (fileInput) fileInput.value = '';
    }
}

function previewPortada(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgPreview = document.getElementById('img_preview_portada');
            imgPreview.src = e.target.result;
            document.getElementById('preview_portada').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('preview_portada').style.display = 'none';
    }
}
</script>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
    <a href="gestion_libros.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        📚 Ver Libros
    </a>
    <?php if ($_SESSION['rol'] === 'Empleado'): ?>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
    <?php endif; ?>
</div>

</body>
</html>

<?php include 'pie.php'; ?>
