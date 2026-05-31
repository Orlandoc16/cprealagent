<?php
// ============================================
// CP Real Agent — Funciones Helper
// ============================================

/**
 * Formatear precio con símbolo €
 */
function format_price(float $price, string $operacion): string {
    $formatted = number_format($price, 0, ',', '.');
    if ($operacion === 'alquiler') {
        return '€' . $formatted . '/mes';
    }
    return '€' . $formatted;
}

/**
 * Obtener nombre legible de tipo de inmueble
 */
function tipo_inmueble_label(string $tipo): string {
    $map = [
        'piso'    => 'Piso',
        'casa'    => 'Casa',
        'chalet'  => 'Chalet',
        'atico'   => 'Ático',
        'duplex'  => 'Dúplex',
        'estudio' => 'Estudio',
        'local'   => 'Local',
        'oficina' => 'Oficina',
        'terreno' => 'Terreno',
        'garaje'  => 'Garaje',
    ];
    return $map[$tipo] ?? ucfirst($tipo);
}

/**
 * Obtener nombre legible de tipo de operación
 */
function tipo_operacion_label(string $tipo): string {
    $map = [
        'alquiler' => 'Alquiler',
        'venta'    => 'Venta',
        'traspaso' => 'Traspaso',
        'compartir'=> 'Compartir',
    ];
    return $map[$tipo] ?? ucfirst($tipo);
}

/**
 * Badge de color según tipo de operación
 */
function tipo_operacion_color(string $tipo): string {
    $map = [
        'alquiler' => 'bg-emerald-500',
        'venta'    => 'bg-blue-600',
        'traspaso' => 'bg-amber-500',
        'compartir'=> 'bg-purple-500',
    ];
    return $map[$tipo] ?? 'bg-gray-500';
}

/**
 * Obtener imagen portada de una propiedad
 */
function get_portada(int $propiedadId, PDO $db): ?string {
    $stmt = $db->prepare("SELECT imagen_path FROM propiedad_imagenes WHERE propiedad_id = ? AND is_portada = 1 LIMIT 1");
    $stmt->execute([$propiedadId]);
    $row = $stmt->fetch();
    if ($row) return UPLOADS_URL . '/' . $row['imagen_path'];
    
    // Si no hay portada, devolver primera imagen
    $stmt = $db->prepare("SELECT imagen_path FROM propiedad_imagenes WHERE propiedad_id = ? ORDER BY orden ASC LIMIT 1");
    $stmt->execute([$propiedadId]);
    $row = $stmt->fetch();
    return $row ? UPLOADS_URL . '/' . $row['imagen_path'] : null;
}

/**
 * Obtener todas las imágenes de una propiedad
 */
function get_imagenes(int $propiedadId, PDO $db): array {
    $stmt = $db->prepare("SELECT * FROM propiedad_imagenes WHERE propiedad_id = ? ORDER BY is_portada DESC, orden ASC");
    $stmt->execute([$propiedadId]);
    return $stmt->fetchAll();
}

/**
 * Obtener ciudades activas
 */
function get_ciudades(PDO $db): array {
    $stmt = $db->query("SELECT * FROM ciudades WHERE activa = 1 ORDER BY nombre ASC");
    return $stmt->fetchAll();
}

/**
 * Incrementar visitas de propiedad
 */
function increment_visitas(int $id, PDO $db): void {
    $stmt = $db->prepare("UPDATE propiedades SET visitas = visitas + 1 WHERE id = ?");
    $stmt->execute([$id]);
}

/**
 * Guardar lead en base de datos
 */
function save_lead(PDO $db, array $data): bool {
    $stmt = $db->prepare("
        INSERT INTO leads (propiedad_id, landing_slug, nombre, email, telefono, mensaje, fuente, utm_source, utm_medium, utm_campaign)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['propiedad_id'] ?? null,
        $data['landing_slug'] ?? null,
        $data['nombre'],
        $data['email'],
        $data['telefono'] ?? null,
        $data['mensaje'] ?? null,
        $data['fuente'] ?? 'web',
        $data['utm_source'] ?? null,
        $data['utm_medium'] ?? null,
        $data['utm_campaign'] ?? null,
    ]);
}

