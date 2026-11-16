<?php
declare(strict_types=1);

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function envv(string $key, string $default): string {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', envv('DB_HOST', 'localhost'), envv('DB_PORT', '5432'), envv('DB_NAME', 'ecommerce'));
        $pdo = new PDO($dsn, envv('DB_USER', 'ecommerce'), envv('DB_PASSWORD', 'ecommerce'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    return $pdo;
}

function redisClient(): Redis {
    static $redis;
    if (!$redis) {
        $redis = new Redis();
        $redis->connect(envv('REDIS_HOST', 'localhost'), (int) envv('REDIS_PORT', '6379'), 1.5);
    }
    return $redis;
}

function products(): array {
    $redis = redisClient();
    $key = 'products:all';
    $cached = $redis->get($key);
    if ($cached !== false) return json_decode($cached, true, 512, JSON_THROW_ON_ERROR);
    $rows = db()->query('SELECT id, name, description, price, image_url FROM products ORDER BY id')->fetchAll();
    $redis->setex($key, 60, json_encode($rows, JSON_THROW_ON_ERROR));
    return $rows;
}

function currentUserId(): string {
    return (string) ($_SESSION['user']['id'] ?? envv('DEMO_USER_ID', '1'));
}

function apiRequest(string $method, string $url, ?array $payload = null): array {
    $options = ['method' => $method, 'ignore_errors' => true, 'timeout' => 5, 'header' => "Content-Type: application/json\r\n"];
    if ($payload !== null) $options['content'] = json_encode($payload, JSON_THROW_ON_ERROR);
    $body = @file_get_contents($url, false, stream_context_create(['http' => $options]));
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s(\d{3})\s/', $statusLine, $matches);
    return ['status' => isset($matches[1]) ? (int) $matches[1] : 0, 'body' => $body ? json_decode($body, true) : []];
}

function page(string $title, callable $content): void {
    ?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=htmlspecialchars($title)?></title><style>body{font-family:system-ui;max-width:1050px;margin:30px auto;padding:0 18px;background:#f5f7fb;color:#172033}nav{display:flex;gap:16px;align-items:center;margin-bottom:24px}nav .account{margin-left:auto}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}.card,.form-card{background:#fff;border-radius:12px;padding:16px;box-shadow:0 3px 14px #0001}.form-card{max-width:460px}.card img{width:100%;aspect-ratio:3/2;object-fit:cover;border-radius:8px}.button,button{display:inline-block;background:#1267d6;color:white;border:0;border-radius:8px;padding:10px 14px;text-decoration:none;cursor:pointer}label{display:block;margin:14px 0 6px}input{box-sizing:border-box;width:100%;padding:10px;border:1px solid #bbc3d0;border-radius:7px}table{width:100%;border-collapse:collapse;background:#fff}td,th{padding:12px;border-bottom:1px solid #ddd;text-align:left}.price{font-weight:700;color:#1267d6}.error{background:#fee;padding:12px;border-radius:8px}.success{background:#e8fff0;padding:12px;border-radius:8px}</style></head><body><nav><a href="/">Sản phẩm</a><a href="/cart.php">Giỏ hàng</a><span class="account"><?php if(isset($_SESSION['user'])): ?>Xin chào, <?=htmlspecialchars($_SESSION['user']['username'])?> · <a href="/logout.php">Đăng xuất</a><?php else: ?><a href="/login.php">Đăng nhập</a> · <a href="/register.php">Đăng ký</a><?php endif; ?></span></nav><h1><?=htmlspecialchars($title)?></h1><?php $content(); ?></body></html><?php
}
