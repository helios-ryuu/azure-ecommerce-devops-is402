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
  <p style="margin:0 0 18px;color:var(--text-secondary);font-size:13.5px">
    Xác thực tài khoản qua <code>user-svc</code> (Java 25 · Spring Boot) và PostgreSQL v18.
  </p>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <div class="field">
      <label for="username">Tên đăng nhập</label>
      <input id="username" name="username" autocomplete="username" placeholder="demo" required>
    </div>
    <div class="field">
      <label for="password">Mật khẩu</label>
      <input id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required>
    </div>
    <button class="lg full" type="submit">Đăng nhập</button>
  </form>
  <p style="margin:18px 0 0;font-size:13px;color:var(--text-secondary);text-align:center">
    Chưa có tài khoản? <a href="/register.php" style="color:var(--accent);font-weight:600;text-decoration:none">Đăng ký ngay</a>
  </p>
</div>
<?php });
