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
        'image_url' => $product['image_url'],
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

page(
    'Giỏ hàng & Đặt mua sản phẩm',
    function () use ($items, $total, $orderResult, $errorMsg, $userId) {
        if ($orderResult !== null):
            $version = (string) ($orderResult['service_version'] ?? 'unknown');
            $badgeClass = str_contains(strtolower($version), 'green') ? 'green' : 'blue';
            $orderId = (string) ($orderResult['data']['id'] ?? '');
?>
<div class="order-banner">
  <div class="order-banner-head">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
    <h2>Đặt mua sản phẩm thành công!</h2>
  </div>

  <div class="meta-grid">
    <div class="meta-item">
      <div class="meta-label">Mã đơn hàng (Cosmos DB Document ID)</div>
      <div class="meta-value"><code><?= htmlspecialchars($orderId) ?></code></div>
    </div>
    <div class="meta-item">
      <div class="meta-label">Xác thực liên dịch vụ (user-svc:8080)</div>
      <div class="meta-value">User ID #<?= htmlspecialchars($userId) ?> · Hợp lệ</div>
    </div>
    <div class="meta-item">
      <div class="meta-label">Phiên bản Order Service (Blue/Green)</div>
      <div class="meta-value"><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($version) ?></span></div>
    </div>
  </div>

  <div class="actions" style="margin-top:0">
    <a class="button" href="/">Tiếp tục mua hàng</a>
  </div>
</div>
<?php
        endif;

        if ($errorMsg !== null):
?>
<div class="alert error"><?= htmlspecialchars($errorMsg) ?></div>
<?php
        endif;

        if (!$items):
?>
<div class="card empty-state">
  <div class="empty-icon" aria-hidden="true">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
  </div>
  <h2 style="margin:0 0 8px;font-size:18px;font-weight:600">Giỏ hàng của bạn đang trống</h2>
  <p style="margin:0 0 20px;color:var(--text-secondary)">
    Chưa có sản phẩm nào trong khóa Redis <code>cart:<?= htmlspecialchars($userId) ?></code>.
  </p>
  <a class="button lg" href="/">Quay lại Trang chủ chọn sản phẩm</a>
</div>
<?php
            return;
        endif;

        $totalQty = array_sum(array_column($items, 'quantity'));
?>
<div class="cart-layout">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Sản phẩm</th>
          <th class="num">Số lượng</th>
          <th class="num">Đơn giá</th>
          <th class="num">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:12px">
              <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" style="width:48px;height:36px;object-fit:cover;border-radius:6px;flex-shrink:0">
              <a href="/detail.php?id=<?= (int) $item['product_id'] ?>" style="color:var(--text-primary);text-decoration:none;font-weight:600"><?= htmlspecialchars($item['name']) ?></a>
            </div>
          </td>
          <td class="num"><?= (int) $item['quantity'] ?></td>
          <td class="num"><?= number_format($item['unit_price'], 0, ',', '.') ?> đ</td>
          <td class="num" style="font-weight:600"><?= number_format($item['line'], 0, ',', '.') ?> đ</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <aside class="card">
    <h2 style="margin:0 0 14px;font-size:17px;font-weight:600">Tóm tắt đơn hàng</h2>
    <div class="summary-row">
      <span>Redis Cart Key</span>
      <code>cart:<?= htmlspecialchars($userId) ?></code>
    </div>
    <div class="summary-row">
      <span>Tài khoản đặt mua</span>
      <strong>User ID #<?= htmlspecialchars($userId) ?></strong>
    </div>
    <div class="summary-row">
      <span>Tổng số lượng</span>
      <span><?= (int) $totalQty ?> sản phẩm</span>
    </div>
    <div class="summary-total">
      <span>Tổng thanh toán</span>
      <span class="price" style="font-size:20px"><?= number_format($total, 0, ',', '.') ?> đ</span>
    </div>

    <form method="post">
      <button class="lg full" type="submit">Đặt mua sản phẩm</button>
    </form>
    <p style="margin:12px 0 0;font-size:12px;color:var(--text-tertiary);text-align:center">
      Luồng xử lý: <code>order-svc</code> → xác thực <code>user-svc</code> → lưu <code>Cosmos DB</code> → Auto-ETL
    </p>
  </aside>
</div>
<?php
    },
    'Kiểm tra danh sách sản phẩm lưu trên Azure Cache for Redis và gửi đơn hàng sang Order Service.'
);
