<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /', true, 303);
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;
if (!$productId || $quantity < 1) {
    http_response_code(422);
    exit('Dữ liệu sản phẩm hoặc số lượng không hợp lệ');
}

$product = findProduct($productId);
if (!$product) {
    http_response_code(404);
    exit('Sản phẩm không tồn tại');
}

$userId = currentUserId();
$key = "cart:$userId";
$redis = redisClient();
$newQty = $quantity;
if ($redis !== null) {
    $newQty = (int) $redis->hIncrBy($key, (string) $productId, $quantity);
    $redis->expire($key, 86400);
} else {
    $_SESSION['fallback_cart'][$productId] = (int) ($_SESSION['fallback_cart'][$productId] ?? 0) + $quantity;
    $newQty = $_SESSION['fallback_cart'][$productId];
}

page('Đã thêm vào giỏ hàng', function () use ($product, $quantity, $newQty, $key) {
?>
<div class="card" style="max-width:620px;margin:0 auto">
  <div class="order-banner-head" style="margin-bottom:14px">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
    <h2 style="margin:0;font-size:18px">Thêm sản phẩm vào giỏ hàng thành công</h2>
  </div>

  <p style="margin:0 0 18px;color:var(--text-secondary)">
    Đã lưu <strong><?= (int) $quantity ?> × <?= htmlspecialchars($product['name']) ?></strong> vào bộ nhớ tạm Azure Cache for Redis.
  </p>

  <div class="meta-grid">
    <div class="meta-item">
      <div class="meta-label">Redis Cart Key</div>
      <div class="meta-value"><code><?= htmlspecialchars($key) ?></code></div>
    </div>
    <div class="meta-item">
      <div class="meta-label">Tổng SL sản phẩm này trong giỏ</div>
      <div class="meta-value"><?= (int) $newQty ?> sản phẩm</div>
    </div>
    <div class="meta-item">
      <div class="meta-label">Đơn giá</div>
      <div class="meta-value" style="color:var(--accent)"><?= number_format((float) $product['price'], 0, ',', '.') ?> đ</div>
    </div>
  </div>

  <div class="actions">
    <a class="button lg" href="/cart.php">Xem giỏ hàng & Đặt mua</a>
    <a class="button secondary lg" href="/">Tiếp tục mua sắm</a>
  </div>
</div>
<?php
});
