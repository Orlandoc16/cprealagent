<?php // admin/propiedades/listar.php — Listado CRUD
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';
start_session();
require_auth();

$db = getDB();

// Acción: eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verify_csrf()) {
        $id = (int)$_POST['id'];
        // Eliminar imágenes del disco
        $imgs = get_imagenes($id, $db);
        foreach ($imgs as $img) {
            $path = UPLOADS_PATH . '/' . $img['imagen_path'];
            if (file_exists($path)) unlink($path);
        }
        $db->prepare("DELETE FROM propiedades WHERE id = ?")->execute([$id]);
        header('Location: /admin/propiedades/listar?deleted=1');
        exit;
    }
}

// Búsqueda
$search = sanitize($_GET['q'] ?? '');
$filterOp = sanitize($_GET['operacion'] ?? '');

$where = ['1=1'];
$params = [];
if ($search) { $where[] = "(titulo LIKE ? OR ciudad LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($filterOp) { $where[] = "tipo_operacion = ?"; $params[] = $filterOp; }

$WHERE = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM propiedades ORDER BY destacada DESC, updated_at DESC");
$stmt->execute($params);
$propiedades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Propiedades — CP Real Agent Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#1BB55B', dark: '#15943F' }, accent: { DEFAULT: '#2C3E50', dark: '#1A252F' } }, fontFamily: { sans: ['Roboto','sans-serif'], slab: ['Roboto Slab','serif'] } } } }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/admin/dashboard" class="text-xl font-slab font-bold text-accent-dark">CP Real Agent</a>
                <span class="text-sm text-gray-400">/ Propiedades</span>
            </div>
            <a href="/admin/propiedades/crear" class="px-4 py-2 bg-primary text-white font-bold rounded-full text-sm hover:bg-primary-dark transition-colors">
                + Nueva propiedad
            </a>
        </div>
    </header>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if (isset($_GET['deleted'])): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">✅ Propiedad eliminada.</div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                <div>
                    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Buscar por título o ciudad..."
                           class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm w-64">
                </div>
                <select name="operacion" class="px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
                    <option value="">Todas las operaciones</option>
                    <option value="venta" <?= $filterOp === 'venta' ? 'selected' : '' ?>>Venta</option>
                    <option value="alquiler" <?= $filterOp === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-accent text-white text-sm rounded-xl">🔍 Buscar</button>
            </form>
        </div>
        
        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Propiedad</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Operación</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Precio</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Estado</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($propiedades as $p): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="font-medium text-accent truncate max-w-xs"><?= e($p['titulo']) ?></div>
                                <div class="text-xs text-gray-500"><?= e($p['ciudad']) ?><?php if ($p['zona_barrio']) echo ' · ' . e($p['zona_barrio']) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-bold text-white rounded-full <?= tipo_operacion_color($p['tipo_operacion']) ?>">
                                    <?= tipo_operacion_label($p['tipo_operacion']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-primary"><?= format_price((float)$p['precio'], $p['tipo_operacion']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($p['destacada']): ?><span class="text-xs text-amber-500">⭐</span> <?php endif; ?>
                                <span class="text-xs <?= $p['activa'] ? 'text-green-600' : 'text-red-500' ?>">
                                    <?= $p['activa'] ? '✅ Activa' : '🔴 Inactiva' ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="/propiedad/<?= e($p['slug']) ?>/" target="_blank" class="text-blue-500 hover:underline mr-2" title="Ver en web">🌐</a>
                                <a href="/admin/propiedades/crear?id=<?= $p['id'] ?>" class="text-primary hover:underline mr-2" title="Editar">✏️</a>
                                <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta propiedad?')">
                                    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:underline" title="Eliminar">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($propiedades)): ?>
            <div class="px-6 py-12 text-center text-gray-400">No hay propiedades</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
