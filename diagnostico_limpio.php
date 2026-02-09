<?php
echo "<h2>🔍 Diagnóstico Completo del Sistema de Email - VERSIÓN LIMPIA</h2>";

// 1. Información básica del sistema
echo "<h3>📋 Información del Sistema PHP</h3>";
echo "<p><strong>Versión PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Sistema:</strong> " . php_uname('s') . " " . php_uname('r') . "</p>";
echo "<p><strong>OpenSSL:</strong> " . (extension_loaded('openssl') ? '✅ Disponible' : '❌ No disponible') . "</p>";
echo "<p><strong>Sockets:</strong> " . (extension_loaded('sockets') ? '✅ Disponible' : '❌ No disponible') . "</p>";
echo "<p><strong>cURL:</strong> " . (extension_loaded('curl') ? '✅ Disponible' : '❌ No disponible') . "</p>";
echo "<p><strong>mbstring:</strong> " . (extension_loaded('mbstring') ? '✅ Disponible' : '❌ No disponible') . "</p>";

echo "<hr>";

// 2. Configuración mail() de PHP
echo "<h3>📧 Configuración mail() de PHP</h3>";
$smtp_host = ini_get('SMTP') ?: 'No configurado';
$smtp_port = ini_get('smtp_port') ?: 'No configurado';
$sendmail_from = ini_get('sendmail_from') ?: 'No configurado';
$sendmail_path = ini_get('sendmail_path') ?: 'No configurado';

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><td><strong>SMTP</strong></td><td>$smtp_host</td></tr>";
echo "<tr><td><strong>smtp_port</strong></td><td>$smtp_port</td></tr>";
echo "<tr><td><strong>sendmail_from</strong></td><td>$sendmail_from</td></tr>";
echo "<tr><td><strong>sendmail_path</strong></td><td>$sendmail_path</td></tr>";
echo "</table>";

echo "<hr>";

// 3. Test de conectividad a Gmail
echo "<h3>🌐 Test de Conectividad a Gmail SMTP</h3>";
echo "<p>Probando conexión directa a <strong>smtp.gmail.com:587</strong>...</p>";

$socket = @stream_socket_client("tcp://smtp.gmail.com:587", $errno, $errstr, 15);

if ($socket) {
    echo "<p style='color: green;'>✅ Conexión TCP exitosa a Gmail</p>";
    
    $response = fgets($socket);
    echo "<p><strong>Respuesta del servidor:</strong> <code>" . htmlspecialchars(trim($response)) . "</code></p>";
    
    if (strpos($response, '220') === 0) {
        echo "<p style='color: green;'>✅ Servidor SMTP respondió correctamente</p>";
        
        // Test EHLO
        fwrite($socket, "EHLO localhost\r\n");
        $ehlo_response = stream_get_contents($socket, 1024);
        echo "<p><strong>Respuesta EHLO:</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; font-size: 12px;'>" . htmlspecialchars($ehlo_response) . "</pre>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Respuesta inesperada del servidor</p>";
    }
    
    fclose($socket);
} else {
    echo "<p style='color: red;'>❌ Error de conexión: $errstr (Código: $errno)</p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Firewall bloqueando puerto 587</li>";
    echo "<li>Antivirus bloqueando conexiones SMTP</li>";
    echo "<li>Problemas de conectividad a internet</li>";
    echo "</ul>";
}

echo "<hr>";

// 4. Test de mail() con un ejemplo simple
echo "<h3>✉️ Test de función mail() PHP</h3>";

$test_subject = "Test mail() - " . date('H:i:s');
$test_message = "Este es un test de la función mail() de PHP.\nFecha: " . date('Y-m-d H:i:s');
$test_headers = "From: test@localhost\r\nContent-Type: text/plain; charset=UTF-8";

echo "<p>Intentando enviar con mail() a: <strong>diaztecete@gmail.com</strong></p>";

if (mail('diaztecete@gmail.com', $test_subject, $test_message, $test_headers)) {
    echo "<p style='color: green;'>✅ mail() ejecutado sin errores</p>";
    echo "<p><em>Nota: Esto no garantiza que el correo se envíe, solo que PHP no reportó errores.</em></p>";
} else {
    echo "<p style='color: red;'>❌ mail() reportó un error</p>";
}

echo "<hr>";

