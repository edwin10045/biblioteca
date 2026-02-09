<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['rol'] !== 'Empleado' && $_SESSION['rol'] !== 'Administrador')) {
    header("Location: index.html");
    exit();
}

if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}
include 'conexion.php';

// Clase EmailLibreria funcional
class EmailLibreria
{
    private $smtp_conn;
    private $debug = false;
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }
    
    public function enviarComprobante($from_email, $app_password, $to_email, $datos_venta)
    {
        try {
            // 1. Conectar a Gmail
            if (!$this->conectar()) {
                return ['success' => false, 'error' => 'No se pudo conectar a Gmail'];
            }
            
            // 2. Autenticar
            if (!$this->autenticar($from_email, $app_password)) {
                return ['success' => false, 'error' => 'Error de autenticación'];
            }
            
            // 3. Enviar correo
            if (!$this->enviarEmail($from_email, $to_email, $datos_venta)) {
                return ['success' => false, 'error' => 'Error enviando correo'];
            }
            
            // 4. Cerrar conexión
            $this->cerrar();
            
            return ['success' => true, 'message' => 'Comprobante enviado exitosamente'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function conectar()
    {
        $this->smtp_conn = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
        
        if (!$this->smtp_conn) {
            return false;
        }
        
        fgets($this->smtp_conn); // Saludo del servidor
        
        // EHLO inicial
        fputs($this->smtp_conn, "EHLO localhost\r\n");
        $this->leer_respuesta_completa();
        
        // STARTTLS
        fputs($this->smtp_conn, "STARTTLS\r\n");
        fgets($this->smtp_conn);
        
        // Activar TLS
        if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return false;
        }
        
        // EHLO después de TLS
        fputs($this->smtp_conn, "EHLO localhost\r\n");
        $this->leer_respuesta_completa();
        
        return true;
    }
    
    private function autenticar($email, $password)
    {
        fputs($this->smtp_conn, "AUTH LOGIN\r\n");
        fgets($this->smtp_conn);
        
        fputs($this->smtp_conn, base64_encode($email) . "\r\n");
        fgets($this->smtp_conn);
        
        fputs($this->smtp_conn, base64_encode($password) . "\r\n");
        $response = fgets($this->smtp_conn);
        
        if (strpos($response, '235') === 0) {
            return true;
        } else {
            return false;
        }
    }
    
    private function enviarEmail($from, $to, $datos)
    {
        fputs($this->smtp_conn, "MAIL FROM:<$from>\r\n");
        fgets($this->smtp_conn);
        
        fputs($this->smtp_conn, "RCPT TO:<$to>\r\n");
        fgets($this->smtp_conn);
        
        fputs($this->smtp_conn, "DATA\r\n");
        fgets($this->smtp_conn);
        
        $subject = "Comprobante de Compra - Biblioteca Universidad";
        $body = $this->construirHTML($datos);
        
        $email = "From: Biblioteca Universidad <$from>\r\n";
        $email .= "To: $to\r\n";
        $email .= "Subject: $subject\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email .= "\r\n";
        $email .= $body;
        $email .= "\r\n.\r\n";
        
        fputs($this->smtp_conn, $email);
        $response = fgets($this->smtp_conn);
        
        if (strpos($response, '250') === 0) {
            return true;
        } else {
            return false;
        }
    }
    
    private function construirHTML($datos)
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;'>
            <div style='background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <h2 style='color: #2c3e50; text-align: center; margin-bottom: 30px;'>🧾 Comprobante de Compra</h2>
                
                <div style='background: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;'>
                    <h3 style='color: #0056b3; margin-top: 0;'>📋 Información del Ticket</h3>
                    <p><strong>Ticket #:</strong> " . str_pad($datos['ticket_id'], 6, '0', STR_PAD_LEFT) . "</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s', strtotime($datos['fecha'])) . "</p>
                    <p><strong>Atendido por:</strong> {$datos['empleado']}</p>
                </div>
                
                <div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <h3 style='color: #856404; margin-top: 0;'>👤 Datos del Cliente</h3>
                    <p><strong>Nombre:</strong> {$datos['cliente']}</p>
                    <p><strong>Teléfono:</strong> {$datos['telefono']}</p>
                    <p><strong>Email:</strong> {$datos['email']}</p>
                </div>
                
                <div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                    <h3 style='color: #0c5460; margin-top: 0;'>📚 Detalles de la Compra</h3>
                    <p><strong>Libro:</strong> {$datos['libro']}</p>
                    <p><strong>Autor:</strong> {$datos['autor']}</p>
                    <p><strong>Editorial:</strong> {$datos['editorial']}</p>
                    <p><strong>Cantidad:</strong> {$datos['cantidad']}</p>
                    <p><strong>Precio unitario:</strong> $" . number_format($datos['precio'], 2) . "</p>
                    <p><strong>Método de pago:</strong> {$datos['metodo_pago']}</p>
                </div>
                
                <div style='background: #d4edda; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0; border: 2px solid #28a745;'>
                    <h3 style='color: #155724; margin: 0; font-size: 24px;'>TOTAL PAGADO: $" . number_format($datos['total'], 2) . "</h3>
                </div>
                
                <div style='text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;'>
                    <h4 style='color: #28a745; margin-top: 0;'>¡Gracias por tu compra!</h4>
                    <p style='margin: 10px 0; color: #666;'>Conserva este comprobante para futuras referencias.</p>
                    <hr style='border: 1px solid #dee2e6; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #999; margin: 0;'>Biblioteca Universidad - Sistema Automatizado</p>
                </div>
            </div>
        </div>";
    }
    
    private function leer_respuesta_completa()
    {
        $response = '';
        while (($line = fgets($this->smtp_conn)) !== false) {
            $response .= $line;
            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        return $response;
    }
    
    private function debug_msg($msg)
    {
        if ($this->debug) {
            echo "DEBUG: $msg\n";
        }
    }
    
    private function cerrar()
    {
        if ($this->smtp_conn) {
            fputs($this->smtp_conn, "QUIT\r\n");
            fclose($this->smtp_conn);
        }
    }
}

$mensaje = "";
$mostrarFormulario = true;
$email_resultado = null;
$ventaEncontrada = null;

// Procesar envío de comprobante
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'enviar_comprobante') {
    $venta_id = intval($_POST['venta_id']);
    $email_destino = mysqli_real_escape_string($enlace, $_POST['email_destino']);
    
    // Obtener información completa de la venta
    $queryVenta = "SELECT v.*, l.titulo, l.precio_venta, a.nombre_completo as autor, e.nombre as editorial, em.nombre_completo as empleado_nombre
                   FROM Ventas v 
                   JOIN Libros l ON v.libro_id = l.id
                   LEFT JOIN Autores a ON l.autor_id = a.id
                   LEFT JOIN Editoriales e ON l.editorial_id = e.id
                   LEFT JOIN Empleados em ON v.empleado_id = em.id
                   WHERE v.id = $venta_id";
    
    $resultVenta = mysqli_query($enlace, $queryVenta);
    $ventaData = mysqli_fetch_assoc($resultVenta);
    
    if ($ventaData) {
        $ventaEncontrada = $ventaData;
        
        // Preparar datos para el email
        $datos_email = [
            'ticket_id' => $ventaData['id'],
            'fecha' => $ventaData['fecha_compra'],
            'empleado' => $ventaData['empleado_nombre'] ?? 'Desconocido',
            'cliente' => $ventaData['cliente_nombre'],
            'telefono' => $ventaData['cliente_telefono'],
            'email' => $email_destino,
            'libro' => $ventaData['titulo'],
            'autor' => $ventaData['autor'] ?? 'N/A',
            'editorial' => $ventaData['editorial'] ?? 'N/A',
            'cantidad' => $ventaData['cantidad'],
            'precio' => $ventaData['precio_venta'],
            'metodo_pago' => $ventaData['metodo_pago'],
            'total' => $ventaData['total_pagado']
        ];
        
        // Enviar email
        $mailer = new EmailLibreria(false);
        $email_resultado = $mailer->enviarComprobante(
            'diaztecete@gmail.com',     // Email origen
            'hqicplranmnjaojr',         // App Password
            $email_destino,             // Email destino
            $datos_email                // Datos del comprobante
        );
        
        if ($email_resultado['success']) {
            $mensaje = "✅ Comprobante enviado exitosamente a: $email_destino";
        } else {
            $mensaje = "❌ Error enviando comprobante: " . $email_resultado['error'];
        }
        
        $mostrarFormulario = false;
    } else {
        $mensaje = "❌ No se encontró la venta con ID: $venta_id";
    }
}

