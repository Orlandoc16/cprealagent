<?php // includes/header.php ?>
<!DOCTYPE html>
<html lang="es" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1BB55B">
    
    <title><?= e($pageTitle ?? 'CP Real Agent') ?> | CP Real Agent — <?= e($pageTagline ?? 'Tu agencia inmobiliaria de confianza') ?></title>
    <meta name="description" content="<?= e($pageDesc ?? 'Encuentra tu hogar ideal. Compra, vende o alquila propiedades con CP Real Agent. Sin comisiones ocultas, trato cercano y profesional.') ?>">
    <meta name="robots" content="<?= $pageRobots ?? 'follow, index, max-snippet:-1, max-image-preview:large' ?>">
    
    <link rel="canonical" href="<?= e($canonicalUrl ?? SITE_URL) ?>">
    
    <!-- Open Graph -->
    <meta property="og:locale" content="es_ES">
    <meta property="og:type" content="<?= $ogType ?? 'website' ?>">
    <meta property="og:title" content="<?= e($pageTitle ?? 'CP Real Agent') ?>">
    <meta property="og:description" content="<?= e($pageDesc ?? 'Tu agencia inmobiliaria de confianza') ?>">
    <meta property="og:image" content="<?= e($ogImage ?? SITE_URL . '/assets/img/og-default.jpg') ?>">
    <meta property="og:url" content="<?= e($canonicalUrl ?? SITE_URL) ?>">
    <meta property="og:site_name" content="CP Real Agent">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle ?? 'CP Real Agent') ?>">
    <meta name="twitter:image" content="<?= e($ogImage ?? SITE_URL . '/assets/img/og-default.jpg') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:    { DEFAULT: '#1BB55B', dark: '#15943F', light: '#D4F5E1' },
                        accent:     { DEFAULT: '#2C3E50', dark: '#1A252F' },
                    },
                    fontFamily: {
                        sans:  ['Roboto', 'sans-serif'],
                        slab: ['Roboto Slab', 'serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.ico">
    
    <?php if (defined('GA_ID') && GA_ID): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= GA_ID ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= GA_ID ?>');
    </script>
    <?php endif; ?>
    
    <?php if (defined('META_PIXEL_ID') && META_PIXEL_ID): ?>
    <!-- Meta Pixel -->
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= META_PIXEL_ID ?>');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?= META_PIXEL_ID ?>&ev=PageView&noscript=1"/></noscript>
    <?php endif; ?>
</head>
<body class="font-sans text-accent bg-white antialiased" x-data="mobileMenu()" x-init="init()">

<!-- ============ NAVBAR ============ -->
<nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
     :class="scrolled ? 'bg-white shadow-md' : 'bg-transparent'"
     @scroll.window="scrolled = (window.pageYOffset > 50)">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 md:h-20">
            
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2">
                <span class="text-xl md:text-2xl font-slab font-bold"
                      :class="scrolled ? 'text-accent-dark' : 'text-white'">CP Real Agent</span>
            </a>
            
            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-8">
                <?php
                $navLinks = [
                    ['url' => '/', 'label' => 'Inicio'],
                    ['url' => '/venta/', 'label' => 'Comprar'],
                    ['url' => '/vender/', 'label' => 'Vender'],
                    ['url' => '/alquiler/', 'label' => 'Alquilar'],
                    ['url' => '/contacto/', 'label' => 'Contacto'],
                ];
                foreach ($navLinks as $link): ?>
                <a href="<?= $link['url'] ?>"
                   class="text-sm font-medium transition-colors hover:text-primary"
                   :class="scrolled ? 'text-accent' : 'text-white/90 hover:text-white'"><?= $link['label'] ?></a>
                <?php endforeach; ?>
            </div>
            
            <!-- Desktop CTAs -->
            <div class="hidden md:flex items-center gap-3">
                <a href="tel:<?= SITE_PHONE ?>" class="text-sm font-medium transition-colors"
                   :class="scrolled ? 'text-accent' : 'text-white'">📞 <?= SITE_PHONE ?></a>
                <a href="<?= whatsapp_link(WHATSAPP_NUMBER) ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-full bg-primary text-white hover:bg-primary-dark transition-colors">
                    💬 WhatsApp
                </a>
            </div>
            
            <!-- Mobile hamburger -->
            <button @click="open = !open" class="md:hidden p-2"
                    :class="scrolled ? 'text-accent' : 'text-white'">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-full"
         class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl z-50 md:hidden">
        <div class="flex flex-col h-full">
            <div class="p-6 border-b">
                <span class="text-xl font-slab font-bold text-accent-dark">CP Real Agent</span>
            </div>
            <div class="flex-1 p-6 space-y-4">
                <?php foreach ($navLinks as $link): ?>
                <a href="<?= $link['url'] ?>" @click="open = false"
                   class="block text-lg font-medium text-accent hover:text-primary"><?= $link['label'] ?></a>
                <?php endforeach; ?>
            </div>
            <div class="p-6 border-t space-y-3">
                <a href="tel:<?= SITE_PHONE ?>" class="block text-center py-2 text-sm font-medium text-accent">📞 <?= SITE_PHONE ?></a>
                <a href="<?= whatsapp_link(WHATSAPP_NUMBER) ?>" target="_blank"
                   class="block text-center py-2.5 text-sm font-medium rounded-full bg-primary text-white">
                    💬 WhatsApp
                </a>
            </div>
        </div>
    </div>
    <!-- Overlay -->
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>
</nav>

<script>
function mobileMenu() {
    return {
        open: false,
        scrolled: false,
        init() {
            this.scrolled = window.pageYOffset > 50;
        }
    }
}
</script>

<main class="pt-16 md:pt-20">
