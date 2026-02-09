<?php
echo "<h2>🔍 Diagnóstico Completo del Sistema de Email</h2>";

// 1. Información del sistema
echo "<h3>📋 Información del Sistema</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Sistema Operativo:</strong> " . php_uname() . "</p>";
echo "<p><strong>OpenSSL:</strong> " . (extension_loaded('openssl') ? '✅ Disponible' : '❌ No disponible') . "</p>";
echo "<p><strong>Sockets:</strong> " . (extension_loaded('sockets') ? '✅ Disponible' : '❌ No disponible') . "</p>";
echo "<p><strong>cURL:</strong> " . (extension_loaded('curl') ? '✅ Disponible' : '❌ No disponible') . "</p>";

echo "<hr>";

// 2. Test de conectividad a Gmail
echo "<h3>🌐 Test de Conectividad a Gmail SMTP</h3>";

$gmail_host = 'smtp.gmail.com';
$gmail_port = 587;

echo "<p>Probando conexión a <strong>$gmail_host:$gmail_port</strong>...</p>";

$context = stream_context_create([
    'socket' => [
        'timeout' => 30,
    ]
]);

$socket = @stream_socket_client("tcp://$gmail_host:$gmail_port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

if ($socket) {
    echo "<p style='color: green;'>✅ Conexión TCP exitosa a Gmail SMTP</p>";
    
    // Leer respuesta inicial
    $response = fgets($socket);
    echo "<p><strong>Respuesta inicial:</strong> <code>" . htmlspecialchars(trim($response)) . "</code></p>";
    
    // Enviar EHLO
    fwrite($socket, "EHLO localhost\r\n");
    $ehlo_response = '';
    while (($line = fgets($socket)) !== false) {
        $ehlo_response .= $line;
        if (substr($line, 3, 1) === ' ') break; // Última línea de respuesta
    }
    echo "<p><strong>Respuesta EHLO:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($ehlo_response) . "</pre>";
    
    // Test STARTTLS
    fwrite($socket, "STARTTLS\r\n");
    $starttls_response = fgets($socket);
    echo "<p><strong>Respuesta STARTTLS:</strong> <code>" . htmlspecialchars(trim($starttls_response)) . "</code></p>";
    
    fclose($socket);
} else {
    echo "<p style='color: red;'>❌ Error de conexión: $errstr ($errno)</p>";
}

echo "<hr>";

// 3. Test de configuración de mail()
echo "<h3>📧 Configuración mail() de PHP</h3>";
echo "<p><strong>SMTP:</strong> " . (ini_get('SMTP') ?: 'No configurado') . "</p>";
echo "<p><strong>smtp_port:</strong> " . (ini_get('smtp_port') ?: 'No configurado') . "</p>";
echo "<p><strong>sendmail_from:</strong> " . (ini_get('sendmail_from') ?: 'No configurado') . "</p>";
echo "<p><strong>sendmail_path:</strong> " . (ini_get('sendmail_path') ?: 'No configurado') . "</p>";

echo "<hr>";

// 4. Test de PHPMailer básico (sin enviar)
echo "<h3>🔧 Test de PHPMailer (sin enviar)</h3>";

if (file_exists('PHPMailer/PHPMailer.php')) {
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';

    // Usar nombres completos de clase en lugar de use statements
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        echo "<p style='color: green;'>✅ PHPMailer cargado correctamente</p>";
        
        // Configurar pero no enviar
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'diaztecete@gmail.com';
        $mail->Password = 'hqicplranmnjaojr';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        echo "<p>✅ Configuración PHPMailer completada</p>";
        echo "<p><strong>Host:</strong> " . $mail->Host . "</p>";
        echo "<p><strong>Puerto:</strong> " . $mail->Port . "</p>";
        echo "<p><strong>Seguridad:</strong> " . $mail->SMTPSecure . "</p>";
        echo "<p><strong>Usuario:</strong> " . $mail->Username . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error PHPMailer: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ PHPMailer no encontrado</p>";
}

echo "<hr>";

// 5. Test manual de SMTP con debugging completo
echo "<h3>🔍 Test Manual SMTP con Debug Completo</h3>";

if (file_exists('PHPMailer/PHPMailer.php')) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'diaztecete@gmail.com';
        $mail->Password = 'hqicplranmnjaojr';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Debug máximo
        $mail->SMTPDebug = 4;
        $mail->Debugoutput = function($str, $level) {
            echo "<div style='background: #f8f9fa; border-left: 4px solid #007bff; padding: 8px; margin: 3px 0; font-family: monospace; font-size: 12px;'>";
            echo "<strong>Debug Level $level:</strong> " . htmlspecialchars($str);
            echo "</div>";
        };
        
        $mail->setFrom('diaztecete@gmail.com', 'Test Biblioteca');
        $mail->addAddress('diaztecete@gmail.com', 'Usuario Test');
        $mail->isHTML(true);
        $mail->Subject = 'Test Debug Completo - ' . date('H:i:s');
        $mail->Body = '<h3>Test de debugging completo</h3><p>Si ves esto, el sistema funciona.</p>';
        
        echo "<div style='border: 2px solid #dc3545; padding: 15px; background: #fff5f5;'>";
        echo "<p><strong>🚀 Intentando enviar con debug completo...</strong></p>";
        
        if ($mail->send()) {
            echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ ¡CORREO ENVIADO EXITOSAMENTE!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $mail->ErrorInfo . "</p>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='border: 2px solid #dc3545; padding: 15px; background: #fff5f5;'>";
        echo "<p style='color: red; font-weight: bold;'>❌ Excepción: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

echo "<hr>";

// 6. Recomendaciones finales
echo "<h3>💡 Recomendaciones</h3>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
echo "<h4>Si aún no llegan los correos:</h4>";
echo "<ol>";
echo "<li><strong>Verifica tu App Password:</strong> Debe ser exactamente 16 caracteres</li>";
echo "<li><strong>Revisa Gmail:</strong> Bandeja de entrada, spam, promociones</li>";
echo "<li><strong>Prueba otro email:</strong> Envía a Yahoo, Hotmail, etc.</li>";
echo "<li><strong>Firewall:</strong> Asegúrate que no bloquee puerto 587</li>";
echo "<li><strong>Antivirus:</strong> Puede bloquear conexiones SMTP</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><small>Diagnóstico ejecutado: " . date('Y-m-d H:i:s') . "</small></p>";
?>
