<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'vendor/autoload.php'; // Para la librería QR Code

// Datos del formulario
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$date = $_POST['date'];
$time = $_POST['time'];
$guests = $_POST['guests'];
$dishes = implode(", ", $_POST['dish']); // Convierte los platos seleccionados en texto

// Generar un código de reserva único
$reservation_code = strtoupper(substr(md5(time()), 0, 8)); 

// Crear el contenido del código QR
$qr_content = "Reserva: $reservation_code\nNombre: $name\nFecha: $date\nHora: $time\nPersonas: $guests\nPlatos: $dishes";
$qr_path = "qrcodes/$reservation_code.png";

// Generar el código QR
$qrCode = new \Endroid\QrCode\QrCode($qr_content);
$qrCode->writeFile($qr_path);

// Enviar correo al restaurante
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'tu_correo@gmail.com'; 
    $mail->Password = 'tu_contraseña'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Correo al restaurante
    $mail->setFrom('tu_correo@gmail.com', 'Reservas Restaurante');
    $mail->addAddress('correo_restaurante@gmail.com');
    $mail->Subject = "Nueva Reserva - $name";
    $mail->Body = "Detalles de la reserva:\n
                   Código: $reservation_code\n
                   Nombre: $name\n
                   Fecha: $date\n
                   Hora: $time\n
                   Personas: $guests\n
                   Platos: $dishes";
    $mail->send();

    // Correo al cliente con el QR
    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = "Tu Reserva - Código: $reservation_code";
    $mail->Body = "Hola $name,\n\nGracias por tu reserva. 
                   Usa este código QR cuando llegues al restaurante:\n
                   Código de reserva: $reservation_code\n
                   Fecha: $date\n
                   Hora: $time\n
                   Personas: $guests\n
                   Platos: $dishes";
    $mail->addAttachment($qr_path);
    $mail->send();

    // Redirigir a la confirmación
    header("Location: confirmacion.html?code=$reservation_code");
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
?>
