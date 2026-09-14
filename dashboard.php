<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();

$user   = currentUser();
$userId = $user['id'];

$caterings = supabaseSelect('caterings', 
    'user_id=eq.' . $userId . '&order=created_at.desc');

$activeId = $_GET['id'] ?? ($caterings[0]['id'] ?? null);

$activeCatering = null;
$schedules      = [];
$doneCount      = 0;
$pendingCount   = 0;
$todaySchedule  = null;

if ($activeId) {
    $cateringData = supabaseSelect('caterings', 'id=eq.' . $activeId);
    $activeCatering = $cateringData[0] ?? null;

    if ($activeCatering) {
        $schedules = supabaseSelect('catering_schedules', 
            'catering_id=eq.' . $activeId . '&order=sequence.asc');

        foreach ($schedules as $s) {
            if ($s['status'] === 'done') $doneCount++;
            else $pendingCount++;
        }

        $today = date('Y-m-d');
        foreach ($schedules as $s) {
            if ($s['schedule_date'] === $today) {
                $todaySchedule = $s;
                break;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $schedId   = (int) $_POST['toggle_id'];
    $newStatus = $_POST['new_status'];

    supabaseUpdate('catering_schedules', ['status' => $newStatus], 'id=eq.' . $schedId);

    header("Location: dashboard.php?id=$activeId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Catering</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav>
    <a href="index.php">➕ Tambah Catering</a>
    <a href="history.php">📋 Riwayat</a>
    <a href="logout.php">🚪 Keluar (<?= htmlspecialchars($user['username']) ?>)</a>
  </nav>

  <h1>🏠 Dashboard Catering</h1>

  <?php if (!$activeCatering): ?>
    <div class="alert info">Belum ada catering. <a href="index.php">Buat sekarang →</a></div>
  <?php else: ?>

    <div class="summary-grid">
      <div class="card total">
        <div class="num"><?= $activeCatering['total_count'] ?></div>
        <div class="label">Total Jatah</div>
      </div>
      <div class="card done">
        <div class="num"><?= $doneCount ?></div>
        <div class="label">Sudah Dimakan ✅</div>
      </div>
      <div class="card pending">
        <div class="num"><?= $pendingCount ?></div>
        <div class="label">Sisa Jatah 🍱</div>
      </div>
      <div class="card info-card">
        <div class="num"><?= $activeCatering['end_month'] ?></div>
        <div class="label">Selesai Bulan</div>
      </div>
    </div>

    <div class="info-box">
      <strong>Periode:</strong> <?= date('d M Y', strtotime($activeCatering['start_date'])) ?>
      → <?= date('d M Y', strtotime($activeCatering['end_date'])) ?>
      <?php if (count($caterings) > 1): ?>
        <br><strong>Ganti catering:</strong>
        <?php foreach ($caterings as $c): ?>
          <a href="dashboard.php?id=<?= $c['id'] ?>"
             class="<?= $c['id'] == $activeId ? 'active-link' : '' ?>">
            <?= date('d M', strtotime($c['start_date'])) ?> - <?= date('d M Y', strtotime($c['end_date'])) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($todaySchedule): ?>
      <div class="today-box <?= $todaySchedule['status'] === 'done' ? 'already' : '' ?>">
        <?php if ($todaySchedule['status'] === 'pending'): ?>
          <h2>🍽️ Hari ini jadwal makan kamu!</h2>
          <p><?= $todaySchedule['day_name'] ?>, <?= date('d M Y', strtotime($todaySchedule['schedule_date'])) ?></p>
          <p class="big">Sisa jatah setelah ini: <strong><?= $pendingCount - 1 ?> lagi</strong></p>
          <form method="POST">
            <input type="hidden" name="toggle_id" value="<?= $todaySchedule['id'] ?>">
            <input type="hidden" name="new_status" value="done">
            <button type="submit" class="btn-eat">✅ Sudah Makan!</button>
          </form>
        <?php else: ?>
          <h2>✔️ Hari ini sudah makan!</h2>
          <p><?= $todaySchedule['day_name'] ?>, <?= date('d M Y', strtotime($todaySchedule['schedule_date'])) ?></p>
          <p class="big">Sisa jatah: <strong><?= $pendingCount ?> lagi</strong></p>
          <form method="POST">
            <input type="hidden" name="toggle_id" value="<?= $todaySchedule['id'] ?>">
            <input type="hidden" name="new_status" value="pending">
            <button type="submit" class="btn-undo">↩️ Batalkan (Belum Makan)</button>
          </form>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="today-box no-meal">
        <h2>😴 Hari ini tidak ada jadwal makan</h2>
        <p>Sisa jatah: <strong><?= $pendingCount ?> lagi</strong></p>
      </div>
    <?php endif; ?>

    <div class="progress-wrap">
      <div class="progress-bar" style="width: <?= round(($doneCount / $activeCatering['total_count']) * 100) ?>%"></div>
    </div>
    <p class="progress-text"><?= round(($doneCount / $activeCatering['total_count']) * 100) ?>% selesai</p>

    <h3>📅 Semua Jadwal</h3>
    <table>
      <thead><tr><th>No</th><th>Tanggal</th><th>Hari</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($schedules as $s): ?>
          <tr class="<?= $s['status'] === 'done' ? 'row-done' : '' ?>">
            <td><?= $s['sequence'] ?></td>
            <td><?= date('d M Y', strtotime($s['schedule_date'])) ?></td>
            <td><?= $s['day_name'] ?></td>
            <td>
              <?php if ($s['status'] === 'done'): ?>
                <span class="badge done">✅ Dimakan</span>
              <?php else: ?>
                <span class="badge pending">⏳ Belum</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="toggle_id" value="<?= $s['id'] ?>">
                <input type="hidden" name="new_status" value="<?= $s['status'] === 'done' ? 'pending' : 'done' ?>">
                <button type="submit" class="btn-sm <?= $s['status'] === 'done' ? 'btn-undo-sm' : 'btn-done-sm' ?>">
                  <?= $s['status'] === 'done' ? '↩️' : '✅' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>