<?php // admin/index.php — Login
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
start_session();

// Si ya está logueado, redirigir
if (!empty($_SESSION['user_id'])) {
    header('Location: /admin/dashboard');
    exit;
}

$error = '';

// Crear tabla de login_attempts si no existe
$db = getDB();
try {
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        $error = 'Introduce email y contraseña.';
    } elseif (check_login_rate_limit($db)) {
        $error = 'Demasiados intentos. Espera ' . ADMIN_LOCKOUT_MINUTES . ' minutos.';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Actualizar último login
            $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            
            // Limpiar intentos
            cleanup_login_attempts($db);
            
            header('Location: /admin/dashboard');
            exit;
        } else {
            register_login_attempt($db);
            $error = 'Email o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin — CP Real Agent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: { DEFAULT: '#1BB55B', dark: '#15943F' }, accent: { DEFAULT: '#2C3E50', dark: '#1A252F' } }, fontFamily: { sans: ['Roboto', 'sans-serif'], slab: ['Roboto Slab', 'serif'] } } }
        }
    </script>
</head>
<body class="font-sans bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        <div class="text-center mb-8">
            <span class="text-3xl font-slab font-bold text-accent-dark">CP Real Agent</span>
            <p class="text-gray-500 mt-1">Panel de administración</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <?php if ($error): ?>
            <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                ❌ <?= e($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required autofocus
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                </div>
                <button type="submit"
                        class="w-full py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</body>
</html>
