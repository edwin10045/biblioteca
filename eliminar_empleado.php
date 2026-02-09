<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include 'conexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: gestion_empleados.php");
    exit();
}

if ($id == $_SESSION['id']) {
    header("Location: gestion_empleados.php");
    exit();
}

$stmt = $enlace->prepare("DELETE FROM Empleados WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: gestion_empleados.php");
exit();
?>
