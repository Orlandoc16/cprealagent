<?php // alquiler/index.php — Propiedades en alquiler
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
start_session();

$db = getDB();
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * PROPERTIES_PER_PAGE;
$ciudadSlug = sanitize($_GET['ciudad'] ?? '');

$where = ["p.activa = 1", "p.tipo_operacion = 'alquiler'"];
$params = [];

if ($ciudadSlug) {
    $where[] = "p.ciudad IN (SELECT nombre FROM ciudades WHERE slug = ?)";
    $params[] = $ciudadSlug;
}

$WHERE = implode(' AND ', $where);
$countStmt = $db->prepare("SELECT COUNT(*) FROM propiedades p WHERE $WHERE");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare("SELECT p.* FROM propiedades p WHERE $WHERE ORDER BY p.destacada DESC, p.created_at DESC LIMIT " . PROPERTIES_PER_PAGE . " OFFSET $offset");
$stmt->execute($params);
$propiedades = $stmt->fetchAll();

$ciudadName = '';
if ($ciudadSlug) {
    $c = $db->prepare("SELECT nombre FROM ciudades WHERE slug = ?");
    $c->execute([$ciudadSlug]);
    $ciudadName = $c->fetchColumn();
}

$pageTitle = $ciudadName ? "Alquiler de propiedades en $ciudadName" : 'Propiedades en alquiler';
$pageDesc = "Explora " . $total . " propiedades en alquiler" . ($ciudadName ? " en $ciudadName" : '') . ". Encuentra tu próximo hogar con CP Real Agent.";
$canonicalUrl = SITE_URL . '/alquiler/' . ($ciudadSlug ? $ciudadSlug . '/' : '');

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero -->
<section class="py-20 bg-accent text-white" style="background: linear-gradient(135deg, #1A252F 0%, #2C3E50 100%);">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-300 text-sm font-medium rounded-full mb-4">🏠 Alquiler</span>
        <h1 class="text-4xl md:text-5xl font-slab font-bold mb-4"><?= e($pageTitle) ?></h1>
        <p class="text-xl text-white/70"><?= $total ?> propiedades disponibles</p>
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
        <?= render_pagination($page, $total, PROPERTIES_PER_PAGE, '/alquiler/' . ($ciudadSlug ? $ciudadSlug . '/' : '')) ?>
        <?php else: ?>
        <div class="text-center py-20">
            <h3 class="text-xl font-bold text-gray-400 mb-2">No hay propiedades en alquiler<?= $ciudadName ? " en $ciudadName" : '' ?> por el momento</h3>
            <a href="/contacto/" class="text-primary font-medium hover:underline">Contactanos y te ayudamos a buscar</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
