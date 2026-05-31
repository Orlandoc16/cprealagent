<?php // vender.php — Servicio de venta
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';
start_session();
store_utm_in_session();

$pageTitle = 'Vende tu propiedad';
$pageDesc = 'Vende tu piso, casa o local con CP Real Agent. Sin comisiones ocultas, tasación gratuita y trato cercano. Vendemos tu propiedad al mejor precio y de forma rápida.';
$canonicalUrl = SITE_URL . '/vender/';

// Procesar formulario de valoración
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $db = getDB();
        $leadData = [
            'nombre'    => sanitize($_POST['nombre'] ?? ''),
            'email'     => sanitize_email($_POST['email'] ?? ''),
            'telefono'  => sanitize($_POST['telefono'] ?? ''),
            'mensaje'   => sanitize($_POST['mensaje'] ?? ''),
            'fuente'    => 'web_vender',
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

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative py-24 md:py-32">
    <div class="absolute inset-0 bg-cover bg-center"
         style="background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=80');">
        <div class="absolute inset-0 bg-gradient-to-r from-accent-dark/90 to-accent-dark/50"></div>
    </div>
    <div class="relative z-10 max-w-5xl mx-auto px-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-white/80 text-sm mb-6">
            💰 Servicio de venta
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-slab font-bold text-white mb-6 leading-tight">
            Vende tu propiedad<br>
            <span class="text-primary">sin comisiones ocultas</span>
        </h1>
        <p class="text-lg md:text-xl text-white/80 max-w-2xl mb-8 font-light">
            Te acompañamos en todo el proceso: tasación gratuita, marketing profesional, negociación y entrega de llaves.
        </p>
        <a href="#valoracion" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-bold rounded-full text-lg hover:bg-primary-dark transition-all hover:scale-105 shadow-lg">
            Solicitar valoración gratuita
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

<!-- Propuesta de valor -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">¿Por qué elegir CP Real Agent?</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">No solo vendemos propiedades. Vivimos en tu ciudad, conocemos cada calle y cómo se mueve el mercado local.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-3xl mx-auto mb-4">🔍</div>
                <h3 class="text-lg font-bold text-accent mb-2">Tasación gratuita</h3>
                <p class="text-gray-600">Analizamos tu propiedad según ubicación, estado y demanda. Sin compromiso.</p>
            </div>
            <div class="text-center p-6">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-3xl mx-auto mb-4">📸</div>
                <h3 class="text-lg font-bold text-accent mb-2">Marketing profesional</h3>
                <p class="text-gray-600">Fotos profesionales, vídeos, home staging y difusión en todos los portales.</p>
            </div>
            <div class="text-center p-6">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-3xl mx-auto mb-4">🤝</div>
                <h3 class="text-lg font-bold text-accent mb-2">Sin comisiones ocultas</h3>
                <p class="text-gray-600">Trato transparente desde el primer día. Tú decides, nosotros lo hacemos posible.</p>
            </div>
        </div>
    </div>
</section>

<!-- Proceso en pasos -->
<section class="py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">Cómo vendemos tu propiedad</h2>
        </div>
        
        <div class="space-y-8">
            <?php
            $pasos = [
                ['step' => '01', 'icon' => '🏠', 'title' => 'Visita y tasación', 'desc' => 'Visitamos tu propiedad, hacemos fotos profesionales y analizamos el mercado para fijar el mejor precio.'],
                ['step' => '02', 'icon' => '📢', 'title' => 'Lanzamiento', 'desc' => 'Publicamos tu propiedad con fotos premium, tour virtual y anuncios optimizados en todos los portales y redes sociales.'],
                ['step' => '03', 'icon' => '👥', 'title' => 'Visitas y negociación', 'desc' => 'Gestionamos las visitas, filtramos compradores cualificados y negociamos el mejor precio por ti.'],
                ['step' => '04', 'icon' => '🔑', 'title' => 'Cierre y entrega', 'desc' => 'Te asesoramos en documentación, contrato, firma en notaría y entrega de llaves. Tú no te preocupas de nada.'],
            ];
            foreach ($pasos as $i => $paso): ?>
            <div class="flex gap-6 items-start">
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center text-xl font-bold"><?= $paso['icon'] ?></div>
                    <?php if ($i < count($pasos) - 1): ?>
                    <div class="w-0.5 h-12 bg-primary/20 mx-auto mt-2"></div>
                    <?php endif; ?>
                </div>
                <div class="pb-4">
                    <span class="text-xs font-bold text-primary uppercase tracking-widest">Paso <?= $paso['step'] ?></span>
                    <h3 class="text-xl font-bold text-accent-dark mt-1 mb-2"><?= $paso['title'] ?></h3>
                    <p class="text-gray-600"><?= $paso['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Formulario valoración -->
<section id="valoracion" class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">¿Quieres saber cuánto vale tu propiedad?</h2>
            <p class="text-gray-600">Completa el formulario y te contactamos en menos de 24 horas con una valoración.</p>
        </div>
        
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
            ✅ ¡Solicitud enviada! Te contactaremos pronto con tu valoración gratuita.
        </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4 bg-gray-50 rounded-2xl p-8">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nombre" placeholder="Tu nombre *" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none bg-white">
                <input type="email" name="email" placeholder="Tu email *" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none bg-white">
            </div>
            <input type="tel" name="telefono" placeholder="Tu teléfono"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none bg-white">
            <textarea name="mensaje" placeholder="Cuéntanos sobre tu propiedad: ubicación, metros, estado..." rows="4" required
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none bg-white resize-none"></textarea>
            <button type="submit"
                    class="w-full px-8 py-4 bg-primary text-white font-bold rounded-full text-lg hover:bg-primary-dark transition-colors">
                Solicitar valoración gratuita
            </button>
            <p class="text-center text-sm text-gray-400">O contacta directamente por <a href="<?= whatsapp_link(WHATSAPP_NUMBER) ?>" class="text-primary hover:underline font-medium">WhatsApp</a></p>
        </form>
    </div>
</section>

<!-- Testimonios / confianza -->
<section class="py-16 bg-primary">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-slab font-bold text-white mb-8">Nuestros números hablan</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <div class="text-4xl font-bold text-white">500+</div>
                <div class="text-white/70 mt-1">Propiedades vendidas</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-white">98%</div>
                <div class="text-white/70 mt-1">Clientes satisfechos</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-white">30</div>
                <div class="text-white/70 mt-1">Días medio de venta</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-white">24h</div>
                <div class="text-white/70 mt-1">Respuesta garantizada</div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
