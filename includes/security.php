<?php
// ============================================
// CP Real Agent — Funciones de Seguridad
// ============================================

/**
 * Genera token CSRF y lo guarda en sesión
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Genera input hidden con token CSRF
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifica token CSRF enviado por POST
 */
function verify_csrf(): bool {
    $token = $_POST['_csrf'] ?? '';
    return hash_equals(csrf_token(), $token);
}

/**
 * Escape HTML para output seguro
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizar string de input
 */
function sanitize(string $str): string {
    return trim(strip_tags($str));
}

/**
 * Sanitizar email
 */
function sanitize_email(string $email): string {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Generar slug URL-friendly
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    return strtolower($text);
}

/**
 * Iniciar sesión segura
 */
function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        session_start();
    }
}

/**
 * Verificar si usuario está autenticado como admin
 */
function require_auth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /admin/');
        exit;
    }
}

/**
 * Verificar rate limiting de login
 */
function check_login_rate_limit(PDO $db): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $db->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $stmt->execute([$ip, ADMIN_LOCKOUT_MINUTES]);
    $row = $stmt->fetch();
    return ($row['attempts'] ?? 0) >= ADMIN_LOGIN_ATTEMPTS;
}

/**
 * Registrar intento de login fallido
 */
function register_login_attempt(PDO $db): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $db->prepare("INSERT INTO login_attempts (ip, created_at) VALUES (?, NOW())");
    $stmt->execute([$ip]);
}

/**
 * Limpiar intentos antiguos de login
 */
function cleanup_login_attempts(PDO $db): void {
    $db->exec("DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
}

/**
 * Capturar parámetros UTM para tracking
 */
function get_utm_params(): array {
    return [
        'utm_source'   => $_GET['utm_source'] ?? null,
        'utm_medium'   => $_GET['utm_medium'] ?? null,
        'utm_campaign' => $_GET['utm_campaign'] ?? null,
    ];
}

/**
 * Guardar UTM params en sesión para persistir en formularios
 */
function store_utm_in_session(): void {
    $utm = get_utm_params();
    foreach ($utm as $key => $value) {
        if ($value) {
            $_SESSION[$key] = $value;
        }
    }
}

/**
 * Validar tipo MIME real de archivo subido
 */
function validate_upload_type(array $file): bool {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    return in_array($mime, ALLOWED_MIME_TYPES);
}
