<?php
ob_start(); // Iniciar buffering para evitar output antes del HTML
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

// Clase EmailLibreria para envío de comprobantes
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
$mostrarTicket = false;
$datosTicket = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verificar si es envío de comprobante
    if (isset($_POST['accion']) && $_POST['accion'] === 'enviar_comprobante') {
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
            $datosTicket = $ventaData;
            
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
            $mostrarTicket = true;
        } else {
            $mensaje = "❌ No se encontró la venta con ID: $venta_id";
        }
    } else {
        // Lógica original de registro de venta
        $libro_id = intval($_POST['libro_id']);
        $cliente_nombre = mysqli_real_escape_string($enlace, $_POST['cliente_nombre']);
        $cliente_telefono = mysqli_real_escape_string($enlace, $_POST['cliente_telefono']);
        $correo = mysqli_real_escape_string($enlace, $_POST['cliente_correo']);
        $metodo_pago = mysqli_real_escape_string($enlace, $_POST['metodo_pago']);
        $cantidad = intval($_POST['cantidad']);
        $empleado_id = $_SESSION['id'];
        $fecha_compra = date("Y-m-d");

        $queryLibro = "SELECT precio_venta, stock_disponible FROM Libros WHERE id = $libro_id";
        $resultLibro = mysqli_query($enlace, $queryLibro);
        $libro = mysqli_fetch_assoc($resultLibro);

        if (!$libro) {
            $mensaje = "Libro no encontrado.";
        } elseif ($libro['stock_disponible'] < $cantidad || $cantidad < 1) {
            $mensaje = "No hay suficiente stock disponible para la cantidad solicitada.";
        } else {
            $total_pagado = $libro['precio_venta'] * $cantidad;

            $insertVenta = "INSERT INTO Ventas (cliente_nombre, cliente_telefono, correo, empleado_id, fecha_compra, total_pagado, metodo_pago, libro_id, cantidad)
                            VALUES ('$cliente_nombre', '$cliente_telefono', '$correo', $empleado_id, '$fecha_compra', $total_pagado, '$metodo_pago', $libro_id, $cantidad)";

            if (mysqli_query($enlace, $insertVenta)) {
                $venta_id = mysqli_insert_id($enlace);
                $updateStock = "UPDATE Libros SET stock_disponible = stock_disponible - $cantidad WHERE id = $libro_id";
                mysqli_query($enlace, $updateStock);

                // Obtener información completa para el ticket
                $queryTicket = "SELECT v.*, l.titulo, l.precio_venta, a.nombre_completo as autor, e.nombre as editorial
                               FROM Ventas v 
                               JOIN Libros l ON v.libro_id = l.id
                               LEFT JOIN Autores a ON l.autor_id = a.id
                               LEFT JOIN Editoriales e ON l.editorial_id = e.id
                               WHERE v.id = $venta_id";
                $resultTicket = mysqli_query($enlace, $queryTicket);
                $datosTicket = mysqli_fetch_assoc($resultTicket);

                // Obtener nombre del empleado
                $empleado_nombre = "Desconocido";
                $queryEmpleado = "SELECT nombre_completo FROM Empleados WHERE id = $empleado_id LIMIT 1";
                $resultEmpleado = mysqli_query($enlace, $queryEmpleado);
                if ($resultEmpleado && $rowEmpleado = mysqli_fetch_assoc($resultEmpleado)) {
                    $empleado_nombre = $rowEmpleado['nombre_completo'];
                }
                $datosTicket['empleado_nombre'] = $empleado_nombre;

                // Enviar email automáticamente usando EmailLibreria
                $datos_email_auto = [
                    'ticket_id' => $datosTicket['id'],
                    'fecha' => $datosTicket['fecha_compra'],
                    'empleado' => $datosTicket['empleado_nombre'],
                    'cliente' => $datosTicket['cliente_nombre'],
                    'telefono' => $datosTicket['cliente_telefono'],
                    'email' => $datosTicket['correo'],
                    'libro' => $datosTicket['titulo'],
                    'autor' => $datosTicket['autor'] ?? 'N/A',
                    'editorial' => $datosTicket['editorial'] ?? 'N/A',
                    'cantidad' => $datosTicket['cantidad'],
                    'precio' => $datosTicket['precio_venta'],
                    'metodo_pago' => $datosTicket['metodo_pago'],
                    'total' => $datosTicket['total_pagado']
                ];
                
                // Usar EmailLibreria en lugar de PHPMailer
                $mailer_auto = new EmailLibreria(false);
                $email_resultado_auto = $mailer_auto->enviarComprobante(
                    'diaztecete@gmail.com',
                    'hqicplranmnjaojr',
                    $datosTicket['correo'],
                    $datos_email_auto
                );
                
                if ($email_resultado_auto['success']) {
                    $mensaje = "✅ Venta registrada correctamente. El comprobante se envió al correo del cliente.";
                } else {
                    $mensaje = "✅ Venta registrada correctamente. ❌ No se pudo enviar correo: " . $email_resultado_auto['error'];
                }
                
                $mostrarFormulario = false;
                $mostrarTicket = true;

            } else {
                $mensaje = "Error al registrar la venta: " . mysqli_error($enlace);
            }
        }
    } // Cierre del else (lógica original de venta)
}

