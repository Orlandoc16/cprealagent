<?php // index.php — Home Principal
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';
start_session();
store_utm_in_session();

$db = getDB();

// Metadatos de página
$pageTitle = 'CP Real Agent';
$pageTagline = 'Tu agencia inmobiliaria de confianza';
$pageDesc = 'Encuentra tu hogar ideal en España. Compra, vende o alquila propiedades con CP Real Agent. Sin comisiones ocultas, trato cercano y profesional.';
$canonicalUrl = SITE_URL;
$ogImage = SITE_URL . '/assets/img/og-default.jpg';
$ogType = 'website';

// Obtener propiedades destacadas
$stmt = $db->query("SELECT * FROM propiedades WHERE activa = 1 AND destacada = 1 ORDER BY created_at DESC LIMIT 6");
$propiedadesDestacadas = $stmt->fetchAll();

// Obtener ciudades
$ciudades = get_ciudades($db);

// Contar total propiedades
$total = $db->query("SELECT COUNT(*) FROM propiedades WHERE activa = 1")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="relative min-h-[85vh] flex items-center justify-center">
    <!-- Fondo -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
         style="background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1920&q=80');">
        <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-black/50 to-transparent"></div>
    </div>
    
    <div class="relative z-10 max-w-5xl mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-white/80 text-sm mb-6">
            <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
            <?= (int)$total ?>+ propiedades disponibles
        </div>
        
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-slab font-bold text-white leading-tight mb-6">
            Tu agencia inmobiliaria<br class="hidden sm:block">
            <span class="text-primary">de confianza</span>
        </h1>
        
        <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto mb-10 font-light">
            Sin comisiones ocultas, trato cercano y profesional. Te ayudamos a comprar, vender o alquilar tu propiedad.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/contacto/" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-bold rounded-full text-lg hover:bg-primary-dark transition-all hover:scale-105 shadow-lg">
                Contactar ahora
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="/venta/" class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-full text-lg hover:bg-white/20 transition-all border border-white/30">
                Ver propiedades
            </a>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white/50 animate-bounce">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    </div>
</section>

<!-- ============ SERVICIOS (3 cards) ============ -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Comprar -->
            <a href="/venta/" class="group p-8 rounded-2xl bg-gray-50 hover:bg-primary hover:shadow-xl transition-all duration-300">
                <div class="w-14 h-14 rounded-xl bg-blue-100 group-hover:bg-white/20 flex items-center justify-center mb-5 transition-colors">
                    <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                </div>
                <h3 class="text-xl font-bold text-accent group-hover:text-white mb-2 transition-colors">Comprar</h3>
                <p class="text-gray-600 group-hover:text-white/80 transition-colors">Encontramos tu hogar ideal entre cientos de propiedades.</p>
            </a>
            
            <!-- Vender -->
            <a href="/vender/" class="group p-8 rounded-2xl bg-gray-50 hover:bg-primary hover:shadow-xl transition-all duration-300">
                <div class="w-14 h-14 rounded-xl bg-amber-100 group-hover:bg-white/20 flex items-center justify-center mb-5 transition-colors">
                    <svg class="w-7 h-7 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-accent group-hover:text-white mb-2 transition-colors">Vender</h3>
                <p class="text-gray-600 group-hover:text-white/80 transition-colors">Vendemos tu propiedad al mejor precio y de forma rápida.</p>
            </a>
            
            <!-- Alquilar -->
            <a href="/alquiler/" class="group p-8 rounded-2xl bg-gray-50 hover:bg-primary hover:shadow-xl transition-all duration-300">
                <div class="w-14 h-14 rounded-xl bg-emerald-100 group-hover:bg-white/20 flex items-center justify-center mb-5 transition-colors">
                    <svg class="w-7 h-7 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-accent group-hover:text-white mb-2 transition-colors">Alquilar</h3>
                <p class="text-gray-600 group-hover:text-white/80 transition-colors">Gestión completa de alquileres, sin preocupaciones.</p>
            </a>
        </div>
    </div>
</section>

