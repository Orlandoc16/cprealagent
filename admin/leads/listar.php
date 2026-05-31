<?php // admin/leads/listar.php — Gestión de leads
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';
start_session();
require_auth();

$db = getDB();

// Filtros
$filterFuente = sanitize($_GET['fuente'] ?? '');
$where = ['1=1'];
$params = [];
if ($filterFuente) { $where[] = "fuente = ?"; $params[] = $filterFuente; }

$WHERE = implode(' AND ', $where);
$stmt = $db->prepare("SELECT l.*, p.titulo as prop_titulo, p.tipo_operacion FROM leads l LEFT JOIN propiedades p ON l.propiedad_id = p.id WHERE $WHERE ORDER BY l.created_at DESC");
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Estadísticas
$stats = [
    'total' => (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
    'mes'   => (int)$db->query("SELECT COUNT(*) FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'semana'=> (int)$db->query("SELECT COUNT(*) FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
    'hoy'   => (int)$db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Leads — CP Real Agent Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:{DEFAULT:'#1BB55B',dark:'#15943F'},accent:{DEFAULT:'#2C3E50',dark:'#1A252F'}},fontFamily:{sans:['Roboto','sans-serif'],slab:['Roboto Slab','serif']}}}}</script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/admin/dashboard" class="text-xl font-slab font-bold text-accent-dark">CP Real Agent</a>
                <span class="text-sm text-gray-400">/ Leads</span>
            </div>
            <a href="/admin/dashboard" class="text-sm text-gray-500 hover:text-primary">← Dashboard</a>
        </div>
    </header>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-sm border">
                <div class="text-sm text-gray-500">Hoy</div>
                <div class="text-2xl font-bold text-primary"><?= $stats['hoy'] ?></div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border">
                <div class="text-sm text-gray-500">Esta semana</div>
                <div class="text-2xl font-bold text-blue-500"><?= $stats['semana'] ?></div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border">
                <div class="text-sm text-gray-500">Este mes</div>
                <div class="text-2xl font-bold text-amber-500"><?= $stats['mes'] ?></div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border">
                <div class="text-sm text-gray-500">Total</div>
                <div class="text-2xl font-bold text-accent-dark"><?= $stats['total'] ?></div>
            </div>
        </div>
        
        <!-- Filtro -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <span class="text-sm text-gray-500">Filtrar por fuente:</span>
            <a href="/admin/leads/listar" class="px-3 py-1.5 text-sm rounded-full <?= !$filterFuente ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?> transition-colors">Todas</a>
            <a href="?fuente=web" class="px-3 py-1.5 text-sm rounded-full <?= $filterFuente === 'web' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Web</a>
            <a href="?fuente=web_vender" class="px-3 py-1.5 text-sm rounded-full <?= $filterFuente === 'web_vender' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Vender</a>
            <a href="?fuente=meta" class="px-3 py-1.5 text-sm rounded-full <?= $filterFuente === 'meta' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">Meta Ads</a>
            <a href="?fuente=whatsapp" class="px-3 py-1.5 text-sm rounded-full <?= $filterFuente === 'whatsapp' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">WhatsApp</a>
        </div>
        
        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Contacto</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Propiedad</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Fuente</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">UTM</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-600">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($leads as $lead): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="font-medium text-accent"><?= e($lead['nombre']) ?></div>
                                <div class="text-xs text-gray-500"><?= e($lead['email']) ?><?php if ($lead['telefono']) echo ' · ' . e($lead['telefono']) ?></div>
                                <?php if ($lead['mensaje']): ?>
                                <div class="text-xs text-gray-400 mt-1 truncate max-w-xs"><?= e($lead['mensaje']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <?php if ($lead['prop_titulo']): ?>
                                <a href="/propiedad/<?= e($lead['prop_titulo'] ?? '') ?>/" target="_blank" class="hover:text-primary truncate block max-w-[200px]"><?= e($lead['prop_titulo']) ?></a>
                                <?php else: ?>
                                <span class="text-gray-400">General</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600"><?= e($lead['fuente']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                <?php if ($lead['utm_campaign']): ?>
                                <div><?= e($lead['utm_campaign']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-right text-xs text-gray-500 whitespace-nowrap">
                                <?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($leads)): ?>
            <div class="px-6 py-12 text-center text-gray-400">No hay leads</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