if ($mostrarFormulario) {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (!isset($_GET['id_libro'])) {
            die("Libro no especificado.");
        }
        $libro_id = intval($_GET['id_libro']);
    }
    $query = "SELECT titulo, precio_venta FROM Libros WHERE id = $libro_id";
    $result = mysqli_query($enlace, $query);
    $libro = mysqli_fetch_assoc($result);

    if (!$libro) {
        die("Libro no encontrado.");
    }
}
?>
<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Venta</title>
    <style>
        /* Reset y limpieza general */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
    
        
        /* Ocultar cualquier texto flotante - MÁS ESPECÍFICO */
        body > p:first-child,
        body > div:first-child:not(.main-container),
        body > span:first-child,
        body > text,
        body::before,
        .debug-output,
        .smtp-debug {
            display: none !important;
            visibility: hidden !important;
            position: absolute !important;
            left: -9999px !important;
            height: 0 !important;
            overflow: hidden !important;
        }
        
        /* Aplicar márgenes negativos si es necesario */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
        }
        
        /* Asegurar que el primer elemento visible sea nuestro contenido */
        .main-container:first-of-type {
            margin-top: 0 !important;
            padding-top: 20px !important;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #5d5c5cff 0%, #ffffffff 100%);
            margin: 0 !important;
            padding: 0 !important;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Contenedor principal */
        .main-container {
            padding: 20px;
            margin-top: 0 !important;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            font-weight: 600;
        }
        
        form {
            background: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #007bff;
        }
        
        .form-section h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: bold;
            color: #34495e;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 10px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.25);
            transform: translateY(-1px);
        }
            background: #eef6fb;
            box-shadow: 0 0 0 2px #67aee8;
        }
        input[readonly] {
            background-color: #ecf0f1;
            cursor: not-allowed;
        }
        button[type="submit"] {
            margin-top: 28px;
            width: 100%;
            padding: 13px;
            background-color: #27ae60;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 2px 8px rgba(39,174,96,0.08);
        }
        button[type="submit"]:hover {
            background-color: #219150;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            border: none;
            border-radius: 7px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #219150;
        }
        .mensaje {
            max-width: 450px;
            margin: 20px auto;
            padding: 15px;
            border-radius: 7px;
            font-weight: bold;
            text-align: center;
            color: white;
        }
        .exito {
            background-color: #27ae60;
        }
        .error {
            background-color: #e74c3c;
        }
        .btn-volver {
            display: block;
            width: 150px;
            margin: 30px auto 0;
            padding: 10px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 7px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-volver:hover {
            background-color: #1c5980;
        }

        /* Estilos para el ticket */
        .ticket-container {
            max-width: 400px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .ticket-header {
            background: linear-gradient(135deg, #2980b9, #3498db);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .ticket-header h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .ticket-header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .ticket-body {
            padding: 25px;
            line-height: 1.6;
        }

        .ticket-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .ticket-row.separador {
            border-top: 1px dashed #bdc3c7;
            margin-top: 15px;
            padding-top: 15px;
        }

        .ticket-label {
            font-weight: bold;
            color: #2c3e50;
        }

        .ticket-value {
            color: #34495e;
            text-align: right;
            max-width: 200px;
            word-wrap: break-word;
        }

        .ticket-total {
            background: #ecf0f1;
            margin: 15px -25px -25px -25px;
            padding: 20px 25px;
        }

        .ticket-total .ticket-row {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 0;
        }

        .ticket-footer {
            text-align: center;
            padding: 15px 25px;
            background: #f8f9fa;
            color: #7f8c8d;
            font-size: 12px;
        }

        .btn-ticket {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            margin: 10px 5px;
            font-size: 14px;
        }

        .btn-ticket:hover {
            background: #219150;
        }

        .btn-ticket.secondary {
            background: #95a5a6;
        }

        .btn-ticket.secondary:hover {
            background: #7f8c8d;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .ticket-container,
            .ticket-container * {
                visibility: visible;
            }
            .ticket-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: none !important;
                box-shadow: none !important;
            }
            .btn-ticket {
                display: none !important;
            }
        }

        /* Estilos para el modal de email */
        .modal-email {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translate(-50%, -60%);
            }
            to { 
                opacity: 1; 
                transform: translate(-50%, -50%);
            }
        }

        .modal-content h3 {
            margin-top: 0;
            color: #2c3e50;
            text-align: center;
        }

        .modal-content input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .modal-content input[type="email"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.25);
        }

        .modal-buttons {
            text-align: center;
            margin-top: 25px;
        }

        .modal-buttons .btn-ticket {
            margin: 0 5px;
        }
    </style>
