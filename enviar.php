<?php
/**
 * ═══════════════════════════════════════════════════════════════════════
 *   📧 ENVIAR.PHP — Manejador de formularios para Contratamos Complementos
 * ───────────────────────────────────────────────────────────────────────
 *
 *   Este archivo recibe los formularios del sitio (cotización y hoja de vida)
 *   y los envía por correo electrónico al destinatario correspondiente.
 *
 *   📍 INSTALACIÓN EN HOSTINGER:
 *      1. Abra el File Manager (hPanel → Archivos → Administrador de archivos)
 *      2. Vaya a la carpeta public_html
 *      3. Suba este archivo (enviar.php) AL LADO de index.html
 *      4. ¡Listo! No requiere configuración adicional, Hostinger ya tiene PHP.
 *
 *   ⚙️  CONFIGURACIÓN:
 *      Solo si necesita cambiar algo, edite las variables de la sección
 *      "CONFIGURACIÓN" justo abajo. Lo demás funciona automáticamente.
 *
 * ═══════════════════════════════════════════════════════════════════════ */


/* ─── CONFIGURACIÓN ──────────────────────────────────────────────────── */

// Correo "remitente" — DEBE ser un correo del mismo dominio en Hostinger
// (de lo contrario los correos llegan a spam o son rechazados).
// Cree esta cuenta en: hPanel → Correos electrónicos → Cuentas de correo
$REMITENTE = 'no-responder@contratamoscomplementos.com';

// Nombre que aparece como remitente en la bandeja de entrada
$NOMBRE_REMITENTE = 'Sitio web Contratamos Complementos';

// Correos de destino (son los mismos que están en CONFIG del index.html)
$CORREO_COTIZACION = 'gerencia@contratamoscomplementos.com';
$CORREO_HOJA_VIDA  = 'gestionhumana@contratamoscomplementos.com';

// Tamaño máximo de archivo adjunto (en MB)
$MAX_FILE_MB = 5;

// Tipos de archivo permitidos para hoja de vida
$EXTENSIONES_PERMITIDAS = ['pdf', 'doc', 'docx'];


/* ─── NO MODIFICAR ABAJO DE ESTE COMENTARIO ──────────────────────────── */

// Solo aceptar peticiones POST
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// ════════════════════════════════════════════════════════════════════
// 🍯 HONEYPOT — Detección anti-spam
// ════════════════════════════════════════════════════════════════════
// El campo 'website' es invisible para humanos (oculto por CSS).
// Si llega lleno, es un bot. Respondemos "success" para no alertarlo
// pero no enviamos el correo. Así el bot piensa que funcionó y no
// intenta de nuevo con otra técnica.
if (!empty($_POST['website'])) {
    // Log opcional (descomente si quiere registrar intentos de spam)
    // error_log('Honeypot triggered: ' . print_r($_POST, true));
    echo json_encode(['success' => true]);
    exit;
}

// Función para responder en JSON y terminar
function respond($success, $message = '') {
    echo json_encode([
        'success' => $success,
        'error'   => $success ? '' : $message
    ]);
    exit;
}