/**
 * Enviar email de notificación de lead
 */
function notify_lead_email(array $lead): bool {
    $to = SITE_EMAIL;
    $subject = 'Nuevo lead: ' . $lead['nombre'];
    $body = "Nuevo contacto desde la web:\n\n";
    $body .= "Nombre: {$lead['nombre']}\n";
    $body .= "Email: {$lead['email']}\n";
    if (!empty($lead['telefono'])) $body .= "Teléfono: {$lead['telefono']}\n";
    if (!empty($lead['mensaje'])) $body .= "Mensaje: {$lead['mensaje']}\n";
    $body .= "Fuente: {$lead['fuente']}\n";
    if (!empty($lead['utm_source'])) $body .= "UTM Source: {$lead['utm_source']}\n";
    $body .= "\nFecha: " . date('d/m/Y H:i');
    
    $headers = "From: noreply@cprealagent.com\r\n";
    $headers .= "Reply-To: {$lead['email']}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Procesar upload de imagen: resize + WebP
 */
function process_image(array $file, string $destDir): ?string {
    if (!validate_upload_type($file)) return null;
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;
    
    $ext = 'webp';
    $filename = uniqid('img_') . '.' . $ext;
    $destPath = $destDir . '/' . $filename;
    
    // Crear directorio si no existe
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    
    $source = $file['tmp_name'];
    
    switch ($file['type']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            // Preservar transparencia
            imagepalettetotruecolor($image);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return null;
    }
    
    if (!$image) return null;
    
    // Resize si excede ancho máximo
    $width = imagesx($image);
    $height = imagesy($image);
    if ($width > MAX_IMAGE_WIDTH) {
        $ratio = MAX_IMAGE_WIDTH / $width;
        $newHeight = (int)($height * $ratio);
        $resized = imagecreatetruecolor(MAX_IMAGE_WIDTH, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, MAX_IMAGE_WIDTH, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    // Strip EXIF
    imagewebp($image, $destPath, 85);
    imagedestroy($image);
    
    // Devolver path relativo a /uploads/
    return str_replace(BASE_PATH . '/uploads/', '', $destPath);
}

/**
 * Paginación SEO-friendly
 */
function render_pagination(int $current, int $total, int $perPage, string $baseUrl): string {
    $totalPages = max(1, ceil($total / $perPage));
    if ($totalPages <= 1) return '';
    
    $html = '<nav class="flex items-center justify-center gap-1 mt-10" aria-label="Paginación">';
    $html .= '<span class="text-sm text-gray-500 mr-2">Página ' . $current . ' de ' . $totalPages . '</span>';
    
    // Previous
    if ($current > 1) {
        $prevUrl = $baseUrl . ($current - 1 > 1 ? '?page=' . ($current - 1) : '');
        $html .= '<a href="' . e($prevUrl) . '" class="px-3 py-2 text-sm rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700">← Anterior</a>';
    }
    
    // Page numbers
    $start = max(1, $current - 2);
    $end = min($totalPages, $current + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $url = $i === 1 ? $baseUrl : $baseUrl . '?page=' . $i;
        $active = $i === $current ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50';
        $html .= '<a href="' . e($url) . '" class="px-3 py-2 text-sm rounded-lg border ' . $active . '">' . $i . '</a>';
    }
    
    // Next
    if ($current < $totalPages) {
        $nextUrl = $baseUrl . '?page=' . ($current + 1);
        $html .= '<a href="' . e($nextUrl) . '" class="px-3 py-2 text-sm rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700">Siguiente →</a>';
    }
    
    $html .= '</nav>';
    return $html;
}

/**
 * Generar WhatsApp link
 */
function whatsapp_link(string $phone, string $text = ''): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return 'https://wa.me/' . $phone . ($text ? '?text=' . urlencode($text) : '');
}
