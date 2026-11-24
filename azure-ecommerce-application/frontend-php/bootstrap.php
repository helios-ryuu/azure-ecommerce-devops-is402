<?php
declare(strict_types=1);

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function envv(string $key, string $default = ''): string {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
            envv('DB_HOST', 'localhost'),
            envv('DB_PORT', '5432'),
            envv('DB_NAME', 'ecommerce'),
            envv('DB_SSLMODE', 'prefer')
        );
        $pdo = new PDO(
            $dsn,
            envv('DB_USER', 'ecommerce'),
            envv('DB_PASSWORD', 'ecommerce'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function redisClient(): ?Redis {
    static $redis = null;
    static $initialized = false;
    if ($initialized) {
        return $redis;
    }
    $initialized = true;
    try {
        $client = new Redis();
        $host = envv('REDIS_HOST', 'localhost');
        $port = (int) envv('REDIS_PORT', '6379');
        $useTls = filter_var(envv('REDIS_TLS', $port === 6380 ? 'true' : 'false'), FILTER_VALIDATE_BOOLEAN);
        $targetHost = ($useTls && !str_starts_with($host, 'tls://')) ? 'tls://' . $host : $host;
        $client->connect($targetHost, $port, 2.0);
        $password = envv('REDIS_PASSWORD', '');
        if ($password !== '') {
            $client->auth($password);
        }
        $redis = $client;
    } catch (Throwable $e) {
        error_log('Redis connection warning: ' . $e->getMessage());
        $redis = null;
    }
    return $redis;
}

function productsWithMeta(): array {
    $start = microtime(true);
    $redis = redisClient();
    $key = 'catalog:products';
    if ($redis !== null) {
        try {
            $cached = $redis->get($key);
            if (is_string($cached) && $cached !== '') {
                $items = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);
                $elapsed = round((microtime(true) - $start) * 1000, 2);
                return [
                    'items' => $items,
                    'source' => 'Redis Cache Hit (catalog:products)',
                    'cached' => true,
                    'latency_ms' => $elapsed,
                ];
            }
        } catch (Throwable $e) {
            error_log('Redis read warning: ' . $e->getMessage());
        }
    }

    $rows = db()->query('SELECT id, name, description, price, image_url FROM products ORDER BY id')->fetchAll();
    if ($redis !== null) {
        try {
            $redis->setex($key, 300, json_encode($rows, JSON_THROW_ON_ERROR));
        } catch (Throwable $e) {
            error_log('Redis write warning: ' . $e->getMessage());
        }
    }
    $elapsed = round((microtime(true) - $start) * 1000, 2);
    return [
        'items' => $rows,
        'source' => 'PostgreSQL v18 (Cache Miss → Nạp vào Redis TTL 300s)',
        'cached' => false,
        'latency_ms' => $elapsed,
    ];
}

function products(): array {
    return productsWithMeta()['items'];
}

function findProduct(int $id): ?array {
    $meta = productsWithMeta();
    foreach ($meta['items'] as $item) {
        if ((int) $item['id'] === $id) {
            $item['_cache_source'] = $meta['source'];
            $item['_cached'] = $meta['cached'];
            $item['_latency_ms'] = $meta['latency_ms'];
            return $item;
        }
    }
    $stmt = db()->prepare('SELECT id, name, description, price, image_url FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['_cache_source'] = 'PostgreSQL v18 Direct Query';
    $row['_cached'] = false;
    $row['_latency_ms'] = 0.0;
    return $row;
}

function currentUserId(): string {
    return (string) ($_SESSION['user']['id'] ?? envv('DEMO_USER_ID', '1'));
}

function cartItemCount(): int {
    try {
        $userId = currentUserId();
        $redis = redisClient();
        $raw = $redis !== null ? ($redis->hGetAll("cart:$userId") ?: []) : ($_SESSION['fallback_cart'] ?? []);
        $sum = 0;
        foreach ($raw as $qty) {
            $sum += max(0, (int) $qty);
        }
        return $sum;
    } catch (Throwable $e) {
        return 0;
    }
}

function activeOrderVersion(): string {
    static $cachedVersion = null;
    if ($cachedVersion !== null) {
        return $cachedVersion;
    }
    $cachedVersion = 'v1-blue';
    try {
        $url = rtrim(envv('ORDER_SERVICE_URL', 'http://localhost:8000'), '/') . '/health';
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 0.8,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if (is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded) && !empty($decoded['version'])) {
                $cachedVersion = (string) $decoded['version'];
            }
        }
    } catch (Throwable $e) {
        // Fallback giữ nguyên v1-blue để không ảnh hưởng luồng giao diện
    }
    return $cachedVersion;
}

