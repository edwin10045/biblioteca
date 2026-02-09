<?php
// Activar debug de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Diagnóstico del Sistema de Email</h1>";
echo "<pre>";

echo "=== VERIFICACIÓN INICIAL ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "OpenSSL disponible: " . (extension_loaded('openssl') ? 'SÍ' : 'NO') . "\n";
echo "Sockets disponibles: " . (function_exists('fsockopen') ? 'SÍ' : 'NO') . "\n";
echo "Stream crypto disponible: " . (function_exists('stream_socket_enable_crypto') ? 'SÍ' : 'NO') . "\n";

echo "\n=== PRUEBA DE CONEXIÓN ===\n";
$smtp_conn = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
if ($smtp_conn) {
    echo "✅ Conexión a Gmail SMTP exitosa\n";
    
    $greeting = fgets($smtp_conn);
    echo "Saludo del servidor: " . trim($greeting) . "\n";
    
    fputs($smtp_conn, "EHLO localhost\r\n");
    $ehlo_response = '';
    while (($line = fgets($smtp_conn)) !== false) {
        $ehlo_response .= $line;
        if (preg_match('/^[0-9]{3} /', $line)) {
            break;
        }
    }
    echo "EHLO response: " . trim($ehlo_response) . "\n";
    
    fputs($smtp_conn, "STARTTLS\r\n");
    $tls_response = fgets($smtp_conn);
    echo "STARTTLS response: " . trim($tls_response) . "\n";
    
    if (stream_socket_enable_crypto($smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        echo "✅ TLS activado exitosamente\n";
        
        fputs($smtp_conn, "EHLO localhost\r\n");
        $ehlo_tls = '';
        while (($line = fgets($smtp_conn)) !== false) {
            $ehlo_tls .= $line;
            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        echo "EHLO post-TLS: " . trim($ehlo_tls) . "\n";
        
        // Probar autenticación
        fputs($smtp_conn, "AUTH LOGIN\r\n");
        $auth_response = fgets($smtp_conn);
        echo "AUTH LOGIN response: " . trim($auth_response) . "\n";
        
        if (strpos($auth_response, '334') === 0) {
            echo "✅ Servidor acepta autenticación LOGIN\n";
            
            // Probar con credenciales
            fputs($smtp_conn, base64_encode('diaztecete@gmail.com') . "\r\n");
            $user_response = fgets($smtp_conn);
            echo "Usuario response: " . trim($user_response) . "\n";
            
            fputs($smtp_conn, base64_encode('hqicplranmnjaojr') . "\r\n");
            $pass_response = fgets($smtp_conn);
            echo "Password response: " . trim($pass_response) . "\n";
            
            if (strpos($pass_response, '235') === 0) {
                echo "✅ AUTENTICACIÓN EXITOSA!\n";
                
                // Probar envío rápido
                fputs($smtp_conn, "MAIL FROM:<diaztecete@gmail.com>\r\n");
                $mail_from = fgets($smtp_conn);
                echo "MAIL FROM response: " . trim($mail_from) . "\n";
                
                fputs($smtp_conn, "RCPT TO:<diaztecete@gmail.com>\r\n");
                $rcpt_to = fgets($smtp_conn);
                echo "RCPT TO response: " . trim($rcpt_to) . "\n";
                
                fputs($smtp_conn, "DATA\r\n");
                $data_response = fgets($smtp_conn);
                echo "DATA response: " . trim($data_response) . "\n";
                
                if (strpos($data_response, '354') === 0) {
                    echo "✅ Servidor listo para recibir mensaje\n";
                    
                    $mensaje = "From: Test <diaztecete@gmail.com>\r\n";
                    $mensaje .= "To: diaztecete@gmail.com\r\n";
                    $mensaje .= "Subject: Test desde Biblioteca\r\n";
                    $mensaje .= "MIME-Version: 1.0\r\n";
                    $mensaje .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    $mensaje .= "\r\n";
                    $mensaje .= "Hola! Este es un test desde el sistema de biblioteca.\r\n";
                    $mensaje .= "Fecha: " . date('Y-m-d H:i:s') . "\r\n";
                    $mensaje .= "\r\n.\r\n";
                    
                    fputs($smtp_conn, $mensaje);
                    $send_response = fgets($smtp_conn);
                    echo "SEND response: " . trim($send_response) . "\n";
                    
                    if (strpos($send_response, '250') === 0) {
                        echo "🎉 ¡EMAIL ENVIADO EXITOSAMENTE!\n";
                    } else {
                        echo "❌ Error enviando email: " . trim($send_response) . "\n";
                    }
                } else {
                    echo "❌ Error en DATA: " . trim($data_response) . "\n";
                }
                
            } else {
                echo "❌ Error de autenticación: " . trim($pass_response) . "\n";
            }
        } else {
            echo "❌ Servidor no acepta AUTH LOGIN: " . trim($auth_response) . "\n";
        }
        
    } else {
        echo "❌ Error activando TLS\n";
    }
    
    fputs($smtp_conn, "QUIT\r\n");
    fclose($smtp_conn);
    
} else {
    echo "❌ Error conectando a Gmail: $errno - $errstr\n";
}

echo "\n=== DIAGNÓSTICO COMPLETADO ===\n";
echo "</pre>";
?>
