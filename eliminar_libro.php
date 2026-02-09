<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($enlace, "DELETE FROM Libros WHERE id = $id");
}

header("Location: gestion_libros.php");
exit();
