<?php // admin/dashboard.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
start_session();
require_auth();

$db = getDB();

// Stats
$totalProps = (int)$db->query("SELECT COUNT(*) FROM propiedades")->fetchColumn();
$activeProps = (int)$db->query("SELECT COUNT(*) FROM propiedades WHERE activa = 1")->fetchColumn();
$leadsMonth = (int)$db->query("SELECT COUNT(*) FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$totalLeads = (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn();

// Últimos leads
$recentLeads = $db->query("SELECT l.*, p.titulo as prop_titulo FROM leads l LEFT JOIN propiedades p ON l.propiedad_id = p.id ORDER BY l.created_at DESC LIMIT 10")->fetchAll();

// Últimas propiedades
$recentProps = $db->query("SELECT * FROM propiedades ORDER BY updated_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard — CP Real Agent Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#1BB55B', dark: '#15943F' }, accent: { DEFAULT: '#2C3E50', dark: '#1A252F' } }, fontFamily: { sans: ['Roboto','sans-serif'], slab: ['Roboto Slab','serif'] } } } }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    
    <!-- Top bar -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/admin/dashboard" class="text-xl font-slab font-bold text-accent-dark">CP Real Agent</a>
                <span class="text-sm text-gray-400">Admin</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/" target="_blank" class="text-sm text-gray-500 hover:text-primary">🌐 Ver web</a>
                <span class="text-sm text-gray-500">👤 <?= e($_SESSION['user_name'] ?? 'Admin') ?></span>
                <a href="/admin/logout" class="px-3 py-1.5 text-sm bg-red-50 text-red-600 rounded-full hover:bg-red-100 transition-colors">Cerrar sesión</a>
            </div>
        </div>
    </header>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Stats cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="text-sm text-gray-500 mb-1">Total propiedades</div>
                <div class="text-3xl font-bold text-accent-dark"><?= $totalProps ?></div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="text-sm text-gray-500 mb-1">Activas</div>
                <div class="text-3xl font-bold text-primary"><?= $activeProps ?></div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="text-sm text-gray-500 mb-1">Leads este mes</div>
                <div class="text-3xl font-bold text-amber-500"><?= $leadsMonth ?></div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="text-sm text-gray-500 mb-1">Total leads</div>
                <div class="text-3xl font-bold text-blue-500"><?= $totalLeads ?></div>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="flex flex-wrap gap-3 mb-8">
            <a href="/admin/propiedades/crear" class="px-5 py-2.5 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors text-sm">
                + Añadir propiedad
            </a>
            <a href="/admin/propiedades/listar" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-full hover:bg-gray-50 transition-colors text-sm">
                Ver propiedades
            </a>
            <a href="/admin/leads/listar" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-full hover:bg-gray-50 transition-colors text-sm">
                Ver leads (<?= $totalLeads ?>)
            </a>
            <a href="/admin/landing/crear" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-full hover:bg-gray-50 transition-colors text-sm">
                + Crear landing
            </a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Últimos leads -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="font-bold text-accent-dark">Últimos leads</h2>
                    <a href="/admin/leads/listar" class="text-sm text-primary hover:underline">Ver todos</a>
                </div>
                <div class="divide-y">
                    <?php if (count($recentLeads) > 0): ?>
                    <?php foreach ($recentLeads as $lead): ?>
                    <div class="px-6 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-medium text-accent text-sm truncate"><?= e($lead['nombre']) ?></div>
                            <div class="text-xs text-gray-500 truncate"><?= e($lead['email']) ?><?php if ($lead['prop_titulo']) echo ' · ' . e($lead['prop_titulo']) ?></div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xs text-gray-400"><?= date('d/m H:i', strtotime($lead['created_at'])) ?></div>
                            <div class="text-xs text-gray-400"><?= e($lead['fuente']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">No hay leads todavía</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Últimas propiedades -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="font-bold text-accent-dark">Últimas propiedades</h2>
                    <a href="/admin/propiedades/listar" class="text-sm text-primary hover:underline">Ver todas</a>
                </div>
                <div class="divide-y">
                    <?php foreach ($recentProps as $prop): ?>
                    <div class="px-6 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-medium text-accent text-sm truncate"><?= e($prop['titulo']) ?></div>
                            <div class="text-xs text-gray-500"><?= e($prop['ciudad']) ?> · <?= tipo_operacion_label($prop['tipo_operacion']) ?></div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-bold text-primary"><?= format_price((float)$prop['precio'], $prop['tipo_operacion']) ?></div>
                            <span class="text-xs <?= $prop['activa'] ? 'text-green-500' : 'text-red-500' ?>">
                                <?= $prop['activa'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
