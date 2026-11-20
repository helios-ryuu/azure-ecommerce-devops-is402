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
    $row['_latency_ms'] = 0.0;
    return $row;
}

function currentUserId(): string {
    return (string) ($_SESSION['user']['id'] ?? envv('DEMO_USER_ID', '1'));
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

function page(string $title, callable $content): void {
    ?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=htmlspecialchars($title)?> — IS402 Cloud E-Commerce</title><style>body{font-family:system-ui,-apple-system,sans-serif;max-width:1080px;margin:28px auto;padding:0 18px;background:#f5f7fb;color:#172033}nav{display:flex;gap:16px;align-items:center;margin-bottom:24px;background:#fff;padding:12px 18px;border-radius:12px;box-shadow:0 2px 10px #0001}nav a{color:#1267d6;text-decoration:none;font-weight:600}nav .account{margin-left:auto}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px}.card,.form-card{background:#fff;border-radius:12px;padding:16px;box-shadow:0 3px 14px #0001}.form-card{max-width:480px}.card img{width:100%;aspect-ratio:3/2;object-fit:cover;border-radius:8px}.button,button{display:inline-block;background:#1267d6;color:white;border:0;border-radius:8px;padding:10px 14px;text-decoration:none;cursor:pointer;font-weight:600}.button.secondary{background:#475569}label{display:block;margin:14px 0 6px}input{box-sizing:border-box;width:100%;padding:10px;border:1px solid #bbc3d0;border-radius:7px}table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px #0001}td,th{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left}.price{font-weight:700;color:#1267d6;font-size:1.1rem}.error{background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px}.success{background:#dcfce7;color:#166534;padding:14px 18px;border-radius:8px;margin-bottom:16px}.badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.82rem;font-weight:600;background:#e0f2fe;color:#0369a1;margin-bottom:14px}.badge.blue{background:#dbeafe;color:#1d4ed8}.badge.green{background:#dcfce7;color:#15803d}</style></head><body><nav><a href="/">Trang chủ (Sản phẩm)</a><a href="/cart.php">Giỏ hàng (Cart)</a><span class="account"><?php if(isset($_SESSION['user'])): ?>Xin chào, <strong><?=htmlspecialchars((string)$_SESSION['user']['username'])?></strong> (ID: <?=htmlspecialchars((string)$_SESSION['user']['id'])?>) · <a href="/logout.php">Đăng xuất</a><?php else: ?>Khách (Demo ID: <?=htmlspecialchars(currentUserId())?>) · <a href="/login.php">Đăng nhập</a> · <a href="/register.php">Đăng ký</a><?php endif; ?></span></nav><h1><?=htmlspecialchars($title)?></h1><?php $content(); ?></body></html><?php
}
