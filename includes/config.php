<?php
// ============================================
// CP Real Agent — Configuración Principal
// EDITA ESTOS VALORES CON TUS CREDENCIALES
// ============================================

// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_de_datos');    // ← Cambiar
define('DB_USER', 'tu_usuario');          // ← Cambiar
define('DB_PASS', 'tu_contraseña');       // ← Cambiar
define('DB_CHARSET', 'utf8mb4');

// Sitio
define('SITE_URL', 'https://www.cprealagent.com');
define('SITE_NAME', 'CP Real Agent');
define('SITE_TAGLINE', 'Tu agencia inmobiliaria de confianza');
define('SITE_PHONE', '+34 900 000 000');  // ← Cambiar
define('SITE_EMAIL', 'info@cprealagent.com');
define('WHATSAPP_NUMBER', '34900000000');  // ← Sin +, con prefijo país
define('SITE_ADDRESS', 'Tu dirección, Ciudad, España');

// Rutas
define('BASE_PATH', __DIR__ . '/..');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('UPLOADS_URL', SITE_URL . '/uploads');

// Sesión
define('SESSION_LIFETIME', 3600); // 1 hora
define('ADMIN_LOGIN_ATTEMPTS', 5);
define('ADMIN_LOCKOUT_MINUTES', 15);

// Subida de imágenes
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_IMAGE_WIDTH', 1920);
define('MAX_PROPERTY_IMAGES', 15);

// Meta / Tracking
define('GA_ID', '');          // Google Analytics ID (ej: G-XXXXXXXXXX)
define('META_PIXEL_ID', '');  // Meta Pixel ID (ej: 123456789)
define('TRUSTPILOT_ID', '');  // Trustpilot widget ID

// Pagination
define('PROPERTIES_PER_PAGE', 12);

// Conexión PDO (singleton)
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}
