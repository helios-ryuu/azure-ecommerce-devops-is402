<?php
require __DIR__ . '/bootstrap.php';

$meta = productsWithMeta();
page('Danh mục sản phẩm (Trang chủ)', function () use ($meta) {
?>
<p>
  <span class="badge">Nguồn dữ liệu: <?= htmlspecialchars($meta['source']) ?> · Thời gian đọc: <?= htmlspecialchars((string) $meta['latency_ms']) ?> ms</span>
</p>
<div class="grid">
  <?php foreach ($meta['items'] as $p): ?>
  <article class="card">
    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    <h2><?= htmlspecialchars($p['name']) ?></h2>
    <p><?= htmlspecialchars($p['description']) ?></p>
    <p class="price"><?= number_format((float) $p['price'], 0, ',', '.') ?> đ</p>
    <a class="button" href="/detail.php?id=<?= (int) $p['id'] ?>">Xem chi tiết</a>
  </article>
  <?php endforeach; ?>
</div>
<?php
});