</head>
<body>
<div class="main-container">
    <h2>📝 Formulario de Venta</h2>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $mostrarFormulario ? 'error' : 'exito' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($mostrarFormulario): ?>
    <form action="formulario_venta.php" method="POST" novalidate>
        <input type="hidden" name="libro_id" value="<?= $libro_id ?>">

        <label for="titulo">Libro:</label>
        <input type="text" id="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" readonly>

        <label for="cliente_nombre">Nombre del cliente:</label>
        <input type="text" name="cliente_nombre" id="cliente_nombre" required placeholder="Ingresa el nombre del cliente">

        <label for="cliente_telefono">Teléfono del cliente:</label>
        <input type="text" name="cliente_telefono" id="cliente_telefono" required placeholder="Ingresa el teléfono del cliente">

        <label for="cliente_correo">Correo del cliente:</label>
        <input type="email" name="cliente_correo" id="cliente_correo" required placeholder="Ingresa el correo del cliente">

        <label for="metodo_pago">Método de pago:</label>
        <select name="metodo_pago" id="metodo_pago" required>
            <option value="">Seleccione</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
            <option value="Transferencia">Transferencia</option>
        </select>

        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" id="cantidad" value="1" min="1" required>

        <label for="total_pagado">Total a pagar:</label>
        <input type="text" id="total_pagado" value="$<?= number_format($libro['precio_venta'], 2) ?>" readonly>

        <button type="submit">Registrar Venta</button>
    </form>
    <?php else: ?>
        <!-- Ticket de Compra -->
        <?php if ($mostrarTicket && $datosTicket): ?>
        <div class="ticket-container">
            <div class="ticket-header">
                <h2>🧾 TICKET DE COMPRA</h2>
                <p>Biblioteca Universidad</p>
                <p><?= date('d/m/Y H:i:s') ?></p>
            </div>
            
            <div class="ticket-body">
                <div class="ticket-row">
                    <span class="ticket-label">Ticket #:</span>
                    <span class="ticket-value"><?= str_pad($datosTicket['id'], 6, '0', STR_PAD_LEFT) ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Fecha:</span>
                    <span class="ticket-value"><?= date('d/m/Y', strtotime($datosTicket['fecha_compra'])) ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Atendido por:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['empleado_nombre']) ?></span>
                </div>

                <div class="ticket-row separador">
                    <span class="ticket-label">Cliente:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['cliente_nombre']) ?></span>
                </div>
                <div class="ticket-row">
                    <span class="ticket-label">Teléfono:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['cliente_telefono']) ?></span>
                </div>
                <div class="ticket-row">
                    <span class="ticket-label">Correo:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['correo']) ?></span>
                </div>

                <div class="ticket-row separador">
                    <span class="ticket-label">Libro:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['titulo']) ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Autor:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['autor']) ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Editorial:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['editorial']) ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Cantidad:</span>
                    <span class="ticket-value"><?= $datosTicket['cantidad'] ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Precio unitario:</span>
                    <span class="ticket-value">$<?= number_format($datosTicket['precio_venta'], 2) ?></span>
                </div>
                
                <div class="ticket-row">
                    <span class="ticket-label">Método de pago:</span>
                    <span class="ticket-value"><?= htmlspecialchars($datosTicket['metodo_pago']) ?></span>
                </div>
            </div>

            <div class="ticket-total">
                <div class="ticket-row">
                    <span class="ticket-label">TOTAL PAGADO:</span>
                    <span class="ticket-value">$<?= number_format($datosTicket['total_pagado'], 2) ?></span>
                </div>
            </div>

            <div class="ticket-footer">
                <p>¡Gracias por su compra!</p>
                <p>Conserve este ticket como comprobante</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button class="btn-ticket" onclick="window.print()">🖨️ Imprimir Ticket</button>
            <button class="btn-ticket" onclick="mostrarFormularioEmail()">📧 Enviar Correo</button>
            <button class="btn-ticket secondary" onclick="window.location.href='enviar_comprobantes.php?venta_id=<?= $datosTicket['id'] ?>'">🔗 Gestionar Comprobante</button>
            <button class="btn-ticket secondary" onclick="window.location.href='registrar_compra.php'">📚 Nueva Venta</button>
            <button class="btn-ticket secondary" onclick="window.location.href='menuadmin.php'">🏠 Menú Principal</button>
        </div>

        <!-- Modal para envío de correo -->
        <div id="modal-email" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
                <h3 style="margin-top: 0; color: #2c3e50;">📧 Enviar Comprobante por Email</h3>
                
                <form method="POST" style="margin: 0; padding: 0; box-shadow: none;">
                    <input type="hidden" name="accion" value="enviar_comprobante">
                    <input type="hidden" name="venta_id" value="<?= $datosTicket['id'] ?>">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #34495e;">
                            Email de destino:
                        </label>
                        <input type="email" 
                               name="email_destino" 
                               required 
                               value="<?= htmlspecialchars($datosTicket['correo']) ?>"
                               placeholder="correo@ejemplo.com"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;">
                        <small style="color: #666; font-size: 12px;">
                            Puedes cambiar el email si necesitas enviarlo a otra dirección
                        </small>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <button type="submit" class="btn-ticket" style="background: #28a745;">
                            🚀 Enviar Comprobante
                        </button>
                        <button type="button" class="btn-ticket secondary" onclick="cerrarFormularioEmail()" style="margin-left: 10px;">
                            ❌ Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        // Solo ejecutar si existen los elementos (formulario de venta)
        const cantidadInput = document.getElementById('cantidad');
        const totalInput = document.getElementById('total_pagado');
        
        if (cantidadInput && totalInput) {
            const precioUnitario = <?= json_encode(floatval($libro['precio_venta'] ?? 0)) ?>;

            cantidadInput.addEventListener('input', () => {
                let cant = parseInt(cantidadInput.value) || 1;
                if (cant < 1) cant = 1;
                const total = (precioUnitario * cant).toFixed(2);
                totalInput.value = "$" + total;
            });
        }

        // Funciones para el modal de email
        function mostrarFormularioEmail() {
            document.getElementById('modal-email').style.display = 'block';
        }

        function cerrarFormularioEmail() {
            document.getElementById('modal-email').style.display = 'none';
        }

        // Cerrar modal si se hace click fuera de él
        const modalEmail = document.getElementById('modal-email');
        if (modalEmail) {
            modalEmail.addEventListener('click', function(e) {
                if (e.target === this) {
                    cerrarFormularioEmail();
                }
            });
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarFormularioEmail();
            }
        });
    </script>
    <div style="text-align: center; margin-top: 30px;">
    <button class="btn-volver" onclick="history.back()">← Volver</button>
</div>

</div>
</body>
</html>
<?php include 'pie.php'; ?>

