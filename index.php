<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Input Catering</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="history.php">📋 Riwayat</a>
    <a href="logout.php">🚪 Keluar (<?= htmlspecialchars($user['username']) ?>)</a>
  </nav>

  <h1>🍱 Input Catering Baru</h1>

  <form action="calculate.php" method="POST" class="form">
    <label>Tanggal Mulai:</label>
    <input type="date" name="start_date" required value="<?= date('Y-m-d') ?>">

    <label>Jumlah Catering (total kali makan):</label>
    <input type="number" name="total_count" min="1" required placeholder="Contoh: 20">

    <label>Pilih Hari Makan:</label>
    <div class="days">
      <label><input type="checkbox" name="days[]" value="1"> Senin</label>
      <label><input type="checkbox" name="days[]" value="2"> Selasa</label>
      <label><input type="checkbox" name="days[]" value="3"> Rabu</label>
      <label><input type="checkbox" name="days[]" value="4"> Kamis</label>
      <label><input type="checkbox" name="days[]" value="5"> Jumat</label>
      <label><input type="checkbox" name="days[]" value="6"> Sabtu</label>
      <label><input type="checkbox" name="days[]" value="0"> Minggu</label>
    </div>

    <button type="submit">💾 Hitung & Simpan</button>
  </form>
</div>
</body>
</html>