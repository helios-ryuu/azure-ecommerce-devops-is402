<?php
require __DIR__ . '/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = $id ? findProduct($id) : null;
if (!$product) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm');
}

page($product['name'], function () use ($product) {
    $isHit = !empty($product['_cached']);
?>
<div class="breadcrumb">
  <a href="/">Danh mục sản phẩm</a>
  <span>/</span>
  <span>Chi tiết #<?= (int) $product['id'] ?></span>
</div>

<div class="infobar">
  <div class="infobar-left">
    <span class="status-dot <?= $isHit ? 'hit' : 'miss' ?>"></span>
    <span><strong>Nguồn dữ liệu:</strong> <?= htmlspecialchars((string) ($product['_cache_source'] ?? 'Cache-Aside')) ?></span>
  </div>
  <span class="badge neutral">Thời gian đọc: <?= htmlspecialchars((string) ($product['_latency_ms'] ?? 0)) ?> ms</span>
</div>

<div class="detail-layout">
  <div class="detail-media">
    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
  </div>

  <article class="card">
    <span class="badge neutral" style="margin-bottom:10px">Mã sản phẩm #<?= (int) $product['id'] ?></span>
    <h2 style="margin:0 0 10px;font-size:22px;font-weight:600;line-height:1.3"><?= htmlspecialchars($product['name']) ?></h2>
    <p style="margin:0;color:var(--text-secondary);font-size:14.5px"><?= htmlspecialchars($product['description']) ?></p>

    <div class="price-lg"><?= number_format((float) $product['price'], 0, ',', '.') ?> đ</div>

    <form method="post" action="/add-to-cart.php">
      <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
      <div class="field" style="max-width:160px">
        <label for="qty">Số lượng</label>
        <input id="qty" name="quantity" type="number" min="1" max="99" value="1">
      </div>
      <div class="actions">
        <button class="lg" type="submit">Thêm vào giỏ hàng</button>
        <a class="button secondary lg" href="/">Quay lại danh mục</a>
      </div>
    </form>
  </article>
</div>
<?php
});
