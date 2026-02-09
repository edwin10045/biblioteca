<?php
session_start();
include 'conexion.php';
include 'encabezado_admin.php';

$autores = mysqli_query($enlace, "SELECT id, nombre_completo FROM Autores ORDER BY nombre_completo");
$editoriales = mysqli_query($enlace, "SELECT id, nombre FROM Editoriales ORDER BY nombre");
$categorias = mysqli_query($enlace, "SELECT id, nombre FROM Categorias ORDER BY nombre");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = mysqli_real_escape_string($enlace, $_POST['titulo']);
    $isbn = mysqli_real_escape_string($enlace, $_POST['isbn']);
    $año = (int)$_POST['año_publicacion'];
    $edicion = mysqli_real_escape_string($enlace, $_POST['edicion']);
    $idioma = mysqli_real_escape_string($enlace, $_POST['idioma']);
    $sinopsis = mysqli_real_escape_string($enlace, $_POST['sinopsis']);
    $num_paginas = (int)$_POST['num_paginas'];
    $formato = mysqli_real_escape_string($enlace, $_POST['formato']);
    $estado = mysqli_real_escape_string($enlace, $_POST['estado']);
    $precio_venta = floatval($_POST['precio_venta']);
    $precio_alquiler = floatval($_POST['precio_alquiler']);
    $stock_total = (int)$_POST['stock_total'];
    $stock_disponible = (int)$_POST['stock_disponible'];
    $autor_id = (int)$_POST['autor_id'];
    $editorial_id = (int)$_POST['editorial_id'];
    $categoria_id = (int)$_POST['categoria_id'];

    $portada_url_final = '';

    $tipo_portada = $_POST['tipo_portada'] ?? 'url';

    if ($tipo_portada === 'subir' && isset($_FILES['portada_archivo']) && $_FILES['portada_archivo']['error'] === 0) {
        $archivo = $_FILES['portada_archivo'];
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($archivo['type'], $tiposPermitidos)) {
            if ($archivo['size'] <= 5 * 1024 * 1024) {
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $nombreArchivo = 'portada_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $rutaDestino = 'portadas/' . $nombreArchivo;

                if (!is_dir('portadas')) {
                    mkdir('portadas', 0755, true);
                }

                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $portada_url_final = $rutaDestino;
                } else {
                    die("Error al mover el archivo subido.");
                }
            } else {
                die("Archivo demasiado grande. Máximo 5MB.");
            }
        } else {
            die("Formato no válido. Solo JPG, PNG, GIF.");
        }
    } else {
        $portada_url_final = mysqli_real_escape_string($enlace, $_POST['portada_url']);
    }

    $query = "INSERT INTO Libros 
        (titulo, isbn, año_publicacion, edicion, idioma, sinopsis, num_paginas, portada_url, formato, estado, precio_venta, precio_alquiler, stock_total, stock_disponible, autor_id, editorial_id, categoria_id) 
        VALUES 
        ('$titulo', '$isbn', $año, '$edicion', '$idioma', '$sinopsis', $num_paginas, '$portada_url_final', '$formato', '$estado', $precio_venta, $precio_alquiler, $stock_total, $stock_disponible, $autor_id, $editorial_id, $categoria_id)";
    
    mysqli_query($enlace, $query);
    header("Location: gestion_libros.php");
    exit();
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
        color: #333;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        margin-top: 20px;
        color: #2c3e50;
    }

    form {
        max-width: 700px;
        margin: 30px auto;
        padding: 25px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
        color: #34495e;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"],
    textarea,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 18px;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
    }

    textarea {
        resize: vertical;
    }

    input[type="radio"] {
        margin-right: 8px;
    }

    #preview_portada img {
        margin-top: 10px;
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        border: 1px solid #ccc;
    }

    input[type="submit"] {
        background-color: #2980b9;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: block;
        margin: 0 auto;
    }

    input[type="submit"]:hover {
        background-color: #1c5980;
    }

    .form-section {
        margin-bottom: 25px;
    }
</style>

<h2>Agregar Nuevo Libro</h2>

<form method="post" enctype="multipart/form-data">
    <div class="form-section">
        <label>Título:</label>
        <input type="text" name="titulo" required>
    </div>

    <div class="form-section">
        <label>ISBN:</label>
        <input type="text" name="isbn">
    </div>

    <div class="form-section">
        <label>Año de publicación:</label>
        <input type="number" name="año_publicacion">
    </div>

    <div class="form-section">
        <label>Edición:</label>
        <input type="text" name="edicion">
    </div>

    <div class="form-section">
        <label>Idioma:</label>
        <input type="text" name="idioma">
    </div>

    <div class="form-section">
        <label>Sinopsis:</label>
        <textarea name="sinopsis" rows="4"></textarea>
    </div>

    <div class="form-section">
        <label>Número de páginas:</label>
        <input type="number" name="num_paginas">
    </div>

    <div class="form-section">
        <label>Selecciona tipo de portada:</label><br>
        <input type="radio" id="url_portada" name="tipo_portada" value="url" checked onchange="togglePortadaInput()">
        <label for="url_portada">Usar URL</label><br>
        <input type="radio" id="subir_portada" name="tipo_portada" value="subir" onchange="togglePortadaInput()">
        <label for="subir_portada">Subir imagen</label>
    </div>

    <div id="input_url_portada" class="form-section">
        <label>URL de la portada:</label>
        <input type="text" name="portada_url">
    </div>

    <div id="input_subir_portada" class="form-section" style="display:none;">
        <input type="file" name="portada_archivo" accept="image/*" onchange="previewPortada(event)">
        <div id="preview_portada" style="margin-top:10px; display:none;">
            <strong>Vista previa:</strong><br>
            <img id="img_preview_portada">
        </div>
    </div>

    <div class="form-section">
        <label>Formato:</label>
        <input type="text" name="formato">
    </div>

    <div class="form-section">
        <label>Estado:</label>
        <input type="text" name="estado">
    </div>

    <div class="form-section">
        <label>Precio de venta:</label>
        <input type="number" step="0.01" name="precio_venta">
    </div>

    <div class="form-section">
        <label>Precio de alquiler:</label>
        <input type="number" step="0.01" name="precio_alquiler">
    </div>

    <div class="form-section">
        <label>Stock total:</label>
        <input type="number" name="stock_total">
    </div>

    <div class="form-section">
        <label>Stock disponible:</label>
        <input type="number" name="stock_disponible">
    </div>

    <div class="form-section">
        <label>Autor:</label>
        <select name="autor_id" required>
            <?php while ($autor = mysqli_fetch_assoc($autores)) { ?>
                <option value="<?= $autor['id'] ?>"><?= htmlspecialchars($autor['nombre_completo']) ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-section">
        <label>Editorial:</label>
        <select name="editorial_id" required>
            <?php while ($editorial = mysqli_fetch_assoc($editoriales)) { ?>
                <option value="<?= $editorial['id'] ?>"><?= htmlspecialchars($editorial['nombre']) ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-section">
        <label>Categoría:</label>
        <select name="categoria_id" required>
            <?php while ($categoria = mysqli_fetch_assoc($categorias)) { ?>
                <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
            <?php } ?>
        </select>
    </div>

    <input type="submit" value="Guardar">
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

<?php include 'pie.php'; ?>
