<?php // propiedades/ver.php — Ficha de propiedad
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
start_session();
store_utm_in_session();

$db = getDB();
$slug = sanitize($_GET['slug'] ?? '');

// Obtener propiedad
$stmt = $db->prepare("SELECT * FROM propiedades WHERE slug = ? AND activa = 1");
$stmt->execute([$slug]);
$prop = $stmt->fetch();

if (!$prop) { http_response_code(404); die('Propiedad no encontrada'); }

// Incrementar visitas
increment_visitas($prop['id'], $db);

// Imágenes
$imagenes = get_imagenes($prop['id'], $db);
$portada = get_portada($prop['id'], $db);

// Propiedades similares
$simStmt = $db->prepare("SELECT * FROM propiedades WHERE activa = 1 AND tipo_operacion = ? AND ciudad = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$simStmt->execute([$prop['tipo_operacion'], $prop['ciudad'], $prop['id']]);
$similares = $simStmt->fetchAll();

// Page meta
$pageTitle = $prop['titulo'];
$pageDesc = mb_substr(strip_tags($prop['descripcion']), 0, 160);
$canonicalUrl = SITE_URL . '/propiedad/' . $prop['slug'] . '/';
$ogImage = $portada ?? SITE_URL . '/assets/img/og-default.jpg';
$ogType = 'article';

$precioText = format_price((float)$prop['precio'], $prop['tipo_operacion']);

// Procesar formulario de contacto
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $leadData = [
            'propiedad_id' => $prop['id'],
            'nombre'    => sanitize($_POST['nombre'] ?? ''),
            'email'     => sanitize_email($_POST['email'] ?? ''),
            'telefono'  => sanitize($_POST['telefono'] ?? ''),
            'mensaje'   => sanitize($_POST['mensaje'] ?? ''),
            'fuente'    => 'web',
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

<!-- Breadcrumbs -->
<nav class="max-w-7xl mx-auto px-4 py-4 text-sm text-gray-500" aria-label="Breadcrumb">
    <a href="/" class="hover:text-primary breadcrumb-sep">Inicio</a>
    <a href="/<?= $prop['tipo_operacion'] ?>/" class="hover:text-primary breadcrumb-sep"><?= tipo_operacion_label($prop['tipo_operacion']) ?></a>
    <a href="/<?= $prop['tipo_operacion'] ?>/<?= slugify($prop['ciudad']) ?>/" class="hover:text-primary breadcrumb-sep"><?= e($prop['ciudad']) ?></a>
    <span class="text-accent font-medium"><?= e($prop['titulo']) ?></span>
</nav>

<!-- Galería principal -->
<section class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Columna izquierda: Galería + detalles -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Galería -->
            <div class="rounded-2xl overflow-hidden">
                <?php if (count($imagenes) > 0): ?>
                <!-- Imagen principal -->
                <div class="relative aspect-[16/10] bg-gray-100">
                    <img id="mainImage" src="<?= UPLOADS_URL . '/' . e($imagenes[0]['imagen_path']) ?>" 
                         alt="<?= e($prop['titulo']) ?>"
                         class="w-full h-full object-cover cursor-pointer" loading="eager">
                    
                    <!-- Contador -->
                    <div class="absolute bottom-4 right-4 px-3 py-1 bg-black/60 text-white text-sm rounded-full">
                        📷 1 / <?= count($imagenes) ?>
                    </div>
                </div>
                
                <!-- Thumbnails -->
                <?php if (count($imagenes) > 1): ?>
                <div class="flex gap-2 p-3 overflow-x-auto">
                    <?php foreach ($imagenes as $i => $img): ?>
                    <button onclick="document.getElementById('mainImage').src='<?= UPLOADS_URL . '/' . e($img['imagen_path']) ?>'"
                            class="flex-shrink-0 w-20 h-16 rounded-lg overflow-hidden border-2 hover:border-primary transition-colors <?= $i === 0 ? 'border-primary' : 'border-transparent' ?>">
                        <img src="<?= UPLOADS_URL . '/' . e($img['imagen_path']) ?>" alt="" 
                             class="w-full h-full object-cover" loading="lazy">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="aspect-[16/10] bg-gray-100 flex items-center justify-center">
                    <span class="text-gray-400">Sin imágenes</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Detalles -->
            <div class="space-y-6">
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="px-3 py-1 text-xs font-bold text-white rounded-full <?= tipo_operacion_color($prop['tipo_operacion']) ?>">
                            <?= tipo_operacion_label($prop['tipo_operacion']) ?>
                        </span>
                        <span class="text-sm text-gray-500"><?= tipo_inmueble_label($prop['tipo_inmueble']) ?></span>
                        <?php if ($prop['certificacion_energetica']): ?>
                        <span class="px-2 py-1 text-xs font-bold text-accent border border-gray-300 rounded">
                            Cert. <?= e($prop['certificacion_energetica']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="text-2xl md:text-3xl font-slab font-bold text-accent-dark"><?= e($prop['titulo']) ?></h1>
                    
                    <div class="flex items-center gap-1 text-gray-500 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <?= e($prop['ciudad']) ?><?php if ($prop['zona_barrio']) echo ' · ' . e($prop['zona_barrio']) ?>
                    </div>
                </div>
                
                <!-- Features grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <?php if ($prop['habitaciones']): ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-2xl font-bold text-accent-dark"><?= (int)$prop['habitaciones'] ?></div>
                        <div class="text-xs text-gray-500">Habitaciones</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($prop['banos']): ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-2xl font-bold text-accent-dark"><?= (int)$prop['banos'] ?></div>
                        <div class="text-xs text-gray-500">Baños</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($prop['superficie']): ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-2xl font-bold text-accent-dark"><?= number_format((float)$prop['superficie'], 0) ?></div>
                        <div class="text-xs text-gray-500">m² totales</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($prop['superficie_util']): ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-2xl font-bold text-accent-dark"><?= number_format((float)$prop['superficie_util'], 0) ?></div>
                        <div class="text-xs text-gray-500">m² útiles</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($prop['planta']): ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-lg font-bold text-accent-dark"><?= e($prop['planta']) ?></div>
                        <div class="text-xs text-gray-500">Planta</div>
                    </div>
                    <?php endif; ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-2xl font-bold text-primary"><?= $precioText ?></div>
                        <div class="text-xs text-gray-500"><?= $prop['tipo_operacion'] === 'alquiler' ? 'Mensual' : 'Precio' ?></div>
                    </div>
                </div>
                
                <!-- Extras -->
                <div>
                    <h3 class="font-bold text-accent mb-3">Características</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $extras = [
                            $prop['ascensor'] => '🛗 Ascensor',
                            $prop['terraza'] => '🌿 Terraza',
                            $prop['garaje'] => '🚗 Garaje',
                            $prop['piscina'] => '🏊 Piscina',
                            $prop['aire_acondicionado'] => '❄️ Aire acondicionado',
                            $prop['amueblado'] => '🛋️ Amueblado',
                        ];
                        foreach ($extras as $active => $label):
                            if ($active): ?>
                        <span class="px-3 py-1.5 bg-green-50 text-green-700 text-sm rounded-full"><?= $label ?></span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                
                <!-- Descripción -->
                <div>
                    <h3 class="font-bold text-accent mb-3">Descripción</h3>
                    <div class="text-gray-600 leading-relaxed whitespace-pre-line"><?= e($prop['descripcion']) ?></div>
                </div>
                
                <!-- Mapa placeholder -->
                <?php if ($prop['lat'] && $prop['lng']): ?>
                <div>
                    <h3 class="font-bold text-accent mb-3">Ubicación</h3>
                    <div class="rounded-xl overflow-hidden bg-gray-100 aspect-video flex items-center justify-center">
                        <span class="text-gray-400 text-sm">Mapa Leaflet.js — Lat: <?= $prop['lat'] ?>, Lng: <?= $prop['lng'] ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Columna derecha: Sidebar sticky -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                
                <!-- Precio card -->
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <div class="text-3xl font-bold text-primary mb-1"><?= $precioText ?></div>
                    <div class="text-sm text-gray-500 mb-6">
                        <?= tipo_inmueble_label($prop['tipo_inmueble']) ?> · <?= e($prop['ciudad']) ?>
                    </div>
                    
                    <?php if ($success): ?>
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                        ✅ ¡Mensaje enviado! Te contactaremos pronto.
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-3">
                        <?= csrf_field() ?>
                        <input type="text" name="nombre" placeholder="Tu nombre" required
                               class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:border-primary outline-none">
                        <input type="email" name="email" placeholder="Tu email" required
                               class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:border-primary outline-none">
                        <input type="tel" name="telefono" placeholder="Tu teléfono"
                               class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:border-primary outline-none">
                        <textarea name="mensaje" placeholder="¿Te interesa esta propiedad? Cuéntanos..." rows="2"
                                  class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:border-primary outline-none resize-none"></textarea>
                        <button type="submit" class="w-full py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors">
                            📧 Contactar
                        </button>
                    </form>
                    
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <a href="tel:<?= SITE_PHONE ?>"
                           class="flex items-center justify-center gap-1.5 py-2.5 text-sm font-medium border border-gray-200 rounded-full hover:bg-gray-100 transition-colors">
                            📞 Llamar
                        </a>
                        <a href="<?= whatsapp_link(WHATSAPP_NUMBER, 'Hola, me interesa: ' . $prop['titulo'] . ' - ' . SITE_URL . '/propiedad/' . $prop['slug'] . '/') ?>"
                           target="_blank"
                           class="flex items-center justify-center gap-1.5 py-2.5 text-sm font-bold text-white bg-primary rounded-full hover:bg-primary-dark transition-colors">
                            💬 WhatsApp
                        </a>
                    </div>
                </div>
                
                <!-- Ref -->
                <div class="text-center text-xs text-gray-400">
                    Ref: CPR-<?= str_pad($prop['id'], 4, '0', STR_PAD_LEFT) ?> · <?= $prop['visitas'] ?> visitas
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Propiedades similares -->
<?php if (count($similares) > 0): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-slab font-bold text-accent-dark mb-8">Propiedades similares</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($similares as $prop): ?>
                <?php require __DIR__ . '/../includes/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Schema.org: RealEstateListing -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "RealEstateListing",
    "name": "<?= e($prop['titulo']) ?>",
    "description": "<?= e(mb_substr($prop['descripcion'], 0, 300)) ?>",
    "url": "<?= $canonicalUrl ?>",
    "datePosted": "<?= $prop['created_at'] ?>",
    "offers": {
        "@type": "Offer",
        "price": "<?= $prop['precio'] ?>",
        "priceCurrency": "EUR"
    },
    <?php if ($prop['lat'] && $prop['lng']): ?>
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": "<?= $prop['lat'] ?>",
        "longitude": "<?= $prop['lng'] ?>"
    },
    <?php endif; ?>
    "address": {
        "@type": "PostalAddress",
        "addressLocality": "<?= e($prop['ciudad']) ?>",
        "addressCountry": "ES"
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
