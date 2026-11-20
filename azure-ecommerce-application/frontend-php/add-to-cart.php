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
<div class="card" style="max-width:560px">
  <p class="success">
    Đã thêm <strong><?= (int) $quantity ?> × <?= htmlspecialchars($product['name']) ?></strong> vào giỏ hàng trên Redis (Key: <code><?= htmlspecialchars($key) ?></code>).
  </p>
  <p>Số lượng hiện tại của mặt hàng này trong giỏ: <strong><?= (int) $newQty ?></strong></p>
  <p class="price">Đơn giá: <?= number_format((float) $product['price'], 0, ',', '.') ?> đ</p>
  <p style="display:flex;gap:12px;margin-top:18px">
    <a class="button" href="/cart.php">Xem giỏ hàng & Mua sản phẩm</a>
    <a class="button secondary" href="/">Tiếp tục mua sắm</a>
  </p>
</div>
<?php
});
