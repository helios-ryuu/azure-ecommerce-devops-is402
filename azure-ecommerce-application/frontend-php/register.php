<?php
require __DIR__ . '/bootstrap.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = ['username' => trim($_POST['username'] ?? ''), 'password' => $_POST['password'] ?? '', 'email' => trim($_POST['email'] ?? '')];
    $result = apiRequest('POST', rtrim(envv('USER_SERVICE_URL', 'http://localhost:8080'), '/') . '/api/users/register', $payload);
    if ($result['status'] === 201) {
        session_regenerate_id(true);
        $_SESSION['user'] = $result['body'];
        header('Location: /', true, 303);
        exit;
    }
    $error = $result['body']['message'] ?? 'Không thể đăng ký (HTTP ' . $result['status'] . ')';
}
page('Đăng ký', function () use ($error) {
?>
<div class="form-card">
  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="post">
    <label>Tên đăng nhập</label><input name="username" minlength="3" maxlength="80" required>
    <label>Email</label><input name="email" type="email" required>
    <label>Mật khẩu</label><input name="password" type="password" minlength="8" required>
    <p><button>Đăng ký</button></p>
  </form>
</div>
<?php });