// Buscar venta por ID
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'buscar_venta') {
    $venta_id = intval($_POST['venta_id']);
    
    $queryVenta = "SELECT v.*, l.titulo, l.precio_venta, a.nombre_completo as autor, e.nombre as editorial, em.nombre_completo as empleado_nombre
                   FROM Ventas v 
                   JOIN Libros l ON v.libro_id = l.id
                   LEFT JOIN Autores a ON l.autor_id = a.id
                   LEFT JOIN Editoriales e ON l.editorial_id = e.id
                   LEFT JOIN Empleados em ON v.empleado_id = em.id
                   WHERE v.id = $venta_id";
    
    $resultVenta = mysqli_query($enlace, $queryVenta);
    $ventaEncontrada = mysqli_fetch_assoc($resultVenta);
    
    if (!$ventaEncontrada) {
        $mensaje = "❌ No se encontró la venta con ID: $venta_id";
    }
}

// Buscar venta por ID desde URL (parámetro GET para acceso directo)
if (isset($_GET['venta_id']) && !empty($_GET['venta_id'])) {
    $venta_id = intval($_GET['venta_id']);
    
    $queryVenta = "SELECT v.*, l.titulo, l.precio_venta, a.nombre_completo as autor, e.nombre as editorial, em.nombre_completo as empleado_nombre
                   FROM Ventas v 
                   JOIN Libros l ON v.libro_id = l.id
                   LEFT JOIN Autores a ON l.autor_id = a.id
                   LEFT JOIN Editoriales e ON l.editorial_id = e.id
                   LEFT JOIN Empleados em ON v.empleado_id = em.id
                   WHERE v.id = $venta_id";
    
    $resultVenta = mysqli_query($enlace, $queryVenta);
    $ventaEncontrada = mysqli_fetch_assoc($resultVenta);
    
    if (!$ventaEncontrada) {
        $mensaje = "❌ No se encontró la venta con ID: $venta_id";
    }
}

