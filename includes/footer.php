<?php // includes/footer.php ?>
</main>

<!-- ============ WHATSAPP FLOTANTE ============ -->
<a href="<?= whatsapp_link(WHATSAPP_NUMBER, 'Hola, me interesa una propiedad de CP Real Agent') ?>"
   target="_blank"
   class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 bg-primary text-white rounded-full shadow-lg hover:bg-primary-dark hover:shadow-xl transition-all hover:scale-105"
   aria-label="Contactar por WhatsApp">
    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    <span class="hidden sm:inline text-sm font-medium">WhatsApp</span>
</a>

<!-- ============ FOOTER ============ -->
<footer class="bg-accent text-white">
    
    <!-- CTA final -->
    <div class="bg-primary py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl md:text-3xl font-slab font-bold text-white mb-4">¿Listo para dar el siguiente paso?</h2>
            <p class="text-white/90 mb-8 max-w-2xl mx-auto">Te ayudamos a comprar, vender o alquilar tu propiedad con transparencia, trato cercano y sin comisiones ocultas.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/contacto/" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-primary font-bold rounded-full hover:bg-gray-100 transition-colors">
                    Contactar ahora
                </a>
                <a href="<?= whatsapp_link(WHATSAPP_NUMBER) ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-8 py-3 bg-white/20 text-white font-bold rounded-full hover:bg-white/30 transition-colors">
                    💬 WhatsApp
                </a>
            </div>
            <p class="mt-6 text-white/70">📞 <?= SITE_PHONE ?></p>
        </div>
    </div>
    
    <!-- Footer principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            
            <!-- Logo + descripción -->
            <div class="md:col-span-1">
                <span class="text-2xl font-slab font-bold">CP Real Agent</span>
                <p class="mt-3 text-sm text-gray-300 leading-relaxed"><?= e(SITE_TAGLINE) ?>. Sin comisiones ocultas, trato cercano y profesional.</p>
            </div>
            
            <!-- Navegación -->
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider text-gray-400 mb-4">Navegación</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/" class="text-gray-300 hover:text-white transition-colors">Inicio</a></li>
                    <li><a href="/venta/" class="text-gray-300 hover:text-white transition-colors">Comprar</a></li>
                    <li><a href="/vender/" class="text-gray-300 hover:text-white transition-colors">Vender</a></li>
                    <li><a href="/alquiler/" class="text-gray-300 hover:text-white transition-colors">Alquilar</a></li>
                    <li><a href="/contacto/" class="text-gray-300 hover:text-white transition-colors">Contacto</a></li>
                </ul>
            </div>
            
            <!-- Contacto -->
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider text-gray-400 mb-4">Contacto</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li>📍 <?= e(SITE_ADDRESS) ?></li>
                    <li>📞 <a href="tel:<?= SITE_PHONE ?>" class="hover:text-white"><?= SITE_PHONE ?></a></li>
                    <li>✉️ <a href="mailto:<?= SITE_EMAIL ?>" class="hover:text-white"><?= SITE_EMAIL ?></a></li>
                    <li>💬 <a href="<?= whatsapp_link(WHATSAPP_NUMBER) ?>" target="_blank" class="hover:text-white">WhatsApp</a></li>
                </ul>
            </div>
            
            <!-- Legal -->
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider text-gray-400 mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/aviso-legal" class="text-gray-300 hover:text-white transition-colors">Aviso legal</a></li>
                    <li><a href="/politica-privacidad" class="text-gray-300 hover:text-white transition-colors">Política de privacidad</a></li>
                    <li><a href="/cookies" class="text-gray-300 hover:text-white transition-colors">Política de cookies</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="mt-10 pt-8 border-t border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-400">&copy; <?= date('Y') ?> CP Real Agent. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Schema.org: RealEstateAgent -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "RealEstateAgent",
    "name": "CP Real Agent",
    "url": "<?= SITE_URL ?>",
    "logo": "<?= SITE_URL ?>/assets/img/logo.svg",
    "telephone": "<?= SITE_PHONE ?>",
    "email": "<?= SITE_EMAIL ?>",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "<?= e(SITE_ADDRESS) ?>",
        "addressCountry": "ES"
    }
}
</script>

</body>
</html>