<!-- ============ SECCIÓN VALORACIÓN ============ -->
<section class="py-20" style="background: #F0FDF4;">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-6">
                    ¿Quieres vender o valorar tu vivienda?
                </h2>
                <p class="text-lg text-gray-600 mb-4 leading-relaxed">
                    Te ayudamos a conseguir el mejor precio de forma rápida, clara y sin compromiso.
                </p>
                <p class="text-gray-600 mb-8">
                    Hacemos una tasación gratuita y analizamos el potencial real de tu propiedad según su ubicación, estado y demanda en la zona.
                </p>
                <a href="/contacto/" class="inline-flex items-center gap-2 px-8 py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-all hover:scale-105">
                    Solicitar valoración gratuita
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
            <div class="rounded-2xl overflow-hidden shadow-lg aspect-[4/3]">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80" 
                     alt="Equipo CP Real Agent" class="w-full h-full object-cover" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- ============ PROPIEDADES DESTACADAS ============ -->
<?php if (count($propiedadesDestacadas) > 0): ?>
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">Propiedades destacadas</h2>
            <p class="text-gray-600 max-w-xl mx-auto">Descubre nuestra selección de propiedades cuidadosamente elegidas para ti.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($propiedadesDestacadas as $prop): ?>
                <?php require __DIR__ . '/includes/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="/propiedades/" class="inline-flex items-center gap-2 px-8 py-3 border-2 border-accent text-accent font-bold rounded-full hover:bg-accent hover:text-white transition-all">
                Ver todas las propiedades
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ CÓMO TRABAJAMOS ============ -->
<section class="py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">Cómo trabajamos</h2>
            <p class="text-gray-600 max-w-xl mx-auto">Un proceso sencillo, transparente y diseñado para que tú solo te preocupes de lo importante.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            $pasos = [
                ['num' => '01', 'icon' => '🔍', 'title' => 'Tasación gratuita', 'desc' => 'Analizamos tu propiedad y su potencial real según ubicación, estado y demanda de la zona.'],
                ['num' => '02', 'icon' => '📸', 'title' => 'Plan de marketing', 'desc' => 'Fotos profesionales, textos optimizados y difusión en portales inmobiliarios y redes sociales.'],
                ['num' => '03', 'icon' => '🤝', 'title' => 'Negociación', 'desc' => 'Negociamos por ti y te asesoramos en documentación, contrato y firma en notaría.'],
                ['num' => '04', 'icon' => '🔑', 'title' => 'Entrega de llaves', 'desc' => 'Acompañamiento completo hasta el final. Tu satisfacción es nuestra prioridad.'],
            ];
            foreach ($pasos as $paso): ?>
            <div class="text-center">
                <div class="relative inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary text-white text-2xl mb-4">
                    <?= $paso['icon'] ?>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Paso <?= $paso['num'] ?></span>
                <h3 class="text-lg font-bold text-accent mt-2 mb-2"><?= $paso['title'] ?></h3>
                <p class="text-sm text-gray-600 leading-relaxed"><?= $paso['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CIUDADES ============ -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">Ciudades donde operamos</h2>
            <p class="text-gray-600 max-w-xl mx-auto">Encontramos propiedades en las principales ciudades de España.</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($ciudades as $ciudad): ?>
            <a href="/venta/<?= e($ciudad['slug']) ?>/" 
               class="relative overflow-hidden rounded-xl aspect-square group">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                     style="background-image: url('https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=400&q=60');">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/20"></div>
                </div>
                <div class="absolute inset-0 flex items-end p-4">
                    <span class="text-white font-bold text-sm"><?= e($ciudad['nombre']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CONTACTO RÁPIDO ============ -->
<section class="py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-slab font-bold text-accent-dark mb-4">¿Tienes alguna duda?</h2>
            <p class="text-gray-600">Déjanos tus datos y te contactamos en menos de 24 horas.</p>
        </div>
        
        <form action="/contacto/" method="POST" class="space-y-4 bg-white rounded-2xl p-8 shadow-md">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nombre" placeholder="Tu nombre" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors">
                <input type="email" name="email" placeholder="Tu email" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors">
            </div>
            <input type="tel" name="telefono" placeholder="Tu teléfono"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors">
            <textarea name="mensaje" placeholder="¿En qué podemos ayudarte?" rows="3" required
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-colors resize-none"></textarea>
            <button type="submit"
                    class="w-full px-8 py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors">
                Enviar mensaje
            </button>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
