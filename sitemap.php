<?php
require_once 'db.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(prolynweb_env('SITE_URL', prolynweb_env('BASE_URL', 'https://prolyn.net')), '/');

$staticPages = [
    ['loc' => '/', 'priority' => '1.0'],
    ['loc' => '/pricing', 'priority' => '0.9'],
    ['loc' => '/moduller', 'priority' => '0.8'],
    ['loc' => '/blog', 'priority' => '0.7'],
    ['loc' => '/video', 'priority' => '0.6'],
    ['loc' => '/contact', 'priority' => '0.5'],
    ['loc' => '/register', 'priority' => '0.6'],
];
$lastmod = date('Y-m-d');

$posts = [];
try {
    $stmt = $pdo->query("SELECT slug, updated_at FROM blog_yazilari WHERE status = 'published' ORDER BY published_at DESC");
    $posts = $stmt->fetchAll();
} catch (Throwable $e) {
    // blog_yazilari okunamazsa sitemap yalnızca statik sayfalarla döner
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($staticPages as $page): ?>
    <url>
        <loc><?= htmlspecialchars($base . $page['loc']) ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($posts as $post): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/blog_detail.php?slug=' . urlencode($post['slug'])) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($post['updated_at'])) ?></lastmod>
        <priority>0.6</priority>
    </url>
<?php endforeach; ?>
</urlset>
