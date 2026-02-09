<?php
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];

    $titulo = mysqli_real_escape_string($enlace, $_POST['titulo']);
    $isbn = mysqli_real_escape_string($enlace, $_POST['isbn']);
    $año_publicacion = (int)$_POST['año_publicacion'];
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

    $sql = "UPDATE Libros SET
                titulo = '$titulo',
                isbn = '$isbn',
                año_publicacion = $año_publicacion,
                edicion = '$edicion',
                idioma = '$idioma',
                sinopsis = '$sinopsis',
                num_paginas = $num_paginas,
                portada_url = '$portada_url_final',
                formato = '$formato',
                estado = '$estado',
                precio_venta = $precio_venta,
                precio_alquiler = $precio_alquiler,
                stock_total = $stock_total,
                stock_disponible = $stock_disponible,
                autor_id = $autor_id,
                editorial_id = $editorial_id,
                categoria_id = $categoria_id
            WHERE id = $id";

    if (mysqli_query($enlace, $sql)) {
        header("Location: editar_libro.php?id=$id&success=1");
        exit();
    } else {
        die("Error al actualizar el libro: " . mysqli_error($enlace));
    }
}
?>
