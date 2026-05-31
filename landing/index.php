<?php // landing/index.php — Render de landing dinámica para Meta Ads
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
start_session();
store_utm_in_session();

$db = getDB();
$slug = sanitize($_GET['slug'] ?? '');

// Obtener landing
$stmt = $db->prepare("SELECT * FROM landing_pages WHERE slug = ? AND activa = 1");
$stmt->execute([$slug]);
$landing = $stmt->fetch();

if (!$landing) { http_response_code(404); die('Landing no encontrada'); }

// Incrementar visitas
$db->prepare("UPDATE landing_pages SET visitas = visitas + 1 WHERE id = ?")->execute([$landing['id']]);

// Obtener propiedades de la landing
$propIds = array_filter(array_map('intval', explode(',', $landing['propiedad_ids'] ?? '')));
$propiedades = [];

if (!empty($propIds)) {
    $placeholders = implode(',', array_fill(0, count($propIds), '?'));
    $stmt = $db->prepare("SELECT * FROM propiedades WHERE activa = 1 AND id IN ($placeholders) ORDER BY destacada DESC");
    $stmt->execute($propIds);
    $propiedades = $stmt->fetchAll();
}

// Si no hay propiedades seleccionadas, usar filtros
if (empty($propiedades)) {
    $filtros = json_decode($landing['filtros_json'] ?? '{}', true);
    $where = ['p.activa = 1'];
    $params = [];
    if (!empty($filtros['operacion'])) { $where[] = 'p.tipo_operacion = ?'; $params[] = $filtros['operacion']; }
    if (!empty($filtros['ciudad'])) { $where[] = 'p.ciudad = ?'; $params[] = $filtros['ciudad']; }
    $WHERE = implode(' AND ', $where);
    $stmt = $db->prepare("SELECT p.* FROM propiedades p WHERE $WHERE ORDER BY p.destacada DESC, p.created_at DESC LIMIT 12");
    $stmt->execute($params);
    $propiedades = $stmt->fetchAll();
}

// Page meta
$pageTitle = $landing['titulo'];
$pageDesc = $landing['subtitulo'] ?? $landing['titulo'];
$canonicalUrl = SITE_URL . '/lp/' . $landing['slug'] . '/';
$ogImage = SITE_URL . '/assets/img/og-default.jpg';

// Procesar formulario lead
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $leadData = [
            'landing_slug'  => $landing['slug'],
            'nombre'    => sanitize($_POST['nombre'] ?? ''),
            'email'     => sanitize_email($_POST['email'] ?? ''),
            'telefono'  => sanitize($_POST['telefono'] ?? ''),
            'mensaje'   => sanitize($_POST['mensaje'] ?? ''),
            'fuente'    => 'meta',
            'utm_source'   => $_SESSION['utm_source'] ?? null,
            'utm_medium'   => $_SESSION['utm_medium'] ?? null,
            'utm_campaign' => $_SESSION['utm_campaign'] ?? null,
        ];
        if ($leadData['nombre'] && $leadData['email']) {
            save_lead($db, $leadData);
            notify_lead_email($leadData);
            $success = true;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero de la landing -->
<section class="relative py-24 md:py-32 bg-accent text-white" style="background: linear-gradient(135deg, #1A252F 0%, #2C3E50 100%);">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-slab font-bold mb-6 leading-tight">
            <?= e($landing['headline'] ?: $landing['titulo']) ?>
        </h1>
        <?php if ($landing['subtitulo']): ?>
        <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto mb-8 font-light"><?= e($landing['subtitulo']) ?></p>
        <?php endif; ?>
        
        <a href="#contacto" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-bold rounded-full text-lg hover:bg-primary-dark transition-all hover:scale-105 shadow-lg">
            <?= e($landing['cta_text'] ?: 'Contactar ahora') ?>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

<?php if ($landing['contenido']): ?>
<section class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4">
        <div class="prose text-gray-600 text-lg leading-relaxed whitespace-pre-line"><?= e($landing['contenido']) ?></div>
    </div>
</section>
<?php endif; ?>

<!-- Propiedades -->
<?php if (!empty($propiedades)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-slab font-bold text-accent-dark">Propiedades disponibles</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($propiedades as $prop): ?>
                <?php require __DIR__ . '/../includes/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Formulario de contacto -->
<section id="contacto" class="py-20 bg-white">
    <div class="max-w-xl mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-slab font-bold text-accent-dark text-center mb-2">¿Te interesa?</h2>
        <p class="text-gray-600 text-center mb-8">Déjanos tus datos y te contactamos en menos de 24 horas.</p>
        
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-center">
            ✅ ¡Perfecto! Te contactaremos pronto.
        </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nombre" placeholder="Tu nombre *" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                <input type="email" name="email" placeholder="Tu email *" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            </div>
            <input type="tel" name="telefono" placeholder="Tu teléfono"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            <textarea name="mensaje" placeholder="¿Qué propiedad te interesa? Cuéntanos..." rows="3"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none resize-none"></textarea>
            <button type="submit"
                    class="w-full px-8 py-4 bg-primary text-white font-bold rounded-full text-lg hover:bg-primary-dark transition-colors">
                <?= e($landing['cta_text'] ?: 'Contactar ahora') ?>
            </button>
            <p class="text-center text-sm text-gray-400">
                O escribe directamente por <a href="<?= whatsapp_link(WHATSAPP_NUMBER, 'Hola, vi la oferta en cprealagent.com') ?>" target="_blank" class="text-primary font-medium hover:underline">WhatsApp</a>
            </p>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
