<?php // comprar.php — Servicio de compra
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';
start_session();
store_utm_in_session();

$db = getDB();
$pageTitle = 'Compra tu propiedad ideal';
$pageDesc = 'Encuentra tu hogar ideal con CP Real Agent. Pisos, casas, chalets y más en las mejores ciudades de España. Te ayudamos en todo el proceso de compra.';
$canonicalUrl = SITE_URL . '/comprar/';

// Propiedades destacadas en venta
$stmt = $db->query("SELECT * FROM propiedades WHERE activa = 1 AND tipo_operacion = 'venta' AND destacada = 1 ORDER BY created_at DESC LIMIT 6");
$destacadas = $stmt->fetchAll();
$ciudades = get_ciudades($db);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative py-24 md:py-32">
    <div class="absolute inset-0 bg-cover bg-center"
         style="background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1920&q=80');">
        <div class="absolute inset-0 bg-gradient-to-r from-accent-dark/90 to-accent-dark/40"></div>
    </div>
    <div class="relative z-10 max-w-5xl mx-auto px-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-white/80 text-sm mb-6">
            🏠 Encuentra tu hogar
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-slab font-bold text-white mb-6 leading-tight">
            Tu próximo hogar<br>
            <span class="text-primary">te está esperando</span>
        </h1>
        <p class="text-lg md:text-xl text-white/80 max-w-2xl mb-8 font-light">
            Explora cientos de propiedades en las mejores ciudades de España. Te acompañamos desde la primera visita hasta las llaves.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="/venta/" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-bold rounded-full text-lg hover:bg-primary-dark transition-all hover:scale-105 shadow-lg">
                Ver todas las propiedades
            </a>
            <a href="<?= whatsapp_link(WHATSAPP_NUMBER, 'Hola, busco una propiedad para comprar') ?>" target="_blank"
               class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-full text-lg hover:bg-white/20 transition-all border border-white/30">
                💬 Hablar con un agente
            </a>
        </div>
    </div>
</section>

<!-- Búsqueda por ciudad -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">¿Dónde quieres vivir?</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($ciudades as $ciudad): ?>
            <a href="/venta/<?= e($ciudad['slug']) ?>/" class="group relative overflow-hidden rounded-xl aspect-square">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                     style="background-image: url('https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=400&q=60');">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/20 group-hover:from-primary/70 transition-colors duration-300"></div>
                </div>
                <div class="absolute inset-0 flex items-end p-4">
                    <div>
                        <span class="text-white font-bold text-sm"><?= e($ciudad['nombre']) ?></span>
                        <span class="block text-white/60 text-xs">Ver propiedades →</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Propiedades destacadas en venta -->
<?php if (count($destacadas) > 0): ?>
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">Oportunidades destacadas</h2>
            <p class="text-gray-600">Propiedades seleccionadas especialmente para ti.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($destacadas as $prop): ?>
                <?php require __DIR__ . '/includes/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-12">
            <a href="/venta/" class="inline-flex items-center gap-2 px-8 py-3 border-2 border-accent text-accent font-bold rounded-full hover:bg-accent hover:text-white transition-all">
                Ver todas las propiedades en venta →
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-20 bg-primary">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-slab font-bold text-white mb-4">¿No encuentras lo que buscas?</h2>
        <p class="text-white/80 text-lg mb-8">Cuéntanos qué necesitas y te buscamos la propiedad perfecta.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/contacto/" class="px-8 py-3 bg-white text-primary font-bold rounded-full hover:bg-gray-100 transition-colors">
                Contactar
            </a>
            <a href="<?= whatsapp_link(WHATSAPP_NUMBER, 'Hola, busco una propiedad para comprar') ?>" target="_blank"
               class="px-8 py-3 bg-white/20 text-white font-bold rounded-full hover:bg-white/30 transition-colors">
                💬 WhatsApp
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