// Función simple para sanitizar texto (evita inyección de cabeceras)
function clean($value) {
    if (!is_string($value)) return '';
    $value = trim($value);
    // Elimina caracteres de control y posibles inyecciones de headers
    $value = str_replace(["\r", "\n", "%0A", "%0D"], '', $value);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Validación de email
function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Determina el tipo de formulario
$kind = $_POST['_kind'] ?? '';
if (!in_array($kind, ['cotizacion', 'hoja-vida'])) {
    respond(false, 'Tipo de formulario no válido.');
}

// Recoge los campos comunes
$email     = clean($_POST['email']     ?? '');
$telefono  = clean($_POST['telefono']  ?? '');

if (!valid_email($email)) {
    respond(false, 'El correo electrónico no es válido.');
}

// ─── PROCESAR SEGÚN EL TIPO DE FORMULARIO ────────────────────────────────

if ($kind === 'cotizacion') {
    // ─── FORMULARIO DE COTIZACIÓN ───
    $destinatario = $CORREO_COTIZACION;

    $empresa  = clean($_POST['empresa']  ?? '');
    $nit      = clean($_POST['nit']      ?? '');
    $contacto = clean($_POST['contacto'] ?? '');
    $cargo    = clean($_POST['cargo']    ?? '');
    $sector   = clean($_POST['sector']   ?? '');
    $numero   = clean($_POST['numero']   ?? '');
    $perfil   = clean($_POST['perfil']   ?? '');
    $detalle  = clean($_POST['detalle']  ?? '');

    if (empty($empresa) || empty($contacto)) {
        respond(false, 'Faltan campos obligatorios.');
    }

    $asunto = "Nueva solicitud de cotización — $empresa";

    // Cuerpo del correo en HTML — diseño limpio y profesional
    $cuerpo = "
    <html>
    <body style='font-family: Arial, sans-serif; color: #0E2233; line-height: 1.6; max-width: 640px; margin: 0 auto; padding: 20px; background: #FBF9F4;'>
      <div style='background: #FFFFFF; padding: 32px; border-radius: 6px; border: 1px solid #E3DED3;'>
        <div style='border-bottom: 2px solid #2FA991; padding-bottom: 16px; margin-bottom: 24px;'>
          <h2 style='margin: 0; color: #1D5B8B; font-size: 22px;'>Nueva solicitud de cotización</h2>
          <p style='margin: 6px 0 0 0; color: #6B7A87; font-size: 13px;'>Recibida desde el formulario del sitio web</p>
        </div>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>📌 Datos de la empresa</h3>
        <table style='width:100%; border-collapse:collapse; margin-bottom: 24px;'>
          <tr><td style='padding:8px 0; color:#6B7A87; width:35%;'><strong>Empresa:</strong></td><td style='padding:8px 0;'>$empresa</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>NIT:</strong></td><td style='padding:8px 0;'>" . ($nit ?: '—') . "</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Sector:</strong></td><td style='padding:8px 0;'>" . ($sector ?: '—') . "</td></tr>
        </table>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>👤 Contacto</h3>
        <table style='width:100%; border-collapse:collapse; margin-bottom: 24px;'>
          <tr><td style='padding:8px 0; color:#6B7A87; width:35%;'><strong>Nombre:</strong></td><td style='padding:8px 0;'>$contacto</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Cargo:</strong></td><td style='padding:8px 0;'>" . ($cargo ?: '—') . "</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Correo:</strong></td><td style='padding:8px 0;'><a href='mailto:$email' style='color:#1D5B8B;'>$email</a></td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Teléfono:</strong></td><td style='padding:8px 0;'>$telefono</td></tr>
        </table>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>📋 Necesidad</h3>
        <table style='width:100%; border-collapse:collapse; margin-bottom: 24px;'>
          <tr><td style='padding:8px 0; color:#6B7A87; width:35%;'><strong>Cantidad de personal:</strong></td><td style='padding:8px 0;'>" . ($numero ?: '—') . "</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Perfil requerido:</strong></td><td style='padding:8px 0;'>" . ($perfil ?: '—') . "</td></tr>
        </table>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>💬 Detalle</h3>
        <div style='background: #F6F3EC; padding: 16px; border-left: 3px solid #2FA991; border-radius: 3px; color: #0E2233;'>
          " . ($detalle ? nl2br($detalle) : '<em style=\"color: #6B7A87;\">Sin detalle adicional</em>') . "
        </div>

        <hr style='border: none; border-top: 1px solid #E3DED3; margin: 32px 0 16px;'>
        <p style='color: #6B7A87; font-size: 12px; margin: 0;'>
          Para responder, escriba directamente al cliente: <a href='mailto:$email' style='color:#1D5B8B;'>$email</a>
        </p>
      </div>
    </body>
    </html>";

    // Cabeceras del correo
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $NOMBRE_REMITENTE <$REMITENTE>\r\n";
    $headers .= "Reply-To: $contacto <$email>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    // Enviar correo
    if (mail($destinatario, $asunto, $cuerpo, $headers)) {
        respond(true);
    } else {
        respond(false, 'No se pudo enviar el correo. Intente más tarde.');
    }

}
elseif ($kind === 'hoja-vida') {
    // ─── FORMULARIO DE HOJA DE VIDA (con archivo adjunto) ───
    $destinatario = $CORREO_HOJA_VIDA;

    $nombre       = clean($_POST['nombre']       ?? '');
    $apellido     = clean($_POST['apellido']     ?? '');
    $cedula       = clean($_POST['cedula']       ?? '');
    $edad         = clean($_POST['edad']         ?? '');
    $ciudad       = clean($_POST['ciudad']       ?? '');
    $experiencia  = clean($_POST['experiencia']  ?? '');
    $perfil       = clean($_POST['perfil']       ?? '');
    $nota         = clean($_POST['nota']         ?? '');

    if (empty($nombre) || empty($apellido) || empty($cedula)) {
        respond(false, 'Faltan campos obligatorios.');
    }

    // Validar y procesar archivo adjunto
    if (!isset($_FILES['hoja_vida']) || $_FILES['hoja_vida']['error'] !== UPLOAD_ERR_OK) {
        respond(false, 'No se recibió la hoja de vida. Adjunte un archivo PDF o Word.');
    }

    $archivo = $_FILES['hoja_vida'];
    $tamano_mb = $archivo['size'] / 1024 / 1024;

    if ($tamano_mb > $MAX_FILE_MB) {
        respond(false, "El archivo es muy grande. Máximo {$MAX_FILE_MB} MB.");
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $EXTENSIONES_PERMITIDAS)) {
        respond(false, 'Solo se permiten archivos PDF, DOC o DOCX.');
    }

    $nombre_archivo = preg_replace('/[^A-Za-z0-9._-]/', '_', $archivo['name']);
    $contenido_archivo = file_get_contents($archivo['tmp_name']);
    $contenido_base64  = chunk_split(base64_encode($contenido_archivo));

    $asunto = "Nueva hoja de vida — $nombre $apellido";

    // Crear separador único para el email multipart (con adjunto)
    $boundary = md5(uniqid(time()));

    // Cabeceras
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "From: $NOMBRE_REMITENTE <$REMITENTE>\r\n";
    $headers .= "Reply-To: $nombre $apellido <$email>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Cuerpo HTML del correo
    $cuerpo_html = "
    <html>
    <body style='font-family: Arial, sans-serif; color: #0E2233; line-height: 1.6; max-width: 640px; margin: 0 auto; padding: 20px; background: #FBF9F4;'>
      <div style='background: #FFFFFF; padding: 32px; border-radius: 6px; border: 1px solid #E3DED3;'>
        <div style='border-bottom: 2px solid #2FA991; padding-bottom: 16px; margin-bottom: 24px;'>
          <h2 style='margin: 0; color: #1D5B8B; font-size: 22px;'>Nueva hoja de vida recibida</h2>
          <p style='margin: 6px 0 0 0; color: #6B7A87; font-size: 13px;'>Postulación desde el sitio web</p>
        </div>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>👤 Datos personales</h3>
        <table style='width:100%; border-collapse:collapse; margin-bottom: 24px;'>
          <tr><td style='padding:8px 0; color:#6B7A87; width:35%;'><strong>Nombre completo:</strong></td><td style='padding:8px 0;'>$nombre $apellido</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Cédula:</strong></td><td style='padding:8px 0;'>$cedula</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Edad:</strong></td><td style='padding:8px 0;'>" . ($edad ? "$edad años" : '—') . "</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Ciudad:</strong></td><td style='padding:8px 0;'>" . ($ciudad ?: '—') . "</td></tr>
        </table>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>📞 Contacto</h3>
        <table style='width:100%; border-collapse:collapse; margin-bottom: 24px;'>
          <tr><td style='padding:8px 0; color:#6B7A87; width:35%;'><strong>Correo:</strong></td><td style='padding:8px 0;'><a href='mailto:$email' style='color:#1D5B8B;'>$email</a></td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Teléfono / WhatsApp:</strong></td><td style='padding:8px 0;'>$telefono</td></tr>
        </table>

        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>💼 Perfil profesional</h3>
        <table style='width:100%; border-collapse:collapse; margin-bottom: 24px;'>
          <tr><td style='padding:8px 0; color:#6B7A87; width:35%;'><strong>Experiencia:</strong></td><td style='padding:8px 0;'>" . ($experiencia ?: '—') . "</td></tr>
          <tr><td style='padding:8px 0; color:#6B7A87;'><strong>Cargo de interés:</strong></td><td style='padding:8px 0;'>" . ($perfil ?: '—') . "</td></tr>
        </table>";

    if (!empty($nota)) {
        $cuerpo_html .= "
        <h3 style='color: #1D5B8B; font-size: 15px; margin: 0 0 12px 0;'>📝 Mensaje del candidato</h3>
        <div style='background: #F6F3EC; padding: 16px; border-left: 3px solid #2FA991; border-radius: 3px;'>" . nl2br($nota) . "</div>";
    }

    $cuerpo_html .= "
        <hr style='border: none; border-top: 1px solid #E3DED3; margin: 32px 0 16px;'>
        <p style='color: #6B7A87; font-size: 12px; margin: 0;'>
          📎 La hoja de vida está adjunta a este correo.<br>
          Para responder, escriba al candidato: <a href='mailto:$email' style='color:#1D5B8B;'>$email</a>
        </p>
      </div>
    </body>
    </html>";

    // Construir mensaje multipart con adjunto
    $mensaje  = "--$boundary\r\n";
    $mensaje .= "Content-Type: text/html; charset=UTF-8\r\n";
    $mensaje .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $mensaje .= $cuerpo_html . "\r\n\r\n";
    $mensaje .= "--$boundary\r\n";
    $mensaje .= "Content-Type: application/octet-stream; name=\"$nombre_archivo\"\r\n";
    $mensaje .= "Content-Transfer-Encoding: base64\r\n";
    $mensaje .= "Content-Disposition: attachment; filename=\"$nombre_archivo\"\r\n\r\n";
    $mensaje .= $contenido_base64 . "\r\n";
    $mensaje .= "--$boundary--";

    if (mail($destinatario, $asunto, $mensaje, $headers)) {
        respond(true);
    } else {
        respond(false, 'No se pudo enviar el correo. Intente más tarde.');
    }
}
else {
    respond(false, 'Tipo de formulario desconocido.');
}
