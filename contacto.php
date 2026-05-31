<?php // contacto.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';
start_session();
store_utm_in_session();

$pageTitle = 'Contacto';
$pageDesc = 'Contacta con CP Real Agent. Estamos aquí para ayudarte a comprar, vender o alquilar tu propiedad.';
$canonicalUrl = SITE_URL . '/contacto/';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Error de seguridad. Intenta de nuevo.'; }
    else {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $telefono = sanitize($_POST['telefono'] ?? '');
        $mensaje = sanitize($_POST['mensaje'] ?? '');
        
        if (!$nombre || !$email || !$mensaje) { $error = 'Por favor completa los campos obligatorios.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'El email no es válido.'; }
        else {
            $db = getDB();
            $leadData = [
                'nombre'    => $nombre,
                'email'     => $email,
                'telefono'  => $telefono,
                'mensaje'   => $mensaje,
                'fuente'    => 'web',
                'utm_source'   => $_SESSION['utm_source'] ?? null,
                'utm_medium'   => $_SESSION['utm_medium'] ?? null,
                'utm_campaign' => $_SESSION['utm_campaign'] ?? null,
            ];
            save_lead($db, $leadData);
            notify_lead_email($leadData);
            $success = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="py-20 bg-accent text-white" style="background: linear-gradient(135deg, #1A252F 0%, #2C3E50 100%);">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-slab font-bold mb-4">Contacto</h1>
        <p class="text-xl text-white/80">¿Tienes alguna duda? Estamos aquí para ayudarte.</p>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Formulario -->
            <div>
                <h2 class="text-2xl font-slab font-bold text-accent-dark mb-6">Envíanos un mensaje</h2>
                
                <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
                    ✅ ¡Mensaje enviado! Te contactaremos en menos de 24 horas.
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    ❌ <?= e($error) ?>
                </div>
                <?php endif; ?>
                
                <form action="/contacto/" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" name="nombre" required value="<?= e($_POST['nombre'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" name="telefono" value="<?= e($_POST['telefono'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje *</label>
                        <textarea name="mensaje" rows="5" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors resize-none"><?= e($_POST['mensaje'] ?? '') ?></textarea>
                    </div>
                    <button type="submit"
                            class="w-full px-8 py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors">
                        Enviar mensaje
                    </button>
                </form>
            </div>
            
            <!-- Info de contacto -->
            <div>
                <h2 class="text-2xl font-slab font-bold text-accent-dark mb-6">Información de contacto</h2>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4 p-5 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">📍</div>
                        <div>
                            <h4 class="font-bold text-accent">Dirección</h4>
                            <p class="text-gray-600"><?= e(SITE_ADDRESS) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-5 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">📞</div>
                        <div>
                            <h4 class="font-bold text-accent">Teléfono</h4>
                            <a href="tel:<?= SITE_PHONE ?>" class="text-gray-600 hover:text-primary"><?= SITE_PHONE ?></a>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-5 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">✉️</div>
                        <div>
                            <h4 class="font-bold text-accent">Email</h4>
                            <a href="mailto:<?= SITE_EMAIL ?>" class="text-gray-600 hover:text-primary"><?= SITE_EMAIL ?></a>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-5 bg-primary/5 rounded-xl border border-primary/20">
                        <div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-xl flex-shrink-0">💬</div>
                        <div>
                            <h4 class="font-bold text-accent">WhatsApp</h4>
                            <p class="text-gray-600 text-sm mb-2">Respuesta rápida, cualquier día de la semana</p>
                            <a href="<?= whatsapp_link(WHATSAPP_NUMBER) ?>" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-full hover:bg-primary-dark transition-colors">
                                Abrir WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mapa placeholder -->
                <div class="mt-8 rounded-xl overflow-hidden bg-gray-100 aspect-video flex items-center justify-center">
                    <span class="text-gray-400 text-sm">Mapa — Integrar Leaflet.js o Google Maps</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
