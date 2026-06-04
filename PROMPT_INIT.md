# Project Starter Blueprint: SEO-Optimized PHP MVC with SSR & Tailwind CSS

This document serves as a comprehensive blueprint for creating a new web application using a robust, custom PHP MVC structure. It is specifically designed for **Server-Side Rendering (SSR)** to ensure maximum **SEO compatibility** with Google Search Engine, while being optimized for deployment on shared hosting or local environments.

## 1. Project Overview & Tech Stack
- **Architecture:** Custom PHP MVC (Model-View-Controller) with native SSR.
- **SEO & SSR:** Built-in dynamic meta tag management, semantic HTML focus, and JSON-LD structured data support.
- **Frontend:** Tailwind CSS (via CDN or compiled), Alpine.js, FontAwesome 6, SweetAlert2.
- **Backend:** PHP 8.x+ with PDO for database interactions.
- **Routing:** Custom array-based routing with a centralized `routes/web.php`.
- **UI Language:** Primarily Lao (UTF-8), using "Noto Sans Lao" font.
- **Deployment Ready:** Handles subdirectory paths automatically for local (XAMPP) and production (InfinityFree) environments.

## 2. Directory Structure
```text
/
├── .env                    # Environment variables (DB, App settings)
├── .htaccess               # Root redirect to public/
├── index.php               # Root fallback entry point
├── tailwind.config.js      # Tailwind configuration
├── app/
│   ├── Controllers/        # Business logic controllers
│   ├── Core/               # Core framework components (Database, Router)
│   ├── Helpers/            # Helper functions (SEO, View, URL)
│   ├── Models/             # Database models
│   └── Services/           # External service integrations
├── public/
│   ├── .htaccess           # URL rewriting for clean URLs
│   ├── index.php           # Front Controller (Main entry point)
│   ├── robots.txt          # SEO instructions for search engines
│   ├── sitemap.xml         # Search engine sitemap
│   ├── css/                # Compiled CSS
│   └── assets/             # Static assets (images, logos)
├── routes/
│   └── web.php             # Route definitions
├── views/
│   ├── components/         # Reusable UI fragments
│   ├── layouts/            # Page templates (main.php)
│   └── pages/              # Specific page views
```

## 3. Core Implementation Blueprints

### A. View Engine with SEO Support (`app/Helpers/view.php`)
```php
<?php
function view($path, $data = []) {
    // Default SEO Data
    $seo = array_merge([
        'title' => 'Default Title | Brand Name',
        'description' => 'Default description for SEO.',
        'keywords' => 'keyword1, keyword2, lao, shop',
        'image' => url('assets/default-og.jpg'),
        'canonical' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
        'json_ld' => null
    ], $data['seo'] ?? []);

    extract($data);
    $viewFile = __DIR__ . "/../../views/" . str_replace('.', '/', $path) . ".php";
    
    ob_start();
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "View file not found: $viewFile";
    }
    $content = ob_get_clean();
    
    require __DIR__ . "/../../views/layouts/main.php";
}

function url($path = '/') {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Detect if running on localhost (XAMPP) or production
    $basePath = (strpos($host, 'localhost') !== false) ? '/buddhaword' : '';
    return $basePath . '/' . ltrim($path, '/');
}
```

### B. SEO-Friendly Layout (`views/layouts/main.php`)
```php
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title><?= htmlspecialchars($seo['title']) ?></title>
    <meta name="title" content="<?= htmlspecialchars($seo['title']) ?>">
    <meta name="description" content="<?= htmlspecialchars($seo['description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seo['keywords']) ?>">
    <link rel="canonical" href="<?= $seo['canonical'] ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $seo['canonical'] ?>">
    <meta property="og:title" content="<?= htmlspecialchars($seo['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['description']) ?>">
    <meta property="og:image" content="<?= $seo['image'] ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $seo['canonical'] ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($seo['title']) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($seo['description']) ?>">
    <meta property="twitter:image" content="<?= $seo['image'] ?>">

    <!-- Structured Data (JSON-LD) -->
    <?php if ($seo['json_ld']): ?>
    <script type="application/ld+json">
        <?= json_encode($seo['json_ld'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- PWA -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="#3b82f6">
</head>
<body class="font-['Noto_Sans_Lao'] bg-gray-50">
    <!-- Main Content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Alpine.js & Plugins -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
```

### C. Example Controller with SEO (`app/Controllers/ProductController.php`)
```php
<?php
namespace App\Controllers;
class ProductController {
    public function show($id) {
        $product = ['name' => 'Lao Silk Shirt', 'price' => 250000]; // Mock data
        
        return view('pages.product_detail', [
            'product' => $product,
            'seo' => [
                'title' => $product['name'] . ' | Shop Name',
                'description' => 'Buy ' . $product['name'] . ' at the best price in Laos.',
                'json_ld' => [
                    "@context" => "https://schema.org/",
                    "@type" => "Product",
                    "name" => $product['name'],
                    "offers" => [
                        "@type" => "Offer",
                        "price" => $product['price'],
                        "priceCurrency" => "LAK"
                    ]
                ]
            ]
        ]);
    }
}
```

## 4. SEO & Performance Optimization
- **Clean URLs:** Managed via `.htaccess` and `Router.php` to ensure search engines can crawl logical paths.
- **Sitemap:** Automatically generate or manually maintain `public/sitemap.xml` listing all important URIs.
- **Robots.txt:** Guide search bots in `public/robots.txt`.
- **SSR (Server-Side Rendering):** All content is rendered on the server, ensuring Googlebot sees the full HTML immediately without needing to execute complex JavaScript.
- **Lazy Loading:** Use native `loading="lazy"` for images to improve PageSpeed scores.

## 5. Deployment & Subdirectory Handling
The `url()` helper in `app/Helpers/view.php` and the root `.htaccess` are configured to handle both root domain deployments and subdirectory environments (like `localhost/buddhaword`).

## 6. Project Initialization Steps
1. Create the directory structure.
2. Setup `public/index.php` with the autoloader.
3. Implement `app/Core/Router.php` and `app/Helpers/view.php` (with SEO logic).
4. Create `views/layouts/main.php` with dynamic meta tags and JSON-LD support.
5. Setup `public/robots.txt` and a basic `public/sitemap.xml`.
6. Define routes in `routes/web.php`.
7. Start building SEO-rich controllers and views.