// Parámetros de búsqueda y paginación
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($enlace, $_GET['buscar']) : '';
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$ventas_por_pagina = 15;
$offset = ($pagina - 1) * $ventas_por_pagina;

// Construir consulta base con búsqueda
$whereClause = "";
if (!empty($buscar)) {
    $whereClause = "WHERE v.cliente_nombre LIKE '%$buscar%' OR v.correo LIKE '%$buscar%' OR l.titulo LIKE '%$buscar%'";
}

// Contar total de ventas
$queryCount = "SELECT COUNT(*) as total FROM Ventas v JOIN Libros l ON v.libro_id = l.id $whereClause";
$resultCount = mysqli_query($enlace, $queryCount);
$totalVentas = mysqli_fetch_assoc($resultCount)['total'];
$totalPaginas = ceil($totalVentas / $ventas_por_pagina);

// Obtener ventas con búsqueda y paginación (más recientes primero)
$queryVentas = "SELECT v.id, v.cliente_nombre, v.cliente_telefono, v.fecha_compra, v.total_pagado, 
                       l.titulo, v.correo, v.metodo_pago, v.cantidad,
                       em.nombre_completo as empleado_nombre
                FROM Ventas v 
                JOIN Libros l ON v.libro_id = l.id
                LEFT JOIN Empleados em ON v.empleado_id = em.id
                $whereClause
                ORDER BY v.fecha_compra DESC, v.id DESC
                LIMIT $offset, $ventas_por_pagina";