function apiRequest(string $method, string $url, ?array $payload = null): array {
    $options = [
        'method' => $method,
        'ignore_errors' => true,
        'timeout' => 5,
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
    ];
    if ($payload !== null) {
        $options['content'] = json_encode($payload, JSON_THROW_ON_ERROR);
    }
    $body = @file_get_contents($url, false, stream_context_create(['http' => $options]));
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s(\d{3})\s/', $statusLine, $matches);
    return [
        'status' => isset($matches[1]) ? (int) $matches[1] : 0,
        'body' => is_string($body) && $body !== '' ? (json_decode($body, true) ?? []) : [],
    ];
}

function page(string $title, callable $content, string $subtitle = ''): void {
    $orderVersion = activeOrderVersion();
    $isGreen = str_contains(strtolower($orderVersion), 'green');
    $themeClass = $isGreen ? 'theme-green' : 'theme-blue';
    $cartCount = cartItemCount();
    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    ?><!doctype html>
<html lang="vi" class="<?= $themeClass ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?> — IS402 Cloud Store</title>
  <style>
    :root {
      --bg-canvas: #f3f3f3;
      --bg-mica-tint: rgba(0, 120, 212, 0.045);
      --surface-card: #ffffff;
      --surface-subtle: #fafafa;
      --surface-acrylic: rgba(255, 255, 255, 0.82);
      --border-hairline: rgba(0, 0, 0, 0.068);
      --border-strong: rgba(0, 0, 0, 0.14);
      --text-primary: #1a1a1a;
      --text-secondary: #5c5c5c;
      --text-tertiary: #767676;

      --accent: #0078d4;
      --accent-hover: #106ebe;
      --accent-pressed: #005a9e;
      --accent-soft: #eff6fc;
      --accent-border: rgba(0, 120, 212, 0.22);

      --success-bg: #dff6dd;
      --success-text: #107c10;
      --success-border: rgba(16, 124, 16, 0.22);
      --warn-bg: #fff4ce;
      --warn-text: #8a5600;
      --warn-border: rgba(138, 86, 0, 0.2);
      --error-bg: #fde7e9;
      --error-text: #a4262c;
      --error-border: rgba(164, 38, 44, 0.22);

      --radius-sm: 6px;
      --radius-md: 8px;
      --radius-lg: 12px;
      --shadow-rest: 0 1px 2px rgba(0, 0, 0, 0.04), 0 0 2px rgba(0, 0, 0, 0.04);
      --shadow-hover: 0 8px 18px rgba(0, 0, 0, 0.07), 0 2px 6px rgba(0, 0, 0, 0.04);
      --font-sans: 'Segoe UI Variable', 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Inter', Roboto, sans-serif;
    }

    html.theme-green {
      --bg-mica-tint: rgba(16, 124, 16, 0.055);
      --accent: #107c10;
      --accent-hover: #0e6a0e;
      --accent-pressed: #0b530b;
      --accent-soft: #ecfaec;
      --accent-border: rgba(16, 124, 16, 0.24);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      font-family: var(--font-sans);
      font-size: 14px;
      line-height: 1.55;
      color: var(--text-primary);
      background-color: var(--bg-canvas);
      background-image: radial-gradient(ellipse 80% 380px at 50% -120px, var(--bg-mica-tint), transparent 72%);
      -webkit-font-smoothing: antialiased;
    }

    code {
      font-family: 'Cascadia Code', 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
      font-size: 0.86em;
      background: rgba(0, 0, 0, 0.045);
      padding: 2px 6px;
      border-radius: 4px;
      color: var(--text-primary);
      word-break: break-all;
    }

    /* Sticky Acrylic Navigation */
    .topbar {
      position: sticky;
      top: 0;
      z-index: 50;
      background: var(--surface-acrylic);
      backdrop-filter: blur(22px) saturate(180%);
      -webkit-backdrop-filter: blur(22px) saturate(180%);
      border-bottom: 1px solid var(--border-hairline);
    }

    .topbar-inner {
      max-width: 1160px;
      margin: 0 auto;
      padding: 0 24px;
      height: 60px;
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .brand {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: var(--text-primary);
      font-weight: 600;
      font-size: 15px;
      letter-spacing: -0.01em;
      white-space: nowrap;
    }

    .brand-icon {
      width: 28px;
      height: 28px;
      border-radius: var(--radius-sm);
      background: var(--accent);
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .nav-link {
      position: relative;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 7px 12px;
      border-radius: var(--radius-sm);
      text-decoration: none;
      color: var(--text-secondary);
      font-weight: 500;
      transition: background 140ms ease, color 140ms ease;
    }

    .nav-link:hover {
      background: rgba(0, 0, 0, 0.04);
      color: var(--text-primary);
    }

    .nav-link.active {
      color: var(--text-primary);
      background: rgba(0, 0, 0, 0.045);
      font-weight: 600;
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      left: 12px;
      right: 12px;
      bottom: 2px;
      height: 2.5px;
      border-radius: 99px;
      background: var(--accent);
    }

    .cart-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 20px;
      height: 20px;
      padding: 0 6px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      background: var(--accent);
      color: #fff;
    }

    .topbar-right {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .env-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      background: var(--accent-soft);
      color: var(--accent);
      border: 1px solid var(--accent-border);
      white-space: nowrap;
    }

    .env-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--accent);
    }

    .account-box {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--text-secondary);
      white-space: nowrap;
    }

    .account-box a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
    }

    .account-box a:hover { text-decoration: underline; }

    /* Main Layout */
    .container {
      width: 100%;
      max-width: 1160px;
      margin: 0 auto;
      padding: 32px 24px 56px;
      flex: 1;
    }

    .page-header {
      margin-bottom: 24px;
    }

    .page-header h1 {
      margin: 0;
      font-size: 26px;
      font-weight: 600;
      letter-spacing: -0.02em;
      color: var(--text-primary);
    }

    .page-subtitle {
      margin: 6px 0 0;
      color: var(--text-secondary);
      font-size: 14px;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--text-secondary);
      margin-bottom: 14px;
    }

    .breadcrumb a {
      color: var(--accent);
      text-decoration: none;
    }

    .breadcrumb a:hover { text-decoration: underline; }

    /* Fluent InfoBar / Badges */
    .infobar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 14px;
      margin-bottom: 24px;
      border-radius: var(--radius-md);
      background: var(--surface-card);
      border: 1px solid var(--border-hairline);
      box-shadow: var(--shadow-rest);
      font-size: 13px;
      color: var(--text-secondary);
    }

    .infobar-left {
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .status-dot.hit { background: #107c10; box-shadow: 0 0 0 3px rgba(16, 124, 16, 0.16); }
    .status-dot.miss { background: #d83b01; box-shadow: 0 0 0 3px rgba(216, 59, 1, 0.16); }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      background: var(--accent-soft);
      color: var(--accent);
      border: 1px solid var(--accent-border);
    }

    .badge.blue {
      background: #eff6fc;
      color: #0078d4;
      border-color: rgba(0, 120, 212, 0.25);
    }

    .badge.green {
      background: #ecfaec;
      color: #107c10;
      border-color: rgba(16, 124, 16, 0.25);
    }

    .badge.neutral {
      background: var(--surface-subtle);
      color: var(--text-secondary);
      border-color: var(--border-hairline);
    }

    /* Cards & Product Grid */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(256px, 1fr));
      gap: 20px;
    }

    .card {
      background: var(--surface-card);
      border: 1px solid var(--border-hairline);
      border-radius: var(--radius-lg);
      padding: 20px;
      box-shadow: var(--shadow-rest);
    }

    .product-card {
      display: flex;
      flex-direction: column;
      padding: 14px;
      transition: transform 180ms cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 180ms ease, border-color 180ms ease;
    }

    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-hover);
      border-color: var(--accent-border);
    }

    .product-thumb {
      display: block;
      width: 100%;
      aspect-ratio: 16 / 10;
      border-radius: var(--radius-md);
      overflow: hidden;
      background: #eaeef3;
      margin-bottom: 14px;
    }

    .product-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 320ms ease;
    }

    .product-card:hover .product-thumb img {
      transform: scale(1.03);
    }

    .product-title {
      margin: 0 0 6px;
      font-size: 15px;
      font-weight: 600;
      color: var(--text-primary);
      line-height: 1.35;
    }

    .product-desc {
      margin: 0 0 16px;
      font-size: 13px;
      color: var(--text-secondary);
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-footer {
      margin-top: auto;
      padding-top: 12px;
      border-top: 1px solid var(--border-hairline);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }

    .price {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: var(--accent);
      letter-spacing: -0.01em;
    }

    .price-lg {
      font-size: 24px;
      font-weight: 700;
      color: var(--accent);
      margin: 12px 0 20px;
    }

    /* Buttons (Fluent 2 Standard) */
    .button, button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: var(--radius-sm);
      border: 1px solid transparent;
      background: var(--accent);
      color: #ffffff;
      font-family: inherit;
      font-size: 13.5px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: background 120ms ease, border-color 120ms ease, transform 80ms ease;
    }

    .button:hover, button:hover {
      background: var(--accent-hover);
    }

    .button:active, button:active {
      background: var(--accent-pressed);
      transform: scale(0.99);
    }

    .button.secondary, button.secondary {
      background: #ffffff;
      color: var(--text-primary);
      border: 1px solid var(--border-strong);
      border-bottom-color: rgba(0, 0, 0, 0.22);
    }

    .button.secondary:hover, button.secondary:hover {
      background: var(--surface-subtle);
    }

    .button.lg, button.lg {
      padding: 10px 20px;
      font-size: 14px;
    }

    .button.full, button.full {
      width: 100%;
    }

    /* Detail Page Split Layout */
    .detail-layout {
      display: grid;
      grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
      gap: 28px;
      align-items: start;
    }

    .detail-media {
      background: var(--surface-card);
      border: 1px solid var(--border-hairline);
      border-radius: var(--radius-lg);
      padding: 12px;
      box-shadow: var(--shadow-rest);
    }

    .detail-media img {
      width: 100%;
      aspect-ratio: 16 / 11;
      object-fit: cover;
      border-radius: var(--radius-md);
      display: block;
    }

    /* Cart Split Layout */
    .cart-layout {
      display: grid;
      grid-template-columns: minmax(0, 1.65fr) minmax(280px, 1fr);
      gap: 24px;
      align-items: start;
    }

    .table-wrap {
      background: var(--surface-card);
      border: 1px solid var(--border-hairline);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-rest);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 14px 16px;
      text-align: left;
      border-bottom: 1px solid var(--border-hairline);
    }

    th {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--text-secondary);
      background: var(--surface-subtle);
    }

    tr:last-child td { border-bottom: none; }

    .num { text-align: right; font-variant-numeric: tabular-nums; }

    .summary-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 0;
      color: var(--text-secondary);
      font-size: 13.5px;
    }

    .summary-total {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 0 18px;
      margin-top: 8px;
      border-top: 1px solid var(--border-hairline);
      font-weight: 600;
      font-size: 15px;
    }

    /* Forms (Fluent Bottom-Accent Inputs) */
    .form-card {
      max-width: 440px;
      margin: 12px auto 0;
      background: var(--surface-card);
      border: 1px solid var(--border-hairline);
      border-radius: var(--radius-lg);
      padding: 28px;
      box-shadow: var(--shadow-rest);
    }

    .field { margin-bottom: 16px; }

    label {
      display: block;
      margin-bottom: 6px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-primary);
    }

    input {
      width: 100%;
      padding: 9px 12px;
      font-family: inherit;
      font-size: 14px;
      color: var(--text-primary);
      background: #ffffff;
      border: 1px solid var(--border-strong);
      border-bottom: 1.5px solid rgba(0, 0, 0, 0.32);
      border-radius: var(--radius-sm);
      outline: none;
      transition: border-color 140ms ease, box-shadow 140ms ease;
    }

    input:focus {
      border-color: var(--border-strong);
      border-bottom: 2px solid var(--accent);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    /* Alerts & Order Confirmation */
    .alert {
      padding: 14px 18px;
      border-radius: var(--radius-md);
      margin-bottom: 20px;
      border: 1px solid transparent;
    }

    .alert.success {
      background: var(--success-bg);
      color: var(--success-text);
      border-color: var(--success-border);
    }

    .alert.error {
      background: var(--error-bg);
      color: var(--error-text);
      border-color: var(--error-border);
    }

    .order-banner {
      background: var(--surface-card);
      border: 1px solid var(--success-border);
      border-left: 4px solid var(--success-text);
      border-radius: var(--radius-lg);
      padding: 22px 24px;
      margin-bottom: 24px;
      box-shadow: var(--shadow-rest);
    }

    .order-banner-head {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--success-text);
      margin-bottom: 16px;
    }

    .order-banner-head h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .meta-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
      margin-bottom: 18px;
    }

    .meta-item {
      background: var(--surface-subtle);
      border: 1px solid var(--border-hairline);
      border-radius: var(--radius-md);
      padding: 12px 14px;
    }

    .meta-label {
      font-size: 11.5px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--text-secondary);
      margin-bottom: 6px;
    }

    .meta-value {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--text-primary);
    }

    .empty-state {
      text-align: center;
      padding: 44px 24px;
      max-width: 520px;
      margin: 0 auto;
    }

    .empty-icon {
      width: 48px;
      height: 48px;
      margin: 0 auto 14px;
      border-radius: 50%;
      background: var(--accent-soft);
      color: var(--accent);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 18px;
    }

    .footer {
      border-top: 1px solid var(--border-hairline);
      background: rgba(255, 255, 255, 0.55);
      padding: 16px 24px;
      font-size: 12.5px;
      color: var(--text-tertiary);
    }

    .footer-inner {
      max-width: 1160px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }

    @media (max-width: 820px) {
      .detail-layout, .cart-layout { grid-template-columns: 1fr; }
      .topbar-inner { height: auto; padding: 10px 16px; flex-wrap: wrap; gap: 10px; }
      .topbar-right { width: 100%; justify-content: space-between; }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="/">
        <span class="brand-icon" aria-hidden="true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
        </span>
        <span>IS402 Cloud Store</span>
      </a>

      <nav class="nav-links" aria-label="Điều hướng chính">
        <a class="nav-link <?= ($currentScript === 'index.php' || $currentScript === 'detail.php') ? 'active' : '' ?>" href="/">Sản phẩm</a>
        <a class="nav-link <?= ($currentScript === 'cart.php' || $currentScript === 'add-to-cart.php') ? 'active' : '' ?>" href="/cart.php">
          <span>Giỏ hàng</span>
          <?php if ($cartCount > 0): ?><span class="cart-pill"><?= $cartCount ?></span><?php endif; ?>
        </a>
      </nav>

      <div class="topbar-right">
        <span class="env-pill" title="Phiên bản Order Service đang nhận lưu lượng (Blue/Green)">
          <span class="env-dot"></span>
          Order Engine: <?= htmlspecialchars($orderVersion) ?>
        </span>
        <span class="account-box">
          <?php if (isset($_SESSION['user'])): ?>
            <span><strong><?= htmlspecialchars((string) $_SESSION['user']['username']) ?></strong> (#<?= htmlspecialchars((string) $_SESSION['user']['id']) ?>)</span>
            <span>·</span>
            <a href="/logout.php">Đăng xuất</a>
          <?php else: ?>
            <span>Demo #<?= htmlspecialchars(currentUserId()) ?></span>
            <span>·</span>
            <a href="/login.php">Đăng nhập</a>
            <span>·</span>
            <a href="/register.php">Đăng ký</a>
          <?php endif; ?>
        </span>
      </div>
    </div>
  </header>

  <main class="container">
    <div class="page-header">
      <h1><?= htmlspecialchars($title) ?></h1>
      <?php if ($subtitle !== ''): ?>
        <p class="page-subtitle"><?= htmlspecialchars($subtitle) ?></p>
      <?php endif; ?>
    </div>
    <?php $content(); ?>
  </main>

  <footer class="footer">
    <div class="footer-inner">
      <span>IS402.R11 Cloud-Native E-Commerce · Microsoft Azure (Japan East)</span>
      <span>AKS Managed NGINX Ingress · PHP 8.5 · Java 25 · Python 3.14 (<strong><?= htmlspecialchars($orderVersion) ?></strong>)</span>
    </div>
  </footer>
</body>
</html>
<?php
}
