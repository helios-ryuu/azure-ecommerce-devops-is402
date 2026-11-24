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
page('Tạo tài khoản mới', function () use ($error) {
?>
<div class="form-card">
  <p style="margin:0 0 18px;color:var(--text-secondary);font-size:13.5px">
    Tài khoản mới sẽ được lưu trực tiếp vào bảng <code>users</code> trên Azure PostgreSQL v18 qua <code>user-svc</code>.
  </p>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <div class="field">
      <label for="username">Tên đăng nhập</label>
      <input id="username" name="username" minlength="3" maxlength="80" autocomplete="username" placeholder="vd: sinhvien_uit" required>
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="email" placeholder="sinhvien@gm.uit.edu.vn" required>
    </div>
    <div class="field">
      <label for="password">Mật khẩu (tối thiểu 8 ký tự)</label>
      <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="••••••••" required>
    </div>
    <button class="lg full" type="submit">Đăng ký tài khoản</button>
  </form>
  <p style="margin:18px 0 0;font-size:13px;color:var(--text-secondary);text-align:center">
    Đã có tài khoản? <a href="/login.php" style="color:var(--accent);font-weight:600;text-decoration:none">Đăng nhập</a>
  </p>
</div>
<?php });
