<?php
require_once 'config.php';
require_once 'auth.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $p = $_POST['password'];

    if (strlen($p) < 4) {
        $error = 'Password minimal 4 karakter!';
    } else {
        $result = registerUser($u, $p);
        if ($result['ok']) {
            $success = $result['msg'] . ' <a href="login.php">Login sekarang →</a>';
        } else {
            $error = $result['msg'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Akun</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container auth-box">
  <h1>📝 Daftar Akun</h1>
  <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
  <form method="POST">
    <label>Username:</label>
    <input type="text" name="username" required>
    <label>Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Daftar</button>
  </form>
  <p class="center">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
</body>
</html>