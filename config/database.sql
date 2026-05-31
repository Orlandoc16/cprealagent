-- ============================================
-- CP Real Agent — Base de Datos MySQL
-- PHP 8.4+ | MySQL 5.7+ / MariaDB 10.4+
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `role` ENUM('admin','editor') DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `propiedades` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) UNIQUE NOT NULL,
  `descripcion` TEXT NOT NULL,
  `tipo_operacion` ENUM('alquiler','venta','traspaso','compartir') NOT NULL,
  `tipo_inmueble` ENUM('piso','casa','chalet','atico','duplex','estudio','local','oficina','terreno','garaje') NOT NULL,
  `precio` DECIMAL(12,2) NOT NULL,
  `moneda` CHAR(3) DEFAULT 'EUR',
  `superficie` DECIMAL(8,2) DEFAULT NULL,
  `superficie_util` DECIMAL(8,2) DEFAULT NULL,
  `habitaciones` TINYINT DEFAULT NULL,
  `banos` TINYINT DEFAULT NULL,
  `planta` VARCHAR(50) DEFAULT NULL,
  `ascensor` TINYINT(1) DEFAULT 0,
  `terraza` TINYINT(1) DEFAULT 0,
  `garaje` TINYINT(1) DEFAULT 0,
  `piscina` TINYINT(1) DEFAULT 0,
  `aire_acondicionado` TINYINT(1) DEFAULT 0,
  `amueblado` TINYINT(1) DEFAULT 0,
  `certificacion_energetica` CHAR(1) DEFAULT NULL,
  `ciudad` VARCHAR(100) NOT NULL,
  `zona_barrio` VARCHAR(100) DEFAULT NULL,
  `direccion` VARCHAR(255) DEFAULT NULL,
  `lat` DECIMAL(10,8) DEFAULT NULL,
  `lng` DECIMAL(11,8) DEFAULT NULL,
  `destacada` TINYINT(1) DEFAULT 0,
  `activa` TINYINT(1) DEFAULT 1,
  `visitas` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_operacion (`tipo_operacion`),
  INDEX idx_ciudad (`ciudad`),
  INDEX idx_tipo (`tipo_inmueble`),
  INDEX idx_precio (`precio`),
  INDEX idx_destacada (`destacada`, `activa`),
  FULLTEXT idx_busqueda (`titulo`, `descripcion`, `ciudad`, `zona_barrio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `propiedad_imagenes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `propiedad_id` INT NOT NULL,
  `imagen_path` VARCHAR(255) NOT NULL,
  `orden` TINYINT DEFAULT 0,
  `is_portada` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades`(`id`) ON DELETE CASCADE,
  INDEX idx_portada (`propiedad_id`, `is_portada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `propiedad_id` INT DEFAULT NULL,
  `landing_slug` VARCHAR(100) DEFAULT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `mensaje` TEXT DEFAULT NULL,
  `fuente` VARCHAR(50) DEFAULT 'web',
  `utm_source` VARCHAR(100) DEFAULT NULL,
  `utm_medium` VARCHAR(100) DEFAULT NULL,
  `utm_campaign` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`propiedad_id`) REFERENCES `propiedades`(`id`) ON DELETE SET NULL,
  INDEX idx_fecha (`created_at`),
  INDEX idx_fuente (`fuente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `landing_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `subtitulo` VARCHAR(300) DEFAULT NULL,
  `headline` VARCHAR(200) DEFAULT NULL,
  `contenido` TEXT DEFAULT NULL,
  `cta_text` VARCHAR(50) DEFAULT 'Contactar ahora',
  `cta_url` VARCHAR(255) DEFAULT NULL,
  `imagen_hero` VARCHAR(255) DEFAULT NULL,
  `propiedad_ids` TEXT DEFAULT NULL,
  `filtros_json` TEXT DEFAULT NULL,
  `activa` TINYINT(1) DEFAULT 1,
  `visitas` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ciudades` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(110) UNIQUE NOT NULL,
  `provincia` VARCHAR(100) DEFAULT NULL,
  `activa` TINYINT(1) DEFAULT 1,
  INDEX idx_slug (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DATOS DE EJEMPLO (Seed)
-- ============================================

-- Usuario admin (password: CPAdmin2026!)
INSERT INTO `users` (`username`, `password_hash`, `email`, `role`) VALUES
('admin', '$2y$12$LJ3m4ovFnCqZ7GqF5G1XkO1V8r.Eq9kPKrqMH6m5WzJdNCRiXiALi', 'admin@cprealagent.com', 'admin');

-- Ciudades
INSERT INTO `ciudades` (`nombre`, `slug`, `provincia`) VALUES
('Madrid', 'madrid', 'Madrid'),
('Barcelona', 'barcelona', 'Barcelona'),
('Valencia', 'valencia', 'Valencia'),
('Málaga', 'malaga', 'Málaga'),
('Sevilla', 'sevilla', 'Sevilla'),
('Alicante', 'alicante', 'Alicante');

-- Propiedades de ejemplo
INSERT INTO `propiedades` (`titulo`, `slug`, `descripcion`, `tipo_operacion`, `tipo_inmueble`, `precio`, `superficie`, `habitaciones`, `banos`, `planta`, `ascensor`, `terraza`, `garaje`, `aire_acondicionado`, `amueblado`, `certificacion_energetica`, `ciudad`, `zona_barrio`, `lat`, `lng`, `destacada`, `activa`) VALUES
('Piso luminoso con terraza en Chamberí', 'piso-luminoso-terraza-chamberi-madrid', 'Precioso piso reformado en pleno corazón de Chamberí. Amplia terraza con vistas despejadas, cocina equipada, suelo de madera y calefacción central. Zona inmejorable con todos los servicios: transporte, colegios, supermercados y parques a menos de 5 minutos.', 'venta', 'piso', 385000.00, 95, 3, 2, '3ª', 1, 1, 0, 1, 0, 'D', 'Madrid', 'Chamberí', 40.4433, -3.7009, 1, 1),

('Ático dúplex panorámico en Eixample', 'atico-duplex-panoramico-eixample-barcelona', 'Espectacular ático dúplex con terraza de 40m² y vistas al Tibidabo. Acabados de lujo, domótica, suelo radiante, aire en todas las estancias. Dos plazas de garaje y trastero incluidos en el precio.', 'venta', 'atico', 620000.00, 120, 3, 2, '8ª', 1, 1, 1, 1, 1, 'B', 'Barcelona', 'Eixample', 41.3925, 2.1638, 1, 1),

('Estudio moderno en Ruzafa', 'estudio-moderno-ruzafo-valencia', 'Estudio totalmente reformado en la zona más de moda de Valencia. Diseño abierto, cocina americana equipada, baño con ducha de obra. Perfecto para inversión o primera vivienda.', 'venta', 'estudio', 145000.00, 42, 1, 1, '2ª', 0, 0, 0, 1, 1, 'C', 'Valencia', 'Ruzafa', 39.4697, -0.3763, 0, 1),

('Piso amplio alquiler en Malasaña', 'piso-amplio-alquiler-malasoana-madrid', 'Bonito piso en alquiler en el barrio más vibrante de Madrid. Tres habitaciones exteriores, salón amplio con balcón, cocina independiente equipada. Gastos de comunidad incluidos en el precio.', 'alquiler', 'piso', 1500.00, 88, 3, 1, '1ª', 1, 0, 0, 1, 1, 'E', 'Madrid', 'Malasaña', 40.4255, -3.7025, 1, 1),

('Chalet independiente con piscina en Pedralbes', 'chalet-independiente-piscina-pedralbes-barcelona', 'Magnífico chalet independiente con jardín privado y piscina. Cuatro dormitorios, tres baños, amplio salón con chimenea, garaje para dos coches. Urbanización privada con seguridad 24h.', 'venta', 'chalet', 890000.00, 280, 4, 3, 'Planta baja', 0, 1, 1, 1, 0, 'C', 'Barcelona', 'Pedralbes', 41.3947, 2.1186, 1, 1),

('Local comercial en centro de Málaga', 'local-comercial-centro-malaga', 'Local comercial en ubicación privilegiada del centro histórico de Málaga. Amplio escaparate, dos plantas, almacén en sótano. Ideal para retail, restauración o showroom.', 'alquiler', 'local', 2200.00, 150, 0, 2, 'Bajo', 0, 0, 0, 1, 0, NULL, 'Málaga', 'Centro', 36.7213, -4.4214, 0, 1),

('Piso reformado en Triana', 'piso-reformado-triana-sevilla', 'Piso en segunda planta con vistas al Guadalquivir, recién reformado con materiales de primera calidad. Dos dormitorios, baño completo, cocina abierta, patio andaluz privado.', 'venta', 'piso', 198000.00, 75, 2, 1, '2ª', 0, 0, 0, 1, 1, 'D', 'Sevilla', 'Triana', 37.3829, -6.0034, 0, 1),

('Dúplex con vistas al mar en Alicante', 'duplex-vistas-mar-alicante', 'Impresionante dúplex de nueva construcción a solo 200 metros de la playa. Tres dormitorios, dos baños, amplio salón-comedor, terraza de 25m² con vistas al Mediterráneo. Piscina comunitaria.', 'venta', 'duplex', 335000.00, 110, 3, 2, '5ª-6ª', 1, 1, 1, 1, 0, 'A', 'Alicante', 'Playa de San Juan', 38.3577, -0.4786, 1, 1),

('Oficina moderna en Castellana', 'oficina-moderna-castellana-madrid', 'Oficina de diseño en pleno Paseo de la Castellana. Espacio diáfano con posibilidad de dividir, mobiliario incluido, fibra óptica, aire centralizado y control de acceso. Zona de aparcamiento disponible.', 'alquiler', 'oficina', 2800.00, 120, 0, 2, '4ª', 1, 0, 1, 1, 1, NULL, 'Madrid', 'Chamartín', 40.4619, -3.6875, 0, 1),

('Casa adosada con jardín en Nervión', 'casa-adosada-jardin-nervion-sevilla', 'Preciosa casa adosada en una de las mejores zonas de Sevilla. Tres plantas, jardín privado, garaje, trastero. Comunidad con piscina y zonas verdes. Cerca del estadio y centros comerciales.', 'alquiler', 'casa', 1800.00, 160, 4, 2, '3 plantas', 0, 1, 1, 1, 1, 'C', 'Sevilla', 'Nervión', 37.3856, -5.9723, 0, 1);