// 5. Test de PHPMailer (verificación de archivos)
echo "<h3>📦 Verificación de PHPMailer</h3>";

$phpmailer_files = [
    'PHPMailer/PHPMailer.php',
    'PHPMailer/SMTP.php',
    'PHPMailer/Exception.php'
];

$all_files_exist = true;
foreach ($phpmailer_files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p>✅ <strong>$file</strong> - Tamaño: " . number_format($size) . " bytes</p>";
    } else {
        echo "<p>❌ <strong>$file</strong> - No encontrado</p>";
        $all_files_exist = false;
    }
}

if ($all_files_exist) {
    echo "<p style='color: green;'>✅ Todos los archivos PHPMailer están presentes</p>";
    
    // Intentar cargar PHPMailer
    try {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';
        
        // Crear instancia usando el namespace completo
        $test_mailer = new PHPMailer\PHPMailer\PHPMailer(false);
        echo "<p style='color: green;'>✅ PHPMailer se cargó correctamente</p>";
        echo "<p><strong>Versión:</strong> " . $test_mailer::VERSION . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error cargando PHPMailer: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p style='color: red;'>❌ Error fatal cargando PHPMailer: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Faltan archivos de PHPMailer</p>";
}

echo "<hr>";

// 6. Información de diagnóstico adicional
echo "<h3>🔧 Información de Diagnóstico</h3>";

echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px;'>";
echo "<h4>Resumen del Sistema:</h4>";
echo "<ul>";

// OpenSSL
if (extension_loaded('openssl')) {
    echo "<li>✅ <strong>OpenSSL:</strong> Disponible para conexiones seguras</li>";
} else {
    echo "<li>❌ <strong>OpenSSL:</strong> No disponible - Requerido para SMTP seguro</li>";
}

// Función mail()
if (function_exists('mail')) {
    echo "<li>✅ <strong>Función mail():</strong> Disponible</li>";
} else {
    echo "<li>❌ <strong>Función mail():</strong> No disponible</li>";
}

// Socket streams
if (function_exists('stream_socket_client')) {
    echo "<li>✅ <strong>Stream sockets:</strong> Disponibles para conexiones SMTP</li>";
} else {
    echo "<li>❌ <strong>Stream sockets:</strong> No disponibles</li>";
}

echo "</ul>";
echo "</div>";

echo "<hr>";

// 7. Recomendaciones
echo "<h3>💡 Recomendaciones Específicas</h3>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
echo "<h4>Para solucionar problemas de email:</h4>";
echo "<ol>";
echo "<li><strong>Si la conexión TCP falla:</strong>";
echo "<ul>";
echo "<li>Desactiva temporalmente antivirus/firewall</li>";
echo "<li>Verifica conectividad: <code>telnet smtp.gmail.com 587</code></li>";
echo "<li>Prueba desde otra red (datos móviles)</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Si PHPMailer falla:</strong>";
echo "<ul>";
echo "<li>Verifica que la App Password sea correcta (16 caracteres)</li>";
echo "<li>Confirma que 2FA esté activado en Gmail</li>";
echo "<li>Prueba el MailerSimple que creamos</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Si mail() no funciona:</strong>";
echo "<ul>";
echo "<li>Configura SMTP en php.ini</li>";
echo "<li>O instala sendmail/postfix</li>";
echo "<li>O usa Mercury Mail de XAMPP</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";

// 8. Próximos pasos
echo "<h3>🚀 Próximos Pasos</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Basado en este diagnóstico:</strong></p>";
echo "<ul>";
if ($socket) {
    echo "<li>✅ La conectividad a Gmail funciona</li>";
    echo "<li>▶️ <a href='test_mailer_simple.php'>Prueba el MailerSimple</a></li>";
} else {
    echo "<li>❌ Hay problemas de conectividad</li>";
    echo "<li>▶️ Revisa firewall/antivirus primero</li>";
}

if ($all_files_exist) {
    echo "<li>✅ PHPMailer está disponible</li>";
} else {
    echo "<li>❌ Necesitas arreglar los archivos PHPMailer</li>";
}

echo "<li>▶️ <a href='formulario_venta_mejorado.php'>Usa el formulario mejorado</a></li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center;'><small>Diagnóstico ejecutado: " . date('Y-m-d H:i:s') . "</small></p>";
?>
