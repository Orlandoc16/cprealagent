<?php // sitemap.php — Genera sitemap.xml dinámico
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$baseUrl = SITE_URL;

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Páginas estáticas
$staticPages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/venta/', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/alquiler/', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/vender/', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/comprar/', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/contacto/', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/sobre-nosotros/', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => '/propiedades/', 'priority' => '0.8', 'changefreq' => 'daily'],
];

foreach ($staticPages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$baseUrl}{$page['loc']}</loc>\n";
    $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$page['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

// Propiedades activas
$stmt = $db->query("SELECT slug, updated_at FROM propiedades WHERE activa = 1");
while ($prop = $stmt->fetch()) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$baseUrl}/propiedad/{$prop['slug']}/</loc>\n";
    $xml .= "    <lastmod>" . date('Y-m-d', strtotime($prop['updated_at'])) . "</lastmod>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.8</priority>\n";
    $xml .= "  </url>\n";
}

// Páginas de ciudades x operación
$ciudades = $db->query("SELECT slug FROM ciudades WHERE activa = 1");
while ($c = $ciudades->fetch()) {
    foreach (['alquiler', 'venta'] as $op) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$baseUrl}/{$op}/{$c['slug']}/</loc>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.6</priority>\n";
        $xml .= "  </url>\n";
    }
}

// Landing pages activas
$landings = $db->query("SELECT slug, created_at FROM landing_pages WHERE activa = 1");
while ($lp = $landings->fetch()) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$baseUrl}/lp/{$lp['slug']}/</loc>\n";
    $xml .= "    <lastmod>" . date('Y-m-d', strtotime($lp['created_at'])) . "</lastmod>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.5</priority>\n";
    $xml .= "  </url>\n";
}

$xml .= '</urlset>';

header('Content-Type: application/xml');
echo $xml;
