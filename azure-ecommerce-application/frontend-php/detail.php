<?php
require __DIR__ . '/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = $id ? findProduct($id) : null;
if (!$product) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm');
}

page('Chi tiết: ' . $product['name'], function () use ($product) {
?>
<p>
  <span class="badge">Nguồn dữ liệu: <?= htmlspecialchars((string) ($product['_cache_source'] ?? 'Cache-Aside')) ?> · <?= htmlspecialchars((string) ($product['_latency_ms'] ?? 0)) ?> ms</span>
</p>
<article class="card" style="max-width:560px">
  <img style="max-width:520px" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
  <h2><?= htmlspecialchars($product['name']) ?></h2>
  <p><?= htmlspecialchars($product['description']) ?></p>
  <p class="price"><?= number_format((float) $product['price'], 0, ',', '.') ?> đ</p>
  <form method="post" action="/add-to-cart.php">
    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
    <label>Số lượng <input name="quantity" type="number" min="1" max="99" value="1"></label>
    <p style="display:flex;gap:10px;margin-top:14px">
      <button type="submit">Thêm vào giỏ hàng</button>
      <a class="button secondary" href="/">Quay lại danh mục</a>
    </p>
  </form>
</article>
<?php
});
