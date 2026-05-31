<?php // propiedades/index.php — Listado con filtros
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
start_session();
store_utm_in_session();

$db = getDB();
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * PROPERTIES_PER_PAGE;

// Filtros
$operacion = sanitize($_GET['operacion'] ?? '');
$ciudadSlug = sanitize($_GET['ciudad'] ?? '');
$tipo = sanitize($_GET['tipo'] ?? '');
$minPrecio = (float)($_GET['min'] ?? 0);
$maxPrecio = (float)($_GET['max'] ?? 0);
$habitaciones = (int)($_GET['habitaciones'] ?? 0);

// Build query
$where = ['p.activa = 1'];
$params = [];

if ($operacion) { $where[] = 'p.tipo_operacion = ?'; $params[] = $operacion; }
if ($ciudadSlug) { $where[] = 'p.ciudad IN (SELECT nombre FROM ciudades WHERE slug = ?)'; $params[] = $ciudadSlug; }
if ($tipo) { $where[] = 'p.tipo_inmueble = ?'; $params[] = $tipo; }
if ($minPrecio > 0) { $where[] = 'p.precio >= ?'; $params[] = $minPrecio; }
if ($maxPrecio > 0) { $where[] = 'p.precio <= ?'; $params[] = $maxPrecio; }
if ($habitaciones > 0) { $where[] = 'p.habitaciones >= ?'; $params[] = $habitaciones; }

$WHERE = implode(' AND ', $where);

// Total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM propiedades p WHERE $WHERE");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Fetch properties
$stmt = $db->prepare("SELECT p.* FROM propiedades p WHERE $WHERE ORDER BY p.destacada DESC, p.created_at DESC LIMIT " . PROPERTIES_PER_PAGE . " OFFSET $offset");
$stmt->execute($params);
$propiedades = $stmt->fetchAll();

// Page meta
$pageTitle = 'Propiedades en España';
if ($operacion) $pageTitle = ucfirst(tipo_operacion_label($operacion)) . ' de propiedades';
if ($ciudadSlug) {
    $ciudadObj = $db->prepare("SELECT nombre FROM ciudades WHERE slug = ?");
    $ciudadObj->execute([$ciudadSlug]);
    $ciudadName = $ciudadObj->fetchColumn();
    if ($ciudadName) $pageTitle .= " en $ciudadName";
}
$pageDesc = "Explora " . $total . " propiedades disponibles en España. " . ($operacion ? ucfirst(tipo_operacion_label($operacion)) . '.' : 'Compra, venta y alquiler.');
$canonicalUrl = SITE_URL . '/propiedades/';

// Get ciudades for filter dropdown
$ciudades = get_ciudades($db);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero -->
<section class="py-16 bg-accent text-white" style="background: linear-gradient(135deg, #1A252F 0%, #2C3E50 100%);">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-slab font-bold mb-2"><?= e($pageTitle) ?></h1>
        <p class="text-white/70"><?= $total ?> propiedades encontradas</p>
        
        <!-- Filtros rápidos -->
        <div class="flex flex-wrap gap-2 mt-6">
            <?php
            $quickOps = [['' => 'Todas'], ['venta' => 'Venta'], ['alquiler' => 'Alquiler']];
            foreach ($quickOps as $qop):
                foreach ($qop as $val => $label): 
                    $active = ($operacion === $val) ? 'bg-primary text-white' : 'bg-white/10 text-white/80 hover:bg-white/20';
            ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['operacion' => $val, 'page' => 1])) ?>"
               class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $active ?>"><?= $label ?></a>
            <?php endforeach; endforeach; ?>
        </div>
    </div>
</section>

<section class="py-8 bg-gray-50 border-b">
    <div class="max-w-6xl mx-auto px-4">
        <form class="flex flex-wrap items-end gap-3" method="GET">
            <input type="hidden" name="operacion" value="<?= e($operacion) ?>">
            <input type="hidden" name="page" value="1">
            
            <div>
                <label class="block text-xs text-gray-500 mb-1">Ciudad</label>
                <select name="ciudad" class="px-3 py-2 text-sm border rounded-xl bg-white focus:border-primary outline-none">
                    <option value="">Todas</option>
                    <?php foreach ($ciudades as $c): ?>
                    <option value="<?= e($c['slug']) ?>" <?= $ciudadSlug === $c['slug'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                <select name="tipo" class="px-3 py-2 text-sm border rounded-xl bg-white focus:border-primary outline-none">
                    <option value="">Todos</option>
                    <?php foreach (['piso','casa','chalet','atico','duplex','estudio','local','oficina','terreno','garaje'] as $t): ?>
                    <option value="<?= $t ?>" <?= $tipo === $t ? 'selected' : '' ?>><?= tipo_inmueble_label($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs text-gray-500 mb-1">Precio mín.</label>
                <input type="number" name="min" placeholder="€ mín" value="<?= $minPrecio ?: '' ?>"
                       class="w-28 px-3 py-2 text-sm border rounded-xl bg-white focus:border-primary outline-none">
            </div>
            
            <div>
                <label class="block text-xs text-gray-500 mb-1">Precio máx.</label>
                <input type="number" name="max" placeholder="€ máx" value="<?= $maxPrecio ?: '' ?>"
                       class="w-28 px-3 py-2 text-sm border rounded-xl bg-white focus:border-primary outline-none">
            </div>
            
            <div>
                <label class="block text-xs text-gray-500 mb-1">Habitaciones</label>
                <select name="habitaciones" class="px-3 py-2 text-sm border rounded-xl bg-white focus:border-primary outline-none">
                    <option value="0" <?= $habitaciones === 0 ? 'selected' : '' ?>>Indiferente</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= $habitaciones === $i ? 'selected' : '' ?>><?= $i ?>+</option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-dark transition-colors">
                🔍 Filtrar
            </button>
            
            <a href="/propiedades/" class="px-5 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
        </form>
    </div>
</section>

<section class="py-10 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <?php if (count($propiedades) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($propiedades as $prop): ?>
                <?php require __DIR__ . '/../includes/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Paginación -->
        <?= render_pagination($page, $total, PROPERTIES_PER_PAGE, '/propiedades/?' . http_build_query(array_diff_key($_GET, ['page' => '']))) ?>
        
        <?php else: ?>
        <div class="text-center py-20">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h3 class="text-xl font-bold text-gray-400 mb-2">No se encontraron propiedades</h3>
            <p class="text-gray-500">Prueba a cambiar los filtros de búsqueda.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
