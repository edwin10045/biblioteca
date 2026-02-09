<?php
    session_start();

    if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
        header("Location: index.html");
        exit();
    }

include 'encabezado_admin.php';
?>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    margin-top: 30px;
    color: #333;
}

.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    padding: 40px 20px;
}

.card {
    width: 280px;
    height: 200px;
    background: linear-gradient(145deg, #e3e3e3, #ffffff);
    border-radius: 15px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: all 0.3s ease;
    text-align: center;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.card:hover {
    transform: scale(1.05);
    background: linear-gradient(145deg, #d0e8ff, #f7fbff);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.card h3 {
    color: #0077cc;
    font-size: 20px;
    margin: 0;
}

.card p {
    color: #333;
    font-size: 14px;
    padding: 10px 0;
}

.card a {
    text-decoration: none;
    background-color: #0077cc;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
    transition: background-color 0.2s;
}
.card a:hover {
    background-color: #005fa3;
}
</style>

<h1>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>

<div class="container">

  

    <div class="card">
        <h3>Gestión de Libros</h3>
        <p>Agrega, edita o elimina libros. Administra el stock y consulta títulos.</p>
        <a href="gestion_libros.php">Ir</a>
    </div>

    <div class="card">
    <h3>Empleados</h3>
    <p>Consulta, edita o elimina empleados y gestiona sus permisos.</p>
    <a href="gestion_empleados.php">Ir</a>
</div>


    <div class="card">
        <h3>Préstamos</h3>
        <p>Revisa y registra préstamos de libros, así como sus fechas límite.</p>
        <a href="adminPrestamos.php">Ir</a>
    </div>

    <div class="card">
        <h3>Devoluciones</h3>
        <p>Registra devoluciones, aplica multas y observa el estado de los libros.</p>
        <a href="devoluciones.php">Ir</a>
    </div>

    <div class="card">
        <h3>Ventas</h3>
        <p>Consulta las ventas realizadas y registra nuevas compras.</p>
        <a href="consultarventas.php">Ir</a>
    </div>

    <div class="card">
        <h3>📧 Enviar Comprobantes</h3>
        <p>Envía comprobantes de compra por email a clientes de ventas registradas.</p>
        <a href="enviar_comprobantes.php">Ir</a>
    </div>

    <div class="card">
        <h3>Reportes</h3>
        <p>Consulta estadísticas de uso, ventas y préstamos por periodo.</p>
        <a href="reportes.php">Ir</a>
    </div>

    <div class="card">
    <h3>Reservas</h3>
    <p>Consulta y gestiona solicitudes de reserva de libros.</p>
    <a href="consultasReservas.php">Ir</a>
</div>



</div>


<?php include 'pie.php'; ?>
