<?php
require __DIR__ . '/bootstrap.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = ['username' => trim($_POST['username'] ?? ''), 'password' => $_POST['password'] ?? ''];
    $result = apiRequest('POST', rtrim(envv('USER_SERVICE_URL', 'http://localhost:8080'), '/') . '/api/users/login', $payload);
    if ($result['status'] === 200) {
        session_regenerate_id(true);
        $_SESSION['user'] = $result['body'];
        header('Location: /', true, 303);
        exit;
    }
    $error = $result['body']['message'] ?? 'Đăng nhập thất bại';
}
page('Đăng nhập', function () use ($error) {
?>
<div class="form-card">
  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="post">
    <label>Tên đăng nhập</label><input name="username" required>
    <label>Mật khẩu</label><input name="password" type="password" required>
    <p><button>Đăng nhập</button></p>
  </form>
</div>
<?php });
