<?php
require __DIR__ . '/bootstrap.php';

$userId = currentUserId();
$redis = redisClient();
$raw = $redis !== null ? ($redis->hGetAll("cart:$userId") ?: []) : ($_SESSION['fallback_cart'] ?? []);

$catalog = [];
foreach (products() as $product) {
    $catalog[(string) $product['id']] = $product;
}

$items = [];
$total = 0.0;
foreach ($raw as $id => $quantity) {
    $sid = (string) $id;
    if (!isset($catalog[$sid])) {
        continue;
    }
    $product = $catalog[$sid];
    $qty = (int) $quantity;
    $line = (float) $product['price'] * $qty;
    $total += $line;
    $items[] = [
        'product_id' => (int) $id,
        'name' => $product['name'],
        'quantity' => $qty,
        'unit_price' => (float) $product['price'],
        'line' => $line,
    ];
}

$orderResult = null;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $items) {
    $payload = [
        'user_id' => $userId,
        'items' => array_map(
            fn($item) => [
                'product_id' => (string) $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['unit_price'],
            ],
            $items
        ),
        'total_amount' => $total,
    ];
    $result = apiRequest('POST', rtrim(envv('ORDER_SERVICE_URL', 'http://localhost:8000'), '/') . '/api/orders', $payload);
    if ($result['status'] === 201) {
        $orderResult = $result['body'];
        if ($redis !== null) {
            $redis->del("cart:$userId");
        }
        unset($_SESSION['fallback_cart']);
        $items = [];
        $total = 0.0;
    } else {
        $detail = $result['body']['detail'] ?? ('HTTP ' . $result['status']);
        $errorMsg = 'Không thể tạo đơn hàng qua Order Service: ' . (is_string($detail) ? $detail : json_encode($detail));
    }
}

page('Giỏ hàng & Đặt mua sản phẩm', function () use ($items, $total, $orderResult, $errorMsg, $userId) {
    if ($orderResult !== null):
        $version = (string) ($orderResult['service_version'] ?? 'unknown');
        $badgeClass = str_contains($version, 'green') ? 'green' : 'blue';
        $orderId = (string) ($orderResult['data']['id'] ?? '');
?>
<div class="success">
  <h3>Đặt mua sản phẩm thành công!</h3>
  <p>Mã đơn hàng (Cosmos DB Document ID): <code><?= htmlspecialchars($orderId) ?></code></p>
  <p>Tài khoản đã xác thực nội bộ qua <code>user-svc:8080</code>: <strong>User ID #<?= htmlspecialchars($userId) ?></strong></p>
  <p>
    Phiên bản Order Service xử lý (Blue/Green):
    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($version) ?></span>
  </p>
  <p><a class="button" href="/">Tiếp tục mua hàng</a></p>
</div>
<?php
    endif;

    if ($errorMsg !== null):
?>
<p class="error"><?= htmlspecialchars($errorMsg) ?></p>
<?php
    endif;

    if (!$items):
?>
<div class="card">
  <p>Giỏ hàng của bạn hiện đang trống (Redis Key: <code>cart:<?= htmlspecialchars($userId) ?></code>).</p>
  <p><a class="button" href="/">Quay lại Trang chủ chọn sản phẩm</a></p>
</div>
<?php
        return;
    endif;
?>
<p><span class="badge">Redis Cart Key: cart:<?= htmlspecialchars($userId) ?></span></p>
<table>
  <tr><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>
  <?php foreach ($items as $item): ?>
  <tr>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td><?= (int) $item['quantity'] ?></td>
    <td><?= number_format($item['unit_price'], 0, ',', '.') ?> đ</td>
    <td><?= number_format($item['line'], 0, ',', '.') ?> đ</td>
  </tr>
  <?php endforeach; ?>
  <tr>
    <th colspan="3">Tổng thanh toán</th>
    <th class="price"><?= number_format($total, 0, ',', '.') ?> đ</th>
  </tr>
</table>
<form method="post" style="margin-top:18px">
  <button type="submit">Mua sản phẩm (Gửi sang Order Service → Xác thực User Service → Lưu Cosmos DB)</button>
</form>
<?php
});
