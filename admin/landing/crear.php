<?php // admin/landing/crear.php — Crear landing page para Meta Ads
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';
start_session();
require_auth();

$db = getDB();

// Listado de landing pages existentes
$landings = $db->query("SELECT l.*, COUNT(ld.id) as lead_count FROM landing_pages l LEFT JOIN leads ld ON ld.landing_slug = l.slug GROUP BY l.id ORDER BY l.created_at DESC")->fetchAll();

// Propiedades activas para seleccionar
$propiedades = $db->query("SELECT id, titulo, tipo_operacion, precio, ciudad FROM propiedades WHERE activa = 1 ORDER BY destacada DESC, created_at DESC")->fetchAll();
$ciudades = get_ciudades($db);

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!verify_csrf()) { $error = 'Error de seguridad.'; }
    else {
        $slug = slugify(sanitize($_POST['slug'] ?? ''));
        if (!$slug) { $error = 'El slug es obligatorio.'; }
        else {
            $check = $db->prepare("SELECT id FROM landing_pages WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) { $error = 'Ya existe una landing con ese slug.'; }
            else {
                $stmt = $db->prepare("
                    INSERT INTO landing_pages (slug, titulo, subtitulo, headline, contenido, cta_text, cta_url, propiedad_ids, filtros_json, activa)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $slug,
                    sanitize($_POST['titulo'] ?? ''),
                    sanitize($_POST['subtitulo'] ?? ''),
                    sanitize($_POST['headline'] ?? ''),
                    $_POST['contenido'] ?? '',
                    sanitize($_POST['cta_text'] ?? 'Contactar ahora'),
                    sanitize($_POST['cta_url'] ?? '/contacto/'),
                    implode(',', array_map('intval', $_POST['propiedad_ids'] ?? [])),
                    json_encode([
                        'operacion' => sanitize($_POST['filtro_operacion'] ?? ''),
                        'ciudad'    => sanitize($_POST['filtro_ciudad'] ?? ''),
                    ]),
                    1,
                ]);
                $success = true;
                $landings = $db->query("SELECT * FROM landing_pages ORDER BY created_at DESC")->fetchAll();
            }
        }
    }
}

// Eliminar landing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verify_csrf()) {
        $db->prepare("DELETE FROM landing_pages WHERE id = ?")->execute([(int)$_POST['id']]);
        header('Location: /admin/landing/crear');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Landing Pages — CP Real Agent Admin</title>
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
                <span class="text-sm text-gray-400">/ Landing Pages</span>
            </div>
            <a href="/admin/dashboard" class="text-sm text-gray-500 hover:text-primary">← Dashboard</a>
        </div>
    </header>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Listado de landings existentes -->
            <div>
                <h2 class="text-lg font-bold text-accent-dark mb-4">Landing Pages creadas</h2>
                
                <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">✅ Landing creada.</div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">❌ <?= e($error) ?></div>
                <?php endif; ?>
                
                <?php if (empty($landings)): ?>
                <div class="bg-white rounded-xl p-8 text-center text-gray-400 border">No hay landing pages creadas todavía</div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($landings as $lp): ?>
                    <div class="bg-white rounded-xl p-4 border flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-medium text-accent truncate"><?= e($lp['titulo']) ?></div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 mt-1">
                                <a href="/lp/<?= e($lp['slug']) ?>/" target="_blank" class="text-primary hover:underline">/lp/<?= e($lp['slug']) ?>/</a>
                                <span>· <?= $lp['visitas'] ?> visitas</span>
                                <span>· <?= $lp['lead_count'] ?? 0 ?> leads</span>
                                <span class="<?= $lp['activa'] ? 'text-green-500' : 'text-red-500' ?>"><?= $lp['activa'] ? '✅' : '🔴' ?></span>
                            </div>
                        </div>
                        <form method="POST" class="flex-shrink-0" onsubmit="return confirm('¿Eliminar esta landing?')">
                            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $lp['id'] ?>">
                            <button type="submit" class="text-red-500 hover:underline text-sm">🗑️</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulario nueva landing -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-lg font-bold text-accent-dark mb-6">Crear nueva landing</h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL) *</label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-400">cprealagent.com/lp/</span>
                            <input type="text" name="slug" required placeholder="oferta-madrid"
                                   class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm"
                                   pattern="[a-z0-9-]+" title="Solo minúsculas, números y guiones">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                        <input type="text" name="titulo" required placeholder="¡Oferta exclusiva en Madrid!"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo</label>
                        <input type="text" name="subtitulo" placeholder="Solo esta semana, 20% de descuento en comisiones"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Headline principal</label>
                        <input type="text" name="headline" placeholder="Tu hogar ideal te espera"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Texto del contenido</label>
                        <textarea name="contenido" rows="4" placeholder="Describe la oferta o campaña..."
                                  class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm resize-none"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Texto del CTA</label>
                            <input type="text" name="cta_text" value="Contactar ahora"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL del CTA</label>
                            <input type="text" name="cta_url" value="/contacto/"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Propiedades a mostrar</label>
                        <div class="max-h-48 overflow-y-auto border rounded-xl p-3 space-y-2">
                            <?php if (empty($propiedades)): ?>
                            <p class="text-gray-400 text-sm">No hay propiedades activas</p>
                            <?php else: foreach ($propiedades as $p): ?>
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 p-1 rounded">
                                <input type="checkbox" name="propiedad_ids[]" value="<?= $p['id'] ?>"
                                       class="w-3.5 h-3.5 text-primary rounded">
                                <span class="text-gray-700"><?= e($p['titulo']) ?></span>
                                <span class="text-gray-400 ml-auto"><?= format_price((float)$p['precio'], $p['tipo_operacion']) ?></span>
                            </label>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Filtro operación</label>
                            <select name="filtro_operacion" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
                                <option value="">Todas</option>
                                <option value="venta">Venta</option>
                                <option value="alquiler">Alquiler</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Filtro ciudad</label>
                            <select name="filtro_ciudad" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
                                <option value="">Todas</option>
                                <?php foreach ($ciudades as $c): ?>
                                <option value="<?= e($c['nombre']) ?>"><?= e($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors">
                        Crear landing page
                    </button>
                    
                    <p class="text-xs text-gray-400 text-center">
                        URL generada: <strong>cprealagent.com/lp/{slug}/</strong><br>
                        Los leads desde esta landing incluirán los UTM params de Meta automáticamente.
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
