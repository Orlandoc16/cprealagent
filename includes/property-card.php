<?php // includes/property-card.php
// $prop — array con datos de la propiedad
// $db — PDO connection
$portada = get_portada($prop['id'], $db);
$precioText = format_price((float)$prop['precio'], $prop['tipo_operacion']);
$opColor = tipo_operacion_color($prop['tipo_operacion']);
$opLabel = tipo_operacion_label($prop['tipo_operacion']);
$tipoLabel = tipo_inmueble_label($prop['tipo_inmueble']);
$slug = e($prop['slug']);
?>

<article class="group bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300">
    <!-- Imagen -->
    <a href="/propiedad/<?= $slug ?>/" class="block relative overflow-hidden aspect-[16/10]">
        <?php if ($portada): ?>
        <img src="<?= e($portada) ?>" alt="<?= e($prop['titulo']) ?>"
             class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-300"
             loading="lazy" width="640" height="400">
        <?php else: ?>
        <div class="w-full h-full bg-gray-100 flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
        </div>
        <?php endif; ?>
        
        <!-- Badges -->
        <div class="absolute top-3 left-3 flex gap-2">
            <span class="px-2.5 py-1 text-xs font-bold text-white rounded-full <?= $opColor ?>"><?= $opLabel ?></span>
            <?php if ($prop['destacada']): ?>
            <span class="px-2.5 py-1 text-xs font-bold text-amber-900 bg-amber-400 rounded-full">⭐ Destacada</span>
            <?php endif; ?>
        </div>
        
        <!-- Certificación energética -->
        <?php if ($prop['certificacion_energetica']): ?>
        <div class="absolute bottom-3 right-3 w-8 h-8 rounded bg-white/90 flex items-center justify-center text-xs font-bold text-accent shadow">
            <?= e($prop['certificacion_energetica']) ?>
        </div>
        <?php endif; ?>
    </a>
    
    <!-- Info -->
    <div class="p-4">
        <!-- Ubicación -->
        <div class="flex items-center gap-1 text-sm text-gray-500 mb-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span><?= e($prop['ciudad']) ?><?php if ($prop['zona_barrio']) echo ' · ' . e($prop['zona_barrio']) ?></span>
        </div>
        
        <!-- Título -->
        <a href="/propiedad/<?= $slug ?>/" class="block">
            <h3 class="font-bold text-accent-dark leading-snug group-hover:text-primary transition-colors line-clamp-2">
                <?= e($prop['titulo']) ?>
            </h3>
        </a>
        
        <!-- Características -->
        <div class="flex items-center gap-3 mt-3 text-sm text-gray-600">
            <?php if ($prop['habitaciones']): ?>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"/></svg>
                <?= (int)$prop['habitaciones'] ?> hab.
            </span>
            <?php endif; ?>
            <?php if ($prop['banos']): ?>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                <?= (int)$prop['banos'] ?> baño<?= (int)$prop['banos'] > 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
            <?php if ($prop['superficie']): ?>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <?= number_format((float)$prop['superficie'], 0) ?> m²
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Precio + CTA -->
        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
            <span class="text-lg font-bold text-primary"><?= $precioText ?></span>
            <a href="/propiedad/<?= $slug ?>/"
               class="px-3 py-1.5 text-xs font-bold text-primary border border-primary rounded-full hover:bg-primary hover:text-white transition-colors">
                Ver detalle
            </a>
        </div>
    </div>
</article>