$resultVentas = mysqli_query($enlace, $queryVentas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Comprobantes - Biblioteca</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6d6e71ff 0%, #ffffffff 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .container {
            background: white;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }
        
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        h3 {
            color: #34495e;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin: 20px 0;
        }
        
        label {
            display: block;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }
        
        .mensaje-exito {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 12px;
            margin: 15px 0;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.15);
        }
        
        .mensaje-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 20px;
            border-radius: 12px;
            margin: 15px 0;
            box-shadow: 0 4px 6px rgba(220, 53, 69, 0.15);
        }
        
        .venta-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 3px solid #007bff;
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.15);
        }
        
        .venta-info h3 {
            color: #007bff;
            margin-top: 0;
            font-size: 22px;
        }
        
        .venta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px;
            background: rgba(255,255,255,0.5);
        }
        
        .venta-row:hover {
            background: rgba(0, 123, 255, 0.05);
        }
        
        .tabla-ventas {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }        
        .tabla-ventas th {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            padding: 15px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border: none;
        }
        
        .tabla-ventas td {
            padding: 12px 8px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
            font-size: 13px;
        }
        
        .tabla-ventas tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .tabla-ventas tr:hover {
            background: rgba(0, 123, 255, 0.1);
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        
        .separador {
            border-top: 4px solid #007bff;
            margin: 40px 0;
            padding-top: 30px;
            position: relative;
        }
        
        .separador::before {
            content: "";
            position: absolute;
            top: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #764ba2);
            border-radius: 2px;
        }
        
        .email-form {
            background: linear-gradient(135deg, #e8f4fd, #d1ecf1);
            padding: 25px;
            border-radius: 15px;
            border-left: 6px solid #007bff;
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.15);
        }
        
        .buscador {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            border: 3px solid #dee2e6;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }
        
        .buscador-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .buscador-form > div {
            flex: 1;
            min-width: 300px;
        }
        
        .paginacion {
            text-align: center;
            margin: 30px 0;
        }
        
        .paginacion a, .paginacion span {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 3px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .paginacion a:hover {
            background: #007bff;
            color: white;
            transform: translateY(-2px);
        }
        
        .paginacion span.actual {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }
        
        .estadisticas {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            border-left: 6px solid #28a745;
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.15);
        }
        
        .email-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .email-status.con-email {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .email-status.sin-email {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .ticket-id {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .total-amount {
            font-weight: bold;
            color: #28a745;
            font-size: 14px;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .btn:disabled:hover {
            transform: none !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        }
        
        .table-header-icon {
            font-size: 16px;
            margin-right: 5px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-indicator.online {
            background: #28a745;
            box-shadow: 0 0 4px #28a745;
        }
        
        .status-indicator.offline {
            background: #dc3545;
            box-shadow: 0 0 4px #dc3545;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 10px;
            }
            
            .buscador-form {
                flex-direction: column;
            }
            
            .buscador-form > div {
                min-width: 100%;
            }
            
            .tabla-ventas th, .tabla-ventas td {
                padding: 8px 4px;
                font-size: 11px;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 3px;
            }
            
            .btn-sm {
                padding: 6px 10px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📧 Enviar Comprobantes de Compra</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo strpos($mensaje, '✅') !== false ? 'mensaje-exito' : 'mensaje-error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mostrarFormulario): ?>
            <!-- Estadísticas generales -->
            <div class="estadisticas card-hover">
                <div style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                            📊 <?php echo $totalVentas; ?>
                        </div>
                        <div style="font-size: 14px; color: #666;">Ventas Totales</div>
                    </div>
                    
                    <?php if (!empty($buscar)): ?>
                        <div style="text-align: center; border-left: 2px solid #28a745; padding-left: 20px;">
                            <div style="font-size: 18px; font-weight: bold; color: #007bff;">
                                🔍 Búsqueda Activa
                            </div>
                            <div style="font-size: 12px; color: #666;">
                                "<strong><?php echo htmlspecialchars($buscar); ?></strong>"
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div style="text-align: center; border-left: 2px solid #28a745; padding-left: 20px;">
                        <div style="font-size: 18px; font-weight: bold; color: #6f42c1;">
                            📄 Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?>
                        </div>
                        <div style="font-size: 12px; color: #666;">Mostrando 15 por página</div>
                    </div>
                </div>
            </div>

            <!-- Buscador de ventas -->
            <div class="buscador card-hover">
                <h3>🔍 Buscar Ventas</h3>
                <form method="GET" class="buscador-form">
                    <div>
                        <label>Buscar por nombre de cliente, email o libro:</label>                        <input type="text" name="buscar" 
                               placeholder="🔎 Ejemplo: Juan Pérez, correo@gmail.com, Cien años..." 
                               value="<?php echo htmlspecialchars($buscar); ?>"
                               style="padding-left: 40px;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            🔍 Buscar
                        </button>
                        <?php if (!empty($buscar)): ?>
                            <a href="?" class="btn btn-secondary">
                                🔄 Limpiar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <?php if (!empty($buscar)): ?>
                    <div style="margin-top: 15px; padding: 10px; background: rgba(0, 123, 255, 0.1); border-radius: 8px; border-left: 4px solid #007bff;">
                        <small>
                            <span class="status-indicator online"></span>
                            <strong>Búsqueda activa:</strong> "<?php echo htmlspecialchars($buscar); ?>" 
                            - Encontrados: <strong><?php echo $totalVentas; ?></strong> resultados
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Búsqueda rápida por ID -->
            <?php if (!$ventaEncontrada): ?>
                <div class="form-group">
                    <h3>🎯 Búsqueda Rápida por ID de Ticket</h3>
                    <form method="POST" style="display: flex; align-items: end; gap: 10px;">
                        <div style="flex: 1;">
                            <label>ID de la Venta:</label>
                            <input type="number" name="venta_id" required placeholder="Ingresa el número del ticket">
                        </div>
                        <button type="submit" name="accion" value="buscar_venta" class="btn btn-primary">
                            🔍 Buscar por ID
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if ($ventaEncontrada): ?>
                <!-- Notificación de acceso directo -->
                <?php if (isset($_GET['venta_id'])): ?>
                    <div style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; color: #856404; padding: 15px; border-radius: 10px; margin: 15px 0; text-align: center;">
                        <h4 style="margin: 0; color: #856404;">🎯 Venta Pre-seleccionada</h4>
                        <p style="margin: 5px 0;">Has accedido directamente desde el formulario de venta. La venta está lista para gestionar.</p>
                    </div>
                <?php endif; ?>
                
                <div class="separador">
                    <!-- Información de la venta encontrada -->
                    <div class="venta-info">
                        <h3>📋 Venta Encontrada - Ticket #<?php echo str_pad($ventaEncontrada['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                        
                        <div class="venta-row">
                            <span><strong>Fecha:</strong></span>
                            <span><?php echo date('d/m/Y H:i', strtotime($ventaEncontrada['fecha_compra'])); ?></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Cliente:</strong></span>
                            <span><?php echo htmlspecialchars($ventaEncontrada['cliente_nombre']); ?></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Teléfono:</strong></span>
                            <span><?php echo htmlspecialchars($ventaEncontrada['cliente_telefono']); ?></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Email original:</strong></span>
                            <span><?php echo htmlspecialchars($ventaEncontrada['correo'] ?: 'No especificado'); ?></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Libro:</strong></span>
                            <span><?php echo htmlspecialchars($ventaEncontrada['titulo']); ?></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Cantidad:</strong></span>
                            <span><?php echo $ventaEncontrada['cantidad']; ?></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Total:</strong></span>
                            <span><strong>$<?php echo number_format($ventaEncontrada['total_pagado'], 2); ?></strong></span>
                        </div>
                        
                        <div class="venta-row">
                            <span><strong>Atendido por:</strong></span>
                            <span><?php echo htmlspecialchars($ventaEncontrada['empleado_nombre'] ?? 'Desconocido'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Paso 2: Enviar comprobante -->
                    <div class="email-form">
                        <h3>📧 Paso 2: Enviar Comprobante</h3>
                        <form method="POST">
                            <input type="hidden" name="venta_id" value="<?php echo $ventaEncontrada['id']; ?>">
                            
                            <div class="form-group">
                                <label>📧 Email de destino:</label>
                                <input type="email" name="email_destino" required 
                                       value="<?php echo htmlspecialchars($ventaEncontrada['correo']); ?>"
                                       placeholder="correo@ejemplo.com">
                                <small style="color: #666;">Puedes cambiarlo si necesitas enviarlo a otro email</small>
                            </div>
                            
                            <button type="submit" name="accion" value="enviar_comprobante" class="btn btn-success" style="width: 100%; font-size: 16px;">
                                🚀 Enviar Comprobante por Email
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Todas las ventas registradas -->
            <?php if (!isset($_GET['venta_id']) || !$ventaEncontrada): ?>
                <div class="separador">
                    <h3>📊 Todas las Ventas Registradas (<?php echo $totalVentas; ?> total)</h3>
            <?php else: ?>
                <!-- Sección colapsada cuando hay venta específica -->
                <div class="separador">
                    <div onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'; this.querySelector('.toggle-icon').innerText = this.querySelector('.toggle-icon').innerText === '▼' ? '▶' : '▼';" 
                         style="cursor: pointer; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 2px solid #dee2e6; margin-bottom: 10px;">
                        <h3 style="margin: 0; color: #495057;">
                            <span class="toggle-icon">▶</span> 📊 Ver Todas las Ventas (<?php echo $totalVentas; ?> total) - Click para expandir
                        </h3>
                    </div>
                    <div style="display: none;">
                        <h3>📊 Todas las Ventas Registradas (<?php echo $totalVentas; ?> total)</h3>
            <?php endif; ?>
                
                <?php if ($totalVentas > 0): ?>
                    <div class="table-responsive">
                        <table class="tabla-ventas">
                            <thead>
                                <tr>
                                    <th>🎫 Ticket</th>
                                    <th>📅 Fecha</th>
                                    <th>👤 Cliente</th>
                                    <th>📱 Teléfono</th>
                                    <th>📚 Libro</th>
                                    <th>📦 Cant.</th>
                                    <th>💰 Total</th>
                                    <th>💳 Pago</th>
                                    <th>📧 Email</th>
                                    <th>👨‍💼 Empleado</th>
                                    <th>⚡ Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($venta = mysqli_fetch_assoc($resultVentas)): ?>
                                    <tr>
                                        <td>
                                            <span class="ticket-id">#<?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px;">
                                                <div><strong><?php echo date('d/m/Y', strtotime($venta['fecha_compra'])); ?></strong></div>
                                                <div style="color: #666;"><?php echo date('H:i', strtotime($venta['fecha_compra'])); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: bold; color: #2c3e50;">
                                                <?php echo htmlspecialchars($venta['cliente_nombre']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; color: #495057;">
                                                <?php echo htmlspecialchars($venta['cliente_telefono']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div title="<?php echo htmlspecialchars($venta['titulo']); ?>" 
                                                 style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <strong><?php echo htmlspecialchars(substr($venta['titulo'], 0, 25)) . (strlen($venta['titulo']) > 25 ? '...' : ''); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 6px; font-weight: bold;">
                                                <?php echo $venta['cantidad']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="total-amount">$<?php echo number_format($venta['total_pagado'], 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #6f42c1; color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                                <?php echo htmlspecialchars($venta['metodo_pago']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($venta['correo']): ?>
                                                <div class="email-status con-email" title="<?php echo htmlspecialchars($venta['correo']); ?>">
                                                    ✅ Tiene Email
                                                    <div style="font-size: 10px; margin-top: 2px;">
                                                        <?php echo htmlspecialchars(substr($venta['correo'], 0, 15)) . (strlen($venta['correo']) > 15 ? '...' : ''); ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="email-status sin-email">
                                                    ❌ Sin Email
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px; color: #495057;">
                                                <?php echo htmlspecialchars(substr($venta['empleado_nombre'] ?? 'N/A', 0, 15)); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <!-- Botón para buscar/ver detalles -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="venta_id" value="<?php echo $venta['id']; ?>">
                                                    <button type="submit" name="accion" value="buscar_venta" 
                                                            class="btn btn-info btn-sm" 
                                                            title="Ver detalles y enviar comprobante">
                                                        👁️ Ver
                                                    </button>
                                                </form>
                                                
                                                <!-- Botón directo para enviar (solo si tiene email) -->
                                                <?php if ($venta['correo']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="venta_id" value="<?php echo $venta['id']; ?>">
                                                        <input type="hidden" name="email_destino" value="<?php echo htmlspecialchars($venta['correo']); ?>">
                                                        <button type="submit" name="accion" value="enviar_comprobante" 
                                                                class="btn btn-success btn-sm"
                                                                onclick="return confirm('¿Enviar comprobante a <?php echo htmlspecialchars($venta['correo']); ?>?')"
                                                                title="Enviar comprobante directamente">
                                                            📧 Enviar
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-warning btn-sm" 
                                                            title="Esta venta no tiene email registrado"
                                                            disabled>
                                                        ⚠️ Sin Email
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($totalPaginas > 1): ?>
                        <div class="paginacion">
                            <?php
                            $url_params = !empty($buscar) ? "?buscar=" . urlencode($buscar) . "&" : "?";
                            
                            // Botón anterior
                            if ($pagina > 1):
                                echo "<a href='{$url_params}pagina=" . ($pagina - 1) . "'>← Anterior</a>";
                            endif;
                            
                            // Números de página
                            $inicio = max(1, $pagina - 2);
                            $fin = min($totalPaginas, $pagina + 2);
                            
                            if ($inicio > 1) {
                                echo "<a href='{$url_params}pagina=1'>1</a>";
                                if ($inicio > 2) echo "<span>...</span>";
                            }
                            
                            for ($i = $inicio; $i <= $fin; $i++) {
                                if ($i == $pagina) {
                                    echo "<span class='actual'>$i</span>";
                                } else {
                                    echo "<a href='{$url_params}pagina=$i'>$i</a>";
                                }
                            }
                            
                            if ($fin < $totalPaginas) {
                                if ($fin < $totalPaginas - 1) echo "<span>...</span>";
                                echo "<a href='{$url_params}pagina=$totalPaginas'>$totalPaginas</a>";
                            }
                            
                            // Botón siguiente
                            if ($pagina < $totalPaginas):
                                echo "<a href='{$url_params}pagina=" . ($pagina + 1) . "'>Siguiente →</a>";
                            endif;
                            ?>
                        </div>
                        
                        <div style="text-align: center; color: #666; font-size: 14px; margin-top: 10px;">
                            Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?> 
                            (<?php echo $totalVentas; ?> ventas total)
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <h4>📭 No se encontraron ventas</h4>
                        <?php if (!empty($buscar)): ?>
                            <p>No hay resultados para la búsqueda: "<strong><?php echo htmlspecialchars($buscar); ?></strong>"</p>
                            <a href="?" class="btn btn-primary">Ver todas las ventas</a>
                        <?php else: ?>
                            <p>No hay ventas registradas en el sistema.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (isset($_GET['venta_id']) && $ventaEncontrada): ?>
                    </div>
            <?php endif; ?>
        
        <?php else: ?>
            <!-- Resultado del envío -->
            <div style="text-align: center;">
                <?php if ($email_resultado && $email_resultado['success']): ?>
                    <h3 style="color: #28a745;">🎉 ¡Comprobante Enviado!</h3>
                    <p>El comprobante de la venta <strong>#<?php echo str_pad($ventaEncontrada['id'], 6, '0', STR_PAD_LEFT); ?></strong> 
                    ha sido enviado exitosamente.</p>
                    <p><strong>Destinatario:</strong> <?php echo htmlspecialchars($_POST['email_destino']); ?></p>
                    <p><small>Revisa la bandeja de entrada, promociones y spam</small></p>
                <?php else: ?>
                    <h3 style="color: #dc3545;">❌ Error en el Envío</h3>
                    <p>No se pudo enviar el comprobante.</p>
                    <?php if ($email_resultado): ?>
                        <p><small><?php echo htmlspecialchars($email_resultado['error']); ?></small></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <button onclick="window.location.reload()" class="btn btn-primary">
                    📧 Enviar Otro Comprobante
                </button>
            </div>
        <?php endif; ?>
        
        <!-- Navegación -->
       <div style="text-align: center; margin-top: 30px; border-top: 2px solid #dee2e6; padding-top: 20px;">
   
        <?php if ($_SESSION['rol'] === 'Empleado'): ?>
    <a href="catalogo.php" class="btn btn-secondary">📚 Volver al Catálogo</a>
<?php endif; ?>
    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
        <a href="consultarventas.php" class="btn btn-secondary">📊 Ver Todas las Ventas</a>
    <?php endif; ?>
<?php if ($_SESSION['rol'] === 'Empleado'): ?>
    <a href="comienzo.php" class="btn btn-secondary">🏠 Menú Principal</a>
<?php endif; ?>
<?php if ($_SESSION['rol'] === 'Administrador'): ?>
    <a href="menuadmin.php" class="btn btn-secondary">🏠 Menú Principal</a>
<?php endif; ?>
</div>
   <center>
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
    
    </div>
</body>

</html>
<?php include 'pie.php'; ?>