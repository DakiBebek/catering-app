<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();

$userId = $_SESSION['user_id'];
$all = supabaseSelect('caterings', 'user_id=eq.' . $userId . '&order=created_at.desc');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="index.php">➕ Tambah</a>
    <a href="logout.php">🚪 Keluar</a>
  </nav>
  <h1>📋 Semua Riwayat Catering</h1>
  <?php if (empty($all)): ?>
    <p>Belum ada data.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Mulai</th><th>Selesai</th><th>Bulan Selesai</th><th>Total</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($all as $c): ?>
          <tr>
            <td><?= date('d M Y', strtotime($c['start_date'])) ?></td>
            <td><?= date('d M Y', strtotime($c['end_date'])) ?></td>
            <td><?= $c['end_month'] ?></td>
            <td><?= $c['total_count'] ?>x</td>
            <td>
              <a href="dashboard.php?id=<?= $c['id'] ?>">Detail</a> |
              <a href="delete.php?id=<?= $c['id'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>