<?php // admin/propiedades/crear.php — Crear/Editar propiedad
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';
start_session();
require_auth();

$db = getDB();
$isEdit = isset($_GET['id']);
$prop = null;
$imagenes = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM propiedades WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $prop = $stmt->fetch();
    if (!$prop) { die('Propiedad no encontrada'); }
    $imagenes = get_imagenes($prop['id'], $db);
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Error de seguridad.'; }
    else {
        $data = [
            'titulo'                 => sanitize($_POST['titulo'] ?? ''),
            'tipo_operacion'         => $_POST['tipo_operacion'] ?? 'venta',
            'tipo_inmueble'          => $_POST['tipo_inmueble'] ?? 'piso',
            'precio'                 => (float)($_POST['precio'] ?? 0),
            'superficie'             => empty($_POST['superficie']) ? null : (float)$_POST['superficie'],
            'superficie_util'        => empty($_POST['superficie_util']) ? null : (float)$_POST['superficie_util'],
            'habitaciones'           => empty($_POST['habitaciones']) ? null : (int)$_POST['habitaciones'],
            'banos'                  => empty($_POST['banos']) ? null : (int)$_POST['banos'],
            'planta'                 => empty($_POST['planta']) ? null : sanitize($_POST['planta']),
            'ascensor'               => isset($_POST['ascensor']) ? 1 : 0,
            'terraza'                => isset($_POST['terraza']) ? 1 : 0,
            'garaje'                 => isset($_POST['garaje']) ? 1 : 0,
            'piscina'                => isset($_POST['piscina']) ? 1 : 0,
            'aire_acondicionado'     => isset($_POST['aire_acondicionado']) ? 1 : 0,
            'amueblado'              => isset($_POST['amueblado']) ? 1 : 0,
            'certificacion_energetica' => empty($_POST['certificacion_energetica']) ? null : $_POST['certificacion_energetica'],
            'ciudad'                 => sanitize($_POST['ciudad'] ?? ''),
            'zona_barrio'            => empty($_POST['zona_barrio']) ? null : sanitize($_POST['zona_barrio']),
            'direccion'              => empty($_POST['direccion']) ? null : sanitize($_POST['direccion']),
            'lat'                    => empty($_POST['lat']) ? null : (float)$_POST['lat'],
            'lng'                    => empty($_POST['lng']) ? null : (float)$_POST['lng'],
            'descripcion'            => $_POST['descripcion'] ?? '',
            'destacada'              => isset($_POST['destacada']) ? 1 : 0,
            'activa'                 => isset($_POST['activa']) ? 1 : 0,
        ];
        
        if (!$data['titulo'] || !$data['precio'] || !$data['ciudad']) {
            $error = 'Los campos título, precio y ciudad son obligatorios.';
        } else {
            $data['slug'] = slugify($data['titulo']) . ($isEdit ? '-' . $prop['id'] : '');
            
            // Check unique slug
            $slugCheck = $db->prepare("SELECT id FROM propiedades WHERE slug = ? AND id != ?");
            $slugCheck->execute([$data['slug'], $isEdit ? $prop['id'] : 0]);
            if ($slugCheck->fetch()) {
                $data['slug'] .= '-' . time();
            }
            
            if ($isEdit) {
                $data['id'] = $prop['id'];
                $sql = "UPDATE propiedades SET 
                    titulo = ?, slug = ?, descripcion = ?, tipo_operacion = ?, tipo_inmueble = ?,
                    precio = ?, superficie = ?, superficie_util = ?, habitaciones = ?, banos = ?,
                    planta = ?, ascensor = ?, terraza = ?, garaje = ?, piscina = ?,
                    aire_acondicionado = ?, amueblado = ?, certificacion_energetica = ?,
                    ciudad = ?, zona_barrio = ?, direccion = ?, lat = ?, lng = ?,
                    destacada = ?, activa = ?
                    WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $data['titulo'], $data['slug'], $data['descripcion'], $data['tipo_operacion'], $data['tipo_inmueble'],
                    $data['precio'], $data['superficie'], $data['superficie_util'], $data['habitaciones'], $data['banos'],
                    $data['planta'], $data['ascensor'], $data['terraza'], $data['garaje'], $data['piscina'],
                    $data['aire_acondicionado'], $data['amueblado'], $data['certificacion_energetica'],
                    $data['ciudad'], $data['zona_barrio'], $data['direccion'], $data['lat'], $data['lng'],
                    $data['destacada'], $data['activa'], $data['id']
                ]);
                $propId = $data['id'];
            } else {
                $sql = "INSERT INTO propiedades (titulo, slug, descripcion, tipo_operacion, tipo_inmueble, precio, superficie, superficie_util, habitaciones, banos, planta, ascensor, terraza, garaje, piscina, aire_acondicionado, amueblado, certificacion_energetica, ciudad, zona_barrio, direccion, lat, lng, destacada, activa) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $data['titulo'], $data['slug'], $data['descripcion'], $data['tipo_operacion'], $data['tipo_inmueble'],
                    $data['precio'], $data['superficie'], $data['superficie_util'], $data['habitaciones'], $data['banos'],
                    $data['planta'], $data['ascensor'], $data['terraza'], $data['garaje'], $data['piscina'],
                    $data['aire_acondicionado'], $data['amueblado'], $data['certificacion_energetica'],
                    $data['ciudad'], $data['zona_barrio'], $data['direccion'], $data['lat'], $data['lng'],
                    $data['destacada'], $data['activa']
                ]);
                $propId = $db->lastInsertId();
            }
            
            // Upload imágenes
            if (!empty($_FILES['imagenes'])) {
                $uploadDir = UPLOADS_PATH . '/propiedades/' . $propId;
                $isFirst = (count($imagenes) === 0);
                
                foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmpName) {
                    if ($_FILES['imagenes']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'tmp_name' => $tmpName,
                            'name'     => $_FILES['imagenes']['name'][$i],
                            'type'     => $_FILES['imagenes']['type'][$i],
                            'size'     => $_FILES['imagenes']['size'][$i],
                        ];
                        $imgPath = process_image($file, $uploadDir);
                        if ($imgPath) {
                            $isPortada = $isFirst ? 1 : 0;
                            $db->prepare("INSERT INTO propiedad_imagenes (propiedad_id, imagen_path, orden, is_portada) VALUES (?,?,?,?)")
                               ->execute([$propId, $imgPath, count($imagenes), $isPortada]);
                            $isFirst = false;
                        }
                    }
                }
            }
            
            $success = true;
            // Reload data after save
            $stmt = $db->prepare("SELECT * FROM propiedades WHERE id = ?");
            $stmt->execute([$propId]);
            $prop = $stmt->fetch();
            $imagenes = get_imagenes($propId, $db);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $isEdit ? 'Editar' : 'Nueva' ?> Propiedad — CP Real Agent Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: { DEFAULT: '#1BB55B', dark: '#15943F' }, accent: { DEFAULT: '#2C3E50', dark: '#1A252F' } }, fontFamily: { sans: ['Roboto','sans-serif'], slab: ['Roboto Slab','serif'] } } } }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/admin/dashboard" class="text-xl font-slab font-bold text-accent-dark">CP Real Agent</a>
                <span class="text-sm text-gray-400">/ <?= $isEdit ? 'Editar' : 'Nueva' ?> propiedad</span>
            </div>
            <a href="/admin/propiedades/listar" class="text-sm text-gray-500 hover:text-primary">← Volver al listado</a>
        </div>
    </header>
    
    <div class="max-w-5xl mx-auto px-4 py-8">
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
            ✅ Propiedad guardada correctamente.
            <a href="/propiedad/<?= e($prop['slug']) ?>/" target="_blank" class="underline ml-2">Ver en la web →</a>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            ❌ <?= e($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <?= csrf_field() ?>
            
            <!-- Info básica -->
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                <h2 class="text-lg font-bold text-accent-dark">Información básica</h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                    <input type="text" name="titulo" required value="<?= e($prop['titulo'] ?? $_POST['titulo'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           placeholder="Ej: Piso luminoso con terraza en Chamberí">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
                    <textarea name="descripcion" required rows="6" 
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none resize-y"
                              placeholder="Describe la propiedad con detalle..."><?= e($prop['descripcion'] ?? $_POST['descripcion'] ?? '') ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de operación *</label>
                        <select name="tipo_operacion" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary outline-none">
                            <option value="venta" <?= ($prop['tipo_operacion'] ?? '') === 'venta' ? 'selected' : '' ?>>Venta</option>
                            <option value="alquiler" <?= ($prop['tipo_operacion'] ?? '') === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                            <option value="traspaso" <?= ($prop['tipo_operacion'] ?? '') === 'traspaso' ? 'selected' : '' ?>>Traspaso</option>
                            <option value="compartir" <?= ($prop['tipo_operacion'] ?? '') === 'compartir' ? 'selected' : '' ?>>Compartir</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de inmueble *</label>
                        <select name="tipo_inmueble" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary outline-none">
                            <?php foreach (['piso','casa','chalet','atico','duplex','estudio','local','oficina','terreno','garaje'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($prop['tipo_inmueble'] ?? '') === $t ? 'selected' : '' ?>><?= tipo_inmueble_label($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio (€) *</label>
                    <input type="number" name="precio" required step="0.01" min="0" 
                           value="<?= e($prop['precio'] ?? $_POST['precio'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary outline-none"
                           placeholder="Ej: 150000">
                </div>
            </div>
            
            <!-- Características -->
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                <h2 class="text-lg font-bold text-accent-dark">Características</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Superficie total (m²)</label>
                        <input type="number" name="superficie" step="0.01"
                               value="<?= e($prop['superficie'] ?? $_POST['superficie'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Superficie útil (m²)</label>
                        <input type="number" name="superficie_util" step="0.01"
                               value="<?= e($prop['superficie_util'] ?? $_POST['superficie_util'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Habitaciones</label>
                        <input type="number" name="habitaciones" min="0"
                               value="<?= e($prop['habitaciones'] ?? $_POST['habitaciones'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Baños</label>
                        <input type="number" name="banos" min="0"
                               value="<?= e($prop['banos'] ?? $_POST['banos'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Planta</label>
                        <input type="text" name="planta"
                               value="<?= e($prop['planta'] ?? $_POST['planta'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm"
                               placeholder="Ej: 3ª, bajo, entreplanta">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Certificación energética</label>
                        <select name="certificacion_energetica" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                            <option value="">No disponible</option>
                            <?php foreach (range('A','G') as $ce): ?>
                            <option value="<?= $ce ?>" <?= ($prop['certificacion_energetica'] ?? '') === $ce ? 'selected' : '' ?>><?= $ce ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Checkboxes -->
                <div class="flex flex-wrap gap-4 pt-2">
                    <?php
                    $checks = [
                        'ascensor' => '🛗 Ascensor',
                        'terraza' => '🌿 Terraza',
                        'garaje' => '🚗 Garaje',
                        'piscina' => '🏊 Piscina',
                        'aire_acondicionado' => '❄️ Aire acondicionado',
                        'amueblado' => '🛋️ Amueblado',
                    ];
                    foreach ($checks as $key => $label): ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="<?= $key ?>" <?= ($prop[$key] ?? 0) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                        <span class="text-sm text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Ubicación -->
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                <h2 class="text-lg font-bold text-accent-dark">Ubicación</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad *</label>
                        <input type="text" name="ciudad" required 
                               value="<?= e($prop['ciudad'] ?? $_POST['ciudad'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary outline-none"
                               placeholder="Ej: Madrid" list="ciudades-list">
                        <datalist id="ciudades-list">
                            <?php foreach (get_ciudades($db) as $c): ?>
                            <option value="<?= e($c['nombre']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zona / Barrio</label>
                        <input type="text" name="zona_barrio"
                               value="<?= e($prop['zona_barrio'] ?? $_POST['zona_barrio'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary outline-none"
                               placeholder="Ej: Chamberí">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Dirección (no visible públicamente)</label>
                    <input type="text" name="direccion"
                           value="<?= e($prop['direccion'] ?? $_POST['direccion'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary outline-none"
                           placeholder="Ej: Calle Ejemplo, 42">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Latitud</label>
                        <input type="number" step="any" name="lat"
                               value="<?= e($prop['lat'] ?? $_POST['lat'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Longitud</label>
                        <input type="number" step="any" name="lng"
                               value="<?= e($prop['lng'] ?? $_POST['lng'] ?? '') ?>"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                </div>
            </div>
            
            <!-- Imágenes -->
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                <h2 class="text-lg font-bold text-accent-dark">Imágenes</h2>
                <p class="text-sm text-gray-500">Formato JPG/PNG/WebP. Máximo 5MB por imagen. Se convierten a WebP automáticamente.</p>
                
                <?php if (count($imagenes) > 0): ?>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    <?php foreach ($imagenes as $img): ?>
                    <div class="relative aspect-square rounded-lg overflow-hidden border <?= $img['is_portada'] ? 'border-primary border-2' : 'border-gray-200' ?>">
                        <img src="<?= UPLOADS_URL . '/' . e($img['imagen_path']) ?>" class="w-full h-full object-cover" loading="lazy">
                        <?php if ($img['is_portada']): ?>
                        <span class="absolute top-1 left-1 px-1.5 py-0.5 bg-primary text-white text-[10px] font-bold rounded">Portada</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="block w-full cursor-pointer">
                        <div class="flex items-center justify-center gap-2 px-4 py-6 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-primary hover:text-primary transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="font-medium">Añadir imágenes (máximo <?= MAX_PROPERTY_IMAGES ?>)</span>
                        </div>
                        <input type="file" name="imagenes[]" multiple accept="image/jpeg,image/png,image/webp" class="hidden">
                    </label>
                </div>
            </div>
            
            <!-- Estado -->
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                <h2 class="text-lg font-bold text-accent-dark">Estado</h2>
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="destacada" <?= ($prop['destacada'] ?? 0) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                        <span class="text-sm text-gray-700">⭐ Destacada (aparece en home)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="activa" <?= ($prop['activa'] ?? 1) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                        <span class="text-sm text-gray-700">✅ Activa (visible públicamente)</span>
                    </label>
                </div>
            </div>
            
            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 bg-primary text-white font-bold rounded-full hover:bg-primary-dark transition-colors">
                    <?= $isEdit ? 'Guardar cambios' : 'Crear propiedad' ?>
                </button>
                <a href="/admin/propiedades/listar" class="px-6 py-3 text-gray-500 hover:text-gray-700">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
