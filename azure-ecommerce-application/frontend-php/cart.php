<?php
require __DIR__ . '/bootstrap.php';
$userId = currentUserId();
$raw = redisClient()->hGetAll("cart:$userId");
$catalog = [];
foreach (products() as $product) $catalog[(string) $product['id']] = $product;
$items = [];
$total = 0;
foreach ($raw as $id => $quantity) {
    if (!isset($catalog[$id])) continue;
    $product = $catalog[$id];
    $line = (float) $product['price'] * (int) $quantity;
    $total += $line;
    $items[] = ['product_id' => (int) $id, 'name' => $product['name'], 'quantity' => (int) $quantity, 'unit_price' => (float) $product['price'], 'line' => $line];
}
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $items) {
    $payload = ['user_id' => $userId, 'items' => array_map(fn($item) => ['product_id' => (string) $item['product_id'], 'quantity' => $item['quantity'], 'price' => $item['unit_price']], $items), 'total_amount' => $total];
    $result = apiRequest('POST', rtrim(envv('ORDER_SERVICE_URL', 'http://localhost:8000'), '/') . '/api/orders', $payload);
    if ($result['status'] === 201) {
        $message = 'Tạo đơn hàng thành công. Mã đơn: ' . ($result['body']['data']['id'] ?? '');
        redisClient()->del("cart:$userId");
    } else {
        $message = 'Order Service lỗi HTTP ' . $result['status'];
    }
}
page('Giỏ hàng', function () use ($items, $total, $message) {
    if ($message) echo '<p class="success">' . htmlspecialchars($message) . '</p>';
    if (!$items) { echo '<p>Giỏ hàng trống.</p>'; return; }
?>
<table>
  <tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr>
  <?php foreach ($items as $item): ?>
  <tr><td><?= htmlspecialchars($item['name']) ?></td><td><?= $item['quantity'] ?></td><td><?= number_format($item['unit_price'], 0, ',', '.') ?></td><td><?= number_format($item['line'], 0, ',', '.') ?></td></tr>
  <?php endforeach; ?>
  <tr><th colspan="3">Tổng</th><th><?= number_format($total, 0, ',', '.') ?> đ</th></tr>
</table>
<form method="post"><p><button>Tạo đơn hàng</button></p></form>
<?php
});
