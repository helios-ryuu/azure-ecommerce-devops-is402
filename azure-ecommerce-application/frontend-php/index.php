<?php
require __DIR__ . '/bootstrap.php';

$meta = productsWithMeta();
page(
    'Danh mục sản phẩm',
    function () use ($meta) {
        $isHit = !empty($meta['cached']);
?>
<div class="infobar">
  <div class="infobar-left">
    <span class="status-dot <?= $isHit ? 'hit' : 'miss' ?>"></span>
    <span><strong>Nguồn dữ liệu:</strong> <?= htmlspecialchars($meta['source']) ?></span>
  </div>
  <span class="badge neutral">Thời gian đọc: <?= htmlspecialchars((string) $meta['latency_ms']) ?> ms · <?= count($meta['items']) ?> sản phẩm</span>
</div>

<div class="grid">
  <?php foreach ($meta['items'] as $p): ?>
  <article class="card product-card">
    <a class="product-thumb" href="/detail.php?id=<?= (int) $p['id'] ?>">
      <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
    </a>
    <h2 class="product-title"><?= htmlspecialchars($p['name']) ?></h2>
    <p class="product-desc"><?= htmlspecialchars($p['description']) ?></p>
    <div class="product-footer">
      <p class="price"><?= number_format((float) $p['price'], 0, ',', '.') ?> đ</p>
      <a class="button" href="/detail.php?id=<?= (int) $p['id'] ?>">Xem chi tiết</a>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php
    },
    'Dữ liệu danh mục tối ưu đọc bằng Azure Cache for Redis (Cache-Aside) kết hợp Azure PostgreSQL Flexible Server.'
);
