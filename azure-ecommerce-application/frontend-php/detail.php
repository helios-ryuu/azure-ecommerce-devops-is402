<?php
require __DIR__ . '/bootstrap.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT id,name,description,price,image_url FROM products WHERE id=?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { http_response_code(404); exit('Không tìm thấy sản phẩm'); }
page($product['name'], function () use ($product) {
?>
<article class="card">
  <img style="max-width:520px" src="<?= htmlspecialchars($product['image_url']) ?>" alt="">
  <p><?= htmlspecialchars($product['description']) ?></p>
  <p class="price"><?= number_format((float) $product['price'], 0, ',', '.') ?> đ</p>
  <form method="post" action="/add-to-cart.php">
    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
    <label>Số lượng <input name="quantity" type="number" min="1" max="99" value="1"></label>
    <button>Thêm vào giỏ</button>
  </form>
</article>
<?php
});
