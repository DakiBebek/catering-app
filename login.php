<?php
require_once 'config.php';
require_once 'auth.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remember = isset($_POST['remember']);
    $result = loginUser(trim($_POST['username']), $_POST['password'], $remember);
    if ($result['ok']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result['msg'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container auth-box">
  <h1>🔐 Login</h1>
  <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
  <form method="POST">
    <label>Username:</label>
    <input type="text" name="username" required>
    <label>Password:</label>
    <input type="password" name="password" required>

    <div class="remember-me">
      <label>
        <input type="checkbox" name="remember" value="1" checked>
        <span>🔑 Ingat saya selama 30 hari</span>
      </label>
    </div>

    <button type="submit">Masuk</button>
  </form>
  <p class="center">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
</body>
</html>